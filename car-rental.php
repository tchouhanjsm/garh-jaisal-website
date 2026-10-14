<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<?php
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/car-rental.php';
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "What can the hotel arrange a car for?", 'answer' => "A car can be arranged for arrival or for a day in the desert. Confirm the specific arrangement with the hotel."],
    ['question' => "How do I confirm the pickup point and fare?", 'answer' => "Contact the hotel with your travel date and intended route to confirm the pickup arrangement and current price."],
    ['question' => "Can a car enter the fort lanes to reach the haveli?", 'answer' => "Vehicles cannot enter the narrow fort lanes. Review the Directions page and confirm the final arrival arrangement with the hotel."],
];
$page = [
    'title' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
    'description' => 'Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.',
    'path' => '/car-rental.php',
    'og_title' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
    'og_description' => 'Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.',
    'og_image' => '/img/desert10.jpg',
    'twitter_title' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
    'twitter_description' => 'Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.',
    'twitter_image' => '/img/desert10.jpg',
    'css' => ['css/packages.css', 'css/tour.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Car for arrival or a desert day', 'item' => $canonical],
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
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="car-tours.php">← Car &amp; City Tours</a></div></nav>
  <section class="page-hero">
    <div class="site-shell page-hero-inner">
      <h1>A car for arrival or a desert day</h1>
      <p>
        Garh Jaisal Haveli can arrange a car for your arrival or for a desert
        day. Confirm the car with the hotel.
      </p>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <a class="button button-primary" href="contact.php">Enquire about this experience</a>
      <div class="package-note">
        <p>Ask the hotel for the <a href="contact.php">current price</a>.</p>
      </div>
    </div>
  </section>

  <section class="section site-faq car-rental-faq" aria-labelledby="car-rental-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="car-rental-faq-title">Before arranging a car.</h2></div>
        <p>Confirm the route, pickup point and fare directly with the hotel before travel.</p>
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
