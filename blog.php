<?php
$activePage = 'blog';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$pageTitle = 'Jaisalmer Travel Guides | Fort, Desert & Itineraries | Garh Jaisal Haveli';
$pageDescription = 'Explore practical Jaisalmer travel guides for the Fort, desert safaris, 2- and 3-day itineraries, honeymoon planning and arrival at Garh Jaisal Haveli.';
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
    'css' => ['css/article.css', 'css/thar-sunset-story.css'],
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
<main class="article-page thar-sunset-story thar-sunset-story--journal" id="main-content" tabindex="-1">
  <section class="article-hero">
    <div class="site-shell article-hero-inner">
      <?php
      $breadcrumbs = [
          ['label' => 'Home', 'href' => '/'],
          ['label' => 'Blog'],
      ];
      require __DIR__ . '/include/ui/breadcrumb.php';
      ?>
      <p class="eyebrow">Blog</p>
      <h1>Practical Jaisalmer travel guides.</h1>
      <p class="lede">Plan your stay with practical guides to the living Fort, desert experiences, longer itineraries and a couples’ escape. Confirm date-sensitive details before booking.</p>
    </div>
  </section>
  <section class="section" data-story-reveal>
    <div class="site-shell">
      <div class="section-head"><div><p class="eyebrow">Start with the useful stuff</p><h2>Guides written around an actual stay.</h2></div><p>These guides use the hotel's current guest information and are designed to answer practical questions before you arrive. Time-sensitive details should always be confirmed for your travel date.</p></div>
      <div class="blog-grid">
        <article class="blog-card blog-card--featured"><a href="jaisalmer-fort-guide.php"><div class="blog-card-image"><img src="img/v2/fort-from-roof.jpg" <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg') ?> alt="Jaisalmer Fort viewed from a rooftop" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Jaisalmer Fort</p><h2>Jaisalmer Fort Guide</h2><p>Arrival, walking inside the Fort, guided sightseeing, rooms, rooftop dining and combining the Fort with the desert.</p><span class="text-link">Read the guide →</span></div></a></article>
        <article class="blog-card"><a href="desert-safari-guide.php"><div class="blog-card-image"><img src="img/desert3.jpg" <?= gj_image_dimension_attributes('img/desert3.jpg') ?> alt="Sand dunes near Jaisalmer" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Desert safari</p><h2>How to Choose a Jaisalmer Desert Safari</h2><p>Compare sunset, cultural and overnight options by time, camel ride and sleeping arrangement.</p><span class="text-link">Read the guide →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-2-day-itinerary.php"><div class="blog-card-image"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic sandstone architecture inside Jaisalmer Fort" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Itinerary</p><h2>Jaisalmer 2-Day Itinerary</h2><p>A practical plan for combining the Fort, guided sightseeing, rooftop dining and a desert experience.</p><span class="text-link">See the itinerary →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-3-day-itinerary.php"><div class="blog-card-image"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic sandstone architecture inside Jaisalmer Fort" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Three-day itinerary</p><h2>Jaisalmer in 3 Days: Fort, Heritage &amp; Desert</h2><p>A flexible three-day plan with a Fort stay, guided heritage sightseeing and a choice of same-evening or overnight desert experience.</p><span class="text-link">Explore the 3-day plan →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-honeymoon-guide.php"><div class="blog-card-image"><img src="img/v2/fort-from-roof.jpg" <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg') ?> alt="Rooftop view across Jaisalmer Fort" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Couples &amp; honeymoon</p><h2>Jaisalmer Honeymoon Guide</h2><p>Plan a heritage stay, ask about rooftop dining and choose a desert experience without assuming everything is one fixed package.</p><span class="text-link">Plan a couples’ escape →</span></div></a></article>
        <article class="blog-card"><a href="book-rooms-in-jaisalmer.php"><div class="blog-card-body"><p class="blog-card-meta">Staying inside the Fort</p><h2>Staying inside Jaisalmer Fort</h2><p>The last part of the journey is on foot, and a room, a rooftop meal and a desert night can fit together into one stay.</p><span class="text-link">Read the stay guide →</span></div></a></article>
        <article class="blog-card">
          <a href="best-time-to-visit-jaisalmer.php">
            <div class="blog-card-image"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Golden sandstone architecture inside Jaisalmer Fort" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">When to visit</p><h2>Best Time to Visit Jaisalmer</h2><p>Compare seasons, outdoor comfort and the details to check before settling on travel dates.</p><span class="text-link">Choose your season →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="how-to-reach-jaisalmer.php">
            <div class="blog-card-image"><img src="img/v2/fort-from-roof.jpg" <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg') ?> alt="Jaisalmer Fort and the sandstone city viewed from a rooftop" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Arrival guide</p><h2>How to Reach Jaisalmer</h2><p>Plan the air, rail or road journey, then prepare for the final walk inside Jaisalmer Fort.</p><span class="text-link">Plan your arrival →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="jaisalmer-desert-safari-packing-list.php">
            <div class="blog-card-image"><img src="img/desert3.jpg" <?= gj_image_dimension_attributes('img/desert3.jpg') ?> alt="Sand dunes in the Thar Desert near Jaisalmer" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Desert checklist</p><h2>What to Pack for a Desert Safari</h2><p>A practical checklist for clothing, footwear, personal essentials and what to confirm for an overnight stay.</p><span class="text-link">Prepare for the desert →</span></div>
          </a>
        </article>
      </div>
    </div>
  </section>
  <?php
$ctaBand = [
    'section_class' => 'section article-footer-cta',
    'inner_class' => 'site-shell article-cta-inner',
    'eyebrow' => 'Plan from the Fort',
    'title' => 'Stay inside Jaisalmer Fort.',
    'text' => 'Explore rooms, arrival information and desert experiences before you book.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'View rooms',
        'href' => 'room.php',
        'class' => 'button button-primary',
    ],
    'secondary' => [
        'label' => 'Explore experiences',
        'href' => 'pack.php',
        'class' => 'button button-secondary',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
