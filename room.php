<?php
$activePage = 'rooms';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$rooms = require __DIR__ . '/include/rooms-data.php';
$roomPhotoDetails = require __DIR__ . '/include/room-photo-details.php';

$roomsByFloor = [];
foreach ($rooms as $room) {
  $roomsByFloor[$room['kicker']][] = $room;
}
$roomCount = count($rooms);
$floorCount = count($roomsByFloor);
$roomFloorNames = array_map('strtolower', array_keys($roomsByFloor));
$roomFloorSummary = $roomFloorNames ? ucfirst(implode(' and ', $roomFloorNames)) : 'See individual room details';

$normalizeRoomFloor = static function ($label) {
    return trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $label)), '-');
};

$roomFeatureCounts = [];
foreach ($rooms as $room) {
    $seenFeatures = [];
    foreach (($room['features'] ?? []) as $feature) {
        $featureLabel = trim((string) $feature);
        if ($featureLabel === '') {
            continue;
        }
        $featureKey = strtolower($featureLabel);
        if (isset($seenFeatures[$featureKey])) {
            continue;
        }
        $seenFeatures[$featureKey] = true;
        if (!isset($roomFeatureCounts[$featureKey])) {
            $roomFeatureCounts[$featureKey] = [
                'key' => $featureKey,
                'label' => $featureLabel,
                'count' => 0,
            ];
        }
        $roomFeatureCounts[$featureKey]['count']++;
    }
}
$roomFeatureOptions = array_values($roomFeatureCounts);
usort($roomFeatureOptions, static function ($left, $right) {
    return strcasecmp($left['label'], $right['label']);
});
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/room.php';
require_once __DIR__ . '/include/schema.php';
$hotelRoomNodes = gj_schema_hotel_rooms($rooms, $schemaSiteUrl, $schemaPageUrl);
$hotelRoomReferences = array_map(static function ($roomNode) {
    return ['@id' => $roomNode['@id']];
}, $hotelRoomNodes);
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Rooms'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($schemaSiteUrl, '/room.php', $breadcrumbs);
$faqItems = [
    ['question' => "Where is Garh Jaisal Haveli?", 'answer' => "Garh Jaisal Haveli is inside Jaisalmer Fort. Vehicles cannot enter the fort lanes, so plan for a final walk from Fort Parking."],
    ['question' => "How do I check current room rates and availability?", 'answer' => "Use a Check availability or Book your stay link on this page to check current rates and availability for your dates."],
    ['question' => "Are all rooms on the same floor?", 'answer' => "No. The rooms are located on the ground and first floors of the haveli."],
    ['question' => "Do all rooms have the same facilities?", 'answer' => "No. Facilities vary by room; compare each room’s listed features before choosing."],
    ['question' => "Where can I find arrival instructions?", 'answer' => "The Directions page explains how to reach the fort and the final walk to the haveli."],
];
$buildRoomHeroPreload = static function () {
    $srcset = [];
    foreach ([480, 800, 1200] as $candidateWidth) {
        $candidate = '/img/responsive/v2/sitting-room-' . $candidateWidth . '.webp';
        if (is_file(__DIR__ . $candidate) && !is_link(__DIR__ . $candidate)) {
            $srcset[] = $candidate . ' ' . $candidateWidth . 'w';
        }
    }

    $descriptor = [
        'href' => $srcset ? strtok($srcset[0], ' ') : '/img/optimized/v2/sitting-room.webp',
        'fetchpriority' => 'high',
    ];
    if ($srcset) {
        $descriptor['imagesrcset'] = implode(', ', $srcset);
        $descriptor['imagesizes'] = '100vw';
    }
    return $descriptor;
};

$page = [
    'title' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
    'description' => 'Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort, including Sweet Purple, Rising Sun, Scarlet, Opium, Red Maharani, Green and BlueSky.',
    'path' => '/room.php',
    'preload' => [$buildRoomHeroPreload()],
    'og_title' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
    'og_description' => 'Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'og_image' => '/img/v2/sitting-room.jpg',
    'twitter_title' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
    'twitter_description' => 'Explore the heritage rooms at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'twitter_image' => '/img/v2/sitting-room.jpg',
    'css' => ['css/thar-sunset-story.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'Rooms at Garh Jaisal Haveli | Jaisalmer Fort',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $schemaPageUrl . '#breadcrumb'],
            'mainEntity' => $hotelRoomReferences,
        ],
        $breadcrumbSchema,
        ...$hotelRoomNodes,
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $schemaPageUrl);
require __DIR__ . '/include/page-head.php';
?>

<body>

