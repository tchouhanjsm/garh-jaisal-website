<?php
/**
 * Read generated Content Studio landing-page records.
 *
 * This registry accepts JSON records only. It never includes PHP, HTML, or CSS
 * supplied by an editor, and it refuses symlinks and malformed record identity.
 */
$studioPagesRoot = dirname(__DIR__);
$studioPagesDirectory = $studioPagesRoot . '/content/pages';
$studioPages = [];

if (!is_dir($studioPagesDirectory)) {
    return $studioPages;
}
if (is_link($studioPagesDirectory)) {
    throw new RuntimeException('Content Studio pages directory must not be a symbolic link.');
}

$studioPageNames = scandir($studioPagesDirectory);
if (!is_array($studioPageNames)) {
    throw new RuntimeException('Could not read Content Studio page records.');
}

foreach ($studioPageNames as $studioPageName) {
    if (substr($studioPageName, -5) !== '.json') {
        continue;
    }
    $studioPageSlug = substr($studioPageName, 0, -5);
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $studioPageSlug)) {
        throw new RuntimeException('Invalid Content Studio page record filename.');
    }
    $studioPagePath = $studioPagesDirectory . '/' . $studioPageName;
    if (is_link($studioPagePath) || !is_file($studioPagePath)) {
        throw new RuntimeException('Content Studio page records must be regular files.');
    }
    $studioPageJson = file_get_contents($studioPagePath);
    $studioPageRecord = is_string($studioPageJson)
        ? json_decode($studioPageJson, true)
        : null;
    if (!is_array($studioPageRecord) || array_values($studioPageRecord) === $studioPageRecord) {
        throw new RuntimeException('Invalid JSON structure in Content Studio page record: ' . $studioPageSlug);
    }
    if (($studioPageRecord['slug'] ?? null) !== $studioPageSlug) {
        throw new RuntimeException('Content Studio page record key/slug mismatch: ' . $studioPageSlug);
    }
    if (!in_array($studioPageRecord['status'] ?? 'draft', ['draft', 'live', 'hidden'], true)) {
        throw new RuntimeException('Invalid publication status in Content Studio page: ' . $studioPageSlug);
    }
    $studioPageRecord['status'] = $studioPageRecord['status'] ?? 'draft';
    $studioPages[$studioPageSlug] = $studioPageRecord;
}

return $studioPages;
