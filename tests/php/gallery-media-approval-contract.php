<?php
/**
 * Contract for signed Gallery approvals and immutable legacy compatibility.
 * Run with: php tests/php/gallery-media-approval-contract.php
 */
$root = dirname(__DIR__, 2);
require_once $root . '/include/gallery-media-approval.php';
$errors = [];

$baseline = require $root . '/include/gallery-legacy-baseline.php';
if (!is_array($baseline) || count($baseline) !== 36) {
    $errors[] = 'Legacy Gallery baseline must contain exactly 36 records.';
} else {
    foreach ($baseline as $id => $expectedHash) {
        $path = $root . '/content/gallery/' . $id . '.json';
        if (!is_string($id) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id)
            || !is_string($expectedHash) || !preg_match('/^[a-f0-9]{64}$/D', $expectedHash)
            || is_link($path) || !is_file($path)) {
            $errors[] = 'Legacy Gallery baseline entry is missing or unsafe: ' . (string) $id;
            continue;
        }
        $raw = file_get_contents($path);
        $record = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_string($raw) || !is_array($record) || ($record['id'] ?? null) !== $id) {
            $errors[] = 'Legacy Gallery baseline record is invalid: ' . $id;
            continue;
        }
        if (!array_key_exists('approval', $record)) {
            if (!hash_equals($expectedHash, hash('sha256', $raw))) {
                $errors[] = 'Changed legacy Gallery records must receive an explicit approval object: ' . $id;
            } elseif (!gj_gallery_media_publication_is_approved($record, $raw, '', $baseline, '', [])) {
                $errors[] = 'Unchanged legacy Gallery record must retain its baseline publication path: ' . $id;
            }
        }
    }
}

$legacy = [
    'id' => 'legacy-fixture',
    'category' => 'hotel',
    'status' => 'live',
    'review_status' => 'draft',
    'image' => ['src' => 'img/legacy.jpg', 'alt' => 'Original legacy image description', 'caption' => '', 'crop' => 'standard'],
];
$legacyRaw = json_encode($legacy, JSON_UNESCAPED_SLASHES);
$legacyHash = hash('sha256', $legacyRaw);
if (!gj_gallery_media_publication_is_approved($legacy, $legacyRaw, '', ['legacy-fixture' => $legacyHash], '', [])) {
    $errors[] = 'Exact original legacy record must remain publishable during rollout.';
}
if (gj_gallery_media_publication_is_approved($legacy, $legacyRaw . ' ', '', ['legacy-fixture' => $legacyHash], '', [])) {
    $errors[] = 'A byte-changed legacy record must not retain grandfathered publication.';
}
if (gj_gallery_media_publication_is_approved($legacy, $legacyRaw, '', [], '', [])) {
    $errors[] = 'A new record without a signed approval must not publish.';
}
$pendingLegacy = $legacy;
$pendingLegacy['approval'] = ['status' => 'pending'];
$pendingLegacyRaw = json_encode($pendingLegacy, JSON_UNESCAPED_SLASHES);
if (gj_gallery_media_publication_is_approved(
    $pendingLegacy, $pendingLegacyRaw, '', ['legacy-fixture' => hash('sha256', $pendingLegacyRaw)], '', []
)) {
    $errors[] = 'An explicit pending approval object must not fall through to legacy grandfathering.';
}

$tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gj-gallery-approval-' . uniqid('', true);
@mkdir($tempRoot, 0775, true);
$imagePath = $tempRoot . DIRECTORY_SEPARATOR . 'reviewed.png';
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);
if (!is_string($png) || file_put_contents($imagePath, $png) === false) {
    $errors[] = 'Unable to create valid PNG fixture.';
}

