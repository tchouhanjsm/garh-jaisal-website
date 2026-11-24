<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteMediaSlots = require __DIR__ . '/include/site-media-slots.php';
$diningHero = gj_site_media_slot($siteMediaSlots, 'dining-hero', ['src' => 'img/rest2.jpg', 'alt' => "Rooftop dining area at Garh Jaisal's Kitchen in Jaisalmer"]);
require_once __DIR__ . '/include/schema.php';
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$hotelPhoneSchema = gj_schema_hotel($siteUrl, $siteConfig)['telephone'];
$hotelPhone = $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $siteConfig['hotel_phone_display'];
$whatsappUrl = $siteConfig['whatsapp_url'];
$hotelEmail = $siteConfig['hotel_email'];

$restaurant = [
    'name' => "Garh Jaisal's Kitchen",
    'tagline' => 'Rajasthani food on the rooftop.',
    'address' => 'Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001',
];
$googleMapsUrl = 'https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8';
$googleMapsEmbedUrl = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3557.6918725708306!2d70.9117074793457!3d26.913272400000007!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3947bd9e89a679cf%3A0xd196c3f044898c10!2sGarh%20Jaisal's%20Kitchen!5e0!3m2!1sen!2sin!4v1791990366078!5m2!1sen!2sin";
$instagramUrl = 'https://www.instagram.com/garhjaisal/';

/* The dining pages share the owner-supplied menu and prices. */
$menuConfig = require __DIR__ . '/include/menu-data.php';
$menuConfig = is_array($menuConfig) ? $menuConfig : [];
$menu = isset($menuConfig['items']) && is_array($menuConfig['items']) ? $menuConfig['items'] : [];
$menuCategories = array_keys($menu);
$menuOwnerConfirmed = !empty($menuConfig['owner_confirmed']);
$restaurantGallerySections = require __DIR__ . '/include/gallery-data.php';
$restaurantPhotoSlides = $restaurantGallerySections['restaurant']['images'] ?? [];


require __DIR__ . '/include/dining-reservation.php';

