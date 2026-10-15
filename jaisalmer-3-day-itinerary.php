<?php
$activePage = 'blog';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$siteUrl = rtrim($siteConfig['site_url'], '/');
$pageTitle = 'Jaisalmer 3-Day Itinerary | Fort, Heritage & Desert';
$pageDescription = 'Plan three days in Jaisalmer with a Fort stay, guided heritage sightseeing and a desert experience. Compare same-evening and overnight options.';
$canonical = $siteUrl . '/jaisalmer-3-day-itinerary.php';
$heroImage = $siteUrl . '/img/v2/facade.jpg';
?>
<!doctype html>
<html lang="en">
<?php
$page = [
    'title' => $pageTitle, 'description' => $pageDescription,
    'path' => '/jaisalmer-3-day-itinerary.php', 'og_type' => 'article',
    'og_title' => $pageTitle, 'og_description' => $pageDescription,
    'og_image' => '/img/v2/facade.jpg', 'og_image_alt' => 'Historic sandstone architecture inside Jaisalmer Fort',
    'twitter_title' => $pageTitle, 'twitter_description' => $pageDescription,
    'twitter_image' => '/img/v2/facade.jpg', 'css' => ['css/article.css'],
    'schema' => [
        ['@type'=>'BlogPosting','@id'=>$canonical.'#article','headline'=>$pageTitle,'description'=>$pageDescription,
         'image'=>[$heroImage],'datePublished'=>'2026-10-15','dateModified'=>'2026-10-15',
         'author'=>['@type'=>'Organization','name'=>'Hotel Garh Jaisal Haveli','url'=>$siteUrl.'/'],
         'publisher'=>['@id'=>$siteUrl.'/#hotel'],'mainEntityOfPage'=>['@id'=>$canonical.'#webpage'],'inLanguage'=>'en-IN'],
        ['@type'=>'WebPage','@id'=>$canonical.'#webpage','url'=>$canonical,'name'=>$pageTitle,
         'isPartOf'=>['@id'=>$siteUrl.'/#website'],'about'=>['@id'=>$siteUrl.'/#hotel'],'breadcrumb'=>['@id'=>$canonical.'#breadcrumb']],
        ['@type'=>'BreadcrumbList','@id'=>$canonical.'#breadcrumb','itemListElement'=>[
          ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>$siteUrl.'/'],
          ['@type'=>'ListItem','position'=>2,'name'=>'Journal','item'=>$siteUrl.'/blog.php'],
          ['@type'=>'ListItem','position'=>3,'name'=>'Jaisalmer 3-Day Itinerary','item'=>$canonical]]],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="article-page" id="main-content" tabindex="-1">
  <header class="article-hero"><div class="site-shell article-hero-inner">
    <?php $breadcrumbs=[['label'=>'Home','href'=>'/'],['label'=>'Blog','href'=>'blog.php'],['label'=>'3-Day Itinerary']]; require __DIR__ . '/include/ui/breadcrumb.php'; ?>
    <p class="eyebrow">Three-day itinerary</p>
    <div class="article-meta"><span>Published 2026-10-15</span><span>Prepared from Garh Jaisal Haveli's current guest information</span></div>
    <h1>Jaisalmer in 3 Days: A Flexible Fort, Heritage and Desert Itinerary</h1>
    <p class="lede">Use three days to make room for the living Fort, a guided heritage route and the desert. The key decision is whether your second night is spent inside the Fort or on an overnight desert experience.</p>
  </div></header>
  <section class="section"><div class="site-shell article-layout">
    <nav class="article-toc" aria-label="On this page"><a href="#overview">Overview</a><a href="#day1">Day 1</a><a href="#day2">Day 2</a><a href="#day3">Day 3</a><a href="#overnight">Overnight choice</a><a href="#before-booking">Before you book</a></nav>
    <article class="article-content">
      <figure class="article-image"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic sandstone architecture inside Jaisalmer Fort" fetchpriority="high"><figcaption class="article-caption">Historic sandstone architecture inside Jaisalmer Fort</figcaption></figure>
      <section id="overview"><p class="eyebrow">At a glance</p><h2>Build the trip around the Fort, heritage sightseeing and your preferred desert night.</h2>
        <p>A three-day visit gives you space to organise the city and desert without treating every hour as a fixed tour. This guide is a planning framework, not a confirmed package or timetable. Current availability, guide language, route details, opening hours and package inclusions need to be checked for your dates.</p>
        <div class="article-facts"><div class="article-fact"><span>City base</span><strong>Stay inside Jaisalmer Fort</strong></div><div class="article-fact"><span>Main choice</span><strong>Return from the desert or stay overnight</strong></div></div>
      </section>
      <section id="day1"><p class="eyebrow">Day 1</p><h2>Arrive, settle in and get to know the living Fort.</h2>
        <p>Vehicles cannot enter the narrow Fort lanes, so guests continue on foot after Fort Parking. Read the <a href="direction.php">Jaisalmer Fort arrival guide</a> before travelling and contact the hotel if you need to discuss the final approach.</p>
        <p>Keep the first part of the day flexible: explore the lanes around your stay and avoid packing too many transfers into arrival day. Browse the <a href="room.php">heritage rooms</a> and confirm current room availability through the booking journey.</p>
        <p>For an evening meal, explore <a href="roof.php">Garh Jaisal's Kitchen</a>. Confirm current menu, prices, service hours and table availability directly with the hotel.</p>
      </section>
      <section id="day2"><p class="eyebrow">Day 2</p><h2>Give the Fort and heritage route a proper place in the plan.</h2>
        <p>Use the second day for a guided route if you want more context than a self-guided walk. The Fort walk focuses on the Jain temples inside Jaisalmer Fort, the Palace Museum and a heritage street walk. Ask the hotel to confirm current price, guide language and availability for your date through the <a href="city-tour.php">city-tour enquiry</a>.</p>
        <p>Guide languages currently listed by the hotel include English, Hindi, Bengali, Italian, French, Spanish and German; availability is confirmed for the requested date. Leave room to rest rather than scheduling around unconfirmed departure times.</p>
      </section>
      <section id="day3"><p class="eyebrow">Day 3</p><h2>Use the final day according to where you spent the second night.</h2>
        <p>If you stayed in the Fort, keep the morning for a final walk or onward departure. If you spent the second night in the desert, confirm the return-transfer arrangement and timing before committing to a train, flight or onward car; do not assume a particular return time.</p>
        <p>For longer journeys or a pickup/drop-off arrangement, share your route and date with the hotel. SUV and sedan arrangements can be requested across Rajasthan and Delhi, with route, vehicle and availability confirmed for each booking. See <a href="car-rental.php">car arrangements</a>.</p>
      </section>
      <section id="overnight"><p class="eyebrow">Choose your second night</p><h2>Same-evening desert safari or an overnight stay?</h2>
        <p><strong>Prefer to sleep in Jaisalmer:</strong> compare the <a href="sunset-camel-safari.php">sunset camel safari</a> when you want a desert experience without changing where you sleep that night; confirm timing and inclusions for your date.</p>
        <p><strong>Want to stay in the desert:</strong> compare <a href="sleep-under-the-stars.php">Sleep Under the Stars</a> with the <a href="swiss-tent-safari.php">Swiss Tent Safari</a>. Ask what is included in the selected package and confirm transfer and return details.</p>
        <p>For a full comparison of current choices, read the <a href="desert-safari-guide.php">Jaisalmer desert safari guide</a> and <a href="pack.php">Packages &amp; Experiences</a>.</p>
      </section>
      <section id="before-booking"><p class="eyebrow">Before you book</p><h2>Confirm the details that make the itinerary realistic.</h2>
        <ul><li>Room availability and the walk from Fort Parking.</li><li>Current guide price, requested language and date availability.</li><li>The selected desert experience, its exact inclusions and return arrangement.</li><li>Current rooftop dining hours, menu and prices.</li><li>Any road transfer, vehicle or pickup/drop-off request.</li></ul>
        <p>The <a href="jaisalmer-2-day-itinerary.php">Jaisalmer 2-day itinerary</a> is an alternative if you have less time. For room dates, go to <a href="room.php">rooms</a> or <a href="contact.php">contact the hotel</a>.</p>
      </section>
      <div class="article-next"><p class="eyebrow">Continue planning</p><div class="article-next-grid"><a class="article-next-card" href="jaisalmer-fort-guide.php"><span>Heritage</span><strong>Jaisalmer Fort Guide</strong></a><a class="article-next-card" href="desert-safari-guide.php"><span>Compare</span><strong>Desert Safari Guide</strong></a><a class="article-next-card" href="jaisalmer-honeymoon-guide.php"><span>Couples</span><strong>Jaisalmer Honeymoon Guide</strong></a></div></div>
    </article>
  </div></section>
  <section class="section article-footer-cta"><div class="site-shell article-cta-inner">
    <p class="eyebrow">Plan your stay</p><h2>Stay inside Jaisalmer Fort, then make room for the desert.</h2>
    <p>Check room availability and ask the hotel to confirm the guided route, desert option and travel arrangements for your dates.</p>
    <div class="actions"><a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a><a class="button button-secondary" href="contact.php">Contact the hotel</a></div>
  </div></section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
