<?php
require_once __DIR__ . '/include/session.php';

$activePage = 'contact';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];

$contactExperience = null;
$requestedExperience = $_GET['experience'] ?? '';
if (is_string($requestedExperience) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $requestedExperience)) {
    $experienceRegistry = require __DIR__ . '/include/experience-data.php';
    $candidateExperience = $experienceRegistry[$requestedExperience] ?? null;
    if (
        is_array($candidateExperience)
        && ($candidateExperience['status'] ?? '') === 'live'
        && ($candidateExperience['duration_type'] ?? 'hub') !== 'hub'
    ) {
        $contactExperience = $candidateExperience;
    }
}

$contactRoom = null;
$requestedRoom = $_GET['room'] ?? '';
if (
    $contactExperience === null
    && is_string($requestedRoom)
    && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $requestedRoom)
) {
    $roomRegistry = require __DIR__ . '/include/rooms-data.php';
    foreach ($roomRegistry as $candidateRoom) {
        $candidateName = (string) ($candidateRoom['name'] ?? '');
        $candidateSlug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($candidateName)), '-');
        if ($candidateSlug !== '' && $candidateSlug === $requestedRoom) {
            $contactRoom = $candidateRoom;
            break;
        }
    }
}

$contactTopics = [
    'arrival' => [
        'label' => 'Arrival arrangements',
        'message' => 'Hello, I would like to confirm arrival arrangements for my stay. My check-in date is: [add date]. My expected arrival time is: [add time]. Please confirm current reception hours, the route from Fort Parking, and whether meet-and-assist or luggage help can be arranged for that date and time. Please confirm the official check-in/check-out times and any instructions for arrivals outside reception hours.',
    ],
    'dining' => [
        'label' => 'Rooftop dining',
        'message' => 'Hello, I would like to enquire about a table at Garh Jaisal\'s Kitchen. Preferred date: [add date]. Preferred time: [add time]. Number of guests: [add number]. Please confirm current service hours, table availability, menu options and prices, and available dietary options for my preferred date.',
    ],
    'visit-planning' => [
        'label' => 'Jaisalmer visit planning',
        'message' => 'Hello, I am planning a visit to Jaisalmer. My travel dates or month are: [add dates]. Planned trip length: [add number of days]. Please share general seasonal considerations and confirm current room, guide and desert options, prices and availability for those dates.',
    ],
    'desert-packing' => [
        'label' => 'Desert safari preparation',
        'message' => 'Hello, I am preparing for a Jaisalmer desert safari. My travel date is: [add date]. Planned experience, if known: [add experience]. Please confirm the current inclusions and transport for the selected experience, plus any packing considerations I should check before departure.',
    ],
    'fort-guide' => [
        'label' => 'Jaisalmer Fort visit',
        'message' => 'Hello, I am planning a visit to Jaisalmer Fort. My travel date is: [add date]. Please confirm current guide availability and languages, the possible Fort walk, applicable entry fees and any access requirements for that date.',
    ],
    'desert-safari' => [
        'label' => 'Jaisalmer desert safari',
        'message' => 'Hello, I would like to enquire about a Jaisalmer desert experience. My travel date is: [add date]. Number of guests: [add number]. Preferred style: sunset return or overnight. Please confirm current options, timings, transport, inclusions, prices and availability for my date.',
    ],
    'two-day-itinerary' => [
        'label' => 'Two-day Jaisalmer itinerary',
        'message' => 'Hello, I am planning a two-day visit to Jaisalmer. My travel dates are: [add dates]. Please advise which current guided, rooftop dining and desert options may fit the trip, and confirm availability, timings, prices and inclusions for my dates.',
    ],
    'three-day-itinerary' => [
        'label' => 'Three-day Jaisalmer itinerary',
        'message' => 'Hello, I am planning a three-day visit to Jaisalmer. My travel dates are: [add dates]. I would like to ask about a Fort stay, a guided heritage route and a desert option. Please confirm current availability, guide language, timings, prices and inclusions for my dates.',
    ],
    'honeymoon' => [
        'label' => 'Couples and honeymoon planning',
        'message' => 'Hello, I am planning a couples trip or honeymoon in Jaisalmer. My travel dates are: [add dates]. I would like to enquire about room options, rooftop dining and desert experiences. Please confirm current availability, prices and arrangements; this enquiry does not reserve anything.',
    ],
    'heritage-havelis' => [
        'label' => 'Havelis and architecture itinerary idea',
        'message' => 'Hello, I would like to ask whether a Jaisalmer havelis and architecture route can be arranged. Please confirm the possible stops, guide availability, timings, transport if needed, applicable entry fees and current price for my travel date.',
    ],
    'jain-temples' => [
        'label' => 'Jain temples and Fort architecture itinerary idea',
        'message' => 'Hello, I would like to ask whether a visit focused on Jaisalmer Fort Jain temples and architecture can be arranged. Please confirm current visitor access and temple rules, timings, guide availability, applicable fees and current price for my travel date.',
    ],
    'lake-cenotaph-photo' => [
        'label' => 'Gadisar Lake and cenotaph photography itinerary idea',
        'message' => 'Hello, I would like to ask whether a photography-friendly sightseeing route involving Gadisar Lake, Bada Bagh or Vyas Chhatri can be arranged. Please advise which stops are feasible, the route, access times, transport, applicable fees and current price for my travel date.',
    ],
    'kuldhara-landscape' => [
        'label' => 'Kuldhara and desert landscape itinerary idea',
        'message' => 'Hello, I would like to ask whether a Kuldhara heritage and nearby desert landscape outing can be arranged. Please confirm current visitor access and rules, possible route, transport, timings, applicable fees and current price for my travel date.',
    ],
    'natural-history' => [
        'label' => 'Akal Wood Fossil Park or desert ecology itinerary idea',
        'message' => 'Hello, I would like to ask whether a natural-history outing to Akal Wood Fossil Park or a suitable Desert National Park area can be arranged. Please confirm the feasible option, access requirements, responsible viewing arrangements, route, transport, timings, applicable fees and current price for my travel date.',
    ],
    'longewala-history' => [
        'label' => 'Longewala War Memorial itinerary idea',
        'message' => 'Hello, I would like to ask whether a day excursion to Longewala War Memorial can be arranged. Please confirm current visitor requirements, route feasibility, total travel time, transport, operating days, applicable fees and current price for my travel date.',
    ],
];
$contactTopic = null;
$contactTopicKey = null;
$requestedTopic = $_GET['topic'] ?? '';
if (
    $contactExperience === null
    && $contactRoom === null
    && is_string($requestedTopic)
    && isset($contactTopics[$requestedTopic])
) {
    $contactTopic = $contactTopics[$requestedTopic];
    $contactTopicKey = $requestedTopic;
}