$pageUrl = $siteUrl . '/restaurant.php';
$heroImage = $siteUrl . '/' . ltrim($diningHero['src'], '/');

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Hotel',
            '@id' => $siteUrl . '/#hotel',
            'name' => 'Hotel Garh Jaisal Haveli',
            'url' => $siteUrl . '/',
            'telephone' => $hotelPhoneSchema,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Fort Kotari Para, Inside Jaisalmer Fort',
                'addressLocality' => 'Jaisalmer',
                'addressRegion' => 'Rajasthan',
                'postalCode' => '345001',
                'addressCountry' => 'IN',
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '/#website',
            'url' => $siteUrl . '/',
            'name' => 'Garh Jaisal Haveli',
            'publisher' => ['@id' => $siteUrl . '/#hotel'],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $pageUrl . '#webpage',
            'url' => $pageUrl,
            'name' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $pageUrl . '#restaurant'],
            'primaryImageOfPage' => ['@id' => $pageUrl . '#primaryimage'],
        ],
        [
            '@type' => 'ImageObject',
            '@id' => $pageUrl . '#primaryimage',
            'url' => $heroImage,
        ],
        [
            '@type' => 'Restaurant',
            '@id' => $pageUrl . '#restaurant',
            'name' => $restaurant['name'],
            'url' => $pageUrl,
            'image' => [$heroImage],
            'servesCuisine' => 'Rajasthani',
            'telephone' => $hotelPhone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Fort Kotari Para, Inside Jaisalmer Fort',
                'addressLocality' => 'Jaisalmer',
                'addressRegion' => 'Rajasthan',
                'postalCode' => '345001',
                'addressCountry' => 'IN',
            ],
            'parentOrganization' => ['@id' => $siteUrl . '/#hotel'],
            'acceptsReservations' => true,
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $pageUrl . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $siteUrl . '/',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $restaurant['name'],
                    'item' => $pageUrl,
                ],
            ],
        ],
    ],
];
?>
<!doctype html>
<html lang="en">
<?php
$faqItems = [
    ['question' => "Where is Garh Jaisal’s Kitchen?", 'answer' => "Garh Jaisal’s Kitchen is located at Fort Kotari Para, inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001."],
    ['question' => "Can menu items and prices change?", 'answer' => "Yes. Menu items and listed prices may change; confirm the current menu and prices with the hotel before visiting."],
    ['question' => "How do I request a table?", 'answer' => "Use the table enquiry form when online requests are available, or contact the hotel by phone or WhatsApp. A request is not confirmed until the hotel replies. Availability can vary, especially for larger groups."],
    ['question' => "Can I ask about dietary requirements?", 'answer' => "Ask the hotel about current vegetarian, vegan, Jain and allergy options before confirming your meal."],
];
$page = [
    'title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'description' => "Enjoy rooftop dining at " . $restaurant['name'] . " inside Jaisalmer Fort. Enquire about a table and confirm current hours, menu and prices.",
    'path' => '/restaurant.php',
    'og_type' => 'website',
    'og_title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'og_description' => "Enjoy rooftop dining at " . $restaurant['name'] . " inside Jaisalmer Fort. Enquire about a table and confirm current hours, menu and prices.",
    'og_image' => '/' . ltrim($diningHero['src'], '/'),
    'og_image_alt' => $diningHero['alt'],
    'twitter_title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'twitter_description' => "Enjoy rooftop dining at " . $restaurant['name'] . " inside Jaisalmer Fort. Enquire about a table and confirm current hours, menu and prices.",
    'twitter_image' => '/' . ltrim($diningHero['src'], '/'),
    'css' => ['css/restaurant.css', 'css/map-preview.css'],
    'schema' => array_values(array_filter(
        $schema['@graph'],
        static function ($node) {
            return !in_array($node['@type'] ?? '', ['Hotel', 'WebSite'], true);
        }
    )),
];
$page['schema'][] = gj_schema_faq($faqItems, $pageUrl);
require __DIR__ . '/include/page-head.php';
?>
<body class="restaurant-page">
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" tabindex="-1">
  <section class="restaurant-hero">
    <div class="site-shell restaurant-hero-grid">
      <div class="restaurant-hero-copy">
        <p class="eyebrow">Garh Jaisal's Kitchen</p>
        <h1>Rooftop dining in the heart of Jaisalmer Fort.</h1>
        <p>Settle in above the sandstone rooftops at Garh Jaisal's Kitchen. <?= $menuOwnerConfirmed ? 'Browse the menu and ask us' : 'Ask us' ?> to confirm current dishes, dietary options, service hours and a table for your date.</p>
        <div class="actions">
          <a class="button button-primary" href="#reserve" data-cta-id="enquiry-restaurant-table" data-cta-location="restaurant-hero">Enquire about a table</a>
          <a class="button button-light" href="<?= e($waLink) ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-restaurant-hero" data-cta-location="restaurant-hero">WhatsApp</a>
        </div>
        <p class="restaurant-rooftop-link"><a href="#menu"><?= $menuOwnerConfirmed ? 'View the menu' : 'Ask about the menu' ?></a> · <a href="roof.php">Explore rooftop dining</a></p>
      </div>

      <div class="restaurant-hero-media">
        <img
          src="<?= e($diningHero['src']) ?>"
          <?= gj_image_dimension_attributes($diningHero['src'], '(max-width: 760px) 100vw, 45vw') ?>
          alt="<?= e($diningHero['alt']) ?>"
          fetchpriority="high"
        >
      </div>
    </div>

    <div class="site-shell restaurant-hours" aria-label="Dining information">
      <div><span>Service hours</span><strong>Confirm current hours with the hotel</strong></div>
      <div><span>Menu</span><strong>Ask about current dishes and prices</strong></div>
      <div><span>Table availability</span><strong>Confirm for your preferred date and time</strong></div>
    </div>
  </section>

  <?php
  $guestDecisionContext = 'dining';
  $guestDecisionPrimaryHref = 'contact.php?topic=dining';
  $guestDecisionPrimaryLabel = 'Ask about menu, hours or a table';
  $guestDecisionSecondaryHref = $waLink;
  $guestDecisionSecondaryLabel = 'WhatsApp the hotel';
  $guestDecisionSecondaryExternal = true;
  require __DIR__ . '/include/ui/guest-decision-panel.php';
  unset($guestDecisionContext, $guestDecisionPrimaryHref, $guestDecisionPrimaryLabel, $guestDecisionSecondaryHref, $guestDecisionSecondaryLabel, $guestDecisionSecondaryExternal);
  ?>

  <section class="section restaurant-intro">
    <div class="site-shell restaurant-intro-grid">
      <div>
        <p class="eyebrow">The kitchen</p>
        <h2>Rajasthani favourites, served above the Golden City.</h2>
      </div>
      <p>Garh Jaisal's Kitchen is the rooftop restaurant at Garh Jaisal Haveli, inside Jaisalmer Fort. Come for the rooftop setting and ask about the food available when you visit. Current dishes, preparation details, dietary options, service hours and prices are confirmed directly with the hotel for your date.</p>
    </div>

    <div class="site-shell restaurant-gallery-social">
      <div class="restaurant-gallery" aria-label="Restaurant and rooftop photo gallery">
        <?php
        $photoCarousel = [
            'label' => "Garh Jaisal's Kitchen restaurant and rooftop",
            'variant' => 'restaurant',
            'slides' => $restaurantPhotoSlides,
        ];
        require __DIR__ . '/include/ui/photo-carousel.php';
        unset($photoCarousel);
        ?>
      </div>
      <aside class="restaurant-instagram-panel" aria-labelledby="restaurant-instagram-title">
        <p class="eyebrow">Follow along</p>
        <p class="restaurant-instagram-wordmark" aria-hidden="true">Instagram</p>
        <h2 id="restaurant-instagram-title">More moments from the rooftop.</h2>
        <p>See new restaurant photos and reels on the Garh Jaisal Haveli Instagram profile.</p>
        <a class="button button-primary" href="<?= e($instagramUrl) ?>" target="_blank" rel="noopener">Follow @garhjaisal <span aria-hidden="true">↗</span></a>
        <p class="restaurant-instagram-note">A lightweight profile link keeps this page quick to load. Individual posts and reels can be featured when their direct URLs are approved.</p>
      </aside>
    </div>
  </section>

  <section class="section restaurant-trip-planner" aria-labelledby="restaurant-trip-planner-title">
    <div class="site-shell restaurant-trip-planner-inner">
      <div class="restaurant-trip-planner-copy">
        <p class="eyebrow">Plan your visit</p>
        <h2 id="restaurant-trip-planner-title">Keep rooftop dining on your trip shortlist.</h2>
        <p>Add the rooftop experience to your Trip while you compare rooms and explore Jaisalmer. A saved Trip item is a planning note, not a table request.</p>
      </div>
      <div class="restaurant-trip-planner-actions">
        <?php
        $tripItem = ['id' => 'rooftop-dining', 'title' => 'Rooftop dining', 'url' => 'roof.php', 'group' => 'dining'];
        $tripButtonClass = 'button button-primary';
        require __DIR__ . '/include/ui/trip-add-button.php';
        ?>
        <a class="text-link" href="catalogue.php">Explore more ways to plan your stay →</a>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/include/ui/dining-menu.php'; ?>

  <section class="section restaurant-info-section">
    <div class="site-shell restaurant-info-grid">
      <div>
        <p class="eyebrow">Good to know</p>
        <h2>Before you dine.</h2>
      </div>
      <div class="restaurant-info-list">
        <article><h3>Location</h3><p>Fort Kotari Para, inside Jaisalmer Fort.</p></article>
        <article><h3>Dietary requests</h3><p>Ask the hotel about current vegetarian, vegan, Jain and allergy options.</p></article>
        <article><h3>Walk-ins</h3><p>Availability can vary. Contact the hotel ahead of time, especially for larger groups.</p></article>
        <p class="restaurant-contact-help">Questions about current menu options or dietary requests? <a class="text-link" href="contact.php?topic=dining">Send a dining enquiry →</a></p>
      </div>
    </div>
  </section>

  <section class="section restaurant-find-us" aria-labelledby="restaurant-find-us-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Find us</p>
          <h2 id="restaurant-find-us-title">Find Garh Jaisal's Kitchen.</h2>
        </div>
        <p>Open the verified Google Maps destination or scan the QR code to get directions to the restaurant.</p>
      </div>
      <div class="restaurant-find-grid">
        <?php
        $mapPreview = [
            'class' => 'restaurant-map-frame',
            'title' => 'Explore Garh Jaisal’s Kitchen on the map.',
            'description' => 'Load the interactive map only when you need it. The verified location link and QR code remain available beside the preview.',
            'embed_url' => $googleMapsEmbedUrl,
            'frame_title' => 'Google Maps location of Garh Jaisal’s Kitchen inside Jaisalmer Fort',
        ];
        require __DIR__ . '/include/ui/map-preview.php';
        ?>
        <aside class="restaurant-map-qr-card">
          <img
            src="img/restaurant-maps-qr.svg"
            alt="QR code to open Google Maps directions to Garh Jaisal's Kitchen"
            width="370"
            height="370"
            loading="lazy"
            decoding="async"
          >
          <h3>Scan for directions</h3>
          <p>Use your phone camera to open the restaurant's Google Maps destination.</p>
          <a class="text-link" href="<?= e($googleMapsUrl) ?>" target="_blank" rel="noopener">Open directions in Google Maps ↗</a>
        </aside>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/include/ui/dining-reservation.php'; ?>

  <section class="section site-faq restaurant-faq" aria-labelledby="restaurant-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="restaurant-faq-title">Before you request a table.</h2></div>
        <p>The team can confirm current menu details, dietary options and table availability.</p>
      </div>
      <?php
      $faq = [
          'items' => $faqItems,
          'item_class' => 'faq-item',
          'answer_wrapper_class' => 'faq-answer',
      ];
      require __DIR__ . '/include/ui/faq.php';
      ?>
    </div>
  </section>

<?php
$planningPath = [
    'title' => 'Make dining part of your stay',
    'intro' => 'Review rooftop dining details, compare rooms and contact the hotel to confirm current menu, hours and table options.',
    'links' => [
        ['label' => 'Rooftop dining', 'href' => 'roof.php', 'description' => 'Explore the rooftop setting and dinner information.'],
        ['label' => 'Compare rooms', 'href' => 'room.php', 'description' => 'Check room details and current rates for your dates.'],
        ['label' => 'Contact the hotel', 'href' => 'contact.php?topic=dining', 'description' => 'Ask about menu, hours or table availability.'],
    ],
];
require __DIR__ . '/include/ui/planning-path.php';
?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/restaurant.js" defer></script>
<script src="js/garh-v2.js"></script>
<script src="js/map-preview.js" defer></script>
</body>
</html>
