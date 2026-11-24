<?php
$siteConfig = require __DIR__ . '/site-config.php';
$footerPhone = $siteConfig['hotel_phone'] ?? '';
$footerPhoneDisplay = $siteConfig['hotel_phone_display'] ?? $footerPhone;
$footerPhoneAlt = $siteConfig['hotel_phone_alt'] ?? '';
$footerPhoneAltDisplay = $siteConfig['hotel_phone_alt_display'] ?? $footerPhoneAlt;
$footerEmail = $siteConfig['hotel_email'] ?? '';
$footerWhatsapp = $siteConfig['whatsapp_url'] ?? '#';
$footerBookingUrl = (string) ($siteConfig['booking_url'] ?? '');
$footerNavData = require __DIR__ . '/nav-data.php';
$footerNav = $footerNavData['footer'];
?>
<footer class="site-footer">
  <div class="footer-fort-art" aria-hidden="true"></div>
  <div class="site-shell footer-grid">
    <div>
      <p class="eyebrow">Garh Jaisal Haveli</p>
      <h2>Stay inside Jaisalmer Fort.</h2>
      <p class="footer-copy">A Chouhan family haveli with Heritage Rooms, rooftop dining and tours from Jaisalmer.</p>
<?php if ($footerBookingUrl !== ''): ?>
      <div class="footer-booking-cta">
        <a class="button button-primary footer-booking-button" href="<?= htmlspecialchars($footerBookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" data-cta-id="booking-footer" data-cta-location="footer-booking-band">Check dates &amp; current rates</a>
      </div>
<?php endif; ?>
      <div class="social-links" aria-label="Social media">
        <a href="https://www.instagram.com/garhjaisal/" target="_blank" rel="noopener">Instagram</a>
        <a href="https://www.facebook.com/garhjaisal" target="_blank" rel="noopener">Facebook</a>
        <a href="https://www.youtube.com/@garhjaisal" target="_blank" rel="noopener">YouTube</a>
        <a href="<?= htmlspecialchars($footerWhatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-footer" data-cta-location="footer-contact">WhatsApp</a>
      </div>
    </div>

<?php foreach ($footerNav as $footerGroup): ?>
    <nav class="footer-link-group" aria-label="<?= htmlspecialchars($footerGroup['label'], ENT_QUOTES, 'UTF-8') ?>">
      <h3 class="footer-title"><?= htmlspecialchars($footerGroup['label'], ENT_QUOTES, 'UTF-8') ?></h3>
      <ul>
<?php foreach ($footerGroup['children'] as $footerItem): ?>
        <li><a href="<?= htmlspecialchars($footerItem['url'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerItem['label'], ENT_QUOTES, 'UTF-8') ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>
<?php endforeach; ?>

    <div class="footer-visit">
      <h3 class="footer-title">Visit &amp; contact</h3>
      <p>Fort Kotari Para<br>Jaisalmer, Rajasthan 345001</p>
      <p><a href="direction.php">Plan your arrival →</a></p>
      <p><a href="tel:<?= htmlspecialchars($footerPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a><br><?php if ($footerPhoneAlt !== ''): ?><a href="tel:<?= htmlspecialchars($footerPhoneAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerPhoneAltDisplay, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?></p>
      <p><a href="mailto:<?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?></a></p>
      <a href="contact.php" data-cta-id="enquiry-footer" data-cta-location="footer-contact">Send an enquiry →</a>
    </div>
  </div>

  <div class="site-shell footer-bottom">
    <span>© <?= date('Y') ?> Hotel Garh Jaisal Haveli</span>
    <nav class="footer-policies" aria-label="Hotel policies">
      <a href="privacy.php">Privacy Policy</a>
      <a href="booking-policy.php">Booking Terms and Conditions</a>
    </nav>
  </div>
</footer>

<?php require __DIR__ . '/ui/trip-tray.php'; ?>
