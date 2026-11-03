<?php
/**
 * Sign one page image approval after its evidence has been reviewed.
 * Usage: php scripts/sign-page-media-approval.php <home|rooms|dining|experiences> <slot-id> <approver>
 * This CLI-only script is not deployed. The HMAC key must never be committed.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}
require_once dirname(__DIR__) . '/include/page-media-approval.php';

$root = realpath(dirname(__DIR__));
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "ERROR Repository root cannot be resolved.\n");
    exit(1);
}
$area = $argv[1] ?? '';
$slotId = $argv[2] ?? '';
$approver = isset($argv[3]) ? trim($argv[3]) : '';
if (!in_array($area, ['home', 'rooms', 'dining', 'experiences'], true) || $slotId === '' || $approver === '') {
    fwrite(STDERR, "Usage: php scripts/sign-page-media-approval.php <home|rooms|dining|experiences> <slot-id> <approver>\n");
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
    fwrite(STDERR, "ERROR Configure GJ_MEDIA_APPROVAL_HMAC_KEY or media_approval_hmac_key in site-config.local.php.\n");
    exit(1);
}
$allowedApprovers = [];
if (is_array($approvers)) {
    foreach ($approvers as $allowedApprover) {
        if (is_string($allowedApprover) && trim($allowedApprover) !== '') {
            $allowedApprovers[] = trim($allowedApprover);
        }
    }
}
if (!in_array($approver, $allowedApprovers, true)) {
    fwrite(STDERR, "ERROR Approver is not present in the server-side media_approvers allowlist.\n");
    exit(1);
}

$relativeFile = 'content/page-media/' . $area . '.json';
$contentRoot = $root . DIRECTORY_SEPARATOR . 'content';
$pageMediaRoot = $contentRoot . DIRECTORY_SEPARATOR . 'page-media';
if (is_link($contentRoot) || !is_dir($contentRoot)
    || is_link($pageMediaRoot) || !is_dir($pageMediaRoot)) {
    fwrite(STDERR, "ERROR Page media directory is missing or unsafe.\n");
    exit(1);
}
$pageMediaReal = realpath($pageMediaRoot);
if (!is_string($pageMediaReal) || strpos($pageMediaReal, $root . DIRECTORY_SEPARATOR) !== 0) {
    fwrite(STDERR, "ERROR Page media directory resolves outside the repository.\n");
    exit(1);
}
$file = $pageMediaReal . DIRECTORY_SEPARATOR . $area . '.json';
if (is_link($file) || !is_file($file)) {
    fwrite(STDERR, "ERROR Page media file is missing or unsafe: " . $relativeFile . "\n");
    exit(1);
}
$raw = file_get_contents($file);
$records = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($records) || !isset($records[$slotId]) || !is_array($records[$slotId])) {
    fwrite(STDERR, "ERROR Slot does not exist in " . $relativeFile . ".\n");
    exit(1);
}
$record = $records[$slotId];
$source = $record['src'] ?? '';
$alt = $record['alt'] ?? '';
$caption = $record['caption'] ?? '';
$approval = is_array($record['approval'] ?? null) ? $record['approval'] : [];
if (($approval['status'] ?? '') !== 'approved') {
    fwrite(STDERR, "ERROR Set approval.status to approved only after review and evidence completion.\n");
    exit(1);
}
if (!is_string($source) || preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$~iD', $source) !== 1
    || strpos($source, '..') !== false || strpos($source, chr(92)) !== false
    || !is_string($alt) || strlen(trim($alt)) < 15
    || !is_string($caption) || strlen($caption) > 180) {
    fwrite(STDERR, "ERROR Slot image path or descriptive metadata is invalid.\n");
    exit(1);
}
$imagePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $source);
$cursor = $root;
foreach (explode('/', $source) as $part) {
    $cursor .= DIRECTORY_SEPARATOR . $part;
    if (is_link($cursor) || !file_exists($cursor)) {
        fwrite(STDERR, "ERROR Image path contains a symlink or missing component.\n");
        exit(1);
    }
}
$realImage = realpath($imagePath);
$realImageRoot = realpath($root . DIRECTORY_SEPARATOR . 'img');
$imageInfo = is_string($realImage) ? @getimagesize($realImage) : false;
if (!is_string($realImage) || !is_string($realImageRoot)
    || strpos($realImage, $realImageRoot . DIRECTORY_SEPARATOR) !== 0
    || !is_array($imageInfo) || empty($imageInfo[0]) || empty($imageInfo[1])) {
    fwrite(STDERR, "ERROR Image must be a decodable regular image beneath img/.\n");
    exit(1);
}
$approval['technical_validation'] = 'passed';
$approval['asset_sha256'] = hash_file('sha256', $realImage);
$approval['approved_by'] = $approver;
$approval['approved_at'] = gmdate('Y-m-d\TH:i:sP');
$approval['signature'] = gj_page_media_approval_sign($slotId, $source, trim($alt), trim($caption), $approval, $key);
if ($approval['signature'] === ''
    || !gj_page_media_approval_is_valid($slotId, $source, trim($alt), trim($caption), $approval, $realImage, $key, $approvers)) {
    fwrite(STDERR, "ERROR Approval evidence is incomplete or could not be signed.\n");
    exit(1);
}
$records[$slotId]['approval'] = $approval;
$encoded = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (!is_string($encoded)) {
    fwrite(STDERR, "ERROR Unable to encode signed page media JSON.\n");
    exit(1);
}

// Refuse to overwrite a concurrent edit, then atomically replace the JSON file.
$currentRaw = @file_get_contents($file);
if (!is_string($currentRaw) || !hash_equals(hash('sha256', $raw), hash('sha256', $currentRaw))) {
    fwrite(STDERR, "ERROR Page media JSON changed during signing; reload and retry.\n");
    exit(1);
}
$tempFile = @tempnam(dirname($file), '.page-media-approval-');
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
    fwrite(STDERR, "ERROR Unable to atomically write signed page media JSON.\n");
    exit(1);
}
echo "Signed approval for " . $slotId . " in " . $relativeFile . ". Review the JSON diff and submit it through a pull request.\n";
