<?php
require_once __DIR__ . '/../../include/lead-storage.php';

function lead_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . "\n");
        exit(1);
    }
}

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gj-leads-' . bin2hex(random_bytes(5));
$public = $root . DIRECTORY_SEPARATOR . 'public';
$private = $root . DIRECTORY_SEPARATOR . 'private';
if (!mkdir($public, 0700, true)) {
    fwrite(STDERR, "FAIL: temporary test setup failed\n");
    exit(1);
}
$_SERVER['DOCUMENT_ROOT'] = $public;
putenv('GJ_LEAD_STORAGE_DIR=' . $private);

$first = gj_store_lead(
    'contact',
    ['name' => 'Test Guest', 'email' => 'guest@example.test', 'message' => 'Storage contract test'],
    '192.0.2.15',
    '+91 98765 43210',
    ['max_per_ip' => 1, 'max_per_phone' => 1]
);
lead_test_assert(!empty($first['ok']), 'valid enquiry is written');
lead_test_assert((bool) preg_match('/^GJ-[0-9]{8}-[A-F0-9]{8}$/D', (string) ($first['reference'] ?? '')), 'reference code has expected format');
$logPath = $private . DIRECTORY_SEPARATOR . 'guest-enquiries.jsonl';
lead_test_assert(is_file($logPath), 'private JSONL log exists');
$permissions = fileperms($logPath);
lead_test_assert(is_int($permissions) && (($permissions & 0777) & 0077) === 0, 'lead file has no group/other permissions');
$lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
lead_test_assert(is_array($lines) && count($lines) === 1, 'one request creates one lead record');
$record = json_decode($lines[0], true);
lead_test_assert(is_array($record) && ($record['type'] ?? '') === 'request', 'record has request event type');
lead_test_assert(($record['reference'] ?? '') === $first['reference'], 'stored reference matches returned reference');
lead_test_assert(($record['data']['message'] ?? '') === 'Storage contract test', 'validated fields are stored');

$limited = gj_store_lead(
    'contact',
    ['name' => 'Another Test'],
    '192.0.2.15',
    '+91 98765 43210',
    ['max_per_ip' => 1, 'max_per_phone' => 1]
);
lead_test_assert(empty($limited['ok']) && ($limited['error'] ?? '') === 'rate_limited', 'IP/phone daily limit blocks repeat requests');

putenv('GJ_LEAD_STORAGE_DIR=' . ($public . DIRECTORY_SEPARATOR . 'private'));
$unsafe = gj_store_lead('contact', ['message' => 'must not be written'], '192.0.2.30', '1234567890');
lead_test_assert(empty($unsafe['ok']) && ($unsafe['error'] ?? '') === 'storage_unavailable', 'path inside document root is rejected');
lead_test_assert(!is_file($public . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'guest-enquiries.jsonl'), 'no public-root lead file is created');

putenv('GJ_LEAD_STORAGE_DIR');
@unlink($logPath);
@rmdir($private);
@rmdir($public . DIRECTORY_SEPARATOR . 'private');
@rmdir($public);
@rmdir($root);
echo "PASS: private lead storage, reference codes, rate-limits and web-root rejection\n";
