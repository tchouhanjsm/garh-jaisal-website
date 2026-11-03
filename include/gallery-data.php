<?php
/**
 * Public gallery presentation metadata plus the owner-managed JSON photo registry.
 *
 * Existing image URLs remain unchanged during migration. New and replacement
 * uploads are stored beneath img/gallery/ so editing a gallery photo never
 * overwrites an image reused by a room, experience or other public page.
 */
$gallerySections = [
    'hotel' => [
        'eyebrow' => 'Hotel',
        'title' => 'The haveli',
        'description' => 'Shared spaces, sandstone details and views around Garh Jaisal Haveli.',
        'images' => [],
    ],
    'rooms' => [
        'eyebrow' => 'Rooms',
        'title' => 'Rooms with character',
        'description' => 'The rooms featured on the current Rooms page, shown from different angles and details.',
        'images' => [],
    ],
    'desert' => [
        'eyebrow' => 'Desert',
        'title' => 'Desert experiences',
        'description' => 'Explore the desert camp, Swiss tent interiors and bathroom, evening programme, and the open-air Sleep Under the Stars experience.',
        'images' => [],
        'cta' => [
            'title' => 'Choosing an overnight desert stay?',
            'text' => 'Compare the open-air Sleep Under the Stars experience with a Swiss Deluxe Tent stay, then confirm current availability and inclusions with the hotel.',
            'label' => 'Compare overnight options',
            'href' => 'camping.php',
        ],
    ],
    'restaurant' => [
        'eyebrow' => 'Restaurant',
        'title' => 'Dining',
        'description' => 'Dining spaces and rooftop restaurant views at Garh Jaisal Haveli.',
        'images' => [],
    ],
];

$galleryRoot = dirname(__DIR__);
$galleryContentRoot = $galleryRoot . '/content/gallery';
$galleryImageRoot = $galleryRoot . '/img';
$allowedCategories = ['hotel', 'rooms', 'desert', 'restaurant'];
$allowedStatuses = ['live', 'draft', 'hidden'];
$allowedCrops = ['arch', 'landscape', 'portrait', 'standard'];

// Only the exact 36 original JSON records are grandfathered during rollout.
// Any byte-level edit exits the legacy path and requires a signed approval.
$legacyBaselinePath = __DIR__ . '/gallery-legacy-baseline.php';
if (is_link($legacyBaselinePath) || !is_file($legacyBaselinePath)) {
    throw new RuntimeException('Gallery legacy approval baseline is missing or unsafe.');
}
$legacyBaseline = require $legacyBaselinePath;
if (!is_array($legacyBaseline) || count($legacyBaseline) !== 36) {
    throw new RuntimeException('Gallery legacy approval baseline must contain exactly 36 records.');
}
foreach ($legacyBaseline as $legacyId => $legacyHash) {
    if (!is_string($legacyId) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $legacyId)
        || !is_string($legacyHash) || !preg_match('/^[a-f0-9]{64}$/D', $legacyHash)) {
        throw new RuntimeException('Gallery legacy approval baseline contains an invalid entry.');
    }
}
$galleryConfig = require __DIR__ . '/site-config.php';
$galleryApprovalKey = is_array($galleryConfig) ? ($galleryConfig['media_approval_hmac_key'] ?? '') : '';
$galleryApprovers = is_array($galleryConfig) ? ($galleryConfig['media_approvers'] ?? []) : [];
if (!is_string($galleryApprovalKey)) $galleryApprovalKey = '';
if (!is_array($galleryApprovers)) $galleryApprovers = [];
$galleryRecords = [];
$usedGalleryIds = [];
$usedGallerySources = [];
$legacyOrderByCategory = array_fill_keys($allowedCategories, 0);

if (is_link($galleryContentRoot)) {
    throw new RuntimeException('Unsafe Sveltia gallery content directory.');
}
$galleryFiles = is_dir($galleryContentRoot) ? (glob($galleryContentRoot . '/*.json') ?: []) : [];
sort($galleryFiles, SORT_STRING);

if (is_link($galleryImageRoot) || !is_dir($galleryImageRoot)) {
    throw new RuntimeException('Gallery image root is missing or unsafe.');
}
$galleryImageRootReal = realpath($galleryImageRoot);
if (!is_string($galleryImageRootReal)) {
    throw new RuntimeException('Gallery image root cannot be resolved.');
}

