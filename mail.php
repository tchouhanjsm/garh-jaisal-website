<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
require_once __DIR__ . '/include/session.php';
require_once __DIR__ . '/include/lead-storage.php';
require_once __DIR__ . '/include/enquiry-recipients.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: contact.php');
    exit;
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
$sessionToken = (string) ($_SESSION['contact_csrf'] ?? '');
if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: contact.php?error=invalid');
    exit;
}

$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    header('Location: contact.php?error=send');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$consent = trim((string) ($_POST['consent'] ?? ''));
$captcha = trim((string) ($_POST['g-recaptcha-response'] ?? ''));
if ($captcha === '') {
    header('Location: contact.php?error=captcha');
    exit;
}

$nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
$messageLength = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);

$valid = $name !== ''
    && $nameLength <= 80
    && $email !== ''
    && strlen($email) <= 120
    && !preg_match('/[\r\n]/', $email)
    && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
    && strlen($phone) <= 20
    && ($phone === '' || preg_match('/^[0-9 +\-]+$/', $phone))
    && $message !== ''
    && $messageLength <= 2000
    && $consent === '1';

if (!$valid) {
    header('Location: contact.php?error=invalid');
    exit;
}

$now = time();
$lastSubmission = (int) ($_SESSION['contact_last_submission'] ?? 0);
if ($lastSubmission > 0 && ($now - $lastSubmission) < 30) {
    header('Location: contact.php?error=send');
    exit;
}
// Apply the session throttle before making a remote CAPTCHA verification request.
$_SESSION['contact_last_submission'] = $now;

$secretKey = trim((string) ($siteConfig['recaptcha_secret'] ?? ''));
if ($secretKey === '' || preg_match('/(?:PASTE_|REPLACE_WITH_|CHANGE_ME|YOUR[_ -]?)/i', $secretKey)) {
    header('Location: contact.php?error=config');
    exit;
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$verifyPayload = http_build_query([
    'secret' => $secretKey,
    'response' => $captcha,
]);
$verifyContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
            . 'Content-Length: ' . strlen($verifyPayload) . "\r\n",
        'content' => $verifyPayload,
        'timeout' => 8,
        'ignore_errors' => true,
    ],
]);
$response = @file_get_contents($verifyUrl, false, $verifyContext);
if (!is_string($response) && function_exists('curl_init')) {
    $handle = curl_init($verifyUrl);
    if ($handle !== false) {
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $verifyPayload,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
        ]);
        $curlResponse = curl_exec($handle);
        $curlErrno = curl_errno($handle);
        curl_close($handle);
        $response = ($curlErrno === 0 && is_string($curlResponse)) ? $curlResponse : false;
    }
}
$captchaData = is_string($response) ? json_decode($response) : null;
$siteHost = strtolower((string) parse_url((string) ($siteConfig['site_url'] ?? ''), PHP_URL_HOST));
$captchaHost = is_object($captchaData) ? strtolower((string) ($captchaData->hostname ?? '')) : '';
$allowedCaptchaHosts = array_values(array_unique([
    $siteHost,
    preg_replace('/^www\\./', '', $siteHost),
]));

if (
    !is_object($captchaData)
    || empty($captchaData->success)
    || $captchaHost === ''
    || !in_array($captchaHost, $allowedCaptchaHosts, true)
) {
    header('Location: contact.php?error=captcha');
    exit;
}

$leadResult = gj_store_lead(
    'contact',
    ['name' => $name, 'email' => $email, 'phone' => $phone, 'message' => $message, 'consent' => true],
    (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    $phone,
    ['max_per_ip' => 10, 'max_per_phone' => 5]
);
if (empty($leadResult['ok'])) {
    header('Location: contact.php?error=' . (($leadResult['error'] ?? '') === 'rate_limited' ? 'send' : 'storage'));
    exit;
}
$leadReference = $leadResult['reference'];

$recipients = gj_configured_enquiry_recipients($siteConfig);

$emailTo = implode(', ', $recipients);
$subject = 'Enquiry [' . $leadReference . '] - Hotel Garh Jaisal';
$replyTo = str_replace(["\r", "\n"], '', $email);

$body = '<p>Enquiry reference: ' . htmlspecialchars($leadReference, ENT_QUOTES, 'UTF-8') . '</p>';
$body .= "<table border='2px' style='border-collapse:collapse; width:100%;'>";
$body .= '<tr><td style="padding:5px;">Name</td><td style="padding:5px;">'
    . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td></tr>';
$body .= '<tr><td style="padding:5px;">Email</td><td style="padding:5px;">'
    . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</td></tr>';
$body .= '<tr><td style="padding:5px;">Phone</td><td style="padding:5px;">'
    . htmlspecialchars($phone !== '' ? $phone : 'Not provided', ENT_QUOTES, 'UTF-8') . '</td></tr>';
$body .= '<tr><td style="padding:5px;">Message</td><td style="padding:5px;">'
    . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</td></tr>';
$body .= '</table>';

$fromAddress = trim((string) ($siteConfig['hotel_email'] ?? 'info@hotelgarhjaisal.com'));
if (preg_match('/[\r\n]/', $fromAddress) || filter_var($fromAddress, FILTER_VALIDATE_EMAIL) === false) {
    $fromAddress = 'info@hotelgarhjaisal.com';
}
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= 'From: ' . str_replace(["\r", "\n"], '', $fromAddress) . "\r\n";
$headers .= 'Reply-To: ' . $replyTo . "\r\n";

$sent = false;
if ($recipients !== []) {
    try {
        $sent = mail($emailTo, $subject, $body, $headers) === true;
    } catch (Throwable $exception) {
        error_log('Contact enquiry mail delivery failed.');
        $sent = false;
    }
}
gj_record_lead_event($leadReference, $sent ? 'owner_notification_sent' : 'owner_notification_failed');

// The enquiry has already been stored. Do not make the guest resubmit it because mail failed.
session_regenerate_id(true);
unset($_SESSION['contact_csrf']);
$_SESSION['enquiry_received'] = true;
$_SESSION['enquiry_reference'] = $leadReference;
$_SESSION['enquiry_notification_sent'] = $sent;
header('Location: thank-you.php');
exit;
