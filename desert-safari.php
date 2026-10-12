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
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences</title>
  <meta name="description" content="Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences">
  <meta property="og:description" content="Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($siteUrl . '/img/desert3.jpg', ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Desert Safari in Jaisalmer | Camel Safari & Overnight Experiences">
  <meta name="twitter:description" content="Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.">
  <meta name="twitter:image" content="<?= htmlspecialchars($siteUrl . '/img/desert3.jpg', ENT_QUOTES, 'UTF-8') ?>">
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
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  ?>
  </script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main>
  <section class="experience-hero">
    <div class="experience-hero-media">
      <img src="img/desert3.jpg" width="1366" height="768" alt="Sand dunes in the Thar Desert near Jaisalmer" fetchpriority="high">
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
        <article><strong>More time in the desert</strong><p>Explore the Desert is the broader day experience for guests who want more time on the route.</p></article>
      </div>
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