$key = str_repeat('ab', 32);
$approvers = ['Owner Reviewer'];
$record = [
    'id' => 'approved-gallery-fixture',
    'category' => 'hotel',
    'status' => 'live',
    'review_status' => 'approved',
    'sourceEvidence' => [
        'status' => 'licensed',
        'creator' => 'Commissioned heritage photographer',
        'sourceUrl' => 'https://example.com/garh-jaisal-photo-licence',
        'licenseName' => 'Commercial website licence',
        'evidenceReference' => 'Commission agreement GJ-2026-001',
        'verifiedBy' => 'Owner Reviewer',
        'verifiedAt' => '2026-10-01',
    ],
    'visualReview' => [
        'status' => 'matched',
        'evidence' => 'Full-size image inspected; sandstone facade matches the page subject and selected crop.',
        'reviewer' => 'Owner Reviewer',
        'reviewedAt' => '2026-10-02',
    ],
    'technicalValidation' => [
        'status' => 'verified',
        'evidenceReference' => 'Image decoded and pixel dimensions checked in release review GJ-2026-01',
    ],
    'image' => [
        'src' => 'img/reviewed.png',
        'alt' => 'Carved sandstone facade of the heritage hotel inside Jaisalmer Fort',
        'caption' => 'The heritage facade',
        'crop' => 'standard',
    ],
    'approval' => [
        'status' => 'approved',
        'asset_sha256' => hash_file('sha256', $imagePath),
        'approved_by' => 'Owner Reviewer',
        'approved_at' => gmdate('Y-m-d\\TH:i:sP'),
    ],
];
$record['approval']['signature'] = gj_gallery_media_approval_sign($record, $record['approval'], $key);
if ($record['approval']['signature'] === ''
    || !gj_gallery_media_approval_is_valid($record, $imagePath, $key, $approvers)) {
    $errors[] = 'Complete signed Gallery evidence must be accepted.';
}
$fixtureJson = json_encode($record, JSON_UNESCAPED_SLASHES);
if (!gj_gallery_media_publication_is_approved($record, $fixtureJson, $imagePath, [], $key, $approvers)) {
    $errors[] = 'A valid signed Gallery record must pass the shared publication policy.';
}
if (gj_gallery_media_publication_is_approved($record, $fixtureJson, $imagePath, [], str_repeat('cd', 32), $approvers)) {
    $errors[] = 'A Gallery signature made with another key must be rejected.';
}
if (gj_gallery_media_publication_is_approved($record, $fixtureJson, $imagePath, [], $key, ['Different Reviewer'])) {
    $errors[] = 'An approver outside the server allowlist must be rejected.';
}

$tampered = $record;
$tampered['sourceEvidence']['evidenceReference'] = 'Different evidence reference after signing';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'Changing rights evidence must invalidate the signature.';
}
$tampered = $record;
$tampered['image']['alt'] = 'Different alt text after signing';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'Changing alt text must invalidate the signature.';
}
$tampered = $record;
$tampered['review_status'] = 'draft';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'A record not editorially approved must not pass the signature gate.';
}
$tampered = $record;
$tampered['sourceEvidence']['status'] = 'unverified';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'Unverified rights evidence must not pass the signature gate.';
}
$tampered = $record;
$tampered['visualReview']['status'] = 'mismatch';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'Visual mismatch must not pass the signature gate.';
}
$tampered = $record;
$tampered['technicalValidation']['status'] = 'pending';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'Pending technical validation must not pass the signature gate.';
}
$tampered = $record;
$tampered['approval']['status'] = 'revoked';
if (gj_gallery_media_approval_is_valid($tampered, $imagePath, $key, $approvers)) {
    $errors[] = 'Revoked approval must not pass the signature gate.';
}
if (gj_gallery_media_approval_date_is_valid('2026-02-30')
    || !gj_gallery_media_approval_date_is_valid('2026-02-28')) {
    $errors[] = 'Gallery evidence dates must be real calendar dates.';
}
if (gj_gallery_media_approval_sign($record, $record['approval'], str_repeat('a', 65)) !== '') {
    $errors[] = 'Odd-length hexadecimal HMAC key must be rejected.';
}

$changedImage = $png . 'changed';
file_put_contents($imagePath, $changedImage);
if (gj_gallery_media_approval_is_valid($record, $imagePath, $key, $approvers)) {
    $errors[] = 'Changing the underlying image bytes must invalidate the signature.';
}
@unlink($imagePath);
@rmdir($tempRoot);

if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS immutable 36-record legacy baseline, signed Gallery evidence, reviewer allowlist, publication state and image/evidence tamper checks.\n";
