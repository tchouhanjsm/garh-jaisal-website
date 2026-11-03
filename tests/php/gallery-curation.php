<?php
/**
 * Verify the owner-managed Gallery registry, image ordering and progressive enhancement.
 *
 * Run with: php tests/php/gallery-curation.php
 */
$root = dirname(__DIR__, 2);
$registryPath = $root . '/include/gallery-data.php';
$galleryPath = $root . '/gallery.php';
$scriptPath = $root . '/js/garh-v2.js';
$stylePath = $root . '/css/gallery.css';
$contentRoot = $root . '/content/gallery';
require_once $root . '/include/gallery-media-approval.php';
$legacyBaseline = require $root . '/include/gallery-legacy-baseline.php';
$runtimeConfig = require $root . '/include/site-config.php';
$approvalKey = is_array($runtimeConfig) ? ($runtimeConfig['media_approval_hmac_key'] ?? '') : '';
$approvers = is_array($runtimeConfig) ? ($runtimeConfig['media_approvers'] ?? []) : [];
if (!is_string($approvalKey)) $approvalKey = '';
if (!is_array($approvers)) $approvers = [];
$errors = [];
if (!is_array($legacyBaseline) || count($legacyBaseline) !== 36) {
    $errors[] = 'Immutable Gallery baseline must contain exactly 36 original records.';
}

if (!is_file($registryPath) || is_link($registryPath)) {
    fwrite(STDERR, "ERROR missing or unsafe include/gallery-data.php\n");
    exit(1);
}
$sections = require $registryPath;
if (!is_array($sections)) {
    fwrite(STDERR, "ERROR Gallery data registry must return an array\n");
    exit(1);
}

$expectedCategories = ['hotel', 'rooms', 'desert', 'restaurant'];
if (array_keys($sections) !== $expectedCategories) {
    $errors[] = 'Gallery categories must remain in the approved hotel, rooms, desert, restaurant order';
}
$allowedStatuses = ['live', 'draft', 'hidden'];
$allowedCrops = ['arch', 'landscape', 'portrait', 'standard'];
$recordFiles = is_dir($contentRoot) && !is_link($contentRoot) ? (glob($contentRoot . '/*.json') ?: []) : [];
sort($recordFiles, SORT_STRING);
$recordsByCategory = array_fill_keys($expectedCategories, []);
$allSources = [];
$legacyOrderByCategory = array_fill_keys($expectedCategories, 0);

foreach ($recordFiles as $recordFile) {
    if (is_link($recordFile) || !is_file($recordFile)) {
        $errors[] = 'Gallery JSON record is missing or unsafe: ' . basename($recordFile);
        continue;
    }
    $recordJson = file_get_contents($recordFile);
    $record = is_string($recordJson) ? json_decode($recordJson, true) : null;
    $id = basename($recordFile, '.json');
    if (
        !is_array($record) || json_last_error() !== JSON_ERROR_NONE
        || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id)
        || ($record['id'] ?? null) !== $id
    ) {
        $errors[] = 'Gallery record identity or JSON is invalid: ' . basename($recordFile);
        continue;
    }

    $category = $record['category'] ?? null;
    $status = $record['status'] ?? null;
    $order = $record['order'] ?? null;
    $image = $record['image'] ?? null;
    $src = is_array($image) && is_string($image['src'] ?? null) ? trim($image['src']) : '';
    $alt = is_array($image) && is_string($image['alt'] ?? null) ? trim($image['alt']) : '';
    $caption = is_array($image) && is_string($image['caption'] ?? null) ? trim($image['caption']) : '';
    $crop = is_array($image) && is_string($image['crop'] ?? null) ? trim($image['crop']) : '';
    if (
        !is_string($category) || !in_array($category, $expectedCategories, true)
        || !is_string($status) || !in_array($status, $allowedStatuses, true)
        || !is_array($image) || $alt === ''
        || !preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$~iD', $src)
        || strpos($src, '..') !== false || strpos($src, chr(92)) !== false
        || !in_array($crop, $allowedCrops, true)
    ) {
        $errors[] = 'Gallery record fields are invalid: ' . $id;
        continue;
    }

    // Preserve the legacy Content Studio record format without an order field.
    if (!array_key_exists('order', $record)) {
        $order = PHP_INT_MAX - 100000 + $legacyOrderByCategory[$category];
        $legacyOrderByCategory[$category]++;
    } elseif (!is_int($order) || $order < 1) {
        $errors[] = 'Gallery record order is invalid: ' . $id;
        continue;
    }

    $candidate = $root . '/' . $src;
    $cursor = $root;
    $unsafePath = false;
    foreach (explode('/', $src) as $part) {
        $cursor .= DIRECTORY_SEPARATOR . $part;
        if (is_link($cursor)) {
            $unsafePath = true;
            break;
        }
    }
    if ($unsafePath) {
        $errors[] = $id . ': symlink in image path ' . $src;
        continue;
    }
    if (!is_file($candidate) || filesize($candidate) <= 0) {
        $errors[] = $id . ': image is missing, empty or not decodable: ' . $src;
        continue;
    }
    if (isset($allSources[$src])) {
        $errors[] = $id . ': duplicate image reference ' . $src;
        continue;
    }
    $approved = gj_gallery_media_publication_is_approved(
        $record, $recordJson, $candidate, $legacyBaseline, $approvalKey, $approvers
    );
    $allSources[$src] = true;
    $recordsByCategory[$category][] = [
        'id' => $id,
        'status' => $status,
        'approved' => $approved,
        'order' => $order,
        'src' => $src,
        'alt' => $alt,
        'expected_caption' => $caption !== '' ? $caption : $alt,
    ];
}

