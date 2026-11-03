<?php
/**
 * Signed approval for owner-managed Gallery records.
 *
 * Legacy records are handled separately by an immutable SHA-256 baseline.
 * This helper only authorizes records carrying an explicit approval object.
 */
if (!function_exists('gj_gallery_media_approval_payload')) {
    function gj_gallery_media_approval_payload($record, $approval)
    {
        if (!is_array($record) || !is_array($approval)) return false;
        $image = is_array($record['image'] ?? null) ? $record['image'] : [];
        $sourceEvidence = is_array($record['sourceEvidence'] ?? null) ? $record['sourceEvidence'] : [];
        $visualReview = is_array($record['visualReview'] ?? null) ? $record['visualReview'] : [];
        $technicalValidation = is_array($record['technicalValidation'] ?? null) ? $record['technicalValidation'] : [];
        $clean = static function ($value) {
            return is_string($value) ? trim($value) : '';
        };
        $payload = [
            'schema' => 1,
            'id' => $clean($record['id'] ?? null),
            'category' => $clean($record['category'] ?? null),
            'status' => $clean($record['status'] ?? null),
            'review_status' => $clean($record['review_status'] ?? null),
            'sourceEvidence' => [
                'status' => $clean($sourceEvidence['status'] ?? null),
                'creator' => $clean($sourceEvidence['creator'] ?? null),
                'sourceUrl' => $clean($sourceEvidence['sourceUrl'] ?? null),
                'licenseName' => $clean($sourceEvidence['licenseName'] ?? null),
                'evidenceReference' => $clean($sourceEvidence['evidenceReference'] ?? null),
                'verifiedBy' => $clean($sourceEvidence['verifiedBy'] ?? null),
                'verifiedAt' => $clean($sourceEvidence['verifiedAt'] ?? null),
            ],
            'visualReview' => [
                'status' => $clean($visualReview['status'] ?? null),
                'evidence' => $clean($visualReview['evidence'] ?? null),
                'reviewer' => $clean($visualReview['reviewer'] ?? null),
                'reviewedAt' => $clean($visualReview['reviewedAt'] ?? null),
            ],
            'technicalValidation' => [
                'status' => $clean($technicalValidation['status'] ?? null),
                'evidenceReference' => $clean($technicalValidation['evidenceReference'] ?? null),
            ],
            'image' => [
                'src' => $clean($image['src'] ?? null),
                'alt' => $clean($image['alt'] ?? null),
                'caption' => $clean($image['caption'] ?? null),
                'crop' => $clean($image['crop'] ?? null),
            ],
            'approval' => [
                'status' => $clean($approval['status'] ?? null),
                'asset_sha256' => strtolower($clean($approval['asset_sha256'] ?? null)),
                'approved_by' => $clean($approval['approved_by'] ?? null),
                'approved_at' => $clean($approval['approved_at'] ?? null),
            ],
        ];
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('gj_gallery_media_approval_sign')) {
    function gj_gallery_media_approval_sign($record, $approval, $key)
    {
        if (!is_string($key) || preg_match('/^(?:[a-f0-9]{2}){32,64}$/iD', $key) !== 1) return '';
        $payload = gj_gallery_media_approval_payload($record, $approval);
        if (!is_string($payload)) return '';
        return hash_hmac('sha256', $payload, $key);
    }
}

if (!function_exists('gj_gallery_media_approval_date_is_valid')) {
    function gj_gallery_media_approval_date_is_valid($value)
    {
        if (!is_string($value) || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/D', $value)) return false;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        return $date instanceof DateTimeImmutable
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
    }
}

if (!function_exists('gj_gallery_media_approval_is_valid')) {
    function gj_gallery_media_approval_is_valid($record, $imagePath, $key, $approvers)
    {
        if (!is_array($record) || !is_array($record['approval'] ?? null)
            || !is_string($key) || preg_match('/^(?:[a-f0-9]{2}){32,64}$/iD', $key) !== 1
            || !is_array($approvers) || !$approvers) return false;

        $approval = $record['approval'];
        $image = is_array($record['image'] ?? null) ? $record['image'] : [];
        $sourceEvidence = is_array($record['sourceEvidence'] ?? null) ? $record['sourceEvidence'] : [];
        $visualReview = is_array($record['visualReview'] ?? null) ? $record['visualReview'] : [];
        $technicalValidation = is_array($record['technicalValidation'] ?? null) ? $record['technicalValidation'] : [];

        if (($record['status'] ?? '') !== 'live' || ($record['review_status'] ?? '') !== 'approved'
            || !in_array($record['category'] ?? '', ['hotel', 'rooms', 'desert', 'restaurant'], true)
            || ($approval['status'] ?? '') !== 'approved') return false;

        $creator = $sourceEvidence['creator'] ?? null;
        $rightsStatus = $sourceEvidence['status'] ?? '';
        $rightsReference = $sourceEvidence['evidenceReference'] ?? null;
        $rightsVerifier = $sourceEvidence['verifiedBy'] ?? null;
        $rightsDate = $sourceEvidence['verifiedAt'] ?? null;
        if (!is_string($creator) || strlen(trim($creator)) < 2 || strlen($creator) > 200
            || !is_string($rightsReference) || strlen(trim($rightsReference)) < 5 || strlen($rightsReference) > 500
            || !is_string($rightsVerifier) || strlen(trim($rightsVerifier)) < 2 || strlen($rightsVerifier) > 100
            || !gj_gallery_media_approval_date_is_valid($rightsDate)
            || !in_array($rightsStatus, ['owner-confirmed', 'licensed'], true)) return false;

        $sourceUrl = $sourceEvidence['sourceUrl'] ?? '';
        $licenseName = $sourceEvidence['licenseName'] ?? '';
        if ($sourceUrl !== '') {
            if (!is_string($sourceUrl) || filter_var($sourceUrl, FILTER_VALIDATE_URL) === false
                || strtolower((string) parse_url($sourceUrl, PHP_URL_SCHEME)) !== 'https'
                || !is_string(parse_url($sourceUrl, PHP_URL_HOST))) return false;
        }
        if ($rightsStatus === 'licensed'
            && ($sourceUrl === '' || !is_string($licenseName) || strlen(trim($licenseName)) < 2 || strlen($licenseName) > 200)) return false;

        if (($visualReview['status'] ?? '') !== 'matched'
            || !is_string($visualReview['evidence'] ?? null) || strlen(trim($visualReview['evidence'])) < 20
            || strlen($visualReview['evidence']) > 1000
            || !is_string($visualReview['reviewer'] ?? null) || strlen(trim($visualReview['reviewer'])) < 2
            || !gj_gallery_media_approval_date_is_valid($visualReview['reviewedAt'] ?? null)) return false;

        if (($technicalValidation['status'] ?? '') !== 'verified'
            || !is_string($technicalValidation['evidenceReference'] ?? null)
            || strlen(trim($technicalValidation['evidenceReference'])) < 5
            || strlen($technicalValidation['evidenceReference']) > 500) return false;

        $src = $image['src'] ?? null;
        $alt = $image['alt'] ?? null;
        $caption = $image['caption'] ?? '';
        $crop = $image['crop'] ?? null;
        if (!is_string($src) || preg_match('~^img/[A-Za-z0-9._/-]+\\.(?:avif|jpe?g|png|webp)$~iD', $src) !== 1
            || strpos($src, '..') !== false || strpos($src, chr(92)) !== false
            || !is_string($alt) || strlen(trim($alt)) < 15 || strlen($alt) > 300
            || !is_string($caption) || strlen($caption) > 180
            || !in_array($crop, ['arch', 'landscape', 'portrait', 'standard'], true)) return false;

        $approver = $approval['approved_by'] ?? null;
        $allowed = [];
        foreach ($approvers as $name) {
            if (is_string($name) && trim($name) !== '') $allowed[] = trim($name);
        }
        if (!is_string($approver) || !in_array(trim($approver), $allowed, true)) return false;

        $approvedAt = $approval['approved_at'] ?? null;
        if (!is_string($approvedAt)
            || !preg_match('/^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}(?:Z|[+-]\\d{2}:\\d{2})$/D', $approvedAt)) return false;
        $approvedTimestamp = strtotime($approvedAt);
        if ($approvedTimestamp === false || $approvedTimestamp > time() + 300) return false;

        $actualHash = @hash_file('sha256', $imagePath);
        $storedHash = $approval['asset_sha256'] ?? null;
        if (!is_string($actualHash) || !is_string($storedHash)
            || !preg_match('/^[a-f0-9]{64}$/iD', $storedHash)
            || !hash_equals(strtolower($actualHash), strtolower($storedHash))) return false;

        $signature = $approval['signature'] ?? null;
        if (!is_string($signature) || !preg_match('/^[a-f0-9]{64}$/iD', $signature)) return false;
        $expected = gj_gallery_media_approval_sign($record, $approval, $key);
        return $expected !== '' && hash_equals($expected, strtolower($signature));
    }
}
if (!function_exists('gj_gallery_media_publication_is_approved')) {
    /**
     * One publication decision for both runtime rendering and regression tests.
     * Records outside the immutable baseline require an explicit signed approval object.
     */
    function gj_gallery_media_publication_is_approved($record, $recordJson, $imagePath, $legacyBaseline, $key, $approvers)
    {
        if (!is_array($record) || !is_string($recordJson) || !is_array($legacyBaseline)) return false;
        $id = $record['id'] ?? null;
        if (!is_string($id) || $id === '') return false;

        if (array_key_exists('approval', $record)) {
            if (!is_file($imagePath)) return false;
            $imageInfo = @getimagesize($imagePath);
            $decodable = is_array($imageInfo) && !empty($imageInfo[0]) && !empty($imageInfo[1]);
            return $decodable && gj_gallery_media_approval_is_valid($record, $imagePath, $key, $approvers);
        }

        $baselineHash = $legacyBaseline[$id] ?? null;
        return is_string($baselineHash)
            && preg_match('/^[a-f0-9]{64}$/D', $baselineHash) === 1
            && hash_equals($baselineHash, hash('sha256', $recordJson));
    }
}
