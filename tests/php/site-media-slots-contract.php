<?php
/**
 * Contract for 33 fixed owner-managed page image slots and signed approval.
 * Run with: php tests/php/site-media-slots-contract.php
 */
require_once dirname(__DIR__, 2) . '/include/site-media-slots.php';
$root = dirname(__DIR__, 2);
$errors = [];
$expected = [
    'home' => ['home-hero-mobile', 'home-hero-desktop', 'home-heritage-intro', 'home-rooftop-feature', 'home-desert-safari-feature', 'home-swiss-tent-feature', 'home-sleep-under-stars-feature', 'home-city-discovery-feature', 'home-gallery-city', 'home-gallery-courtyard', 'home-gallery-reception', 'home-gallery-rooftop', 'home-gallery-entrance'],
    'rooms' => ['rooms-hero', 'room-sweet-purple-room-primary', 'room-rising-sun-room-primary', 'room-scarlet-room-primary', 'room-opium-room-primary', 'room-red-maharani-room-primary', 'room-green-room-primary', 'room-bluesky-room-primary'],
    'dining' => ['dining-hero'],
    'experiences' => ['experience-desert-safari-hero', 'experience-guided-jaisalmer-tour-hero', 'experience-sunset-camel-safari-hero', 'experience-sleep-under-the-stars-hero', 'experience-swiss-tent-safari-hero', 'experience-cultural-safari-hero', 'experience-explore-the-desert-hero', 'experience-half-day-jaisalmer-tour-hero', 'experience-full-day-jaisalmer-tour-hero', 'experience-longewala-tour-hero', 'experience-rajasthan-tours-hero'],
];
$expectedCount = array_sum(array_map('count', $expected));
$slots = gj_load_site_media_slots($root);
if (count($slots) !== $expectedCount) $errors[] = 'Expected 33 valid fixed slot records; found ' . count($slots) . '.';
foreach ($expected as $area => $ids) {
    foreach ($ids as $id) {
        if (!isset($slots[$id]) || ($slots[$id]['area'] ?? null) !== $area) {
            $errors[] = 'Missing or misclassified slot: ' . $id;
            continue;
        }
        if (($slots[$id]['approved'] ?? true) !== false) $errors[] = 'Existing slot must remain pending: ' . $id;
    }
}
$fallback = ['src' => 'img/reviewed-fallback.webp', 'alt' => 'Reviewed fallback description for a page image'];
if (gj_site_media_slot($slots, 'missing-slot', $fallback) !== $fallback) $errors[] = 'Missing slot must preserve fallback.';
foreach (['home-hero-mobile', 'home-hero-desktop', 'rooms-hero', 'dining-hero', 'experience-desert-safari-hero'] as $id) {
    if (gj_site_media_slot($slots, $id, $fallback) !== $fallback) $errors[] = 'Unapproved slot must preserve fallback: ' . $id;
}

