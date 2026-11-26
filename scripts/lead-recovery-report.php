<?php
/** CLI-only, read-only notification recovery inventory. Never send mail or expose guest details. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function gj_lead_recovery_report(string $path): array
{
    clearstatcache(true, $path);
    if (is_link($path) || !is_file($path)) {
        throw new RuntimeException('Expected a regular private lead log.');
    }
    $handle = @fopen($path, 'rb');
    if (!is_resource($handle)) {
        throw new RuntimeException('Cannot open private lead log.');
    }
    try {
        if (!flock($handle, LOCK_SH)) {
            throw new RuntimeException('Cannot acquire shared read lock.');
        }
        clearstatcache(true, $path);
        $opened = fstat($handle);
        $named = @lstat($path);
        if (!is_array($opened) || !is_array($named)
            || ($named['mode'] & 0170000) !== 0100000
            || $opened['dev'] !== $named['dev'] || $opened['ino'] !== $named['ino']
            || $opened['nlink'] !== 1 || ($opened['mode'] & 0077) !== 0) {
            throw new RuntimeException('Lead log is not private or is an unsafe alias.');
        }
        $requests = [];
        $malformed = 0;
        while (($line = fgets($handle)) !== false) {
            $record = json_decode($line, true);
            $reference = is_array($record) ? ($record['reference'] ?? null) : null;
            if (!is_string($reference) || !preg_match('/^GJ-[0-9]{8}-[A-F0-9]{8}$/D', $reference)) {
                $malformed++;
                continue;
            }
            if (($record['type'] ?? '') === 'request') {
                if (isset($requests[$reference])) {
                    $malformed++;
                    continue;
                }
                $source = $record['source'] ?? '';
                $received = $record['received_at'] ?? '';
                $requests[$reference] = [
                    'reference' => $reference,
                    'source' => is_string($source) && in_array($source, ['contact', 'restaurant_reservation'], true) ? $source : 'other',
                    'received_at' => is_string($received) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $received) ? $received : null,
                    'notification' => 'unrecorded',
                ];
            } elseif (($record['type'] ?? '') === 'event' && isset($requests[$reference])) {
                $status = $record['status'] ?? '';
                if ($status === 'owner_notification_sent') {
                    $requests[$reference]['notification'] = 'sent';
                } elseif ($status === 'owner_notification_failed' && $requests[$reference]['notification'] !== 'sent') {
                    $requests[$reference]['notification'] = 'failed';
                }
            } else {
                $malformed++;
            }
        }
        if (!feof($handle)) {
            throw new RuntimeException('Lead log could not be read completely.');
        }
        $counts = ['sent' => 0, 'failed' => 0, 'unrecorded' => 0];
        $pending = [];
        foreach ($requests as $request) {
            $counts[$request['notification']]++;
            if ($request['notification'] !== 'sent') {
                $pending[] = $request;
            }
        }
        return ['requests' => count($requests), 'notification_counts' => $counts, 'malformed_or_orphan_records' => $malformed, 'needs_review' => $pending];
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 2) {
        fwrite(STDERR, "Usage: php scripts/lead-recovery-report.php /absolute/private/guest-enquiries.jsonl\n");
        exit(2);
    }
    try {
        $report = gj_lead_recovery_report($argv[1]);
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        exit($report['malformed_or_orphan_records'] > 0 ? 1 : 0);
    } catch (RuntimeException $error) {
        fwrite(STDERR, $error->getMessage() . PHP_EOL);
        exit(2);
    }
}
