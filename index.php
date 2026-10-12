<?php
$activePage = 'home';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Garh Jaisal Haveli | Stay Inside Jaisalmer Fort</title>
  <meta name="description" content="Stay inside Jaisalmer Fort at Garh Jaisal Haveli. Heritage rooms, rooftop views, dining and desert experiences in the heart of the living fort.">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Garh Jaisal Haveli | Stay Inside Jaisalmer Fort">
  <meta property="og:description" content="Heritage rooms, rooftop views and desert experiences inside Jaisalmer Fort.">
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/v2/facade.jpg">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Garh Jaisal Haveli | Stay Inside Jaisalmer Fort">
  <meta name="twitter:description" content="Heritage rooms, rooftop views and desert experiences inside Jaisalmer Fort.">
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/v2/facade.jpg">
  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-0C646S1FQ0"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-0C646S1FQ0');
    gtag('config', 'G-6S5PCEVXH4');
  </script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Hotel",
      "@id": "https://www.hotelgarhjaisal.com/#hotel",
      "name": "Hotel Garh Jaisal Haveli",
      "url": "https://www.hotelgarhjaisal.com/",
      "telephone": "+91-7727047567",
      "image": [
        "https://www.hotelgarhjaisal.com/img/v2/facade.jpg",
        "https://www.hotelgarhjaisal.com/img/5.webp"
      ],
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Fort Kotari Para, Inside Jaisalmer Fort",
        "addressLocality": "Jaisalmer",
        "addressRegion": "Rajasthan",
        "postalCode": "345001",
        "addressCountry": "IN"
      }
    },
    {
      "@type": "WebSite",
      "@id": "https://www.hotelgarhjaisal.com/#website",
      "url": "https://www.hotelgarhjaisal.com/",
      "name": "Garh Jaisal Haveli",
      "publisher": {
        "@id": "https://www.hotelgarhjaisal.com/#hotel"
      }
    },
    {
      "@type": "WebPage",
      "@id": "https://www.hotelgarhjaisal.com/#webpage",
      "url": "https://www.hotelgarhjaisal.com/",
      "name": "Garh Jaisal Haveli | Stay Inside Jaisalmer Fort",
      "isPartOf": {"@id": "https://www.hotelgarhjaisal.com/#website"},
      "about": {"@id": "https://www.hotelgarhjaisal.com/#hotel"}
    }
  ]
}
</script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" tabindex="-1">
<section class="hero">
  <div class="hero-media">
    <img
      src="img/v2/facade.jpg"
      width="1333"
      height="2000"
      alt="Carved sandstone facade of Garh Jaisal Haveli inside Jaisalmer Fort"
      fetchpriority="high">
    <div class="hero-overlay"></div>
  </div>

  <div class="site-shell hero-content">
    <p class="eyebrow">Garh Jaisal Haveli</p>

    <h1>Stay inside Jaisalmer Fort.</h1>

    <p>
      Stay at Garh Jaisal Haveli, inside Jaisalmer Fort, with heritage rooms,
      rooftop dining and desert experiences arranged from the hotel.
    </p>

    <div class="actions">
      <a
        class="button button-primary"
        href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
        target="_blank"
        rel="noopener">
        Book your stay
      </a>

      <a class="button button-light" href="direction.php">
        Plan your arrival
      </a>
    </div>
  </div>
</section>

<section class="home-discovery" aria-label="Explore Garh Jaisal Haveli">
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

<section class="home-arrival-note" aria-labelledby="home-arrival-title">
  <div class="site-shell home-arrival-note-inner">
    <div class="home-arrival-note-copy">
      <p class="eyebrow">Before you arrive</p>
      <h2 id="home-arrival-title">Arriving inside the fort</h2>
      <p><strong>Vehicles cannot enter Jaisalmer Fort.</strong> The final stretch to Garh Jaisal Haveli is on foot through the fort lanes. Reception is open 7:00 AM–10:30 PM; please contact us in advance for late arrivals.</p>
    </div>
    <a class="button button-primary" href="direction.php">Read the arrival guide</a>
  </div>
</section>

