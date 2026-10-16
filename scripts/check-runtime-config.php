<?php
/**
 * Runtime configuration preflight.
 *
 * Usage:
 *   php scripts/check-runtime-config.php          # CI/development: deployment secrets warn only
 *   php scripts/check-runtime-config.php --strict # staging/release: missing secrets fail
 *
 * Deliberately never prints secret values, email addresses, tokens or URLs.
 */
function gj_runtime_config_diagnostics(array $config)
{
    $errors = [];
    $warnings = [];

    $siteUrl = isset($config['site_url']) && is_string($config['site_url']) ? trim($config['site_url']) : '';
    $siteParts = $siteUrl !== '' ? parse_url($siteUrl) : false;
    if (!is_array($siteParts)
        || strtolower((string) ($siteParts['scheme'] ?? '')) !== 'https'
        || empty($siteParts['host'])
        || isset($siteParts['user'])
        || isset($siteParts['pass'])) {
        $errors[] = 'site_url must be an absolute HTTPS URL without embedded credentials.';
    }

    $bookingUrl = isset($config['booking_url']) && is_string($config['booking_url']) ? trim($config['booking_url']) : '';
    $bookingParts = $bookingUrl !== '' ? parse_url($bookingUrl) : false;
    if (!is_array($bookingParts)
        || strtolower((string) ($bookingParts['scheme'] ?? '')) !== 'https'
        || empty($bookingParts['host'])
        || isset($bookingParts['user'])
        || isset($bookingParts['pass'])) {
        $errors[] = 'booking_url must be an absolute HTTPS URL without embedded credentials.';
    }

    $hotelEmail = isset($config['hotel_email']) && is_string($config['hotel_email']) ? trim($config['hotel_email']) : '';
    if (filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n]/', $hotelEmail)) {
        $errors[] = 'hotel_email must be a valid single email address.';
    }

    $analyticsIds = $config['analytics_ids'] ?? [];
    if (!is_array($analyticsIds)) {
        $errors[] = 'analytics_ids must be an array.';
    } else {
        foreach ($analyticsIds as $analyticsId) {
            if (!is_string($analyticsId) || !preg_match('/^G-[A-Z0-9]+$/', $analyticsId)) {
                $errors[] = 'analytics_ids contains an invalid Google Analytics measurement ID.';
                break;
            }
        }
    }

    $secret = isset($config['recaptcha_secret']) && is_string($config['recaptcha_secret']) ? trim($config['recaptcha_secret']) : '';
    $secretConfigured = $secret !== '' && !preg_match('/(?:PASTE_|REPLACE_WITH_|CHANGE_ME|YOUR[_ -]?)/i', $secret);
    if (!$secretConfigured) {
        $warnings[] = 'Server-side reCAPTCHA secret is missing or appears to be a placeholder.';
    }

    $recipients = isset($config['inquiry_recipients']) && is_array($config['inquiry_recipients']) ? $config['inquiry_recipients'] : [];
    $validRecipientCount = 0;
    foreach ($recipients as $recipient) {
        if (is_string($recipient) && preg_match('/[\r\n]/', $recipient) !== 1
            && filter_var(trim($recipient), FILTER_VALIDATE_EMAIL) !== false) {
            $validRecipientCount++;
        }
    }
    if ($validRecipientCount === 0) {
        $warnings[] = 'No valid enquiry-recipient email address is configured.';
    }

    return [
        'errors' => array_values(array_unique($errors)),
        'warnings' => array_values(array_unique($warnings)),
        'secret_configured' => $secretConfigured,
        'valid_recipient_count' => $validRecipientCount,
    ];
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $config = require __DIR__ . '/../include/site-config.php';
    $strict = in_array('--strict', $argv, true);
    $report = gj_runtime_config_diagnostics(is_array($config) ? $config : []);

    echo "Runtime configuration preflight\n";
    echo 'reCAPTCHA secret: ' . ($report['secret_configured'] ? 'configured' : 'missing/placeholder') . PHP_EOL;
    echo 'Valid enquiry recipients: ' . $report['valid_recipient_count'] . PHP_EOL;
    foreach ($report['warnings'] as $warning) {
        fwrite($strict ? STDERR : STDOUT, ($strict ? 'ERROR ' : 'WARN  ') . $warning . PHP_EOL);
    }
    foreach ($report['errors'] as $error) {
        fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    }

    $failed = !empty($report['errors']) || ($strict && !empty($report['warnings']));
    if (!$failed) {
        echo $strict
            ? "Runtime configuration is ready for staging/release.\n"
            : "Runtime configuration structure is valid. Deployment-only warnings may be expected in CI.\n";
    }
    exit($failed ? 1 : 0);
}
