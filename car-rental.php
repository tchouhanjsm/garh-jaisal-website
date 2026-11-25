<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$whatsappUrl = rtrim($siteConfig['whatsapp_url'], '/');
$carWhatsAppUrl = $whatsappUrl . '?text=' . rawurlencode(
    'Hello, I would like a quote for a Rajasthan trip. Package: [car with hotels or vehicle only]. '
    . 'Pickup date and location: [add details]. Drop-off date and location: [add details]. '
    . 'Places or route: [add stops]. Travellers: [add number]. Luggage: [add details]. '
    . 'Please confirm availability, what the quote includes and the total price.'
);
?>
<!doctype html>
<html lang="en">
<?php
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/car-rental.php';
require_once __DIR__ . '/include/schema.php';
$title = 'Delhi Airport Transfers & Rajasthan Tours | Garh Jaisal';
$description = 'Enquire about Delhi Airport pickup and return, a Rajasthan tour with hotels and a car, or vehicle-only travel. Share your route and dates for a quote.';
$faqItems = [
    ['question' => 'Can I start and finish at Delhi Airport?', 'answer' => 'Yes. You can request pickup at Delhi Airport and return to the airport after travelling in Rajasthan. Share both dates, your stops and any flight timing you already know so we can confirm the arrangements.'],
    ['question' => 'What is the difference between the two options?', 'answer' => 'Request a quote for a car with hotels if you want us to consider accommodation as part of the plan. Choose vehicle only if you are arranging your own stays. We will confirm the route, vehicle, availability, inclusions and total price for your dates.'],
    ['question' => 'Can a car enter the Fort lanes to reach the haveli?', 'answer' => 'Cars cannot enter the narrow Fort lanes. For hotel arrival, use Fort Parking and follow the Directions page for the final walk.'],
];
$page = [
    'title' => $title,
    'description' => $description,
    'path' => '/car-rental.php',
    'og_title' => $title,
    'og_description' => $description,
    'og_image' => '/img/desert10.jpg',
    'og_image_alt' => 'A jeep crossing open sand on a desert route near Jaisalmer',
    'twitter_title' => $title,
    'twitter_description' => $description,
    'twitter_image' => '/img/desert10.jpg',
    'css' => ['css/packages.css', 'css/tour.css', 'css/car-rental.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $title,
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Delhi Airport transfers and Rajasthan tours', 'item' => $canonical],
            ],
        ],
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" class="car-rental-page" tabindex="-1">
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="car-tours.php">← Car &amp; City Tours</a></div></nav>
  <section class="page-hero tour-detail-hero car-service-hero">
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Pickup and return at Delhi Airport</p>
      <h1>Delhi Airport transfers and Rajasthan tours</h1>
      <p>Start at Delhi Airport, travel through Rajasthan and return to the airport. Ask for a car with hotels or a vehicle only, with pickup, stops and drop-off arranged around your dates.</p>
      <div class="actions">
        <a class="button button-primary" href="#car-travel-quote">Request a travel quote</a>
        <?php
          $tripItem = ['id' => 'car-arrangements', 'title' => 'SUV and sedan arrangements', 'url' => 'car-rental.php', 'group' => 'transport'];
          $tripButtonClass = 'button button-outline';
          require __DIR__ . '/include/ui/trip-add-button.php';
        ?>
        <a class="button button-outline" href="direction.php">Directions to the haveli</a>
      </div>
    </div>
  </section>

  <section class="section car-offers" aria-labelledby="car-service-types-title">
    <div class="site-shell">
      <div class="section-head">
        <h2 id="car-service-types-title">How would you like to travel?</h2>
      </div>
      <div class="car-offer-grid">
        <article class="car-offer car-offer-package">
          <h3>Rajasthan tour with hotels and a car</h3>
          <p>Ask for one quote covering accommodation and car travel. Share the cities you want to visit, how long you have and any preferences for your stays.</p>
          <a class="button button-primary" href="#car-travel-quote" data-car-kind="Car with hotels">Enquire about hotels and a car</a>
        </article>
        <article class="car-offer">
          <h3>Vehicle only</h3>
          <p>Arrange your own hotels and ask us about a car for the route. Request an SUV or sedan for airport transfers, intercity journeys, or desert pickup and drop-off.</p>
          <a class="button button-secondary" href="#car-travel-quote" data-car-kind="Vehicle only">Enquire about a vehicle</a>
        </article>
      </div>
      <div class="car-route-ideas">
        <h3>Route ideas from Delhi Airport</h3>
        <ul>
          <li>Delhi Airport → Jaipur → Jodhpur → Jaisalmer → Delhi Airport</li>
          <li>Delhi Airport → Jaipur → Udaipur → Jodhpur → Delhi Airport</li>
        </ul>
        <p>These are sample city combinations. Share your own stops and dates so we can check what is feasible.</p>
      </div>
    </div>
  </section>

  <section id="car-travel-quote" class="section car-quote" aria-labelledby="car-quote-title">
    <div class="site-shell car-quote-grid">
      <div>
        <h2 id="car-quote-title">Ask for a quote</h2>
        <p>Review and send your draft in WhatsApp. Wait for us to confirm the route, pickup point, availability, inclusions and price before making plans.</p>
        <p>For a pickup from Jaisalmer airport or railway station, share your arrival time. If you are staying at the haveli, use Fort Parking as your drop-off and <a href="direction.php#arrival-overview-title">check the final walking route</a>.</p>
        <a class="text-link" href="<?= htmlspecialchars($carWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Request a car quote on WhatsApp</a>
      </div>
      <form class="car-quote-form" data-car-quote-form data-whatsapp-url="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" hidden>
        <label class="car-quote-wide">What would you like?
          <select name="kind" required>
            <option value="">Choose an option</option>
            <option>Car with hotels</option>
            <option>Vehicle only</option>
          </select>
        </label>
        <label>Pickup location <input name="pickup" type="text" maxlength="120" required placeholder="e.g. Delhi Airport"></label>
        <label>Pickup date <input name="pickupDate" type="date" required></label>
        <label>Drop-off location <input name="dropoff" type="text" maxlength="120" required placeholder="e.g. Delhi Airport"></label>
        <label>Drop-off date <input name="dropoffDate" type="date" required></label>
        <label>Passengers <input name="passengers" type="number" min="1" step="1" inputmode="numeric" required></label>
        <label>Luggage (optional) <input name="luggage" type="text" maxlength="120" placeholder="e.g. 2 suitcases"></label>
        <label class="car-quote-wide">Stops or other details (optional) <textarea name="stops" rows="3" maxlength="350" placeholder="Cities, flight times or accommodation preferences"></textarea></label>
        <button class="button button-primary car-quote-wide" type="submit">Continue to WhatsApp</button>
      </form>
    </div>
  </section>

  <section class="section site-faq car-rental-faq" aria-labelledby="car-rental-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="car-rental-faq-title">Before arranging a car.</h2></div>
        <p>Tell us if you prefer a driver who can communicate in Hindi and English, or if you have a specific pickup requirement.</p>
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
<script src="js/car-rental.js" defer></script>
</body>
</html>
