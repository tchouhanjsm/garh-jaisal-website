<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<?php
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/city-tour.php';
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "What is the Fort walk?", 'answer' => "It is a walk from Garh Jaisal Haveli through the lanes inside Jaisalmer Fort."],
    ['question' => "Can I compare it with a longer guided tour?", 'answer' => "Yes. The Guided Jaisalmer Tours page links to half-day and full-day options; contact the hotel to discuss what suits your available time."],
    ['question' => "How do I confirm the current price?", 'answer' => "Contact the hotel for the current price and details before booking."],
];
$page = [
    'title' => 'A Walk through Jaisalmer Fort | Garh Jaisal Haveli',
    'description' => 'A walk inside Jaisalmer Fort starts from Garh Jaisal Haveli and continues through the lanes of the fort. Ask the hotel for the current price.',
    'path' => '/city-tour.php',
    'og_title' => 'A Walk through Jaisalmer Fort | Garh Jaisal Haveli',
    'og_description' => 'A walk inside Jaisalmer Fort starts from Garh Jaisal Haveli and continues through the lanes of the fort. Ask the hotel for the current price.',
    'og_image' => '/img/1.webp',
    'twitter_title' => 'A Walk through Jaisalmer Fort | Garh Jaisal Haveli',
    'twitter_description' => 'A walk inside Jaisalmer Fort starts from Garh Jaisal Haveli and continues through the lanes of the fort. Ask the hotel for the current price.',
    'twitter_image' => '/img/1.webp',
    'css' => ['css/packages.css', 'css/tour.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'A Walk through Jaisalmer Fort | Garh Jaisal Haveli',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'A walk inside Jaisalmer Fort', 'item' => $canonical],
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

  <section class="section site-faq fort-walk-faq" aria-labelledby="fort-walk-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="fort-walk-faq-title">Questions about the Fort walk.</h2></div>
        <p>The hotel can confirm current details and help you compare it with the guided city-tour options.</p>
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
