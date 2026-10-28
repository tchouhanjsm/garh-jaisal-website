<?php
/**
 * Contract check for the public editorial publishing pipeline.
 *
 * Verifies the registry, shared template, archive discovery, sitemap source
 * and public route wrappers without requiring a web server.
 */
$root = dirname(__DIR__);
$failures = [];
$assert = static function ($condition, $message) use (&$failures) {
    if (!$condition) $failures[] = $message;
};
$read = static function ($relative) use ($root) {
    $path = $root . '/' . $relative;
    if (!is_file($path) || is_link($path)) return false;
    return file_get_contents($path);
};

$registry = require $root . '/include/editorial-guides-data.php';
$assert(is_array($registry) && count($registry) >= 12, 'Editorial registry should contain the existing substantial guide set.');
$template = $read('include/editorial-guide-page.php');
$archive = $read('blog.php');
$sitemapGenerator = $read('scripts/generate-sitemap.php');
$head = $read('include/page-head.php');
$articleCss = $read('css/article.css');
foreach ([
    'include/editorial-guide-page.php' => $template,
    'blog.php' => $archive,
    'scripts/generate-sitemap.php' => $sitemapGenerator,
    'include/page-head.php' => $head,
    'css/article.css' => $articleCss,
] as $path => $value) {
    $assert(is_string($value), 'Required publication pipeline file missing: ' . $path);
}
if (!is_array($registry) || !is_string($template) || !is_string($archive) || !is_string($sitemapGenerator) || !is_string($head) || !is_string($articleCss)) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