foreach ($galleryFiles as $galleryFile) {
    if (is_link($galleryFile) || !is_file($galleryFile)) {
        throw new RuntimeException('Unsafe Sveltia gallery record: ' . basename($galleryFile));
    }

    $recordJson = file_get_contents($galleryFile);
    $record = is_string($recordJson) ? json_decode($recordJson, true) : null;
    $id = basename($galleryFile, '.json');
    if (
        !is_array($record)
        || json_last_error() !== JSON_ERROR_NONE
        || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id)
        || ($record['id'] ?? null) !== $id
        || isset($usedGalleryIds[$id])
    ) {
        throw new RuntimeException('Invalid Sveltia gallery record identity: ' . $id);
    }

    $category = $record['category'] ?? null;
    $status = $record['status'] ?? null;
    $hasExplicitOrder = array_key_exists('order', $record);
    $order = $hasExplicitOrder ? $record['order'] : null;
    $photo = $record['image'] ?? null;
    $src = is_array($photo) && is_string($photo['src'] ?? null) ? trim($photo['src']) : '';
    $alt = is_array($photo) && is_string($photo['alt'] ?? null) ? trim($photo['alt']) : '';
    $crop = is_array($photo) && is_string($photo['crop'] ?? null) ? $photo['crop'] : '';

    if (
        !is_string($category) || !in_array($category, $allowedCategories, true)
        || !is_string($status) || !in_array($status, $allowedStatuses, true)
        || !is_array($photo)
        || $src === ''
        || !preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$~iD', $src)
        || strpos($src, '..') !== false
        || strpos($src, chr(92)) !== false
        || $alt === ''
        || !in_array($crop, $allowedCrops, true)
    ) {
        throw new RuntimeException('Invalid Sveltia/Content Studio gallery record fields: ' . $id);
    }

    // Older local Content Studio records have no numeric order; keep them after explicitly ordered records.
    if (!$hasExplicitOrder) {
        $order = PHP_INT_MAX - 100000 + $legacyOrderByCategory[$category];
        $legacyOrderByCategory[$category]++;
    }
    if (!is_int($order) || $order < 1) {
        throw new RuntimeException('Invalid Gallery display order: ' . $id);
    }

    $relativeParts = explode('/', substr($src, 4));
    $cursor = $galleryImageRootReal;
    foreach ($relativeParts as $index => $part) {
        $cursor .= DIRECTORY_SEPARATOR . $part;
        if (is_link($cursor) || !file_exists($cursor)) {
            throw new RuntimeException('Missing or unsafe Sveltia gallery image: ' . $src);
        }
        if ($index < count($relativeParts) - 1 && !is_dir($cursor)) {
            throw new RuntimeException('Invalid Sveltia gallery image path: ' . $src);
        }
    }
    if (!is_file($cursor)) {
        throw new RuntimeException('Sveltia gallery source is not a regular image file: ' . $src);
    }
    $realImagePath = realpath($cursor);
    if (!is_string($realImagePath) || strpos($realImagePath, $galleryImageRootReal . DIRECTORY_SEPARATOR) !== 0) {
        throw new RuntimeException('Sveltia gallery image escapes img/: ' . $src);
    }
    if (isset($usedGallerySources[$src])) {
        throw new RuntimeException('Duplicate Sveltia gallery source path: ' . $src);
    }

    $hasApprovalObject = array_key_exists('approval', $record);
    $approved = false;
    if ($hasApprovalObject) {
        // Explicitly governed records must pass rights, visual, technical, signer and asset checks.
        $imageInfo = @getimagesize($realImagePath);
        $decodable = is_array($imageInfo) && !empty($imageInfo[0]) && !empty($imageInfo[1]);
        $approved = $decodable && gj_gallery_media_approval_is_valid(
            $record, $realImagePath, $galleryApprovalKey, $galleryApprovers
        );
    } else {
        // A byte-for-byte match is the only grandfathered path for the 36 original photos.
        $baselineHash = $legacyBaseline[$id] ?? null;
        $currentRecordHash = hash('sha256', $recordJson);
        $approved = is_string($baselineHash) && hash_equals($baselineHash, $currentRecordHash);
    }

    $usedGalleryIds[$id] = true;
    $usedGallerySources[$src] = true;
    $galleryRecords[$category][] = [
        'id' => $id,
        'status' => $status,
        'approved' => $approved,
        'approval_status' => is_array($record['approval'] ?? null)
            ? (string) ($record['approval']['status'] ?? 'pending')
            : ($approved ? 'legacy-baseline' : 'pending'),
        'order' => $order,
        'image' => [
            'src' => $src,
            'alt' => $alt,
            'crop' => $crop,
            // A blank CMS caption must retain the legacy alt-text fallback used by carousels.
            'caption' => is_string($photo['caption'] ?? null) && trim($photo['caption']) !== ''
                ? trim($photo['caption'])
                : $alt,
        ],
    ];
}

foreach ($allowedCategories as $category) {
    $records = $galleryRecords[$category] ?? [];
    usort($records, static function ($left, $right) {
        $orderCompare = $left['order'] <=> $right['order'];
        return $orderCompare !== 0 ? $orderCompare : strcmp($left['id'], $right['id']);
    });
    foreach ($records as $record) {
        if ($record['status'] !== 'live' || ($record['approved'] ?? false) !== true) {
            continue;
        }
        $gallerySections[$category]['images'][] = $record['image'];
    }
}

return $gallerySections;
