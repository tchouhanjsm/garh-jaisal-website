<?php
$activePage = 'home';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$rooms = require __DIR__ . '/include/rooms-data.php';
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/';
$buildHeroPreload = static function ($stem, $media, $fallback) {
    $srcset = [];
    foreach ([480, 800, 1200] as $candidateWidth) {
        $candidate = '/img/responsive/v2/' . $stem . '-' . $candidateWidth . '.webp';
        if (is_file(__DIR__ . $candidate) && !is_link(__DIR__ . $candidate)) {
            $srcset[] = $candidate . ' ' . $candidateWidth . 'w';
        }
    }
    $descriptor = [
        'href' => $srcset ? strtok($srcset[0], ' ') : $fallback,
        'media' => $media,
        'fetchpriority' => 'high',
    ];
    if ($srcset) {
        $descriptor['imagesrcset'] = implode(', ', $srcset);
        $descriptor['imagesizes'] = '100vw';
    }
    return $descriptor;
};
$page = [
    'title' => 'Garh Jaisal Haveli | Stay Inside Jaisalmer Fort',
    'description' => 'Stay inside Jaisalmer Fort at Garh Jaisal Haveli. Heritage rooms, rooftop views, dining and desert experiences in the heart of the living fort.',
    'path' => '/',
    'preload' => [
        $buildHeroPreload('facade', '(max-width: 767px)', '/img/optimized/v2/facade.webp'),
        $buildHeroPreload('fort-from-roof', '(min-width: 768px)', '/img/optimized/v2/fort-from-roof.webp'),
    ],
    'og_title' => 'Garh Jaisal Haveli | Stay Inside Jaisalmer Fort',
    'og_description' => 'Heritage rooms, rooftop views and desert experiences inside Jaisalmer Fort.',
    'og_image' => '/img/v2/facade.jpg',
    'twitter_title' => 'Garh Jaisal Haveli | Stay Inside Jaisalmer Fort',
    'twitter_description' => 'Heritage rooms, rooftop views and desert experiences inside Jaisalmer Fort.',
    'twitter_image' => '/img/v2/facade.jpg',
    'css' => ['css/thar-sunset-story.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'Garh Jaisal Haveli | Stay Inside Jaisalmer Fort',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" class="thar-sunset-story thar-sunset-story--home" tabindex="-1">
<section class="hero">
  <div class="hero-media">
    <picture class="hero-picture">
      <source
        media="(min-width: 768px)"
        <?= gj_image_dimension_attributes('img/optimized/v2/fort-from-roof.webp', '100vw') ?>>
      <img
        src="img/optimized/v2/facade.webp"
        <?= gj_image_dimension_attributes('img/optimized/v2/facade.webp', '100vw') ?>
        alt=""
        aria-hidden="true"
        fetchpriority="high"
        decoding="async">
    </picture>
    <div class="hero-overlay"></div>
  </div>

  <div class="site-shell hero-content">
    <p class="eyebrow">Garh Jaisal Haveli</p>

    <h1>Wake up inside Jaisalmer Fort.</h1>

    <p>
      Wake up among the sandstone lanes of the living Fort, then return to a heritage-style room after rooftop dining, a guided city walk or a journey into the Thar.
      rooftop dining and desert experiences arranged from the hotel.
    </p>

    <div class="actions">
      <a
        class="button button-primary"
        href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
        target="_blank"
        rel="noopener">
        Check rooms &amp; rates
      </a>

      <a class="button button-light" href="#start-planning">
        Plan your Jaisalmer stay
      </a>
    </div>
  </div>
</section>

<section class="site-shell at-a-glance-section home-quick-facts" aria-label="Quick facts about Garh Jaisal Haveli">
  <?php
  $atAGlance = [
      'items' => [
          ['label' => 'Location', 'value' => 'Inside Jaisalmer Fort'],
          ['label' => 'Stay', 'value' => count($rooms) . ' heritage rooms'],
          ['label' => 'Dining', 'value' => 'Rooftop restaurant'],
          ['label' => 'Experiences', 'value' => 'Desert and guided city tours'],
      ],
  ];
  require __DIR__ . '/include/ui/at-a-glance.php';
  ?>
</section>

<section class="home-discovery" aria-label="Explore Garh Jaisal Haveli" data-story-reveal>
  <div class="site-shell home-discovery-grid">
    <a class="home-discovery-link" href="room.php">
      <span class="home-discovery-number" aria-hidden="true">01</span>
      <span><strong>Stay inside the Fort</strong><small>Explore heritage rooms</small></span>
      <span class="home-discovery-arrow" aria-hidden="true">↗</span>
    </a>
    <a class="home-discovery-link" href="pack.php">
      <span class="home-discovery-number" aria-hidden="true">02</span>
      <span><strong>Plan an experience</strong><small>Desert and city tours</small></span>
      <span class="home-discovery-arrow" aria-hidden="true">↗</span>
    </a>
    <a class="home-discovery-link" href="roof.php">
      <span class="home-discovery-number" aria-hidden="true">03</span>
      <span><strong>Dine on the rooftop</strong><small>Explore rooftop dining</small></span>
      <span class="home-discovery-arrow" aria-hidden="true">↗</span>
    </a>
  </div>
</section>

<section class="home-arrival-note" data-story-reveal aria-labelledby="home-arrival-title">
  <div class="site-shell home-arrival-note-inner">
    <div class="home-arrival-note-copy">
      <p class="eyebrow">Before you arrive</p>
      <h2 id="home-arrival-title">Arriving inside the fort</h2>
      <p><strong>Vehicles cannot enter Jaisalmer Fort.</strong> The final stretch to Garh Jaisal Haveli is on foot through the fort lanes. Contact the hotel before travelling to confirm current reception hours and any late-arrival arrangements, or <a href="contact.php?topic=arrival">send an arrival enquiry</a>.</p>
    </div>
    <a class="button button-primary" href="direction.php">Read the arrival guide</a>
  </div>
</section>

<section class="section intro" data-story-reveal>
  <div class="site-shell intro-grid">

    <div class="intro-copy">

      <p class="eyebrow">The place</p>

      <h2>Experience Jaisalmer from inside its living Fort.</h2>

      <p>
        Garh Jaisal Haveli is located inside Jaisalmer Fort, surrounded by
        the sandstone architecture and everyday life of the historic fort.
        The stay combines heritage-style rooms, rooftop dining and access
        to Jaisalmer and the desert.
      </p>

      <div class="feature-list">

        <article class="feature">
          <span class="feature-num">01</span>
          <div>
            <h3>Inside Jaisalmer Fort</h3>
            <p>
              Stay within the historic fort rather than outside its walls.
            </p>
          </div>
        </article>

        <article class="feature">
          <span class="feature-num">02</span>
          <div>
            <h3>Rooftop views</h3>
            <p>
              Enjoy rooftop dining and views across the Golden City. Confirm current service times and menu options with the hotel before visiting.
            </p>
          </div>
        </article>

        <article class="feature">
          <span class="feature-num">03</span>
          <div>
            <h3>Help with arrival</h3>
            <p>
              Contact the hotel before arrival to ask whether meet-and-assist or luggage help can be arranged; wait for confirmation before relying on any arrangement.
            </p>
          </div>
        </article>

      </div>

    </div>

    <div class="image-panel">
      <img
        src="img/5.webp" <?= gj_image_dimension_attributes('img/5.webp', '(max-width: 800px) 100vw, 50vw') ?>
        alt="Carved sandstone architecture inside Garh Jaisal Haveli"
        loading="lazy">
    </div>

  </div>
</section>

<section class="section conversion-journey" id="start-planning" aria-labelledby="conversion-journey-title">
  <div class="site-shell">
    <div class="section-head">
      <div><p class="eyebrow">Build your Jaisalmer stay</p><h2 id="conversion-journey-title">Start with the stay. Add the experiences that matter to you.</h2></div>
      <p>Planning Rajasthan? Make Jaisalmer one memorable stop in the wider journey. Start with local options and confirm rates, routes and arrangements for your dates.</p>
    </div>
    <div class="conversion-journey__grid">
      <article class="conversion-journey__card"><span class="conversion-journey__step">01 / STAY</span><h3>Make the Fort your base</h3><p>Compare room photos and details, then check current rates, availability and facilities.</p><a href="room.php">Explore the rooms ↗</a></article>
      <article class="conversion-journey__card"><span class="conversion-journey__step">02 / DISCOVER</span><h3>Meet the city beyond the postcard</h3><p>Ask about a Fort walk or guided route, with guide language and itinerary confirmed in advance.</p><a href="guided-jaisalmer-tour.php">Compare guided tours ↗</a></article>
      <article class="conversion-journey__card"><span class="conversion-journey__step">03 / DESERT</span><h3>Choose your Thar experience</h3><p>Compare sunset and overnight options, then confirm transport, timings and inclusions.</p><a href="desert-safari.php">Explore desert safaris ↗</a></article>
    </div>
    <p class="conversion-note">Have a date or travel style in mind? <a href="contact.php?topic=visit-planning">Tell us what you are planning</a>.</p>
  </div>
</section>
  <section class="section home-rooms" id="rooms-preview" data-story-reveal aria-labelledby="rooms-preview-title">
    <?php
    $featuredRooms = array_slice($rooms, 0, 3);
    ?>
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Stay</p><h2 id="rooms-preview-title">Find your room inside the Fort.</h2></div>
        <p>From colourful rooms to carved interiors, each room has its own character. Explore the current room collection and choose the space that suits your stay.</p>
      </div>
      <div class="cards home-room-cards">
        <?php foreach ($featuredRooms as $room): ?>
          <?php $roomAnchor = 'room-' . trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($room['name'])), '-'); ?>
          <?php $roomDetailsUrl = 'room.php#' . $roomAnchor; ?>
          <article class="card home-room-card">
            <div class="card-image">
              <a class="home-room-card-image-link" href="<?= htmlspecialchars($roomDetailsUrl, ENT_QUOTES, 'UTF-8') ?>">
                <img src="<?= htmlspecialchars($room['image'], ENT_QUOTES, 'UTF-8') ?>" <?= gj_image_dimension_attributes($room['image'], '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="<?= htmlspecialchars($room['alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
              </a>
            </div>
            <div class="card-body">
              <div class="card-kicker"><?= htmlspecialchars($room['kicker'], ENT_QUOTES, 'UTF-8') ?></div>
              <h3><?= htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($room['description'], ENT_QUOTES, 'UTF-8') ?></p>
              <a class="text-link home-room-detail-link" href="<?= htmlspecialchars($roomDetailsUrl, ENT_QUOTES, 'UTF-8') ?>">View room details →</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

<section class="section section-feature home-rooftop" id="rooftop-dining" data-story-reveal aria-labelledby="rooftop-dining-title">
  <div class="site-shell wide-feature">

    <div class="copy">
      <p class="eyebrow eyebrow-light">Rooftop dining</p>

      <h2 id="rooftop-dining-title">Rooftop dining, with the Golden City around you.</h2>

      <p>
        Visit the rooftop for dining and views across Jaisalmer's sandstone
        rooftops. Contact the hotel to confirm current service times and menu
        options before visiting.
      </p>

      <p>
        Candle-light dinners can also be arranged on the rooftop terrace.
      </p>

      <div class="actions">
        <a class="button button-primary" href="roof.php">
          Explore rooftop dining
        </a>
      </div>
    </div>

    <div class="media">
      <img
        src="img/roofslide.webp"
        width="1600"
        height="575"
        alt="Rooftop table at dusk overlooking the lights of Jaisalmer"
        loading="lazy">
    </div>

  </div>
</section>

<section class="section home-experiences" id="desert-experiences" data-story-reveal aria-labelledby="desert-experiences-title">
  <div class="site-shell">

    <div class="section-head">
      <div>
        <p class="eyebrow">Desert experiences</p>
        <h2 id="desert-experiences-title">From the Fort to the open Thar.</h2>
      </div>

      <p>
        Arrange a desert experience from Garh Jaisal, from a sunset camel
        safari to an overnight stay among the dunes.
      </p>
    </div>

    <div class="cards">

      <article class="card">
        <div class="card-image">
          <img
            src="img/desert7.jpg" <?= gj_image_dimension_attributes('img/desert7.jpg', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?>
            alt="Camel safari at sunset in the Thar Desert near Jaisalmer"
            loading="lazy">
        </div>

        <div class="card-body">
          <div class="card-kicker">Sunset experience</div>

          <h3>Camel safari</h3>

          <p>
            Head into the dunes in the evening and watch the desert change
            colour as the sun goes down.
          </p>

          <a class="text-link" href="sunset-camel-safari.php">
            Explore desert trips →
          </a>
        </div>
      </article>


      <article class="card">
        <div class="card-image">
          <img
            src="img/v2/swiss-tent.webp" <?= gj_image_dimension_attributes('img/v2/swiss-tent.webp') ?>
            alt="Swiss tent accommodation in the Jaisalmer desert"
            loading="lazy">
        </div>

        <div class="card-body">
          <div class="card-kicker">Overnight in a tent</div>

          <h3>Sleep in the desert</h3>

          <p>
            Stay overnight in a Swiss tent at the desert camp, with dinner,
            camp atmosphere and the dunes around you.
          </p>

          <a class="text-link" href="swiss-tent-safari.php">
            Explore tent stays →
          </a>
        </div>
      </article>


      <article class="card">
        <div class="card-image">
          <img
            src="img/tent.jpg" <?= gj_image_dimension_attributes('img/tent.jpg', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?>
            alt="People sleeping under a starry desert sky in the Jaisalmer desert"
            loading="lazy">
        </div>

        <div class="card-body">
          <div class="card-kicker">Private desert experience</div>

          <h3>Sleep under the stars</h3>

          <p>
            Leave the camp behind for a private sleeping experience in the
            dunes, with the open desert above you.
          </p>

          <a class="text-link" href="sleep-under-the-stars.php">
            Discover the experience →
          </a>
        </div>
      </article>

    </div>
  </div>
</section>

<section class="section arrival" id="arrival-guide" data-story-reveal aria-labelledby="arrival-guide-title">
  <div class="site-shell">

    <div class="section-head">
      <div>
        <p class="eyebrow">Getting here</p>
        <h2 id="arrival-guide-title">Arriving at the fort is part of the experience.</h2>
      </div>

      <p>
        Garh Jaisal Haveli is inside Jaisalmer Fort. Vehicles cannot enter
        the fort, so your final stretch is on foot through the fort lanes.
      </p>
    </div>

    <div class="steps">

      <div class="step">
        <span>01</span>
        <strong>Reach Fort Parking</strong>
        <span>Your vehicle drop-off point outside the fort.</span>
      </div>

      <div class="step">
        <span>02</span>
        <strong>Call the hotel</strong>
        <span>Let our team know that you have arrived.</span>
      </div>

      <div class="step">
        <span>03</span>
        <strong>Ask about arrival assistance</strong>
        <span>Contact the hotel before travelling to ask whether help can be arranged; wait for confirmation before relying on it.</span>
      </div>

      <div class="step">
        <span>04</span>
        <strong>Walk to Garh Jaisal</strong>
        <span>A short walk through the living fort.</span>
      </div>

    </div>

    <div class="callout">
      <p>
        <strong>Before travelling:</strong> Confirm current reception hours and ask whether late-arrival arrangements can be made, or <a href="contact.php?topic=arrival">send an arrival enquiry</a>.
      </p>

      <a class="button" href="direction.php">
        Full arrival guide
      </a>
    </div>

  </div>
</section>
<section class="section home-gallery" id="property-gallery" data-story-reveal aria-labelledby="property-gallery-title">
  <div class="site-shell">

    <div class="section-head">
      <div>
        <p class="eyebrow">The place</p>
        <h2 id="property-gallery-title">A glimpse of Garh Jaisal and its surroundings.</h2>
      </div>

      <a class="text-link" href="gallery.php">
        View full gallery →
      </a>
    </div>

    <div class="gallery-strip">

      <figure>
        <img
          src="img/jcity.jpg" <?= gj_image_dimension_attributes('img/jcity.jpg', '(max-width: 760px) 100vw, 50vw') ?>
          alt="Jaisalmer city seen from the fort"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/courtyard.jpg" <?= gj_image_dimension_attributes('img/courtyard.jpg', '(max-width: 760px) 50vw, 28vw') ?>
          alt="Carved sandstone hallway inside a heritage haveli"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/recep1.jpg" <?= gj_image_dimension_attributes('img/recep1.jpg', '(max-width: 760px) 50vw, 28vw') ?>
          alt="Reception area with traditional furnishings at Garh Jaisal Haveli"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/s4.jpg" <?= gj_image_dimension_attributes('img/s4.jpg', '(max-width: 760px) 50vw, 28vw') ?>
          alt="View across Jaisalmer from the rooftop"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/big9.jpg" <?= gj_image_dimension_attributes('img/big9.jpg', '(max-width: 760px) 50vw, 28vw') ?>
          alt="A carved entrance in a historic sandstone building"
          loading="lazy">
      </figure>

    </div>

  </div>
</section>

<section class="section conversion-faq" aria-labelledby="conversion-faq-title">
  <div class="site-shell">
    <div class="section-head"><div><p class="eyebrow">Before you decide</p><h2 id="conversion-faq-title">A few useful things to know.</h2></div><p>We would rather help you arrive prepared than leave you guessing about practical details.</p></div>
    <div class="conversion-faq__list">
      <details><summary>Can a car drive up to the haveli?</summary><p>No. Vehicles cannot enter Jaisalmer Fort, so the final stretch is on foot. Read the <a href="direction.php">arrival guide</a> and contact the hotel if you need to discuss assistance.</p></details>
      <details><summary>How do I choose a room and rate?</summary><p>Room features vary. Review the details and check current rates and availability for your exact dates.</p></details>
      <details><summary>Can I add a guided tour or desert safari?</summary><p>Ask about options for your date. Confirm the route, timing, transport, guide availability, price and inclusions before deciding.</p></details>
      <details><summary>Can I request a rooftop dinner?</summary><p>Send a dining enquiry with your preferred date, time and group size. It is not confirmed until the team confirms current hours and availability.</p></details>
    </div>
    <p class="conversion-faq__contact">Need a detail checked before booking? <a href="contact.php">Ask the hotel directly</a>.</p>
  </div>
</section>
<?php
$ctaBand = [
    'section_class' => 'section final-cta',
    'inner_class' => 'site-shell final-cta-inner',
    'eyebrow' => 'Plan your stay',
    'title' => 'Make Jaisalmer more than a stop on the map.',
    'text' => 'Check your dates, get the arrival details straight, and ask about the city or desert experiences that fit your trip.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'Book your stay',
        'href' => $bookingUrl,
        'class' => 'button button-primary',
        'target' => '_blank',
        'rel' => 'noopener',
    ],
    'secondary' => [
        'label' => 'Contact us',
        'href' => 'contact.php',
        'class' => 'button final-contact-button',
    ],
    'tertiary' => [
        'label' => 'Read our guide to staying inside Jaisalmer Fort →',
        'href' => 'book-rooms-in-jaisalmer.php',
        'class' => 'text-link final-cta-guide-link',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
