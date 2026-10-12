<?php
$activePage = 'rooms';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$rooms = require __DIR__ . '/include/rooms-data.php';

$roomsByFloor = [];
foreach ($rooms as $room) {
  $roomsByFloor[$room['kicker']][] = $room;
}
$roomCount = count($rooms);
$floorCount = count($roomsByFloor);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Rooms at Garh Jaisal Haveli | Jaisalmer Fort</title>

  <meta
    name="description"
    content="Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort, including Sweet Purple, Rising Sun, Scarlet, Opium, Red Maharani, Green and BlueSky."
  >

  <meta name="robots" content="index,follow">

  <link
    rel="canonical"
    href="https://www.hotelgarhjaisal.com/room.php"
  >

  <meta property="og:type" content="website">
  <meta
    property="og:title"
    content="Rooms at Garh Jaisal Haveli | Jaisalmer Fort"
  >
  <meta
    property="og:description"
    content="Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort."
  >
  <meta
    property="og:url"
    content="https://www.hotelgarhjaisal.com/room.php"
  >
  <meta
    property="og:image"
    content="https://www.hotelgarhjaisal.com/img/purple-room.jpg"
  >

  <meta name="twitter:card" content="summary_large_image">
  <meta
    name="twitter:title"
    content="Rooms at Garh Jaisal Haveli | Jaisalmer Fort"
  >
  <meta
    name="twitter:description"
    content="Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort."
  >
  <meta
    name="twitter:image"
    content="https://www.hotelgarhjaisal.com/img/purple-room.jpg"
  >

  <link
    rel="icon"
    type="image/png"
    href="img/jaisalmerlogo.png"
  >

  <link rel="stylesheet" href="css/garh-v2.css">

  <script async src="https://www.googletagmanager.com/gtag/js?id=G-0C646S1FQ0"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag(){
      dataLayer.push(arguments);
    }

    gtag('js', new Date());
    gtag('config', 'G-0C646S1FQ0');
    gtag('config', 'G-6S5PCEVXH4');
  </script>
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@graph":[
      {
        "@type":"WebPage",
        "@id":"https://www.hotelgarhjaisal.com/room.php#webpage",
        "url":"https://www.hotelgarhjaisal.com/room.php",
        "name":"Rooms at Garh Jaisal Haveli | Jaisalmer Fort",
        "isPartOf":{"@id":"https://www.hotelgarhjaisal.com/#website"},
        "about":{"@id":"https://www.hotelgarhjaisal.com/#hotel"},
        "breadcrumb":{"@id":"https://www.hotelgarhjaisal.com/room.php#breadcrumb"}
      },
      {
        "@type":"BreadcrumbList",
        "@id":"https://www.hotelgarhjaisal.com/room.php#breadcrumb",
        "itemListElement":[
          {"@type":"ListItem","position":1,"name":"Home","item":"https://www.hotelgarhjaisal.com/"},
          {"@type":"ListItem","position":2,"name":"Rooms","item":"https://www.hotelgarhjaisal.com/room.php"}
        ]
      }
    ]
  }
  </script>
</head>

<body>