$seenTitles = [];
$seenDescriptions = [];
$liveSlugs = [];
foreach ($registry as $slug => $guide) {
    $safeSlug = is_string($slug) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug);
    $assert($safeSlug, 'Invalid editorial registry slug: ' . (string) $slug);
    if (!$safeSlug || !is_array($guide)) continue;
    $assert(($guide['slug'] ?? null) === $slug, 'Registry key/slug mismatch: ' . $slug);
    $status = $guide['status'] ?? 'live';
    $assert(in_array($status, ['live', 'draft', 'hidden'], true), 'Invalid publication status: ' . $slug);
    foreach (['title', 'description', 'h1', 'lede', 'hero_image', 'hero_alt', 'date_published', 'date_modified', 'date_display'] as $field) {
        $assert(is_string($guide[$field] ?? null) && trim($guide[$field]) !== '', 'Missing required ' . $field . ': ' . $slug);
    }
    $title = trim((string) ($guide['title'] ?? ''));
    $description = trim((string) ($guide['description'] ?? ''));
    if ($status === 'live') {
        $assert($title !== '' && mb_strlen($title) <= 90, 'Live article title must be concise and unique: ' . $slug);
        $assert($description !== '' && mb_strlen($description) <= 180, 'Live description must be concise: ' . $slug);
        $assert(!isset($seenTitles[$title]), 'Duplicate live SEO title: ' . $title);
        $assert(!isset($seenDescriptions[$description]), 'Duplicate live meta description: ' . $slug);
        $seenTitles[$title] = $slug;
        $seenDescriptions[$description] = $slug;
        $liveSlugs[] = $slug;
        $assert(is_file($root . '/' . $slug . '.php'), 'Live guide is missing its public PHP wrapper: ' . $slug);
        $assert(!empty($guide['sources']) && is_array($guide['sources']), 'Live guide must cite at least one source: ' . $slug);
        foreach (($guide['sources'] ?? []) as $source) {
            $assert(is_array($source) && preg_match('#^https://#i', (string) ($source['url'] ?? '')), 'Source must have an HTTPS URL: ' . $slug);
        }
        $assert(!empty($guide['hero_alt']), 'Live guide hero image needs descriptive alternative text: ' . $slug);
        foreach (['toc', 'sections', 'related'] as $field) {
            $assert(is_array($guide[$field] ?? null) && count($guide[$field]) > 0, 'Live guide needs non-empty ' . $field . ': ' . $slug);
        }
        $published = (string) ($guide['date_published'] ?? '');
        $modified = (string) ($guide['date_modified'] ?? '');
        foreach (['published' => $published, 'modified' => $modified] as $label => $date) {
            $assert((bool) preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) && checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4)), 'Invalid ' . $label . ' date: ' . $slug);
        }
        $assert($modified >= $published, 'Modified date cannot precede published date: ' . $slug);
        $ids = [];
        foreach (($guide['sections'] ?? []) as $section) {
            $id = is_array($section) ? (string) ($section['id'] ?? '') : '';
            $assert($id !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id), 'Invalid section anchor: ' . $slug);
            $assert(!isset($ids[$id]), 'Duplicate section anchor ' . $id . ' in ' . $slug);
            if ($id !== '') $ids[$id] = true;
        }
    }
}
$assert(strpos($template, "'@type' => 'BlogPosting'") !== false, 'Article template must emit BlogPosting schema.');
$assert(strpos($template, 'gj_schema_breadcrumb_list') !== false, 'Article template must emit breadcrumb structured data.');
$assert(strpos($template, "'path' => '/' . $guide['slug'] . '.php'") !== false, 'Article canonical path must derive from the registry slug.');
$assert(strpos($template, "'robots' => $isDraft ? 'noindex,follow' : 'index,follow'") !== false, 'Draft preview must remain noindex.');
$assert(strpos($template, '#official-sources') !== false && strpos($template, 'target="_blank" rel="noopener"') !== false, 'Source links must remain visible and safely opened.');
$assert(strpos($template, 'article-topic-links') !== false, 'Article pages must provide cross-guide discovery.');
$assert(strpos($archive, "($guide['status'] ?? 'live') === 'live'") !== false || strpos($archive, "($guide['status'] ?? 'live') === 'live'") !== false, 'Archive must filter editorial records to live status.');
$assert(strpos($archive, 'journal-topic-index') !== false, 'Journal archive must offer topic navigation.');
$assert(strpos($sitemapGenerator, "($editorialGuides[$routeSlug]['status'] ?? 'live') !== 'live'") !== false, 'Static legacy article URLs must honour registry status.');
$assert(strpos($sitemapGenerator, 'foreach ($editorialGuides as $slug => $guide)') !== false, 'Sitemap must derive editorial routes from the same registry.');
$assert(strpos($head, 'link rel="canonical"') !== false && strpos($head, 'property="og:url"') !== false, 'Shared head must emit canonical and Open Graph URL.');
$assert(strpos($articleCss, 'prefers-reduced-motion: reduce') !== false, 'Editorial motion must honor reduced-motion preference.');
$assert(strpos($articleCss, '@media (max-width: 900px)') !== false && strpos($articleCss, '@media (max-width: 560px)') !== false, 'Archive and article layouts need tablet and narrow mobile rules.');

$sitemap = $read('sitemap.xml');
if (is_string($sitemap)) {
    preg_match_all('#<loc>https://www\.hotelgarhjaisal\.com/([^<]+)</loc>#', $sitemap, $matches);
    $sitemapSlugs = array_map(static function ($path) { return basename($path, '.php'); }, $matches[1]);
    foreach ($liveSlugs as $slug) {
        $assert(in_array($slug, $sitemapSlugs, true), 'Live article missing from sitemap: ' . $slug);
    }
    foreach ($registry as $slug => $guide) {
        if (is_array($guide) && ($guide['status'] ?? 'live') !== 'live') {
            $assert(!in_array($slug, $sitemapSlugs, true), 'Non-live editorial route must not be in sitemap: ' . $slug);
        }
    }
}
if ($failures) {
    fwrite(STDERR, 'FAIL editorial publication contract (' . count($failures) . ' issue(s)):' . PHP_EOL);
    foreach ($failures as $failure) fwrite(STDERR, ' - ' . $failure . PHP_EOL);
    exit(1);
}
echo 'PASS editorial publication contract: ' . count($liveSlugs) . ' live guides, unique metadata, sources, routes, schema/template and sitemap parity.' . PHP_EOL;
