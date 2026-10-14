<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
$experiences = require __DIR__ . '/include/experience-data.php';
$activePage = 'packages';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$analyticsIds = array_values(array_filter((array) ($siteConfig['analytics_ids'] ?? []), 'is_string'));
$canonical = $siteUrl . '/desert-safari.php';
$items = array_keys(array_filter($experiences, static function ($item) {
    return ($item['status'] ?? 'live') === 'live' && ($item['category'] ?? '') === 'desert' && ($item['duration_type'] ?? '') !== 'hub';
}));
usort($items, static function ($a, $b) use ($experiences) {
    return ($experiences[$a]['order'] ?? 999) <=> ($experiences[$b]['order'] ?? 999);
});
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Which desert options are listed here?", 'answer' => "The hub links to a sunset camel safari, cultural desert evening, Sleep Under the Stars and the Swiss Tent Safari. A separate Camping page explains the two overnight setups."],
    ['question' => "Do desert departure times stay the same all year?", 'answer' => "No. Departure and sunset timings change with the season. Confirm the schedule with the hotel for your travel date."],
    ['question' => "How do I choose between a same-evening trip and an overnight stay?", 'answer' => "Choose based on whether you want to return to Jaisalmer the same evening or sleep in the desert, then compare the overnight setup and confirmed inclusions."],
    ['question' => "How do I confirm the route and inclusions?", 'answer' => "Ask the hotel to confirm the current price, departure details, route, pickup arrangement, availability and exact inclusions for your date."],
];
$page = [
    'title' => 'Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences',
    'description' => 'Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.',
    'path' => '/desert-safari.php',
    'og_title' => 'Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences',
    'og_description' => 'Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.',
    'og_image' => '/img/desert3.jpg',
    'twitter_title' => 'Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences',
    'twitter_description' => 'Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.',
    'twitter_image' => '/img/desert3.jpg',
    'css' => ['css/experiences.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences',
            'description' => 'Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Packages', 'item' => $siteUrl . '/pack.php'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Desert Safari', 'item' => $canonical],
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
  <section class="experience-hero">
    <div class="experience-hero-media">
      <img src="img/desert3.jpg" <?= gj_image_dimension_attributes('img/desert3.jpg') ?> alt="Sand dunes in the Thar Desert near Jaisalmer" fetchpriority="high">
      <div class="experience-hero-overlay"></div>
    </div>
    <div class="site-shell experience-hero-content">
      <nav class="experience-breadcrumbs" aria-label="Breadcrumb">
        <a href="index.php">Home</a><span aria-hidden="true">/</span><a href="pack.php">Packages</a><span aria-hidden="true">/</span><span aria-current="page">Desert Safari</span>
      </nav>
      <p class="eyebrow">Desert safari</p>
      <h1>Desert Safari in Jaisalmer</h1>
      <p>Choose a desert experience based on how much time you have, whether you want to return the same evening, and how you would like to spend the night.</p>
      <div class="actions">
        <a class="button button-primary" href="contact.php">Ask about availability</a>
        <a class="button button-light" href="pack.php">Back to packages</a>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Choose by the kind of evening you want</p>
          <h2>Choose how you want to experience the desert.</h2>
        </div>
        <p>The route and timing depend on the selected package and season. Confirm the current operating details with the hotel before booking.</p>
      </div>

      <div class="experience-card-grid">
        <?php foreach ($items as $slug): $item = $experiences[$slug]; ?>
          <article class="experience-card">
            <a href="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>.php">
              <img src="<?= htmlspecialchars($item['hero_image'], ENT_QUOTES, 'UTF-8') ?>" <?= gj_image_dimension_attributes($item['hero_image']) ?> alt="<?= htmlspecialchars($item['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
              <div>
                <p class="experience-card-kicker"><?= htmlspecialchars($item['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
                <h2><?= htmlspecialchars($item['h1'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                <span>View itinerary →</span>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="experience-hub-note">Looking for camping? <a class="text-link" href="camping.php">See how camping works at Garh Jaisal Haveli →</a></p>
    </div>
  </section>

  <section class="section route-planner">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Choose by time</p>
          <h2>Which desert experience fits your stay?</h2>
        </div>
      </div>
      <div class="planner-grid">
        <article><strong>One evening</strong><p>Choose a sunset camel safari or cultural safari and return to Jaisalmer the same evening.</p></article>
        <article><strong>One night, open sky</strong><p>Choose Sleep Under the Stars for an overnight desert experience without tent accommodation.</p></article>
        <article><strong>One night, tent</strong><p>Choose the Swiss Tent Safari when you prefer tent accommodation and a cultural evening.</p></article>
        <article><strong>Need help choosing?</strong><p>Ask us to compare the currently available desert options and confirm the current price, route, pickup arrangement and timing for your travel date.</p><a class="text-link" href="contact.php">Ask about desert options →</a></article>
      </div>
    </div>
  </section>


  <section class="section site-faq desert-faq" aria-labelledby="desert-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="desert-faq-title">Questions about choosing a desert safari.</h2></div>
        <p>Seasonal timing and package details should be confirmed for your date.</p>
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
<section class="section experience-cta">
    <div class="site-shell experience-cta-inner">
      <p class="eyebrow">Plan from the Fort</p>
      <h2>Stay inside Jaisalmer Fort, then head into the desert.</h2>
      <p>Ask the hotel for the latest price, departure details and availability for your travel date.</p>
      <div class="actions"><a class="button button-primary" href="contact.php">Contact us</a><a class="button button-secondary" href="guided-jaisalmer-tour.php">Explore guided city tours</a></div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
