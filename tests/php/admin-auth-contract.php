<?php
require_once dirname(__DIR__, 2) . '/include/admin-auth.php';

$errors = [];
$password = 'test-' . 'owner-' . 'pw';
$hash = password_hash($password, PASSWORD_DEFAULT);
$config = ['media_admin_auth' => ['username' => 'owner-test', 'password_hash' => $hash]];
$httpsServer = ['HTTPS' => 'on', 'PHP_AUTH_USER' => 'owner-test', 'PHP_AUTH_PW' => $password];

if (!gj_media_admin_auth_configured($config)) $errors[] = 'A valid password hash must configure owner authentication.';
if (!gj_media_admin_is_authorized($config, $httpsServer)) $errors[] = 'Correct HTTPS Basic credentials must authorize.';
$wrongUser = $httpsServer;
$wrongUser['PHP_AUTH_USER'] = 'another-user';
if (gj_media_admin_is_authorized($config, $wrongUser)) $errors[] = 'A different username must be rejected.';
$wrongPassword = $httpsServer;
$wrongPassword['PHP_AUTH_PW'] = 'wrong-password';
if (gj_media_admin_is_authorized($config, $wrongPassword)) $errors[] = 'An incorrect password must be rejected.';
$insecure = $httpsServer;
unset($insecure['HTTPS']);
if (gj_media_admin_is_authorized($config, $insecure)) $errors[] = 'Basic credentials must not authorize over plain HTTP.';
$missing = ['media_admin_auth' => ['username' => '', 'password_hash' => '']];
if (gj_media_admin_auth_configured($missing)) $errors[] = 'Missing local owner credentials must fail closed.';
$malformed = ['media_admin_auth' => ['username' => 'owner-test', 'password_hash' => 'not-a-password-hash']];
if (gj_media_admin_auth_configured($malformed)) $errors[] = 'Malformed password hashes must fail closed.';
$headerServer = ['HTTPS' => 'on', 'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('owner-test:' . $password)];
if (!gj_media_admin_is_authorized($config, $headerServer)) $errors[] = 'Authorization header Basic credentials must be supported.';
if (gj_media_admin_is_authorized($config, [])) $errors[] = 'Missing credentials must be rejected.';
$conflictingTls = $httpsServer;
$conflictingTls['HTTPS'] = 'off';
$conflictingTls['SERVER_PORT'] = '443';
if (gj_media_admin_request_is_https($conflictingTls)) $errors[] = 'Explicit HTTPS=off must override a conflicting port 443 value.';
$portOnlyTls = ['SERVER_PORT' => '443'];
if (!gj_media_admin_request_is_https($portOnlyTls)) $errors[] = 'Port 443 may identify TLS when the server omits the HTTPS flag.';
$emptyHttpsTls = ['HTTPS' => '', 'SERVER_PORT' => '443'];
if (!gj_media_admin_request_is_https($emptyHttpsTls)) $errors[] = 'An empty HTTPS flag must defer to port 443 when the server omits its TLS value.';

if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, 'ERROR ' . $error . PHP_EOL);
    exit(1);
}
echo "PASS owner Media Library authentication: configuration, HTTPS, valid/invalid credentials and fail-closed behavior.\n";
