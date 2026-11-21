<?php
/**
 * Regression contract for safe image preloads and local typography.
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
            'imagesrcset' => '/img/responsive/v2/facade-480.webp 480w, /img/responsive/v2/facade-800.webp 800w',
            'imagesizes' => '100vw',
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
    strpos($html, 'href="/img/optimized/v2/facade.webp" media="(max-width: 767px)" fetchpriority="high"') !== false,
    'The mobile image preload must include its validated media query and high priority.'
);
$assert(
    strpos($html, 'href="/img/optimized/v2/fort-from-roof.webp" media="(min-width: 768px)" fetchpriority="high"') !== false,
    'The desktop image preload must include its validated media query and high priority.'
);
$assert(
    strpos($html, 'imagesrcset="img/responsive/v2/facade-480.webp 480w, img/responsive/v2/facade-800.webp 800w" imagesizes="100vw"') !== false,
    'Responsive image preloads must carry a candidate list and matching sizes.'
);
$assert(
    strpos($html, 'media="(max-width: 767px)" onload="alert(1)') === false,
    'Untrusted media text must not be emitted as a preload attribute.'
);
$assert(
    strpos($html, 'fonts.googleapis.com') === false && strpos($html, 'fonts.gstatic.com') === false,
    'The shared head must not request remote fonts.'
);

$homeHtml = (static function () {
    ob_start();
    require dirname(__DIR__, 2) . '/index.php';
    return (string) ob_get_clean();
})();
$homeHeroSource = 'img/optimized/v2/fort-from-roof.webp';
preg_match_all('/<img\b[^>]*\bfetchpriority="high"[^>]*>/', $homeHtml, $priorityImages);
$homeHero = $priorityImages[0][0] ?? '';
$assert(
    count($priorityImages[0]) === 1 && strpos($homeHero, 'src="' . $homeHeroSource . '"') !== false,
    'The homepage must prioritize one full-width Fort panorama.'
);
preg_match_all('/<link rel="preload" as="image"[^>]*>/', $homeHtml, $imagePreloads);
if (preg_match('/\bsrcset="([^"]+)"/', $homeHero, $heroCandidates) === 1) {
    $preload = $imagePreloads[0][0] ?? '';
    preg_match('/\bimagesrcset="([^"]+)"/', $preload, $preloadedCandidates);
    // Both root-relative and relative candidates resolve from the homepage.
    $candidateList = preg_replace('~(^|, )/~', '$1', $heroCandidates[1]);
    $assert(
        count($imagePreloads[0]) === 1
            && strpos($preload, 'href="/' . $homeHeroSource . '"') !== false
            && strpos($preload, 'fetchpriority="high"') !== false
            && preg_replace('~(^|, )/~', '$1', $preloadedCandidates[1] ?? '') === $candidateList
            && strpos($preload, 'imagesizes="100vw"') !== false
            && strpos($homeHero, ' sizes="100vw"') !== false,
        'The homepage preload must match the displayed panorama candidates and full-width slot.'
    );
} else {
    $assert($imagePreloads[0] === [], 'The panorama fallback must not preload a missing responsive candidate.');
}

foreach ($failures as $failure) {
    fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
}
printf("Preload contract: %d assertion(s), %d failure(s)\n", $assertions, count($failures));
exit($failures ? 1 : 0);
