<?php
$activePage = 'rooms';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$whatsappUrl = $siteConfig['whatsapp_url'];
$siteUrl = rtrim((string) $siteConfig['site_url'], '/');
$pageTitle = 'Staying inside Jaisalmer Fort | Garh Jaisal Haveli';
$pageDescription = 'A practical guide to staying inside Jaisalmer Fort at Garh Jaisal Haveli: the walk from Fort Parking, rooftop meals, rooms, and desert trips.';
$canonical = $siteUrl . '/book-rooms-in-jaisalmer.php';
$headline = 'Staying inside Jaisalmer Fort';
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Staying inside Jaisalmer Fort'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($siteUrl, '/book-rooms-in-jaisalmer.php', $breadcrumbs);
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/book-rooms-in-jaisalmer.php',
    'og_type' => 'website',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/v2/fort-from-roof.jpg',
    'og_image_alt' => 'View across Jaisalmer’s historic sandstone city from Garh Jaisal Haveli rooftop',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/v2/fort-from-roof.jpg',
    'twitter_image_alt' => 'View across Jaisalmer’s historic sandstone city from Garh Jaisal Haveli rooftop',
    'css' => ['css/guide.css'],
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
<main class="guide-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo">
    <div class="page-hero-media" aria-hidden="true">
      <img
        src="img/v2/fort-from-roof.jpg"
        <?= gj_image_dimension_attributes('img/v2/fort-from-roof.jpg', '100vw') ?>
        alt=""
        fetchpriority="high"
        decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <p class="eyebrow">Stay guide</p>
      <h1><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="guide-lede">Jaisalmer Fort is a living fort. Garh Jaisal Haveli sits inside it, at Fort Kotari Para, so the stay is among the lanes rather than outside the walls.</p>
      <div class="actions guide-hero-actions">
        <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a>
        <a class="button button-light" href="room.php">Compare the rooms</a>
      </div>
      <p class="guide-hero-contact">Questions about room-specific facilities or arrival? <a href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Message us on WhatsApp</a> or <a href="contact.php">send an enquiry</a>.</p>
    </div>
  </section>

  <section class="section booking-path" aria-labelledby="booking-path-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">A simple booking path</p>
          <h2 id="booking-path-title">Choose the room, check the dates, then plan the rest.</h2>
        </div>
        <p>Room rates and availability are shown in the booking system. Tours, dining and arrival requests should be confirmed with the hotel for your travel dates.</p>
      </div>
      <ol class="booking-path-steps">
        <li>
          <span aria-hidden="true">01</span>
          <h3>Compare the rooms</h3>
          <p>Review room photos and listed facilities before choosing the room that fits your stay.</p>
          <a href="room.php">Explore the rooms <span aria-hidden="true">→</span></a>
        </li>
        <li>
          <span aria-hidden="true">02</span>
          <h3>Check your dates</h3>
          <p>Open the booking system to see the current rate and availability for your dates.</p>
          <a href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check availability <span aria-hidden="true">↗</span></a>
        </li>
        <li>
          <span aria-hidden="true">03</span>
          <h3>Plan the extras</h3>
          <p>Ask the hotel to confirm any desert experience, guided route, dining plan or arrival arrangement before relying on it.</p>
          <a href="contact.php?topic=visit-planning">Ask about your plans <span aria-hidden="true">→</span></a>
        </li>
      </ol>
    </div>
  </section>

  <section class="section">
    <div class="site-shell guide-layout">
      <nav class="guide-jump" aria-label="On this page">
        <a href="#arrive">Arrive</a>
        <a href="#stay">Stay</a>
        <a href="#eat">Eat</a>
        <a href="#desert">Desert</a>
        <a href="#book">Book</a>
      </nav>
      <article class="guide-copy">
        <p class="eyebrow" id="arrive">Arrive</p>
        <h2>The last part is on foot.</h2>
        <p>Vehicles cannot enter the fort lanes, so guests continue on foot after Fort Parking. Contact the hotel before travel to confirm current reception hours and ask whether luggage or late-arrival assistance can be arranged; wait for confirmation before relying on it. See the <a href="direction.php">arrival guide</a> or <a href="contact.php?topic=arrival">send an arrival enquiry</a>.</p>
        <p class="eyebrow" id="stay">Stay</p>
        <h2>Rooms inside the haveli.</h2>
        <p>The rooms are Sweet Purple, Rising Sun, Scarlet, Opium, Red Maharani, Green, and BlueSky. Each is different. Sweet Purple has a carved bed and a second bed. Green has a window seat. Rates stay on the booking engine, not on this page. <a href="room.php">See the rooms</a>.</p>
        <p class="eyebrow" id="eat">Eat</p>
        <h2>Meals on the roof.</h2>
        <p>Garh Jaisal’s Kitchen is the rooftop restaurant. The menu includes vegetarian, vegan, and non-vegetarian dishes, and candlelight dinners can be arranged. Hours and prices are confirmed with the hotel. From the terrace you look over the havelis and the lit city. <a href="roof.php">Rooftop dining</a>.</p>
        <p>Planning a honeymoon or couples’ escape? Our <a href="jaisalmer-honeymoon-guide.php">Jaisalmer honeymoon guide</a> helps you plan a Fort stay, rooftop dinner and desert experience while checking details that depend on your dates.</p>
        <p class="eyebrow" id="desert">Desert</p>
        <h2>The Thar, arranged from the fort.</h2>
        <p>Camel safaris, a night under the stars, and Swiss tent stays are arranged from the hotel. October to March is the busy season. Ask the hotel for the current price before you commit. <a href="pack.php">Desert experiences</a>.</p>
        <p class="eyebrow" id="book">Book</p>
        <h2>Reserve the room, then plan the rest.</h2>
        <p>Book the room through the hotel’s booking page, or write from the <a href="contact.php">contact form</a>. Desert trips and rooftop dinners are arranged with the hotel after the room is held.</p>
        <div class="actions">
          <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
          <a class="button button-light" href="room.php">See the rooms</a>
        </div>
      </article>
    </div>
  </section>

<?php
$planningPath = [
    'title' => 'Continue planning your Fort stay',
    'intro' => 'Compare the room options, review the final walk through the Fort, and ask the hotel to confirm arrangements for your dates.',
    'links' => [
        ['label' => 'Compare the rooms', 'href' => 'room.php', 'description' => 'Review room features and check current availability.'],
        ['label' => 'Plan your arrival', 'href' => 'direction.php', 'description' => 'Read the route and confirm arrival details before travelling.'],
        ['label' => 'Ask a question', 'href' => 'contact.php?topic=arrival', 'description' => 'Send an enquiry about arrival or stay planning.'],
    ],
];
require __DIR__ . '/include/ui/planning-path.php';
?>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
