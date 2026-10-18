<?php
$activePage = 'blog';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$pageTitle = 'Jaisalmer Travel Guides | Garh Jaisal Haveli';
$pageDescription = 'Explore practical Jaisalmer travel guides for the Fort, desert safaris, 2- and 3-day itineraries, honeymoon planning and arrival at Garh Jaisal Haveli.';
$canonical = $siteUrl . '/blog.php';
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Blog'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($siteUrl, '/blog.php', $breadcrumbs);
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/blog.php',
    'preload' => [
        ['href' => '/img/j7.jpg', 'fetchpriority' => 'high'],
    ],
    'og_type' => 'website',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/j7.jpg',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/j7.jpg',
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
        $breadcrumbSchema,
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="article-page thar-sunset-story thar-sunset-story--journal" id="main-content" tabindex="-1">
  <section class="article-hero">
    <div class="site-shell article-hero-inner">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <p class="eyebrow">Blog</p>
      <h1>Travel guides for Jaisalmer’s Fort, food and desert.</h1>
      <p class="lede">Explore the living Fort, carved havelis, regional food, old-city markets and the landscapes beyond the city. Use these guides to plan your interests, then confirm date-sensitive access, menus and arrangements directly.</p>
    </div>
  </section>
  <section class="section" data-story-reveal>
    <div class="site-shell">
      <div class="section-head"><div><p class="eyebrow">Start with the useful stuff</p><h2>Guides written around an actual stay.</h2></div><p>These guides use the hotel's current guest information and are designed to answer practical questions before you arrive. Time-sensitive details should always be confirmed for your travel date.</p></div>
      <div class="blog-grid">
        <article class="blog-card blog-card--featured"><a href="jaisalmer-fort-guide.php"><div class="blog-card-image"><img src="img/j7.jpg" <?= gj_image_dimension_attributes('img/j7.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 60vw') ?> alt="Jaisalmer Fort and a public courtyard from street level" loading="eager" decoding="async"></div><div class="blog-card-body"><p class="blog-card-meta">Jaisalmer Fort</p><h2>Jaisalmer Fort Guide</h2><p>Arrival, walking inside the Fort, guided sightseeing, rooms, rooftop dining and combining the Fort with the desert.</p><span class="text-link">Read the guide →</span></div></a></article>
        <article class="blog-card"><a href="desert-safari-guide.php"><div class="blog-card-image"><img src="img/desert3.jpg" <?= gj_image_dimension_attributes('img/desert3.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="Camel riders travelling over dunes near Jaisalmer" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Desert safari</p><h2>How to Choose a Jaisalmer Desert Safari</h2><p>Compare sunset, cultural and overnight options by time, camel ride and sleeping arrangement.</p><span class="text-link">Read the guide →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-2-day-itinerary.php"><div class="blog-card-image"><img src="img/1.webp" <?= gj_image_dimension_attributes('img/1.webp') ?> alt="Panoramic view across Jaisalmer&#x27;s sandstone city" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Itinerary</p><h2>Jaisalmer 2-Day Itinerary</h2><p>A practical plan for combining the Fort, guided sightseeing, rooftop dining and a desert experience.</p><span class="text-link">See the itinerary →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-3-day-itinerary.php"><div class="blog-card-image"><img src="img/s4.jpg" <?= gj_image_dimension_attributes('img/s4.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="Jaisalmer&#x27;s old city illuminated after dark" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Three-day itinerary</p><h2>Jaisalmer in 3 Days: Fort, Heritage &amp; Desert</h2><p>A flexible three-day plan with a Fort stay, guided heritage sightseeing and a choice of same-evening or overnight desert experience.</p><span class="text-link">Explore the 3-day plan →</span></div></a></article>
        <article class="blog-card"><a href="jaisalmer-honeymoon-guide.php"><div class="blog-card-image"><img src="img/big6.jpg" <?= gj_image_dimension_attributes('img/big6.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="Rooftop dining terrace at a sandstone heritage hotel" loading="lazy"></div><div class="blog-card-body"><p class="blog-card-meta">Couples &amp; honeymoon</p><h2>Jaisalmer Honeymoon Guide</h2><p>Plan a heritage stay, ask about rooftop dining and choose a desert experience without assuming everything is one fixed package.</p><span class="text-link">Plan a couples’ escape →</span></div></a></article>
        <article class="blog-card"><a href="book-rooms-in-jaisalmer.php"><div class="blog-card-body"><p class="blog-card-meta">Staying inside the Fort</p><h2>Staying inside Jaisalmer Fort</h2><p>The last part of the journey is on foot, and a room, a rooftop meal and a desert night can fit together into one stay.</p><span class="text-link">Read the stay guide →</span></div></a></article>
        <article class="blog-card">
          <a href="best-time-to-visit-jaisalmer.php">
            <div class="blog-card-image"><img src="img/6.webp" <?= gj_image_dimension_attributes('img/6.webp') ?> alt="Rooftop seating with views over Jaisalmer" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">When to visit</p><h2>Best Time to Visit Jaisalmer</h2><p>Compare seasons, outdoor comfort and the details to check before settling on travel dates.</p><span class="text-link">Choose your season →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="how-to-reach-jaisalmer.php">
            <div class="blog-card-image"><img src="img/jcity.jpg" <?= gj_image_dimension_attributes('img/jcity.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="View over Jaisalmer&#x27;s historic city from the fort edge" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Arrival guide</p><h2>How to Reach Jaisalmer</h2><p>Plan the air, rail or road journey, then prepare for the final walk inside Jaisalmer Fort.</p><span class="text-link">Plan your arrival →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="jaisalmer-desert-safari-packing-list.php">
            <div class="blog-card-image"><img src="img/desert10.jpg" <?= gj_image_dimension_attributes('img/desert10.jpg') ?> alt="Jeep crossing open sand on a desert route" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Desert checklist</p><h2>What to Pack for a Desert Safari</h2><p>A practical checklist for clothing, footwear, personal essentials and what to confirm for an overnight stay.</p><span class="text-link">Prepare for the desert →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="jaisalmer-havelis-guide.php">
            <div class="blog-card-image"><img src="img/j5.jpg" <?= gj_image_dimension_attributes('img/j5.jpg') ?> alt="Intricate sandstone balconies on a Jaisalmer haveli" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Architecture</p><h2>Jaisalmer Havelis: Sandstone Architecture</h2><p>Read the facades, notice the craftsmanship and plan a respectful heritage walk.</p><span class="text-link">Explore the havelis →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="jaisalmer-food-guide.php">
            <div class="blog-card-image"><img src="img/Desert_Camp/camp_dinner.jpeg" <?= gj_image_dimension_attributes('img/Desert_Camp/camp_dinner.jpeg') ?> alt="A meal spread served at a desert camp near Jaisalmer" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Food and culture</p><h2>A Food Lover’s Guide to Jaisalmer</h2><p>Recognise regional dishes and learn what to ask before ordering or choosing a menu.</p><span class="text-link">Explore Rajasthani food →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="jaisalmer-beyond-the-fort-guide.php">
            <div class="blog-card-image"><img src="img/j8.jpg" <?= gj_image_dimension_attributes('img/j8.jpg') ?> alt="Heritage buildings beside the water near Jaisalmer" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Places and day trips</p><h2>Beyond Jaisalmer Fort</h2><p>Consider Gadisar Lake, Bada Bagh and wider heritage landscapes without overloading the day.</p><span class="text-link">Explore beyond the Fort →</span></div>
          </a>
        </article>
        <article class="blog-card">
          <a href="jaisalmer-markets-and-crafts-guide.php">
            <div class="blog-card-image"><img src="img/j9.jpg" <?= gj_image_dimension_attributes('img/j9.jpg') ?> alt="Colourful traditional puppets at a Jaisalmer market" loading="lazy"></div>
            <div class="blog-card-body"><p class="blog-card-meta">Markets and crafts</p><h2>Jaisalmer Markets and Crafts</h2><p>Explore textiles, embroidery, leatherwork and jewellery with practical provenance questions.</p><span class="text-link">Discover local markets →</span></div>
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
