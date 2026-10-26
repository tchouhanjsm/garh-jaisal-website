<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
$experiences = require __DIR__ . '/include/experience-data.php';
$activePage = 'packages';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$analyticsIds = array_values(array_filter((array) ($siteConfig['analytics_ids'] ?? []), 'is_string'));
$canonical = $siteUrl . '/guided-jaisalmer-tour.php';
$items = array_keys(array_filter($experiences, static function ($item) {
    return ($item['status'] ?? 'live') === 'live' && ($item['category'] ?? '') === 'city' && ($item['duration_type'] ?? '') !== 'hub';
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
    ['question' => "Which guided tour lengths are listed?", 'answer' => "The hub links to half-day and full-day Jaisalmer tour options, alongside the walk inside Jaisalmer Fort."],
    ['question' => "Can the guided route be adjusted?", 'answer' => "The route can be adjusted around your interests, opening hours and the time available on your travel date."],
    ['question' => "What should I confirm before booking?", 'answer' => "Ask the hotel to confirm the current price, route, guide availability and language, entry fees where applicable, and pickup arrangements."],
];
$page = [
    'title' => 'Guided Jaisalmer Tours from the Fort | Garh Jaisal Haveli',
    'description' => 'Explore Jaisalmer with guided half-day and full-day routes from Garh Jaisal Haveli inside the Fort. Compare options and confirm guide availability, language, itinerary and current price for your dates.',
    'path' => '/guided-jaisalmer-tour.php',
    'og_title' => 'Guided Jaisalmer Tours from the Fort | Garh Jaisal Haveli',
    'og_description' => 'Explore Jaisalmer with guided half-day and full-day routes from Garh Jaisal Haveli inside the Fort. Compare options and confirm guide availability, language, itinerary and current price for your dates.',
    'og_image' => '/img/j5.jpg',
    'twitter_title' => 'Guided Jaisalmer Tours from the Fort | Garh Jaisal Haveli',
    'twitter_description' => 'Explore Jaisalmer with guided half-day and full-day routes from Garh Jaisal Haveli inside the Fort. Compare options and confirm guide availability, language, itinerary and current price for your dates.',
    'twitter_image' => '/img/j5.jpg',
    'css' => ['css/experiences.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Guided Jaisalmer Tours from the Fort | Garh Jaisal Haveli',
            'description' => 'Explore Jaisalmer with guided half-day and full-day routes from Garh Jaisal Haveli inside the Fort. Compare options and confirm guide availability, language, itinerary and current price for your dates.',
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
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Guided Jaisalmer Tours', 'item' => $canonical],
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
      <img src="img/j5.jpg" <?= gj_image_dimension_attributes('img/j5.jpg') ?> alt="Carved sandstone balconies on a historic Jaisalmer haveli" fetchpriority="high">
      <div class="experience-hero-overlay"></div>
    </div>
    <div class="site-shell experience-hero-content">
      <nav class="experience-breadcrumbs" aria-label="Breadcrumb">
        <a href="index.php">Home</a><span aria-hidden="true">/</span><a href="pack.php">Packages</a><span aria-hidden="true">/</span><span aria-current="page">Guided Jaisalmer Tours</span>
      </nav>
      <p class="eyebrow">Jaisalmer tours</p>
      <h1>See Jaisalmer with a guide, at your pace.</h1>
      <p>Choose a focused Fort walk, a half-day exploration or a fuller day across Jaisalmer's heritage areas. Tell us what interests you and how much time you have; the route, guide language and current price are confirmed for your date.</p>
      <div class="actions">
        <a class="button button-primary" href="contact.php?topic=visit-planning">Plan my guided tour</a>
        <a class="button button-light" href="#guided-options">Compare tour options</a>
      </div>
    </div>
  </section>

  <section class="section" id="guided-options">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Guided city experiences</p>
          <h2>Choose the pace that fits your stay.</h2>
        </div>
        <p>The route can be adjusted around your interests, opening hours and the time available on your travel date.</p>
      </div>

      <div class="experience-card-grid experience-card-grid--two">
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
    </div>
  </section>

  <?php
  $experiencePhotoCollections = require __DIR__ . '/include/experience-photo-collections.php';
  $cityPhotoCollection = $experiencePhotoCollections['guided-jaisalmer-tour'] ?? [];
  ?>
  <?php if (count($cityPhotoCollection['slides'] ?? []) > 1): ?>
    <section class="section experience-photo-gallery" data-story-reveal aria-labelledby="city-photo-gallery-title">
      <div class="site-shell">
        <div class="section-head">
          <div><p class="eyebrow">City photo gallery</p><h2 id="city-photo-gallery-title"><?= htmlspecialchars((string) ($cityPhotoCollection['title'] ?? 'Jaisalmer heritage in pictures'), ENT_QUOTES, 'UTF-8') ?></h2></div>
          <p><?= htmlspecialchars((string) ($cityPhotoCollection['intro'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php
        $photoCarousel = [
            'label' => (string) ($cityPhotoCollection['title'] ?? 'Jaisalmer photo gallery'),
            'variant' => 'experience',
            'slides' => $cityPhotoCollection['slides'],
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
          <p class="eyebrow">A practical choice</p>
          <h2>Begin with the place you came to see.</h2>
        </div>
        <p>Starting from Garh Jaisal Haveli puts you inside the Fort's lanes already. Ask the hotel to confirm where your guide will meet you, how the route works on foot and whether transport is needed for any stops beyond the Fort.</p>
      </div>
      <div class="planner-grid planner-grid--two">
        <article><strong>Limited time</strong><p>The half-day option keeps the route focused on the fort, heritage areas, havelis and markets.</p></article>
        <article><strong>More time to explore</strong><p>The full-day option gives the guide more time for context, additional heritage stops and the areas that matter to you.</p></article>
      </div>
    </div>
  </section>

  <section class="section experience-cta">
    <div class="site-shell experience-cta-inner">
      <p class="eyebrow">Plan your Jaisalmer stay</p>
      <h2>Give Jaisalmer the time it deserves.</h2>
      <p>Share your date, preferred language and interests. We will confirm guide availability, the route, current price and any applicable entry fees.</p>
      <div class="actions"><a class="button button-primary" href="contact.php">Contact us</a><a class="button button-secondary" href="desert-safari.php">Explore desert safaris</a></div>
    </div>
  </section>

  <section class="section site-faq guided-tours-faq" aria-labelledby="guided-tours-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="guided-tours-faq-title">Before choosing a guided tour.</h2></div>
        <p>Confirm the itinerary and current arrangements before finalising your plan.</p>
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
