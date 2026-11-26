<?php
// Isolated synthetic storage test: never calls mail or a public endpoint.
require_once dirname(__DIR__, 2) . '/include/lead-storage.php';
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
$directory = sys_get_temp_dir() . '/gj-lead-test-' . bin2hex(random_bytes(8));
putenv('GJ_LEAD_STORAGE_DIR=' . $directory);
function require_condition(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
try {
    $source = 'restaurant_reservation';
    $phone = '0000000000';
    $data = ['name' => 'Synthetic test', 'phone' => $phone, 'requested_date' => '2099-01-01', 'requested_time' => '12:00', 'guests' => 2, 'consent' => true];
    $limits = ['max_per_ip' => 10, 'max_per_phone' => 1, 'duplicate_window_seconds' => 300];
    $first = gj_store_lead($source, $data, '192.0.2.1', $phone, $limits);
    require_condition(!empty($first['ok']) && empty($first['duplicate']), 'first enquiry not stored');
    $second = gj_store_lead($source, $data, '192.0.2.2', $phone, $limits);
    require_condition(!empty($second['duplicate']) && $second['reference'] === $first['reference'] && !$second['email_notified'], 'retry did not recover the original receipt before notification');
    require_condition(gj_record_lead_event($first['reference'], 'owner_notification_sent'), 'event write failed');
    $third = gj_store_lead($source, $data, '192.0.2.3', $phone, $limits);
    require_condition(!empty($third['duplicate']) && $third['reference'] === $first['reference'] && $third['email_notified'], 'notification status not recovered');
    $changed = $data;
    $changed['guests'] = 3;
    $blocked = gj_store_lead($source, $changed, '192.0.2.4', $phone, $limits);
    require_condition(($blocked['error'] ?? '') === 'rate_limited', 'distinct request must still obey rate limits');
    $limits['max_per_phone'] = 5;
    $different = gj_store_lead($source, $changed, '192.0.2.4', $phone, $limits);
    require_condition(!empty($different['ok']) && empty($different['duplicate']) && $different['reference'] !== $first['reference'], 'distinct request was collapsed');
    $log = $directory . '/guest-enquiries.jsonl';
    $records = array_map(static function ($line) { return json_decode($line, true); }, file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    require_condition(count(array_filter($records, static function ($r) { return ($r['type'] ?? '') === 'request'; })) === 2, 'incorrect number of stored requests');
    foreach ($records as &$record) {
        if (($record['type'] ?? '') === 'request') $record['received_at'] = date(DATE_ATOM, time() - 601);
    }
    unset($record);
    file_put_contents($log, implode("\n", array_map('json_encode', $records)) . "\n");
    $later = gj_store_lead($source, $data, '192.0.2.5', $phone, $limits);
    require_condition(!empty($later['ok']) && empty($later['duplicate']) && $later['reference'] !== $first['reference'], 'expired duplicate window prevented a new request');
    echo "PASS restaurant lead idempotency and notification recovery\n";
} finally {
    if (is_file($directory . '/guest-enquiries.jsonl')) unlink($directory . '/guest-enquiries.jsonl');
    if (is_dir($directory)) rmdir($directory);
    putenv('GJ_LEAD_STORAGE_DIR');
}
