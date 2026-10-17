<?php
/** First-party lead storage outside the public document root. */
function gj_lead_storage_directory(): ?string
{
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($documentRoot === false) {
        $documentRoot = realpath(dirname(__DIR__));
    }
    if ($documentRoot === false || $documentRoot === '') {
        return null;
    }

    $configured = trim((string) getenv('GJ_LEAD_STORAGE_DIR'));
    if ($configured !== '') {
        if ($configured[0] !== DIRECTORY_SEPARATOR) {
            return null;
        }
        $candidate = $configured;
    } else {
        $candidate = dirname($documentRoot) . DIRECTORY_SEPARATOR . 'gj-private-data';
    }

    if (!is_dir($candidate) && !@mkdir($candidate, 0700, true) && !is_dir($candidate)) {
        return null;
    }
    $directory = realpath($candidate);
    if ($directory === false || $directory === '') {
        return null;
    }

    $rootPrefix = rtrim($documentRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if ($directory === $documentRoot || strpos($directory . DIRECTORY_SEPARATOR, $rootPrefix) === 0) {
        return null;
    }
    @chmod($directory, 0700);
    return $directory;
}

/**
 * Store a validated request and apply per-source daily IP/mobile limits.
 * Raw IP addresses are not stored; irreversible hashes support duplicate counting.
 */
function gj_store_lead(string $source, array $fields, string $ipAddress = '', string $phone = '', array $limits = []): array
{
    $directory = gj_lead_storage_directory();
    if ($directory === null) {
        return ['ok' => false, 'error' => 'storage_unavailable'];
    }

    $path = $directory . DIRECTORY_SEPARATOR . 'guest-enquiries.jsonl';
    $handle = @fopen($path, 'c+');
    if (!is_resource($handle) || !@flock($handle, LOCK_EX)) {
        if (is_resource($handle)) {
            fclose($handle);
        }
        return ['ok' => false, 'error' => 'storage_unavailable'];
    }

    $source = preg_match('/^[a-z0-9_-]{1,48}$/iD', $source) ? strtolower($source) : 'unknown';
    $now = time();
    $today = date('Y-m-d', $now);
    $ipHash = trim($ipAddress) !== '' ? hash('sha256', trim($ipAddress)) : '';
    $digits = preg_replace('/[^0-9]/', '', $phone);
    $phoneHash = is_string($digits) && $digits !== '' ? hash('sha256', $digits) : '';
    $maxPerIp = array_key_exists('max_per_ip', $limits) ? max(0, (int) $limits['max_per_ip']) : 10;
    $maxPerPhone = array_key_exists('max_per_phone', $limits) ? max(0, (int) $limits['max_per_phone']) : 5;

    rewind($handle);
    $existing = stream_get_contents($handle);
    $ipCount = 0;
    $phoneCount = 0;
    if (is_string($existing) && $existing !== '') {
        foreach (explode("\n", $existing) as $line) {
            $record = json_decode($line, true);
            if (!is_array($record)
                || ($record['type'] ?? '') !== 'request'
                || ($record['source'] ?? '') !== $source
                || substr((string) ($record['received_at'] ?? ''), 0, 10) !== $today) {
                continue;
            }
            if ($ipHash !== '' && hash_equals($ipHash, (string) ($record['ip_hash'] ?? ''))) {
                $ipCount++;
            }
            if ($phoneHash !== '' && hash_equals($phoneHash, (string) ($record['phone_hash'] ?? ''))) {
                $phoneCount++;
            }
        }
    }

    if (($maxPerIp > 0 && $ipHash !== '' && $ipCount >= $maxPerIp)
        || ($maxPerPhone > 0 && $phoneHash !== '' && $phoneCount >= $maxPerPhone)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return ['ok' => false, 'error' => 'rate_limited'];
    }

    try {
        $reference = 'GJ-' . date('Ymd', $now) . '-' . strtoupper(bin2hex(random_bytes(4)));
    } catch (Throwable $exception) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return ['ok' => false, 'error' => 'storage_unavailable'];
    }

    $record = [
        'type' => 'request',
        'reference' => $reference,
        'received_at' => date(DATE_ATOM, $now),
        'source' => $source,
        'status' => 'received',
        'ip_hash' => $ipHash,
        'phone_hash' => $phoneHash,
        'data' => $fields,
    ];
    $encoded = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!is_string($encoded)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return ['ok' => false, 'error' => 'storage_unavailable'];
    }

    fseek($handle, 0, SEEK_END);
    $line = $encoded . "\n";
    $written = fwrite($handle, $line);
    $flushed = fflush($handle);
    @chmod($path, 0600);
    flock($handle, LOCK_UN);
    fclose($handle);

    if ($written !== strlen($line) || !$flushed) {
        return ['ok' => false, 'error' => 'storage_unavailable'];
    }
    return ['ok' => true, 'reference' => $reference];
}

/** Append a status event without copying personal data to the event record. */
function gj_record_lead_event(string $reference, string $status): bool
{
    if (!preg_match('/^GJ-[0-9]{8}-[A-F0-9]{8}$/D', $reference)
        || !preg_match('/^[a-z0-9_-]{1,48}$/iD', $status)) {
        return false;
    }
    $directory = gj_lead_storage_directory();
    if ($directory === null) {
        return false;
    }
    $path = $directory . DIRECTORY_SEPARATOR . 'guest-enquiries.jsonl';
    $handle = @fopen($path, 'ab');
    if (!is_resource($handle) || !@flock($handle, LOCK_EX)) {
        if (is_resource($handle)) {
            fclose($handle);
        }
        return false;
    }
    $encoded = json_encode([
        'type' => 'event',
        'reference' => $reference,
        'recorded_at' => date(DATE_ATOM),
        'status' => $status,
    ], JSON_UNESCAPED_SLASHES);
    $line = is_string($encoded) ? $encoded . "\n" : '';
    $written = $line !== '' ? fwrite($handle, $line) : false;
    $flushed = fflush($handle);
    @chmod($path, 0600);
    flock($handle, LOCK_UN);
    fclose($handle);
    return is_int($written) && $written === strlen($line) && $flushed;
}
