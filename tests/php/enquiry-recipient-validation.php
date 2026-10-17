<?php
require_once __DIR__ . '/../../include/enquiry-recipients.php';

function gj_recipient_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$empty = gj_configured_enquiry_recipients([
    'hotel_email' => 'production-inbox@example.com',
    'inquiry_recipients' => [],
]);
gj_recipient_test_assert($empty === [], 'missing explicit recipients must not fall back to the public hotel contact address');

$configured = gj_configured_enquiry_recipients([
    'hotel_email' => 'production-inbox@example.com',
    'inquiry_recipients' => [
        'staging-inbox@example.test',
        'staging-inbox@example.test',
        'bad-address',
        "attacker@example.test\r\nBcc:someone@example.test",
        42,
    ],
]);
gj_recipient_test_assert(
    $configured === ['staging-inbox@example.test'],
    'only unique, valid, explicitly configured recipient addresses are returned'
);

echo "PASS: enquiry notifications require explicit, validated server recipients." . PHP_EOL;
