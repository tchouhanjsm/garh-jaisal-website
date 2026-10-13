<?php
/**
 * Regression guard: previews/staging must not accidentally become indexable.
 * This test exercises the real shared page head without starting a web server.
 */
$root = dirname(__DIR__, 2);
$cases = [
    ['staging defaults to noindex', 'https://staging.example.net', null, 'noindex,follow'],
    ['staging cannot override noindex with index', 'https://staging.example.net', 'index,follow', 'noindex,follow'],
    ['preview origin defaults to noindex', 'https://preview.example.net:8443', null, 'noindex,follow'],
    ['production default remains indexable', 'https://www.hotelgarhjaisal.com', null, 'index,follow'],
    ['production explicit noindex is preserved', 'https://www.hotelgarhjaisal.com', 'noindex,follow', 'noindex,follow'],
    ['production apex is not accepted as configured canonical origin', 'https://hotelgarhjaisal.com', null, 'noindex,follow'],
];

$renderRobots = static function ($siteUrl, $requestedRobots) use ($root) {
    $siteConfig = ['site_url' => $siteUrl, 'analytics_ids' => []];
    $page = [
        'title' => 'Staging robots regression',
        'description' => 'Automated test of production versus non-production robots behavior.',
        'path' => '/staging-robots-test.php',
    ];
    if ($requestedRobots !== null) {
        $page['robots'] = $requestedRobots;
    }

    ob_start();
    require $root . '/include/page-head.php';
    $html = ob_get_clean();
    if (!is_string($html) || !preg_match('/<meta\s+name="robots"\s+content="([^"]+)"/i', $html, $matches)) {
        throw new RuntimeException('Shared page head did not emit a readable robots meta tag.');
    }
    return $matches[1];
};

$errors = [];
foreach ($cases as [$label, $siteUrl, $requestedRobots, $expected]) {
    $actual = $renderRobots($siteUrl, $requestedRobots);
    if ($actual !== $expected) {
        $errors[] = $label . ': expected ' . $expected . ', got ' . $actual;
    }
}

if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, 'FAIL ' . $error . PHP_EOL);
    }
    exit(1);
}

echo 'PASS staging noindex guard: ' . count($cases) . " cases.\n";
