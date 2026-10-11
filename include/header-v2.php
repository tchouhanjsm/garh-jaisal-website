<?php
$siteConfig = require __DIR__ . '/site-config.php';
$bookingUrl = $bookingUrl ?? $siteConfig['booking_url'];
$hotelPhone = $hotelPhone ?? $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $hotelPhoneDisplay ?? $siteConfig['hotel_phone_display'];
$whatsappUrl = $whatsappUrl ?? $siteConfig['whatsapp_url'];
$activePage = $activePage ?? '';
?>

<header class="site-header">
  <div class="nav-wrap">
    <div class="site-shell nav-inner">

      <a class="brand" href="index.php" aria-label="Garh Jaisal Haveli home">
        <img
          src="img/jaisalmerlogo.png"
          alt="Garh Jaisal Haveli"
          width="42"
          height="42"
        >
        <span class="brand-copy">
          <strong>Garh Jaisal Haveli</strong>
          <small>Jaisalmer Fort</small>
        </span>
      </a>

      <button
        class="nav-toggle"
        type="button"
        aria-expanded="false"
        aria-controls="main-nav"
        aria-label="Open navigation"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav id="main-nav" class="main-nav" aria-label="Primary navigation">
        <a class="<?= $activePage === 'home' ? 'is-active' : '' ?>" href="index.php">Home</a>
        <a class="<?= $activePage === 'about' ? 'is-active' : '' ?>" href="about.php">About Us</a>
        <a class="<?= $activePage === 'rooms' ? 'is-active' : '' ?>" href="room.php">Rooms</a>
        <a class="<?= $activePage === 'packages' ? 'is-active' : '' ?>" href="pack.php">Packages</a>
        <a class="<?= $activePage === 'directions' ? 'is-active' : '' ?>" href="direction.php">Directions</a>
        <a class="<?= $activePage === 'gallery' ? 'is-active' : '' ?>" href="gallery.php">Gallery</a>
        <a class="<?= $activePage === 'contact' ? 'is-active' : '' ?>" href="contact.php">Contact Us</a>
        <a class="<?= $activePage === 'blog' ? 'is-active' : '' ?>" href="blog.php">Blog</a>
        <a
          class="nav-book"
          href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
          target="_blank"
          rel="noopener"
        >
          Book Now
        </a>
      </nav>
    </div>
  </div>
</header>

<div class="contact-dock" aria-label="Contact Garh Jaisal">
  <a
    class="contact-dock-item"
    href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"
    aria-label="Call Garh Jaisal at <?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?>"
  >
    <span class="contact-icon" aria-hidden="true">☎</span>
    <span>Call</span>
  </a>

  <a
    class="contact-dock-item whatsapp"
    href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>"
    target="_blank"
    rel="noopener"
    aria-label="WhatsApp Garh Jaisal"
  >
    <img src="img/whatsapp.png" alt="" width="22" height="22">
    <span>WhatsApp</span>
  </a>
</div>

<div class="mobile-cta" aria-label="Quick contact actions">
  <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" class="mobile-cta-contact">Call</a>
  <a href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="mobile-cta-whatsapp">WhatsApp</a>
  <a href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="mobile-cta-book">Book Now</a>
</div>
