<?php
$activePage = 'about';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/about.php';
$page = [
    'title' => 'About Garh Jaisal Haveli | A Stay in Jaisalmer Fort',
    'description' => 'Discover Garh Jaisal Haveli, a heritage-style stay inside Jaisalmer Fort, with characterful rooms, rooftop dining and help planning local experiences.',
    'path' => '/about.php',
    'og_title' => 'About Garh Jaisal Haveli | A Stay in Jaisalmer Fort',
    'og_description' => 'Discover Garh Jaisal Haveli, a heritage-style stay inside Jaisalmer Fort, with characterful rooms, rooftop dining and help planning local experiences.',
    'og_image' => '/img/courtyard.jpg',
    'og_image_alt' => 'Courtyard balconies inside Garh Jaisal Haveli',
    'twitter_title' => 'About Garh Jaisal Haveli | A Stay in Jaisalmer Fort',
    'twitter_description' => 'Discover Garh Jaisal Haveli, a heritage-style stay inside Jaisalmer Fort, with characterful rooms, rooftop dining and help planning local experiences.',
    'twitter_image' => '/img/courtyard.jpg',
    'css' => ['css/about.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'About Garh Jaisal Haveli | A Stay in Jaisalmer Fort',
            'description' => 'Discover Garh Jaisal Haveli, a heritage-style stay inside Jaisalmer Fort, with characterful rooms, rooftop dining and help planning local experiences.',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => $schemaSiteUrl . '/img/courtyard.jpg',
            ],
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
                    'name' => 'About',
                    'item' => $schemaPageUrl,
                ],
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="about-page" id="main-content" tabindex="-1">
  <section class="page-hero about-hero">
    <div class="site-shell page-hero-inner">
      <?php
      $breadcrumbs = [
          ['label' => 'Home', 'href' => '/'],
          ['label' => 'About'],
      ];
      require __DIR__ . '/include/ui/breadcrumb.php';
      ?>
      <p class="eyebrow">About</p>
      <h1>Stay inside the Fort. Feel closer to Jaisalmer.</h1>
      <p>
        Garh Jaisal Haveli places you inside Jaisalmer Fort, among the sandstone lanes and the everyday life of this living landmark. Come for the character of the house; stay close to the city, the rooftop and the routes that lead into the Thar Desert.
      </p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
        <a class="button button-light" href="room.php">See the rooms</a>
      </div>
    </div>
  </section>

  <section class="section intro about-story">
    <div class="site-shell intro-grid">
      <div class="intro-copy">
        <p class="eyebrow">The house</p>
        <h2>A place to experience Jaisalmer from within.</h2>
        <p>
          Some places are simply a base for a trip. Here, the Fort is part of the address. Step out into its lanes, notice the sandstone details and local rhythm, then come back to a room within the walls.
        </p>
        <p>
          Carved jharokhas, traditional furnishings and hand-painted details give the house its character. Practical comforts, including en-suite bathrooms, Wi-Fi and housekeeping, help make the stay straightforward.
        </p>
        <p>
          Each room has its own look, so take time to compare the photos and listed features before choosing. If a particular facility matters to you, ask us to confirm that it is available in the room you book.
        </p>
      </div>
      <div class="image-panel">
        <img
          src="img/v2/courtyard.jpg"
          alt="Courtyard balconies inside Garh Jaisal Haveli"
          <?= gj_image_dimension_attributes('img/v2/courtyard.jpg', '(max-width: 760px) 100vw, 50vw') ?>
          loading="lazy"
          decoding="async"
        >
      </div>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">The stay</p>
          <h2>Character is only part of a good stay.</h2>
        </div>
        <p>The setting should feel special, and the practical details should still be clear before you arrive.</p>
      </div>
      <div class="about-points">
        <article class="about-point">
          <h3>A living Fort address</h3>
          <p>Stay among sandstone architecture and the daily life of Jaisalmer Fort, rather than outside its walls.</p>
        </article>
        <article class="about-point">
          <h3>Comfort with character</h3>
          <p>Traditional furnishings sit alongside practical comforts. Room facilities vary, so check the details for your selected room.</p>
        </article>
        <article class="about-point">
          <h3>Help planning the days</h3>
          <p>Ask about a Fort walk, guided city route, rooftop dining or a desert experience. The team can confirm current options for your dates.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section section-feature">
    <div class="site-shell wide-feature">
      <div class="copy">
        <p class="eyebrow eyebrow-light">Evenings</p>
        <h2>End the day above the sandstone rooftops.</h2>
        <p>
          The rooftop gives you a different view of the Golden City. Ask about dining and candlelight options for your date, and explore the property's photos before you plan your evening.
        </p>
        <div class="actions">
          <a class="button button-primary" href="roof.php">Rooftop dining</a>
          <a class="button button-light" href="gallery.php">Photo gallery</a>
        </div>
      </div>
      <div class="media">
        <img
          src="img/big6.jpg"
          alt="Rooftop terrace at Garh Jaisal Haveli overlooking Jaisalmer Fort"
          <?= gj_image_dimension_attributes('img/big6.jpg', '(max-width: 760px) 100vw, 50vw') ?>
          loading="lazy"
          decoding="async"
        >
      </div>
    </div>
  </section>

  <section class="section intro">
    <div class="site-shell intro-grid">
      <div class="image-panel">
        <img
          src="img/s9.jpg"
          alt="Traditional interior details at Garh Jaisal Haveli"
          <?= gj_image_dimension_attributes('img/s9.jpg', '(max-width: 1080px) 100vw, 50vw') ?>
          loading="lazy"
          decoding="async"
        >
      </div>
      <div class="intro-copy">
        <p class="eyebrow">Hospitality</p>
        <h2>Good hospitality begins before you arrive.</h2>
        <p>
          A stay inside the Fort takes a little more planning than a roadside hotel. We make the arrival details clear and help you ask about currently available desert safaris, camel experiences and guided heritage walks.
        </p>
        <p>
          Tell us what matters to you before you arrive: your travel dates, how much time you have and the kind of experience you are looking for. We will confirm guide language, timings and arrangements rather than ask you to rely on assumptions.
        </p>
        <p>
          Our idea of a good welcome is simple: a characterful place to stay, clear practical information and a helpful starting point for your time in Jaisalmer.
        </p>
        <div class="actions">
          <a class="button button-primary" href="pack.php">Desert experiences</a>
          <a class="button about-button-ink" href="direction.php">How to arrive</a>
        </div>
      </div>
    </div>
  </section>

