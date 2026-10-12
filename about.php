<?php
$activePage = 'about';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>About Garh Jaisal Haveli | A Stay in Jaisalmer Fort</title>
  <meta
    name="description"
    content="Read the story of Garh Jaisal Haveli, a heritage stay inside Jaisalmer Fort, with preserved jharokhas, rooftop meals and help planning desert trips."
  >
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/about.php">

  <meta property="og:type" content="website">
  <meta property="og:title" content="About Garh Jaisal Haveli | A Stay in Jaisalmer Fort">
  <meta
    property="og:description"
    content="Garh Jaisal Haveli is a heritage stay inside Jaisalmer Fort, with antique character and everyday comfort."
  >
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/about.php">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/courtyard.jpg">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="About Garh Jaisal Haveli | A Stay in Jaisalmer Fort">
  <meta
    name="twitter:description"
    content="A heritage stay inside Jaisalmer Fort, with preserved rooms, rooftop dining and local hospitality."
  >
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/courtyard.jpg">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/about.css">

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
        "@type": "WebPage",
        "@id": "https://www.hotelgarhjaisal.com/about.php#webpage",
        "url": "https://www.hotelgarhjaisal.com/about.php",
        "name": "About Garh Jaisal Haveli | A Stay in Jaisalmer Fort",
        "description": "Read the story of Garh Jaisal Haveli, a heritage stay inside Jaisalmer Fort, with preserved jharokhas, rooftop meals and help planning desert trips.",
        "isPartOf": { "@id": "https://www.hotelgarhjaisal.com/#website" },
        "about": { "@id": "https://www.hotelgarhjaisal.com/#hotel" },
        "primaryImageOfPage": {
          "@type": "ImageObject",
          "url": "https://www.hotelgarhjaisal.com/img/courtyard.jpg"
        },
        "breadcrumb": { "@id": "https://www.hotelgarhjaisal.com/about.php#breadcrumb" }
      },
      {
        "@type": "BreadcrumbList",
        "@id": "https://www.hotelgarhjaisal.com/about.php#breadcrumb",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://www.hotelgarhjaisal.com/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "About",
            "item": "https://www.hotelgarhjaisal.com/about.php"
          }
        ]
      }
    ]
  }
  </script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="about-page" id="main-content" tabindex="-1">
  <section class="page-hero about-hero">
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">About</p>
      <h1>A heritage stay inside Jaisalmer Fort.</h1>
      <p>
        Garh Jaisal Haveli is a heritage stay inside Jaisalmer Fort. The house keeps the character of an antique structure and the comfort of a place that feels like home.
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
        <h2>Antique character, with room to be comfortable.</h2>
        <p>
          The property sits within the ancient fort, so a stay here is a way to feel the warmth of Rajasthan from inside its walls. Guests come for an authentic stay that sits between antique detail and everyday comfort.
        </p>
        <p>
          Thoughtfully preserved buildings, sculpted jharokhas and hand-painted walls form the background. En-suite bathrooms, Wi-Fi and housekeeping keep the stay relaxed.
        </p>
        <p>
          Rooms are furnished in a Rajasthani style, with modern facilities in each room. Couples and families with children can settle in for a comfortable stay, including a quiet or romantic weekend.
        </p>
      </div>
      <div class="image-panel">
        <img
          src="img/v2/courtyard.jpg"
          alt="Courtyard balconies inside Garh Jaisal Haveli"
          width="900"
          height="1200"
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
          <h2>What the house looks after.</h2>
        </div>
        <p>Cleanliness and comfort come first. The team follows a clear standard so the stay stays straightforward.</p>
      </div>
      <div class="about-points">
        <article class="about-point">
          <h3>Preserved stonework</h3>
          <p>Carved jharokhas, hand-painted walls and buildings kept as an antique structure inside the fort.</p>
        </article>
        <article class="about-point">
          <h3>Comfort in the room</h3>
          <p>En-suite bathrooms, Wi-Fi, housekeeping and modern facilities sit alongside the Rajasthani furnishings.</p>
        </article>
        <article class="about-point">
          <h3>A stay that can suit a careful budget</h3>
          <p>The house aims for a balance of affordability, cleanliness and comfort, with seasonal packages and special offers.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section section-feature">
    <div class="site-shell wide-feature">
      <div class="copy">
        <p class="eyebrow eyebrow-light">Evenings</p>
        <h2>Rooftop meals under a starlit sky.</h2>
        <p>
          Guests can eat on the rooftop and watch the sky over the Golden City. Common spaces are there when you want company, and a small on-site gallery shows local art.
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
          width="1200"
          height="800"
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
          width="900"
          height="1200"
          loading="lazy"
          decoding="async"
        >
      </div>
      <div class="intro-copy">
        <p class="eyebrow">Hospitality</p>
        <h2>A team that helps you plan the days.</h2>
        <p>
          Staff welcome you with local hospitality. They can help shape an itinerary and arrange desert safaris, camel riding, guided heritage walks and transfers.
        </p>
        <p>
          The team is multilingual. Ask for directions, local tips or a special request, and someone can help so the stay stays personal.
        </p>
        <p>
          In the centre of the Golden City, the idea is simple: Rajasthani hospitality, stone carving, views and stories close by, in a stay that feels like home.
        </p>
        <div class="actions">
          <a class="button button-primary" href="pack.php">Desert experiences</a>
          <a class="button about-button-ink" href="direction.php">How to arrive</a>
        </div>
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

  <section class="section about-cta">
    <div class="site-shell">
      <p class="eyebrow">Stay</p>
      <h2>Come for the fort, stay for the house.</h2>
      <p>Check availability, or send a note if you want help with an itinerary before you arrive.</p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
        <a class="button button-light" href="contact.php">Contact the hotel</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
