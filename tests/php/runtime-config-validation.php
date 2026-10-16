<?php
require_once __DIR__ . '/../../scripts/check-runtime-config.php';

function gj_runtime_test_assert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$complete = [
    'site_url' => 'https://staging.example.test',
    'booking_url' => 'https://booking.example.test/reserve',
    'hotel_email' => 'hotel@example.test',
    'analytics_ids' => ['G-ABC123', 'G-987XYZ'],
    'recaptcha_secret' => 'test-secret-value',
    'inquiry_recipients' => ['bookings@example.test'],
];
$healthyReport = gj_runtime_config_diagnostics($complete);
gj_runtime_test_assert($healthyReport['errors'] === [], 'A complete valid configuration should have no errors.');
gj_runtime_test_assert($healthyReport['warnings'] === [], 'A complete valid configuration should have no warnings.');
gj_runtime_test_assert($healthyReport['secret_configured'] === true, 'A non-placeholder test secret should be recognized as configured.');
gj_runtime_test_assert($healthyReport['valid_recipient_count'] === 1, 'Only valid recipient addresses should be counted.');

$placeholder = $complete;
$placeholder['recaptcha_secret'] = 'PASTE_SERVER_ONLY_RECAPTCHA_SECRET_HERE';
$placeholder['inquiry_recipients'] = ['REPLACE_WITH_APPROVED_ENQUIRY_ADDRESS', 'bad-address'];
$placeholderReport = gj_runtime_config_diagnostics($placeholder);
gj_runtime_test_assert($placeholderReport['errors'] === [], 'Missing deployment secrets should be warnings, not structural errors.');
gj_runtime_test_assert(count($placeholderReport['warnings']) === 2, 'Placeholder secret and missing valid recipient should both be reported.');
gj_runtime_test_assert($placeholderReport['secret_configured'] === false, 'A sample secret must never be treated as ready.');
gj_runtime_test_assert($placeholderReport['valid_recipient_count'] === 0, 'Placeholder recipient must not be counted as valid.');
$diagnostics = json_encode($placeholderReport);
gj_runtime_test_assert(strpos($diagnostics, 'PASTE_SERVER_ONLY') === false, 'Diagnostics must never echo a secret value.');
gj_runtime_test_assert(strpos($diagnostics, 'REPLACE_WITH_APPROVED') === false, 'Diagnostics must never echo recipient values.');

$invalid = $complete;
$invalid['site_url'] = 'http://staging.example.test';
$invalid['booking_url'] = 'javascript:alert(1)';
$invalid['hotel_email'] = "hotel@example.test\r\nBcc:guest@example.test";
$invalidReport = gj_runtime_config_diagnostics($invalid);
gj_runtime_test_assert(count($invalidReport['errors']) >= 3, 'Invalid origins and header-injection email values must be rejected.');

echo "Runtime configuration validation tests passed.\n";
