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
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Guided Jaisalmer Tours | Half-Day & Full-Day Tours</title>
  <meta name="description" content="Explore Jaisalmer with a guided tour arranged from Garh Jaisal Haveli, with half-day and full-day options covering the fort, havelis, markets and heritage areas.">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Guided Jaisalmer Tours | Half-Day & Full-Day Tours">
  <meta property="og:description" content="Explore Jaisalmer with a guided tour arranged from Garh Jaisal Haveli, with half-day and full-day options covering the fort, havelis, markets and heritage areas.">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($siteUrl . '/img/v2/facade.jpg', ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Guided Jaisalmer Tours | Half-Day & Full-Day Tours">
  <meta name="twitter:description" content="Explore Jaisalmer with a guided tour arranged from Garh Jaisal Haveli, with half-day and full-day options covering the fort, havelis, markets and heritage areas.">
  <meta name="twitter:image" content="<?= htmlspecialchars($siteUrl . '/img/v2/facade.jpg', ENT_QUOTES, 'UTF-8') ?>">
  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
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
              'name' => 'Guided Jaisalmer Tours | Half-Day & Full-Day Tours',
              'description' => 'Explore Jaisalmer with a guided tour arranged from Garh Jaisal Haveli, with half-day and full-day options covering the fort, havelis, markets and heritage areas.',
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
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  ?>
  </script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main id="main-content" tabindex="-1">
  <section class="experience-hero">
    <div class="experience-hero-media">
      <img src="img/v2/facade.jpg" width="1333" height="2000" alt="Historic architecture inside Jaisalmer Fort" fetchpriority="high">
      <div class="experience-hero-overlay"></div>
    </div>
    <div class="site-shell experience-hero-content">
      <nav class="experience-breadcrumbs" aria-label="Breadcrumb">
        <a href="index.php">Home</a><span aria-hidden="true">/</span><a href="pack.php">Packages</a><span aria-hidden="true">/</span><span aria-current="page">Guided Jaisalmer Tours</span>
      </nav>
      <p class="eyebrow">Jaisalmer tours</p>
      <h1>Guided Jaisalmer Tours</h1>
      <p>See more of Jaisalmer with a locally arranged guided tour. Choose a shorter heritage walk when time is limited or a fuller day when you want more of the city.</p>
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
          <p class="eyebrow">Guided city experiences</p>
          <h2>Choose how much time you have.</h2>
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

  <section class="section route-planner">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">A practical choice</p>
          <h2>Why start from the Fort?</h2>
        </div>
        <p>Garh Jaisal Haveli is inside Jaisalmer Fort, so the guided experience can begin from the living fort rather than adding a separate hotel transfer just to reach the heritage core.</p>
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
      <h2>See the Fort, then connect it to the city around it.</h2>
      <p>Ask the hotel about the current guide availability, route, guide language and applicable entry fees.</p>
      <div class="actions"><a class="button button-primary" href="contact.php">Contact us</a><a class="button button-secondary" href="desert-safari.php">Explore desert safaris</a></div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
