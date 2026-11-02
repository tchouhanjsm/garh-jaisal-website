<?php
/** Contract for the shared guest decision registry and page integrations. */
$root = dirname(__DIR__, 2);
$errors = [];
$registryPath = $root . '/include/guest-decision-content.php';
$registry = is_file($registryPath) ? require $registryPath : null;
$contexts = ['home', 'stay', 'dining', 'arrival', 'contact'];

if (!is_array($registry)) {
    $errors[] = 'Guest decision registry is missing or does not return an array.';
} else {
    foreach ($contexts as $context) {
        $panel = $registry[$context] ?? null;
        if (!is_array($panel)) {
            $errors[] = 'Missing guest decision context: ' . $context;
            continue;
        }
        foreach (['title', 'intro', 'cta_href', 'cta_label', 'cta_id', 'cta_location'] as $key) {
            if (!isset($panel[$key]) || !is_string($panel[$key]) || trim($panel[$key]) === '') {
                $errors[] = $context . ' must define non-empty ' . $key;
            }
        }
        foreach (['shown', 'confirm'] as $group) {
            if (!isset($panel[$group]) || !is_array($panel[$group]) || $panel[$group] === []) {
                $errors[] = $context . ' must define a non-empty ' . $group . ' group';
                continue;
            }
            foreach ($panel[$group] as $index => $item) {
                if (!is_array($item)
                    || !is_string($item['label'] ?? null) || trim($item['label']) === ''
                    || !is_string($item['detail'] ?? null) || trim($item['detail']) === '') {
                    $errors[] = $context . '.' . $group . '[' . $index . '] needs a label and detail';
                }
            }
        }
        $href = (string) ($panel['cta_href'] ?? '');
        if (preg_match('#^(?:javascript:|data:|https?://)#i', $href)) {
            $errors[] = $context . ' primary CTA must remain a same-site path or fragment';
        }
    }
}

$componentPath = $root . '/include/ui/guest-decision-panel.php';
$component = is_file($componentPath) ? file_get_contents($componentPath) : false;
if (!is_string($component)) {
    $errors[] = 'Guest decision UI partial is missing or unreadable.';
} else {
    foreach ([
        'htmlspecialchars((string) $value, ENT_QUOTES, \'UTF-8\')',
        'data-guest-decision-context=',
        'guestDecisionPanel[\'shown\']',
        'guestDecisionPanel[\'confirm\']',
        'Published on this site',
        'Confirm with the hotel',
        'target="_blank" rel="noopener"',
        'data-cta-id=',
        'data-cta-location=',
    ] as $needle) {
        if (strpos($component, $needle) === false) $errors[] = 'Guest decision partial missing contract: ' . $needle;
    }
}

$routeContracts = [
    'index.php' => ['guestDecisionContext = \'home\'', 'guest-decision-panel.php'],
    'room.php' => ['guestDecisionContext = \'stay\'', 'contact.php?topic=stay', 'Check live rates'],
    'restaurant.php' => ['guestDecisionContext = \'dining\'', 'guest-decision-panel.php'],
    'direction.php' => ['guestDecisionContext = \'arrival\'', 'guest-decision-panel.php'],
    'contact.php' => ['guestDecisionContext = \'contact\'', 'id="contact-form"', 'contactTopicKey === \'stay\''],
];
foreach ($routeContracts as $relative => $needles) {
    $path = $root . '/' . $relative;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) {
        $errors[] = $relative . ' is missing or unreadable';
        continue;
    }
    foreach ($needles as $needle) {
        if (strpos($source, $needle) === false) $errors[] = $relative . ' missing guest decision integration: ' . $needle;
    }
}

$topicsPath = $root . '/include/contact-topics.php';
$topics = is_file($topicsPath) ? require $topicsPath : null;
if (!is_array($topics) || !isset($topics['stay']['message'])
    || strpos((string) $topics['stay']['message'], 'cancellation policy') === false
    || strpos((string) $topics['stay']['message'], 'maximum occupancy') === false) {
    $errors[] = 'The allowlisted stay topic must ask about occupancy and cancellation without asserting their values.';
}

$trackingPath = $root . '/js/garh-v2.js';
$tracking = is_file($trackingPath) ? file_get_contents($trackingPath) : false;
if (!is_string($tracking)
    || strpos($tracking, 'room_details_enquiry_click') === false
    || strpos($tracking, "destination.searchParams.get('topic') === 'stay'") === false) {
    $errors[] = 'Room details enquiry intent must be attributed without guest data.';
}

$stylePath = $root . '/css/conversion-system.css';
$style = is_file($stylePath) ? file_get_contents($stylePath) : false;
if (!is_string($style)) {
    $errors[] = 'Shared conversion stylesheet is missing.';
} else {
    foreach (['.guest-decision', '.guest-decision__columns', '@media (max-width: 760px)', ':focus-visible', 'prefers-reduced-motion'] as $needle) {
        if (strpos($style, $needle) === false) $errors[] = 'Shared conversion stylesheet missing component contract: ' . $needle;
    }
}

if ($errors) {
    fwrite(STDERR, "FAIL guest decision content contract:\\n - " . implode("\\n - ", $errors) . "\\n");
    exit(1);
}
echo "PASS guest decision content registry and rendering contract.\\n";
