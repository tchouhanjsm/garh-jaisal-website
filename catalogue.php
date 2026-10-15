<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$canonical = $siteUrl . '/catalogue.php';
$bookingUrl = (string) ($siteConfig['booking_url'] ?? '');
$whatsappUrl = rtrim((string) ($siteConfig['whatsapp_url'] ?? ''), '/');
$catalogueDefinitions = require __DIR__ . '/include/catalogue-data.php';
$experiences = require __DIR__ . '/include/experience-data.php';
$rooms = require __DIR__ . '/include/rooms-data.php';
$products = [];

foreach ($catalogueDefinitions as $definition) {
    $source = (string) ($definition['source'] ?? '');
    $product = $definition;

    if ($source === 'experience') {
        $slug = (string) ($definition['slug'] ?? '');
        if (
            !isset($experiences[$slug])
            || ($experiences[$slug]['status'] ?? 'live') !== 'live'
            || ($experiences[$slug]['duration_type'] ?? 'hub') === 'hub'
        ) {
            continue;
        }
        $experience = $experiences[$slug];
        $product['title'] = $experience['h1'];
        $product['description'] = $experience['summary'] ?? $experience['intro'] ?? $experience['description'];
        $product['image'] = $experience['hero_image'];
        $product['alt'] = $experience['hero_alt'];
        $product['url'] = $experience['slug'] . '.php';
        $product['duration'] = $experience['at_a_glance']['Duration'] ?? '';
    } elseif ($source === 'rooms') {
        $product['description'] = str_replace('seven', strtolower((string) count($rooms)), (string) $definition['description']);
        $product['book_url'] = $bookingUrl;
        $product['duration'] = '';
    } elseif ($source === 'service') {
        $product['duration'] = '';
    } else {
        continue;
    }

    $message = 'Hello, I would like to enquire about ' . (string) ($product['title'] ?? 'this option')
        . '. Please share the current price, availability and arrangements for my travel dates.';
    $product['whatsapp_url'] = $whatsappUrl . '?text=' . rawurlencode($message);
    $products[] = $product;
}

