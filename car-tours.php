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
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Which tour ideas can I ask about?", 'answer' => "The page lists existing guided tours, a Fort walk and car arrangements, plus suggested themes around havelis, Jain architecture, Kuldhara, photography and natural history. Suggested themes are enquiry ideas, not confirmed packages."],
    ['question' => "Can the hotel arrange a car for arrival?", 'answer' => "The hotel can arrange a car for arrival or a desert day. Confirm the route, pickup arrangement and current price directly with the hotel."],
    ['question' => "How do I confirm timing and price?", 'answer' => "Contact the hotel with your travel date and intended route to confirm current availability, pickup details and price."],
];
$page = [
    'title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'description' => 'Explore guided Jaisalmer city tours and enquire about car arrangements for arrival or a desert day. Confirm route, vehicle and availability directly with the hotel.',
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
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
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
      <p>Choose a guided walk inside Jaisalmer Fort, compare the half-day and full-day city tours, or enquire separately about SUV/sedan pickup, drop-off and intercity journeys across Rajasthan and Delhi. New themed routes remain ideas until the hotel confirms feasibility, access, transport, timings, inclusions and price.</p>
      <div class="actions">
        <a class="button button-primary" href="catalogue.php">Build your trip shortlist</a>
        <a class="button button-light" href="contact.php?topic=city-tour">Ask about arrangements</a>
      </div>
    </div>
  </section>
  <section class="section">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Guided tours</p><h2>Explore the city with a guide.</h2></div>
        <p>Choose a half-day or full-day guided tour, or ask about a walk through the lanes of Jaisalmer Fort.</p>
      </div>
      <div class="experience-card-grid">
        <article class="experience-card"><a href="guided-jaisalmer-tour.php"><div class="experience-card-media"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic facade inside Jaisalmer Fort" loading="lazy"></div><div class="experience-card-copy"><p class="experience-card-kicker">Guided city tours</p><h2>Guided Jaisalmer Tours</h2><p>Compare the existing half-day and full-day guided tour options.</p><span>View guided city tours →</span></div></a><div class="experience-card-plan-action"><?php $tripItem = ['id' => 'guided-jaisalmer-tour', 'title' => 'Guided Jaisalmer Tours', 'url' => 'guided-jaisalmer-tour.php']; $tripButtonClass = 'button button-secondary'; require __DIR__ . '/include/ui/trip-add-button.php'; unset($tripItem, $tripButtonClass); ?></div></article>
        <article class="experience-card"><a href="city-tour.php"><div class="experience-card-media"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic sandstone architecture inside Jaisalmer Fort" loading="lazy"></div><div class="experience-card-copy"><p class="experience-card-kicker">Fort walk</p><h2>A Walk inside Jaisalmer Fort</h2><p>Read about the existing fort walk and ask the hotel to confirm the current arrangements.</p><span>Explore the Fort walk →</span></div></a><div class="experience-card-plan-action"><?php $tripItem = ['id' => 'fort-walk', 'title' => 'A Walk inside Jaisalmer Fort', 'url' => 'city-tour.php']; $tripButtonClass = 'button button-secondary'; require __DIR__ . '/include/ui/trip-add-button.php'; unset($tripItem, $tripButtonClass); ?></div></article>
        <article class="experience-card"><a href="car-rental.php"><div class="experience-card-media"><img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg') ?> alt="Historic architecture in Jaisalmer, the starting point for car arrangements" loading="lazy"></div><div class="experience-card-copy"><p class="experience-card-kicker">Car arrangements</p><h2>Car for Arrival or a Desert Day</h2><p>Contact the hotel to confirm vehicle arrangements, route, availability and current price.</p><span>Ask about car arrangements →</span></div></a><div class="experience-card-plan-action"><?php $tripItem = ['id' => 'car-arrangements', 'title' => 'Car for Arrival or a Desert Day', 'url' => 'car-rental.php']; $tripButtonClass = 'button button-secondary'; require __DIR__ . '/include/ui/trip-add-button.php'; unset($tripItem, $tripButtonClass); ?></div></article>
      </div>
    </div>
  </section>
  <section class="section tour-ideas" aria-labelledby="tour-ideas-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Explore by interest</p>
          <h2 id="tour-ideas-title">More ways to discover Jaisalmer.</h2>
        </div>
        <p>These researched themes may help shape a private or guided itinerary. They are ideas to enquire about, not confirmed Garh Jaisal packages.</p>
      </div>
      <p class="tour-ideas-disclaimer">Before accepting a booking, the hotel must confirm whether the route can be arranged, current access and visitor rules, transport, guide availability, timings, fees and the final price. An enquiry link does not reserve a tour.</p>
      <div class="tour-idea-grid">
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Architecture & heritage</p>
          <h3>Golden sandstone havelis</h3>
          <p>A heritage-focused route built around Jaisalmer’s carved havelis and old-city architecture, with stops agreed in advance.</p>
          <a class="tour-idea-link" href="contact.php?topic=heritage-havelis">Ask about this itinerary idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Fort & sacred architecture</p>
          <h3>Jain temples inside the Fort</h3>
          <p>An architecture-led visit focused on the Jain temples within Jaisalmer Fort, subject to current visitor access and temple rules.</p>
          <a class="tour-idea-link" href="contact.php?topic=jain-temples">Ask about this itinerary idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Photography & landscapes</p>
          <h3>Lake and cenotaph viewpoints</h3>
          <p>Ask about a photography-friendly route involving Gadisar Lake, Bada Bagh or Vyas Chhatri. These stops may need to be planned as separate outings.</p>
          <a class="tour-idea-link" href="contact.php?topic=lake-cenotaph-photo">Ask about this itinerary idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Living history & landscape</p>
          <h3>Kuldhara and the desert edge</h3>
          <p>A proposed heritage outing around Kuldhara and the surrounding landscape. Daylight access, visitor rules and the route must be checked.</p>
          <a class="tour-idea-link" href="contact.php?topic=kuldhara-landscape">Ask about this itinerary idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Geology & nature</p>
          <h3>Fossils or desert ecology</h3>
          <p>Ask which natural-history option, if any, can be arranged: Akal Wood Fossil Park or a suitable Desert National Park area visit. Wildlife sightings are never guaranteed.</p>
          <a class="tour-idea-link" href="contact.php?topic=natural-history">Ask about this itinerary idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Military history</p>
          <h3>Longewala War Memorial</h3>
          <p>A potential history-focused day excursion. Operational feasibility, visitor requirements, route, transport and total time need confirmation.</p>
          <a class="tour-idea-link" href="contact.php?topic=longewala-history">Ask about this itinerary idea <span aria-hidden="true">→</span></a>
        </article>
      </div>
    </div>
  </section>

  <section class="section packages-cta"><div class="site-shell"><div class="packages-cta-inner"><p class="eyebrow">Plan with the hotel</p><h2>Confirm the details before you travel.</h2><p>Tour routes, timings, transport, fees and availability can vary. Contact the hotel for the current details for your date.</p><div class="actions"><a class="button button-primary" href="contact.php">Contact Garh Jaisal Haveli</a><a class="button button-secondary" href="direction.php">Plan your arrival</a></div></div></div></section>

  <section class="section site-faq car-tours-faq" aria-labelledby="car-tours-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="car-tours-faq-title">Questions about city tours and car arrangements.</h2></div>
        <p>Routes and car arrangements depend on your plans; confirm the current details with the hotel.</p>
      </div>
      <?php
      $faq = [
          'items' => $faqItems,
          'item_class' => 'faq-item',
          'answer_wrapper_class' => 'faq-answer',
      ];
      require __DIR__ . '/include/ui/faq.php';
      ?>
    </div>
  </section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
