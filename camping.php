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

  <title>Desert Camping near Jaisalmer | Garh Jaisal Haveli</title>
  <meta
    name="description"
    content="Camping at Garh Jaisal Haveli means Sleep Under the Stars or an overnight stay in a Swiss tent in the desert. Ask the hotel for the current price."
  >
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/camping.php">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Desert Camping near Jaisalmer | Garh Jaisal Haveli">
  <meta property="og:description" content="Camping at Garh Jaisal Haveli means Sleep Under the Stars or an overnight stay in a Swiss tent in the desert. Ask the hotel for the current price.">
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/camping.php">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/v2/swiss-tent.webp">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Desert Camping near Jaisalmer | Garh Jaisal Haveli">
  <meta name="twitter:description" content="Camping at Garh Jaisal Haveli means Sleep Under the Stars or an overnight stay in a Swiss tent in the desert. Ask the hotel for the current price.">
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/v2/swiss-tent.webp">

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
        "@id":"https://www.hotelgarhjaisal.com/camping.php#webpage",
        "url":"https://www.hotelgarhjaisal.com/camping.php",
        "name":"Desert Camping near Jaisalmer | Garh Jaisal Haveli",
        "isPartOf":{"@id":"https://www.hotelgarhjaisal.com/#website"},
        "about":{"@id":"https://www.hotelgarhjaisal.com/#hotel"},
        "breadcrumb":{"@id":"https://www.hotelgarhjaisal.com/camping.php#breadcrumb"}
      },
      {
        "@type":"BreadcrumbList",
        "@id":"https://www.hotelgarhjaisal.com/camping.php#breadcrumb",
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
            "name":"Camping",
            "item":"https://www.hotelgarhjaisal.com/camping.php"
          }
        ]
      }
    ]
  }
  </script>
</head>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main>
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="desert-safari.php">← Desert Tours</a></div></nav>
  <section class="page-hero">
    <div class="site-shell page-hero-inner">
      <h1>Camping</h1>
      <p>
        Camping at this hotel means Sleep Under the Stars and the overnight
        safari in a Swiss tent.
      </p>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <p><a class="button button-primary" href="sleep-under-the-stars.php">Sleep Under the Stars</a></p>
      <p><a class="button button-outline" href="swiss-tent-safari.php">Overnight Safari in a Swiss Tent</a></p>
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
