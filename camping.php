<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<?php
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/camping.php';
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "What does camping mean at Garh Jaisal Haveli?", 'answer' => "The Camping page describes Sleep Under the Stars and an overnight stay in a Swiss tent in the desert."],
    ['question' => "What is the difference between the two overnight options?", 'answer' => "Sleep Under the Stars is an open-sky overnight experience, while the Swiss Tent Safari offers overnight tent accommodation."],
    ['question' => "What should I confirm before booking?", 'answer' => "Ask the hotel to confirm current price, availability, pickup, itinerary, meals and overnight arrangements for your travel date."],
];
$page = [
    'title' => 'Desert Camping near Jaisalmer | Garh Jaisal Haveli',
    'description' => 'Camping at Garh Jaisal Haveli means Sleep Under the Stars or an overnight stay in a Swiss tent in the desert. Ask the hotel for the current price.',
    'path' => '/camping.php',
    'og_title' => 'Desert Camping near Jaisalmer | Garh Jaisal Haveli',
    'og_description' => 'Camping at Garh Jaisal Haveli means Sleep Under the Stars or an overnight stay in a Swiss tent in the desert. Ask the hotel for the current price.',
    'og_image' => '/img/v2/swiss-tent.webp',
    'og_image_alt' => 'White Swiss-style tents at a desert accommodation near Jaisalmer',
    'twitter_title' => 'Desert Camping near Jaisalmer | Garh Jaisal Haveli',
    'twitter_description' => 'Camping at Garh Jaisal Haveli means Sleep Under the Stars or an overnight stay in a Swiss tent in the desert. Ask the hotel for the current price.',
    'twitter_image' => '/img/v2/swiss-tent.webp',
    'css' => ['css/packages.css', 'css/tour.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Desert Camping near Jaisalmer | Garh Jaisal Haveli',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Camping', 'item' => $canonical],
            ],
        ],
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" tabindex="-1">
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="desert-safari.php">← Desert Tours</a></div></nav>
  <section class="page-hero page-hero--photo camping-page-hero">
    <div class="page-hero-media" aria-hidden="true">
      <img
        src="img/Desert_Camp/camp_evening.jpeg"
        <?= gj_image_dimension_attributes('img/Desert_Camp/camp_evening.jpeg', '100vw') ?>
        alt=""
        fetchpriority="high"
        decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Desert overnight experiences</p>
      <h1>Choose how you spend the night in the desert.</h1>
      <p>
        Camping at this hotel means Sleep Under the Stars and the overnight
        safari in a Swiss tent.
      </p>
    </div>
  </section>

  <section class="section camping-options" aria-labelledby="camping-options-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Compare the stays</p>
          <h2 id="camping-options-title">Two different ways to sleep in the Thar.</h2>
        </div>
        <p>Compare the open-sky experience with a tented stay, then confirm the exact setup, inclusions and current availability for your date.</p>
      </div>

      <div class="camping-option-grid">
        <article class="camping-option-card">
          <div class="camping-option-media">
            <img
              src="img/sleeping_under_stars/Tour_1.jpeg"
              <?= gj_image_dimension_attributes('img/sleeping_under_stars/Tour_1.jpeg', '(max-width: 760px) 100vw, 50vw') ?>
              alt="Mattress and bedding set out in the desert beside a small tent"
              loading="lazy"
              decoding="async">
          </div>
          <div class="camping-option-copy">
            <p class="eyebrow">Open-sky overnight</p>
            <h3>Sleep Under the Stars</h3>
            <p>An open-air overnight experience in the desert. Ask the hotel to confirm the sleeping setup, weather plan, inclusions and current availability before booking.</p>
            <a class="button button-primary" href="sleep-under-the-stars.php">Explore Sleep Under the Stars</a>
          </div>
        </article>

        <article class="camping-option-card">
          <div class="camping-option-media">
            <img
              src="img/Desert_Camp/tent_deluxe.jpeg"
              <?= gj_image_dimension_attributes('img/Desert_Camp/tent_deluxe.jpeg', '(max-width: 760px) 100vw, 50vw') ?>
              alt="Deluxe tent accommodation at the desert camp near Jaisalmer"
              loading="lazy"
              decoding="async">
          </div>
          <div class="camping-option-copy">
            <p class="eyebrow">Tented overnight</p>
            <h3>Overnight Safari in a Swiss Tent</h3>
            <p>A stay in tented accommodation. Confirm the tent category, meals, transfers and current price with the hotel for your travel date.</p>
            <a class="button button-primary" href="swiss-tent-safari.php">Explore Swiss Tent Safari</a>
          </div>
        </article>
      </div>

      <div class="package-note">
        <p>Prices and date-specific arrangements need confirmation. <a data-contact-handoff="desert_safari" href="contact.php?topic=desert-safari">Ask the hotel for the current price</a>.</p>
      </div>
    </div>
  </section>

  <section class="section site-faq camping-faq" aria-labelledby="camping-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="camping-faq-title">Questions about desert camping.</h2></div>
        <p>Confirm the overnight setup and current inclusions for your chosen date.</p>
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
<?php require __DIR__ . '/include/ui/planning-path.php'; ?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
