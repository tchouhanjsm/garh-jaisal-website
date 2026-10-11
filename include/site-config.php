<?php
/**
 * Public site configuration.
 * Secrets are intentionally loaded only from site-config.local.php or the
 * GJ_RECAPTCHA_SECRET environment variable and are not stored in this file.
 */
$defaults = [
    'site_url' => 'https://www.hotelgarhjaisal.com',
    'booking_url' => 'https://asiatech.in/booking_engine/index3?token=NjA2Ng==',
    'hotel_phone' => '+917727047567',
    'hotel_phone_display' => '+91 77270 47567',
    'hotel_phone_alt' => '+912992253836',
    'hotel_phone_alt_display' => '+91 2992 253836',
    'whatsapp_url' => 'https://wa.me/917727047567',
    'hotel_email' => 'info@hotelgarhjaisal.com',
    'recaptcha_site_key' => '6LcPO8wqAAAAAKCGR-vt2pvgN8d0lj3flL7_DDtY',
    'recaptcha_secret' => '',
    // Owner decision needed: confirm which measurement IDs should be active.
    'analytics_ids' => ['G-0C646S1FQ0', 'G-6S5PCEVXH4'],
    // Configure real recipients in site-config.local.php on the server.
    'inquiry_recipients' => [],
];

$local = __DIR__ . '/site-config.local.php';
$overrides = is_file($local) ? require $local : [];
if (!is_array($overrides)) {
    $overrides = [];
}

$config = array_replace($defaults, $overrides);

$environmentSecret = getenv('GJ_RECAPTCHA_SECRET');
if (is_string($environmentSecret) && $environmentSecret !== '') {
    $config['recaptcha_secret'] = $environmentSecret;
}

return $config;
