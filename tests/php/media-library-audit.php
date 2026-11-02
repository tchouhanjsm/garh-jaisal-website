<?php
/**
 * Verify the read-only media inventory, literal reference detection and safety limits.
 *
 * Run with: php tests/php/media-library-audit.php
 */
require_once dirname(__DIR__, 2) . '/include/media-library-audit.php';

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gj-media-audit-' . uniqid('', true);
$errors = [];

$makeDirectory = static function ($path) {
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Unable to create test directory.');
    }
};
$write = static function ($relative, $contents) use ($root, $makeDirectory) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $makeDirectory(dirname($path));
    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('Unable to write test fixture: ' . $relative);
    }
};
$removeTree = static function ($path) use (&$removeTree) {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (!is_dir($path)) {
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $removeTree($path . DIRECTORY_SEPARATOR . $entry);
    }
    @rmdir($path);
};

try {
    $makeDirectory($root . '/img/gallery');
    $makeDirectory($root . '/img/responsive/gallery');
    $write('img/gallery/room.webp', 'same-image-bytes');
    $write('img/gallery/room-copy.webp', 'same-image-bytes');
    $write('img/shared.jpg', 'shared-image-bytes');
    $write('img/not-used.png', str_repeat('N', (512 * 1024) + 1));
    $largeDimensionGif = base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
    if (!is_string($largeDimensionGif) || strlen($largeDimensionGif) < 10) {
        throw new RuntimeException('Unable to prepare the large-dimension GIF fixture.');
    }
    $largeDimensionGif = substr_replace($largeDimensionGif, pack('v', 3000), 6, 2);
    $largeDimensionGif = substr_replace($largeDimensionGif, pack('v', 1000), 8, 2);
    $write('img/gallery/panorama.gif', $largeDimensionGif);
    $write('img/responsive/gallery/room-480.webp', 'responsive-derivative-bytes');

    $write('room.php', '<?php $hero = "/img/gallery/room.webp";');
    $write('css/site.css', '.hero { background-image: url("../img/shared.jpg"); }');
    $write('content/gallery/room-entry.json', json_encode([
        'id' => 'room-entry',
        'category' => 'rooms',
        'status' => 'live',
        'image' => ['src' => 'img/gallery/room.webp', 'alt' => 'Room interior', 'crop' => 'standard'],
    ]));
    $write('content/gallery/missing-entry.json', json_encode([
        'id' => 'missing-entry',
        'category' => 'hotel',
        'status' => 'draft',
        'image' => ['src' => 'img/missing.webp', 'alt' => 'Missing image', 'crop' => 'standard'],
    ]));
    $write('include/site-config.local.php', '<?php $private = "img/should-never-be-scanned.jpg";');
    $write('docs/old-guide.md', 'img/not-used.png');
    $write('tests/fixture.php', 'img/not-used.png');

    // A source larger than the per-file scan budget must make coverage partial.
    $largeSource = fopen($root . '/large-source.php', 'wb');
    if (!is_resource($largeSource)) {
        throw new RuntimeException('Unable to create oversized source fixture.');
    }
    fwrite($largeSource, '<?php // oversized source fixture');
    ftruncate($largeSource, (2 * 1024 * 1024) + 1);
    fclose($largeSource);

    $report = gj_media_library_audit($root);
    $byPath = [];
    foreach ($report['assets'] as $asset) {
        $byPath[$asset['path']] = $asset;
    }

    if (($report['summary']['total_assets'] ?? 0) !== 6) {
        $errors[] = 'Expected six image assets in inventory.';
    }
    $expectedTotalBytes = array_sum(array_column($report['assets'], 'bytes'));
    if (($report['summary']['total_bytes'] ?? -1) !== $expectedTotalBytes) {
        $errors[] = 'Total storage footprint must equal the sum of inventoried file sizes.';
    }
    if (($report['summary']['large_file_assets'] ?? 0) !== 1
        || !in_array('large-file', $byPath['img/not-used.png']['optimization_flags'] ?? [], true)) {
        $errors[] = 'Files larger than the review threshold must receive a large-file signal.';
    }
    if (($report['summary']['oversized_dimension_assets'] ?? 0) !== 1
        || !in_array('large-dimensions', $byPath['img/gallery/panorama.gif']['optimization_flags'] ?? [], true)) {
        $errors[] = 'Images beyond the dimension review threshold must receive a large-dimensions signal.';
    }
    if (($report['summary']['optimization_review_assets'] ?? 0) !== 2) {
        $errors[] = 'Optimisation review count must count each flagged image once.';
    }
    if (($byPath['img/gallery/room.webp']['usage_count'] ?? 0) !== 2) {
        $errors[] = 'Expected the Room image to be found in PHP and its Gallery record.';
    }
    if (($byPath['img/shared.jpg']['usage_count'] ?? 0) !== 1) {
        $errors[] = 'Expected CSS ../img references to be normalised.';
    }
    if (($byPath['img/not-used.png']['usage_count'] ?? 0) !== 0) {
        $errors[] = 'Documentation and test fixtures must not count as production usages.';
    }
    if (($byPath['img/responsive/gallery/room-480.webp']['usage_state'] ?? '') !== 'responsive-derivative') {
        $errors[] = 'Responsive derivatives must be identified separately from deletion candidates.';
    }
    if (($byPath['img/responsive/gallery/room-480.webp']['gallery_entries'][0]['category'] ?? '') !== 'rooms') {
        $errors[] = 'Responsive derivatives must inherit their original Gallery category for accurate filtering.';
    }
    if (($report['summary']['source_scan_complete'] ?? true) !== false
        || ($report['summary']['source_files_skipped'] ?? 0) !== 1) {
        $errors[] = 'A source file skipped for exceeding the per-file budget must mark usage coverage partial.';
    }
    if (!count(array_filter($report['warnings'], static function ($warning) {
        return strpos($warning, 'per-file scan limit') !== false;
    }))) {
        $errors[] = 'Partial source coverage must be visible in the warning list.';
    }
    if (($byPath['img/gallery/room.webp']['is_duplicate'] ?? false) !== true) {
        $errors[] = 'Exact duplicate file content should be detected.';
    }
    if (count($report['duplicate_groups'] ?? []) !== 1) {
        $errors[] = 'Expected one SHA-256 duplicate group.';
    }
    if (count($report['missing_gallery_references'] ?? []) !== 1) {
        $errors[] = 'Missing Gallery media references should be surfaced.';
    }

    $privateWasRead = false;
    foreach ($report['assets'] as $asset) {
        foreach ($asset['usage_files'] as $sourceFile) {
            if ($sourceFile === 'include/site-config.local.php') {
                $privateWasRead = true;
            }
        }
    }
    if ($privateWasRead) {
        $errors[] = 'Private local configuration must never be scanned.';
    }

    if ($errors) {
        foreach ($errors as $error) {
            fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
        }
        exit(1);
    }

    echo "PASS read-only media inventory, usage mapping, duplicate detection and private-config exclusions.\n";
} finally {
    $removeTree($root);
}
