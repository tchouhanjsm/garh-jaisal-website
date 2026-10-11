<?php
$siteConfig = require __DIR__ . '/include/site-config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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
$captcha = trim((string) ($_POST['g-recaptcha-response'] ?? ''));

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
    && $messageLength <= 2000;

if (!$valid) {
    header('Location: contact.php?error=invalid');
    exit;
}

$secretKey = trim((string) ($siteConfig['recaptcha_secret'] ?? ''));
if ($secretKey === '') {
    header('Location: contact.php?error=config');
    exit;
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify?secret='
    . rawurlencode($secretKey)
    . '&response='
    . rawurlencode($captcha);
$response = @file_get_contents($verifyUrl);
if (!is_string($response) && function_exists('curl_init')) {
    $handle = curl_init($verifyUrl);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    $curlResponse = curl_exec($handle);
    curl_close($handle);
    $response = is_string($curlResponse) ? $curlResponse : false;
}
$captchaData = is_string($response) ? json_decode($response) : null;

if (!is_object($captchaData) || empty($captchaData->success)) {
    header('Location: contact.php?error=captcha');
    exit;
}

$now = time();
$lastSubmission = (int) ($_SESSION['contact_last_submission'] ?? 0);
if ($lastSubmission > 0 && ($now - $lastSubmission) < 30) {
    header('Location: contact.php?error=send');
    exit;
}
$_SESSION['contact_last_submission'] = $now;

$recipients = array_values(array_filter((array) ($siteConfig['inquiry_recipients'] ?? []), static function ($value) {
    return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL);
}));
if ($recipients === []) {
    header('Location: contact.php?error=config');
    exit;
}

$emailTo = implode(', ', $recipients);
$subject = 'Enquiry From Hotel Garh Jaisal';
$replyTo = str_replace(["\r", "\n"], '', $email);

$body = '';
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

$fromAddress = (string) ($siteConfig['hotel_email'] ?? 'info@hotelgarhjaisal.com');
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= 'From: ' . str_replace(["\r", "\n"], '', $fromAddress) . "\r\n";
$headers .= 'Reply-To: ' . $replyTo . "\r\n";

$sent = mail($emailTo, $subject, $body, $headers);
if ($sent !== true) {
    header('Location: contact.php?error=send');
    exit;
}

unset($_SESSION['contact_csrf']);
$_SESSION['enquiry_received'] = true;
header('Location: thank-you.php');
exit;
