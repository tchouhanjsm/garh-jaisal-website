<?php
/** Registry-backed room/experience/topic attribution and safe retry contract. */
require_once __DIR__ . '/../../include/enquiry-context.php';
$errors = [];
$assert = static function (bool $ok, string $message) use (&$errors): void {
    if (!$ok) {
        $errors[] = $message;
    }
};
$expectGeneral = static function ($type, $slug) use ($assert): void {
    $value = gj_resolve_enquiry_context($type, $slug);
    $assert(($value['type'] ?? '') === 'general', 'Unknown or unsafe context must fall back to general.');
    $assert(($value['label'] ?? '') === 'General enquiry', 'Fallback label must be server-derived.');
};
$rooms = require __DIR__ . '/../../include/rooms-data.php';
$assert(is_array($rooms) && count($rooms) > 0, 'Room registry is available.');
foreach ($rooms as $room) {
    $name = (string) ($room['name'] ?? '');
    $slug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-');
    $ctx = gj_resolve_enquiry_context('room', $slug);
    $assert(($ctx['type'] ?? '') === 'room' && ($ctx['slug'] ?? '') === $slug, 'Room resolves by expected slug: ' . $slug);
    $assert(($ctx['label'] ?? '') === $name, 'Room display label matches registry: ' . $slug);
}
$experiences = require __DIR__ . '/../../include/experience-data.php';
$assert(is_array($experiences), 'Experience registry is available.');
foreach ($experiences as $slug => $experience) {
    if (!is_array($experience) || ($experience['status'] ?? '') !== 'live'
        || ($experience['duration_type'] ?? 'hub') === 'hub'
        || !is_string($experience['h1'] ?? null) || trim($experience['h1']) === '') {
        continue;
    }
    $ctx = gj_resolve_enquiry_context('experience', (string) $slug);
    $assert(($ctx['type'] ?? '') === 'experience', 'Live detail resolves as experience: ' . $slug);
    $assert(($ctx['label'] ?? '') === trim($experience['h1']), 'Experience label matches registry: ' . $slug);
}
$topics = require __DIR__ . '/../../include/contact-topics.php';
$assert(is_array($topics) && count($topics) > 0, 'Topic registry is available.');
foreach ($topics as $slug => $topic) {
    $ctx = gj_resolve_enquiry_context('topic', (string) $slug);
    $assert(($ctx['type'] ?? '') === 'topic' && ($ctx['label'] ?? '') === ($topic['label'] ?? null), 'Topic resolves from registry: ' . $slug);
}
$expectGeneral('room', str_repeat('.', 5) . '/unrecognised');
$expectGeneral('room', 'unknown-room');
$expectGeneral('rooms', 'unknown-room,green-room');
$expectGeneral('rooms', 'green-room,green-room');
$expectGeneral('rooms', 'green-room');

$roomSlugs = [];
$roomNamesBySlug = [];
foreach ($rooms as $roomRecord) {
    $roomSlug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) ($roomRecord['name'] ?? ''))), '-');
    if ($roomSlug !== '') {
        $roomSlugs[] = $roomSlug;
        $roomNamesBySlug[$roomSlug] = (string) $roomRecord['name'];
    }
}
$twoRoomSlugs = array_slice(array_values(array_unique($roomSlugs)), 0, 2);
if (count($twoRoomSlugs) === 2) {
    $shortlistContext = gj_resolve_enquiry_context('rooms', implode(',', $twoRoomSlugs));
    $assert(($shortlistContext['type'] ?? '') === 'rooms', 'Two valid room slugs resolve as a room shortlist.');
    $assert(($shortlistContext['type_label'] ?? '') === 'Room shortlist', 'Shortlist context uses a clear owner-facing type label.');
    $assert(($shortlistContext['label'] ?? '') === implode(', ', array_map(static function ($slug) use ($roomNamesBySlug) { return $roomNamesBySlug[$slug]; }, $twoRoomSlugs)), 'Room shortlist label is derived from the room registry in selected order.');
    $retryUrl = gj_contact_context_return_url($shortlistContext, 'captcha');
    parse_str((string) parse_url($retryUrl, PHP_URL_QUERY), $retryQuery);
    $assert(($retryQuery['rooms'] ?? []) === $twoRoomSlugs, 'Room shortlist retry preserves all selected room slugs.');
    $assert(($retryQuery['error'] ?? '') === 'captcha', 'Room shortlist retry preserves the safe error state.');
}
$expectGeneral('experience', 'draft-or-unknown');
$expectGeneral('topic', 'html-tag-like-value');
$expectGeneral(['room'], 'green-room');
$expectGeneral('topic', ['arrival']);
$room = gj_resolve_enquiry_context('room', 'green-room');
$assert(gj_contact_context_return_url($room, 'invalid') === 'contact.php?room=green-room&error=invalid', 'Room retry preserves context.');
$topic = gj_resolve_enquiry_context('topic', 'arrival');
$assert(gj_contact_context_return_url($topic, 'captcha') === 'contact.php?topic=arrival&error=captcha', 'CAPTCHA retry preserves context.');
$experience = gj_resolve_enquiry_context('experience', 'sunset-camel-safari');
if (($experience['type'] ?? '') === 'experience') {
    $assert(gj_contact_context_return_url($experience, 'send') === 'contact.php?experience=sunset-camel-safari&error=send', 'Experience retry preserves context.');
}
$assert(gj_contact_context_return_url(gj_general_enquiry_context(), 'invalid') === 'contact.php?error=invalid', 'General retry remains generic.');
$root = dirname(__DIR__, 2);
$contactSource = file_get_contents($root . '/contact.php');
$mailSource = file_get_contents($root . '/mail.php');
$assert(is_string($contactSource) && strpos($contactSource, 'name="enquiry_context_token"') !== false, 'Contact form includes opaque token.');
$assert(is_string($contactSource) && strpos($contactSource, 'contact_context_tokens') !== false, 'Context token is session-bound.');
$assert(is_string($contactSource) && strpos($contactSource, '$contactRooms') !== false && strpos($contactSource, 'contact-rooms-context') !== false, 'Contact renders allowlisted multi-room shortlist context.');
$assert(is_string($contactSource) && strpos($contactSource, 'Room shortlist enquiry:') !== false, 'Contact explains the selected-room enquiry and current-rate confirmation.');
$assert(is_string($mailSource) && strpos($mailSource, 'gj_resolve_enquiry_context(') !== false, 'Mail handler validates allowlisted context.');
$assert(is_string($mailSource) && strpos($mailSource, "'enquiry_context' => [") !== false, 'Private lead record carries context.');
$assert(is_string($mailSource) && strpos($mailSource, '$subjectContext') !== false && strpos($mailSource, 'type_label') !== false, 'Owner email subject includes trusted type.');
$assert(is_string($mailSource) && strpos($mailSource, 'Requested item</td>') !== false, 'Owner email includes context label.');
$assert(is_string($mailSource) && strpos($mailSource, 'gj_contact_context_return_url($enquiryContext') !== false, 'Recoverable errors use trusted context retry URLs.');
if ($errors !== []) {
    fwrite(STDERR, "FAIL enquiry context contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS: registry-backed enquiry context, opaque tokens, safe retry routes and owner notification labels.\n";
