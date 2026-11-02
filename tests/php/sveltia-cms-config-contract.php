<?php
/**
 * Verify the hosted Sveltia CMS configuration and cPanel runtime inclusion.
 *
 * Run with: php tests/php/sveltia-cms-config-contract.php
 */
$root = dirname(__DIR__, 2);
$errors = [];

$requiredFiles = [
    'admin/index.html',
    'admin/config.yml',
    'admin/.htaccess',
    'docs/SVELTIA-CMS-OWNER-GUIDE.md',
    'include/gallery-data.php',
    'scripts/build_deploy_package.py',
];
foreach ($requiredFiles as $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path) || is_link($path)) {
        $errors[] = 'Required regular file is missing or unsafe: ' . $relative;
    }
}

$read = static function ($relative) use ($root, &$errors) {
    $path = $root . '/' . $relative;
    if (!is_file($path) || is_link($path)) return '';
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        $errors[] = 'Unable to read ' . $relative;
        return '';
    }
    return $contents;
};

$html = $read('admin/index.html');
$config = $read('admin/config.yml');
$headers = $read('admin/.htaccess');
$galleryLoader = $read('include/gallery-data.php');
$packageBuilder = $read('scripts/build_deploy_package.py');
$guide = $read('docs/SVELTIA-CMS-OWNER-GUIDE.md');

foreach ([
    'admin/index.html' => [
        'name="robots" content="noindex, nofollow"',
        '@sveltia/cms@0.256.1/dist/sveltia-cms.js',
    ],
    'admin/config.yml' => [
        'name: github',
        'repo: tchouhanjsm/garh-jaisal-website',
        'branch: cms-media',
        'media_folder: /img/gallery',
        'public_folder: img/gallery',
        'max_file_size: 15728640',
        'format: webp',
        'quality: 84',
        'width: 1600',
        'height: 1600',
        'name: order',
        'widget: number',
        'value_type: int',
        'default: 100',
        'min: 1',
        'widget: hidden',
        'name: review_status',
        'name: caption',
        'folder: /content/gallery',
        'format: json',
        'identifier_field: id',
        'default: draft',
        'value: hidden',
        'widget: image',
        'name: alt',
        'value: hotel',
        'value: rooms',
        'value: desert',
        'value: restaurant',
    ],
    'admin/.htaccess' => [
        'Options -Indexes',
        'X-Robots-Tag "noindex, nofollow"',
        'Cache-Control "no-store',
    ],
] as $relative => $markers) {
    $content = $relative === 'admin/index.html' ? $html : ($relative === 'admin/config.yml' ? $config : $headers);
    foreach ($markers as $marker) {
        if (strpos($content, $marker) === false) {
            $errors[] = $relative . ': required marker is missing: ' . $marker;
        }
    }
}

$expectedMediaConfig = "media_libraries:\n  default:\n    config:\n      max_file_size: 15728640";
if (strpos($config, $expectedMediaConfig) === false) {
    $errors[] = 'Sveltia internal media settings must be nested under media_libraries.default.config.';
}
if (preg_match('/media_libraries:\s*\n\s+all:/', $config)) {
    $errors[] = 'Unsupported media_libraries.all configuration must not be used.';
}
if (strpos($config, 'filename_template:') !== false) {
    $errors[] = 'Do not rely on an undocumented filename_template option.';
}
if (strpos($config, 'slugify_filename: true') === false
    || strpos($config, "transformations:\n        raster_image:") === false) {
    $errors[] = 'Sveltia media upload configuration must include filename slugification and raster transformation.';
}

if (preg_match('/(?:ghp_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,}|client_secret\s*:)/i', $config)) {
    $errors[] = 'CMS configuration appears to contain a credential or client secret.';
}
if (preg_match('/branch:\s*main\b/i', $config)) {
    $errors[] = 'Sveltia must not write directly to main.';
}
if (strpos($config, 'delete: false') === false) {
    $errors[] = 'Gallery entry deletion must remain disabled in the CMS collection.';
}
if (strpos($galleryLoader, "'/content/gallery") === false || strpos($galleryLoader, "'order'") === false) {
    // The PHP loader resolves the JSON overlay relative to the repository root.
    $errors[] = 'PHP gallery loader no longer references the managed content/gallery overlay.';
}
if (
    strpos($packageBuilder, '"admin"') === false
    || strpos($packageBuilder, '"admin/config.yml"') === false
    || strpos($packageBuilder, '"admin/index.html"') === false
) {
    $errors[] = 'The cPanel package builder must include the CMS runtime directory and entry points.';
}
if (strpos($guide, 'local storage') === false || strpos($guide, '15 MiB') === false || strpos($guide, 'cms-media') === false) {
    $errors[] = 'Owner guide must explain token storage, upload limits and the CMS branch workflow.';
}

if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS Sveltia CMS configuration, branch safety, image optimisation and cPanel inclusion contract.\n";
