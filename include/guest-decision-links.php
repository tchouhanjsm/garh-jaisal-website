<?php
/**
 * Safe destinations for the guest decision component.
 *
 * Primary actions are restricted to local PHP routes or in-page fragments.
 * Secondary external actions must use the configured booking or WhatsApp
 * destination; arbitrary hosts and protocol-relative URLs are rejected.
 */
function gj_guest_decision_safe_internal_href($href)
{
    if (!is_string($href) || $href === '' || trim($href) !== $href
        || preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
        return '';
    }

    if ($href[0] === '#') {
        return preg_match('/^#[A-Za-z][A-Za-z0-9_:.-]*$/D', $href) ? $href : '';
    }

    if (preg_match('/^(?:[A-Za-z][A-Za-z0-9+.-]*:|\/\/)/', $href) || strpos($href, '..') !== false) {
        return '';
    }

    return preg_match('/^[A-Za-z0-9][A-Za-z0-9._\/-]*(?:\?[A-Za-z0-9_.~%&=+-]*)?$/D', $href)
        ? $href
        : '';
}

/**
 * Only permit HTTPS URLs whose host and path match site-config.php.
 * The booking URL must retain its exact query (including the configured
 * booking token); WhatsApp may add its normal prefilled-message query.
 */
function gj_guest_decision_safe_external_href($href, $siteConfig)
{
    if (!is_string($href) || $href === '' || trim($href) !== $href
        || preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
        return '';
    }

    $candidate = parse_url($href);
    if (!is_array($candidate)
        || strtolower((string) ($candidate['scheme'] ?? '')) !== 'https'
        || empty($candidate['host'])
        || isset($candidate['user'])
        || isset($candidate['pass'])
        || isset($candidate['port'])
        || isset($candidate['fragment'])) {
        return '';
    }

    foreach (['booking_url', 'whatsapp_url'] as $key) {
        $configuredUrl = is_array($siteConfig) ? ($siteConfig[$key] ?? '') : '';
        $configured = is_string($configuredUrl) ? parse_url($configuredUrl) : false;
        if (!is_array($configured)
            || strtolower((string) ($configured['scheme'] ?? '')) !== 'https'
            || empty($configured['host'])) {
            continue;
        }

        $sameHost = strtolower((string) $candidate['host']) === strtolower((string) $configured['host']);
        $samePath = (string) ($candidate['path'] ?? '/') === (string) ($configured['path'] ?? '/');
        if (!$sameHost || !$samePath) {
            continue;
        }

        if ($key === 'booking_url' && (string) ($candidate['query'] ?? '') !== (string) ($configured['query'] ?? '')) {
            continue;
        }

        return $href;
    }

    return '';
}
