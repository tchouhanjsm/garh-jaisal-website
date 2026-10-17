<?php
/**
 * Only explicit server-configured recipients may receive guest enquiry mail.
 * Never fall back to the public hotel contact address: staging could otherwise
 * deliver test enquiries into the production inbox.
 */
function gj_configured_enquiry_recipients(array $siteConfig): array
{
    $configured = $siteConfig['inquiry_recipients'] ?? [];
    if (!is_array($configured)) {
        return [];
    }

    $recipients = [];
    foreach ($configured as $recipient) {
        if (!is_string($recipient)) {
            continue;
        }
        $recipient = trim($recipient);
        if ($recipient === '' || preg_match('/[\r\n]/', $recipient)
            || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            continue;
        }
        $recipients[] = $recipient;
    }

    return array_values(array_unique($recipients));
}
