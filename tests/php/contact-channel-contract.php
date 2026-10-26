<?php
/**
 * Guard against public contact-channel destinations drifting between pages.
 * Runs with the public configuration defaults; no local secret file is needed.
 */
$root = dirname(__DIR__, 2);
$config = require $root . '/include/site-config.php';
$errors = [];
$expected = [
    'hotel_phone' => '+917727047567',
    'hotel_phone_display' => '+91 77270 47567',
    'hotel_phone_alt' => '+912992253836',
    'hotel_phone_alt_display' => '+91 2992 253836',
    'hotel_email' => 'info@hotelgarhjaisal.com',
    'hotel_email_alt' => 'mukeshkila@yahoo.com',
];
foreach ($expected as $key => $value) {
    if (!isset($config[$key]) || !is_string($config[$key]) || $config[$key] !== $value) {
        $errors[] = 'Public contact value changed unexpectedly: ' . $key;
    }
}
$checks = [
    'contact.php' => [
        '$hotelPhone = (string) ($siteConfig[\'hotel_phone\'] ?? \'\');',
        '$hotelPhoneAlt = (string) ($siteConfig[\'hotel_phone_alt\'] ?? \'\');',
        '$hotelEmail = (string) ($siteConfig[\'hotel_email\'] ?? \'\');',
        '$hotelEmailAlt = (string) ($siteConfig[\'hotel_email_alt\'] ?? \'\');',
    ],
    'direction.php' => [
        '$hotelPhone = $siteConfig[\'hotel_phone\'];',
        '$hotelPhoneAlt = $siteConfig[\'hotel_phone_alt\'];',
        '$hotelEmail = (string) ($siteConfig[\'hotel_email\'] ?? \'\');',
        '$hotelEmailAlt = (string) ($siteConfig[\'hotel_email_alt\'] ?? \'\');',
    ],
    'roof.php' => ['$hotelPhone = (string) ($siteConfig[\'hotel_phone\'] ?? \'\');'],
];
$legacy = [
    'contact.php' => ['tel:+917727047567', 'tel:+912992253836', 'mailto:mukeshkila@yahoo.com', 'mailto:info@hotelgarhjaisal.com'],
    'direction.php' => ['mailto:info@hotelgarhjaisal.com', 'mailto:mukeshkila@yahoo.com'],
    'roof.php' => ['tel:+917727047567'],
];
foreach ($checks as $file => $markers) {
    $path = $root . '/' . $file;
    $source = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($source)) {
        $errors[] = 'Missing or unreadable contact page: ' . $file;
        continue;
    }
    foreach ($markers as $marker) {
        if (strpos($source, $marker) === false) $errors[] = $file . ' must use shared contact config; missing ' . $marker;
    }
    foreach ($legacy[$file] as $legacyHref) {
        if (strpos($source, $legacyHref) !== false) $errors[] = $file . ' still hard-codes ' . $legacyHref;
    }
}
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS contact-channel consistency." . PHP_EOL;
