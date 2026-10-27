<?php
/**
 * Guard contextual section navigation and core data on the live experience detail pages.
 */
$root = dirname(__DIR__, 2);
require $root . '/include/experience-data.php';
$registry = isset($experiences) && is_array($experiences) ? $experiences : [];
$errors = [];
$slugs = [
    'sunset-camel-safari',
    'sleep-under-the-stars',
    'swiss-tent-safari',
    'cultural-safari',
    'half-day-jaisalmer-tour',
    'full-day-jaisalmer-tour',
];
$requiredFields = ['slug', 'status', 'duration_type', 'summary', 'at_a_glance', 'timeline_label', 'timeline_note', 'timeline', 'included', 'before_book', 'faq'];
foreach ($slugs as $slug) {
    $experience = $registry[$slug] ?? null;
    if (!is_array($experience) || ($experience['status'] ?? '') !== 'live') {
        $errors[] = $slug . ' must remain a published live experience.';
        continue;
    }
    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $experience)) {
            $errors[] = $slug . ' is missing required display data: ' . $field;
        }
    }
    foreach (['at_a_glance', 'timeline', 'included', 'faq'] as $field) {
        if (empty($experience[$field]) || !is_array($experience[$field])) {
            $errors[] = $slug . ' must provide a non-empty ' . $field . ' section.';
        }
    }
    foreach (($experience['timeline'] ?? []) as $index => $step) {
        foreach (['step', 'title', 'text'] as $field) {
            if (trim((string) ($step[$field] ?? '')) === '') {
                $errors[] = $slug . ' timeline item ' . ($index + 1) . ' is missing ' . $field . '.';
            }
        }
    }
    $route = $root . '/' . $slug . '.php';
    $routeSource = is_file($route) ? file_get_contents($route) : false;
    if (!is_string($routeSource) || strpos($routeSource, 'include/experience-page.php') === false) {
        $errors[] = $slug . '.php must use the shared experience detail template.';
    }
}

$template = file_get_contents($root . '/include/experience-page.php');
$css = file_get_contents($root . '/css/experiences.css');
$markers = [
    '<nav class="experience-section-nav" aria-label="Experience page sections">',
    'href="#experience-overview"',
    'href="#experience-itinerary"',
    'href="#experience-details"',
    'href="#experience-faq"',
    'id="experience-overview"',
    'id="experience-itinerary"',
    'id="experience-details"',
    'id="experience-faq"',
];
if (!is_string($template)) {
    $errors[] = 'Shared experience detail template is unreadable.';
} else {
    foreach ($markers as $marker) {
        if (strpos($template, $marker) === false) $errors[] = 'Experience detail template is missing navigation marker: ' . $marker;
    }
    if (strpos($template, 'href="#experience-photo-gallery"') === false || strpos($template, 'id="experience-photo-gallery"') === false) {
        $errors[] = 'Optional photo-gallery navigation must point to the optional gallery section.';
    }
    if (substr_count($template, 'count($experienceGallerySlides) > 1') < 2) {
        $errors[] = 'Photo-gallery navigation and target must share the same rendered condition.';
    }
    if (substr_count($template, '!empty($experience[\'faq\'])') < 2) {
        $errors[] = 'FAQ navigation and target must share the same rendered condition.';
    }
}
foreach (['.experience-section-nav', '.experience-section-nav a:focus-visible', '@media(max-width:600px)', 'prefers-reduced-motion'] as $marker) {
    if (!is_string($css) || strpos($css, $marker) === false) $errors[] = 'Experience navigation stylesheet is missing marker: ' . $marker;
}
if (is_string($css) && preg_match('/\.experience-section-nav\s*\{[^}]*position\s*:\s*sticky/s', $css)) {
    $errors[] = 'Experience section navigation must remain in normal document flow.';
}
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo 'PASS: six live experience detail pages have complete core sections and contextual navigation targets.' . PHP_EOL;
