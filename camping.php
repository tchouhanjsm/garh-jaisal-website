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
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
