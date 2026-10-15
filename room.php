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
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/room.php';
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Where is Garh Jaisal Haveli?", 'answer' => "Garh Jaisal Haveli is inside Jaisalmer Fort. Vehicles cannot enter the fort lanes, so plan for a final walk from Fort Parking."],
    ['question' => "How do I check current room rates and availability?", 'answer' => "Use a Check availability or Book your stay link on this page to check current rates and availability for your dates."],
    ['question' => "Are all rooms on the same floor?", 'answer' => "No. The rooms are located on the ground and first floors of the haveli."],
    ['question' => "Do all rooms have the same facilities?", 'answer' => "No. Facilities vary by room; compare each room’s listed features before choosing."],
    ['question' => "Where can I find arrival instructions?", 'answer' => "The Directions page explains how to reach the fort and the final walk to the haveli."],
];
$page = [
    'title' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
    'description' => 'Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort, including Sweet Purple, Rising Sun, Scarlet, Opium, Red Maharani, Green and BlueSky.',
    'path' => '/room.php',
    'og_title' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
    'og_description' => 'Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'og_image' => '/img/purple-room.jpg',
    'twitter_title' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
    'twitter_description' => 'Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'twitter_image' => '/img/purple-room.jpg',
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $schemaPageUrl . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $schemaPageUrl . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $schemaSiteUrl . '/',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Rooms',
                    'item' => $schemaPageUrl,
                ],
            ],
        ],
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $schemaPageUrl);
require __DIR__ . '/include/page-head.php';
?>

<body>

<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="rooms-page" id="main-content" tabindex="-1">

  <!-- =====================================================
       PAGE HERO
       ===================================================== -->

  <section class="page-hero page-hero--photo rooms-hero" aria-labelledby="rooms-page-title">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/purple-room.jpg" <?= gj_image_dimension_attributes('img/v2/purple-room.jpg') ?> alt="" aria-hidden="true" fetchpriority="high" decoding="async">
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
        <?php
          $roomSlug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($room['name'])), '-');
          $roomId = 'room-' . $roomSlug;
        ?>
        <article class="room-card" id="<?= htmlspecialchars($roomId, ENT_QUOTES, 'UTF-8') ?>" data-room-floor="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $floorKicker)), ENT_QUOTES, 'UTF-8') ?>">

          <div class="room-card-media">
            <img
              src="<?= htmlspecialchars($room['image'], ENT_QUOTES, 'UTF-8') ?>"
              <?= gj_image_dimension_attributes($room['image']) ?>
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

            <?php
              $tripItem = [
                  'id' => 'room-' . $roomSlug,
                  'title' => $room['name'],
                  'url' => 'room.php#' . $roomId,
                  'group' => 'stay',
              ];
              $tripButtonClass = 'button button-light';
              require __DIR__ . '/include/ui/trip-add-button.php';
            ?>
            <a
              class="button button-primary room-book"
              aria-label="Check availability for <?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?>"
              href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
              target="_blank"
              rel="noopener"
            >
              Check availability
            </a>
            <a
              class="text-link room-enquiry-link"
              aria-label="Ask about <?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?>"
              href="contact.php?room=<?= htmlspecialchars($roomSlug, ENT_QUOTES, 'UTF-8') ?>"
            >Ask about this room →</a>

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



  <section class="section site-faq room-faq" aria-labelledby="room-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="room-faq-title">Questions before you choose a room.</h2></div>
        <p>A few practical details can help you choose a room and plan your arrival.</p>
      </div>
      <?php
      $faq = [
          'items' => $faqItems,
          'item_class' => 'faq-item',
          'answer_wrapper_class' => 'faq-answer',
      ];
      require __DIR__ . '/include/ui/faq.php';
      ?>
    </div>
  </section>
  <!-- =====================================================
       BOOKING CTA
       ===================================================== -->

  <?php
$ctaBand = [
    'section_class' => 'section rooms-cta',
    'inner_class' => 'site-shell rooms-cta-inner',
    'eyebrow' => 'Plan your stay',
    'title' => 'Choose your room.',
    'text' => 'Check current availability and rates through our booking system.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'Book your stay',
        'href' => $bookingUrl,
        'class' => 'button button-primary',
        'target' => '_blank',
        'rel' => 'noopener',
    ],
    'secondary' => [
        'label' => 'Read our guide to staying inside Jaisalmer Fort →',
        'href' => 'book-rooms-in-jaisalmer.php',
        'class' => 'text-link rooms-cta-guide-link',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>

</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>

<script src="js/garh-v2.js"></script>

</body>
</html>