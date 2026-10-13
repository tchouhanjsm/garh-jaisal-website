<?php
$activePage = 'gallery';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Gallery | Garh Jaisal Haveli, Jaisalmer Fort</title>
  <meta
    name="description"
    content="Explore rooms, the haveli, desert experiences and dining at Garh Jaisal Haveli inside Jaisalmer Fort."
  >
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/gallery.php">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Gallery | Garh Jaisal Haveli, Jaisalmer Fort">
  <meta
    property="og:description"
    content="See the rooms, haveli, desert experiences and dining at Garh Jaisal Haveli."
  >
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/gallery.php">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/s1.jpg">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Gallery | Garh Jaisal Haveli, Jaisalmer Fort">
  <meta name="twitter:description" content="See Garh Jaisal Haveli, its rooms, desert experiences and dining."
  >
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/s1.jpg">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/gallery.css">

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
        "@id":"https://www.hotelgarhjaisal.com/gallery.php#webpage",
        "url":"https://www.hotelgarhjaisal.com/gallery.php",
        "name":"Gallery | Garh Jaisal Haveli, Jaisalmer Fort",
        "isPartOf":{"@id":"https://www.hotelgarhjaisal.com/#website"},
        "about":{"@id":"https://www.hotelgarhjaisal.com/#hotel"},
        "breadcrumb":{"@id":"https://www.hotelgarhjaisal.com/gallery.php#breadcrumb"}
      },
      {
        "@type":"BreadcrumbList",
        "@id":"https://www.hotelgarhjaisal.com/gallery.php#breadcrumb",
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
            "name":"Gallery",
            "item":"https://www.hotelgarhjaisal.com/gallery.php"
          }
        ]
      }
    ]
  }
  </script>
