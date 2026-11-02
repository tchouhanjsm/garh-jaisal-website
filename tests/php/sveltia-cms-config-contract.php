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
    'admin/media-library.php',
    'admin/config.yml',
    'admin/.htaccess',
    'docs/SVELTIA-CMS-OWNER-GUIDE.md',
    'include/gallery-data.php',
    'include/media-library-audit.php',
    'include/site-media-slots.php',
    'content/page-media/home.json',
    'content/page-media/rooms.json',
    'content/page-media/dining.json',
    'content/page-media/experiences.json',
    'tests/php/site-media-slots-contract.php',
    'tests/php/media-library-audit.php',
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
$mediaLibrary = $read('admin/media-library.php');
$mediaAudit = $read('include/media-library-audit.php');
$siteMediaLoader = $read('include/site-media-slots.php');
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
        'auth_methods: [token]',
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
$galleryStart = strpos($config, "  - name: gallery");
$nextCollection = $galleryStart === false ? false : strpos($config, "\n  - name: page_media_slots", $galleryStart + 1);
$galleryConfig = $galleryStart === false ? '' : substr($config, $galleryStart, $nextCollection === false ? null : $nextCollection - $galleryStart);
$galleryField = static function ($name) use ($galleryConfig) {
    $marker = "      - name: " . $name . "\n";
    $start = strpos($galleryConfig, $marker);
    if ($start === false) return '';
    $next = strpos($galleryConfig, "\n      - name:", $start + strlen($marker));
    return substr($galleryConfig, $start, $next === false ? null : $next - $start);
};
$reviewStatusField = $galleryField('review_status');
$sourceEvidenceField = $galleryField('sourceEvidence');
$visualReviewField = $galleryField('visualReview');
$technicalValidationField = $galleryField('technicalValidation');
if ($reviewStatusField === '' || strpos($reviewStatusField, 'widget: select') === false || strpos($reviewStatusField, 'default: draft') === false || strpos($reviewStatusField, 'value: approved') === false || strpos($reviewStatusField, 'widget: hidden') !== false) {
    $errors[] = 'Gallery editorial review status must be visible, selectable and default to Draft.';
}
foreach (['name: creator', 'name: sourceUrl', 'name: licenseName', 'name: evidenceReference', 'name: verifiedBy', 'name: verifiedAt'] as $marker) {
    if ($sourceEvidenceField === '' || strpos($sourceEvidenceField, $marker) === false) $errors[] = 'Gallery source/rights evidence is missing field: ' . $marker;
}
if (strpos($sourceEvidenceField, 'default: unverified') === false || strpos($sourceEvidenceField, 'value: owner-confirmed') === false || strpos($sourceEvidenceField, 'value: licensed') === false) {
    $errors[] = 'Gallery rights evidence must default to Unverified and distinguish owner-confirmed from licensed evidence.';
}
foreach (['name: evidence', 'name: reviewer', 'name: reviewedAt'] as $marker) {
    if ($visualReviewField === '' || strpos($visualReviewField, $marker) === false) $errors[] = 'Gallery visual review is missing field: ' . $marker;
}
if (strpos($visualReviewField, 'default: not_assessed') === false || strpos($visualReviewField, 'value: matched') === false || strpos($visualReviewField, 'value: mismatch') === false) {
    $errors[] = 'Gallery visual review must default to Not assessed and distinguish match from mismatch.';
}
if ($technicalValidationField === '' || strpos($technicalValidationField, 'default: pending') === false || strpos($technicalValidationField, 'value: verified') === false || strpos($technicalValidationField, 'name: evidenceReference') === false) {
    $errors[] = 'Gallery technical validation must default to Pending and link evidence for verified checks.';
}
if (strpos($guide, 'Rights evidence status: Unverified') === false || strpos($guide, 'do not backfill the 36 existing Gallery records') === false || strpos($guide, 'not cryptographic signatures') === false) {
    $errors[] = 'Owner guide must explain fail-closed defaults, existing-record boundaries and reviewer identity limits.';
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
if (strpos($mediaLibrary, 'Media library intelligence') === false
    || stripos($mediaLibrary, 'never delete from this report alone') === false) {
    $errors[] = 'Read-only media library dashboard must explain usage limitations and must not expose mutation actions.';
}
if (strpos($siteMediaLoader, 'getimagesize') === false
    || strpos($siteMediaLoader, 'realpath') === false
    || strpos($siteMediaLoader, 'is_link') === false
    || strpos($siteMediaLoader, 'gj_site_media_slot') === false) {
    $errors[] = 'Page media slot loader must validate decodable images beneath img/ and retain safe fallbacks.';
}
if (strpos($config, 'name: page_media_slots') === false
    || strpos($config, 'file: content/page-media/home.json') === false
    || strpos($config, 'file: content/page-media/rooms.json') === false
    || strpos($config, 'file: content/page-media/dining.json') === false
    || strpos($config, 'file: content/page-media/experiences.json') === false) {
    $errors[] = 'Sveltia must expose the four fixed Page image slot files.';
}
if (strpos($read('content/.htaccess'), 'Require all denied') === false) {
    $errors[] = 'CMS content JSON must remain blocked from direct public access.';
}
if (strpos($mediaAudit, 'include/site-config.local.php') === false
    || strpos($mediaAudit, 'large_file_assets') === false
    || strpos($mediaAudit, 'oversized_dimension_assets') === false
    || strpos($mediaAudit, 'large_dimension_threshold_px') === false
    || strpos($mediaLibrary, 'stat-storage') === false
    || strpos($mediaLibrary, 'optimization-review') === false
    || strpos($mediaLibrary, 'commits/main/') === false
    || strpos($mediaLibrary, 'View Git history') === false
    || strpos($html, 'Owner tools') === false
    || strpos($mediaAudit, 'no-literal-reference') === false
    || strpos($mediaAudit, 'sha256') === false
    || strpos($mediaAudit, 'source_scan_complete') === false
    || strpos($mediaAudit, "\$assets[\$index]['gallery_entries'] = \$assets[\$sourceIndex]['gallery_entries'];") === false) {
    $errors[] = 'Media audit must exclude private local configuration and provide conservative usage/duplicate evidence.';
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
