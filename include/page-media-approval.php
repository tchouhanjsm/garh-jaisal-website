<?php
/**
 * Signed, asset-bound approval evidence for fixed page image slots.
 * The HMAC key belongs only in server/local configuration or a trusted CLI environment.
 */
if (!function_exists('gj_page_media_approval_payload')) {
    function gj_page_media_approval_payload($slotId, $source, $alt, $caption, $approval)
    {
        $approval = is_array($approval) ? $approval : [];
        $payload = [
            'schema' => 1,
            'slot_id' => (string) $slotId,
            'src' => (string) $source,
            'alt' => (string) $alt,
            'caption' => (string) $caption,
            'approval' => [
                'status' => (string) ($approval['status'] ?? ''),
                'source_credit' => trim((string) ($approval['source_credit'] ?? '')),
                'rights_basis' => trim((string) ($approval['rights_basis'] ?? '')),
                'visual_match_review' => trim((string) ($approval['visual_match_review'] ?? '')),
                'alt_text_review' => trim((string) ($approval['alt_text_review'] ?? '')),
                'technical_validation' => (string) ($approval['technical_validation'] ?? ''),
                'asset_sha256' => strtolower((string) ($approval['asset_sha256'] ?? '')),
                'approved_by' => trim((string) ($approval['approved_by'] ?? '')),
                'approved_at' => (string) ($approval['approved_at'] ?? ''),
            ],
        ];
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('gj_page_media_approval_sign')) {
    function gj_page_media_approval_sign($slotId, $source, $alt, $caption, $approval, $key)
    {
        if (!is_string($key) || preg_match('/^(?:[a-f0-9]{2}){32,64}$/iD', $key) !== 1) return '';
        $payload = gj_page_media_approval_payload($slotId, $source, $alt, $caption, $approval);
        if (!is_string($payload)) return '';
        return hash_hmac('sha256', $payload, $key);
    }
}

if (!function_exists('gj_page_media_approval_is_valid')) {
    function gj_page_media_approval_is_valid($slotId, $source, $alt, $caption, $approval, $imagePath, $key, $approvers)
    {
        if (!is_array($approval) || !is_string($key)
            || preg_match('/^(?:[a-f0-9]{2}){32,64}$/iD', $key) !== 1
            || !is_array($approvers) || !$approvers) return false;

        $requiredText = [
            'source_credit' => 5,
            'rights_basis' => 15,
            'visual_match_review' => 15,
            'alt_text_review' => 15,
        ];
        foreach ($requiredText as $field => $minimum) {
            $value = $approval[$field] ?? null;
            if (!is_string($value) || strlen(trim($value)) < $minimum || strlen($value) > 500) return false;
        }
        if (($approval['status'] ?? '') !== 'approved'
            || ($approval['technical_validation'] ?? '') !== 'passed') return false;

        $approver = $approval['approved_by'] ?? null;
        $allowedApprovers = [];
        foreach ($approvers as $allowed) {
            if (is_string($allowed) && trim($allowed) !== '') $allowedApprovers[] = trim($allowed);
        }
        if (!is_string($approver) || !in_array(trim($approver), $allowedApprovers, true)) return false;

        $approvedAt = $approval['approved_at'] ?? null;
        if (!is_string($approvedAt)
            || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $approvedAt)) return false;
        $approvedTimestamp = strtotime($approvedAt);
        if ($approvedTimestamp === false || $approvedTimestamp > time() + 300) return false;

        $expectedAssetHash = @hash_file('sha256', $imagePath);
        $storedAssetHash = $approval['asset_sha256'] ?? null;
        if (!is_string($expectedAssetHash) || !is_string($storedAssetHash)
            || !preg_match('/^[a-f0-9]{64}$/iD', $storedAssetHash)
            || !hash_equals(strtolower($expectedAssetHash), strtolower($storedAssetHash))) return false;

        $signature = $approval['signature'] ?? null;
        if (!is_string($signature) || !preg_match('/^[a-f0-9]{64}$/iD', $signature)) return false;
        $expectedSignature = gj_page_media_approval_sign($slotId, $source, $alt, $caption, $approval, $key);
        return $expectedSignature !== '' && hash_equals($expectedSignature, strtolower($signature));
    }
}
