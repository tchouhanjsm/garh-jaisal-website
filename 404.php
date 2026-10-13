<?php
http_response_code(404);
$activePage = '';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$page = [
    'title' => 'Page not found | Garh Jaisal Haveli',
    'description' => 'This page could not be found. Return to Garh Jaisal Haveli or contact the hotel for help planning your Jaisalmer stay.',
    'path' => '/404.php',
    'robots' => 'noindex,follow',
];
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/include/page-head.php'; ?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="section" id="main-content" tabindex="-1">
  <div class="site-shell">
    <p class="eyebrow">404 · Page not found</p>
    <h1>We couldn’t find that page.</h1>
    <p>The link may be old or the address may have been entered incorrectly. You can return to the main site or contact us for help.</p>
    <div class="actions" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:24px">
      <a class="button button-primary" href="index.php">Back to home</a>
      <a class="button button-secondary" href="room.php">Explore rooms</a>
      <a class="button button-secondary" href="contact.php">Contact the hotel</a>
      <a class="button button-secondary" href="<?= htmlspecialchars($siteConfig['whatsapp_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">WhatsApp</a>
      <a class="button button-secondary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a>
    </div>
  </div>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
