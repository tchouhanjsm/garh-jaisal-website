<?php
/**
 * Contract for safe local image metadata inspection.
 * Run in the PHP 7.4/8.2 Site Checks matrix; no web server is required.
 */
$root = dirname(__DIR__, 2);
require_once $root . '/include/image-metadata.php';

$failures = [];
$assert = static function ($condition, $message) use (&$failures) {
    if (!$condition) {
        $failures[] = $message;
    }
};

$source = 'img/v2/facade.jpg';
$metadata = gj_local_image_metadata($source);
$assert(is_array($metadata), 'A known local JPEG must return metadata.');
if (is_array($metadata)) {
    $assert(($metadata['path'] ?? null) === $source, 'Metadata should retain the normalized local path.');
    $assert(($metadata['url_path'] ?? null) === '/' . $source, 'Metadata URL path should be site-root-relative.');
    $assert(is_int($metadata['width'] ?? null) && $metadata['width'] > 0, 'Image width must be a positive integer.');
    $assert(is_int($metadata['height'] ?? null) && $metadata['height'] > 0, 'Image height must be a positive integer.');
    $assert(($metadata['mime'] ?? null) === 'image/jpeg', 'JPEG metadata should expose the decoded MIME type.');
    $assert(is_int($metadata['bytes'] ?? null) && $metadata['bytes'] > 0, 'Image byte count must be a positive integer.');
}

$rejected = [
    '',
    '/etc/passwd',
    '../include/site-config.php',
    'img/../include/site-config.php',
    'img\\v2\\facade.jpg',
    'https://example.com/remote.jpg',
    '//example.com/remote.jpg',
    'img/metadata-does-not-exist.jpg',
    'img/v2/facade.jpg?download=1',
    'img/v2/facade.jpg#fragment',
];
foreach ($rejected as $candidate) {
    $assert(
        gj_local_image_metadata($candidate) === null,
        'Unsafe or unsupported image input must return null: ' . $candidate
    );
}

$helperSource = file_get_contents($root . '/include/image-metadata.php');
$assert(is_string($helperSource), 'Image metadata helper source must be readable for its source-safety contract.');
if (is_string($helperSource)) {
    $assert(strpos($helperSource, 'realpath') !== false, 'Image helper must resolve canonical filesystem paths.');
    $assert(strpos($helperSource, 'is_link') !== false, 'Image helper must reject symlinked image files.');
    $assert(strpos($helperSource, '$imageRoot . DIRECTORY_SEPARATOR') !== false, 'Resolved images must remain beneath the real img directory.');
}

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "PASS safe local image metadata: dimensions, MIME, bytes, path rejection and symlink safeguards.\n";
