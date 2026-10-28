<?php
/**
 * Verify social image alt metadata is emitted safely and is never copied to a
 * different image unless the page explicitly supplies a corresponding alt.
 */
$root = dirname(__DIR__, 2);
$failures = [];

$assert = static function ($condition, $message) use (&$failures) {
    if (!$condition) {
        $failures[] = $message;
    }
};

$renderHead = static function ($overrides) use ($root) {
    $siteConfig = [
        'site_url' => 'https://www.hotelgarhjaisal.com',
        'analytics_ids' => [],
    ];
    $page = array_merge([
        'title' => 'Social image metadata contract',
        'description' => 'A deterministic test of social preview image descriptions.',
        'path' => '/social-image-contract-test.php',
    ], $overrides);

    ob_start();
    require $root . '/include/page-head.php';
    $html = ob_get_clean();
    return is_string($html) ? $html : '';
};

$sameImageHtml = $renderHead([
    'og_image' => '/img/sample.jpg',
    'og_image_alt' => 'A "stone" facade & courtyard',
]);
$assert(
    strpos($sameImageHtml, 'property="og:image:alt" content="A &quot;stone&quot; facade &amp; courtyard"') !== false,
    'Open Graph image alt must be escaped and emitted.'
);
$assert(
    strpos($sameImageHtml, 'name="twitter:image:alt" content="A &quot;stone&quot; facade &amp; courtyard"') !== false,
    'The same image must share its accurate alt text with Twitter/X metadata.'
);

$differentImageHtml = $renderHead([
    'og_image' => '/img/open-graph.jpg',
    'og_image_alt' => 'A sandstone facade',
    'twitter_image' => '/img/twitter-card.jpg',
]);
$assert(
    strpos($differentImageHtml, 'name="twitter:image:alt"') === false,
    'Open Graph alt text must not be applied to a different Twitter/X image implicitly.'
);

$explicitTwitterAltHtml = $renderHead([
    'og_image' => '/img/open-graph.jpg',
    'og_image_alt' => 'A sandstone facade',
    'twitter_image' => '/img/twitter-card.jpg',
    'twitter_image_alt' => 'A rooftop at dusk',
]);
$assert(
    strpos($explicitTwitterAltHtml, 'name="twitter:image:alt" content="A rooftop at dusk"') !== false,
    'A separately configured Twitter/X image must use its own explicit alt text.'
);

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "PASS social image alt contract: shared fallback, distinct-image protection and escaped output.\n";
