<?php
/** Regression contract for conversion-led copy, responsive controls and a11y. */
$root = dirname(__DIR__, 2);
$errors = [];
$required = [
    'index.php' => ['Wake up inside Jaisalmer Fort.', 'Heritage stay · Inside Jaisalmer Fort', 'individually styled rooms', 'home-hero-facts', 'Explore the rooms', 'conversion-journey', 'conversion-faq', 'Check rooms &amp; rates', 'guided-jaisalmer-tour.php', 'desert-safari.php'],
    'about.php' => ['Stay inside the Fort. Feel closer to Jaisalmer.', 'Good hospitality begins before you arrive.', 'conversion-promise'],
    'desert-safari.php' => ['Find the Jaisalmer desert safari that fits your trip.', 'desert-options', 'conversion-checklist', 'Confirm my safari details'],
    'guided-jaisalmer-tour.php' => ['See Jaisalmer with a guide, at your pace.', 'guided-options', 'Plan my guided tour'],
    'city-tour.php' => ['Discover the stories inside Jaisalmer Fort.', 'Ask about route, guide'],
    'room.php' => ['Find your room inside Jaisalmer Fort.'],
    'restaurant.php' => ['Rooftop dining in the heart of Jaisalmer Fort.', 'Enquire about a table'],
    'include/experience-page.php' => ['An enquiry is not a confirmed booking.', 'Build the day around what you want to discover.'],
    'include/footer-v2.php' => ['Let Jaisalmer begin inside the Fort.', 'Check dates &amp; current rates'],
];
foreach ($required as $relative => $needles) {
    $path = $root . '/' . $relative;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) { $errors[] = $relative . ': file could not be read'; continue; }
    foreach ($needles as $needle) {
        if (strpos($source, $needle) === false) $errors[] = $relative . ': missing contract: ' . $needle;
    }
}
$cssPath = $root . '/css/conversion-system.css';
$css = is_file($cssPath) ? file_get_contents($cssPath) : false;
if (!is_string($css)) {
    $errors[] = 'css/conversion-system.css: file could not be read';
} else {
    foreach (['@media (max-width: 760px)', 'min-height: 48px', 'focus-visible', 'prefers-reduced-motion', '.conversion-journey__grid', '.conversion-faq__list'] as $needle) {
        if (strpos($css, $needle) === false) $errors[] = 'conversion-system.css: missing responsive/accessibility contract: ' . $needle;
    }
}
$head = file_get_contents($root . '/include/page-head.php');
if (!is_string($head) || strpos($head, 'css/conversion-system.css') === false) $errors[] = 'Shared page head must load the conversion stylesheet.';
if ($errors) {
    fwrite(STDERR, "FAIL website conversion contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS website conversion copy and mobile contracts.\n";
