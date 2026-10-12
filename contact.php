<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$activePage = 'contact';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];

if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}
$contactCsrf = $_SESSION['contact_csrf'];

$errorKey = $_GET['error'] ?? '';
if (!is_string($errorKey)) {
    $errorKey = '';
}

$errorMessages = [
    'captcha' => 'The security check did not pass. Please complete it and try again.',
    'invalid' => 'Please check your name, email, and message, then try again.',
    'send' => 'We could not send your message. Please call or email the hotel instead.',
    'config' => 'The contact form is temporarily unavailable. Please call or email the hotel instead.',
];
$errorMessage = $errorMessages[$errorKey] ?? '';

$pageTitle = 'Contact Hotel Garh Jaisal Haveli in Jaisalmer Fort';
$pageDescription = 'Write to Hotel Garh Jaisal Haveli inside Jaisalmer Fort. Call, email, or send an enquiry about rooms, arrival, and your stay in the living fort.';
$canonical = 'https://www.hotelgarhjaisal.com/contact.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/v2/sitting-room.jpg">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/v2/sitting-room.jpg">
  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/contact.css">
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-0C646S1FQ0"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-0C646S1FQ0');
    gtag('config', 'G-6S5PCEVXH4');
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ContactPage",
    "name": "Contact Hotel Garh Jaisal Haveli",
    "url": "https://www.hotelgarhjaisal.com/contact.php",
    "mainEntity": {
      "@type": "Hotel",
      "name": "Hotel Garh Jaisal Haveli",
      "telephone": ["+91 77270 47567", "+91 2992 253836"],
      "email": ["mukeshkila@yahoo.com", "info@hotelgarhjaisal.com"],
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Hotel Garh Jaisal Haveli, Fort Kotari Para",
        "addressLocality": "Jaisalmer",
        "addressRegion": "Rajasthan",
        "postalCode": "345001",
        "addressCountry": "IN"
      }
    }
  }
  </script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="contact-page" id="main-content" tabindex="-1">
  <section class="section">
    <div class="site-shell">
      <p class="eyebrow">Jaisalmer Fort</p>
      <h1>Contact Garh Jaisal Haveli in Jaisalmer Fort.</h1>
      <p class="contact-lead">Write or call Hotel Garh Jaisal Haveli. Ask about rooms, arrival, or your stay inside the fort.</p>

      <div class="actions contact-booking-actions">
        <a class="button button-primary contact-booking-button" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a>
        <a class="button button-secondary contact-arrival-link" href="direction.php">Plan your arrival</a>
      </div>

      <div class="contact-layout">
        <div class="contact-facts">
          <h2>Phone</h2>
          <p>
            <a href="tel:+917727047567">+91 77270 47567</a><br>
            <a href="tel:+912992253836">+91 2992 253836</a>
          </p>
          <h2>Email</h2>
          <p>
            <a href="mailto:mukeshkila@yahoo.com">mukeshkila@yahoo.com</a><br>
            <a href="mailto:info@hotelgarhjaisal.com">info@hotelgarhjaisal.com</a>
          </p>
          <h2>Address</h2>
          <p>Hotel Garh Jaisal Haveli, Fort Kotari Para, Jaisalmer, Rajasthan 345001</p>
        </div>

        <form class="contact-form" action="mail.php" method="post">
          <h2>Send an enquiry</h2>
          <?php if ($errorMessage !== ''): ?>
            <p class="contact-error" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <div class="contact-field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="80" autocomplete="name" required>
          </div>
          <div class="contact-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="120" autocomplete="email" required>
          </div>
          <div class="contact-field">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" maxlength="20" autocomplete="tel" inputmode="tel">
          </div>
          <div class="contact-field">
            <label for="message">Message</label>
            <textarea id="message" name="message" maxlength="2000" rows="6" required></textarea>
          </div>
          <div class="hp-field" aria-hidden="true">
            <label for="website">Leave this field empty</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($contactCsrf, ENT_QUOTES, 'UTF-8') ?>">
          <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($siteConfig['recaptcha_site_key'], ENT_QUOTES, 'UTF-8') ?>"></div>
          <button class="button button-primary" type="submit">Send message</button>
        </form>
      </div>
    </div>
  </section>

  <section class="contact-map" aria-label="Map">
    <iframe
      title="Map showing Hotel Garh Jaisal Haveli in Jaisalmer"
      src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14230.800346465852!2d70.9144008!3d26.9130118!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3947bc2f419b709f%3A0x31fe2bcd223dfd99!2sHotel%20Garh%20Jaisal%20Haveli%2C%20Jaisalmer!5e0!3m2!1sen!2sin!4v1694691227129!5m2!1sen!2sin"
      width="100%"
      height="360"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      allowfullscreen></iframe>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="js/garh-v2.js"></script>
</body>
</html>
