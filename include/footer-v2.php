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
  <div class="site-shell footer-grid">
    <div>
      <p class="eyebrow">Garh Jaisal Haveli</p>
      <h2>Let Jaisalmer begin inside the Fort.</h2>
      <p class="footer-copy">A heritage-style stay inside Jaisalmer Fort, with characterful rooms, rooftop dining and locally arranged city and desert experiences.</p>
<?php if ($footerBookingUrl !== ''): ?>
      <div class="footer-booking-cta">
        <a class="button button-primary footer-booking-button" href="<?= htmlspecialchars($footerBookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check dates &amp; current rates</a>
        <p>Choose your dates first; contact the hotel to confirm room details or arrival questions.</p>
      </div>
<?php endif; ?>
      <div class="social-links" aria-label="Social media">
        <a href="https://www.instagram.com/garhjaisal/" target="_blank" rel="noopener">Instagram</a>
        <a href="https://www.facebook.com/garhjaisal" target="_blank" rel="noopener">Facebook</a>
        <a href="https://www.youtube.com/@garhjaisal" target="_blank" rel="noopener">YouTube</a>
        <a href="<?= htmlspecialchars($footerWhatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>

    <div>
      <p class="footer-title">Visit</p>
      <p>Fort Kotari Para<br>Inside Jaisalmer Fort<br>Jaisalmer, Rajasthan 345001</p>
      <a href="direction.php">Plan your arrival →</a>
    </div>

    <div>
      <p class="footer-title">Contact</p>
      <p><a href="tel:<?= htmlspecialchars($footerPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a><br><?php if ($footerPhoneAlt !== ''): ?><a href="tel:<?= htmlspecialchars($footerPhoneAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerPhoneAltDisplay, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?></p>
      <p><a href="mailto:<?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?></a></p>
      <a data-contact-handoff="general" href="contact.php">Send an enquiry →</a>
    </div>

    <div>
      <p class="footer-title">Plan your stay</p>
<?php foreach ($footerNav as $footerItem): ?>
      <p><a href="<?= htmlspecialchars($footerItem['url'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerItem['label'], ENT_QUOTES, 'UTF-8') ?></a></p>
<?php endforeach; ?>
    </div>
  </div>

  <div class="site-shell footer-bottom">
    <span>© <?= date('Y') ?> Hotel Garh Jaisal Haveli</span>
    <span>Jaisalmer, Rajasthan</span>
  </div>
</footer>

<?php require __DIR__ . '/ui/trip-tray.php'; ?>
