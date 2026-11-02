<?php
/** Behavioral contract for guest decision data, links and route integration. */
$root = dirname(__DIR__, 2);
$errors = [];
$fail = static function ($message) use (&$errors) {
    $errors[] = $message;
};

require_once $root . '/include/guest-decision-links.php';

$fixtureConfig = [
    'booking_url' => 'https://book.example/booking/index?token=approved',
    'whatsapp_url' => 'https://wa.me/910000000000',
];
$internalCases = [
    ['contact.php?topic=stay', true],
    ['#contact-form', true],
    ['javascript:alert(1)', false],
    ['data:text/html,unsafe', false],
    ['//evil.example/path', false],
    ['https://evil.example/path', false],
    ['../mail.php', false],
    [' contact.php?topic=stay', false],
];
foreach ($internalCases as $case) {
    $actual = gj_guest_decision_safe_internal_href($case[0]);
    if (($actual !== '') !== $case[1]) {
        $fail('Internal destination validation failed for: ' . $case[0]);
    }
}

$externalCases = [
    ['https://book.example/booking/index?token=approved', true],
    ['https://wa.me/910000000000?text=Hello%20there', true],
    ['http://wa.me/910000000000', false],
    ['https://evil.example/booking/index?token=approved', false],
    ['https://book.example/other?token=approved', false],
    ['https://book.example/booking/index?token=changed', false],
    ['https://user:pass@wa.me/910000000000', false],
    ['//wa.me/910000000000', false],
];
foreach ($externalCases as $case) {
    $actual = gj_guest_decision_safe_external_href($case[0], $fixtureConfig);
    if (($actual !== '') !== $case[1]) {
        $fail('External destination validation failed for: ' . $case[0]);
    }
}

$registryPath = $root . '/include/guest-decision-content.php';
$registry = is_file($registryPath) ? require $registryPath : null;
$contexts = ['home', 'stay', 'dining', 'arrival', 'contact'];
if (!is_array($registry)) {
    $fail('Guest decision registry is missing or does not return an array.');
} else {
    foreach ($contexts as $context) {
        $panel = $registry[$context] ?? null;
        if (!is_array($panel)) {
            $fail('Missing guest decision context: ' . $context);
            continue;
        }
        foreach (['title', 'intro', 'cta_href', 'cta_label', 'cta_id', 'cta_location'] as $key) {
            if (!isset($panel[$key]) || !is_string($panel[$key]) || trim($panel[$key]) === '') {
                $fail($context . ' must define non-empty ' . $key);
            }
        }
        foreach (['shown', 'confirm'] as $group) {
            if (!isset($panel[$group]) || !is_array($panel[$group]) || $panel[$group] === []) {
                $fail($context . ' must define a non-empty ' . $group . ' group');
                continue;
            }
            foreach ($panel[$group] as $index => $item) {
                if (!is_array($item)
                    || !is_string($item['label'] ?? null) || trim($item['label']) === ''
                    || !is_string($item['detail'] ?? null) || trim($item['detail']) === '') {
                    $fail($context . '.' . $group . '[' . $index . '] needs a label and detail');
                }
            }
        }
        if (gj_guest_decision_safe_internal_href($panel['cta_href'] ?? '') === '') {
            $fail($context . ' primary CTA must remain a valid local route or fragment');
        }
    }
}

$rooms = require $root . '/include/rooms-data.php';
$stayDetail = (string) ($registry['stay']['shown'][0]['detail'] ?? '');
if (!is_array($rooms) || strpos($stayDetail, (string) count($rooms)) === false) {
    $fail('Stay decision summary must derive its room count from include/rooms-data.php.');
}
$roomFloors = [];
foreach ($rooms as $room) {
    if (is_array($room) && is_string($room['kicker'] ?? null)) {
        $roomFloors[] = trim($room['kicker']);
    }
}
foreach (array_unique(array_filter($roomFloors)) as $floor) {
    if (strpos($stayDetail, $floor) === false) {
        $fail('Stay decision summary is missing a floor from the room registry: ' . $floor);
    }
}

