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
    if (!@chmod($directory, 0700)) {
        return null;
    }
    clearstatcache(true, $directory);
    $permissions = @fileperms($directory);
    return is_int($permissions) && ($permissions & 0777) === 0700 ? $directory : null;
}

/** Open and lock only a regular, private lead file; reject aliases to other files. */
function gj_open_lead_log(string $path)
{
    clearstatcache(true, $path);
    if (is_link($path) || (file_exists($path) && !is_file($path))) {
        return false;
    }
    // Create new logs private from the first open, not only after chmod.
    $previousMask = umask(0077);
    try {
        $handle = @fopen($path, 'c+');
    } finally {
        umask($previousMask);
    }
    if (!is_resource($handle)) {
        return false;
    }
    if (!@flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }
    clearstatcache(true, $path);
    $opened = fstat($handle);
    $named = @lstat($path);
    if (!is_array($opened) || !is_array($named)
        || ($opened['mode'] & 0170000) !== 0100000
        || ($named['mode'] & 0170000) !== 0100000
        || $opened['dev'] !== $named['dev'] || $opened['ino'] !== $named['ino']
        || $opened['nlink'] !== 1 || !@chmod($path, 0600)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }
    // PHP may cache the pre-chmod lstat mode. Verify the actual mode of the
    // locked descriptor and invalidate path metadata for subsequent callers.
    clearstatcache(true, $path);
    $secured = fstat($handle);
    if (!is_array($secured) || ($secured['mode'] & 0777) !== 0600) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }
    return $handle;
}

/** Append under the existing lock and roll back a failed partial write. */
function gj_append_lead_line($handle, string $line): bool
{
    if ($line === '' || fseek($handle, 0, SEEK_END) !== 0) {
        return false;
    }
    $offset = ftell($handle);
    if ($offset === false) {
        return false;
    }
    // Do not concatenate a new record onto a truncated final record. Preserve
    // the damaged log for the authorised operator instead of overwriting it.
    if ($offset > 0) {
        if (fseek($handle, -1, SEEK_END) !== 0 || fread($handle, 1) !== "\n" || fseek($handle, 0, SEEK_END) !== 0) {
            return false;
        }
    }
    $written = @fwrite($handle, $line);
    if ($written === strlen($line) && @fflush($handle)) {
        return true;
    }
    @ftruncate($handle, $offset);
    @fflush($handle);
    return false;
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
    $handle = gj_open_lead_log($path);
    if (!is_resource($handle)) {
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
    $ipCount = 0;
    $phoneCount = 0;
    // Only callers opting in deduplicate identical validated requests briefly.
    // The same exclusive lock covers both duplicate lookup and append.
    $duplicateWindow = min(600, max(0, (int) ($limits['duplicate_window_seconds'] ?? 0)));
    $canMatchContact = $source === 'contact' && is_string($fields['email'] ?? null)
        && filter_var($fields['email'], FILTER_VALIDATE_EMAIL) !== false;
    $duplicateReference = '';
    $duplicateNotified = false;
    while (($line = fgets($handle)) !== false) {
        $record = json_decode($line, true);
        if (!is_array($record)) continue;
        if ($duplicateReference !== '' && ($record['type'] ?? '') === 'event'
            && ($record['reference'] ?? '') === $duplicateReference
            && ($record['status'] ?? '') === 'owner_notification_sent') {
            $duplicateNotified = true;
        }
        if ($duplicateWindow > 0 && ($record['type'] ?? '') === 'request'
            && ($record['source'] ?? '') === $source
            && ($canMatchContact || ($phoneHash !== ''
                && hash_equals($phoneHash, (string) ($record['phone_hash'] ?? ''))))
            && ($record['data'] ?? null) === $fields
            && is_string($record['reference'] ?? null)
            && preg_match('/^GJ-[0-9]{8}-[A-F0-9]{8}$/D', $record['reference'])) {
            $receivedAt = strtotime((string) ($record['received_at'] ?? ''));
            if ($receivedAt !== false && $receivedAt <= $now && $receivedAt >= $now - $duplicateWindow) {
                $duplicateReference = $record['reference'];
                $duplicateNotified = false;
            }
        }
        if (($record['type'] ?? '') !== 'request'
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
    if (!feof($handle)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return ['ok' => false, 'error' => 'storage_unavailable'];
    }

    if ($duplicateReference !== '') {
        flock($handle, LOCK_UN);
        fclose($handle);
        return ['ok' => true, 'reference' => $duplicateReference, 'duplicate' => true, 'email_notified' => $duplicateNotified];
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

    $stored = gj_append_lead_line($handle, $encoded . "\n");
    flock($handle, LOCK_UN);
    fclose($handle);

    if (!$stored) {
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
    $handle = gj_open_lead_log($path);
    if (!is_resource($handle)) {
        return false;
    }
    $encoded = json_encode([
        'type' => 'event',
        'reference' => $reference,
        'recorded_at' => date(DATE_ATOM),
        'status' => $status,
    ], JSON_UNESCAPED_SLASHES);
    $line = is_string($encoded) ? $encoded . "\n" : '';
    $stored = gj_append_lead_line($handle, $line);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $stored;
}
