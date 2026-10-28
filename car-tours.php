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
    ['question' => "Which tour ideas can I ask about?", 'answer' => "The six researched themes are examples, not a complete menu. Use the custom itinerary builder to ask about other places or combine interests. All proposed routes remain enquiries until the hotel confirms feasibility and current details."],
    ['question' => "Can the hotel arrange a car for arrival?", 'answer' => "The hotel can arrange a car for arrival or a desert day. Confirm the route, pickup arrangement and current price directly with the hotel."],
    ['question' => "How do I confirm timing and price?", 'answer' => "Contact the hotel with your travel date and intended route to confirm current availability, pickup details and price."],
];
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Packages', 'href' => '/pack.php'],
    ['label' => 'Car & City Tours'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($siteUrl, '/car-tours.php', $breadcrumbs);
$page = [
    'title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'description' => 'Explore guided Jaisalmer city tours or enquire about car arrangements. Share your route and dates so the hotel can confirm options.',
    'path' => '/car-tours.php',
    'og_title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'og_description' => 'Explore guided Jaisalmer city tours or enquire about car arrangements. Share your route and dates so the hotel can confirm options.',
    'og_image' => '/img/jcity.jpg',
    'og_image_alt' => 'View over Jaisalmer’s historic city from the fort edge',
    'twitter_title' => 'Car & City Tours from Jaisalmer | Garh Jaisal Haveli',
    'twitter_description' => 'Explore guided Jaisalmer city tours or enquire about car arrangements. Share your route and dates so the hotel can confirm options.',
    'twitter_image' => '/img/jcity.jpg',
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
        $breadcrumbSchema,
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main id="main-content" tabindex="-1">
  <section class="package-hero">
    <div class="package-hero-media">
      <img src="img/jcity.jpg" <?= gj_image_dimension_attributes('img/jcity.jpg', '100vw') ?> alt="View over Jaisalmer’s historic city from the fort edge" fetchpriority="high" decoding="async">
      <div class="package-hero-overlay"></div>
    </div>
    <div class="site-shell package-hero-content">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <p class="eyebrow">Car &amp; City Tours</p>
      <h1>Explore Jaisalmer and plan your route.</h1>
      <p>Choose a guided walk inside Jaisalmer Fort, compare the half-day and full-day city tours, or enquire separately about SUV/sedan pickup, drop-off and intercity journeys across Rajasthan and Delhi. New themed routes remain ideas until the hotel confirms feasibility, access, transport, timings, inclusions and price.</p>
      <div class="actions">
        <a class="button button-primary" href="catalogue.php">Build your trip shortlist</a>
        <a class="button button-light" href="contact.php?topic=city-tour">Ask about arrangements</a>
      </div>
    </div>
  </section>
  <section class="section car-city-choice" aria-labelledby="car-city-choice-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Start with your plan</p><h2 id="car-city-choice-title">Choose what you need help arranging.</h2></div>
        <p>Begin with an existing tour page or tell us about a route you have in mind. The hotel will confirm feasibility and details for your date.</p>
      </div>
      <div class="car-city-route-grid">
        <a class="car-city-route" href="guided-jaisalmer-tour.php">
          <span class="car-city-route-number">01</span>
          <span class="car-city-route-copy"><strong>A guided visit around Jaisalmer</strong><span>Compare the existing Fort walk, half-day and full-day options.</span></span>
          <span class="car-city-route-arrow" aria-hidden="true">→</span>
        </a>
        <a class="car-city-route" href="car-rental.php">
          <span class="car-city-route-number">02</span>
          <span class="car-city-route-copy"><strong>A car or transfer enquiry</strong><span>Share your route and travel date so the hotel can confirm vehicle and arrangements.</span></span>
          <span class="car-city-route-arrow" aria-hidden="true">→</span>
        </a>
        <a class="car-city-route" href="custom-itinerary.php">
          <span class="car-city-route-number">03</span>
          <span class="car-city-route-copy"><strong>Build a route around your interests</strong><span>Combine interests or describe any places—not limited to the examples below.</span></span>
          <span class="car-city-route-arrow" aria-hidden="true">→</span>
        </a>
      </div>
      <div class="section-head car-city-existing-heading">
        <div><p class="eyebrow">Existing pages</p><h2>Explore the current options.</h2></div>
        <p>Open the relevant page for more context, then use its enquiry or trip-planning action.</p>
      </div>
      <div class="experience-card-grid">
        <article class="experience-card"><a href="guided-jaisalmer-tour.php"><div class="experience-card-media"><img src="img/j10.jpg" <?= gj_image_dimension_attributes('img/j10.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="Ornately carved sandstone architecture in Jaisalmer&#x27;s historic centre" loading="lazy" decoding="async"></div><div class="experience-card-copy"><p class="experience-card-kicker">Guided city tours</p><h2>Guided Jaisalmer Tours</h2><p>Compare the existing half-day and full-day guided tour options.</p><span>View guided city tours →</span></div></a><div class="experience-card-plan-action"><?php $tripItem = ['id' => 'guided-jaisalmer-tour', 'title' => 'Guided Jaisalmer Tours', 'url' => 'guided-jaisalmer-tour.php']; $tripButtonClass = 'button button-secondary'; require __DIR__ . '/include/ui/trip-add-button.php'; unset($tripItem, $tripButtonClass); ?></div></article>
        <article class="experience-card"><a href="city-tour.php"><div class="experience-card-media"><img src="img/j6.jpg" <?= gj_image_dimension_attributes('img/j6.jpg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="A sandstone passageway in Jaisalmer&#x27;s historic centre" loading="lazy" decoding="async"></div><div class="experience-card-copy"><p class="experience-card-kicker">Fort walk</p><h2>A Walk inside Jaisalmer Fort</h2><p>Read about the existing fort walk and ask the hotel to confirm the current arrangements.</p><span>Explore the Fort walk →</span></div></a><div class="experience-card-plan-action"><?php $tripItem = ['id' => 'fort-walk', 'title' => 'A Walk inside Jaisalmer Fort', 'url' => 'city-tour.php']; $tripButtonClass = 'button button-secondary'; require __DIR__ . '/include/ui/trip-add-button.php'; unset($tripItem, $tripButtonClass); ?></div></article>
        <article class="experience-card"><a href="car-rental.php"><div class="experience-card-media"><img src="img/sleeping_under_stars/tour_3.jpeg" <?= gj_image_dimension_attributes('img/sleeping_under_stars/tour_3.jpeg', '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="A jeep at a desert stop near Jaisalmer" loading="lazy" decoding="async"></div><div class="experience-card-copy"><p class="experience-card-kicker">Car arrangements</p><h2>Car for Arrival or a Desert Day</h2><p>Contact the hotel to confirm vehicle arrangements, route, availability and current price.</p><span>Ask about car arrangements →</span></div></a><div class="experience-card-plan-action"><?php $tripItem = ['id' => 'car-arrangements', 'title' => 'Car for Arrival or a Desert Day', 'url' => 'car-rental.php']; $tripButtonClass = 'button button-secondary'; require __DIR__ . '/include/ui/trip-add-button.php'; unset($tripItem, $tripButtonClass); ?></div></article>
      </div>
    </div>
  </section>
  <section class="section tour-ideas" aria-labelledby="tour-ideas-title" id="proposed-itineraries">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Explore by interest</p>
          <h2 id="tour-ideas-title">More ways to discover Jaisalmer.</h2>
        </div>
        <p>The six themes below are starting points, not a fixed menu. Combine interests or describe other places in the custom itinerary builder.</p>
      </div>
      <p class="tour-ideas-disclaimer">These six themes are conversation starters, not bookable products and not a complete list. The hotel has not published them as confirmed itineraries. Ask the hotel to confirm feasible routes, current access and visitor rules, transport, guide availability, timings, fees and the final price. Sending an enquiry does not reserve a tour.</p>
      <div class="tour-ideas-custom-builder">
        <div>
          <p class="eyebrow">Have a different idea?</p>
          <h3>Build your own route.</h3>
          <p>Choose several interests, add places that matter to you, and prepare one enquiry. The hotel will confirm what can be arranged for your dates.</p>
        </div>
        <a class="button button-secondary" href="custom-itinerary.php">Build a custom itinerary</a>
      </div>
      <div class="tour-idea-grid">
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Architecture & heritage</p>
          <h3>Golden sandstone havelis</h3>
          <p>A heritage-focused route built around Jaisalmer’s carved havelis and old-city architecture, with stops agreed in advance.</p>
          <a class="tour-idea-link" href="contact.php?topic=heritage-havelis" aria-label="Enquire about a proposed golden sandstone havelis itinerary">Enquire about this idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Fort & sacred architecture</p>
          <h3>Jain temples inside the Fort</h3>
          <p>An architecture-led visit focused on the Jain temples within Jaisalmer Fort, subject to current visitor access and temple rules.</p>
          <a class="tour-idea-link" href="contact.php?topic=jain-temples" aria-label="Enquire about a proposed Jain temples itinerary">Enquire about this idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Photography & landscapes</p>
          <h3>Lake and cenotaph viewpoints</h3>
          <p>Ask about a photography-friendly route involving Gadisar Lake, Bada Bagh or Vyas Chhatri. These stops may need to be planned as separate outings.</p>
          <a class="tour-idea-link" href="contact.php?topic=lake-cenotaph-photo" aria-label="Enquire about a proposed lake and cenotaph photography route">Enquire about this idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Living history & landscape</p>
          <h3>Kuldhara and the desert edge</h3>
          <p>A proposed heritage outing around Kuldhara and the surrounding landscape. Daylight access, visitor rules and the route must be checked.</p>
          <a class="tour-idea-link" href="contact.php?topic=kuldhara-landscape" aria-label="Enquire about a proposed Kuldhara and desert landscape route">Enquire about this idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Geology & nature</p>
          <h3>Fossils or desert ecology</h3>
          <p>Ask which natural-history option, if any, can be arranged: Akal Wood Fossil Park or a suitable Desert National Park area visit. Wildlife sightings are never guaranteed.</p>
          <a class="tour-idea-link" href="contact.php?topic=natural-history" aria-label="Enquire about proposed fossils or desert ecology options">Enquire about this idea <span aria-hidden="true">→</span></a>
        </article>
        <article class="tour-idea-card">
          <p class="tour-idea-kicker">Military history</p>
          <h3>Longewala War Memorial</h3>
          <p>A potential history-focused day excursion. Operational feasibility, visitor requirements, route, transport and total time need confirmation.</p>
          <a class="tour-idea-link" href="contact.php?topic=longewala-history" aria-label="Enquire about a proposed Longewala War Memorial outing">Enquire about this idea <span aria-hidden="true">→</span></a>
        </article>
      </div>
    </div>
  </section>

  <?php require __DIR__ . '/include/ui/stay-booking-note.php'; ?>

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
<?php require __DIR__ . '/include/ui/planning-path.php'; ?>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