require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => 'Can I combine a room stay with a desert experience?', 'answer' => 'Yes, you can add several options to your Trip shortlist and send one enquiry to discuss timing and arrangements. The hotel confirms current rates and availability for your dates.'],
    ['question' => 'Are the prices fixed on this page?', 'answer' => 'No prices are displayed here. Ask the hotel for the current rates, inclusions and availability for your travel dates.'],
    ['question' => 'Does adding something to my Trip confirm a booking?', 'answer' => 'No. The Trip tray is a shortlist for an enquiry. A team member must confirm the current arrangements directly with you.'],
];
$itemList = [];
foreach ($products as $index => $product) {
    $itemList[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => $siteUrl . '/' . $product['url'],
        'name' => $product['title'],
    ];
}
$page = [
    'title' => 'Jaisalmer Trip Planner | Rooms, Desert Stays, Tours & Transport',
    'description' => 'Build a Jaisalmer trip around a Fort stay, desert experience, guided tour, car arrangement or rooftop dining. Shortlist options and enquire for current rates.',
    'path' => '/catalogue.php',
    'og_image' => '/img/desert3.jpg',
    'og_image_alt' => 'Sand dunes in the Thar Desert near Jaisalmer',
    'css' => ['css/catalogue.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Jaisalmer Trip Planner | Garh Jaisal Haveli',
            'description' => 'A data-driven shortlist of existing room, desert, guided tour, transport and rooftop dining options.',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Trip planner', 'item' => $canonical],
            ],
        ],
        [
            '@type' => 'ItemList',
            '@id' => $canonical . '#catalogue',
            'name' => 'Jaisalmer trip options',
            'itemListElement' => $itemList,
        ],
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" class="catalogue-page" tabindex="-1">
  <section class="catalogue-hero" aria-labelledby="catalogue-title">
    <div class="catalogue-hero-media" aria-hidden="true">
      <img src="img/desert3.jpg" <?= function_exists('gj_image_dimension_attributes') ? gj_image_dimension_attributes('img/desert3.jpg') : '' ?> alt="" fetchpriority="high" decoding="async">
    </div>
    <div class="catalogue-hero-overlay"></div>
    <div class="site-shell catalogue-hero-content">
      <p class="eyebrow">Thar Sunset · Plan in one place</p>
      <h1 id="catalogue-title">Build a Jaisalmer trip around your stay.</h1>
      <p>Shortlist a room, desert evening or overnight camp, guided sightseeing, transport and rooftop dining. Send one enquiry to discuss the current rates and arrangements for your dates.</p>
      <div class="actions">
        <a class="button button-primary" href="#trip-products">Explore the options</a>
        <a class="button button-light" href="contact.php">Ask the hotel</a>
      </div>
      <p class="catalogue-hero-note">Inside Jaisalmer Fort · Desert experiences by enquiry · No booking is confirmed by adding an item</p>
    </div>
  </section>

  <section class="section catalogue-products" id="trip-products" aria-labelledby="trip-products-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Choose your pieces</p>
          <h2 id="trip-products-title">Stay, explore, travel and dine.</h2>
        </div>
        <p>Each card uses the current page details. Prices and date-specific availability are confirmed by the hotel, not assumed here.</p>
      </div>
      <div class="catalogue-grid">
        <?php foreach ($products as $product): ?>
          <?php require __DIR__ . '/include/ui/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php
  $openSky = $experiences['sleep-under-the-stars'] ?? null;
  $swissTent = $experiences['swiss-tent-safari'] ?? null;
  $campOptions = [];
  foreach ([
      ['slug' => 'sleep-under-the-stars', 'item' => $openSky, 'label' => 'Open-sky overnight'],
      ['slug' => 'swiss-tent-safari', 'item' => $swissTent, 'label' => 'Swiss tent overnight'],
  ] as $option) {
      if (is_array($option['item']) && ($option['item']['status'] ?? 'live') === 'live') {
          $campOptions[] = $option;
      }
  }
  ?>
  <section class="section catalogue-compare" aria-labelledby="camp-compare-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Compare the overnight setup</p>
          <h2 id="camp-compare-title">Open sky or a Swiss tent?</h2>
        </div>
        <p>Both are existing overnight options. Compare the accommodation style, then ask about the current itinerary and price.</p>
      </div>
      <div class="catalogue-compare-grid">
        <?php foreach ($campOptions as $option): ?>
          <?php
          $camp = $option['item'];
          $campSlug = $option['slug'];
          $tripItem = [
              'id' => $campSlug,
              'title' => $camp['h1'],
              'url' => $campSlug . '.php',
          ];
          ?>
          <article class="catalogue-compare-card">
            <img
              src="<?= htmlspecialchars($camp['hero_image'], ENT_QUOTES, 'UTF-8') ?>"
              <?= function_exists('gj_image_dimension_attributes') ? gj_image_dimension_attributes($camp['hero_image']) : '' ?>
              alt="<?= htmlspecialchars($camp['hero_alt'], ENT_QUOTES, 'UTF-8') ?>"
              loading="lazy"
              decoding="async"
            >
            <div class="catalogue-compare-body">
              <p class="eyebrow"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></p>
              <h3><?= htmlspecialchars($camp['h1'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($camp['summary'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
              <ul>
                <?php if (!empty($camp['at_a_glance']['Duration'])): ?>
                  <li><strong>Duration:</strong> <?= htmlspecialchars($camp['at_a_glance']['Duration'], ENT_QUOTES, 'UTF-8') ?></li>
                <?php endif; ?>
                <?php if ($campSlug === 'sleep-under-the-stars'): ?>
                  <li><strong>Sleep setup:</strong> Open-air, with a mattress and blankets.</li>
                  <li><strong>Bathroom:</strong> Not the same setup as a Swiss tent; confirm the current arrangement before booking.</li>
                <?php else: ?>
                  <li><strong>Sleep setup:</strong> Swiss Deluxe Tent accommodation.</li>
                  <li><strong>Bathroom:</strong> Attached bathroom with hot and cold shower.</li>
                <?php endif; ?>
              </ul>
              <?php require __DIR__ . '/include/ui/trip-add-button.php'; ?>
              <a class="text-link" href="<?= htmlspecialchars($campSlug, ENT_QUOTES, 'UTF-8') ?>.php">Read this experience →</a>
            </div>
          </article>
          <?php unset($tripItem); ?>
        <?php endforeach; ?>
      </div>
      <p class="catalogue-footnote">Exact pickup, departure, sunset timing, inclusions and availability vary by date and are confirmed by the hotel.</p>
    </div>
  </section>

  <section class="section catalogue-transport" aria-labelledby="transport-compare-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Getting between places</p>
          <h2 id="transport-compare-title">Choose the journey you need.</h2>
        </div>
        <p>Share your locations and date so the team can check the route, vehicle, availability and quote.</p>
      </div>
      <div class="catalogue-transport-grid">
        <?php
        $transportOptions = [
            [
                'id' => 'transport-desert-pickup',
                'title' => 'Desert pickup or drop-off',
                'description' => 'Ask about a car arrangement to or from your desert location.',
                'url' => 'car-rental.php',
            ],
            [
                'id' => 'transport-intercity',
                'title' => 'Intercity car journey',
                'description' => 'Ask about SUV or sedan travel across Rajasthan and Delhi.',
                'url' => 'car-rental.php',
            ],
            [
                'id' => 'transport-fort-arrival',
                'title' => 'Arrival at the living Fort',
                'description' => 'Cars cannot enter the Fort lanes. Plan the final walk from Fort Parking using the Directions page.',
                'url' => 'direction.php',
            ],
        ];
        foreach ($transportOptions as $transport):
            $tripItem = ['id' => $transport['id'], 'title' => $transport['title'], 'url' => $transport['url']];
        ?>
          <article class="catalogue-transport-card">
            <p class="eyebrow">Transport</p>
            <h3><?= htmlspecialchars($transport['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($transport['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php require __DIR__ . '/include/ui/trip-add-button.php'; ?>
            <a class="text-link" href="<?= htmlspecialchars($transport['url'], ENT_QUOTES, 'UTF-8') ?>">View details →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section catalogue-next-step">
    <div class="site-shell catalogue-next-step-inner">
      <p class="eyebrow">One enquiry, several options</p>
      <h2>Put your shortlist together, then ask us to confirm it.</h2>
      <p>The Trip tray stays in this browser tab. When you are ready, it opens WhatsApp with the selected options in a draft message for you to review.</p>
      <div class="actions">
        <a class="button button-primary" href="#trip-products">Add an option</a>
        <a class="button button-light" href="contact.php">Send a general enquiry instead</a>
      </div>
    </div>
  </section>

  <section class="section site-faq catalogue-faq" aria-labelledby="catalogue-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Good to know</p><h2 id="catalogue-faq-title">Planning your Trip.</h2></div>
        <p>Use the shortlist to start a conversation; the hotel confirms the final details.</p>
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
