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
$dimensionAttributes = gj_image_dimension_attributes($source);
$responsiveAttributes = gj_image_dimension_attributes($source, '100vw');
$preloadDescriptor = gj_image_responsive_preload_descriptor($source, '100vw');
$assert(
    gj_image_responsive_preload_descriptor('../include/site-config.php', '100vw') === null,
    'Responsive image preloads must fail closed for traversal and non-image paths.'
);
$assert(
    gj_image_responsive_preload_descriptor($source, '') === null,
    'Responsive image preloads must reject an empty sizes hint.'
);
if (is_array($preloadDescriptor)) {
    $assert(($preloadDescriptor['href'] ?? null) === $source, 'Image preload href must use the normalized original fallback path.');
    $assert(($preloadDescriptor['fetchpriority'] ?? null) === 'high', 'Responsive hero preload must use high fetch priority.');
    $assert(($preloadDescriptor['imagesizes'] ?? null) === '100vw', 'Responsive preload must preserve the rendered sizes hint.');
    $assert(is_string($preloadDescriptor['imagesrcset'] ?? null) && $preloadDescriptor['imagesrcset'] !== '', 'Responsive preload must include verified width candidates.');
    $srcsetMatch = [];
    $hasRenderedSrcset = preg_match('/\\bsrcset="([^"]+)"/', $responsiveAttributes, $srcsetMatch) === 1;
    $assert($hasRenderedSrcset, 'Rendered responsive image attributes must expose the candidate list used by its preload.');
    if ($hasRenderedSrcset) {
        $normalizeSrcset = static function ($srcset) {
            $normalized = [];
            foreach (preg_split('/\\s*,\\s*/', trim($srcset)) as $candidate) {
                $parts = preg_split('/\\s+/', trim($candidate));
                $normalized[] = ltrim($parts[0], '/') . ' ' . ($parts[1] ?? '');
            }
            return implode(', ', $normalized);
        };
        $assert(
            $normalizeSrcset($srcsetMatch[1]) === $preloadDescriptor['imagesrcset'],
            'Responsive preload and rendered srcset must use the same verified candidates and widths.'
        );
    }
}
$assert(
    is_string($dimensionAttributes) && preg_match('/width="[1-9][0-9]*" height="[1-9][0-9]*"/', $dimensionAttributes) === 1,
    'Canonical image helper must emit positive intrinsic dimensions for a trusted local asset.'
);
$assert(
    gj_image_dimension_attributes('../include/site-config.php') === '',
    'Canonical image helper must fail closed for traversal and non-image paths.'
);

foreach (['img/rest5.jpg', 'img/jcity.jpg'] as $nativeHeroSource) {
    $nativeMetadata = gj_local_image_metadata($nativeHeroSource);
    if (
        is_array($nativeMetadata)
        && $nativeMetadata['width'] > 800
        && $nativeMetadata['width'] <= 1200
        && (800 / $nativeMetadata['width']) < 0.9
    ) {
        $nativeCandidates = gj_image_responsive_candidates($nativeHeroSource);
        $largestCandidate = $nativeCandidates ? $nativeCandidates[count($nativeCandidates) - 1] : null;
        $assert(
            is_array($largestCandidate)
            && ($largestCandidate['path'] ?? null) === $nativeHeroSource
            && ($largestCandidate['width'] ?? null) === $nativeMetadata['width'],
            'A 1200px-or-smaller hero must retain its verified native source when the largest derivative leaves under 90% coverage: ' . $nativeHeroSource
        );
        $nativePreload = gj_image_responsive_preload_descriptor($nativeHeroSource, '100vw');
        $assert(
            is_array($nativePreload)
            && strpos($nativePreload['imagesrcset'], $nativeHeroSource . ' ' . $nativeMetadata['width'] . 'w') !== false,
            'Hero preload must use the same native-width candidate as the rendered srcset: ' . $nativeHeroSource
        );
    }
}
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

$missingPathWarnings = [];
set_error_handler(static function ($severity, $message) use (&$missingPathWarnings) {
    $missingPathWarnings[] = $message;
    return true;
});
$missingMetadata = gj_local_image_metadata('img/responsive/v2/does-not-exist-480.webp');
restore_error_handler();
$assert($missingMetadata === null, 'A missing optional responsive variant must fail closed.');
$assert($missingPathWarnings === [], 'A missing optional responsive variant must not emit filesystem warnings.');

$imgRoot = $root . '/img';
$linkName = '.image-metadata-contract-' . getmypid();
$linkPath = $imgRoot . '/' . $linkName;
$assert(
    !file_exists($linkPath) && !is_link($linkPath),
    'The nested-image symlink test path must be unique and unused.'
);
if (function_exists('symlink') && @symlink($imgRoot . '/v2', $linkPath)) {
    $assert(
        gj_local_image_metadata('img/' . $linkName . '/facade.jpg') === null,
        'An otherwise valid image reached through a symlinked parent directory must be rejected.'
    );
    @unlink($linkPath);
} else {
    $assert(false, 'The runtime must support creating a temporary symlink for the path-safety contract.');
}

$helperSource = file_get_contents($root . '/include/image-metadata.php');
$assert(is_string($helperSource), 'Image metadata helper source must be readable for its source-safety contract.');
if (is_string($helperSource)) {
    $assert(strpos($helperSource, 'realpath') !== false, 'Image helper must resolve canonical filesystem paths.');
    $assert(strpos($helperSource, 'is_link') !== false, 'Image helper must reject symlinked image files.');
    $assert(strpos($helperSource, '$imageRoot . DIRECTORY_SEPARATOR') !== false, 'Resolved images must remain beneath the real img directory.');
    $assert(strpos($helperSource, 'function gj_image_dimension_attributes') !== false, 'The canonical responsive attribute emitter must live in the metadata module.');
    $assert(strpos($helperSource, 'function gj_image_responsive_candidates') !== false, 'The shared verified candidate resolver must serve responsive markup and preload descriptors.');
    $assert(strpos($helperSource, 'function gj_image_responsive_preload_descriptor') !== false, 'Responsive image preload descriptors must live beside the canonical image metadata helper.');
    $assert(strpos($helperSource, 'gj_local_image_metadata($candidateSource)') !== false, 'Responsive candidates must use path-safe metadata inspection.');
}
$headerSource = file_get_contents($root . '/include/header-v2.php');
$assert(
    is_string($headerSource) && strpos($headerSource, "require_once __DIR__ . '/image-metadata.php';") !== false,
    'The shared header must load the canonical image metadata module.'
);
$assert(
    is_string($headerSource) && strpos($headerSource, 'function gj_image_dimension_attributes') === false,
    'The shared header must not keep a second implementation of responsive image delivery.'
);

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "PASS safe local image metadata: dimensions, MIME, bytes, path rejection and symlink safeguards.\n";