$arrivalShown = $registry['arrival']['shown'] ?? [];
foreach ($arrivalShown as $item) {
    $detail = strtolower((string) ($item['detail'] ?? ''));
    if (strpos($detail, 'vehicles cannot enter') !== false || strpos($detail, '300 metres') !== false) {
        $fail('Arrival decision panel repeats facts already presented in the route/driver instructions.');
    }
}

$componentPath = $root . '/include/ui/guest-decision-panel.php';
$component = is_file($componentPath) ? file_get_contents($componentPath) : false;
if (!is_string($component)) {
    $fail('Guest decision UI partial is missing or unreadable.');
} else {
    foreach ([
        "require_once dirname(__DIR__) . '/guest-decision-links.php'",
        'gj_guest_decision_safe_internal_href',
        'gj_guest_decision_safe_external_href',
        'htmlspecialchars(is_scalar($value) ? (string) $value, ENT_QUOTES,',
        'data-guest-decision-context=',
        'Published on this site',
        'Confirm with the hotel',
        'rel="noopener noreferrer"',
    ] as $needle) {
        if (strpos($component, $needle) === false) {
            $fail('Guest decision partial missing behavior contract: ' . $needle);
        }
    }
}

$routeContracts = [
    'index.php' => ["$" . "guestDecisionContext = 'home'", 'guest-decision-panel.php'],
    'room.php' => ["$" . "guestDecisionContext = 'stay'", 'contact.php?topic=stay', 'Check live rates'],
    'restaurant.php' => ["$" . "guestDecisionContext = 'dining'", 'guest-decision-panel.php'],
    'direction.php' => ["$" . "guestDecisionContext = 'arrival'", 'guest-decision-panel.php'],
    'contact.php' => ["$" . "guestDecisionContext = 'contact'", 'id="contact-form"', "contactTopicKey === 'stay'"],
];
foreach ($routeContracts as $relative => $needles) {
    $path = $root . '/' . $relative;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) {
        $fail($relative . ' is missing or unreadable');
        continue;
    }
    foreach ($needles as $needle) {
        if (strpos($source, $needle) === false) {
            $fail($relative . ' missing guest decision integration: ' . $needle);
        }
    }
}

$homepage = file_get_contents($root . '/index.php');
if (strpos($homepage, 'home-planning-confidence') !== false) {
    $fail('Homepage still contains the duplicate planning-confidence card section.');
}
$roomPage = file_get_contents($root . '/room.php');
if (strpos($roomPage, 'rooms-cta-inner') !== false) {
    $fail('Rooms page still contains the duplicate generic booking CTA band.');
}

$topicsPath = $root . '/include/contact-topics.php';
$topics = is_file($topicsPath) ? require $topicsPath : null;
if (!is_array($topics) || !isset($topics['stay']['message'])
    || strpos((string) $topics['stay']['message'], 'cancellation policy') === false
    || strpos((string) $topics['stay']['message'], 'maximum occupancy') === false) {
    $fail('The allowlisted stay topic must ask about occupancy and cancellation without asserting their values.');
}

$trackingPath = $root . '/js/garh-v2.js';
$tracking = is_file($trackingPath) ? file_get_contents($trackingPath) : false;
if (!is_string($tracking)
    || strpos($tracking, 'room_details_enquiry_click') === false
    || strpos($tracking, "destination.searchParams.get('topic') === 'stay' || destination.searchParams.has('room')") === false) {
    $fail('Both the generic stay topic and room-specific enquiry handoff must use privacy-safe room intent attribution.');
}

$stylePath = $root . '/css/conversion-system.css';
$style = is_file($stylePath) ? file_get_contents($stylePath) : false;
if (!is_string($style)) {
    $fail('Shared conversion stylesheet is missing.');
} else {
    foreach (['.guest-decision', '.guest-decision__columns', '@media (max-width: 760px)', ':focus-visible', 'prefers-reduced-motion', 'overflow-wrap: anywhere'] as $needle) {
        if (strpos($style, $needle) === false) {
            $fail('Shared conversion stylesheet missing component contract: ' . $needle);
        }
    }
}

if ($errors) {
    fwrite(STDERR, "FAIL guest decision content contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS guest decision data, destination safety, and route contracts.\n";
