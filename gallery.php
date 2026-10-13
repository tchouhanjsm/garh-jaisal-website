<?php
$activePage = 'gallery';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/gallery.php';
$page = [
    'title' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
    'description' => 'Explore rooms, the haveli, desert experiences and dining at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'path' => '/gallery.php',
    'og_title' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
    'og_description' => 'See the rooms, haveli, desert experiences and dining at Garh Jaisal Haveli.',
    'og_image' => '/img/s1.jpg',
    'twitter_title' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
    'twitter_description' => 'See Garh Jaisal Haveli, its rooms, desert experiences and dining.',
    'twitter_image' => '/img/s1.jpg',
    'css' => ['css/gallery.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
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
                    'name' => 'Gallery',
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

<main class="gallery-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo gallery-hero">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/rooftop-dusk.jpg" <?= gj_image_dimension_attributes('img/v2/rooftop-dusk.jpg') ?> alt="" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <?php
      $breadcrumbs = [
          ['label' => 'Home', 'href' => '/'],
          ['label' => 'Gallery'],
      ];
      require __DIR__ . '/include/ui/breadcrumb.php';
      ?>
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
      <a href="#hotel">Fort &amp; haveli</a>
      <a href="#rooms">Rooms</a>
      <a href="#desert">Desert</a>
      <a href="#restaurant">Rooftop dining</a>
    </div>

    <div class="site-shell gallery-filters" hidden role="group" aria-label="Filter gallery photos">
      <button type="button" data-gallery-filter="all" aria-pressed="true">All photos</button>
      <button type="button" data-gallery-filter="hotel" aria-pressed="false">Fort &amp; haveli</button>
      <button type="button" data-gallery-filter="rooms" aria-pressed="false">Rooms</button>
      <button type="button" data-gallery-filter="desert" aria-pressed="false">Desert</button>
      <button type="button" data-gallery-filter="restaurant" aria-pressed="false">Rooftop dining</button>
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
    <section class="section gallery-section" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-gallery-category="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
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
              <a href="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" data-gallery-open aria-label="Open full-size image: <?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>">
                <img
                  src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                  <?= gj_image_dimension_attributes($src) ?>
                  alt="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                  loading="lazy"
                  decoding="async"
                >
                <span class="gallery-item-label">View photo</span>
              </a>
            </figure>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>

  <dialog class="gallery-lightbox" aria-label="Gallery photo viewer">
    <div class="gallery-lightbox-inner">
      <div class="gallery-lightbox-top">
        <p class="eyebrow">Photo viewer</p>
        <button type="button" class="gallery-lightbox-close" data-gallery-close>Close <span aria-hidden="true">×</span></button>
      </div>
      <figure class="gallery-lightbox-figure">
        <img data-gallery-lightbox-image alt="">
        <figcaption data-gallery-lightbox-caption></figcaption>
      </figure>
      <div class="gallery-lightbox-actions">
        <button type="button" data-gallery-previous>Previous photo</button>
        <button type="button" data-gallery-next>Next photo</button>
      </div>
    </div>
  </dialog>

  <?php
$ctaBand = [
    'section_class' => 'section gallery-cta',
    'inner_class' => 'site-shell gallery-cta-inner',
    'eyebrow' => 'Plan your stay',
    'title' => 'See a room you like?',
    'text' => 'Explore the room collection or check availability for your dates.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'View rooms',
        'href' => 'room.php',
        'class' => 'button button-primary',
    ],
    'secondary' => [
        'label' => 'Book your stay',
        'href' => $bookingUrl,
        'class' => 'button gallery-cta-light',
        'target' => '_blank',
        'rel' => 'noopener',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
