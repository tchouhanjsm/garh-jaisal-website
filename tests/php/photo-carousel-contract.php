<?php
/** Static contract for the shared photo-carousel renderer and its no-JS fallback. */
$root = dirname(__DIR__, 2);
$path = $root . '/include/ui/photo-carousel.php';
$source = is_file($path) ? file_get_contents($path) : false;
$errors = [];
if (!is_string($source)) {
    $errors[] = 'Shared photo-carousel renderer is missing or unreadable';
} else {
    $required = [
        'data-photo-carousel',
        'data-photo-carousel-slide',
        'data-photo-carousel-controls hidden',
        'data-photo-carousel-status',
        'role="status"',
        'aria-live="polite"',
        'aria-atomic="true"',
        'loading="lazy"',
        'decoding="async"',
        "function_exists('gj_image_dimension_attributes')",
        'is_link($photoCarouselAbsolute)',
        'preg_match',
        'htmlspecialchars($photoCarouselImage[',
    ];
    foreach ($required as $needle) {
        if (strpos($source, $needle) === false) $errors[] = 'Shared photo-carousel renderer missing marker: ' . $needle;
    }
    if (strpos($source, 'loading="eager"') !== false || strpos($source, 'fetchpriority="high"') !== false) {
        $errors[] = 'Below-fold carousel media must not be promoted to eager/high-priority loading';
    }
}
if ($errors) {
    fwrite(STDERR, "FAIL shared photo-carousel contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS shared photo-carousel contract.\n";
