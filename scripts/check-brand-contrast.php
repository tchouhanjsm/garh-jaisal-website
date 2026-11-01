<?php
/**
 * Enforce minimum contrast for the approved identity token pairs.
 * This intentionally checks the documented combinations only, not every
 * computed foreground/background combination across every page.
 *
 * Usage: php scripts/check-brand-contrast.php
 */
$root = dirname(__DIR__);
$tokenCss = file_get_contents($root . '/css/tokens.css');
$baseCss = file_get_contents($root . '/css/garh-v2.css');
$storyCss = file_get_contents($root . '/css/thar-sunset-story.css');
$brandDoc = file_get_contents($root . '/docs/DESIGN-SYSTEM.md');
$svgPath = $root . '/img/brand/bastion-line.svg';

if (!is_string($tokenCss) || !is_string($baseCss) || !is_string($storyCss) || !is_string($brandDoc)) {
    fwrite(STDERR, "Brand contract files could not be read.\n");
    exit(1);
}

function gj_brand_token($name, $sources)
{
    foreach ($sources as $source) {
        if (preg_match('/--' . preg_quote($name, '/') . '\\s*:\\s*(#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?)\\s*;/', $source, $matches)) {
            $hex = substr($matches[1], 1);
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            return '#' . strtolower($hex);
        }
    }
    return null;
}

function gj_brand_luminance($hex)
{
    $hex = substr($hex, 1);
    $channels = [];
    for ($i = 0; $i < 6; $i += 2) {
        $channel = hexdec(substr($hex, $i, 2)) / 255;
        $channels[] = $channel <= 0.04045 ? $channel / 12.92 : pow(($channel + 0.055) / 1.055, 2.4);
    }
    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function gj_brand_contrast_ratio($foreground, $background)
{
    $one = gj_brand_luminance($foreground);
    $two = gj_brand_luminance($background);
    return (max($one, $two) + 0.05) / (min($one, $two) + 0.05);
}

$sources = [$tokenCss, $baseCss];
$tokenNames = ['night', 'parchment', 'gold', 'saffron', 'garnet', 'ink', 'gold-ink', 'peacock', 'alert', 'white'];
$semanticAliases = [
    'surface' => 'parchment',
    'surface-dark' => 'night',
    'action-primary' => 'saffron',
];
foreach ($semanticAliases as $semantic => $target) {
    if (!preg_match('/--' . preg_quote($semantic, '/') . '\\s*:\\s*var\\(--' . preg_quote($target, '/') . '\\)\\s*;/', $tokenCss)) {
        fwrite(STDERR, "Missing semantic alias contract: --" . $semantic . " must point to --" . $target . "." . PHP_EOL);
        exit(1);
    }
}
foreach (['surface-raised', 'surface-muted', 'surface-dark-raised', 'line', 'line-strong', 'text-muted', 'text-on-dark-muted', 'motion-standard', 'ease-standard', 'content-wide', 'fs-display', 'fs-body-large'] as $name) {
    if (!preg_match('/--' . preg_quote($name, '/') . '\\s*:\\s*(?!;)\\S+/', $tokenCss)) {
        fwrite(STDERR, "Missing semantic design token: --" . $name . "." . PHP_EOL);
        exit(1);
    }
}
$tokens = [];
foreach ($tokenNames as $name) {
    $tokens[$name] = gj_brand_token($name, $sources);
    if ($tokens[$name] === null) {
        fwrite(STDERR, "Missing literal brand colour token: --" . $name . "\n");
        exit(1);
    }
}
foreach (['radius-control', 'radius-card'] as $name) {
    if (!preg_match('/--' . preg_quote($name, '/') . '\\s*:\\s*6px\\s*;/', $tokenCss)) {
        fwrite(STDERR, "Expected --" . $name . " to be 6px in css/tokens.css.\n");
        exit(1);
    }
}

$pairs = [
    ['ink on parchment', 'ink', 'parchment'],
    ['white on saffron', 'white', 'saffron'],
    ['parchment on night', 'parchment', 'night'],
    ['gold on night', 'gold', 'night'],
    ['peacock on parchment', 'peacock', 'parchment'],
    ['alert on parchment', 'alert', 'parchment'],
    ['antique brass on parchment', 'gold-ink', 'parchment'],
    ['parchment on garnet', 'parchment', 'garnet'],
];
$failed = false;
foreach ($pairs as $pair) {
    $ratio = gj_brand_contrast_ratio($tokens[$pair[1]], $tokens[$pair[2]]);
    printf("%-34s %s:1\n", $pair[0], number_format($ratio, 2));
    if ($ratio < 4.5) {
        fwrite(STDERR, sprintf("FAIL: %s is below 4.5:1.\n", $pair[0]));
        $failed = true;
    }
}
if (!is_file($svgPath) || strpos($storyCss, 'bastion-line.svg') === false) {
    fwrite(STDERR, "The shared bastion motif must exist and be referenced by the story stylesheet.\n");
    $failed = true;
}
if (strpos($brandDoc, 'representative pairs') === false || strpos($brandDoc, 'not every possible rendered CSS combination') === false) {
    fwrite(STDERR, "Brand documentation must describe the limits of automated contrast coverage.\n");
    $failed = true;
}
if ($failed) {
    exit(1);
}
echo "Brand contrast and design-token contract passed.\n";
