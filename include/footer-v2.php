<?php
$siteConfig = require __DIR__ . '/site-config.php';
$footerPhone = $siteConfig['hotel_phone'] ?? '';
$footerPhoneDisplay = $siteConfig['hotel_phone_display'] ?? $footerPhone;
$footerPhoneAlt = $siteConfig['hotel_phone_alt'] ?? '';
$footerPhoneAltDisplay = $siteConfig['hotel_phone_alt_display'] ?? $footerPhoneAlt;
$footerEmail = $siteConfig['hotel_email'] ?? '';
$footerWhatsapp = $siteConfig['whatsapp_url'] ?? '#';
?>
<footer class="site-footer">
  <div class="site-shell footer-grid">
    <div>
      <p class="eyebrow">Garh Jaisal Haveli</p>
      <h2>Stay inside the living fort.</h2>
      <p class="footer-copy">A heritage haveli inside Jaisalmer Fort, with intimate rooms, rooftop views and desert experiences arranged from the hotel.</p>
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
      <a href="contact.php">Send an enquiry →</a>
    </div>

    <div>
      <p class="footer-title">Plan your stay</p>
      <p><a href="room.php">Rooms</a></p>
      <p><a href="roof.php">Rooftop dining</a></p>
      <p><a href="restaurant.php">Restaurant & menu</a></p>
      <p><a href="pack.php">Desert experiences</a></p>
      <p><a href="guided-jaisalmer-tour.php">Guided Jaisalmer tours</a></p>
      <p><a href="gallery.php">Gallery</a></p>
      <p><a href="blog.php">Jaisalmer travel guides</a></p>
    </div>
  </div>

  <div class="site-shell footer-bottom">
    <span>© <?= date('Y') ?> Hotel Garh Jaisal Haveli</span>
    <span>Jaisalmer, Rajasthan</span>
  </div>
</footer>
