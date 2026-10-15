<?php
$activePage = 'blog';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$siteUrl = rtrim($siteConfig['site_url'], '/');
$pageTitle = 'Jaisalmer 2-Day Itinerary | Fort, City & Desert';
$pageDescription = 'A practical 2-day Jaisalmer itinerary built around staying inside Jaisalmer Fort, with time for heritage sightseeing, rooftop dining and a desert experience.';
$canonical = $siteUrl . '/jaisalmer-2-day-itinerary.php';
$heroImage = $siteUrl . '/img/v2/facade.jpg';
?>
<!doctype html>
<html lang="en">
<?php
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/jaisalmer-2-day-itinerary.php',
    'og_type' => 'article',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/v2/facade.jpg',
    'og_image_alt' => 'Historic sandstone architecture inside Jaisalmer Fort',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/v2/facade.jpg',
    'css' => ['css/article.css'],
    'schema' => [
        [
            '@type' => 'BlogPosting',
            '@id' => $canonical . '#article',
            'headline' => $pageTitle,
            'description' => $pageDescription,
            'image' => [$heroImage],
            'datePublished' => '2026-10-06',
            'dateModified' => '2026-10-15',
            'author' => ['@type' => 'Organization', 'name' => 'Hotel Garh Jaisal Haveli', 'url' => $siteUrl . '/'],
            'publisher' => ['@id' => $siteUrl . '/#hotel'],
            'mainEntityOfPage' => ['@id' => $canonical . '#webpage'],
            'inLanguage' => 'en-IN',
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $pageTitle,
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => $siteUrl . '/blog.php'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Jaisalmer 2-Day Itinerary', 'item' => $canonical],
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="article-page" id="main-content" tabindex="-1">
  <header class="article-hero">
    <div class="site-shell article-hero-inner">
      <?php
      $breadcrumbs = [
          ['label' => 'Home', 'href' => '/'],
          ['label' => 'Blog', 'href' => 'blog.php'],
          ['label' => '2-Day Itinerary'],
      ];
      require __DIR__ . '/include/ui/breadcrumb.php';
      ?>
<p class="eyebrow">2-day itinerary</p>
      <div class="article-meta"><span>Published 2026-10-06</span><span>Prepared from Garh Jaisal Haveli's current guest information</span></div>
      <h1>Jaisalmer 2-Day Itinerary</h1>
      <p class="lede">A practical 2-day Jaisalmer itinerary built around staying inside Jaisalmer Fort, with time for heritage sightseeing, rooftop dining and a desert experience.</p>
    </div>
  </header>
  <section class="section">
    <div class="site-shell article-layout">
      <nav class="article-toc" aria-label="On this page"><a href="#overview">Overview</a><a href="#day1">Day 1</a><a href="#day2">Day 2</a><a href="#variants">Variations</a><a href="#book">Before you book</a></nav>
      <article class="article-content">
        <figure class="article-image"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic sandstone architecture inside Jaisalmer Fort" fetchpriority="high"><figcaption class="article-caption">Historic sandstone architecture inside Jaisalmer Fort</figcaption></figure>
        <section id="overview"><p class="eyebrow">At a glance</p><h2>A two-day Jaisalmer stay works best when the Fort and desert get their own time.</h2><div class="article-facts"><div class="article-fact"><span>Day 1</span><strong>Fort, havelis and rooftop evening</strong></div><div class="article-fact"><span>Day 2</span><strong>Guided Jaisalmer route + desert</strong></div></div><p>This itinerary is designed around staying inside Jaisalmer Fort at Garh Jaisal Haveli. It is a planning framework, not a fixed timetable: actual sightseeing and desert timings should be adjusted for opening hours, weather, travel plans and the confirmed experience.</p></section>
<section id="day1"><p class="eyebrow">Day 1</p><h2>Stay inside the Fort and get oriented.</h2><h3>Arrival and check-in</h3><p>Use the <a href="direction.php">arrival guide</a> before you leave for Jaisalmer. Because vehicles cannot enter the fort lanes, plan enough time for the final walk from Fort Parking.</p><h3>Afternoon: explore the Fort</h3><p>Walk the lanes around Garh Jaisal Haveli and get a feel for the living fort. You can keep the first afternoon flexible rather than filling it with transfers.</p><h3>Evening: rooftop or sunset desert</h3><p>Choose between a quieter evening at <a href="roof.php">Garh Jaisal's Kitchen</a> and a <a href="sunset-camel-safari.php">sunset camel safari</a> if you want the desert on your first day.</p></section>
<section id="day2"><p class="eyebrow">Day 2</p><h2>Use the morning for the city, then choose the desert.</h2><h3>Morning: guided Jaisalmer</h3><p>Take the <a href="guided-jaisalmer-tour.php">guided Jaisalmer route</a> that matches your time. A half-day keeps the focus tight; a full-day gives you more room for heritage stops and local context.</p><h3>Afternoon to evening: desert</h3><p>Return from the city route and head into the desert if you have chosen a same-day safari. For an overnight plan, compare <a href="sleep-under-the-stars.php">Sleep Under the Stars</a> with the <a href="swiss-tent-safari.php">Swiss Tent Safari</a>.</p></section>
<section id="variants"><p class="eyebrow">Adjust the plan</p><h2>Three easy variations.</h2><ul><li><strong>More heritage:</strong> choose the full-day guided route and keep the desert for a separate future stay.</li><li><strong>More desert:</strong> keep Day 1 inside the fort and reserve Day 2 for a longer desert experience.</li><li><strong>More relaxed:</strong> use a half-day tour, leave space for the rooftop and choose a sunset safari rather than an overnight.</li></ul><p>Have a third day? Our <a href="jaisalmer-3-day-itinerary.php">Jaisalmer 3-day itinerary</a> explains how to leave room for the Fort, guided heritage sightseeing and a same-evening or overnight desert option.</p></section>
<section id="book"><p class="eyebrow">Before you book</p><h2>Confirm the details that can change.</h2><p>Ask the hotel to confirm the current room availability, guide language, entry fees where applicable, desert departure arrangement, overnight facilities and final return time. This keeps the itinerary realistic rather than treating it like a fixed tour brochure.</p><p>For the latest options, start with <a href="pack.php">Packages & Experiences</a> or <a href="contact.php">contact the hotel</a>.</p></section>
        <div class="article-next">
          <p class="eyebrow">Continue planning</p>
          <div class="article-next-grid"><a class="article-next-card" href="jaisalmer-fort-guide.php"><span>Read first</span><strong>Jaisalmer Fort Guide</strong></a><a class="article-next-card" href="desert-safari-guide.php"><span>Choose</span><strong>Desert Safari Guide</strong></a><a class="article-next-card" href="guided-jaisalmer-tour.php"><span>Explore</span><strong>Guided Jaisalmer Tours</strong></a></div>
        </div>
      </article>
    </div>
  </section>
  <section class="section article-footer-cta">
    <div class="site-shell article-cta-inner">
      <p class="eyebrow">Plan your stay</p>
      <h2>Stay inside Jaisalmer Fort, then explore beyond it.</h2>
      <p>Ask the hotel about rooms, arrival, guided tours and desert experiences for your travel dates.</p>
      <div class="actions"><a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a><a class="button button-secondary" href="contact.php">Contact the hotel</a></div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
