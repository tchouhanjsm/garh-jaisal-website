<?php
/**
 * Local-only Content Studio preview entry point.
 *
 * It resolves a short-lived, token-bound JSON record and passes it to one of
 * the site's existing trusted PHP renderers. User input never selects a file
 * path, PHP source file, stylesheet, or include.
 */
$remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
if (!in_array($remoteAddress, ['127.0.0.1', '::1'], true)
    || !preg_match('/^(?:127\\.0\\.0\\.1|localhost)(?::[0-9]{1,5})?$/', $host)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Preview not found.\n");
}

$type = (string) ($_GET['type'] ?? '');
$token = (string) ($_GET['token'] ?? '');
if (!in_array($type, ['tours', 'blogs'], true) || !preg_match('/^[a-f0-9]{24}$/', $token)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Preview not found.\n");
}

$root = dirname(__DIR__, 2);
$slug = 'content-studio-preview-' . $token;
$directory = $root . '/content/' . ($type === 'tours' ? 'experiences' : 'editorial');
$file = $directory . '/' . $slug . '.json';
header('Cache-Control: no-store, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

if (!is_dir($directory) || is_link($directory) || !is_file($file) || is_link($file)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Preview not found or expired.\n");
}

$record = json_decode((string) file_get_contents($file), true);
$expiresAt = is_array($record) ? ($record['studio_preview_expires_at'] ?? null) : null;
if (!is_array($record)
    || ($record['slug'] ?? null) !== $slug
    || ($record['studio_preview_token'] ?? null) !== $token
    || ($record['status'] ?? null) !== 'draft'
    || ($record['review_status'] ?? null) !== 'draft'
    || !is_int($expiresAt)
    || $expiresAt <= (int) floor(microtime(true) * 1000)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Preview not found or expired.\n");
}

if ($type === 'tours') {
    $experienceSlug = $slug;
    require $root . '/include/experience-page.php';
    exit;
}

$editorialGuideSlug = $slug;
require $root . '/include/editorial-guide-page.php';
