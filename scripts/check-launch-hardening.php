<?php
/**
 * Static safety gate for Apache/cPanel launch configuration.
 * This validates intended rules, not the production host's Apache modules.
 */
$root = dirname(__DIR__);
$errors = [];
$htaccessPath = $root . '/.htaccess';
$htaccess = is_file($htaccessPath) ? file_get_contents($htaccessPath) : false;

if (!is_string($htaccess)) {
    $errors[] = '.htaccess is missing';
} else {
    $requiredRules = [
        'Options -Indexes' => 'directory listing must be disabled',
        'RewriteRule ^ https://www.hotelgarhjaisal.com%{REQUEST_URI} [R=301,L,NE]' => 'canonical HTTPS/host redirect is missing',
        'RewriteRule ^include/site-config\.local\.php$ - [F,L,NC]' => 'server-only local configuration must be blocked',
        'Header always set X-Content-Type-Options "nosniff"' => 'nosniff header is missing',
        'Header always set X-Frame-Options "SAMEORIGIN"' => 'frame protection header is missing',
        'Header always set Referrer-Policy "strict-origin-when-cross-origin"' => 'referrer policy is missing',
        'Header always set Permissions-Policy' => 'permissions policy is missing',
        'AddOutputFilterByType DEFLATE' => 'compression rules are missing',
        'ExpiresByType image/webp' => 'image cache rules are missing',
        'ErrorDocument 404 /404.php' => 'custom 404 mapping is missing',
    ];
    foreach ($requiredRules as $needle => $message) {
        if (strpos($htaccess, $needle) === false) {
            $errors[] = $message;
        }
    }
    if (strpos($htaccess, 'Strict-Transport-Security') !== false) {
        $errors[] = 'HSTS must remain deferred until the owner verifies certificate and subdomain coverage';
    }
}

$notFoundPath = $root . '/404.php';
$notFound = is_file($notFoundPath) ? file_get_contents($notFoundPath) : false;
if (!is_string($notFound)) {
    $errors[] = '404.php is missing';
} else {
    foreach ([
        "http_response_code(404)",
        "'robots' => 'noindex,follow'",
        "include/page-head.php",
        "include/header-v2.php",
        "include/footer-v2.php",
        "siteConfig['whatsapp_url']",
        "siteConfig['booking_url']",
    ] as $needle) {
        if (strpos($notFound, $needle) === false) {
            $errors[] = '404.php is missing expected recovery/SEO contract: ' . $needle;
        }
    }
}

foreach ($errors as $error) {
    fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
}
printf("%d error(s)\n", count($errors));
exit($errors ? 1 : 0);
