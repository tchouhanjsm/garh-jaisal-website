<?php
/**
 * Validate source masters for the first-party marketing kit.
 * This contract does not approve final campaign copy.
 */
$root = dirname(__DIR__);
$directory = $root . '/marketing/templates';
$expected = [
    'house-ad.svg' => [1200, 628],
    'partner-ad.svg' => [1200, 628],
    'instagram-post.svg' => [1080, 1080],
    'story-cover.svg' => [1080, 1920],
    'whatsapp-reply-card.svg' => [1080, 1080],
    'festival-banner.svg' => [1600, 600],
];
$allowed = ['#14122B', '#F7EFE0', '#D9A441', '#B94A0E', '#5B1A2B'];
$errors = [];

$actual = [];
foreach (glob($directory . '/*.svg') ?: [] as $path) {
    $actual[] = basename($path);
}
$expectedNames = array_keys($expected);
sort($actual);
sort($expectedNames);
if ($actual !== $expectedNames) {
    $errors[] = 'Expected exactly six SVG masters; found: ' . implode(', ', $actual);
}
foreach ($expected as $filename => $dimensions) {
    $path = $directory . '/' . $filename;
    if (!is_file($path)) {
        $errors[] = $filename . ': file is missing';
        continue;
    }
    $source = file_get_contents($path);
    if (!is_string($source)) {
        $errors[] = $filename . ': file could not be read';
        continue;
    }
    [$width, $height] = $dimensions;
    if (!preg_match('/<svg\b[^>]*\bwidth="' . $width . '"[^>]*\bheight="' . $height . '"/s', $source)) {
        $errors[] = $filename . ': root dimensions must be ' . $width . ' × ' . $height;
    }
    if (strpos($source, 'viewBox="0 0 ' . $width . ' ' . $height . '"') === false) {
        $errors[] = $filename . ': viewBox must match canvas dimensions';
    }
    if (!preg_match('/<title\b[^>]*>.*?<\/title>/s', $source) || !preg_match('/<desc\b[^>]*>.*?<\/desc>/s', $source)) {
        $errors[] = $filename . ': accessible title and description are required';
    }
    if (strpos($source, '[') === false || strpos($source, ']') === false) {
        $errors[] = $filename . ': visible edit-before-publishing placeholders are required';
    }
    if (preg_match('/<rect\b[^>]*\brx="/s', $source) && strpos($source, 'rx="6"') === false) {
        $errors[] = $filename . ': rectangular cards/controls must use the approved 6px geometry';
    }
    if (preg_match('/(?:\bhref|xlink:href)\s*=|<script\b|<foreignObject\b|\son[a-z][a-z0-9_-]*\s*=/i', $source)) {
        $errors[] = $filename . ': external references, scripts, foreignObject and event attributes are not allowed';
    }
    preg_match_all('/#[0-9a-fA-F]{6}\b/', $source, $matches);
    $used = array_unique(array_map('strtoupper', $matches[0]));
    if (!$used) {
        $errors[] = $filename . ': explicit brand palette colours are required';
    }
    foreach ($used as $colour) {
        if (!in_array($colour, $allowed, true)) {
            $errors[] = $filename . ': unapproved colour ' . $colour;
        }
    }
}
if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    }
    exit(1);
}
echo 'PASS six marketing SVG masters meet canvas, accessibility, placeholder, palette and source-safety contracts.' . PHP_EOL;
