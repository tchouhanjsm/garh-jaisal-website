<?php
$bookingUrl = $bookingUrl ?? 'https://asiatech.in/booking_engine/index3?token=NjA2Ng==';

$hotelPhone = '+917727047567';
$hotelPhoneDisplay = '+91 77270 47567';
$whatsappUrl = 'https://wa.me/917727047567';

$activePage = $activePage ?? '';
?>

<header class="site-header">

  <div class="nav-wrap">
    <div class="site-shell nav-inner">

      <!-- Hotel name -->
      <a class="brand" href="index.php" aria-label="Garh Jaisal Haveli home">
        <span class="brand-copy">
          <strong>Garh Jaisal Haveli</strong>
          <small>Jaisalmer Fort</small>
        </span>
      </a>

      <!-- Center logo -->
      <a class="center-logo" href="index.php" aria-label="Garh Jaisal Haveli home">
        <img
          src="img/jaisalmerlogo.png"
          alt="Garh Jaisal Haveli"
          width="84"
          height="44"
        >
      </a>

      <!-- Desktop / mobile navigation -->
      <nav id="main-nav" class="main-nav" aria-label="Primary navigation">

        <a class="<?= $activePage === 'stay' ? 'is-active' : '' ?>" href="room.php">
          Stay
        </a>

        <a class="<?= $activePage === 'dining' ? 'is-active' : '' ?>" href="roof.php">
          Dining
        </a>

        <a class="<?= $activePage === 'desert' ? 'is-active' : '' ?>" href="pack.php">
          Desert
        </a>

        <a class="<?= $activePage === 'about' ? 'is-active' : '' ?>" href="about.php">
          About
        </a>

        <a class="<?= $activePage === 'arrival' ? 'is-active' : '' ?>" href="direction.php">
          Getting Here
        </a>

        <a class="<?= $activePage === 'gallery' ? 'is-active' : '' ?>" href="gallery.php">
          Gallery
        </a>

        <a
          class="nav-book"
          href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
          target="_blank"
          rel="noopener"
        >
          Book Now
        </a>

      </nav>

      <!-- Mobile menu -->
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

    </div>
  </div>

</header>


<!-- Floating contact actions -->
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
    class="contact-dock-item"
    href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>"
    target="_blank"
    rel="noopener"
    aria-label="WhatsApp Garh Jaisal"
  >
    <img src="img/whatsapp.png" alt="" width="22" height="22">
    <span>WhatsApp</span>
  </a>

</div>


<!-- Mobile conversion bar -->
<div class="mobile-cta">

  <a
    href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"
    class="mobile-cta-contact"
  >
    Call
  </a>

  <a
    href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>"
    target="_blank"
    rel="noopener"
    class="mobile-cta-whatsapp"
  >
    WhatsApp
  </a>

  <a
    href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
    target="_blank"
    rel="noopener"
    class="mobile-cta-book"
  >
    Book Now
  </a>

</div>