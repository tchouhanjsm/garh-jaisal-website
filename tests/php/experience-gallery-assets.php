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
        'img/Desert_Camp/Prog.jpeg',
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
} else {
    $globalGallerySource = file_get_contents($galleryPath);
    if (!is_string($globalGallerySource)) {
        $errors[] = 'gallery.php could not be read';
    } else {
        $globalGalleryAssets = [
            'Desert_Camp/camp_from_above.jpeg',
            'Desert_Camp/camp_evening.jpeg',
            'Desert_Camp/CampingInJsm.jpeg',
            'Desert_Camp/tent_deluxe.jpeg',
            'Desert_Camp/camp_dinner.jpeg',
            'Desert_Camp/Bathroom.jpeg',
            'Desert_Camp/Outtent.jpeg',
            'Desert_Camp/Prog.jpeg',
            'Desert_Camp/Tent.jpeg',
            'Desert_Camp/Tentout.jpeg',
            'Desert_Camp/tentout1.jpeg',
            'sleeping_under_stars/Tour_1.jpeg',
            'sleeping_under_stars/tour_2.jpeg',
            'sleeping_under_stars/tour_3.jpeg',
            'sleeping_under_stars/tour_4.jpeg',
            'sleeping_under_stars/tour_6.jpeg',
            'sleeping_under_stars/tour_7.jpeg',
            'sleeping_under_stars/tour_8.jpeg',
            'sleeping_under_stars/tour_9.jpeg',
            'sleeping_under_stars/tour_10.jpeg',
        ];
        foreach ($globalGalleryAssets as $asset) {
            if (strpos($globalGallerySource, "'" . $asset . "'") === false) {
                $errors[] = 'gallery.php does not reference the expected photo: ' . $asset;
            }
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
printf("PASS: validated %d experience gallery assets and the main Gallery references\n", $checkedGalleryAssets);
