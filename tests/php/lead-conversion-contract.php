<?php
/** Guard success-only lead conversion events and privacy-safe context attribution. */
$root = dirname(__DIR__, 2);
$errors = [];
$read = static function ($relative) use ($root, &$errors) {
    $path = $root . '/' . $relative;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) { $errors[] = $relative . ': source file is missing or unreadable'; return ''; }
    return $source;
};
$thankYou = $read('thank-you.php');
$contact = $read('contact.php');
$analytics = $read('js/garh-v2.js');
$expectations = [
    ['thank-you.php uses shared page head', strpos($thankYou, "require __DIR__ . '/include/page-head.php';") !== false],
    ['thank-you remains noindex', strpos($thankYou, "'robots' => 'noindex,nofollow'") !== false],
    ['success event uses GA4 generate_lead', strpos($thankYou, "window.gtag('event', 'generate_lead'") !== false],
    ['one-time session flags are consumed', strpos($thankYou, 'unset($_SESSION[\'enquiry_received\'], $_SESSION[\'enquiry_reference\'], $_SESSION[\'enquiry_notification_sent\']);') !== false],
    ['experience context exposes registered slug only', strpos($contact, 'data-experience-slug=') !== false],
    ['room context exposes validated room slug', strpos($contact, 'data-room-slug=') !== false],
    ['experience intent is classified', strpos($analytics, "enquiryContext = 'experience'") !== false],
    ['experience slug is added as a validated dimension', strpos($analytics, 'contextParameters.experience_slug = experienceSlug') !== false],
    ['room slug is added as a validated dimension', strpos($analytics, 'contextParameters.room_slug = roomSlug') !== false],
    ['campaign values persist only in per-tab session storage', strpos($analytics, 'gj_conversion_attribution_v1') !== false && strpos($analytics, 'window.sessionStorage.setItem') !== false],
    ['campaign attribution is limited to approved UTM fields', strpos($analytics, "const attributionKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content']") !== false],
    ['stable CTA IDs are validated before entering analytics', strpos($analytics, 'ctaMetadataForLink') !== false && strpos($analytics, 'metadata.cta_id = id') !== false],
    ['same-site table request handoff emits a dedicated intent event', strpos($analytics, "trackIntentEvent('table_enquiry_click'") !== false],
    ['table request click is scoped to the restaurant reserve anchor', strpos($analytics, "destination.hash === '#reserve'") !== false],
];
foreach ($expectations as [$description, $passed]) { if (!$passed) $errors[] = $description; }
$eventPosition = strpos($thankYou, "window.gtag('event', 'generate_lead'");
$lastSuccessGate = $eventPosition === false ? false : strrpos(substr($thankYou, 0, $eventPosition), '<?php if ($enquiryReceived): ?>');
$eventClose = $eventPosition === false ? false : strpos($thankYou, '<?php endif; ?>', $eventPosition);
$consumePosition = strpos($thankYou, 'unset($_SESSION[\'enquiry_received\']');
if ($eventPosition === false || $lastSuccessGate === false || $eventClose === false || $consumePosition === false || $consumePosition > $eventPosition) {
    $errors[] = 'generate_lead must be gated by a recorded enquiry and the one-time session flag must be consumed first';
} else {
    $scriptEnd = strpos($thankYou, '</script>', $eventPosition);
    $eventScript = $scriptEnd === false ? '' : substr($thankYou, $eventPosition, $scriptEnd - $eventPosition);
    foreach (["'name'", "'email'", "'phone'", "'message'", 'enquiryReference'] as $sensitiveField) {
        if (strpos($eventScript, $sensitiveField) !== false) $errors[] = 'generate_lead must not transmit sensitive field: ' . $sensitiveField;
    }
}
if ($errors) {
    fwrite(STDERR, "FAIL lead conversion contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS: success-only lead conversion, one-time guard and safe enquiry context.\n";
