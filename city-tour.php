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
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
