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
    'guided-jaisalmer-tour.php' => ['half-day-jaisalmer-tour', 'full-day-jaisalmer-tour'],
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
    if (in_array($path, ['desert-safari.php', 'guided-jaisalmer-tour.php'], true)) {
        if (strpos($source, 'foreach ($items as $slug)') === false
            || strpos($source, '\'id\' => $slug') === false
            || strpos($source, '\'url\' => $slug . \'.php\'') === false
            || strpos($source, 'experience-card-detail-cta') === false
            || strpos($source, 'More details →') === false) {
            $errors[] = $path . ': live experiences must be rendered from the registry with an explicit details-page CTA';
        }
        foreach ($ids as $id) {
            $candidate = $registry[$id] ?? null;
            if (!is_array($candidate) || ($candidate['status'] ?? '') !== 'live'
                || ($candidate['duration_type'] ?? 'hub') === 'hub'
                || !is_file($root . '/' . $id . '.php')) {
                $errors[] = $path . ': expected live experience detail route missing from the registry: ' . $id;
            }
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

$comparisonRequirements = [
    'sleep-under-the-stars' => [
        'label' => 'Open-sky overnight',
        'fields' => ['Sleep setup', 'Bathroom'],
    ],
    'swiss-tent-safari' => [
        'label' => 'Swiss tent overnight',
        'fields' => ['Sleep setup', 'Bathroom'],
    ],
];
foreach ($comparisonRequirements as $slug => $requirements) {
    $experience = $registry[$slug] ?? null;
    if (!is_array($experience) || ($experience['status'] ?? '') !== 'live') {
        $errors[] = 'Overnight comparison must reference a live experience: ' . $slug;
        continue;
    }
    if (($experience['comparison_label'] ?? '') !== $requirements['label']) {
        $errors[] = $slug . ': missing approved comparison label';
    }
    $facts = $experience['comparison_facts'] ?? [];
    $labels = [];
    foreach ($facts as $fact) {
        if (!is_array($fact) || trim((string) ($fact['label'] ?? '')) === '' || trim((string) ($fact['value'] ?? '')) === '') {
            $errors[] = $slug . ': comparison facts require non-empty labels and values';
            continue;
        }
        $labels[] = (string) $fact['label'];
    }
    foreach ($requirements['fields'] as $field) {
        if (!in_array($field, $labels, true)) {
            $errors[] = $slug . ': missing registry-backed comparison field ' . $field;
        }
    }
}
if (is_string($catalogueSource)) {
    if (strpos($catalogueSource, "if (\$campSlug === 'sleep-under-the-stars')") !== false) {
        $errors[] = 'Overnight comparison copy must be registry-driven, not duplicated by slug';
    }
    if (strpos($catalogueSource, "foreach ((\$camp['comparison_facts'] ?? []) as \$comparisonFact)") === false) {
        $errors[] = 'Catalogue comparison must render the shared registry comparison facts';
    }
}
if (!is_string($tripScriptSource) || strpos($tripScriptSource, "event.key !== 'Escape'") === false || strpos($tripScriptSource, 'setPanelOpen(false, true);') === false) {
    $errors[] = 'Trip tray must close on Escape and restore focus to its visible trigger';
}
if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    }
    exit(1);
}
echo "PASS: source-backed discovery hubs route to the session-only Trip shortlist without creating offers or server-side leads.\n";
