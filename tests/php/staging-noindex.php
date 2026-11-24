<?php
/**
 * Regression guard: previews/staging must not accidentally become indexable.
 * This test exercises the real shared page head without starting a web server.
 */
$root = dirname(__DIR__, 2);
$production = 'https://www.hotelgarhjaisal.com';
$cases = [
    ['staging defaults to noindex', 'https://staging.example.net', 'staging.example.net', null, 'noindex,follow'],
    ['staging cannot override noindex with index', 'https://staging.example.net', 'staging.example.net', 'index,follow', 'noindex,follow'],
    ['preview inherits production canonical safely', $production, 'preview.example.net:8443', 'index,follow', 'noindex,follow'],
    ['missing Host fails closed', $production, null, null, 'noindex,follow'],
    ['forwarded production hostname cannot override localhost', $production, '127.0.0.1:8080', null, 'noindex,follow'],
    ['host suffixes are not production', $production, 'www.hotelgarhjaisal.com.preview.example.net', null, 'noindex,follow'],
    ['noncanonical port is not production', $production, 'www.hotelgarhjaisal.com:8080', null, 'noindex,follow'],
    ['production default remains indexable', $production, 'WWW.HOTELGARHJAISAL.COM:443', null, 'index,follow'],
    ['production explicit noindex is preserved', $production, 'www.hotelgarhjaisal.com', 'noindex,nofollow', 'noindex,nofollow'],
    ['production request cannot override a staging canonical', 'https://staging.example.net', 'www.hotelgarhjaisal.com', null, 'noindex,follow'],
    ['production apex is not accepted as configured canonical origin', 'https://hotelgarhjaisal.com', 'www.hotelgarhjaisal.com', null, 'noindex,follow'],
];

$renderRobots = static function ($siteUrl, $requestHost, $requestedRobots) use ($root) {
    if ($requestHost === null) {
        unset($_SERVER['HTTP_HOST']);
    } else {
        $_SERVER['HTTP_HOST'] = $requestHost;
    }
    $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.hotelgarhjaisal.com';
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
foreach ($cases as [$label, $siteUrl, $requestHost, $requestedRobots, $expected]) {
    $actual = $renderRobots($siteUrl, $requestHost, $requestedRobots);
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
