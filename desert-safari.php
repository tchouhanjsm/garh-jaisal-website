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
    'title' => 'Jaisalmer Desert Safari | Sunset Camel Safaris & Overnight Stays',
    'description' => 'Compare sunset camel safaris, cultural evenings and overnight desert stays planned from Garh Jaisal Haveli inside Jaisalmer Fort. Confirm availability, timing, inclusions and price for your dates.',
    'path' => '/desert-safari.php',
    'og_title' => 'Jaisalmer Desert Safari | Sunset Camel Safaris & Overnight Stays',
    'og_description' => 'Compare sunset camel safaris, cultural evenings and overnight desert stays planned from Garh Jaisal Haveli inside Jaisalmer Fort. Confirm availability, timing, inclusions and price for your dates.',
    'og_image' => '/img/desert3.jpg',
    'twitter_title' => 'Jaisalmer Desert Safari | Sunset Camel Safaris & Overnight Stays',
    'twitter_description' => 'Compare sunset camel safaris, cultural evenings and overnight desert stays planned from Garh Jaisal Haveli inside Jaisalmer Fort. Confirm availability, timing, inclusions and price for your dates.',
    'twitter_image' => '/img/desert3.jpg',
    'css' => ['css/experiences.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences',
            'description' => 'Compare sunset camel safaris, cultural evenings and overnight desert stays planned from Garh Jaisal Haveli inside Jaisalmer Fort. Confirm availability, timing, inclusions and price for your dates.',
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
      <h1>Find the Jaisalmer desert safari that fits your trip.</h1>
      <p>A sunset outing and an overnight stay are very different experiences. Compare the options below, then ask us to confirm the route, transport, timings, accommodation, meals and total price for your date.</p>
      <div class="actions">
        <a class="button button-primary" href="#desert-options">Compare safari options</a>
        <a class="button button-light" href="contact.php?topic=desert-safari">Check dates &amp; prices</a>
      </div>
    </div>
  </section>

  <section class="section" id="desert-options">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Choose by the kind of evening you want</p>
          <h2>Pick the kind of evening you want to remember.</h2>
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
                <?php
                $experienceCardItem = $item;
                $experienceCardFactPriority = ['Duration', 'Overnight', 'Accommodation', 'Camel ride', 'Meals', 'Evening', 'Return', 'Focus', 'Best for', 'Guide languages'];
                $experienceCardFactLimit = 3;
                require __DIR__ . '/include/ui/experience-card-facts.php';
                unset($experienceCardItem, $experienceCardFactPriority, $experienceCardFactLimit);
                ?>
                <span class="experience-card-detail-cta">More details →</span>
              </div>
            </a>
            <div class="experience-card-plan-action">
              <?php
              $tripItem = ['id' => $slug, 'title' => $item['h1'], 'url' => $slug . '.php'];
              $tripButtonClass = 'button button-secondary';
              require __DIR__ . '/include/ui/trip-add-button.php';
              unset($tripItem, $tripButtonClass);
              ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="experience-hub-note">Looking for camping? <a class="text-link" href="camping.php">See how camping works at Garh Jaisal Haveli →</a></p>
    </div>
  </section>

  <?php
  $experiencePhotoCollections = require __DIR__ . '/include/experience-photo-collections.php';
  $desertPhotoCollection = $experiencePhotoCollections['desert-safari'] ?? [];
  ?>
  <?php if (count($desertPhotoCollection['slides'] ?? []) > 1): ?>
    <section class="section experience-photo-gallery" data-story-reveal aria-labelledby="desert-photo-gallery-title">
      <div class="site-shell">
        <div class="section-head">
          <div><p class="eyebrow">Desert photo gallery</p><h2 id="desert-photo-gallery-title"><?= htmlspecialchars((string) ($desertPhotoCollection['title'] ?? 'Desert experiences in pictures'), ENT_QUOTES, 'UTF-8') ?></h2></div>
          <p><?= htmlspecialchars((string) ($desertPhotoCollection['intro'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php
        $photoCarousel = [
            'label' => (string) ($desertPhotoCollection['title'] ?? 'Desert photo gallery'),
            'variant' => 'experience',
            'slides' => $desertPhotoCollection['slides'],
        ];
        require __DIR__ . '/include/ui/photo-carousel.php';
        unset($photoCarousel);
        ?>
      </div>
    </section>
  <?php endif; ?>

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
        <article><strong>Need help choosing?</strong><p>Ask us to compare the currently available desert options and confirm the current price, route, pickup arrangement and timing for your travel date.</p><a class="text-link" href="contact.php">Ask about desert options →</a><a class="text-link" href="catalogue.php">Build your trip shortlist →</a></article>
      </div>
    </div>
  </section>


  <section class="section conversion-checklist" aria-labelledby="desert-checklist-title">
  <div class="site-shell conversion-checklist__layout">
    <div><p class="eyebrow">Before you confirm</p><h2 id="desert-checklist-title">Know what is included, not just what it is called.</h2>
      <p>Desert packages can differ in important ways. Ask us to confirm these details for the exact option and date you are considering.</p>
      <a class="button button-primary" href="contact.php?topic=desert-safari">Confirm my safari details</a>
    </div>
    <ul><li>Departure, return and sunset timing for your date</li><li>Transport and pickup or meeting arrangements</li><li>Approximate camel-ride duration and route</li><li>Meal, cultural programme and overnight accommodation inclusions</li><li>Total price, availability and any conditions before payment</li></ul>
  </div>
</section>
<?php require __DIR__ . '/include/ui/stay-booking-note.php'; ?>

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
      <h2>Make the Fort your base, then head for the dunes.</h2>
      <p>Tell us your date and preferred experience. We will confirm the current price, transport, timings and inclusions before you decide.</p>
      <div class="actions"><a class="button button-primary" href="contact.php">Contact us</a><a class="button button-secondary" href="guided-jaisalmer-tour.php">Explore guided city tours</a></div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
