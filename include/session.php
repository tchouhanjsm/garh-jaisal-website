<?php
/**
 * Start the site session with conservative cookie defaults.
 * Secure cookies are enabled when the request reaches PHP over HTTPS.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off' && $_SERVER['HTTPS'] !== '',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
