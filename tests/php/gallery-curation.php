<?php
/**
 * Guard the public Gallery's curated image inventory and progressive-enhancement hooks.
 *
 * Run with: php tests/php/gallery-curation.php
 */
$root = dirname(__DIR__, 2);
$registryPath = $root . '/include/gallery-data.php';
$galleryPath = $root . '/gallery.php';
$scriptPath = $root . '/js/garh-v2.js';
$stylePath = $root . '/css/gallery.css';
$errors = [];

if (!is_file($registryPath)) {
    fwrite(STDERR, "ERROR missing include/gallery-data.php\n");
    exit(1);
}
$sections = require $registryPath;
if (!is_array($sections)) {
    fwrite(STDERR, "ERROR Gallery data registry must return an array\n");
    exit(1);
}

$expectedCounts = [
    'hotel' => 7,
    'rooms' => 14,
    'desert' => 10,
    'restaurant' => 5,
];
if (array_keys($sections) !== array_keys($expectedCounts)) {
    $errors[] = 'Gallery categories must remain in the approved hotel, rooms, desert, restaurant order';
}

$allowedCrops = ['arch', 'landscape', 'portrait', 'standard'];
$allSources = [];
$total = 0;
foreach ($expectedCounts as $category => $expectedCount) {
    if (!isset($sections[$category]) || !is_array($sections[$category])) {
        $errors[] = 'Gallery category is missing or invalid: ' . $category;
        continue;
    }
    $section = $sections[$category];
    foreach (['eyebrow', 'title', 'description', 'images'] as $requiredKey) {
        if (!isset($section[$requiredKey])) {
            $errors[] = $category . ': missing required registry field ' . $requiredKey;
        }
    }
    if (!isset($section['images']) || !is_array($section['images'])) {
        $errors[] = $category . ': images must be an array';
        continue;
    }
    if (count($section['images']) !== $expectedCount) {
        $errors[] = $category . ': expected ' . $expectedCount . ' curated photos, found ' . count($section['images']);
    }

    foreach ($section['images'] as $index => $photo) {
        $label = $category . ' photo ' . ($index + 1);
        if (!is_array($photo)) {
            $errors[] = $label . ': photo must be an array';
            continue;
        }
        $src = isset($photo['src']) && is_string($photo['src']) ? trim($photo['src']) : '';
        $alt = isset($photo['alt']) && is_string($photo['alt']) ? trim($photo['alt']) : '';
        $crop = isset($photo['crop']) && is_string($photo['crop']) ? trim($photo['crop']) : '';

        if (
            $src === ''
            || strpos($src, '..') !== false
            || strpos($src, chr(92)) !== false
            || !preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$~iD', $src)
        ) {
            $errors[] = $label . ': invalid local image path';
            continue;
        }
        if (!is_file($root . '/' . $src)) {
            $errors[] = $label . ': image file is missing: ' . $src;
        }
        if ($alt === '') {
            $errors[] = $label . ': descriptive alt text is required';
        }
        if (!in_array($crop, $allowedCrops, true)) {
            $errors[] = $label . ': unsupported crop treatment ' . $crop;
        }
        if (isset($allSources[$src])) {
            $errors[] = $label . ': duplicate source path ' . $src;
        }
        $allSources[$src] = true;
        $total++;
    }
}

if ($total !== 36) {
    $errors[] = 'Gallery must contain exactly 36 curated photos; found ' . $total;
}

$expectedDesertSources = [
    'img/Desert_Camp/camp_from_above.jpeg',
    'img/Desert_Camp/camp_evening.jpeg',
    'img/Desert_Camp/tent_deluxe.jpeg',
    'img/Desert_Camp/Bathroom.jpeg',
    'img/Desert_Camp/Prog.jpeg',
    'img/Desert_Camp/Tent.jpeg',
    'img/Desert_Camp/Tentout.jpeg',
    'img/sleeping_under_stars/Tour_1.jpeg',
    'img/sleeping_under_stars/tour_4.jpeg',
    'img/sleeping_under_stars/tour_6.jpeg',
];
$desertSources = [];
foreach (($sections['desert']['images'] ?? []) as $photo) {
    if (is_array($photo) && isset($photo['src']) && is_string($photo['src'])) {
        $desertSources[] = $photo['src'];
    }
}
foreach ($expectedDesertSources as $source) {
    if (!in_array($source, $desertSources, true)) {
        $errors[] = 'approved desert selection is missing: ' . $source;
    }
}

foreach ([
    $galleryPath => [
        "require __DIR__ . '/include/gallery-data.php'",
        'data-gallery-more',
        'data-gallery-count',
        'data-gallery-open',
    ],
    $scriptPath => [
        'const pageSize = 6;',
        'galleryVisibleCount',
        'visibleImages',
    ],
    $stylePath => [
        '.gallery-item--arch',
        '.gallery-item--portrait',
        '.gallery-item--landscape',
        'aspect-ratio: 21 / 9;',
        '.gallery-more:focus-visible',
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
    printf("%d Gallery curation error(s) across %d registry photos\n", count($errors), $total);
    exit(1);
}

printf("PASS: validated %d curated Gallery photos across %d ordered categories\n", $total, count($expectedCounts));
