<?php
/**
 * Contract for signed Gallery approvals and immutable legacy compatibility.
 * Run with: php tests/php/gallery-media-approval-contract.php
 */
$root = dirname(__DIR__, 2);
require_once $root . '/include/gallery-media-approval.php';
$errors = [];

// The optional server allowlist override must accept only a JSON list of non-empty identities.
$previousApproversEnv = getenv('GJ_MEDIA_APPROVERS_JSON');
putenv('GJ_MEDIA_APPROVERS_JSON=[" Gallery CI Reviewer ","Gallery CI Reviewer"]');
$envConfig = require $root . '/include/site-config.php';
if (($envConfig['media_approvers'] ?? null) !== ['Gallery CI Reviewer']) {
    $errors[] = 'GJ_MEDIA_APPROVERS_JSON must normalize and deduplicate a valid allowlist.';
}
putenv('GJ_MEDIA_APPROVERS_JSON=not-json');
$invalidEnvConfig = require $root . '/include/site-config.php';
if (($invalidEnvConfig['media_approvers'] ?? null) !== []) {
    $errors[] = 'Malformed explicit GJ_MEDIA_APPROVERS_JSON must fail closed.';
}
if ($previousApproversEnv === false) {
    putenv('GJ_MEDIA_APPROVERS_JSON');
} else {
    putenv('GJ_MEDIA_APPROVERS_JSON=' . $previousApproversEnv);
}

$baseline = require $root . '/include/gallery-legacy-baseline.php';
if (!is_array($baseline) || count($baseline) !== 36) {
    $errors[] = 'Legacy Gallery baseline must contain exactly 36 records.';
} else {
    foreach ($baseline as $id => $expected) {
        $path = $root . '/content/gallery/' . $id . '.json';
        if (!is_string($id) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id)
            || !is_array($expected)
            || !is_string($expected['record_sha256'] ?? null)
            || !preg_match('/^[a-f0-9]{64}$/D', $expected['record_sha256'])
            || !is_string($expected['image_path'] ?? null)
            || !is_string($expected['image_git_blob_sha1'] ?? null)
            || !preg_match('/^[a-f0-9]{40}$/D', $expected['image_git_blob_sha1'])
            || !is_int($expected['image_bytes'] ?? null) || $expected['image_bytes'] < 1
            || is_link($path) || !is_file($path)) {
            $errors[] = 'Legacy Gallery baseline entry is missing or unsafe: ' . (string) $id;
            continue;
        }
        $raw = file_get_contents($path);
        $record = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_string($raw) || !is_array($record) || ($record['id'] ?? null) !== $id
            || array_key_exists('approval', $record)) {
            $errors[] = 'Legacy Gallery record identity or legacy state is invalid: ' . $id;
            continue;
        }
        $source = $record['image']['src'] ?? '';
        $imagePath = is_string($source) ? $root . '/' . $source : '';
        if (!hash_equals($expected['record_sha256'], hash('sha256', $raw))) {
            $errors[] = 'Changed legacy Gallery record must receive a signed approval: ' . $id;
        }
        if ($source !== $expected['image_path']) {
            $errors[] = 'Legacy Gallery source path changed: ' . $id;
        }
        if (!gj_gallery_media_publication_is_approved($record, $raw, $imagePath, $baseline, '', [])) {
            $errors[] = 'Unchanged legacy JSON and source image must retain the exact baseline path: ' . $id;
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
$tempLegacyImage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gj-gallery-legacy-image-' . uniqid('', true) . '.png';
$legacyImageBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);
if (!is_string($legacyImageBytes) || file_put_contents($tempLegacyImage, $legacyImageBytes) === false) {
    $errors[] = 'Unable to create decodable legacy image fixture.';
}
if (gj_gallery_media_legacy_asset_git_blob_sha1($tempLegacyImage) !== '2676aad561e54816c19347fcee2d5c09975b419e'
    || strlen($legacyImageBytes) !== 68) {
    $errors[] = 'Legacy Git blob identity must match the canonical Git object hash for the image fixture.';
}
$legacyBaselineFixture = [
    'legacy-fixture' => [
        'record_sha256' => hash('sha256', $legacyRaw),
        'image_path' => 'img/legacy.jpg',
        'image_git_blob_sha1' => gj_gallery_media_legacy_asset_git_blob_sha1($tempLegacyImage),
        'image_bytes' => strlen($legacyImageBytes),
    ],
];
if (!gj_gallery_media_publication_is_approved($legacy, $legacyRaw, $tempLegacyImage, $legacyBaselineFixture, '', [])) {
    $errors[] = 'Exact legacy JSON and exact source bytes must remain publishable during rollout.';
}
if (gj_gallery_media_publication_is_approved($legacy, $legacyRaw . ' ', $tempLegacyImage, $legacyBaselineFixture, '', [])) {
    $errors[] = 'A byte-changed legacy record must not retain grandfathered publication.';
}
file_put_contents($tempLegacyImage, $legacyImageBytes . 'changed');
if (gj_gallery_media_publication_is_approved($legacy, $legacyRaw, $tempLegacyImage, $legacyBaselineFixture, '', [])) {
    $errors[] = 'Replacing a legacy source image without changing JSON must invalidate its baseline.';
}
if (gj_gallery_media_publication_is_approved($legacy, $legacyRaw, $tempLegacyImage, [], '', [])) {
    $errors[] = 'A new record without a signed approval must not publish.';
}
$pendingLegacy = $legacy;
$pendingLegacy['approval'] = ['status' => 'pending'];
$pendingLegacyRaw = json_encode($pendingLegacy, JSON_UNESCAPED_SLASHES);
if (gj_gallery_media_publication_is_approved(
    $pendingLegacy, $pendingLegacyRaw, $tempLegacyImage, $legacyBaselineFixture, '', []
)) {
    $errors[] = 'An explicit pending approval object must not fall through to legacy grandfathering.';
}
@unlink($tempLegacyImage);

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
    'altTextReview' => [
        'status' => 'matched',
        'evidence' => 'Alt text accurately identifies the visible sandstone facade without keyword stuffing.',
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
