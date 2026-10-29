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

$defaultImageHtml = $renderHead([]);
$assert(
    strpos($defaultImageHtml, 'property="og:image:alt" content="Carved facade of Garh Jaisal Haveli inside Jaisalmer Fort"') !== false,
    'The shared default facade image must have its reviewed description.'
);
$assert(
    strpos($defaultImageHtml, 'name="twitter:image:alt" content="Carved facade of Garh Jaisal Haveli inside Jaisalmer Fort"') !== false,
    'The shared default Twitter/X image must inherit the same reviewed description.'
);

$unmappedImageHtml = $renderHead(['og_image' => '/img/unmapped.jpg']);
$assert(
    strpos($unmappedImageHtml, 'property="og:image:alt"') === false,
    'The default facade description must not be attached to a different, unmapped image.'
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

$dimensionHtml = $renderHead([
    'og_image' => '/img/v2/facade.jpg',
    'og_image_alt' => 'Carved facade of Garh Jaisal Haveli inside Jaisalmer Fort',
]);
$assert(
    (bool) preg_match('/property="og:image:width" content="[1-9][0-9]*"/', $dimensionHtml)
    && (bool) preg_match('/property="og:image:height" content="[1-9][0-9]*"/', $dimensionHtml),
    'A decodable local social image must expose its actual intrinsic dimensions.'
);
$assert(
    strpos($dimensionHtml, 'property="og:image:type" content="image/jpeg"') !== false,
    'A decodable local social image must expose its actual MIME type.'
);

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "PASS social image alt contract: reviewed default, custom-image specificity, distinct-image protection and escaped output.\n";
