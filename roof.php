<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$hotelPhone = (string) ($siteConfig['hotel_phone'] ?? '');
$hotelPhoneDisplay = (string) ($siteConfig['hotel_phone_display'] ?? $hotelPhone);
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/roof.php';
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Where is rooftop dining?", 'answer' => "Garh Jaisal’s Kitchen is the rooftop restaurant at Garh Jaisal Haveli, inside Jaisalmer Fort."],
    ['question' => "Can I check the menu before visiting?", 'answer' => "Yes. Open the restaurant menu page to review the menu; dishes and listed prices may change, so confirm current details with the hotel."],
    ['question' => "How do I check table availability?", 'answer' => "Contact the hotel to confirm current table availability. For same-day enquiries, calling or WhatsApp is a direct way to reach the team."],
    ['question' => "Can the kitchen help with dietary requests?", 'answer' => "The menu includes vegetarian, vegan and non-vegetarian dishes. Ask the hotel about current Jain and allergy options before confirming."],
];
$page = [
    'title' => "Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen",
    'description' => "Dine on the rooftop at Garh Jaisal's Kitchen inside Jaisalmer Fort, with city views and candlelight dinner options. Confirm hours and menu.",
    'path' => '/roof.php',
    'og_type' => 'website',
    'og_title' => "Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen",
    'og_description' => "Dine on the rooftop at Garh Jaisal's Kitchen inside Jaisalmer Fort, with city views and candlelight dinner options. Confirm hours and menu.",
    'og_image' => '/img/rest5.jpg',
    'og_image_alt' => 'Dining at Garh Jaisal Haveli',
    'twitter_title' => "Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen",
    'twitter_description' => "Dine on the rooftop at Garh Jaisal's Kitchen inside Jaisalmer Fort, with city views and candlelight dinner options. Confirm hours and menu.",
    'twitter_image' => '/img/rest5.jpg',
    'css' => ['css/roof.css', 'css/thar-sunset-story.css'],
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
      <img src="img/rest5.jpg" <?= gj_image_dimension_attributes('img/rest5.jpg', '100vw') ?> alt="" aria-hidden="true" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Rooftop dining</p>
      <h1>Rooftop dining at Garh Jaisal's Kitchen.</h1>
      <p>
        Candlelight dinners on the terrace, with views across the havelis,
        the royal palace and lake as the light changes over the fort.
      </p>
      <div class="actions">
        <a class="button button-primary" href="restaurant.php#menu">View the menu</a>
        <a class="button button-light" href="contact.php?topic=dining">Contact the hotel</a>
        <?php
          $tripItem = ['id' => 'rooftop-dining', 'title' => 'Rooftop dining', 'url' => 'roof.php', 'group' => 'dining'];
          $tripButtonClass = 'button button-light';
          require __DIR__ . '/include/ui/trip-add-button.php';
        ?>
        <a
          class="button button-light"
          href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
          target="_blank"
          rel="noopener"
          data-cta-id="booking-rooftop-dining"
          data-cta-location="rooftop-dining-hero"
        >Book a stay</a>
      </div>
    </div>
  </section>

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
          <a href="restaurant.php#menu">Explore the menu and dishes</a>.
        </p>
      </div>

      <div class="roof-banner">
        <img
          src="img/rest3.jpg"
          alt="Rooftop dining area with views across Jaisalmer"
          <?= gj_image_dimension_attributes('img/rest3.jpg', '(max-width: 760px) 100vw, 1180px') ?>
        >
      </div>

      <div class="roof-copy">
        <p>
          Garh Jaisal's Kitchen serves meals on the rooftop. The setting is
          meant for a candlelight dinner, with the city spread out around you:
          havelis, the royal palace and the lake. Contact the hotel to confirm
          current dining service times before visiting.
        </p>
        <p>
          The menu includes vegetarian, vegan, and non-vegetarian dishes. Visit
          <a href="restaurant.php#menu">Garh Jaisal's Kitchen menu</a> for the menu details.
          Confirm current menu options and prices with the hotel before you visit.
        </p>
        <p>
          <a class="text-link" href="contact.php?topic=dining">Contact the hotel</a>
          or call <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a>.
          For how to reach the fort, see <a href="direction.php">directions</a>.
        </p>
      </div>

      <div class="roof-trip-action">
        <a class="text-link" href="catalogue.php">Combine dining with a stay or desert option →</a>
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
        <p>Check the menu and contact the hotel to confirm current availability before you visit.</p>
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
<script src="js/garh-v2.js"></script>
</body>
</html>
