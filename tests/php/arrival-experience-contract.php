<?php
/** Contract for the premium Directions/Arrival batch on PR #161. */
$root = dirname(__DIR__, 2);
$errors = [];
$directionPath = $root . '/direction.php';
$direction = is_file($directionPath) ? file_get_contents($directionPath) : false;
if (!is_string($direction)) {
    $errors[] = 'direction.php is missing or unreadable';
} else {
    foreach ([
        "'css/arrival.css', 'css/arrival-experience.css'",
        '$arrivalAddress =',
        '$arrivalDriverNote = implode("\\n",',
        'id="arrival-driver-title"',
        'data-arrival-copy=',
        'data-arrival-copy-status',
        'booking-arrival-final',
        'arrival-final-cta',
        'js/arrival-guide.js',
        '5–6 minute walk',
        '3–4 min walk',
        'Rickshaws often stay outside from 8:00 AM to 8:00 PM.',
    ] as $needle) {
        if (strpos($direction, $needle) === false) $errors[] = 'direction.php missing marker: ' . $needle;
    }
}
$cssPath = $root . '/css/arrival-experience.css';
$css = is_file($cssPath) ? file_get_contents($cssPath) : false;
if (!is_string($css)) {
    $errors[] = 'css/arrival-experience.css is missing or unreadable';
} else {
    foreach (['.arrival-driver-card','.arrival-driver-copy-button[hidden]','.arrival-route-steps','.arrival-map','@media (max-width: 760px)','@media (prefers-reduced-motion: reduce)',':focus-visible'] as $needle) {
        if (strpos($css, $needle) === false) $errors[] = 'arrival stylesheet missing marker: ' . $needle;
    }
}
$jsPath = $root . '/js/arrival-guide.js';
$js = is_file($jsPath) ? file_get_contents($jsPath) : false;
if (!is_string($js)) {
    $errors[] = 'js/arrival-guide.js is missing or unreadable';
} else {
    foreach (['navigator.clipboard.writeText',"document.execCommand('copy')",'data-arrival-copy-status','button.hidden = false','aria-hidden'] as $needle) {
        if (strpos($js, $needle) === false) $errors[] = 'arrival script missing marker: ' . $needle;
    }
}
if (strpos($js ?: '', 'fetch(') !== false || strpos($js ?: '', 'XMLHttpRequest') !== false) {
    $errors[] = 'arrival script must not make network requests';
}
if ($errors) {
    fwrite(STDERR, "FAIL premium arrival experience contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS premium arrival experience contract.\n";
