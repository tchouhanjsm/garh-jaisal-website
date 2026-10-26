<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
require_once __DIR__ . '/include/session.php';
require_once __DIR__ . '/include/lead-storage.php';
require_once __DIR__ . '/include/enquiry-context.php';

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

$contactContextToken = $_POST['enquiry_context_token'] ?? '';
$contactContextTokens = $_SESSION['contact_context_tokens'] ?? [];
$storedContext = null;
$contactContextTokenValid = false;
$contextNow = time();
if (
    is_string($contactContextToken)
    && preg_match('/^[a-f0-9]{32}$/D', $contactContextToken)
    && is_array($contactContextTokens)
    && isset($contactContextTokens[$contactContextToken])
    && is_array($contactContextTokens[$contactContextToken])
) {
    $candidateContext = $contactContextTokens[$contactContextToken];
    $issuedAt = isset($candidateContext['issued_at']) && is_numeric($candidateContext['issued_at'])
        ? (int) $candidateContext['issued_at']
        : 0;
    if ($issuedAt > 0 && $issuedAt <= $contextNow + 60 && $issuedAt >= $contextNow - 7200) {
        $storedContext = $candidateContext;
        $contactContextTokenValid = true;
    }
}
$enquiryContext = gj_resolve_enquiry_context(
    $storedContext['type'] ?? 'general',
    $storedContext['slug'] ?? ''
);

$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    header('Location: ' . gj_contact_context_return_url($enquiryContext, 'send'));
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$consent = trim((string) ($_POST['consent'] ?? ''));
$captcha = trim((string) ($_POST['g-recaptcha-response'] ?? ''));
if ($captcha === '') {
    header('Location: ' . gj_contact_context_return_url($enquiryContext, 'captcha'));
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
    header('Location: ' . gj_contact_context_return_url($enquiryContext, 'invalid'));
    exit;
}

$now = time();
$lastSubmission = (int) ($_SESSION['contact_last_submission'] ?? 0);
if ($lastSubmission > 0 && ($now - $lastSubmission) < 30) {
    header('Location: ' . gj_contact_context_return_url($enquiryContext, 'send'));
    exit;
}
// Apply the session throttle before making a remote CAPTCHA verification request.
$_SESSION['contact_last_submission'] = $now;

$secretKey = trim((string) ($siteConfig['recaptcha_secret'] ?? ''));
if ($secretKey === '' || preg_match('/(?:PASTE_|REPLACE_WITH_|CHANGE_ME|YOUR[_ -]?)/i', $secretKey)) {
    header('Location: ' . gj_contact_context_return_url($enquiryContext, 'config'));
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
    header('Location: ' . gj_contact_context_return_url($enquiryContext, 'captcha'));
    exit;
}

$leadResult = gj_store_lead(
    'contact',
    [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'consent' => true,
        'enquiry_context' => [
            'type' => $enquiryContext['type'],
            'slug' => $enquiryContext['slug'],
            'label' => $enquiryContext['label'],
        ],
    ],
    (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    $phone,
    ['max_per_ip' => 10, 'max_per_phone' => 5]
);
if (empty($leadResult['ok'])) {
    header('Location: ' . gj_contact_context_return_url($enquiryContext, (($leadResult['error'] ?? '') === 'rate_limited' ? 'send' : 'storage')));
    exit;
}
$leadReference = $leadResult['reference'];
if (
    $contactContextTokenValid
    && is_array($_SESSION['contact_context_tokens'] ?? null)
    && isset($_SESSION['contact_context_tokens'][$contactContextToken])
) {
    unset($_SESSION['contact_context_tokens'][$contactContextToken]);
}

$recipients = array_values(array_filter((array) ($siteConfig['inquiry_recipients'] ?? []), static function ($value) {
    return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL);
}));
if ($recipients === []) {
    $fallbackRecipient = trim((string) ($siteConfig['hotel_email'] ?? ''));
    if ($fallbackRecipient !== '' && !preg_match('/[\r\n]/', $fallbackRecipient)
        && filter_var($fallbackRecipient, FILTER_VALIDATE_EMAIL) !== false) {
        $recipients = [$fallbackRecipient];
    }
}

$emailTo = implode(', ', $recipients);
$subjectContext = trim(str_replace(["\r", "\n"], ' ', (string) ($enquiryContext['label'] ?? 'General enquiry')));
if ($subjectContext === '') {
    $subjectContext = 'General enquiry';
}
if (function_exists('mb_substr')) {
    $subjectContext = mb_substr($subjectContext, 0, 80, 'UTF-8');
} else {
    $subjectContext = substr($subjectContext, 0, 80);
}
$subject = 'Enquiry [' . $leadReference . '] - '
    . $enquiryContext['type_label'] . ': ' . $subjectContext . ' - Hotel Garh Jaisal';
$replyTo = str_replace(["\r", "\n"], '', $email);

$body = '<p>Enquiry reference: ' . htmlspecialchars($leadReference, ENT_QUOTES, 'UTF-8') . '</p>';
$body .= "<table border='2px' style='border-collapse:collapse; width:100%;'>";
$body .= '<tr><td style="padding:5px;">Enquiry type</td><td style="padding:5px;">'
    . htmlspecialchars($enquiryContext['type_label'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
$body .= '<tr><td style="padding:5px;">Requested item</td><td style="padding:5px;">'
    . htmlspecialchars($enquiryContext['label'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
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