if (count($recordFiles) < 36) {
    $errors[] = 'Gallery should retain the 36 migrated owner-managed records; found ' . count($recordFiles);
}

$totalLive = 0;
foreach ($expectedCategories as $category) {
    if (empty($recordsByCategory[$category])) {
        $errors[] = 'Gallery category has no owner-managed records: ' . $category;
    }
    usort($recordsByCategory[$category], static function ($left, $right) {
        $orderCompare = $left['order'] <=> $right['order'];
        return $orderCompare !== 0 ? $orderCompare : strcmp($left['id'], $right['id']);
    });

    $expectedLiveSources = [];
    foreach ($recordsByCategory[$category] as $record) {
        if ($record['status'] === 'live' && ($record['approved'] ?? false) === true) {
            $expectedLiveSources[] = $record['src'];
        }
    }
    $actualLiveSources = [];
    $actualLivePhotos = [];
    foreach (($sections[$category]['images'] ?? []) as $photo) {
        if (is_array($photo) && is_string($photo['src'] ?? null)) {
            $actualLiveSources[] = $photo['src'];
            $actualLivePhotos[$photo['src']] = $photo;
        }
    }
    if ($actualLiveSources !== $expectedLiveSources) {
        $errors[] = $category . ': PHP renderer must publish only live records in numeric order';
    }
    foreach ($recordsByCategory[$category] as $record) {
        if ($record['status'] !== 'live' || ($record['approved'] ?? false) !== true) {
            continue;
        }
        $publishedPhoto = $actualLivePhotos[$record['src']] ?? [];
        $publishedCaption = is_string($publishedPhoto['caption'] ?? null) ? trim($publishedPhoto['caption']) : '';
        if ($publishedCaption !== $record['expected_caption']) {
            $errors[] = $category . ': published caption must use the CMS caption or fall back to alt text for ' . $record['id'];
        }
    }
    if (count($actualLiveSources) < 1) {
        $errors[] = $category . ': keep at least one live photo in each public gallery category';
    }
    $totalLive += count($actualLiveSources);
}

if ($totalLive < 4) {
    $errors[] = 'Gallery must publish at least one image in each of its four categories.';
}

foreach ([
    $galleryPath => [
        "require __DIR__ . '/include/gallery-data.php'",
        'data-gallery-more',
        'data-gallery-count',
        'data-gallery-open',
        'gallery-lightbox-instructions',
        'data-gallery-lightbox-position',
        'aria-live="polite"',
    ],
    $scriptPath => [
        'const pageSize = 6;',
        'galleryVisibleCount',
        'visibleImages',
        'ArrowLeft',
        'ArrowRight',
        'prewarmNeighbors',
        'activeTrigger.focus',
    ],
    $stylePath => [
        '.gallery-item--arch',
        '.gallery-item--portrait',
        '.gallery-item--landscape',
        'aspect-ratio: 21 / 9;',
        '.gallery-more:focus-visible',
        '.gallery-lightbox-help',
        '.gallery-lightbox-position',
        '.gallery-lightbox-actions button:disabled',
    ],
] as $path => $markers) {
    if (!is_file($path)) {
        $errors[] = 'required Gallery implementation file missing: ' . basename($path);
        continue;
    }
    $source = file_get_contents($path);
    if (!is_string($source)) {
        $errors[] = 'could not read Gallery implementation file: ' . basename($path);
        continue;
    }
    foreach ($markers as $marker) {
        if (strpos($source, $marker) === false) {
            $errors[] = basename($path) . ': required progressive Gallery marker is missing: ' . $marker;
        }
    }
}

foreach ($errors as $error) {
    fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
}
if ($errors) {
    printf("%d Gallery curation error(s) across %d managed records\n", count($errors), count($recordFiles));
    exit(1);
}

printf("PASS: validated %d owner-managed Gallery records; %d live photos across %d ordered categories\n", count($recordFiles), $totalLive, count($expectedCategories));