<section class="section intro">
  <div class="site-shell intro-grid">

    <div class="intro-copy">

      <p class="eyebrow">The place</p>

      <h2>A heritage haveli inside the fort.</h2>

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
              Enjoy breakfast, dinner and views across the Golden City.
            </p>
          </div>
        </article>

        <article class="feature">
          <span class="feature-num">03</span>
          <div>
            <h3>Help with arrival</h3>
            <p>
              Our team can meet you near Fort Parking and assist with luggage.
            </p>
          </div>
        </article>

      </div>

    </div>

    <div class="image-panel">
      <img
        src="img/5.webp"
        alt="Carved sandstone architecture inside Garh Jaisal Haveli"
        loading="lazy">
    </div>

  </div>
</section>

  <section class="section home-rooms" id="rooms-preview" aria-labelledby="rooms-preview-title">
    <?php
    $rooms = require __DIR__ . '/include/rooms-data.php';
    $featuredRooms = array_slice($rooms, 0, 3);
    ?>
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Stay</p><h2 id="rooms-preview-title">Rooms shaped by Jaisalmer.</h2></div>
        <p>From colourful rooms to carved interiors, each room has its own character. Explore the current room collection and choose the space that suits your stay.</p>
      </div>
      <div class="cards home-room-cards">
        <?php foreach ($featuredRooms as $room): ?>
          <?php $roomAnchor = 'room-' . trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($room['name'])), '-'); ?>
          <?php $roomDetailsUrl = 'room.php#' . $roomAnchor; ?>
          <article class="card home-room-card">
            <div class="card-image">
              <a class="home-room-card-image-link" href="<?= htmlspecialchars($roomDetailsUrl, ENT_QUOTES, 'UTF-8') ?>">
                <img src="<?= htmlspecialchars($room['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($room['alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
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

<section class="section section-feature home-rooftop" id="rooftop-dining" aria-labelledby="rooftop-dining-title">
  <div class="site-shell wide-feature">

    <div class="copy">
      <p class="eyebrow eyebrow-light">Rooftop dining</p>

      <h2 id="rooftop-dining-title">Meals with the fort around you.</h2>

      <p>
        Come up to the rooftop for breakfast, dinner or a quiet drink while
        the sandstone walls and rooftops of Jaisalmer stretch around you.
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

<section class="section home-experiences" id="desert-experiences" aria-labelledby="desert-experiences-title">
  <div class="site-shell">

    <div class="section-head">
      <div>
        <p class="eyebrow">Desert experiences</p>
        <h2 id="desert-experiences-title">From the fort to the Thar.</h2>
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
            src="img/desert7.jpg"
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
            src="img/v2/swiss-tent.webp"
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
            src="img/tent.jpg"
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

<section class="section arrival" id="arrival-guide" aria-labelledby="arrival-guide-title">
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
        <strong>Meet our team</strong>
        <span>We can meet you and assist with your luggage.</span>
      </div>

      <div class="step">
        <span>04</span>
        <strong>Walk to Garh Jaisal</strong>
        <span>A short walk through the living fort.</span>
      </div>

    </div>

    <div class="callout">
      <p>
        <strong>Reception:</strong> 7:00 AM – 10:30 PM.
        Please contact us in advance for late arrivals.
      </p>

      <a class="button" href="direction.php">
        Full arrival guide
      </a>
    </div>

  </div>
</section>
<section class="section home-gallery" id="property-gallery" aria-labelledby="property-gallery-title">
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
          src="img/jcity.jpg"
          alt="Jaisalmer city seen from the fort"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/3.webp"
          alt="Sandstone architecture at Garh Jaisal Haveli"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/big2.jpg"
          alt="Rooftop view at Garh Jaisal Haveli"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/s4.jpg"
          alt="View across Jaisalmer from the rooftop"
          loading="lazy">
      </figure>

      <figure>
        <img
          src="img/j10.jpg"
          alt="Jaisalmer sandstone architecture"
          loading="lazy">
      </figure>

    </div>

  </div>
</section>



<section class="section final-cta">
  <div class="site-shell final-cta-inner">

    <p class="eyebrow">Plan your stay</p>

    <h2>Stay at Garh Jaisal Haveli.</h2>

    <p>
      Check availability, ask about arrival, or enquire about our desert experiences.
    </p>

    <div class="actions">
      <a
        class="button button-primary"
        href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
        target="_blank"
        rel="noopener">
        Book your stay
      </a>

      <a class="button final-contact-button" href="contact.php">
        Contact us
      </a>
    </div>

  </div>
</section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