<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="rooms-page thar-sunset-story thar-sunset-story--rooms" id="main-content" tabindex="-1">

  <!-- =====================================================
       PAGE HERO
       ===================================================== -->

  <section class="page-hero page-hero--photo rooms-hero" aria-labelledby="rooms-page-title">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/optimized/v2/sitting-room.webp" <?= gj_image_dimension_attributes('img/optimized/v2/sitting-room.webp', '100vw') ?> alt="" aria-hidden="true" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell rooms-hero-grid">
      <div class="rooms-hero-copy">
        <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
        <p class="eyebrow">Stay inside Jaisalmer Fort</p>
        <h1 id="rooms-page-title">Find your room inside Jaisalmer Fort.</h1>
        <p class="rooms-hero-lede">
          Compare the room photos and details, then check current rates, availability and room-specific facilities for your dates.
        </p>
        <div class="rooms-hero-actions">
          <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check availability</a>
          <a class="button button-light" href="#room-directory">Explore the rooms</a>
        </div>
        <p class="rooms-hero-note">Inside Jaisalmer Fort · Ground and first-floor rooms</p>
        <p class="rooms-hero-guide"><a href="book-rooms-in-jaisalmer.php">How direct booking works <span aria-hidden="true">→</span></a></p>
      </div>

      <aside class="rooms-hero-summary" aria-label="Stay at a glance">
        <p class="eyebrow">Stay at a glance</p>
        <?php
        $atAGlance = [
            'class' => 'at-a-glance at-a-glance--stacked',
            'items' => [
                ['label' => 'Room selection', 'value' => $roomCount . ' individually styled rooms'],
                ['label' => 'Floor layout', 'value' => $roomFloorSummary],
                ['label' => 'Room facilities', 'value' => 'Vary by room'],
                ['label' => 'Rates and availability', 'value' => 'Check current rates for your dates'],
            ],
        ];
        require __DIR__ . '/include/ui/at-a-glance.php';
        ?>
      </aside>
    </div>
  </section>


  <!-- =====================================================
       INTRO
       ===================================================== -->

  <section class="section rooms-intro" data-story-reveal>
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

    <section class="site-shell room-finder" data-room-finder hidden aria-labelledby="room-finder-title">
      <div class="room-finder-top">
        <div class="room-finder-intro">
          <p class="eyebrow">Make a shortlist</p>
          <h3 id="room-finder-title">Find a room by its details.</h3>
          <p>Search the room names, descriptions or listed features, then combine a floor and feature filter to narrow the selection.</p>
        </div>
        <label class="room-finder-search">
          <span>Search rooms and features</span>
          <input type="search" data-room-search placeholder="Try “BlueSky” or “window seat”" autocomplete="off">
        </label>
      </div>

      <div class="room-finder-controls">
        <label class="room-finder-floor">
          <span>Floor</span>
          <select data-room-floor-filter>
            <option value="all">All floors</option>
            <?php foreach ($roomsByFloor as $floorKicker => $floorRooms): ?>
              <option value="<?= htmlspecialchars($normalizeRoomFloor($floorKicker), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords($floorKicker), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <fieldset class="room-feature-filter">
          <legend>Filter by listed feature</legend>
          <div class="room-feature-filter-options">
            <button class="room-filter-chip" type="button" data-room-filter-feature="all" aria-pressed="true">Any feature</button>
            <?php foreach ($roomFeatureOptions as $roomFeature): ?>
              <button class="room-filter-chip" type="button" data-room-filter-feature="<?= htmlspecialchars($roomFeature['key'], ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false">
                <span><?= htmlspecialchars($roomFeature['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <small><?= (int) $roomFeature['count'] ?> <?= $roomFeature['count'] === 1 ? 'room' : 'rooms' ?></small>
              </button>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <button class="room-finder-reset" type="button" data-room-reset>Clear filters</button>
      </div>

      <p class="room-finder-status" data-room-result-status role="status" aria-live="polite"><?= $roomCount ?> rooms to explore.</p>
      <div class="room-finder-empty" data-room-empty hidden>
        <p><strong>No rooms match those filters.</strong> Adjust the search or clear the filters to see all rooms.</p>
        <button class="room-finder-reset" type="button" data-room-reset>Clear filters</button>
      </div>
    </section>

  <nav class="rooms-floor-nav" aria-label="Browse rooms by floor">
    <div class="site-shell rooms-floor-nav-inner">
      <span class="rooms-floor-nav-label">Browse by floor</span>
      <div class="rooms-floor-nav-links">
        <?php foreach ($roomsByFloor as $floorKicker => $floorRooms): ?>
          <?php $floorId = 'rooms-' . trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($floorKicker)), '-'); ?>
          <a href="#<?= htmlspecialchars($floorId, ENT_QUOTES, 'UTF-8') ?>" data-room-floor-link="<?= htmlspecialchars($normalizeRoomFloor($floorKicker), ENT_QUOTES, 'UTF-8') ?>" aria-label="Browse <?= htmlspecialchars($floorKicker, ENT_QUOTES, 'UTF-8') ?>, <?= count($floorRooms) ?> rooms">
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
  <section id="<?= htmlspecialchars($floorId, ENT_QUOTES, 'UTF-8') ?>" class="<?= htmlspecialchars($floorClass, ENT_QUOTES, 'UTF-8') ?>" data-room-floor-group="<?= htmlspecialchars($normalizeRoomFloor($floorKicker), ENT_QUOTES, 'UTF-8') ?>" data-story-reveal>
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
          $roomPhotoSlides = [[
              'src' => $room['image'],
              'alt' => $room['alt'],
              'caption' => $room['name'],
          ]];
          if (isset($roomPhotoDetails[$roomSlug]) && is_array($roomPhotoDetails[$roomSlug])) {
              $roomPhotoSlides[] = $roomPhotoDetails[$roomSlug];
          }
        ?>
        <?php
          $roomFeatureKeys = array_values(array_unique(array_map(static function ($feature) {
              return strtolower(trim((string) $feature));
          }, $room['features'])));
          $roomFeatureData = '|' . implode('|', $roomFeatureKeys) . '|';
        ?>
        <article class="room-card" id="<?= htmlspecialchars($roomId, ENT_QUOTES, 'UTF-8') ?>" data-room-floor="<?= htmlspecialchars($normalizeRoomFloor($floorKicker), ENT_QUOTES, 'UTF-8') ?>" data-room-features="<?= htmlspecialchars($roomFeatureData, ENT_QUOTES, 'UTF-8') ?>">

          <div class="room-card-media">
            <?php
            $photoCarousel = [
                'label' => $room['name'] . ' photos',
                'variant' => 'room',
                'slides' => $roomPhotoSlides,
            ];
            require __DIR__ . '/include/ui/photo-carousel.php';
            unset($photoCarousel);
            ?>
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

            <div class="room-card-compare-actions" aria-label="Room comparison">
              <button class="room-compare-toggle" type="button" data-room-compare-toggle hidden aria-pressed="false">Add to compare</button>
              <button class="room-compare-open" type="button" data-room-comparison-open hidden>Review comparison</button>
            </div>

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
        <?php unset($roomPhotoSlides); ?>
        <?php endforeach; ?>

      </div>
    </div>
  </section>
  <?php endforeach; ?>
  </section><!-- /.rooms-directory -->

  <dialog class="room-comparison-dialog" data-room-comparison-dialog aria-labelledby="room-comparison-title">
    <div class="room-comparison-dialog-inner">
      <header class="room-comparison-header">
        <div>
          <p class="eyebrow">Your shortlist</p>
          <h2 id="room-comparison-title">Compare rooms side by side.</h2>
        </div>
        <button class="room-comparison-close" type="button" data-room-comparison-close aria-label="Close room comparison">Close</button>
      </header>
      <p class="room-comparison-status" data-room-comparison-status role="status" aria-live="polite">Choose two or three rooms from the directory to compare.</p>
      <div class="room-comparison-tools">
        <label class="room-comparison-difference-control" data-room-comparison-difference-control hidden>
          <input type="checkbox" data-room-comparison-differences aria-controls="room-comparison-content">
          <span>Show differences only</span>
        </label>
        <p class="room-comparison-filter-status" data-room-comparison-filter-status role="status" aria-live="polite" hidden></p>
      </div>
      <p class="room-comparison-scroll-hint" id="room-comparison-scroll-hint">On narrow screens, scroll the table horizontally to compare each room column.</p>
      <div id="room-comparison-content" class="room-comparison-content" data-room-comparison-content tabindex="0" role="region" aria-label="Selected room comparison table" aria-describedby="room-comparison-scroll-hint"></div>
      <footer class="room-comparison-footer">
        <a class="room-comparison-enquiry" data-room-comparison-enquiry href="contact.php" hidden>Ask about selected rooms</a>
        <button class="room-comparison-trip" type="button" data-room-comparison-trip hidden>Add room options to Trip</button>
        <button class="room-comparison-clear" type="button" data-room-comparison-clear>Clear shortlist</button>
        <button class="room-comparison-close-link" type="button" data-room-comparison-close>Return to rooms</button>
      </footer>
    </div>
  </dialog>


  <!-- =====================================================
       STAY INFORMATION
       ===================================================== -->

  <section class="section rooms-info" data-story-reveal>
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



  <section class="section site-faq room-faq" data-story-reveal aria-labelledby="room-faq-title">
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