<section class="section conversion-promise" aria-labelledby="conversion-promise-title">
  <div class="site-shell">
    <div class="section-head">
      <div><p class="eyebrow">Our hosting approach</p><h2 id="conversion-promise-title">Special setting. Clear details. A more considered trip.</h2></div>
      <p>We do not want you to discover important practical details only after you arrive. Here is what to check with us before you confirm your plans.</p>
    </div>
    <div class="conversion-promise__grid">
      <article><span>01</span><h3>Know the arrival</h3><p>Vehicles cannot enter the Fort. Read the route and confirm any arrival assistance before travelling.</p><a href="direction.php">Read the arrival guide ↗</a></article>
      <article><span>02</span><h3>Choose the right room</h3><p>Compare room photos and listed features. Ask us to verify any facility that is important to your stay.</p><a href="room.php">Compare rooms ↗</a></article>
      <article><span>03</span><h3>Confirm the experience</h3><p>Routes, schedules, current rates and inclusions depend on the selected experience and date.</p><a href="pack.php">Explore local experiences ↗</a></article>
    </div>
  </div>
</section>
  <section class="section about-explore">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Explore</p>
          <h2>Plan the rest of the stay.</h2>
        </div>
        <p>Rooms, dining, desert trips, arrival and a way to write to the hotel.</p>
      </div>
      <nav class="about-links" aria-label="More about the hotel">
        <a href="room.php">Rooms</a>
        <a href="roof.php">Rooftop dining</a>
        <a href="pack.php">Desert experiences</a>
        <a href="direction.php">Getting here</a>
        <a href="gallery.php">Gallery</a>
        <a href="contact.php">Contact</a>
      </nav>
    </div>
  </section>

  <?php
$ctaBand = [
    'section_class' => 'section about-cta',
    'inner_class' => 'site-shell',
    'eyebrow' => 'Stay',
    'title' => 'Come for the fort, stay for the house.',
    'text' => 'Check your dates or contact the hotel to confirm room details, arrival arrangements and local experiences available during your stay.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'Book your stay',
        'href' => $bookingUrl,
        'class' => 'button button-primary',
        'target' => '_blank',
        'rel' => 'noopener',
    ],
    'secondary' => [
        'label' => 'Contact the hotel',
        'href' => 'contact.php',
        'class' => 'button button-light',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>

<?php
$planningPath = [
    'title' => 'Explore your stay',
    'intro' => 'Move from the story of the haveli to the practical details for rooms, arrival and experiences.',
    'links' => [
        ['label' => 'Explore the rooms', 'href' => 'room.php', 'description' => 'Compare room features before choosing your dates.'],
        ['label' => 'Plan your arrival', 'href' => 'direction.php', 'description' => 'Review directions and the final walk inside the Fort.'],
        ['label' => 'Dining and experiences', 'href' => 'roof.php', 'description' => 'Discover rooftop dining and ask about current options.'],
    ],
];
require __DIR__ . '/include/ui/planning-path.php';
?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
