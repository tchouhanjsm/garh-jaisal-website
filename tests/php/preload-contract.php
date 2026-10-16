<?php
/**
 * Regression contract for safe, viewport-aware image preloads and font stylesheet delivery.
 */
$failures = [];
$assertions = 0;
$assert = static function ($condition, $message) use (&$failures, &$assertions) {
    $assertions++;
    if (!$condition) {
        $failures[] = $message;
    }
};

$siteConfig = [
    'site_url' => 'https://www.hotelgarhjaisal.com',
    'analytics_ids' => [],
];
$page = [
    'title' => 'Preload contract test',
    'description' => 'Exercise the shared page head preload contract.',
    'path' => '/',
    'preload' => [
        [
            'href' => '/img/optimized/v2/facade.webp',
            'media' => '(max-width: 767px)',
            'fetchpriority' => 'high',
        ],
        [
            'href' => '/img/optimized/v2/fort-from-roof.webp',
            'media' => '(min-width: 768px)',
            'fetchpriority' => 'high',
        ],
        [
            'href' => '/img/optimized/v2/courtyard.webp',
            'media' => '(max-width: 767px)" onload="alert(1)',
            'fetchpriority' => 'high',
        ],
    ],
];

ob_start();
require dirname(__DIR__, 2) . '/include/page-head.php';
$html = (string) ob_get_clean();

$assert(
    strpos($html, 'href="https://www.hotelgarhjaisal.com/img/optimized/v2/facade.webp" media="(max-width: 767px)" fetchpriority="high"') !== false,
    'The mobile image preload must include its validated media query and high priority.'
);
$assert(
    strpos($html, 'href="https://www.hotelgarhjaisal.com/img/optimized/v2/fort-from-roof.webp" media="(min-width: 768px)" fetchpriority="high"') !== false,
    'The desktop image preload must include its validated media query and high priority.'
);
$assert(
    strpos($html, 'media="(max-width: 767px)" onload="alert(1)') === false,
    'Untrusted media text must not be emitted as a preload attribute.'
);
$assert(
    strpos($html, 'rel="preload" as="style"') !== false
        && strpos($html, 'media="print" onload="this.media=\'all\'"') !== false
        && strpos($html, '<noscript><link') !== false,
    'Google Fonts must be loaded without blocking first render and retain a no-JavaScript fallback.'
);

foreach ($failures as $failure) {
    fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
}
printf("Preload contract: %d assertion(s), %d failure(s)\n", $assertions, count($failures));
exit($failures ? 1 : 0);
