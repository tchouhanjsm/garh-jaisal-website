<?php
/**
 * Regression test for repeated source-image helper calls.
 *
 * A cached dimension lookup must not leave the responsive srcset branch using
 * an uninitialized local path. The test captures PHP warnings on PHP 7.4/8.2.
 */
$root = dirname(__DIR__, 2);
$headerHtml = '';
ob_start();
require_once $root . '/include/header-v2.php';
$headerHtml = (string) ob_get_clean();

$errors = [];
$warnings = [];
set_error_handler(static function ($severity, $message) use (&$warnings) {
    if (($severity & E_WARNING) !== 0) {
        $warnings[] = $message;
        return true;
    }
    return false;
});

$source = 'img/v2/facade.jpg';
$first = gj_image_dimension_attributes($source);
$second = gj_image_dimension_attributes($source, '100vw');

restore_error_handler();

if (!preg_match('/width="[1-9][0-9]*" height="[1-9][0-9]*"/', $first)) {
    $errors[] = 'First helper call should return positive intrinsic dimensions.';
}
if (!preg_match('/width="[1-9][0-9]*" height="[1-9][0-9]*"/', $second)) {
    $errors[] = 'Cached helper call with responsive sizes should retain intrinsic dimensions.';
}
if ($warnings) {
    $errors[] = 'Repeated image helper calls emitted PHP warning(s): ' . implode('; ', array_unique($warnings));
}
if (strpos($headerHtml, 'class="site-header"') === false) {
    $errors[] = 'The shared header fixture did not render as expected.';
}

foreach ($errors as $error) {
    fwrite(STDERR, 'FAIL: ' . $error . PHP_EOL);
}
if ($errors) {
    exit(1);
}
echo "PASS: image dimension cache can be reused with responsive sizes without PHP warnings.\n";
