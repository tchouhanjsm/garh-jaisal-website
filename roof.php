<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$hotelPhone = (string) ($siteConfig['hotel_phone'] ?? '');
$hotelPhoneDisplay = (string) ($siteConfig['hotel_phone_display'] ?? $hotelPhone);
$hotelEmail = $siteConfig['hotel_email'];
$whatsappUrl = $siteConfig['whatsapp_url'];
$restaurant = ['name' => "Garh Jaisal's Kitchen"];
$reservationPath = 'roof.php';
require __DIR__ . '/include/dining-reservation.php';
$menuConfig = require __DIR__ . '/include/menu-data.php';
$menu = is_array($menuConfig['items'] ?? null) ? $menuConfig['items'] : [];
$menuCategories = array_keys($menu);
$menuOwnerConfirmed = !empty($menuConfig['owner_confirmed']) && $menu !== [];
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/roof.php';
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
require_once __DIR__ . '/include/image-metadata.php';
$roofHeroPreload = gj_image_responsive_preload_descriptor('img/roofslide.webp', '100vw');
$faqItems = [
    ['question' => "Where is rooftop dining?", 'answer' => "Garh Jaisal’s Kitchen is the rooftop restaurant at Garh Jaisal Haveli, inside Jaisalmer Fort."],
    ['question' => "Can I check the menu before visiting?", 'answer' => $menuOwnerConfirmed
        ? "See the Menu tab and confirm current dishes and prices with the hotel."
        : "Use the Menu tab to ask the hotel about current dishes, prices and dietary options."],
    ['question' => "How do I check table availability?", 'answer' => "Dinner is served {$dinnerHoursLabel}, with last seating at {$lastSeatingLabel}. Use the Book a Table tab to send a request. The hotel must confirm availability. For same-day enquiries, call or WhatsApp the hotel."],
    ['question' => "Can I request a candlelight dinner inside Jaisalmer Fort?", 'answer' => "Yes. Use the Book a Table tab to ask Garh Jaisal’s Kitchen about a rooftop candlelight dinner. The hotel will confirm availability and dinner details for your date."],
];
$page = [
    'title' => "Candlelight Rooftop Dining | Jaisalmer Fort",
    'description' => "Request a candlelight dinner or rooftop table at Garh Jaisal's Kitchen inside Jaisalmer Fort. Ask about availability for your preferred date.",
    'path' => '/roof.php',
    'preload' => is_array($roofHeroPreload) ? [$roofHeroPreload] : [],
    'og_type' => 'website',
    'og_title' => "Candlelight Rooftop Dining | Jaisalmer Fort",
    'og_description' => "Request a candlelight dinner or rooftop table at Garh Jaisal's Kitchen inside Jaisalmer Fort. Ask about availability for your preferred date.",
    'og_image' => '/img/roofslide.webp',
    'og_image_alt' => 'Candlelit rooftop terrace and Jaisalmer city view at dusk',
    'twitter_title' => "Candlelight Rooftop Dining | Jaisalmer Fort",
    'twitter_description' => "Request a candlelight dinner or rooftop table at Garh Jaisal's Kitchen inside Jaisalmer Fort. Ask about availability for your preferred date.",
    'twitter_image' => '/img/roofslide.webp',
    'css' => ['css/restaurant.css', 'css/roof.css', 'css/roof-dining.css', 'css/thar-sunset-story.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => "Rooftop Dining at Garh Jaisal's Kitchen | Jaisalmer Fort",
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/restaurant.php#restaurant'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Rooftop dining', 'item' => $canonical],
            ],
        ],
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="roof-page thar-sunset-story thar-sunset-story--dining" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/roofslide.webp" <?= gj_image_dimension_attributes('img/roofslide.webp', '100vw') ?> alt="" aria-hidden="true" fetchpriority="high" loading="eager" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Rooftop dining</p>
      <h1>Rooftop dining at Garh Jaisal's Kitchen.</h1>
      <p>
        Candlelight dinners on the terrace, with views across the havelis,
        the royal palace and lake as the light changes over the fort.
      </p>
      <p>Dinner <?= e($dinnerHoursLabel) ?> · Last seating <?= e($lastSeatingLabel) ?></p>
      <div class="actions">
        <a
          class="button button-primary"
          href="#reserve"
          data-cta-id="enquiry-rooftop-table"
          data-cta-location="rooftop-dining-hero"
        >Enquire about a table</a>
        <a class="button button-light" href="#menu"><?= $menuOwnerConfirmed ? 'View the menu' : 'Ask about the menu' ?></a>
      </div>
    </div>
  </section>

  <div class="roof-dining" data-roof-dining data-default-panel="<?= $sent || $errors !== [] ? 'reserve' : 'menu' ?>">
    <nav class="site-shell roof-dining-tabs" aria-label="Rooftop dining options">
      <a id="roof-menu-tab" href="#menu">Menu</a>
      <a id="roof-reserve-tab" href="#reserve">Book a Table</a>
    </nav>
    <?php require __DIR__ . '/include/ui/dining-menu.php'; ?>
    <?php require __DIR__ . '/include/ui/dining-reservation.php'; ?>
  </div>

  <section class="section" data-story-reveal>
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Garh Jaisal's Kitchen</p>
          <h2>Meals on the rooftop.</h2>
        </div>
        <p>
          The restaurant is at the hotel: Fort Kotari Para, inside Jaisalmer Fort,
          Jaisalmer, Rajasthan 345001.
          <a href="#menu">See menu information</a>.
        </p>
      </div>

      <figure class="roof-banner">
        <img
          src="img/rest3.jpg"
          alt="Rooftop dining area with views across Jaisalmer"
          <?= gj_image_dimension_attributes('img/rest3.jpg', '(max-width: 1000px) 100vw, 1000px') ?>
          loading="lazy"
          decoding="async"
        >
        <figcaption>Views across Jaisalmer from the rooftop terrace</figcaption>
      </figure>

      <div class="roof-copy">
        <p>
          Garh Jaisal's Kitchen serves meals on the rooftop. The setting is
          meant for a candlelight dinner, with the city spread out around you:
          havelis, the royal palace and the lake. Contact the hotel to confirm
          current dining service times before visiting.
        </p>
        <p>
          Ask the hotel to confirm current dishes, prices and dietary options before you visit.
          <a href="#menu"><?= $menuOwnerConfirmed ? 'See the menu' : 'Ask about the menu' ?></a>.
        </p>
        <p>
          <a class="text-link" href="contact.php?topic=dining">Contact the hotel</a>
          or call <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a>.
          For how to reach the fort, see <a href="direction.php">directions</a>.
        </p>
      </div>

      <div class="roof-trip-action" aria-labelledby="roof-trip-title">
        <div class="roof-trip-copy">
          <p class="eyebrow">Plan with your stay</p>
          <h3 id="roof-trip-title">Add rooftop dining to your Jaisalmer plans.</h3>
          <p>Keep this experience in your Trip while you compare rooms and explore the city. Adding it does not request or confirm a table.</p>
        </div>
        <div class="roof-trip-actions">
          <?php
            $tripItem = ['id' => 'rooftop-dining', 'title' => 'Rooftop dining', 'url' => 'roof.php', 'group' => 'dining'];
            $tripButtonClass = 'button button-primary';
            require __DIR__ . '/include/ui/trip-add-button.php';
          ?>
          <a class="text-link" href="catalogue.php">Combine dining with a stay or desert option →</a>
        </div>
      </div>

      <div class="cards">
        <article class="card">
          <div class="card-image">
            <img
              src="img/roof.webp"
              alt="Rooftop terrace at sunset with a candlelit table facing Jaisalmer Fort"
              <?= gj_image_dimension_attributes('img/roof.webp', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?>
              loading="lazy"
              decoding="async"
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
              <?= gj_image_dimension_attributes('img/r2.webp', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?>
              loading="lazy"
              decoding="async"
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
              <?= gj_image_dimension_attributes('img/r3.webp', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?>
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="card-body">
            <p class="card-kicker">Views from the roof</p>
            <h3>Light over the fort</h3>
            <p>See the light change across the fort and city. Contact the hotel to confirm current dining service times before visiting.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="section site-faq roof-faq" data-story-reveal aria-labelledby="roof-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="roof-faq-title">Rooftop dining questions.</h2></div>
        <p>Ask about current menu options and confirm availability before you visit.</p>
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
    'title' => 'Complete your Fort visit',
    'intro' => 'Make dining part of a wider stay by checking room availability and planning experiences in advance.',
    'links' => [
        ['label' => 'Stay inside the Fort', 'href' => 'room.php', 'description' => 'Compare the rooms and check your dates.'],
        ['label' => 'Restaurant details', 'href' => 'restaurant.php', 'description' => 'Review dining information and ask about current menu details.'],
        ['label' => 'Desert experiences', 'href' => 'desert-safari.php', 'description' => 'Explore options for a sunset or overnight outing.'],
    ],
];
require __DIR__ . '/include/ui/planning-path.php';
?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="<?= $htmlEscape($versionedAssetUrl('js/garh-v2.js')) ?>"></script>
<script src="<?= $htmlEscape($versionedAssetUrl('js/roof-dining.js')) ?>" defer></script>
<script src="<?= $htmlEscape($versionedAssetUrl('js/restaurant.js')) ?>" defer></script>
</body>
</html>
