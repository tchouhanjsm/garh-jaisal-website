<?php
$activePage = 'rooms';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$pageTitle = 'Staying inside Jaisalmer Fort | Garh Jaisal Haveli';
$pageDescription = 'A practical guide to staying inside Jaisalmer Fort at Garh Jaisal Haveli: the walk from Fort Parking, rooftop meals, rooms, and desert trips.';
$canonical = 'https://www.hotelgarhjaisal.com/book-rooms-in-jaisalmer.php';
$headline = 'Staying inside Jaisalmer Fort';
?>
<!doctype html>
<html lang="en">
<?php
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/book-rooms-in-jaisalmer.php',
    'og_type' => 'article',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/v2/fort-from-roof.jpg',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/v2/fort-from-roof.jpg',
    'css' => ['css/guide.css'],
    'schema' => [
        [
            '@type' => 'Article',
            'headline' => 'Staying inside Jaisalmer Fort',
            'datePublished' => '2026-10-02',
            'dateModified' => '2026-10-15',
            'image' => 'https://www.hotelgarhjaisal.com/img/v2/fort-from-roof.jpg',
            'author' => ['@type' => 'Organization', 'name' => 'Hotel Garh Jaisal Haveli', 'url' => 'https://www.hotelgarhjaisal.com/'],
            'publisher' => ['@id' => 'https://www.hotelgarhjaisal.com/#hotel'],
            'mainEntityOfPage' => 'https://www.hotelgarhjaisal.com/book-rooms-in-jaisalmer.php',
            'description' => 'A practical guide to staying inside Jaisalmer Fort at Garh Jaisal Haveli.',
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="guide-page" id="main-content" tabindex="-1">
  <section class="page-hero">
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Journal</p>
      <h1><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="guide-lede">Jaisalmer Fort is a living fort. Garh Jaisal Haveli sits inside it, at Fort Kotari Para, so the stay is among the lanes rather than outside the walls.</p>
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
      <article>
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
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
