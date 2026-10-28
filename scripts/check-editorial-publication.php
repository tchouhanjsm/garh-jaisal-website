<?php
/**
 * Contract check for the public editorial publishing pipeline.
 *
 * Verifies the registry, shared template, archive discovery, sitemap source
 * and public route wrappers without requiring a web server.
 */
$root = dirname(__DIR__);
require_once $root . '/include/image-metadata.php';
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
    foreach (['title', 'description', 'h1', 'lede', 'hero_image', 'hero_alt', 'hero_caption', 'date_published', 'date_modified', 'date_display'] as $field) {
        $assert(is_string($guide[$field] ?? null) && trim($guide[$field]) !== '', 'Missing required ' . $field . ': ' . $slug);
    }
    $title = trim((string) ($guide['title'] ?? ''));
    $description = trim((string) ($guide['description'] ?? ''));
    if ($status === 'live') {
        $assert($title !== '' && strlen($title) <= 90, 'Live article title must be concise and unique: ' . $slug);
        $assert($description !== '' && strlen($description) <= 180, 'Live description must be concise: ' . $slug);
        $assert(!isset($seenTitles[$title]), 'Duplicate live SEO title: ' . $title);
        $assert(!isset($seenDescriptions[$description]), 'Duplicate live meta description: ' . $slug);
        $seenTitles[$title] = $slug;
        $seenDescriptions[$description] = $slug;
        $liveSlugs[] = $slug;
        $assert(is_file($root . '/' . $slug . '.php'), 'Live guide is missing its public PHP wrapper: ' . $slug);
        $assert(!empty($guide['sources']) && is_array($guide['sources']), 'Live guide must cite at least one source: ' . $slug);
        foreach (($guide['sources'] ?? []) as $source) {
            $sourceUrl = is_array($source) ? (string) ($source['url'] ?? '') : '';
            $isExternalSource = preg_match('#^https://#i', $sourceUrl) === 1;
            $isInternalLink = preg_match('~^[A-Za-z0-9][A-Za-z0-9._/-]*\\.php(?:[?][A-Za-z0-9._=%&-]+)?$~', $sourceUrl) === 1;
            $assert(is_array($source) && ($isExternalSource || $isInternalLink), 'Source must be a valid HTTPS citation or internal reference: ' . $slug);
            if ($isExternalSource) {
                $assert(trim((string) ($source['label'] ?? '')) !== '' && trim((string) ($source['note'] ?? '')) !== '', 'External source needs a descriptive label and editorial context: ' . $slug);
            }
        }
        $assert(!empty($guide['hero_alt']), 'Live guide hero image needs descriptive alternative text: ' . $slug);
        $heroSource = trim((string) ($guide['hero_image'] ?? ''));
        $safeHeroSource = preg_match('~^img/[A-Za-z0-9._/-]+$~D', $heroSource) === 1
            && strpos($heroSource, '..') === false
            && strpos($heroSource, chr(92)) === false;
        $heroFile = $root . '/' . $heroSource;
        $heroMetadata = $safeHeroSource ? gj_local_image_metadata($heroSource) : null;
        $heroExtension = strtolower(pathinfo($heroSource, PATHINFO_EXTENSION));
        $assert($safeHeroSource && is_file($heroFile) && !is_link($heroFile), 'Live editorial hero image must be a safe, local regular file: ' . $slug . ' → ' . $heroSource);
        $assert(is_array($heroMetadata) || ($safeHeroSource && is_file($heroFile) && $heroExtension === 'avif'), 'Live editorial hero image must be decodable or use the supported AVIF fallback path: ' . $slug);
        $assert(strlen(trim((string) ($guide['hero_alt'] ?? ''))) >= 15, 'Live editorial hero alt text should describe the subject rather than a generic image: ' . $slug);
        $assert(strlen(trim((string) ($guide['hero_caption'] ?? ''))) >= 20 && strlen(trim((string) ($guide['hero_caption'] ?? ''))) <= 180, 'Live editorial hero caption must be useful and concise: ' . $slug);
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
        $availableAnchors = $ids;
        $availableAnchors['official-sources'] = true;
        $tocIds = [];
        foreach (($guide['toc'] ?? []) as $tocItem) {
            $tocId = is_array($tocItem) ? (string) ($tocItem['id'] ?? '') : '';
            $tocLabel = is_array($tocItem) ? trim((string) ($tocItem['label'] ?? '')) : '';
            $assert($tocId !== '' && isset($availableAnchors[$tocId]), 'TOC points to a missing article section: ' . $slug . '#' . $tocId);
            $assert($tocLabel !== '', 'TOC item needs an accessible label: ' . $slug . '#' . $tocId);
            $assert(!isset($tocIds[$tocId]), 'Duplicate TOC anchor: ' . $slug . '#' . $tocId);
            if ($tocId !== '') $tocIds[$tocId] = true;
        }

        // Validate every internal route referenced by article copy, related
        // cards, source links or CTAs. External URLs are not treated as paths.
        $editorialValuesToScan = [
            $guide['sections'] ?? [],
            $guide['related'] ?? [],
            $guide['sources'] ?? [],
            $guide['cta_primary_href'] ?? '',
            $guide['cta_secondary_href'] ?? '',
        ];
        while ($editorialValuesToScan) {
            $editorialValue = array_pop($editorialValuesToScan);
            if (is_array($editorialValue)) {
                foreach ($editorialValue as $nestedEditorialValue) {
                    $editorialValuesToScan[] = $nestedEditorialValue;
                }
                continue;
            }
            if (!is_string($editorialValue) || preg_match('#^https?://#i', trim($editorialValue))) {
                continue;
            }
            if (preg_match_all('~(?<![A-Za-z0-9])([A-Za-z0-9][A-Za-z0-9._/-]*\.php(?:\?[A-Za-z0-9._=%&-]+)?)~', $editorialValue, $routeMatches)) {
                foreach ($routeMatches[1] as $routeReference) {
                    $routePath = parse_url($routeReference, PHP_URL_PATH);
                    $routePath = is_string($routePath) ? ltrim($routePath, '/') : '';
                    $safeInternalPath = $routePath !== '' && strpos($routePath, '..') === false;
                    $assert($safeInternalPath, 'Unsafe internal route reference in ' . $slug . ': ' . $routeReference);
                    if (!$safeInternalPath) continue;
                    $internalFile = $root . '/' . $routePath;
                    $assert(is_file($internalFile) && !is_link($internalFile), 'Internal editorial route does not resolve: ' . $slug . ' → ' . $routeReference);
                }
            }
        }
    }
}
$assert(strpos($template, "'@type' => 'BlogPosting'") !== false, 'Article template must emit BlogPosting schema.');
$assert(strpos($template, 'gj_schema_image_object') !== false, 'Article image schema must use shared media metadata.');
$assert(strpos($template, '<figcaption class="article-caption">') !== false, 'Editorial hero image must render its reviewed visible caption.');
$assert(strpos($template, '$guideHeroCaption') !== false, 'Editorial renderer must require and escape a hero caption.');
$assert(strpos($template, 'gj_schema_breadcrumb_list') !== false, 'Article template must emit breadcrumb structured data.');
$assert(strpos($sitemapGenerator, '$routeSlug') !== false && strpos($sitemapGenerator, 'status') !== false && strpos($sitemapGenerator, 'continue;') !== false, 'Static legacy article URLs must honour registry status.');
$assert(strpos($template, "'robots' => " . '$isDraft' . " ? 'noindex,follow' : 'index,follow'") !== false, 'Draft preview must remain noindex.');
$assert(strpos($template, '#official-sources') !== false && strpos($template, 'target="_blank" rel="noopener"') !== false, 'Source links must remain visible and safely opened.');
$assert(strpos($template, 'article-topic-links') !== false, 'Article pages must provide cross-guide discovery.');
$assert(strpos($archive, "=== 'live'") !== false && strpos($archive, '$archiveGuides = array_filter($editorialGuides') !== false, 'Archive must filter editorial records to live status.');
$assert(strpos($archive, 'journal-topic-index') !== false, 'Journal archive must offer topic navigation.');
$assert(strpos($sitemapGenerator, '$routeSlug') !== false && strpos($sitemapGenerator, 'status') !== false && strpos($sitemapGenerator, 'continue;') !== false, 'Static legacy article URLs must honour registry status.');
$assert(strpos($sitemapGenerator, 'foreach ($editorialGuides as $slug => $guide)') !== false, 'Sitemap must derive editorial routes from the same registry.');
$assert(strpos($sitemapGenerator, "'include/editorial-guide-page.php'") !== false, 'Sitemap lastmod must include changes to the shared article renderer.');
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
