<?php
/**
 * SEO release-readiness contract for the merged editorial publication pipeline.
 * Keep this check compatible with PHP 7.4 and runnable without a web server.
 */
$root = dirname(__DIR__, 2);
$failures = [];
$assert = static function ($condition, $message) use (&$failures) {
    if (!$condition) {
        $failures[] = $message;
    }
};

$config = require $root . '/include/site-config.php';
$guides = require $root . '/include/editorial-guides-data.php';
$topics = require $root . '/include/editorial-topic-taxonomy.php';
$siteUrl = rtrim((string) ($config['site_url'] ?? ''), '/');
$siteParts = parse_url($siteUrl);
$assert(is_array($siteParts) && strtolower((string) ($siteParts['scheme'] ?? '')) === 'https', 'Canonical site URL must be HTTPS.');
$assert(is_array($siteParts) && !empty($siteParts['host']), 'Canonical site URL must provide a host.');
$assert(is_array($guides), 'Editorial registry must return an array.');
$assert(is_array($topics) && count($topics) > 0, 'Editorial taxonomy must return a non-empty topic map.');

$sitemapPath = $root . '/sitemap.xml';
$robotsPath = $root . '/robots.txt';
$templatePath = $root . '/include/editorial-guide-page.php';
$headPath = $root . '/include/page-head.php';
$sitemap = is_file($sitemapPath) && !is_link($sitemapPath) ? file_get_contents($sitemapPath) : false;
$robots = is_file($robotsPath) && !is_link($robotsPath) ? file_get_contents($robotsPath) : false;
$template = is_file($templatePath) && !is_link($templatePath) ? file_get_contents($templatePath) : false;
$head = is_file($headPath) && !is_link($headPath) ? file_get_contents($headPath) : false;
$assert(is_string($sitemap) && trim($sitemap) !== '', 'Committed sitemap.xml must exist and be non-empty.');
$assert(is_string($robots) && trim($robots) !== '', 'robots.txt must exist and be non-empty.');
$assert(is_string($template), 'Shared editorial guide renderer is missing.');
$assert(is_string($head), 'Shared page-head renderer is missing.');
if (!is_array($guides) || !is_array($topics) || !is_string($sitemap) || !is_string($robots) || !is_string($template) || !is_string($head) || !is_array($siteParts)) {
    fwrite(STDERR, "FAIL editorial SEO release readiness: a required source file or registry is unavailable.\n");
    exit(1);
}

$assert(strpos($head, 'link rel="canonical"') !== false, 'Shared head must emit canonical URLs.');
foreach ([
    'name="description"',
    'name="robots"',
    'property="og:type"',
    'property="og:title"',
    'property="og:description"',
    'property="og:url"',
    'property="og:image"',
    'name="twitter:card"',
    'name="twitter:title"',
    'name="twitter:description"',
    'name="twitter:image"',
] as $marker) {
    $assert(strpos($head, $marker) !== false, 'Shared head is missing SEO metadata marker: ' . $marker);
}
$assert(strpos($template, "'@type' => 'BlogPosting'") !== false, 'Editorial renderer must emit BlogPosting JSON-LD.');
$assert(strpos($template, 'gj_schema_breadcrumb_list') !== false, 'Editorial renderer must emit breadcrumb JSON-LD.');
$assert(strpos($template, "'datePublished' =>") !== false && strpos($template, "'dateModified' =>") !== false, 'BlogPosting must expose publication and modification dates.');
$assert(strpos($template, "'robots' => ") !== false && strpos($template, "'index,follow'") !== false && strpos($template, "'noindex,follow'") !== false, 'Editorial visibility must control indexability.');

$expectedSitemap = $siteUrl . '/sitemap.xml';
preg_match_all('/^\s*Sitemap:\s*(\S+)\s*$/im', $robots, $sitemapDirectives);
$assert(count($sitemapDirectives[1] ?? []) === 1, 'robots.txt must declare exactly one Sitemap directive.');
if (count($sitemapDirectives[1] ?? []) === 1) {
    $assert(trim($sitemapDirectives[1][0]) === $expectedSitemap, 'robots.txt Sitemap directive must match the configured canonical origin.');
}
$assert(!preg_match('/^\s*Disallow:\s*\/\s*(?:#.*)?$/im', $robots), 'robots.txt must not block the whole public site.');

