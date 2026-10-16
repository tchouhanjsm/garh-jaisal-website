<?php
/**
 * Verify the shared quick-facts renderer escapes data and ignores invalid rows.
 *
 * Run with: php tests/php/at-a-glance-contract.php
 */
$root = dirname(__DIR__, 2);
$component = $root . '/include/ui/at-a-glance.php';
$errors = [];
if (!is_file($component)) {
    fwrite(STDERR, "ERROR include/ui/at-a-glance.php is missing\n");
    exit(1);
}

$atAGlance = [
    'class' => 'at-a-glance at-a-glance--test',
    'items' => [
        ['label' => 'Location', 'value' => 'Inside Jaisalmer Fort'],
        ['label' => '<script>label()</script>', 'value' => '<img src=x onerror=alert(1)>'],
        ['label' => 'Missing value', 'value' => '  '],
        'not-a-fact',
    ],
];
ob_start();
require $component;
$output = (string) ob_get_clean();

foreach ([
    'class="at-a-glance at-a-glance--test"' => 'configured safe class names must render',
    '<dt class="at-a-glance__label">Location</dt>' => 'fact label must render semantically',
    '<dd class="at-a-glance__value">Inside Jaisalmer Fort</dd>' => 'fact value must render semantically',
    '&lt;script&gt;label()&lt;/script&gt;' => 'fact labels must be escaped',
    '&lt;img src=x onerror=alert(1)&gt;' => 'fact values must be escaped',
] as $needle => $message) {
    if (strpos($output, $needle) === false) {
        $errors[] = $message;
    }
}
if (strpos($output, '<script>') !== false || strpos($output, '<img') !== false) {
    $errors[] = 'unescaped markup must never be emitted';
}
if (strpos($output, 'Missing value') !== false || strpos($output, 'not-a-fact') !== false) {
    $errors[] = 'invalid or empty facts must be omitted';
}

$atAGlance = [
    'class' => 'safe" onclick="alert(1)',
    'items' => [
        ['label' => 'Only a label', 'value' => ''],
    ],
];
ob_start();
require $component;
$emptyOutput = (string) ob_get_clean();
if (trim($emptyOutput) !== '') {
    $errors[] = 'invalid class / empty fact lists must not emit markup';
}

$atAGlance = [
    'items' => 'not-an-array',
];
ob_start();
require $component;
$malformedItemsOutput = (string) ob_get_clean();
if (trim($malformedItemsOutput) !== '') {
    $errors[] = 'malformed item collections must not emit markup';
}

foreach ($errors as $error) {
    fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
}
if ($errors) {
    printf("%d quick-facts contract error(s)\n", count($errors));
    exit(1);
}
echo "PASS: quick-facts labels/values are escaped and invalid rows are omitted\n";
