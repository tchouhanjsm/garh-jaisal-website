<?php
/** Ensure global reduced-motion support loads after route-level styles. */
$root = dirname(__DIR__, 2);
$headPath = $root . '/include/page-head.php';
$stylePath = $root . '/css/accessibility.css';
$failures = [];
$assert = static function ($condition, $message) use (&$failures) {
    if (!$condition) { $failures[] = $message; }
};
$head = is_file($headPath) && !is_link($headPath) ? file_get_contents($headPath) : false;
$css = is_file($stylePath) && !is_link($stylePath) ? file_get_contents($stylePath) : false;
$assert(is_string($head), 'The shared page-head renderer must be readable.');
$assert(is_string($css) && trim($css) !== '', 'The shared accessibility stylesheet must exist and be non-empty.');
if (is_string($head)) {
    $conversionIndex = strpos($head, 'css/conversion-system.css');
    $accessibilityIndex = strpos($head, 'css/accessibility.css');
    $assert($accessibilityIndex !== false, 'The shared page head must load css/accessibility.css.');
    $assert($conversionIndex !== false && $accessibilityIndex !== false && $accessibilityIndex > $conversionIndex,
        'The preference stylesheet must load after the shared conversion system.');
}
if (is_string($css)) {
    foreach ([
        '@media (prefers-reduced-motion: reduce)',
        'animation: none !important',
        'scroll-behavior: auto !important',
        'transition: none !important',
        '*:hover',
        'transform: none !important',
    ] as $marker) {
        $assert(strpos($css, $marker) !== false, 'Missing accessibility stylesheet contract marker: ' . $marker);
    }
}
foreach ($failures as $failure) { fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL); }
if ($failures) { printf("Accessibility stylesheet contract: %d failure(s)\n", count($failures)); exit(1); }
echo "PASS: shared reduced-motion preference layer and load order are present.\n";
