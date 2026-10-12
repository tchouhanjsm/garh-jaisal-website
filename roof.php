<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen</title>

  <meta
    name="description"
    content="Rooftop dining at Garh Jaisal's Kitchen in Jaisalmer: vegetarian, vegan and non-vegetarian meals, candlelight dinners, and views of havelis, palace and lake."
  >

  <meta name="robots" content="index,follow">

  <link
    rel="canonical"
    href="https://www.hotelgarhjaisal.com/roof.php"
  >

  <meta property="og:type" content="website">
  <meta
    property="og:title"
    content="Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen"
  >
  <meta
    property="og:description"
    content="Candlelight dinners and city views at Garh Jaisal's Kitchen, the rooftop restaurant inside Jaisalmer Fort."
  >
  <meta
    property="og:url"
    content="https://www.hotelgarhjaisal.com/roof.php"
  >
  <meta
    property="og:image"
    content="https://www.hotelgarhjaisal.com/img/v2/fort-from-roof.jpg"
  >

  <meta name="twitter:card" content="summary_large_image">
  <meta
    name="twitter:title"
    content="Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen"
  >
  <meta
    name="twitter:description"
    content="Candlelight dinners and city views at Garh Jaisal's Kitchen, the rooftop restaurant inside Jaisalmer Fort."
  >
  <meta
    name="twitter:image"
    content="https://www.hotelgarhjaisal.com/img/v2/fort-from-roof.jpg"
  >

  <link
    rel="icon"
    type="image/png"
    href="img/jaisalmerlogo.png"
  >

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/roof.css">

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
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebPage",
        "@id": "https://www.hotelgarhjaisal.com/roof.php#webpage",
        "url": "https://www.hotelgarhjaisal.com/roof.php",
        "name": "Rooftop Dining at Garh Jaisal's Kitchen | Jaisalmer Fort",
        "isPartOf": {"@id": "https://www.hotelgarhjaisal.com/#website"},
        "about": {"@id": "https://www.hotelgarhjaisal.com/restaurant.php#restaurant"},
        "breadcrumb": {"@id": "https://www.hotelgarhjaisal.com/roof.php#breadcrumb"}
      },
      {
        "@type": "BreadcrumbList",
        "@id": "https://www.hotelgarhjaisal.com/roof.php#breadcrumb",
        "itemListElement": [
          {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://www.hotelgarhjaisal.com/"},
          {"@type": "ListItem", "position": 2, "name": "Rooftop dining", "item": "https://www.hotelgarhjaisal.com/roof.php"}
        ]
      }
    ]
  }
  </script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="roof-page">
  <section class="page-hero page-hero--photo">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/fort-from-roof.jpg" alt="" width="1600" height="1066" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Rooftop dining</p>
      <h1>Rooftop dining at Garh Jaisal's Kitchen.</h1>
      <p>
        Candlelight dinners on the terrace, with views of the havelis,
        the royal palace, the lake, and the sunrise and sunset.
      </p>
      <div class="actions">
        <a class="button button-primary" href="restaurant.php">View the menu</a>
        <a class="button button-light" href="contact.php">Contact the hotel</a>
        <a
          class="button button-light"
          href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
          target="_blank"
          rel="noopener"
        >Book a stay</a>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Garh Jaisal's Kitchen</p>
          <h2>Meals on the rooftop.</h2>
        </div>
        <p>
          The restaurant is at the hotel: Fort Kotari Para, inside Jaisalmer Fort,
          Jaisalmer, Rajasthan 345001.
        </p>
      </div>

      <div class="roof-banner">
        <img
          src="img/v2/fort-from-roof.jpg"
          alt="Rooftop table at dusk overlooking the lights of Jaisalmer"
          width="2000"
          height="1333"
        >
      </div>

      <div class="roof-copy">
        <p>
          Garh Jaisal's Kitchen serves meals on the rooftop. The setting is
          meant for a candlelight dinner, with the city spread out around you:
          havelis, the royal palace, the lake, and the sunrise and sunset.
        </p>
        <p>
          The menu includes vegetarian, vegan, and non-vegetarian dishes.
          Dining hours and menu prices are not listed on this page. Confirm
          them with the hotel before you visit.
        </p>
        <p>
          <a class="text-link" href="contact.php">Contact the hotel</a>
          or call <a href="tel:+917727047567">+91 77270 47567</a>.
          For how to reach the fort, see <a href="direction.php">directions</a>.
        </p>
      </div>

      <div class="cards">
        <article class="card">
          <div class="card-image">
            <img
              src="img/roof.webp"
              alt="Rooftop terrace at sunset with a candlelit table facing Jaisalmer Fort"
              width="900"
              height="600"
              loading="lazy"
            >
          </div>
          <div class="card-body">
            <p class="card-kicker">The terrace</p>
            <h3>Candlelight on the roof</h3>
            <p>A table on the terrace, with the fort lit behind it at sunset.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-image">
            <img
              src="img/r2.webp"
              alt="Candlelit rooftop table and cushioned seating looking over Jaisalmer at dusk"
              width="900"
              height="600"
              loading="lazy"
            >
          </div>
          <div class="card-body">
            <p class="card-kicker">City views</p>
            <h3>Havelis, palace and lake</h3>
            <p>The rooftop looks across the havelis, the royal palace and the lake.</p>
          </div>
        </article>

        <article class="card">
          <div class="card-image">
            <img
              src="img/r3.webp"
              alt="Evening dining tables on the rooftop terrace with the sandstone fort behind them"
              width="900"
              height="600"
              loading="lazy"
            >
          </div>
          <div class="card-body">
            <p class="card-kicker">Sunrise and sunset</p>
            <h3>The day from the roof</h3>
            <p>Stay for the sunrise or sunset while you eat above the fort.</p>
          </div>
        </article>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
