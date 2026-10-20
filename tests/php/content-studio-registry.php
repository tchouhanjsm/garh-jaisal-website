<?php
/**
 * Integration check for Content Studio JSON overlays. Creates temporary fixtures,
 * verifies registry ingestion, then removes the fixtures unconditionally.
 */
$root = dirname(__DIR__, 2);
$experienceSlug = 'content-studio-smoke-experience';
$editorialSlug = 'content-studio-smoke-guide';
$galleryId = 'content-studio-smoke-photo';
$paths = [
    $root . '/content/experiences/' . $experienceSlug . '.json',
    $root . '/content/editorial/' . $editorialSlug . '.json',
    $root . '/content/gallery/' . $galleryId . '.json',
];
$dirs = array_unique(array_map('dirname', $paths));
$errors = [];
foreach ($paths as $path) {
    if (file_exists($path) || is_link($path)) {
        fwrite(STDERR, "ERROR smoke fixture already exists; refusing to overwrite it.\n");
        exit(1);
    }
}
$gallery = require $root . '/include/gallery-data.php';
$baseline = [];
foreach ($gallery as $section) {
    foreach (($section['images'] ?? []) as $photo) {
        if (is_array($photo) && is_string($photo['src'] ?? null)) $baseline[$photo['src']] = true;
    }
}
$testImage = null;
foreach (['img/desert12.jpg', 'img/s9.jpg', 'img/j2.jpg', 'img/j6.jpg'] as $candidate) {
    if (is_file($root . '/' . $candidate) && !is_link($root . '/' . $candidate) && !isset($baseline[$candidate])) {
        $testImage = $candidate;
        break;
    }
}
if ($testImage === null) {
    fwrite(STDERR, "ERROR no unused existing image is available for Gallery integration testing.\n");
    exit(1);
}
try {
    foreach ($dirs as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) throw new RuntimeException('Cannot create test content directory.');
    }
    $experience = [
        'slug' => $experienceSlug, 'category' => 'desert', 'parent' => 'desert-safari',
        'menu_label' => 'Smoke experience', 'duration_type' => 'overnight', 'order' => 900,
        'status' => 'draft', 'title' => 'Content Studio smoke-test experience',
        'description' => 'A description long enough to exercise the Content Studio experience loader.',
        'h1' => 'Content Studio smoke-test experience', 'eyebrow' => 'Test only',
        'hero_image' => 'img/desert3.jpg', 'hero_alt' => 'A local photograph showing the dunes near Jaisalmer',
        'summary' => 'A summary used only to test the data-driven tour registry.',
        'at_a_glance' => ['Duration' => 'Overnight'],
        'timeline' => [['step' => '01', 'title' => 'Temporary test', 'text' => 'Not public test data.']],
        'included' => [], 'not_included' => [], 'best_for' => 'Automated integration testing of the overlay.',
        'before_book' => 'This fixture is automatically removed after the integration test.',
        'faq' => [], 'related' => [], 'date_modified' => gmdate('Y-m-d'),
    ];
    $editorial = [
        'slug' => $editorialSlug, 'status' => 'draft', 'title' => 'Content Studio smoke-test guide',
        'description' => 'A description long enough to exercise the Content Studio editorial loader.',
        'h1' => 'Content Studio smoke-test guide', 'hero_image' => 'img/desert3.jpg',
        'hero_alt' => 'A local photograph showing the dunes near Jaisalmer',
        'toc' => [['id' => 'temporary-section', 'label' => 'Temporary section']],
        'sections' => [['id' => 'temporary-section', 'heading' => 'Temporary section', 'paragraphs' => ['Temporary test body.'], 'items' => []]],
        'sources' => [['label' => 'Official Rajasthan Tourism', 'url' => 'https://www.tourism.rajasthan.gov.in/', 'note' => 'Temporary smoke test reference.']],
        'related' => [], 'date_published' => gmdate('Y-m-d'), 'date_modified' => gmdate('Y-m-d'),
    ];
    $photo = [
        'id' => $galleryId, 'category' => 'hotel', 'status' => 'live',
        'image' => [
            'src' => $testImage,
            'alt' => 'An additional photo source used during Content Studio integration testing',
            'caption' => 'Temporary integration test image',
            'crop' => 'landscape',
        ],
    ];
    file_put_contents($paths[0], json_encode($experience, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    file_put_contents($paths[1], json_encode($editorial, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    file_put_contents($paths[2], json_encode($photo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    $experienceRegistry = require $root . '/include/experience-data.php';
    if (!isset($experienceRegistry[$experienceSlug]) || ($experienceRegistry[$experienceSlug]['status'] ?? null) !== 'draft') {
        $errors[] = 'Experience overlay did not preserve draft status.';
    }
    $editorialRegistry = require $root . '/include/editorial-guides-data.php';
    if (!isset($editorialRegistry[$editorialSlug]) || ($editorialRegistry[$editorialSlug]['status'] ?? null) !== 'draft') {
        $errors[] = 'Editorial overlay did not preserve draft status.';
    }
    $galleryRegistry = require $root . '/include/gallery-data.php';
    $matches = array_filter($galleryRegistry['hotel']['images'] ?? [], static function ($item) use ($testImage) {
        return is_array($item) && ($item['src'] ?? null) === $testImage;
    });
    if (count($matches) !== 1) $errors[] = 'Live Gallery overlay was not appended exactly once.';
} catch (Throwable $error) {
    $errors[] = 'Overlay integration test failed: ' . $error->getMessage();
} finally {
    foreach ($paths as $path) if (is_file($path) && !is_link($path)) @unlink($path);
    foreach (array_reverse($dirs) as $dir) {
        if (is_dir($dir) && count(array_diff(scandir($dir) ?: [], ['.', '..'])) === 0) @rmdir($dir);
    }
}
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS Content Studio tour, editorial and Gallery JSON overlays load safely.\n";
