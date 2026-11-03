<?php
/**
 * Sign one owner-managed Gallery record after rights, visual and technical review.
 * Usage: php scripts/sign-gallery-media-approval.php <gallery-id> <approver>
 * This CLI-only script is not deployed; its HMAC key must never be committed.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}
require_once dirname(__DIR__) . '/include/gallery-media-approval.php';

$root = realpath(dirname(__DIR__));
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "ERROR Repository root cannot be resolved.\n");
    exit(1);
}
$id = isset($argv[1]) ? trim($argv[1]) : '';
$approver = isset($argv[2]) ? trim($argv[2]) : '';
if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id) || $approver === '') {
    fwrite(STDERR, "Usage: php scripts/sign-gallery-media-approval.php <gallery-id> <approver>\n");
    exit(2);
}

$configPath = $root . '/include/site-config.php';
if (is_link($configPath) || !is_file($configPath)) {
    fwrite(STDERR, "ERROR Public site configuration is missing or unsafe.\n");
    exit(1);
}
$config = require $configPath;
$key = is_array($config) ? ($config['media_approval_hmac_key'] ?? '') : '';
$approvers = is_array($config) ? ($config['media_approvers'] ?? []) : [];
if (!is_string($key) || preg_match('/^(?:[a-f0-9]{2}){32,64}$/iD', $key) !== 1) {
    fwrite(STDERR, "ERROR Configure a random 32-byte-or-longer hex media_approval_hmac_key.\n");
    exit(1);
}
$allowedApprovers = [];
if (is_array($approvers)) {
    foreach ($approvers as $allowed) {
        if (is_string($allowed) && trim($allowed) !== '') $allowedApprovers[] = trim($allowed);
    }
}
if (!in_array($approver, $allowedApprovers, true)) {
    fwrite(STDERR, "ERROR Approver is not present in the server-side media_approvers allowlist.\n");
    exit(1);
}

$contentRoot = $root . DIRECTORY_SEPARATOR . 'content';
$galleryRoot = $contentRoot . DIRECTORY_SEPARATOR . 'gallery';
if (is_link($contentRoot) || !is_dir($contentRoot) || is_link($galleryRoot) || !is_dir($galleryRoot)) {
    fwrite(STDERR, "ERROR Gallery content directory is missing or unsafe.\n");
    exit(1);
}
$galleryRootReal = realpath($galleryRoot);
if (!is_string($galleryRootReal) || strpos($galleryRootReal, $root . DIRECTORY_SEPARATOR) !== 0) {
    fwrite(STDERR, "ERROR Gallery content directory resolves outside the repository.\n");
    exit(1);
}
$file = $galleryRootReal . DIRECTORY_SEPARATOR . $id . '.json';
if (is_link($file) || !is_file($file)) {
    fwrite(STDERR, "ERROR Gallery record is missing or unsafe: " . $id . "\n");
    exit(1);
}

$raw = file_get_contents($file);
$record = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($record) || json_last_error() !== JSON_ERROR_NONE || ($record['id'] ?? null) !== $id) {
    fwrite(STDERR, "ERROR Gallery record identity or JSON is invalid.\n");
    exit(1);
}
if (($record['status'] ?? '') !== 'live' || ($record['review_status'] ?? '') !== 'approved') {
    fwrite(STDERR, "ERROR Set publication status to Live and editorial review to Approved only after review.\n");
    exit(1);
}
$approval = is_array($record['approval'] ?? null) ? $record['approval'] : [];
if (($approval['status'] ?? '') !== 'approved') {
    fwrite(STDERR, "ERROR Set approval status to Approved only after completing the evidence review.\n");
    exit(1);
}

$image = is_array($record['image'] ?? null) ? $record['image'] : [];
$source = $image['src'] ?? '';
if (!is_string($source) || preg_match('~^img/[A-Za-z0-9._/-]+\\.(?:avif|jpe?g|png|webp)$~iD', $source) !== 1
    || strpos($source, '..') !== false || strpos($source, chr(92)) !== false) {
    fwrite(STDERR, "ERROR Gallery image path is invalid.\n");
    exit(1);
}
$imageRoot = $root . DIRECTORY_SEPARATOR . 'img';
if (is_link($imageRoot) || !is_dir($imageRoot)) {
    fwrite(STDERR, "ERROR Image root is missing or unsafe.\n");
    exit(1);
}
$imageRootReal = realpath($imageRoot);
$imageCursor = $root;
foreach (explode('/', $source) as $part) {
    $imageCursor .= DIRECTORY_SEPARATOR . $part;
    if (is_link($imageCursor) || !file_exists($imageCursor)) {
        fwrite(STDERR, "ERROR Image path contains a symlink or missing component.\n");
        exit(1);
    }
}
$realImage = realpath($imageCursor);
$imageInfo = is_string($realImage) ? @getimagesize($realImage) : false;
if (!is_string($imageRootReal) || !is_string($realImage)
    || strpos($realImage, $imageRootReal . DIRECTORY_SEPARATOR) !== 0
    || !is_array($imageInfo) || empty($imageInfo[0]) || empty($imageInfo[1])) {
    fwrite(STDERR, "ERROR Image must be a decodable regular file beneath img/.\n");
    exit(1);
}

$approval['asset_sha256'] = hash_file('sha256', $realImage);
$approval['approved_by'] = $approver;
$approval['approved_at'] = gmdate('Y-m-d\\TH:i:sP');
$record['approval'] = $approval;
$record['approval']['signature'] = gj_gallery_media_approval_sign($record, $record['approval'], $key);
if ($record['approval']['signature'] === ''
    || !gj_gallery_media_approval_is_valid($record, $realImage, $key, $approvers)) {
    fwrite(STDERR, "ERROR Rights, visual, technical or signer evidence is incomplete; record was not changed.\n");
    exit(1);
}

$encoded = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (!is_string($encoded)) {
    fwrite(STDERR, "ERROR Unable to encode signed Gallery record.\n");
    exit(1);
}
$currentRaw = @file_get_contents($file);
if (!is_string($currentRaw) || !hash_equals(hash('sha256', $raw), hash('sha256', $currentRaw))) {
    fwrite(STDERR, "ERROR Gallery record changed during signing; reload and retry.\n");
    exit(1);
}
$tempFile = @tempnam(dirname($file), '.gallery-approval-');
if (!is_string($tempFile) || dirname($tempFile) !== dirname($file)) {
    if (is_string($tempFile)) @unlink($tempFile);
    fwrite(STDERR, "ERROR Unable to create a temporary approval file.\n");
    exit(1);
}
$payload = $encoded . PHP_EOL;
$written = @file_put_contents($tempFile, $payload, LOCK_EX);
$permissions = @fileperms($file);
if (!is_int($written) || $written !== strlen($payload)
    || (is_int($permissions) && !@chmod($tempFile, $permissions & 0777))
    || !@rename($tempFile, $file)) {
    @unlink($tempFile);
    fwrite(STDERR, "ERROR Unable to atomically write signed Gallery approval.\n");
    exit(1);
}
echo "Signed Gallery approval for " . $id . ". Review the JSON diff and submit it through a pull request.\n";
