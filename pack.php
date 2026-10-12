<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
$experiences = require __DIR__ . '/include/experience-data.php';
$activePage = 'packages';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$analyticsIds = array_values(array_filter((array) ($siteConfig['analytics_ids'] ?? []), 'is_string'));
$canonical = $siteUrl . '/pack.php';
$visibleExperiences = array_filter($experiences, static function ($item) {
    return ($item['status'] ?? 'live') === 'live' && ($item['duration_type'] ?? '') !== 'hub';
});
$desertItems = array_keys(array_filter($visibleExperiences, static function ($item) {
    return ($item['category'] ?? '') === 'desert';
}));
$guideItems = array_keys(array_filter($visibleExperiences, static function ($item) {
    return ($item['category'] ?? '') === 'city';
}));
$sortExperienceSlugs = static function (&$slugs) use ($visibleExperiences) {
    usort($slugs, static function ($a, $b) use ($visibleExperiences) {
        return ($visibleExperiences[$a]['order'] ?? 999) <=> ($visibleExperiences[$b]['order'] ?? 999);
    });
};
$sortExperienceSlugs($desertItems);
$sortExperienceSlugs($guideItems);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli</title>
  <meta name="description" content="Explore desert safari and guided Jaisalmer tour options arranged from Garh Jaisal Haveli, including camel safaris, overnight desert stays and half-day or full-day city tours.">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli">
  <meta property="og:description" content="Choose from desert safari experiences and guided Jaisalmer tours arranged from Garh Jaisal Haveli inside Jaisalmer Fort.">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($siteUrl . '/img/desert3.jpg', ENT_QUOTES, 'UTF-8') ?>">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli">
  <meta name="twitter:description" content="Choose from desert safari experiences and guided Jaisalmer tours arranged from Garh Jaisal Haveli inside Jaisalmer Fort.">
  <meta name="twitter:image" content="<?= htmlspecialchars($siteUrl . '/img/desert3.jpg', ENT_QUOTES, 'UTF-8') ?>">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/packages.css">
  <link rel="stylesheet" href="css/experiences.css">

  <?php if ($analyticsIds): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($analyticsIds[0], ENT_QUOTES, 'UTF-8') ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    <?php foreach ($analyticsIds as $analyticsId): ?>
    gtag('config', '<?= htmlspecialchars($analyticsId, ENT_QUOTES, 'UTF-8') ?>');
    <?php endforeach; ?>
  </script>
  <?php endif; ?>

  <script type="application/ld+json">
  <?php
  echo json_encode([
      '@context' => 'https://schema.org',
      '@graph' => [
          [
              '@type' => 'WebPage',
              '@id' => $canonical . '#webpage',
              'url' => $canonical,
              'name' => 'Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli',
              'description' => 'Explore desert safari and guided Jaisalmer tour options arranged from Garh Jaisal Haveli.',
              'isPartOf' => ['@id' => $siteUrl . '/#website'],
              'about' => ['@id' => $siteUrl . '/#hotel'],
              'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
          ],
          [
              '@type' => 'BreadcrumbList',
              '@id' => $canonical . '#breadcrumb',
              'itemListElement' => [
                  [
                      '@type' => 'ListItem',
                      'position' => 1,
                      'name' => 'Home',
                      'item' => $siteUrl . '/',
                  ],
                  [
                      '@type' => 'ListItem',
                      'position' => 2,
                      'name' => 'Packages',
                      'item' => $canonical,
                  ],
              ],
          ],
      ],
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  ?>
  </script>
</head>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main>
  <section class="package-hero">
    <div class="package-hero-media">
      <img
        src="img/desert3.jpg"
        alt="Sand dunes in the Thar Desert near Jaisalmer"
        width="1366"
        height="768"
        fetchpriority="high"
      >
      <div class="package-hero-overlay"></div>
    </div>

    <div class="site-shell package-hero-content">
      <p class="eyebrow">Packages and experiences</p>
      <h1>Jaisalmer desert experiences and tours.</h1>
      <p>
        Stay inside Jaisalmer Fort, then choose a desert safari or a guided tour
        that fits the time you have in Jaisalmer.
      </p>
      <div class="actions">
        <a class="button button-primary" href="contact.php">Ask about availability</a>
        <a class="button button-light" href="room.php">View rooms</a>
      </div>
    </div>
  </section>

  <section class="section experience-map-section">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Experience map</p>
          <h2>Start with a category, then choose the route.</h2>
        </div>
        <p>The structure keeps the choices simple: desert experiences on one side, guided Jaisalmer tours on the other.</p>
      </div>

      <div class="experience-map" aria-label="Garh Jaisal experience map">
        <div class="experience-map-root">Packages</div>

        <div class="experience-map-branch experience-map-branch--desert">
          <a class="experience-map-category" href="desert-safari.php">Desert Safari</a>
          <div class="experience-map-leaves">
            <a href="sunset-camel-safari.php">Sunset Camel Safari</a>
            <a href="sleep-under-the-stars.php">Sleep Under the Stars</a>
            <a href="cultural-safari.php">Cultural Desert Safari</a>
            <a href="swiss-tent-safari.php">Swiss Tent Safari</a>
            <a href="explore-the-desert.php">Explore the Desert</a>
          </div>
        </div>

        <div class="experience-map-branch experience-map-branch--guide">
          <a class="experience-map-category" href="car-tours.php">Car &amp; City Tours</a>
          <div class="experience-map-leaves">
            <a href="half-day-jaisalmer-tour.php">Half-Day Guided Tour</a>
            <a href="full-day-jaisalmer-tour.php">Full-Day Guided Tour</a>
            <a href="city-tour.php">Walk inside Jaisalmer Fort</a>
            <a href="car-rental.php">Car for arrival or a desert day</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section packages-intro">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Choose your route</p>
          <h2>One place to plan the desert and the city.</h2>
        </div>
        <p>
          Garh Jaisal Haveli is inside Jaisalmer Fort. From here, you can arrange
          a desert experience or spend time understanding Jaisalmer with a local guide.
        </p>
      </div>

      <div class="experience-card-grid experience-card-grid--two">
        <article class="experience-card">
          <a href="desert-safari.php">
            <img src="img/desert6.jpg" alt="Camel riders in the Jaisalmer desert" loading="lazy">
            <div>
              <p class="experience-card-kicker">Desert safari</p>
              <h2>Desert Safari in Jaisalmer</h2>
              <p>Choose a sunset safari, cultural evening, open-sky overnight or Swiss tent stay.</p>
              <span>Explore desert safaris →</span>
            </div>
          </a>
        </article>

        <article class="experience-card">
          <a href="car-tours.php">
            <img src="img/v2/facade.jpg" alt="Historic architecture inside Jaisalmer Fort" loading="lazy">
            <div>
              <p class="experience-card-kicker">Car &amp; City Tours</p>
              <h2>Explore Jaisalmer and plan your route</h2>
              <p>Compare guided city tours and enquire about car arrangements for your arrival or a desert day.</p>
              <span>Explore Car &amp; City Tours →</span>
            </div>
          </a>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Desert safari</p>
          <h2>Choose how you want to spend your evening.</h2>
        </div>
        <a class="text-link" href="desert-safari.php">View all desert options →</a>
      </div>

      <div class="experience-card-grid">
        <?php foreach ($desertItems as $slug): $item = $experiences[$slug]; ?>
          <article class="experience-card">
            <a href="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>.php">
              <img src="<?= htmlspecialchars($item['hero_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
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

  <section class="section route-planner">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Car &amp; City Tours</p>
          <h2>Choose a guided walk or ask about car arrangements.</h2>
        </div>
        <a class="text-link" href="car-tours.php">View Car &amp; City Tours →</a>
      </div>

      <div class="experience-card-grid experience-card-grid--two">
        <?php foreach ($guideItems as $slug): $item = $experiences[$slug]; ?>
          <article class="experience-card">
            <a href="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>.php">
              <img src="<?= htmlspecialchars($item['hero_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
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

  <section class="section packages-planning">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Plan your stay</p>
          <h2>Start with the time you have.</h2>
        </div>
        <p>
          You do not need to fit every experience into one trip. Use the route
          below to narrow down the options, then ask us for the current details.
        </p>
      </div>

      <div class="planner-grid">
        <article><strong>4 hours</strong><p>Choose the half-day guided Jaisalmer tour when your time is limited.</p></article>
        <article><strong>One evening</strong><p>Choose a sunset camel safari or cultural desert safari and return to Jaisalmer.</p></article>
        <article><strong>One night</strong><p>Choose sleeping under the stars or a Swiss tent depending on the overnight setup you prefer.</p></article>
        <article><strong>Full day</strong><p>Choose the full-day guided city tour or a broader desert experience.</p></article>
      </div>

      <div class="package-note">
        <p><strong>Current rates and timings:</strong> Confirm the price, availability, route, pickup arrangement, guide availability and exact inclusions for your travel date before booking.</p>
      </div>
    </div>
  </section>

  <section class="section packages-cta">
    <div class="site-shell">
      <div class="packages-cta-inner">
        <p class="eyebrow">Need help choosing?</p>
        <h2>Tell us how much time you have in Jaisalmer.</h2>
        <p>We can help you compare the desert and city experiences available for your dates.</p>
        <div class="actions">
          <a class="button button-primary" href="contact.php">Contact us</a>
          <a class="button button-secondary" href="direction.php">Plan your arrival</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
