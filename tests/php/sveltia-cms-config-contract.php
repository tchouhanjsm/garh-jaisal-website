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
    'include/page-media-approval.php',
    'include/admin-auth.php',
    'scripts/sign-page-media-approval.php',
    'tests/php/admin-auth-contract.php',
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
$approvalHelper = $read('include/page-media-approval.php');
$adminAuth = $read('include/admin-auth.php');
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
if (strpos($mediaLibrary, 'Media library intelligence') === false
    || stripos($mediaLibrary, 'never delete from this report alone') === false) {
    $errors[] = 'Read-only media library dashboard must explain usage limitations and must not expose mutation actions.';
}
if (strpos($mediaLibrary, 'gj_require_media_admin_access') === false
    || strpos($mediaLibrary, 'gj_require_media_admin_access') > strpos($mediaLibrary, 'gj_media_library_audit')) {
    $errors[] = 'Media Library authorization must run before the inventory audit.';
}
if (strpos($adminAuth, 'password_verify') === false
    || strpos($adminAuth, 'WWW-Authenticate: Basic') === false
    || strpos($adminAuth, 'Vary: Authorization') === false
    || strpos($adminAuth, 'media_admin_auth') === false) {
    $errors[] = 'Owner dashboard must use server-configured HTTPS Basic authentication.';
}
if (strpos($approvalHelper, 'hash_hmac') === false
    || strpos($approvalHelper, 'asset_sha256') === false
    || strpos($siteMediaLoader, 'gj_page_media_approval_is_valid') === false
    || strpos($siteMediaLoader, "['approved'] !== true") === false) {
    $errors[] = 'Page image slots must require signed, asset-bound approval evidence.';
}
if (strpos($config, 'name: approval') === false
    || strpos($config, 'name: source_credit') === false
    || strpos($config, 'name: rights_basis') === false
    || strpos($config, 'name: visual_match_review') === false
    || strpos($config, 'name: alt_text_review') === false) {
    $errors[] = 'Sveltia Page image slots must expose the review evidence fields.';
}

$approvalSchemaCount = preg_match_all('/^ {12}- name: approval$/m', $config);
if ($approvalSchemaCount !== 33) {
    $errors[] = 'Every one of the 33 fixed page image slots must expose exactly one approval object; found ' . (int) $approvalSchemaCount . '.';
}

$pageMediaAreas = ['home' => 13, 'rooms' => 8, 'dining' => 1, 'experiences' => 11];
$pageMediaCount = 0;
foreach ($pageMediaAreas as $area => $expectedCount) {
    $records = json_decode($read('content/page-media/' . $area . '.json'), true);
    if (!is_array($records) || count($records) !== $expectedCount) {
        $errors[] = 'Expected ' . $expectedCount . ' fixed page-media records in ' . $area . '.';
        continue;
    }
    foreach ($records as $slotId => $record) {
        $pageMediaCount++;
        $approval = is_array($record['approval'] ?? null) ? $record['approval'] : [];
        if (($approval['status'] ?? null) !== 'pending'
            || ($approval['source_credit'] ?? null) !== ''
            || ($approval['rights_basis'] ?? null) !== ''
            || ($approval['visual_match_review'] ?? null) !== ''
            || ($approval['alt_text_review'] ?? null) !== ''
            || ($approval['technical_validation'] ?? null) !== ''
            || ($approval['asset_sha256'] ?? null) !== ''
            || ($approval['approved_by'] ?? null) !== ''
            || ($approval['approved_at'] ?? null) !== ''
            || ($approval['signature'] ?? null) !== '') {
            $errors[] = 'Existing page-media slot must be explicitly pending with no fabricated approval evidence: ' . $area . '/' . $slotId;
        }
    }
}
if ($pageMediaCount !== 33) {
    $errors[] = 'Expected 33 total fixed page-media records; found ' . $pageMediaCount . '.';
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
    || strpos($packageBuilder, '"include/admin-auth.php"') === false
    || strpos($packageBuilder, '"include/page-media-approval.php"') === false
) {
    $errors[] = 'The cPanel package builder must include the CMS runtime directory and entry points.';
}
if (strpos($guide, 'local storage') === false || strpos($guide, '15 MiB') === false || strpos($guide, 'cms-media') === false
    || strpos($guide, 'GJ_MEDIA_APPROVAL_HMAC_KEY') === false
    || strpos($guide, 'media_admin_auth') === false) {
    $errors[] = 'Owner guide must explain token storage, upload limits and the CMS branch workflow.';
}

if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS Sveltia CMS configuration, branch safety, image optimisation and cPanel inclusion contract.\n";
