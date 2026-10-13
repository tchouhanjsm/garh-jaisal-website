<?php
$activePage = 'blog';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$pageTitle = 'Jaisalmer Travel Guides | Garh Jaisal Haveli';
$pageDescription = 'Practical Jaisalmer travel guides from Garh Jaisal Haveli, covering the Fort, arrival, desert safari options and a 2-day itinerary.';
$canonical = $siteUrl . '/blog.php';
?>
<!doctype html>
<html lang="en">
<?php
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/blog.php',
    'og_type' => 'website',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/v2/fort-from-roof.jpg',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/v2/fort-from-roof.jpg',
    'css' => ['css/article.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $pageTitle,
            'description' => $pageDescription,
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => $canonical],
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="article-page" id="main-content" tabindex="-1">
  <section class="article-hero">
    <div class="site-shell article-hero-inner">
      <p class="eyebrow">Journal</p>
      <h1>Practical Jaisalmer travel guides.</h1>
      <p class="lede">Notes from inside Jaisalmer Fort: how to arrive, how to explore the living fort, how to choose a desert safari and how to shape a short stay.</p>
    </div>
  </section>
  <section class="section">
    <div class="site-shell">
      <div class="section-head"><div><p class="eyebrow">Start with the useful stuff</p><h2>Guides written around an actual stay.</h2></div><p>These guides use the hotel's current guest information and are designed to answer practical questions before you arrive. Time-sensitive details should always be confirmed for your travel date.</p></div>
      <div class="blog-grid">
        <article class="blog-card"><a href="jaisalmer-fort-guide.php"><div class="blog-card-image"><img src="img/v2/fort-from-roof.jpg" <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg') ?> alt="Jaisalmer Fort viewed from a rooftop" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Jaisalmer Fort</p><h2>Jaisalmer Fort Guide</h2><p>Arrival, walking inside the Fort, guided sightseeing, rooms, rooftop dining and combining the Fort with the desert.</p><span class="text-link">Read the guide →</span></div></a></article>
        <article class="blog-card"><a href="desert-safari-guide.php"><div class="blog-card-image"><img src="img/desert3.jpg" <?= gj_image_dimension_attributes('img/desert3.jpg') ?> alt="Sand dunes near Jaisalmer" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Desert safari</p><h2>How to Choose a Jaisalmer Desert Safari</h2><p>Compare sunset, cultural and overnight options by time, camel ride and sleeping arrangement.</p><span class="text-link">Read the guide →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-2-day-itinerary.php"><div class="blog-card-image"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic sandstone architecture inside Jaisalmer Fort" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Itinerary</p><h2>Jaisalmer 2-Day Itinerary</h2><p>A practical plan for combining the Fort, guided sightseeing, rooftop dining and a desert experience.</p><span class="text-link">See the itinerary →</span></div></a></article>
        <article class="blog-card"><a href="book-rooms-in-jaisalmer.php"><div class="blog-card-body"><p class="blog-card-meta">Staying inside the Fort</p><h2>Staying inside Jaisalmer Fort</h2><p>The last part of the journey is on foot, and a room, a rooftop meal and a desert night can fit together into one stay.</p><span class="text-link">Read the stay guide →</span></div></a></article>
      </div>
    </div>
  </section>
  <section class="section article-footer-cta"><div class="site-shell article-cta-inner"><p class="eyebrow">Plan from the Fort</p><h2>Stay inside Jaisalmer Fort.</h2><p>Explore rooms, arrival information and desert experiences before you book.</p><div class="actions"><a class="button button-primary" href="room.php">View rooms</a><a class="button button-secondary" href="pack.php">Explore experiences</a></div></div></section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
