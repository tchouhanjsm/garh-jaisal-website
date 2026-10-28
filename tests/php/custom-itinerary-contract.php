<?php
/** Source contract for the custom itinerary builder: browser-local and enquiry-only. */
$root = dirname(__DIR__, 2);
$errors = [];
$requiredMarkers = [
    'custom-itinerary.php' => ['data-custom-itinerary-builder', 'id="custom-itinerary-form"', 'id="custom-itinerary-draft"', 'custom-itinerary.js', 'gj_schema_breadcrumb_list', 'gj_schema_faq', 'contact.php?topic=custom-itinerary'],
    'car-tours.php' => ['href="custom-itinerary.php"', 'not a complete list', 'tour-ideas-custom-builder'],
    'include/contact-topics.php' => ["'custom-itinerary' =>", 'custom Jaisalmer itinerary'],
    'scripts/generate-sitemap.php' => ["['path' => '/custom-itinerary.php', 'priority' => '0.7']"],
    'sitemap.xml' => ['https://www.hotelgarhjaisal.com/custom-itinerary.php</loc><lastmod>'],
    'js/custom-itinerary.js' => ["querySelectorAll('input[name=\"interests\"]:checked')", "searchParams.set('text', message)", 'aria-disabled', 'new URL(whatsappBase)'],
    'css/custom-itinerary.css' => ['.custom-itinerary-content-grid', '@media(max-width:600px)', 'prefers-reduced-motion'],
];
foreach ($requiredMarkers as $relativePath => $markers) {
    $path = $root . '/' . $relativePath;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) {
        $errors[] = 'Missing or unreadable source file: ' . $relativePath;
        continue;
    }
    foreach ($markers as $marker) {
        if (strpos($source, $marker) === false) $errors[] = $relativePath . ' is missing required marker: ' . $marker;
    }
}

$sitemapContent = file_get_contents($root . '/sitemap.xml');
$sitemapMatch = [];
$hasValidCustomItineraryLastmod = is_string($sitemapContent)
    && preg_match(
        '~<url><loc>https://www\\.hotelgarhjaisal\\.com/custom-itinerary\\.php</loc><lastmod>(\\d{4}-\\d{2}-\\d{2})</lastmod><priority>0\\.7</priority></url>~',
        $sitemapContent,
        $sitemapMatch
    ) === 1
    && checkdate(
        (int) substr($sitemapMatch[1], 5, 2),
        (int) substr($sitemapMatch[1], 8, 2),
        (int) substr($sitemapMatch[1], 0, 4)
    );
if (!$hasValidCustomItineraryLastmod) {
    $errors[] = 'sitemap.xml custom itinerary route must have a valid lastmod date and retain its reviewed priority.';
}

$page = file_get_contents($root . '/custom-itinerary.php');
if (is_string($page)) {
    foreach (['$_POST', 'method="post"', 'file_put_contents(', 'INSERT INTO'] as $forbidden) {
        if (stripos($page, $forbidden) !== false) $errors[] = 'Custom itinerary page must not submit or persist guest data: found ' . $forbidden;
    }
}
$script = file_get_contents($root . '/js/custom-itinerary.js');
if (is_string($script)) {
    foreach (['fetch(', 'XMLHttpRequest', 'localStorage', 'sessionStorage', 'navigator.sendBeacon'] as $forbidden) {
        if (strpos($script, $forbidden) !== false) $errors[] = 'Custom itinerary draft must remain in-browser; found ' . $forbidden;
    }
}
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS: custom itinerary remains open-ended, enquiry-only and browser-local." . PHP_EOL;
