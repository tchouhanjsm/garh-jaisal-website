<?php
/**
 * Guard the Sprint 32 discovery-to-shortlist contract.
 * Run with: php tests/php/experience-discovery.php
 */
$root = dirname(__DIR__, 2);
$registry = require $root . '/include/experience-data.php';
$errors = [];
$required = [
    'desert-safari.php' => ['sunset-camel-safari', 'cultural-safari', 'sleep-under-the-stars', 'swiss-tent-safari'],
    'car-tours.php' => ['guided-jaisalmer-tour', 'fort-walk', 'car-arrangements'],
];
foreach ($required as $path => $ids) {
    $source = file_get_contents($root . '/' . $path);
    if (!is_string($source)) {
        $errors[] = $path . ': could not read hub page';
        continue;
    }
    if (strpos($source, 'catalogue.php') === false || strpos($source, 'Build your trip shortlist') === false) {
        $errors[] = $path . ': missing contextual link to the unified Trip shortlist';
    }
    if ($path === 'desert-safari.php') {
        if (strpos($source, 'foreach ($items as $slug)') === false || strpos($source, '\'id\' => $slug') === false || strpos($source, '\'url\' => $slug . \'.php\'') === false) {
            $errors[] = 'desert-safari.php: shared experience loop must generate each shortlist item from the published slug';
        }
    } else {
        foreach ($ids as $id) {
            if (strpos($source, "'id' => '" . $id . "'") === false) {
                $errors[] = $path . ': missing Trip shortlist item ' . $id;
            }
        }
    }
}
foreach (['sunset-camel-safari', 'cultural-safari', 'sleep-under-the-stars', 'swiss-tent-safari', 'guided-jaisalmer-tour'] as $slug) {
    if (!isset($registry[$slug]) || ($registry[$slug]['status'] ?? '') !== 'live') {
        $errors[] = 'Expected catalogue/hub selection is not backed by a live experience: ' . $slug;
    }
}
$catalogueSource = file_get_contents($root . '/catalogue.php');
$tripScriptSource = file_get_contents($root . '/js/garh-v2.js');
if (!is_string($catalogueSource) || strpos($catalogueSource, 'gj-trip-items-v1') !== false) {
    // Storage key belongs to JS; server-side catalogue must not persist guest selections.
    $errors[] = 'catalogue.php must not add server-side Trip storage';
}
if (!is_string($tripScriptSource) || strpos($tripScriptSource, 'window.sessionStorage.getItem(storageKey)') === false || strpos($tripScriptSource, 'window.sessionStorage.setItem(storageKey') === false) {
    $errors[] = 'Trip selections must remain in tab-scoped session storage';
}
$card = file_get_contents($root . '/include/ui/product-card.php');
if (!is_string($card) || strpos($card, 'Current price on request') === false || strpos($card, 'product-card-planning-note') === false) {
    $errors[] = 'Catalogue product card must retain price-on-request and shortlist guidance';
}
if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    }
    exit(1);
}
echo "PASS: source-backed discovery hubs route to the session-only Trip shortlist without creating offers or server-side leads.\n";
