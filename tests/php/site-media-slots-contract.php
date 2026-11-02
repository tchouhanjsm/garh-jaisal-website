<?php
/**
 * Contract for 33 fixed owner-managed page image slots.
 *
 * Run with: php tests/php/site-media-slots-contract.php
 */
require_once dirname(__DIR__, 2) . '/include/site-media-slots.php';
$root = dirname(__DIR__, 2);
$slots = gj_load_site_media_slots($root);
$errors = [];
$expected = [
    'home' => ['home-hero-mobile', 'home-hero-desktop', 'home-heritage-intro', 'home-rooftop-feature', 'home-desert-safari-feature', 'home-swiss-tent-feature', 'home-sleep-under-stars-feature', 'home-city-discovery-feature', 'home-gallery-city', 'home-gallery-courtyard', 'home-gallery-reception', 'home-gallery-rooftop', 'home-gallery-entrance'],
    'rooms' => ['rooms-hero', 'room-sweet-purple-room-primary', 'room-rising-sun-room-primary', 'room-scarlet-room-primary', 'room-opium-room-primary', 'room-red-maharani-room-primary', 'room-green-room-primary', 'room-bluesky-room-primary'],
    'dining' => ['dining-hero'],
    'experiences' => ['experience-desert-safari-hero', 'experience-guided-jaisalmer-tour-hero', 'experience-sunset-camel-safari-hero', 'experience-sleep-under-the-stars-hero', 'experience-swiss-tent-safari-hero', 'experience-cultural-safari-hero', 'experience-explore-the-desert-hero', 'experience-half-day-jaisalmer-tour-hero', 'experience-full-day-jaisalmer-tour-hero', 'experience-longewala-tour-hero', 'experience-rajasthan-tours-hero'],
];
$count = 0;
foreach ($expected as $area => $ids) {
    $count += count($ids);
    foreach ($ids as $id) {
        if (!isset($slots[$id]) || ($slots[$id]['area'] ?? null) !== $area) {
            $errors[] = 'Missing or misclassified slot: ' . $id;
            continue;
        }
        $image = $slots[$id]['image'] ?? [];
        if (!is_array($image) || !is_string($image['src'] ?? null)
            || !is_string($image['alt'] ?? null) || strlen(trim($image['alt'])) < 15) {
            $errors[] = 'Incomplete image metadata: ' . $id;
        }
    }
}
if (count($slots) !== $count) $errors[] = 'Expected 33 slots; found ' . count($slots) . '.';
$fallback = ['src' => 'img/fallback.webp', 'alt' => 'Reviewed fallback description for a page image'];
if (gj_site_media_slot($slots, 'missing-slot', $fallback) !== $fallback) {
    $errors[] = 'Missing slot must return the reviewed fallback unchanged.';
}
if (isset($slots['home-hero-mobile'])) {
    $chosen = gj_site_media_slot($slots, 'home-hero-mobile', $fallback);
    if (($chosen['src'] ?? null) !== $slots['home-hero-mobile']['image']['src']) {
        $errors[] = 'Existing slot must override the page fallback.';
    }
}

$tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gj-page-media-' . uniqid('', true);
$contentRoot = $tempRoot . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'page-media';
@mkdir($contentRoot, 0775, true);
@mkdir($tempRoot . DIRECTORY_SEPARATOR . 'img', 0775, true);
file_put_contents($contentRoot . '/home.json', json_encode([
    'home-hero-mobile' => [
        'src' => 'img/../outside.webp',
        'alt' => 'A descriptive but unsafe image path',
    ],
]));
if (gj_load_site_media_slots($tempRoot) !== []) {
    $errors[] = 'Unsafe page image paths must be ignored.';
}
$removeTree = null;
$removeTree = static function ($path) use (&$removeTree) {
    if (is_link($path) || is_file($path)) { @unlink($path); return; }
    if (!is_dir($path)) return;
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') $removeTree($path . DIRECTORY_SEPARATOR . $entry);
    }
    @rmdir($path);
};
$removeTree($tempRoot);
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS 33 fixed page image slots, safe image validation and PHP fallbacks.\n";
