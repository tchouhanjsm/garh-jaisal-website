<?php
/**
 * Verify curated experience-gallery assets and their accessibility text.
 *
 * Run with: php tests/php/experience-gallery-assets.php
 */
$root = dirname(__DIR__, 2);
$registryPath = $root . '/include/experience-data.php';
$galleryPath = $root . '/gallery.php';
$errors = [];
$checkedGalleryAssets = 0;

$requiredResponsiveHeroSources = [
    'half-day-jaisalmer-tour' => 'img/j5.jpg',
    'sleep-under-the-stars' => 'img/sleeping_under_stars/tour_9.jpeg',
];
$responsiveManifestPath = $root . '/scripts/responsive-image-assets-manifest.json';
if (!is_file($responsiveManifestPath)) {
    $errors[] = 'responsive image manifest is missing';
    $responsiveManifest = [];
} else {
    $responsiveManifest = json_decode((string) file_get_contents($responsiveManifestPath), true);
    if (!is_array($responsiveManifest)) {
        $errors[] = 'responsive image manifest must contain valid JSON';
        $responsiveManifest = [];
    }
}
$manifestSources = isset($responsiveManifest['sources']) && is_array($responsiveManifest['sources'])
    ? $responsiveManifest['sources']
    : [];
$requiredHeroSources = isset($responsiveManifest['required_hero_sources']) && is_array($responsiveManifest['required_hero_sources'])
    ? $responsiveManifest['required_hero_sources']
    : [];
foreach ($requiredResponsiveHeroSources as $slug => $expectedSource) {
    if (!isset($registry[$slug]) || !is_array($registry[$slug])) {
        $errors[] = $slug . ': required responsive hero record is missing';
        continue;
    }
    $experience = $registry[$slug];
    if (($experience['hero_image'] ?? null) !== $expectedSource) {
        $errors[] = $slug . ': hero image must use the reviewed full-resolution source ' . $expectedSource;
    }
    if (!in_array($expectedSource, $manifestSources, true)) {
        $errors[] = $slug . ': hero source is missing from responsive image generation allowlist';
    }
    if (!in_array($expectedSource, $requiredHeroSources, true)) {
        $errors[] = $slug . ': hero source is not a required responsive release asset';
    }
    $imageDimensions = @getimagesize($root . '/' . $expectedSource);
    if (!is_array($imageDimensions) || !isset($imageDimensions[0]) || $imageDimensions[0] < 1200) {
        $errors[] = $slug . ': hero source must be at least 1200 pixels wide';
    }
    if (trim((string) ($experience['hero_alt'] ?? '')) === '') {
        $errors[] = $slug . ': hero alt text is required';
    }
    if (trim((string) ($experience['hero_caption'] ?? '')) === '') {
        $errors[] = $slug . ': hero caption is required';
    }
}

if (!is_file($registryPath)) {
    fwrite(STDERR, "ERROR missing experience registry\n");
    exit(1);
}
$registry = require $registryPath;
if (!is_array($registry)) {
    fwrite(STDERR, "ERROR experience registry must return an array\n");
    exit(1);
}

$requiredExperienceAssets = [
    'sleep-under-the-stars' => [
        'img/sleeping_under_stars/Tour_1.jpeg',
        'img/sleeping_under_stars/tour_2.jpeg',
        'img/sleeping_under_stars/tour_3.jpeg',
        'img/sleeping_under_stars/tour_4.jpeg',
        'img/sleeping_under_stars/tour_6.jpeg',
        'img/sleeping_under_stars/tour_7.jpeg',
        'img/sleeping_under_stars/tour_8.jpeg',
        'img/sleeping_under_stars/tour_9.jpeg',
        'img/sleeping_under_stars/tour_10.jpeg',
    ],
    'swiss-tent-safari' => [
        'img/Desert_Camp/camp_from_above.jpeg',
        'img/Desert_Camp/camp_evening.jpeg',
        'img/Desert_Camp/CampingInJsm.jpeg',
        'img/Desert_Camp/tent_deluxe.jpeg',
        'img/Desert_Camp/camp_dinner.jpeg',
        'img/Desert_Camp/Bathroom.jpeg',
        'img/Desert_Camp/Outtent.jpeg',
        'img/Desert_Camp/Tent.jpeg',
        'img/Desert_Camp/Tentout.jpeg',
        'img/Desert_Camp/tentout1.jpeg',
    ],
];
foreach ($registry as $slug => $experience) {
    if (!is_array($experience) || !isset($experience['gallery'])) {
        continue;
    }
    if (!is_array($experience['gallery'])) {
        $errors[] = $slug . ': gallery must be an array';
        continue;
    }
    $sources = [];
    foreach ($experience['gallery'] as $index => $photo) {
        $label = $slug . ' gallery item ' . ($index + 1);
        if (!is_array($photo)) {
            $errors[] = $label . ': item must be an array';
            continue;
        }
        $src = isset($photo['src']) && is_string($photo['src']) ? trim($photo['src']) : '';
        $alt = isset($photo['alt']) && is_string($photo['alt']) ? trim($photo['alt']) : '';
        $caption = isset($photo['caption']) && is_string($photo['caption']) ? trim($photo['caption']) : '';
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
            $errors[] = $label . ': alt text is required';
        }
        if ($caption === '') {
            $errors[] = $label . ': non-empty caption is required';
        }
        $sources[] = $src;
        $checkedGalleryAssets++;
    }

    if (isset($requiredExperienceAssets[$slug])) {
        foreach ($requiredExperienceAssets[$slug] as $requiredSrc) {
            if (!in_array($requiredSrc, $sources, true)) {
                $errors[] = $slug . ': required gallery image is missing: ' . $requiredSrc;
            }
        }
    }
}

foreach ($requiredExperienceAssets as $slug => $requiredAssets) {
    if (!isset($registry[$slug]) || !isset($registry[$slug]['gallery']) || !is_array($registry[$slug]['gallery'])) {
        $errors[] = $slug . ': required experience gallery is missing';
    }
}

if (!is_file($galleryPath)) {
    $errors[] = 'gallery.php is missing';
}

$galleryDataPath = $root . '/include/gallery-data.php';
if (!is_file($galleryDataPath)) {
    $errors[] = 'include/gallery-data.php is missing';
} else {
    $gallerySections = require $galleryDataPath;
    $curatedDesertSources = [];
    if (is_array($gallerySections) && isset($gallerySections['desert']['images']) && is_array($gallerySections['desert']['images'])) {
        foreach ($gallerySections['desert']['images'] as $photo) {
            if (is_array($photo) && isset($photo['src']) && is_string($photo['src'])) {
                $curatedDesertSources[] = $photo['src'];
            }
        }
    } else {
        $errors[] = 'main Gallery desert section is missing from the data registry';
    }

    // The main Gallery is intentionally a 36-photo introduction; the full owner-provided
    // desert library stays available in the dedicated experience pages and is checked above.
    $requiredCuratedDesertAssets = [
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
    foreach ($requiredCuratedDesertAssets as $asset) {
        if (!in_array($asset, $curatedDesertSources, true)) {
            $errors[] = 'main Gallery is missing a selected desert photo: ' . $asset;
        }
    }
}
foreach ($errors as $error) {
    fwrite(STDERR, "ERROR " . $error . PHP_EOL);
}
if ($errors) {
    printf("%d gallery asset error(s) across %d validated assets\n", count($errors), $checkedGalleryAssets);
    exit(1);
}
printf("PASS: validated %d experience gallery assets and the curated main Gallery desert subset\n", $checkedGalleryAssets);
