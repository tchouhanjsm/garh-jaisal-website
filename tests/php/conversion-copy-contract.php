<?php
/** Regression contract for conversion-led copy, responsive controls and a11y. */
$root = dirname(__DIR__, 2);
$errors = [];
$required = [
    'index.php' => ['Wake up inside Jaisalmer Fort.', 'Heritage stay · Inside Jaisalmer Fort', 'individually styled rooms', 'home-hero-facts', 'Explore the rooms', 'conversion-journey', 'home-planning-panel', 'id="arrival-guide"', 'conversion-faq', 'Check rooms &amp; rates', 'guided-jaisalmer-tour.php', 'desert-safari.php', 'data-contact-handoff'],
    'about.php' => ['Stay inside the Fort. Feel closer to Jaisalmer.', 'Good hospitality begins before you arrive.', 'conversion-promise'],
    'desert-safari.php' => ['Find the Jaisalmer desert safari that fits your trip.', 'desert-options', 'conversion-checklist', 'Confirm my safari details'],
    'guided-jaisalmer-tour.php' => ['See Jaisalmer with a guide, at your pace.', 'guided-options', 'Plan my guided tour'],
    'city-tour.php' => ['Discover the stories inside Jaisalmer Fort.', 'Ask about route, guide'],
    'room.php' => ['Find your room inside Jaisalmer Fort.', 'data-contact-handoff'],
    'restaurant.php' => ['Rooftop dining in the heart of Jaisalmer Fort.', 'Enquire about a table'],
    'include/experience-page.php' => ['An enquiry is not a confirmed booking.', 'Build the day around what you want to discover.', "require __DIR__ . '/ui/planning-path.php';"],
    'include/editorial-guide-page.php' => ["require __DIR__ . '/ui/planning-path.php';"],
    'include/ui/planning-path.php' => ['planning-path-grid', 'Related planning pages', 'htmlspecialchars', 'planning-paths-data.php', 'contact-topics.php', 'allowlisted contact topic'],
    'include/contact-topics.php' => ['room-availability', 'car-arrangements', 'helpful_details', 'current price'],
    'include/planning-paths-data.php' => ['Reviewed planning destinations', 'same-site PHP pages', 'Review room features'],
    'include/footer-v2.php' => ['Let Jaisalmer begin inside the Fort.', 'Check dates &amp; current rates', 'data-contact-handoff="general"'],
    'js/garh-v2.js' => ['contactHandoffContexts', 'contact_handoff_click', 'destination_path', 'enquiry_context'],
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

$homeSource = file_get_contents($root . '/index.php');
if (!is_string($homeSource)) {
    $errors[] = 'index.php: file could not be read for homepage hierarchy checks';
} else {
    if (substr_count($homeSource, 'id="arrival-guide"') !== 1) {
        $errors[] = 'Homepage must expose one canonical arrival-guide anchor; duplicate arrival sections are not allowed';
    }
    if (strpos($homeSource, '<section class="section arrival" id="arrival-guide"') === false
        || strpos($homeSource, 'Arriving at the fort is part of the experience.') === false) {
        $errors[] = 'Homepage must retain the detailed, addressable arrival guide section';
    }
    if (strpos($homeSource, 'home-planning-panel') === false) {
        $errors[] = 'Homepage must provide the consolidated editorial planning panel';
    }
}


// The planning-path registry is a publication contract for every sitemap route.
$contactTopicsPath = $root . '/include/contact-topics.php';
$contactTopics = is_file($contactTopicsPath) ? require $contactTopicsPath : [];
if (!is_array($contactTopics)) {
    $errors[] = 'include/contact-topics.php must return an allowlist array';
    $contactTopics = [];
}
foreach ($contactTopics as $topicSlug => $topic) {
    if (
        !is_string($topicSlug)
        || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $topicSlug)
        || !is_array($topic)
        || trim((string) ($topic['label'] ?? '')) === ''
        || trim((string) ($topic['message'] ?? '')) === ''
    ) {
        $errors[] = 'Contact topic must have a valid slug, label and prefilled message';
        continue;
    }
    $helpfulDetails = $topic['helpful_details'] ?? null;
    if (!is_array($helpfulDetails) || $helpfulDetails === []) {
        $errors[] = 'Contact topic needs helpful-details guidance: ' . $topicSlug;
        continue;
    }
    foreach ($helpfulDetails as $detail) {
        if (!is_string($detail) || trim($detail) === '') {
            $errors[] = 'Contact helpful detail must be non-empty text: ' . $topicSlug;
        }
    }
}
foreach (['room-availability', 'car-arrangements'] as $requiredTopic) {
    if (!isset($contactTopics[$requiredTopic])) {
        $errors[] = 'Contextual enquiry handoff is missing topic: ' . $requiredTopic;
    }
}
$planningRegistryPath = $root . '/include/planning-paths-data.php';
if (!is_file($planningRegistryPath)) {
    $errors[] = 'include/planning-paths-data.php: reviewed planning destination registry is missing';
    $planningRegistry = [];
} else {
    $planningRegistry = require $planningRegistryPath;
    if (!is_array($planningRegistry)) {
        $errors[] = 'Planning destination registry must return an array';
        $planningRegistry = [];
    }
}
$sitemapPath = $root . '/sitemap.xml';
$sitemapSource = is_file($sitemapPath) ? file_get_contents($sitemapPath) : false;
$sitemapRoutes = [];
if (!is_string($sitemapSource)) {
    $errors[] = 'sitemap.xml: file could not be read for planning-path coverage';
} else {
    preg_match_all('~<loc>\\s*https?://[^/]+([^<\\s]*)\\s*</loc>~i', $sitemapSource, $sitemapMatches);
    foreach ($sitemapMatches[1] as $urlPath) {
        $route = parse_url($urlPath, PHP_URL_PATH);
        $sitemapRoutes[] = is_string($route) && $route !== '' ? $route : '/';
    }
    $sitemapRoutes = array_values(array_unique($sitemapRoutes));
}
foreach (array_diff($sitemapRoutes, array_keys($planningRegistry)) as $missingRoute) {
    $errors[] = 'Planning destination registry has no entry for sitemap route: ' . $missingRoute;
}
foreach (array_diff(array_keys($planningRegistry), $sitemapRoutes) as $unpublishedRoute) {
    $errors[] = 'Planning destination registry contains a route outside the sitemap: ' . $unpublishedRoute;
}
foreach ($planningRegistry as $route => $entry) {
    if (!is_string($route) || $route === '' || !is_array($entry)) {
        $errors[] = 'Planning registry keys must be route strings with configuration arrays';
        continue;
    }
    if (trim((string) ($entry['title'] ?? '')) === '' || trim((string) ($entry['intro'] ?? '')) === '') {
        $errors[] = 'Planning registry route requires a title and introduction: ' . $route;
    }
    $links = $entry['links'] ?? null;
    if (!is_array($links) || count($links) !== 3) {
        $errors[] = 'Planning registry route must have exactly three links: ' . $route;
        continue;
    }
    $hrefs = [];
    $destinationPaths = [];
    foreach ($links as $link) {
        if (!is_array($link)) {
            $errors[] = 'Planning registry contains a malformed link: ' . $route;
            continue;
        }
        $label = trim((string) ($link['label'] ?? ''));
        $href = trim((string) ($link['href'] ?? ''));
        $description = trim((string) ($link['description'] ?? ''));
        $destinationPath = parse_url($href, PHP_URL_PATH);
        $query = parse_url($href, PHP_URL_QUERY);
        $isDirectRoute = is_string($destinationPath)
            && preg_match('~^[a-z0-9-]+\\.php$~iD', $destinationPath) === 1
            && $query === null;
        $isContextualContact = false;
        if (
            is_string($destinationPath)
            && $destinationPath === 'contact.php'
            && is_string($query)
            && preg_match('/^topic=([a-z0-9]+(?:-[a-z0-9]+)*)$/iD', $query, $topicMatch) === 1
            && isset($contactTopics[$topicMatch[1]])
        ) {
            $isContextualContact = true;
        }
        if ($label === '' || $description === '' || (!$isDirectRoute && !$isContextualContact)) {
            $errors[] = 'Planning registry links require copy and a same-site PHP route or allowlisted contact topic: ' . $route;
            continue;
        }
        if (!is_file($root . '/' . $destinationPath)) {
            $errors[] = 'Planning registry destination does not exist: ' . $destinationPath;
        }
        if ('/' . $destinationPath === $route) {
            $errors[] = 'Planning registry must not link a route to itself: ' . $route;
        }
        $hrefs[] = $href;
        $destinationPaths[] = $destinationPath;
    }
    if (count(array_unique($hrefs)) !== 3 || count(array_unique($destinationPaths)) !== 3) {
        $errors[] = 'Planning registry destinations must be distinct by href and route path: ' . $route;
    }
}


