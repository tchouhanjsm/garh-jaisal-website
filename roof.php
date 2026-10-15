<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
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
    'description' => "Rooftop dining at Garh Jaisal's Kitchen in Jaisalmer: vegetarian, vegan and non-vegetarian meals, candlelight dinners, and views of havelis, palace and lake.",
    'path' => '/roof.php',
    'og_type' => 'website',
    'og_title' => "Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen",
    'og_description' => "Candlelight dinners and city views at Garh Jaisal's Kitchen, the rooftop restaurant inside Jaisalmer Fort.",
    'og_image' => '/img/v2/fort-from-roof.jpg',
    'twitter_title' => "Rooftop Dining in Jaisalmer | Garh Jaisal's Kitchen",
    'twitter_description' => "Candlelight dinners and city views at Garh Jaisal's Kitchen, the rooftop restaurant inside Jaisalmer Fort.",
    'twitter_image' => '/img/v2/fort-from-roof.jpg',
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
      <img src="img/v2/fort-from-roof.jpg" <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg') ?> alt="" aria-hidden="true" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Rooftop dining</p>
      <h1>Rooftop dining at Garh Jaisal's Kitchen.</h1>
      <p>
        Candlelight dinners on the terrace, with views of the havelis,
        the royal palace, the lake, and the sunrise and sunset.
      </p>
      <div class="actions">
        <a class="button button-primary" href="restaurant.php#menu">View the menu</a>
        <a class="button button-light" href="contact.php?topic=dining">Contact the hotel</a>
        <a
          class="button button-light"
          href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>"
          target="_blank"
          rel="noopener"
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
          src="img/v2/fort-from-roof.jpg"
          alt="Rooftop table at dusk overlooking the lights of Jaisalmer"
          <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg') ?>
        >
      </div>

      <div class="roof-copy">
        <p>
          Garh Jaisal's Kitchen serves meals on the rooftop. The setting is
          meant for a candlelight dinner, with the city spread out around you:
          havelis, the royal palace, the lake, and the sunrise and sunset.
        </p>
        <p>
          The menu includes vegetarian, vegan, and non-vegetarian dishes. Visit
          <a href="restaurant.php#menu">Garh Jaisal's Kitchen menu</a> for the menu details.
          Confirm current menu options and prices with the hotel before you visit.
        </p>
        <p>
          <a class="text-link" href="contact.php?topic=dining">Contact the hotel</a>
          or call <a href="tel:+917727047567">+91 77270 47567</a>.
          For how to reach the fort, see <a href="direction.php">directions</a>.
        </p>
      </div>

      <div class="roof-trip-action">
        <?php
          $tripItem = ['id' => 'rooftop-dining', 'title' => "Rooftop dining at Garh Jaisal's Kitchen", 'url' => 'roof.php', 'group' => 'dining'];
          $tripButtonClass = 'button button-secondary';
          require __DIR__ . '/include/ui/trip-add-button.php';
        ?>
        <a class="text-link" href="catalogue.php">Combine dining with a stay or desert option →</a>
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
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