$prefilledMessage = '';
if ($contactExperience !== null) {
    $prefilledMessage = 'Hello, I would like to enquire about ' . $contactExperience['h1']
        . '. Please confirm the current price, availability, timings, arrangements and exact inclusions for my travel date.';
} elseif ($contactRoom !== null) {
    $prefilledMessage = 'Hello, I would like to enquire about ' . $contactRoom['name']
        . '. Please confirm the current availability, rate and room features for my travel dates.';
} elseif ($contactTopic !== null) {
    $prefilledMessage = $contactTopic['message'];
}

if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}
$contactCsrf = $_SESSION['contact_csrf'];

$errorKey = $_GET['error'] ?? '';
if (!is_string($errorKey)) {
    $errorKey = '';
}

$errorMessages = [
    'captcha' => 'The security check did not pass. Please complete it and try again.',
    'invalid' => 'Please check your name, email, and message, then try again.',
    'send' => 'We could not send your message. Please call or email the hotel instead.',
    'config' => 'The contact form is temporarily unavailable. Please call or email the hotel instead.',
    'storage' => 'We could not securely record this enquiry. Please call or message the hotel instead.';
];
$errorMessage = $errorMessages[$errorKey] ?? '';

$pageTitle = 'Contact Hotel Garh Jaisal Haveli in Jaisalmer Fort';
$pageDescription = 'Contact Garh Jaisal Haveli about rooms, arrival, dining and desert or city experiences. Call, email, or send an enquiry to plan your stay inside Jaisalmer Fort.';
$canonical = 'https://www.hotelgarhjaisal.com/contact.php';
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/contact.php';
require_once __DIR__ . '/include/schema.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Contact'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($schemaSiteUrl, '/contact.php', $breadcrumbs);
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/contact.php',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/v2/sitting-room.jpg',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/v2/sitting-room.jpg',
    'css' => ['css/contact.css'],
    'schema' => [
        [
            '@type' => 'ContactPage',
            '@id' => $schemaPageUrl . '#webpage',
            'name' => 'Contact Hotel Garh Jaisal Haveli',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $schemaPageUrl . '#breadcrumb'],
            'url' => $schemaPageUrl,
            'mainEntity' => [
                '@type' => 'Hotel',
                'name' => 'Hotel Garh Jaisal Haveli',
                'telephone' => ['+91 77270 47567', '+91 2992 253836'],
                'email' => ['mukeshkila@yahoo.com', 'info@hotelgarhjaisal.com'],
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => 'Hotel Garh Jaisal Haveli, Fort Kotari Para',
                    'addressLocality' => 'Jaisalmer',
                    'addressRegion' => 'Rajasthan',
                    'postalCode' => '345001',
                    'addressCountry' => 'IN',
                ],
            ],
        ],
        $breadcrumbSchema,
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="contact-page" id="main-content" tabindex="-1">
  <section class="page-hero contact-hero">
    <div class="site-shell page-hero-inner">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <p class="eyebrow">Plan your stay</p>
      <h1>Contact Garh Jaisal Haveli in Jaisalmer Fort.</h1>
      <p class="contact-lead">Write or call Hotel Garh Jaisal Haveli. Ask about rooms, arrival, rooftop dining, or desert and city experiences arranged by the hotel.</p>
    </div>
  </section>

  <section class="section contact-content">
    <div class="site-shell">
      <div class="actions contact-booking-actions">
        <a class="button button-primary contact-booking-button" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a>
        <a class="button button-secondary contact-arrival-link" href="direction.php">Plan your arrival</a>
        <a class="button button-secondary contact-whatsapp-button" href="<?= htmlspecialchars($siteConfig['whatsapp_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Message on WhatsApp</a>
      </div>

      <div class="contact-layout">
        <div class="contact-facts">
          <h2>Phone</h2>
          <p>
            <a href="tel:+917727047567">+91 77270 47567</a><br>
            <a href="tel:+912992253836">+91 2992 253836</a>
          </p>
          <h2>Email</h2>
          <p>
            <a href="mailto:mukeshkila@yahoo.com">mukeshkila@yahoo.com</a><br>
            <a href="mailto:info@hotelgarhjaisal.com">info@hotelgarhjaisal.com</a>
          </p>
          <h2>Address</h2>
          <p>Hotel Garh Jaisal Haveli, Fort Kotari Para, Jaisalmer, Rajasthan 345001</p>
        </div>

        <form class="contact-form" action="mail.php" method="post">
          <h2>Send an enquiry</h2>
          <?php if ($contactExperience !== null): ?>
            <p class="contact-experience-context">Experience enquiry: <strong><?= htmlspecialchars($contactExperience['h1'], ENT_QUOTES, 'UTF-8') ?></strong>. Review the draft message below and add your travel date or any specific questions.</p>
          <?php elseif ($contactRoom !== null): ?>
            <p class="contact-room-context">Room enquiry: <strong><?= htmlspecialchars($contactRoom['name'], ENT_QUOTES, 'UTF-8') ?></strong>. Review the draft message below and add your travel dates or any specific questions.</p>
          <?php elseif ($contactTopic !== null): ?>
            <?php if ($contactTopicKey === 'arrival'): ?>
              <p class="contact-topic-context">Enquiry topic: <strong><?= htmlspecialchars($contactTopic['label'], ENT_QUOTES, 'UTF-8') ?></strong>. Add your check-in date and expected arrival time to the draft message so the hotel can advise what may be arranged. Do not rely on meet-and-assist or luggage assistance until the hotel confirms it.</p>
            <?php elseif ($contactTopicKey === 'dining'): ?>
              <p class="contact-topic-context">Enquiry topic: <strong><?= htmlspecialchars($contactTopic['label'], ENT_QUOTES, 'UTF-8') ?></strong>. Add your preferred date, time and number of guests. This enquiry does not confirm a table; wait for the hotel to confirm service hours and availability.</p>
            <?php else: ?>
              <p class="contact-topic-context">Enquiry topic: <strong><?= htmlspecialchars($contactTopic['label'], ENT_QUOTES, 'UTF-8') ?></strong>. Review the draft message below and add your date or any specific questions.</p>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($errorMessage !== ''): ?>
            <p class="contact-error" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="contact-error-help">You can also <a href="tel:<?= htmlspecialchars($siteConfig['hotel_phone'], ENT_QUOTES, 'UTF-8') ?>">call the hotel</a> or <a href="<?= htmlspecialchars($siteConfig['whatsapp_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">message us on WhatsApp</a>.</p>
          <?php endif; ?>

          <div class="contact-field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="80" autocomplete="name" required>
          </div>
          <div class="contact-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="120" autocomplete="email" required>
          </div>
          <div class="contact-field">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" maxlength="20" autocomplete="tel" inputmode="tel">
          </div>
          <div class="contact-field">
            <label for="message">Message</label>
            <textarea id="message" name="message" maxlength="2000" rows="6" required><?= htmlspecialchars($prefilledMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <div class="contact-field contact-consent">
            <label for="consent"><input type="checkbox" id="consent" name="consent" value="1" required> I agree that the hotel may use these details to respond to my enquiry.</label>
          </div>
          <div class="hp-field" aria-hidden="true">
            <label for="website">Leave this field empty</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($contactCsrf, ENT_QUOTES, 'UTF-8') ?>">
          <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($siteConfig['recaptcha_site_key'], ENT_QUOTES, 'UTF-8') ?>"></div>
          <button class="button button-primary" type="submit">Send message</button>
        </form>
      </div>
    </div>
  </section>

  <section class="contact-map" aria-label="Map">
    <iframe
      title="Map showing Hotel Garh Jaisal Haveli in Jaisalmer"
      src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14230.800346465852!2d70.9144008!3d26.9130118!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3947bc2f419b709f%3A0x31fe2bcd223dfd99!2sHotel%20Garh%20Jaisal%20Haveli%2C%20Jaisalmer!5e0!3m2!1sen!2sin!4v1694691227129!5m2!1sen!2sin"
      width="100%"
      height="360"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      allowfullscreen></iframe>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="js/garh-v2.js"></script>
</body>
</html>