</head>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="gallery-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo gallery-hero">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/rooftop-dusk.jpg" <?= gj_image_dimension_attributes('img/v2/rooftop-dusk.jpg') ?> alt="" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Gallery</p>
      <h1>See Garh Jaisal Haveli.</h1>
      <p>
        Browse the haveli, rooms, desert experiences and dining spaces before you arrive.
      </p>
      <div class="actions">
        <a class="button button-primary" href="room.php">Explore rooms</a>
        <a class="button button-light" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
      </div>
    </div>
  </section>

  <section class="section gallery-intro">
    <div class="site-shell gallery-intro-inner">
      <div>
        <p class="eyebrow">The collection</p>
        <h2>A look at the place before your stay.</h2>
      </div>
      <p>
        Explore the spaces and experiences that make up a stay at Garh Jaisal Haveli.
        Select an image to open the full-size photograph.
      </p>
    </div>

    <div class="site-shell gallery-jump" aria-label="Gallery sections">
      <a href="#hotel">Hotel</a>
      <a href="#rooms">Rooms</a>
      <a href="#desert">Desert</a>
      <a href="#restaurant">Restaurant</a>
    </div>
  </section>

  <?php
  $sections = [
    'hotel' => [
      'eyebrow' => 'Hotel',
      'title' => 'The haveli',
      'description' => 'Shared spaces, sandstone details and views around Garh Jaisal Haveli.',
      'images' => [
        ['v2/facade.jpg', 'Carved facade of Garh Jaisal Haveli inside Jaisalmer Fort'],
        ['v2/courtyard.jpg', 'Courtyard balconies inside Garh Jaisal Haveli'],
        ['v2/sitting-room.jpg', 'Sitting room with carved wood at Garh Jaisal Haveli'],
        ['v2/fort-from-roof.jpg', 'Jaisalmer Fort seen from the rooftop at dusk'],
        ['recep1.jpg', 'Reception area at Garh Jaisal Haveli'],
        ['s1.jpg', 'Interior of Garh Jaisal Haveli'],
        ['s2.jpg', 'Heritage interior at Garh Jaisal Haveli'],
        ['s3.jpg', 'Sandstone details at Garh Jaisal Haveli'],
        ['s5.jpg', 'Heritage interior at Garh Jaisal Haveli'],
        ['s6.jpg', 'Interior detail at Garh Jaisal Haveli'],
        ['s7.jpg', 'Interior space at Garh Jaisal Haveli']
      ]
    ],
    'rooms' => [
      'eyebrow' => 'Rooms',
      'title' => 'Rooms with character',
      'description' => 'The rooms featured on the current Rooms page, shown from different angles and details.',
      'images' => [
        ['maharaniroom.jpg', 'Red Maharani Room at Garh Jaisal Haveli'],
        ['redmaha1.jpg', 'Red Maharani Room interior'],
        ['opium1.jpg', 'Opium Room at Garh Jaisal Haveli'],
        ['opium2.jpg', 'Opium Room interior'],
        ['opium-room.jpg', 'Opium Room at Garh Jaisal Haveli'],
        ['purple-room.jpg', 'Sweet Purple Room at Garh Jaisal Haveli'],
        ['purple1.jpg', 'Sweet Purple Room interior'],
        ['purple2.jpg', 'Sweet Purple Room interior detail'],
        ['purplet.jpg', 'Sweet Purple Room detail'],
        ['risingroom.jpg', 'Rising Sun Room at Garh Jaisal Haveli'],
        ['sun1.jpg', 'Rising Sun Room interior'],
        ['sun2.jpg', 'Rising Sun Room interior'],
        ['sunt1.jpg', 'Rising Sun Room detail'],
        ['scarlet.jpg', 'Scarlet Room at Garh Jaisal Haveli'],
        ['scar1.jpg', 'Scarlet Room interior'],
        ['scar2.jpg', 'Scarlet Room detail'],
        ['scar-t.jpg', 'Scarlet Room detail'],
        ['green-room.jpg', 'Green Room at Garh Jaisal Haveli'],
        ['green1.jpg', 'Green Room interior'],
        ['green2.jpg', 'Green Room detail'],
        ['greent.jpg', 'Green Room detail'],
        ['rr3.jpg', 'BlueSky Room at Garh Jaisal Haveli'],
        ['rr2.jpg', 'BlueSky Room interior']
      ]
    ],
    'desert' => [
      'eyebrow' => 'Desert',
      'title' => 'Desert experiences',
      'description' => 'Desert landscapes, camel safaris and overnight experiences arranged from Jaisalmer.',
      'images' => [
        ['desert1.jpg', 'Desert landscape near Jaisalmer'],
        ['desert2.jpg', 'Sand dunes near Jaisalmer'],
        ['desert5.jpg', 'Desert experience near Jaisalmer'],
        ['desert3.jpg', 'Desert dunes near Jaisalmer'],
        ['desert4.jpg', 'Desert safari near Jaisalmer'],
        ['desert6.jpg', 'Camel safari in the desert near Jaisalmer'],
        ['desert7.jpg', 'Desert camp experience near Jaisalmer'],
        ['desert8.jpg', 'Desert landscape near Jaisalmer'],
        ['desert10.jpg', 'Desert experience near Jaisalmer']
      ]
    ],
    'restaurant' => [
      'eyebrow' => 'Restaurant',
      'title' => 'Dining',
      'description' => 'Dining spaces and rooftop restaurant views at Garh Jaisal Haveli.',
      'images' => [
        ['res1.jpg', 'Restaurant at Garh Jaisal Haveli'],
        ['rest2.jpg', 'Dining space at Garh Jaisal Haveli'],
        ['rest3.jpg', 'Rooftop dining at Garh Jaisal Haveli'],
        ['rest4.jpg', 'Restaurant view at Garh Jaisal Haveli'],
        ['rest5.jpg', 'Dining at Garh Jaisal Haveli']
      ]
    ]
  ];

  foreach ($sections as $id => $section):
  ?>
    <section class="section gallery-section" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
      <div class="site-shell">
        <div class="section-head gallery-section-head">
          <div>
            <p class="eyebrow"><?= htmlspecialchars($section['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
          <p><?= htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="gallery-grid">
          <?php foreach ($section['images'] as $image): ?>
            <?php
              $src = 'img/' . $image[0];
              $alt = $image[1];
            ?>
            <figure class="gallery-item">
              <a href="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" aria-label="Open full-size image: <?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>">
                <img
                  src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                  <?= gj_image_dimension_attributes($src) ?>
                  alt="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                  loading="lazy"
                  decoding="async"
                >
                <span class="gallery-item-label">Open image</span>
              </a>
            </figure>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>

  <section class="section gallery-cta">
    <div class="site-shell gallery-cta-inner">
      <p class="eyebrow">Plan your stay</p>
      <h2>See a room you like?</h2>
      <p>Explore the room collection or check availability for your dates.</p>
      <div class="actions">
        <a class="button button-primary" href="room.php">View rooms</a>
        <a class="button gallery-cta-light" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
