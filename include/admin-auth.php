<?php
/**
 * Server-side HTTP Basic authentication for the owner Media Library report.
 * Credentials are loaded from include/site-config.local.php via site-config.php.
 */
if (!function_exists('gj_media_admin_auth_configured')) {
    function gj_media_admin_auth_configured($config)
    {
        $settings = is_array($config['media_admin_auth'] ?? null) ? $config['media_admin_auth'] : [];
        $username = $settings['username'] ?? '';
        $passwordHash = $settings['password_hash'] ?? '';
        if (!is_string($username) || trim($username) === ''
            || !is_string($passwordHash) || trim($passwordHash) === '') return false;
        $info = password_get_info($passwordHash);
        return is_array($info) && isset($info['algoName']) && $info['algoName'] !== 'unknown';
    }
}

if (!function_exists('gj_media_admin_request_is_https')) {
    function gj_media_admin_request_is_https($server = null)
    {
        $server = is_array($server) ? $server : $_SERVER;
        if ((isset($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off' && (string) $server['HTTPS'] !== '')
            || (isset($server['SERVER_PORT']) && (string) $server['SERVER_PORT'] === '443')) return true;
        $remote = $server['REMOTE_ADDR'] ?? '';
        $host = strtolower((string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? ''));
        return in_array($remote, ['127.0.0.1', '::1'], true)
            && in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    }
}

if (!function_exists('gj_media_admin_credentials')) {
    function gj_media_admin_credentials($server = null)
    {
        $server = is_array($server) ? $server : $_SERVER;
        $username = $server['PHP_AUTH_USER'] ?? null;
        $password = $server['PHP_AUTH_PW'] ?? null;
        if (is_string($username) && is_string($password)) return [$username, $password];
        $header = $server['HTTP_AUTHORIZATION'] ?? ($server['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if (is_string($header) && preg_match('/^Basic\s+([A-Za-z0-9+\/=]+)$/iD', trim($header), $matches)) {
            $decoded = base64_decode($matches[1], true);
            if (is_string($decoded) && strpos($decoded, ':') !== false) return explode(':', $decoded, 2);
        }
        return [null, null];
    }
}

if (!function_exists('gj_media_admin_is_authorized')) {
    function gj_media_admin_is_authorized($config, $server = null)
    {
        if (!gj_media_admin_auth_configured($config) || !gj_media_admin_request_is_https($server)) return false;
        $settings = $config['media_admin_auth'];
        list($username, $password) = gj_media_admin_credentials($server);
        if (!is_string($username) || !is_string($password)) return false;
        return hash_equals(trim($settings['username']), $username)
            && password_verify($password, $settings['password_hash']);
    }
}

if (!function_exists('gj_require_media_admin_access')) {
    function gj_require_media_admin_access($rootPath = null, $isJson = null)
    {
        $rootPath = is_string($rootPath) && $rootPath !== '' ? $rootPath : dirname(__DIR__);
        $isJson = is_bool($isJson) ? $isJson : (isset($_GET['format']) && $_GET['format'] === 'json');
        header('X-Robots-Tag: noindex, nofollow');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Cache-Control: private, no-store, max-age=0, must-revalidate');
        header('Vary: Authorization');

        $configPath = $rootPath . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'site-config.php';
        $config = [];
        if (is_file($configPath) && !is_link($configPath)) {
            $loaded = require $configPath;
            if (is_array($loaded)) $config = $loaded;
        }
        $deny = static function ($status, $message) use ($isJson) {
            http_response_code($status);
            if ($status === 401) header('WWW-Authenticate: Basic realm="Garh Jaisal owner media"');
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
            } else {
                header('Content-Type: text/plain; charset=utf-8');
                echo $message;
            }
            exit;
        };
        if (!gj_media_admin_auth_configured($config)) $deny(503, 'Owner media access is not configured.');
        if (!gj_media_admin_request_is_https()) $deny(403, 'Owner media access requires HTTPS.');
        if (!gj_media_admin_is_authorized($config)) $deny(401, 'Authentication required.');
    }
}