$makeFixture = static function () {
    $tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gj-page-media-' . uniqid('', true);
    @mkdir($tempRoot . '/content/page-media', 0775, true);
    @mkdir($tempRoot . '/img', 0775, true);
    return $tempRoot;
};
$removeTree = null;
$removeTree = static function ($path) use (&$removeTree) {
    if (is_link($path) || is_file($path)) { @unlink($path); return; }
    if (!is_dir($path)) return;
    foreach (scandir($path) ?: [] as $entry) if ($entry !== '.' && $entry !== '..') $removeTree($path . DIRECTORY_SEPARATOR . $entry);
    @rmdir($path);
};
$key = str_repeat('a1', 32);
$approvers = ['Owner Reviewer'];
$source = 'img/hero.png';
$alt = 'Carved sandstone facade approved for the homepage hero';
$caption = 'A reviewed homepage image';
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/n3sAAAAASUVORK5CYII=', true);
if (!is_string($png)) $errors[] = 'Unable to decode PNG fixture.';
$validRoot = $makeFixture();
$imagePath = $validRoot . '/img/hero.png';
file_put_contents($imagePath, $png);
$approval = [
    'status' => 'approved',
    'source_credit' => 'Photograph supplied by the hotel owner',
    'rights_basis' => 'Owner confirms this photograph is licensed for the hotel website',
    'visual_match_review' => 'Reviewer confirms the facade matches the page description',
    'alt_text_review' => 'Reviewer confirms the alt text accurately describes the image',
    'technical_validation' => 'passed',
    'asset_sha256' => hash_file('sha256', $imagePath),
    'approved_by' => 'Owner Reviewer',
    'approved_at' => gmdate('Y-m-d\TH:i:sP'),
];
$approval['signature'] = gj_page_media_approval_sign('home-hero-mobile', $source, $alt, $caption, $approval, $key);
$records = [
    'home-hero-mobile' => ['src' => $source, 'alt' => $alt, 'caption' => $caption, 'approval' => $approval],
    'home-hero-desktop' => ['src' => $source, 'alt' => $alt, 'caption' => $caption, 'approval' => array_replace($approval, ['signature' => ''])],
    'home-heritage-intro' => ['src' => $source, 'alt' => $alt, 'caption' => $caption, 'approval' => array_replace($approval, ['status' => 'revoked'])],
    'home-rooftop-feature' => ['src' => $source, 'alt' => $alt, 'caption' => $caption, 'approval' => array_replace($approval, ['approved_by' => 'Unlisted Editor'])],
];
file_put_contents($validRoot . '/content/page-media/home.json', json_encode($records));
$fixtureSlots = gj_load_site_media_slots($validRoot, $key, $approvers);
if (($fixtureSlots['home-hero-mobile']['approved'] ?? false) !== true) $errors[] = 'Complete signed evidence must be accepted.';
foreach (['home-hero-desktop', 'home-heritage-intro', 'home-rooftop-feature'] as $id) {
    if (($fixtureSlots[$id]['approved'] ?? true) !== false
        || gj_site_media_slot($fixtureSlots, $id, $fallback) !== $fallback) $errors[] = 'Missing signature, revoked status or unlisted approver must preserve fallback: ' . $id;
}
if (($fixtureSlots['home-hero-mobile']['image']['src'] ?? '') !== $source) $errors[] = 'Approved slot must retain validated source.';

$wrongKeyRoot = $makeFixture();
copy($imagePath, $wrongKeyRoot . '/img/hero.png');
file_put_contents($wrongKeyRoot . '/content/page-media/home.json', json_encode(['home-hero-mobile' => $records['home-hero-mobile']]));
$wrongKeySlots = gj_load_site_media_slots($wrongKeyRoot, str_repeat('b2', 32), $approvers);
if (($wrongKeySlots['home-hero-mobile']['approved'] ?? true) !== false) $errors[] = 'Wrong HMAC key must be rejected.';

$wrongApproverRoot = $makeFixture();
copy($imagePath, $wrongApproverRoot . '/img/hero.png');
file_put_contents($wrongApproverRoot . '/content/page-media/home.json', json_encode(['home-hero-mobile' => $records['home-hero-mobile']]));
$wrongApproverSlots = gj_load_site_media_slots($wrongApproverRoot, $key, ['Different Reviewer']);
if (($wrongApproverSlots['home-hero-mobile']['approved'] ?? true) !== false) $errors[] = 'Approver outside server allowlist must be rejected.';

$tamperedRoot = $makeFixture();
copy($imagePath, $tamperedRoot . '/img/hero.png');
$tamperedRecord = $records['home-hero-mobile'];
$tamperedRecord['alt'] = 'Changed alt text after approval';
file_put_contents($tamperedRoot . '/content/page-media/home.json', json_encode(['home-hero-mobile' => $tamperedRecord]));
$tamperedSlots = gj_load_site_media_slots($tamperedRoot, $key, $approvers);
if (($tamperedSlots['home-hero-mobile']['approved'] ?? true) !== false) $errors[] = 'Changing alt text must invalidate signature.';

$changedAssetRoot = $makeFixture();
file_put_contents($changedAssetRoot . '/img/hero.png', $png . 'changed');
file_put_contents($changedAssetRoot . '/content/page-media/home.json', json_encode(['home-hero-mobile' => $records['home-hero-mobile']]));
$changedAssetSlots = gj_load_site_media_slots($changedAssetRoot, $key, $approvers);
if (($changedAssetSlots['home-hero-mobile']['approved'] ?? true) !== false) $errors[] = 'Changing image bytes must invalidate signature.';

foreach ([$validRoot, $wrongKeyRoot, $wrongApproverRoot, $tamperedRoot, $changedAssetRoot] as $fixtureRoot) $removeTree($fixtureRoot);
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS 33 slots, fail-closed defaults, signed approval, reviewer allowlist, asset binding, tamper/revocation and fallback preservation.\n";