<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="rooms-page" id="main-content" tabindex="-1">

  <!-- =====================================================
       PAGE HERO
       ===================================================== -->

  <section class="page-hero page-hero--photo rooms-hero" aria-labelledby="rooms-page-title">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/purple-room.jpg" alt="" width="1600" height="1066" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell rooms-hero-grid">
      <div class="rooms-hero-copy">
        <p class="eyebrow">Stay inside Jaisalmer Fort</p>
        <h1 id="rooms-page-title">Rooms at Garh Jaisal Haveli.</h1>
        <p class="rooms-hero-lede">
          Find an individually styled heritage room, then check current rates
          and availability through our booking system.
        </p>
        <div class="rooms-hero-actions">
          <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check availability</a>
          <a class="button button-light" href="#room-directory">Explore the rooms</a>
        </div>
        <p class="rooms-hero-note">Inside Jaisalmer Fort · Ground and first-floor rooms</p>
      </div>

      <aside class="rooms-hero-summary" aria-label="Stay at a glance">
        <p class="eyebrow">Stay at a glance</p>
        <div class="rooms-summary-stat">
          <strong><?= (int) $roomCount ?></strong>
          <span>Individually styled rooms</span>
        </div>
        <div class="rooms-summary-stat">
          <strong><?= (int) $floorCount ?></strong>
          <span>Floors to explore</span>
        </div>
        <div class="rooms-summary-stat">
          <strong>Online</strong>
          <span>Check current rates and availability</span>
        </div>
      </aside>
    </div>
  </section>


  <!-- =====================================================
       INTRO
       ===================================================== -->

  <section class="section rooms-intro">
    <div class="site-shell rooms-intro-grid">

      <div>
        <p class="eyebrow">Your stay</p>

        <h2>Rooms inside the fort.</h2>
      </div>

      <div class="rooms-intro-copy">

        <p>
          Our rooms are individually styled with traditional colours,
          carved details and views across Jaisalmer.
        </p>

        <p>
          Rooms are located on the ground and first floors of the haveli.
          Facilities vary by room.
        </p>

      </div>

    </div>
  </section>

  <section id="room-directory" class="rooms-directory" aria-labelledby="room-directory-title">
    <div class="site-shell rooms-directory-head">
      <div>
        <p class="eyebrow">Explore the rooms</p>
        <h2 id="room-directory-title">Choose the room that feels right.</h2>
      </div>
      <p>Compare the room descriptions and listed features below. Room facilities vary; use the availability link to check the current rate for your stay.</p>
    </div>

  <nav class="rooms-floor-nav" aria-label="Browse rooms by floor">
    <div class="site-shell rooms-floor-nav-inner">
      <span class="rooms-floor-nav-label">Browse by floor</span>
      <div class="rooms-floor-nav-links">
        <?php foreach ($roomsByFloor as $floorKicker => $floorRooms): ?>
          <?php $floorId = 'rooms-' . trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($floorKicker)), '-'); ?>
          <a href="#<?= htmlspecialchars($floorId, ENT_QUOTES, 'UTF-8') ?>" aria-label="Browse <?= htmlspecialchars($floorKicker, ENT_QUOTES, 'UTF-8') ?>, <?= count($floorRooms) ?> rooms">
            <span><?= htmlspecialchars(ucwords($floorKicker), ENT_QUOTES, 'UTF-8') ?></span>
            <small><?= count($floorRooms) ?> rooms</small>
          </a>
        <?php endforeach; ?>
      </div>
      <a class="rooms-floor-nav-book" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check availability</a>
    </div>
  </nav>


  <?php $floorIndex = 0; ?>
  <?php foreach ($roomsByFloor as $floorKicker => $floorRooms): ?>
  <?php
    $floorClass = 'section rooms-floor';
    if ($floorIndex % 2 === 1) {
      $floorClass .= ' rooms-floor-alt';
    }
    $floorIndex++;
  ?>
  <?php $floorId = 'rooms-' . trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($floorKicker)), '-'); ?>
  <section id="<?= htmlspecialchars($floorId, ENT_QUOTES, 'UTF-8') ?>" class="<?= htmlspecialchars($floorClass, ENT_QUOTES, 'UTF-8') ?>">
    <div class="site-shell">

      <div class="room-floor-heading">
        <p class="eyebrow"><?= htmlspecialchars($floorKicker, ENT_QUOTES, 'UTF-8') ?></p>
        <h2><?= htmlspecialchars(ucwords($floorKicker) . ' Rooms', ENT_QUOTES, 'UTF-8') ?></h2>
      </div>

      <div class="room-grid">

        <?php foreach ($floorRooms as $room): ?>
        <?php $roomId = 'room-' . trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($room['name'])), '-'); ?>
        <article class="room-card" id="<?= htmlspecialchars($roomId, ENT_QUOTES, 'UTF-8') ?>" data-room-floor="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $floorKicker)), ENT_QUOTES, 'UTF-8') ?>">

          <div class="room-card-media">
            <img
              src="<?= htmlspecialchars($room['image'], ENT_QUOTES, 'UTF-8') ?>"
              alt="<?= htmlspecialchars($room['alt'], ENT_QUOTES, 'UTF-8') ?>"
              loading="lazy"
            >
          </div>

          <div class="room-card-body">

            <p class="room-kicker"><?= htmlspecialchars($room['kicker'], ENT_QUOTES, 'UTF-8') ?></p>

            <h3><?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?></h3>

            <p class="room-description">
              <?= htmlspecialchars($room['description'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <ul class="room-features">
              <?php foreach ($room['features'] as $feature): ?>
              <li><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>

            <a
              class="button button-primary room-book"
              aria-label="Check availability for <?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?>"
              href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
              target="_blank"
              rel="noopener"
            >
              Check availability
            </a>

          </div>
        </article>
        <?php endforeach; ?>

      </div>
    </div>
  </section>
  <?php endforeach; ?>
  </section><!-- /.rooms-directory -->


  <!-- =====================================================
       STAY INFORMATION
       ===================================================== -->

  <section class="section rooms-info">
    <div class="site-shell">

      <div class="rooms-info-box">

        <div>
          <p class="eyebrow">Before you book</p>

          <h2>A few things to know.</h2>
        </div>

        <div class="rooms-info-copy">

          <p>
            Garh Jaisal Haveli is inside Jaisalmer Fort. Vehicles cannot
            enter the fort lanes, so guests should expect a short walk
            from Fort Parking.
          </p>

          <p>
            Room facilities vary by room. Check the current availability
            and rate when selecting your room.
          </p>

          <a class="text-link" href="direction.php">
            Plan your arrival →
          </a>

        </div>

      </div>

    </div>
  </section>


  <!-- =====================================================
       BOOKING CTA
       ===================================================== -->

  <section class="section rooms-cta">
    <div class="site-shell rooms-cta-inner">

      <p class="eyebrow">Plan your stay</p>

      <h2>Choose your room.</h2>

      <p>
        Check current availability and rates through our booking system.
      </p>

      <a
        class="button button-primary"
        href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
        target="_blank"
        rel="noopener"
      >
        Book your stay
      </a>

    </div>
  </section>

</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>

<script src="js/garh-v2.js"></script>

</body>
</html>