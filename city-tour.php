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

  <title>A Walk through Jaisalmer Fort | Garh Jaisal Haveli</title>
  <meta
    name="description"
    content="A walk inside Jaisalmer Fort starts from Garh Jaisal Haveli and continues through the lanes of the fort. Ask the hotel for the current price."
  >
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/city-tour.php">

  <meta property="og:type" content="website">
  <meta property="og:title" content="A Walk through Jaisalmer Fort | Garh Jaisal Haveli">
  <meta property="og:description" content="A walk inside Jaisalmer Fort starts from Garh Jaisal Haveli and continues through the lanes of the fort. Ask the hotel for the current price.">
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/city-tour.php">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/1.webp">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="A Walk through Jaisalmer Fort | Garh Jaisal Haveli">
  <meta name="twitter:description" content="A walk inside Jaisalmer Fort starts from Garh Jaisal Haveli and continues through the lanes of the fort. Ask the hotel for the current price.">
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/1.webp">

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
        "@id":"https://www.hotelgarhjaisal.com/city-tour.php#webpage",
        "url":"https://www.hotelgarhjaisal.com/city-tour.php",
        "name":"A Walk through Jaisalmer Fort | Garh Jaisal Haveli",
        "isPartOf":{"@id":"https://www.hotelgarhjaisal.com/#website"},
        "about":{"@id":"https://www.hotelgarhjaisal.com/#hotel"},
        "breadcrumb":{"@id":"https://www.hotelgarhjaisal.com/city-tour.php#breadcrumb"}
      },
      {
        "@type":"BreadcrumbList",
        "@id":"https://www.hotelgarhjaisal.com/city-tour.php#breadcrumb",
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
            "name":"A walk inside Jaisalmer Fort",
            "item":"https://www.hotelgarhjaisal.com/city-tour.php"
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
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="car-tours.php">← Car &amp; City Tours</a></div></nav>
  <section class="page-hero">
    <div class="site-shell page-hero-inner">
      <h1>A walk inside Jaisalmer Fort</h1>
      <p>
        Take a walk inside Jaisalmer Fort from the haveli, and continue
        through the fort lanes.
      </p>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <p><a class="button button-primary" href="contact.php">Contact the hotel</a></p>
      <p><a class="button button-outline" href="direction.php">Plan your arrival</a></p>
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