// Contextual contact handoffs are production markup, not just an analytics event name.
$handoffSourcePaths = [
    'index.php',
    'room.php',
    'pack.php',
    'catalogue.php',
    'direction.php',
    'roof.php',
    'restaurant.php',
    'book-rooms-in-jaisalmer.php',
    'camping.php',
    'car-rental.php',
    'city-tour.php',
    'car-tours.php',
    'custom-itinerary.php',
    'desert-safari.php',
    'guided-jaisalmer-tour.php',
    'include/experience-page.php',
    'include/ui/experience-conversion-checkpoint.php',
    'include/footer-v2.php',
];
$analyticsSource = file_get_contents($root . '/js/garh-v2.js');
$allowedHandoffContexts = [];
if (
    is_string($analyticsSource)
    && preg_match('/const contactHandoffContexts = new Set\(\[(.*?)\]\);/s', $analyticsSource, $handoffContextMatch) === 1
) {
    preg_match_all('/\'([a-z0-9_]+)\'/D', $handoffContextMatch[1], $handoffContextValues);
    $allowedHandoffContexts = $handoffContextValues[1] ?? [];
}
if ($allowedHandoffContexts === []) {
    $errors[] = 'Analytics JavaScript must expose the approved contact-handoff context allowlist';
}
foreach ($handoffSourcePaths as $relative) {
    $sourcePath = $root . '/' . $relative;
    $source = is_file($sourcePath) ? file_get_contents($sourcePath) : false;
    if (!is_string($source)) {
        $errors[] = $relative . ': contact handoff source could not be read';
        continue;
    }
    if (strpos($source, 'data-contact-handoff=') === false) {
        $errors[] = $relative . ': expected a controlled data-contact-handoff marker';
        continue;
    }
    if (preg_match_all('/data-contact-handoff="([a-z0-9_]+)"/', $source, $handoffMarkers)) {
        foreach ($handoffMarkers[1] as $handoffContext) {
            if (!in_array($handoffContext, $allowedHandoffContexts, true)) {
                $errors[] = $relative . ': unapproved contact handoff context: ' . $handoffContext;
            }
        }
    }
}

if ($errors) {
    fwrite(STDERR, "FAIL website conversion contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS website conversion copy and mobile contracts.\n";