$validStatuses = ['live', 'draft', 'hidden'];
$liveRoutes = [];
$topicCounts = array_fill_keys(array_keys($topics), 0);
$registrySlugs = [];
$weakLabels = ['here', 'click here', 'read more', 'learn more', 'more', 'details', 'click to read'];
foreach ($topics as $topicId => $topicLabel) {
    $assert(is_string($topicId) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $topicId), 'Topic IDs must be URL-safe.');
    $assert(is_string($topicLabel) && trim($topicLabel) !== '', 'Every topic needs a visible label: ' . (string) $topicId);
}

foreach ($guides as $slug => $guide) {
    $safeSlug = is_string($slug) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug);
    $assert($safeSlug, 'Editorial registry contains an invalid slug: ' . (string) $slug);
    $assert(is_array($guide), 'Editorial registry entry must be an array: ' . (string) $slug);
    if (!$safeSlug || !is_array($guide)) {
        continue;
    }
    $assert(($guide['slug'] ?? null) === $slug, 'Registry key/slug mismatch: ' . $slug);
    $status = $guide['status'] ?? 'live';
    $assert(in_array($status, $validStatuses, true), 'Invalid editorial status: ' . $slug);
    $declaredTopic = $guide['topic'] ?? 'trip-planning';
    $assert(is_string($declaredTopic) && isset($topics[$declaredTopic]), 'Editorial record has an unknown topic: ' . $slug . ' → ' . (string) $declaredTopic);
    if ($status === 'live') {
        $route = '/' . $slug . '.php';
        $liveRoutes[$route] = true;
        if (isset($topicCounts[$declaredTopic])) {
            $topicCounts[$declaredTopic]++;
        }
        $wrapper = $root . '/' . $slug . '.php';
        $assert(is_file($wrapper) && !is_link($wrapper), 'Live editorial route must be a regular PHP file: ' . $route);
    }

    $relatedDestinations = [];
    foreach (($guide['related'] ?? []) as $relatedIndex => $related) {
        $assert(is_array($related), 'Related guide entry must be an array: ' . $slug . ' #' . $relatedIndex);
        if (!is_array($related)) {
            continue;
        }
        $href = trim((string) ($related['href'] ?? ''));
        $label = trim((string) ($related['label'] ?? ''));
        $assert($href !== '', 'Related guide needs a destination: ' . $slug . ' #' . $relatedIndex);
        $assert($label !== '', 'Related guide needs visible link text: ' . $slug . ' #' . $relatedIndex);
        if (in_array(strtolower(preg_replace('/\s+/', ' ', $label)), $weakLabels, true)) {
            $assert(false, 'Related guide uses generic anchor text: ' . $slug . ' → ' . $label);
        }
        if ($href === '' || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//|@)#i', $href) || $href[0] === '#') {
            continue;
        }
        $path = parse_url($href, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || substr(strtolower($path), -4) !== '.php') {
            continue;
        }
        $assert(strpos($path, '..') === false && strpos($path, chr(92)) === false, 'Related guide has an unsafe local route: ' . $slug . ' → ' . $href);
        $destination = basename($path, '.php');
        $routeKey = strtolower($path);
        $assert($destination !== $slug, 'Related guide must not point to itself: ' . $slug);
        $assert(!isset($relatedDestinations[$routeKey]), 'Duplicate related-guide destination: ' . $slug . ' → ' . $path);
        $relatedDestinations[$routeKey] = true;
        $localFile = $root . '/' . ltrim(rawurldecode($path), '/');
        $assert(is_file($localFile) && !is_link($localFile), 'Related guide route must resolve locally: ' . $slug . ' → ' . $href);
        if (isset($guides[$destination])) {
            $targetStatus = is_array($guides[$destination]) ? ($guides[$destination]['status'] ?? 'live') : 'invalid';
            $assert($targetStatus === 'live' || $status !== 'live', 'Live guide links to a non-live editorial record: ' . $slug . ' → ' . $destination);
        }
    }
}
foreach ($topicCounts as $topicId => $count) {
    $assert($count > 0, 'Every taxonomy topic needs at least one live guide: ' . $topicId);
}

