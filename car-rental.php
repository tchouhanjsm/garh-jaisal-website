<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Car for Arrival or a Desert Day | Garh Jaisal Haveli</title>
  <meta
    name="description"
    content="Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price."
  >
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/car-rental.php">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Car for Arrival or a Desert Day | Garh Jaisal Haveli">
  <meta property="og:description" content="Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.">
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/car-rental.php">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/desert10.jpg">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Car for Arrival or a Desert Day | Garh Jaisal Haveli">
  <meta name="twitter:description" content="Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.">
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/desert10.jpg">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/packages.css">
  <link rel="stylesheet" href="css/tour.css">

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
    "@context":"https://schema.org",
    "@graph":[
      {
        "@type":"WebPage",
        "@id":"https://www.hotelgarhjaisal.com/car-rental.php#webpage",
        "url":"https://www.hotelgarhjaisal.com/car-rental.php",
        "name":"Car for Arrival or a Desert Day | Garh Jaisal Haveli",
        "isPartOf":{"@id":"https://www.hotelgarhjaisal.com/#website"},
        "about":{"@id":"https://www.hotelgarhjaisal.com/#hotel"},
        "breadcrumb":{"@id":"https://www.hotelgarhjaisal.com/car-rental.php#breadcrumb"}
      },
      {
        "@type":"BreadcrumbList",
        "@id":"https://www.hotelgarhjaisal.com/car-rental.php#breadcrumb",
        "itemListElement":[
          {
            "@type":"ListItem",
            "position":1,
            "name":"Home",
            "item":"https://www.hotelgarhjaisal.com/"
          },
          {
            "@type":"ListItem",
            "position":2,
            "name":"Car for arrival or a desert day",
            "item":"https://www.hotelgarhjaisal.com/car-rental.php"
          }
        ]
      }
    ]
  }
  </script>
</head>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" tabindex="-1">
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="car-tours.php">← Car &amp; City Tours</a></div></nav>
  <section class="page-hero">
    <div class="site-shell page-hero-inner">
      <h1>A car for arrival or a desert day</h1>
      <p>
        Garh Jaisal Haveli can arrange a car for your arrival or for a desert
        day. Confirm the car with the hotel.
      </p>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <a class="button button-primary" href="contact.php">Enquire about this experience</a>
      <div class="package-note">
        <p>Ask the hotel for the <a href="contact.php">current price</a>.</p>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
