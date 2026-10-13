<?php
/**
 * Shared page head contract.
 *
 * Required:
 *   $page['title']       Page title.
 *   $page['description'] Meta description.
 *   $page['path']        Site-relative path; '/' for the homepage.
 *
 * Optional:
 *   robots, og_image, og_title, og_description, twitter_card,
 *   twitter_title, twitter_description, twitter_image, css, schema, preload.
 *
 * The helper emits the complete <head> element. Extra CSS follows
 * css/garh-v2.css. Site-wide Hotel/WebSite JSON-LD nodes are followed by the
 * current page's schema nodes. Analytics IDs come only from site-config.php.
 */
if (!isset($siteConfig) || !is_array($siteConfig)) {
    $siteConfig = require __DIR__ . '/site-config.php';
}

if (!isset($page) || !is_array($page)) {
    throw new InvalidArgumentException('The page head requires a $page configuration array.');
}

foreach (['title', 'description', 'path'] as $requiredKey) {
    if (!isset($page[$requiredKey]) || !is_string($page[$requiredKey]) || trim($page[$requiredKey]) === '') {
        throw new InvalidArgumentException('Missing required page head value: ' . $requiredKey);
    }
}

$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
if (strncmp($siteUrl, 'https://', 8) !== 0) {
    throw new InvalidArgumentException('The canonical site_url must use HTTPS.');
}

$pagePath = $page['path'];
if (
    $pagePath !== '/'
    && (
        $pagePath[0] !== '/'
        || strncmp($pagePath, '//', 2) === 0
        || strpos($pagePath, '?') !== false
        || strpos($pagePath, '#') !== false
        || strpos($pagePath, chr(92)) !== false
    )
) {
    throw new InvalidArgumentException('The page path must be site-relative and must not contain a query or fragment.');
}

$canonical = $siteUrl . ($pagePath === '/' ? '/' : $pagePath);
$pageTitle = $page['title'];
$pageDescription = $page['description'];
$robots = $page['robots'] ?? 'index,follow';
$ogType = $page['og_type'] ?? 'website';
$ogTitle = $page['og_title'] ?? $pageTitle;
$ogDescription = $page['og_description'] ?? $pageDescription;
$twitterCard = $page['twitter_card'] ?? 'summary_large_image';
$twitterTitle = $page['twitter_title'] ?? $pageTitle;
$twitterDescription = $page['twitter_description'] ?? $pageDescription;
$ogImageSource = $page['og_image'] ?? '/img/v2/facade.jpg';
$twitterImageSource = $page['twitter_image'] ?? $ogImageSource;

$htmlEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$absoluteAssetUrl = static function ($value) use ($siteUrl) {
    $value = (string) $value;
    if (preg_match('#^https://#i', $value)) {
        return $value;
    }
    return $siteUrl . '/' . ltrim($value, '/');
};

$extraStylesheets = $page['css'] ?? [];
if (!is_array($extraStylesheets)) {
    throw new InvalidArgumentException('The page css value must be an array of stylesheet paths.');
}

$preloads = $page['preload'] ?? [];
if (!is_array($preloads)) {
    throw new InvalidArgumentException('The page preload value must be an array of preload descriptors.');
}

require_once __DIR__ . '/schema.php';
$pageSchema = $page['schema'] ?? [];
if (!is_array($pageSchema)) {
    throw new InvalidArgumentException('The page schema value must be an array of JSON-LD nodes.');
}
foreach ($pageSchema as $schemaNode) {
    if (!is_array($schemaNode)) {
        throw new InvalidArgumentException('Each page schema entry must be an array.');
    }
}
$schemaNodes = array_merge(
    [
        gj_schema_hotel($siteUrl),
        gj_schema_website($siteUrl),
    ],
    $pageSchema
);
$schemaJson = json_encode(
    [
        '@context' => 'https://schema.org',
        '@graph' => $schemaNodes,
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT
);
if ($schemaJson === false) {
    throw new RuntimeException('Unable to encode the page JSON-LD: ' . json_last_error_msg());
}

$analyticsIds = $siteConfig['analytics_ids'] ?? [];
if (!is_array($analyticsIds)) {
    $analyticsIds = [];
}
$analyticsIds = array_values(array_unique(array_filter(
    $analyticsIds,
    static function ($id) {
        return is_string($id) && $id !== '';
    }
)));
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $htmlEscape($pageTitle) ?></title>
  <meta name="description" content="<?= $htmlEscape($pageDescription) ?>">
  <meta name="robots" content="<?= $htmlEscape($robots) ?>">
  <link rel="canonical" href="<?= $htmlEscape($canonical) ?>">

  <meta property="og:type" content="<?= $htmlEscape($ogType) ?>">
  <meta property="og:title" content="<?= $htmlEscape($ogTitle) ?>">
  <meta property="og:description" content="<?= $htmlEscape($ogDescription) ?>">
  <meta property="og:url" content="<?= $htmlEscape($canonical) ?>">
  <meta property="og:image" content="<?= $htmlEscape($absoluteAssetUrl($ogImageSource)) ?>">

  <meta name="twitter:card" content="<?= $htmlEscape($twitterCard) ?>">
  <meta name="twitter:title" content="<?= $htmlEscape($twitterTitle) ?>">
  <meta name="twitter:description" content="<?= $htmlEscape($twitterDescription) ?>">
  <meta name="twitter:image" content="<?= $htmlEscape($absoluteAssetUrl($twitterImageSource)) ?>">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
<?php foreach ($extraStylesheets as $stylesheet): ?>
  <link rel="stylesheet" href="<?= $htmlEscape($stylesheet) ?>">
<?php endforeach; ?>
<?php foreach ($preloads as $preload): ?>
<?php if (is_array($preload) && isset($preload['href']) && is_string($preload['href']) && $preload['href'] !== ''): ?>
  <link rel="preload" as="image" href="<?= $htmlEscape($absoluteAssetUrl($preload['href'])) ?>"<?php if (!empty($preload['imagesrcset'])): ?> imagesrcset="<?= $htmlEscape($preload['imagesrcset']) ?>"<?php endif; ?><?php if (!empty($preload['imagesizes'])): ?> imagesizes="<?= $htmlEscape($preload['imagesizes']) ?>"<?php endif; ?>>
<?php endif; ?>
<?php endforeach; ?>
<?php if ($analyticsIds): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $htmlEscape(rawurlencode($analyticsIds[0])) ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
<?php foreach ($analyticsIds as $analyticsId): ?>
    gtag('config', <?= json_encode($analyticsId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
<?php endforeach; ?>
  </script>
<?php endif; ?>
  <script type="application/ld+json"><?= $schemaJson ?></script>
</head>
