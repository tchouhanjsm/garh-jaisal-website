<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/car-tours.php';
$analyticsIds = array_values(array_filter((array) ($siteConfig['analytics_ids'] ?? []), 'is_string'));
?>
<!doctype html>
<html lang="en">
<?php
$page = [
    'title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'description' => 'Explore guided Jaisalmer city tours and enquire about car arrangements for arrival or a desert day with Garh Jaisal Haveli. Confirm current details directly with the hotel.',
    'path' => '/car-tours.php',
    'og_title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'og_description' => 'Compare existing guided Jaisalmer tour options and enquire about car arrangements. Routes, prices and availability should be confirmed directly.',
    'og_image' => '/img/v2/facade.jpg',
    'twitter_title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'twitter_description' => 'Compare existing guided Jaisalmer tour options and enquire about car arrangements.',
    'twitter_image' => '/img/v2/facade.jpg',
    'css' => ['css/packages.css', 'css/experiences.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
            'description' => 'Guided Jaisalmer tour options and enquiries about car arrangements from Garh Jaisal Haveli.',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
            'inLanguage' => 'en',
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Packages', 'item' => $siteUrl . '/pack.php'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Car & City Tours', 'item' => $canonical],
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main id="main-content" tabindex="-1">
  <nav class="site-shell breadcrumb-nav" aria-label="Breadcrumb"><ol><li><a href="index.php">Home</a></li><li><a href="pack.php">Packages</a></li><li aria-current="page">Car &amp; City Tours</li></ol></nav>
  <section class="package-hero">
    <div class="package-hero-media">
      <img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic architecture inside Jaisalmer Fort" fetchpriority="high">
      <div class="package-hero-overlay"></div>
    </div>
    <div class="site-shell package-hero-content">
      <p class="eyebrow">Car &amp; City Tours</p>
      <h1>Explore Jaisalmer and plan your route.</h1>
      <p>Choose an existing guided city tour or contact Garh Jaisal Haveli to ask about car arrangements. Current routes, timings, inclusions and prices must be confirmed for your travel date.</p>
      <div class="actions">
        <a class="button button-primary" href="contact.php">Ask about arrangements</a>
        <a class="button button-light" href="guided-jaisalmer-tour.php">View guided city tours</a>
      </div>
    </div>
  </section>
  <section class="section">
    <div class="site-shell">
      <nav class="breadcrumb-nav" aria-label="Tour categories"><ol><li><a href="pack.php">All packages</a></li><li aria-current="page">Car &amp; City Tours</li></ol></nav>
      <div class="section-head">
        <div><p class="eyebrow">Guided tours</p><h2>Explore the city with a guide.</h2></div>
        <p>Choose a half-day or full-day guided tour, or ask about a walk through the lanes of Jaisalmer Fort.</p>
      </div>
      <div class="experience-card-grid">
        <article class="experience-card"><a href="guided-jaisalmer-tour.php"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic facade inside Jaisalmer Fort" loading="lazy"><div><p class="experience-card-kicker">Guided city tours</p><h2>Guided Jaisalmer Tours</h2><p>Compare the existing half-day and full-day guided tour options.</p><span>View guided tours →</span></div></a></article>
        <article class="experience-card"><a href="city-tour.php"><img src="img/1.webp" alt="Historic lanes and architecture inside Jaisalmer Fort" width="1200" height="800" loading="lazy"><div><p class="experience-card-kicker">Fort walk</p><h2>A Walk inside Jaisalmer Fort</h2><p>Read about the existing fort walk and ask the hotel to confirm the current arrangements.</p><span>Explore the Fort walk →</span></div></a></article>
        <article class="experience-card"><a href="car-rental.php"><img src="img/desert10.jpg" alt="Desert landscape near Jaisalmer" width="1366" height="768" loading="lazy"><div><p class="experience-card-kicker">Car arrangements</p><h2>Car for Arrival or a Desert Day</h2><p>Contact the hotel to confirm vehicle arrangements, route, availability and current price.</p><span>Ask about car arrangements →</span></div></a></article>
      </div>
    </div>
  </section>
  <section class="section packages-cta"><div class="site-shell"><div class="packages-cta-inner"><p class="eyebrow">Plan with the hotel</p><h2>Confirm the details before you travel.</h2><p>Tour routes, timings, transport, fees and availability can vary. Contact the hotel for the current details for your date.</p><div class="actions"><a class="button button-primary" href="contact.php">Contact Garh Jaisal Haveli</a><a class="button button-secondary" href="direction.php">Plan your arrival</a></div></div></div></section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