$sitemapRoutes = [];
$sitemapLocs = [];
$sitemapBlocks = [];
preg_match_all('~<url\b[^>]*>(.*?)</url>~si', $sitemap, $sitemapBlocks);
$assert(count($sitemapBlocks[1] ?? []) > 0, 'Sitemap must contain URL entries.');
foreach (($sitemapBlocks[1] ?? []) as $block) {
    preg_match_all('~<loc>\s*([^<]+)\s*</loc>~i', $block, $locMatches);
    preg_match_all('~<lastmod>\s*([^<]+)\s*</lastmod>~i', $block, $dateMatches);
    $assert(count($locMatches[1] ?? []) === 1, 'Each sitemap URL entry must have exactly one loc element.');
    $assert(count($dateMatches[1] ?? []) === 1, 'Each sitemap URL entry must have exactly one lastmod element.');
    if (count($locMatches[1] ?? []) !== 1) {
        continue;
    }
    $loc = html_entity_decode(trim($locMatches[1][0]), ENT_QUOTES | ENT_XML1, 'UTF-8');
    $parts = parse_url($loc);
    $assert(is_array($parts) && strtolower((string) ($parts['scheme'] ?? '')) === 'https', 'Sitemap URL must be absolute HTTPS: ' . $loc);
    $assert(is_array($parts) && strtolower((string) ($parts['host'] ?? '')) === strtolower((string) ($siteParts['host'] ?? '')), 'Sitemap URL must use the configured canonical host: ' . $loc);
    $assert(is_array($parts) && !isset($parts['query']) && !isset($parts['fragment']) && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['port']), 'Sitemap URL must be canonical and contain no query, fragment, credentials or port: ' . $loc);
    if (!is_array($parts)) {
        continue;
    }
    $path = (string) ($parts['path'] ?? '/');
    $routeKey = $path === '' ? '/' : $path;
    $assert(!isset($sitemapLocs[$loc]), 'Sitemap contains duplicate loc URLs: ' . $loc);
    $assert(!isset($sitemapRoutes[$routeKey]), 'Sitemap contains duplicate route paths: ' . $routeKey);
    $sitemapLocs[$loc] = true;
    $sitemapRoutes[$routeKey] = true;

    $lastmod = trim((string) (($dateMatches[1] ?? [])[0] ?? ''));
    $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $lastmod)
        && checkdate((int) substr($lastmod, 5, 2), (int) substr($lastmod, 8, 2), (int) substr($lastmod, 0, 4));
    $assert((bool) $validDate, 'Sitemap lastmod must be a valid YYYY-MM-DD date: ' . $loc . ' → ' . $lastmod);

    $relativePath = $routeKey === '/' ? 'index.php' : rawurldecode(ltrim($routeKey, '/'));
    $assert($routeKey === '/' || preg_match('/\.php$/i', $relativePath), 'Sitemap entries must be public PHP routes or the homepage: ' . $loc);
    $localTarget = $root . '/' . $relativePath;
    $assert(is_file($localTarget) && !is_link($localTarget), 'Sitemap route must resolve to a regular local file: ' . $loc);
}
foreach ($liveRoutes as $route => $_unused) {
    $assert(isset($sitemapRoutes[$route]), 'Live editorial route is missing from sitemap: ' . $route);
}
foreach ($guides as $slug => $guide) {
    if (!is_array($guide)) {
        continue;
    }
    if (($guide['status'] ?? 'live') !== 'live') {
        $assert(!isset($sitemapRoutes['/' . $slug . '.php']), 'Draft/hidden editorial route must not enter sitemap: /' . $slug . '.php');
    }
}

$summary = 'registry=' . count($guides)
    . ', live-editorial=' . count($liveRoutes)
    . ', topics=' . count($topics)
    . ', sitemap-routes=' . count($sitemapRoutes);
if ($failures) {
    fwrite(STDERR, 'FAIL editorial SEO release readiness (' . count($failures) . ' issue(s); ' . $summary . '):' . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, ' - ' . $failure . PHP_EOL);
    }
    exit(1);
}
echo 'PASS editorial SEO release readiness: ' . $summary . '; robots, canonical/social head contract, registry status, related links and sitemap parity verified.' . PHP_EOL;
