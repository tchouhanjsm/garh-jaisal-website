<?php
require_once __DIR__ . '/include/session.php';

$enquiryReceived = !empty($_SESSION['enquiry_received']);
$enquiryReference = is_string($_SESSION['enquiry_reference'] ?? null) ? $_SESSION['enquiry_reference'] : '';
$enquiryNotificationSent = !empty($_SESSION['enquiry_notification_sent']);

// Consume the success flag before rendering. Refreshing this page must not emit
// a second generate_lead event or repeat the one-time enquiry confirmation.
unset($_SESSION['enquiry_received'], $_SESSION['enquiry_reference'], $_SESSION['enquiry_notification_sent']);

$siteConfig = require __DIR__ . '/include/site-config.php';
$page = [
    'title' => 'Thank you | Garh Jaisal Haveli',
    'description' => 'Confirmation page for an enquiry to Garh Jaisal Haveli.',
    'path' => '/thank-you.php',
    'robots' => 'noindex,nofollow',
    'css' => [],
];
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/include/page-head.php'; ?>
<body>
<?php
$activePage = '';
include __DIR__ . '/include/header-v2.php';
?>

<main class="section">
  <div class="site-shell">
    <p class="eyebrow">Garh Jaisal Haveli</p>
    <h1>Thank you.</h1>
    <?php if ($enquiryReceived): ?>
      <p>Your enquiry was recorded<?= $enquiryReference !== '' ? ' with reference <strong>' . htmlspecialchars($enquiryReference, ENT_QUOTES, 'UTF-8') . '</strong>' : '' ?>.</p>
      <?php if ($enquiryNotificationSent): ?>
        <p>An email notification was sent to the hotel. For anything time-sensitive, call <a href="tel:+917727047567">+91 77270 47567</a>.</p>
      <?php else: ?>
        <p>Automated email notification could not be confirmed. Please call <a href="tel:+917727047567">+91 77270 47567</a> or message the hotel on WhatsApp and quote your reference, if shown, to make sure the enquiry is seen.</p>
      <?php endif; ?>
    <?php else: ?>
      <p>Thank you for visiting Hotel Garh Jaisal Haveli in Jaisalmer Fort.</p>
    <?php endif; ?>
    <p><a class="button button-primary" href="index.php">Back to home</a></p>
    <p><a class="button button-primary" href="contact.php">Contact the hotel</a></p>
  </div>
</main>

<?php if ($enquiryReceived): ?>
<script>
  // This confirmation is reached only after mail.php has validated and stored
  // the enquiry. Never send a guest's contact details or free-text message to GA4.
  if (typeof window.gtag === 'function') {
    window.gtag('event', 'generate_lead', {
      lead_source: 'hotel_website_contact_form',
      method: 'contact_form',
      page_context: 'contact',
      transport_type: 'beacon'
    });
  }
</script>
<?php endif; ?>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
