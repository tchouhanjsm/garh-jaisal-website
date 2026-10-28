<?php
/**
 * Publication and navigation consistency checks for the data-driven experience model.
 *
 * This script intentionally checks source registries and generated sitemap membership;
 * rendered canonical/robots behavior remains covered by the Playwright suite.
 */
$root = dirname(__DIR__);
require_once $root . '/include/image-metadata.php';
$experiences = require $root . '/include/experience-data.php';
$nav = require $root . '/include/nav-data.php';
$siteCheckErrors = [];

$read = static function ($path) {
    $value = file_get_contents($path);
    return is_string($value) ? $value : '';
};

$publicNavUrls = [];
$collectNavUrls = static function ($items) use (&$collectNavUrls, &$publicNavUrls) {
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        if (isset($item['url']) && is_string($item['url'])) {
            $publicNavUrls[] = $item['url'];
        }
        foreach (['children', 'groups'] as $key) {
            if (!isset($item[$key]) || !is_array($item[$key])) {
                continue;
            }
            foreach ($item[$key] as $child) {
                if (!is_array($child)) {
                    continue;
                }
                if (isset($child['url']) && is_string($child['url'])) {
                    $publicNavUrls[] = $child['url'];
                }
                foreach (['children', 'groups'] as $nestedKey) {
                    if (isset($child[$nestedKey]) && is_array($child[$nestedKey])) {
                        $collectNavUrls($child[$nestedKey]);
                    }
                }
            }
        }
    }
};
$collectNavUrls($nav['main'] ?? []);
$collectNavUrls($nav['footer'] ?? []);

foreach ($experiences as $slug => $experience) {
    $status = $experience['status'] ?? 'live';
    $file = $slug . '.php';
    if (!in_array($status, ['live', 'draft', 'hidden'], true)) {
        $siteCheckErrors[] = 'Experience ' . $slug . ' has invalid status: ' . (string) $status;
        continue;
    }
    if (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        $siteCheckErrors[] = 'Experience registry contains invalid slug: ' . (string) $slug;
        continue;
    }
    if (($experience['slug'] ?? null) !== $slug) {
        $siteCheckErrors[] = 'Experience key/slug mismatch: ' . $slug;
    }
    if ($status === 'live') {
        $heroSource = trim((string) ($experience['hero_image'] ?? ''));
        $safeHeroSource = preg_match('~^img/[A-Za-z0-9._/-]+$~D', $heroSource) === 1
            && strpos($heroSource, '..') === false
            && strpos($heroSource, chr(92)) === false;
        $heroFile = $root . '/' . $heroSource;
        $heroMetadata = $safeHeroSource ? gj_local_image_metadata($heroSource) : null;
        $heroExtension = strtolower(pathinfo($heroSource, PATHINFO_EXTENSION));
        if (!$safeHeroSource || !is_file($heroFile) || is_link($heroFile)) {
            $siteCheckErrors[] = 'Live experience hero image must be a safe, local regular file: ' . $slug;
        } elseif (!is_array($heroMetadata) && $heroExtension !== 'avif') {
            $siteCheckErrors[] = 'Live experience hero image is not decodable by the PHP image metadata helper: ' . $slug;
        }
        if (strlen(trim((string) ($experience['hero_alt'] ?? ''))) < 15) {
            $siteCheckErrors[] = 'Live experience hero alt must describe the subject: ' . $slug;
        }
        $captionLength = strlen(trim((string) ($experience['hero_caption'] ?? '')));
        if ($captionLength < 20 || $captionLength > 180) {
            $siteCheckErrors[] = 'Live experience hero caption must be useful and concise: ' . $slug;
        }
    }
    if ($status === 'live' && !is_file($root . '/' . $file)) {
        $siteCheckErrors[] = 'Live experience page is missing: ' . $file;
    }
    if ($status !== 'live') {
        if (in_array($file, $publicNavUrls, true)) {
            $siteCheckErrors[] = 'Non-live experience is linked in navigation/footer: ' . $file;
        }
        $source = is_file($root . '/' . $file) ? $read($root . '/' . $file) : '';
        if ($source !== '' && strpos($source, 'include/experience-page.php') === false) {
            $siteCheckErrors[] = 'Non-live experience should use the shared renderer so the 404 behavior stays consistent: ' . $file;
        }
    }
}

$sitemap = $read($root . '/sitemap.xml');
$renderer = $read($root . '/include/experience-page.php');
if (strpos($renderer, 'gj_schema_image_object') === false || strpos($renderer, 'experienceHeroCaption') === false) {
    $siteCheckErrors[] = 'Experience renderer must use the shared image schema and caption contract.';
}
$studioSource = $read($root . '/scripts/content-studio.js');
if (strpos($studioSource, "textField(record.hero_caption, 'Hero image caption', 20, 180)") === false) {
    $siteCheckErrors[] = 'Content Studio must require reviewed hero captions before publishing tours and guides.';
}
$guideRenderer = $read($root . '/include/editorial-guide-page.php');
$schemaRequirePosition = strpos($guideRenderer, "require_once __DIR__ . '/schema.php';");
$imageObjectPosition = strpos($guideRenderer, '$guideHeroImageObject = gj_schema_image_object');
if (!is_int($schemaRequirePosition) || !is_int($imageObjectPosition) || $schemaRequirePosition > $imageObjectPosition) {
    $siteCheckErrors[] = 'Editorial renderer must load schema helpers before constructing the hero ImageObject.';
}

preg_match_all('#<loc>https?://[^/]+/([^<]*)</loc>#i', $sitemap, $matches);
$sitemapPaths = array_map('trim', $matches[1] ?? []);
foreach ($experiences as $slug => $experience) {
    $path = $slug . '.php';
    $status = $experience['status'] ?? 'live';
    $inSitemap = in_array($path, $sitemapPaths, true);
    if ($status === 'live' && !$inSitemap) {
        $siteCheckErrors[] = 'Live experience is absent from sitemap.xml: ' . $path;
    }
    if ($status !== 'live' && $inSitemap) {
        $siteCheckErrors[] = 'Non-live experience appears in sitemap.xml: ' . $path;
    }
}

if ($siteCheckErrors) {
    foreach (array_unique($siteCheckErrors) as $error) {
        fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    }
    exit(1);
}

echo 'PASS experience publication boundary: ' . count($experiences) . " experience entries checked against navigation and sitemap.\n";
