<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$whatsappUrl = rtrim($siteConfig['whatsapp_url'], '/');
$carWhatsAppUrl = $whatsappUrl . '?text=' . rawurlencode(
    'Hello, I would like to enquire about an SUV or sedan arrangement. '
    . 'Travel date: [add date]. Pickup: [add location]. Destination/route: [add destination]. '
    . 'Travellers: [add number]. Luggage: [add details]. '
    . 'Please confirm vehicle availability, pickup/drop-off details and the current quote.'
);
?>
<!doctype html>
<html lang="en">
<?php
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/car-rental.php';
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Which vehicles and areas can I enquire about?", 'answer' => "SUV and sedan arrangements can be requested for desert pickup/drop-off and intercity journeys across Rajasthan and Delhi. Share your exact locations and date so the route, vehicle and quote can be confirmed."],
    ['question' => "What languages can the driver communicate in?", 'answer' => "Drivers can communicate in Hindi and English. Tell the hotel if you have any specific communication needs."],
    ['question' => "Can a car enter the Fort lanes to reach the haveli?", 'answer' => "Cars cannot enter the narrow Fort lanes. For hotel arrival, use Fort Parking and follow the Directions page for the final walk."],
];
$page = [
    'title' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
    'description' => 'Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.',
    'path' => '/car-rental.php',
    'og_title' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
    'og_description' => 'Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.',
    'og_image' => '/img/desert10.jpg',
    'og_image_alt' => 'A jeep crossing open sand on a desert route near Jaisalmer',
    'twitter_title' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
    'twitter_description' => 'Garh Jaisal Haveli can arrange a car for arrival or a desert day. Confirm the arrangement on the contact page. Ask the hotel for the current price.',
    'twitter_image' => '/img/desert10.jpg',
    'css' => ['css/packages.css', 'css/tour.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Car for Arrival or a Desert Day | Garh Jaisal Haveli',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Car for arrival or a desert day', 'item' => $canonical],
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
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="car-tours.php">← Car &amp; City Tours</a></div></nav>
  <section class="page-hero tour-detail-hero car-service-hero">
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">SUV and sedan arrangements</p>
      <h1>Car services across Rajasthan and Delhi.</h1>
      <p>Enquire about pickup and drop-off from desert locations, transfers and intercity journeys. SUV and sedan arrangements are available by enquiry, with drivers who can communicate in Hindi and English.</p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($carWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Request a car quote on WhatsApp</a>
        <a class="button button-secondary" href="contact.php?topic=car-arrangements">Ask about car arrangements</a>
        <?php
          $tripItem = ['id' => 'car-arrangements', 'title' => 'SUV and sedan arrangements', 'url' => 'car-rental.php', 'group' => 'transport'];
          $tripButtonClass = 'button button-outline';
          require __DIR__ . '/include/ui/trip-add-button.php';
        ?>
        <a class="button button-outline" href="direction.php">Directions to the haveli</a>
      </div>
    </div>
  </section>

  <section class="section tour-service-section" aria-labelledby="car-service-types-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Choose the journey</p><h2 id="car-service-types-title">Different journeys, arranged to your route.</h2></div>
        <p>Share your exact locations and travel date to confirm the vehicle, route, availability and final price.</p>
      </div>
      <div class="tour-service-grid">
        <article class="tour-service-card">
          <span class="tour-service-index" aria-hidden="true">01</span>
          <h3>Desert pickup and drop-off</h3>
          <p>Ask about vehicle arrangements to or from your desert location. Provide the camp or pickup point so the route can be checked.</p>
        </article>
        <article class="tour-service-card">
          <span class="tour-service-index" aria-hidden="true">02</span>
          <h3>Intercity journeys</h3>
          <p>Enquire about SUV or sedan travel across Rajasthan and Delhi, with the origin, destination and travel date confirmed in advance.</p>
        </article>
        <article class="tour-service-card">
          <span class="tour-service-index" aria-hidden="true">03</span>
          <h3>Hindi and English communication</h3>
          <p>Drivers can communicate in Hindi and English. Share any route, pickup or communication requirements when you enquire.</p>
        </article>
      </div>
      <div class="tour-service-note">
        <h3>What to include in your enquiry</h3>
        <p>Travel date, pickup location, destination or planned stops, number of travellers, luggage and whether you prefer an SUV or sedan.</p>
        <a class="text-link" href="<?= htmlspecialchars($carWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Send these details on WhatsApp →</a>
      </div>
    </div>
  </section>  <section class="section site-faq car-rental-faq" aria-labelledby="car-rental-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="car-rental-faq-title">Before arranging a car.</h2></div>
        <p>Confirm the route, pickup point and fare directly with the hotel before travel.</p>
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
