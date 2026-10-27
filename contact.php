<?php
require_once __DIR__ . '/include/session.php';

$activePage = 'contact';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$hotelPhone = (string) ($siteConfig['hotel_phone'] ?? '');
$hotelPhoneDisplay = (string) ($siteConfig['hotel_phone_display'] ?? $hotelPhone);
$hotelPhoneAlt = (string) ($siteConfig['hotel_phone_alt'] ?? '');
$hotelPhoneAltDisplay = (string) ($siteConfig['hotel_phone_alt_display'] ?? $hotelPhoneAlt);
$hotelEmail = (string) ($siteConfig['hotel_email'] ?? '');
$hotelEmailAlt = (string) ($siteConfig['hotel_email_alt'] ?? '');

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

$contactRooms = [];
$requestedRooms = $_GET['rooms'] ?? [];
if ($contactExperience === null && is_array($requestedRooms)) {
    $requestedRoomSlugs = array_values($requestedRooms);
    $validRoomList = count($requestedRoomSlugs) >= 2 && count($requestedRoomSlugs) <= 3;
    foreach ($requestedRoomSlugs as $requestedRoomSlug) {
        if (!is_string($requestedRoomSlug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $requestedRoomSlug)) {
            $validRoomList = false;
            break;
        }
    }
    if ($validRoomList && count(array_unique($requestedRoomSlugs)) === count($requestedRoomSlugs)) {
        $roomRegistry = require __DIR__ . '/include/rooms-data.php';
        $roomIndex = [];
        foreach ($roomRegistry as $candidateRoom) {
            if (!is_array($candidateRoom) || !is_string($candidateRoom['name'] ?? null)) {
                continue;
            }
            $candidateSlug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($candidateRoom['name'])), '-');
            if ($candidateSlug !== '') {
                $roomIndex[$candidateSlug] = ['slug' => $candidateSlug, 'name' => trim($candidateRoom['name'])];
            }
        }
        foreach ($requestedRoomSlugs as $requestedRoomSlug) {
            if (!isset($roomIndex[$requestedRoomSlug])) {
                $contactRooms = [];
                break;
            }
            $contactRooms[] = $roomIndex[$requestedRoomSlug];
        }
    }
}
$contactRoomSlugs = array_column($contactRooms, 'slug');
$contactRoomNames = array_column($contactRooms, 'name');

$contactTrip = [];
$contactTripIds = [];
$requestedTrip = $_GET['trip'] ?? [];
if ($contactExperience === null && $contactRooms === [] && is_array($requestedTrip)) {
    $requestedTripIds = array_values($requestedTrip);
    $validTripList = count($requestedTripIds) >= 1 && count($requestedTripIds) <= 12;
    foreach ($requestedTripIds as $requestedTripId) {
        if (!is_string($requestedTripId) || !preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/D', $requestedTripId)) {
            $validTripList = false;
            break;
        }
    }
    if ($validTripList && count(array_unique($requestedTripIds)) === count($requestedTripIds)) {
        require_once __DIR__ . '/include/enquiry-context.php';
        $resolvedTrip = gj_resolve_enquiry_context('trip', implode(',', $requestedTripIds));
        if (($resolvedTrip['type'] ?? '') === 'trip') {
            $contactTrip = $resolvedTrip['items'];
            $contactTripIds = array_column($contactTrip, 'slug');
        }
    }
}

$contactRoom = null;
$requestedRoom = $_GET['room'] ?? '';
if (
    $contactExperience === null
    && $contactRooms === []
    && $contactTrip === []
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

$contactTopics = require __DIR__ . '/include/contact-topics.php';
$contactTopic = null;
$contactTopicKey = null;
$requestedTopic = $_GET['topic'] ?? '';
if (
    $contactExperience === null
    && $contactRooms === []
    && $contactTrip === []
    && $contactRoom === null
    && is_string($requestedTopic)
    && isset($contactTopics[$requestedTopic])
) {
    $contactTopic = $contactTopics[$requestedTopic];
    $contactTopicKey = $requestedTopic;
}

$contactContextType = 'general';
$contactContextSlug = '';
if ($contactExperience !== null) {
    $contactContextType = 'experience';
    $contactContextSlug = (string) $contactExperience['slug'];
} elseif ($contactRooms !== []) {
    $contactContextType = 'rooms';
    $contactContextSlug = implode(',', $contactRoomSlugs);
} elseif ($contactTrip !== []) {
    $contactContextType = 'trip';
    $contactContextSlug = implode(',', $contactTripIds);
} elseif ($contactRoom !== null) {
    $contactContextType = 'room';
    $contactContextSlug = (string) $requestedRoom;
} elseif ($contactTopic !== null && is_string($contactTopicKey)) {
    $contactContextType = 'topic';
    $contactContextSlug = $contactTopicKey;
}

$prefilledMessage = '';
if ($contactExperience !== null) {
    $prefilledMessage = 'Hello, I would like to enquire about ' . $contactExperience['h1']
        . '. Please confirm the current price, availability, timings, arrangements and exact inclusions for my travel date.';
} elseif ($contactRooms !== []) {
    $prefilledMessage = 'Hello, I would like to enquire about these rooms: ' . implode(', ', $contactRoomNames)
        . '. Please confirm the current availability, rate and room-specific facilities for my travel dates.';
} elseif ($contactTrip !== []) {
    $contactTripNames = array_column($contactTrip, 'label');
    $prefilledMessage = 'Hello, I would like to discuss these options for my Jaisalmer trip: '
        . implode(', ', $contactTripNames)
        . '. Please confirm current rates, availability and arrangements for my travel dates. I understand this is an enquiry, not a confirmed booking.';
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

$contextTokenIssuedAt = time();
$contactContextTokens = $_SESSION['contact_context_tokens'] ?? [];
if (!is_array($contactContextTokens)) {
    $contactContextTokens = [];
}
foreach ($contactContextTokens as $storedToken => $storedContext) {
    if (
        !is_array($storedContext)
        || !isset($storedContext['issued_at'])
        || !is_numeric($storedContext['issued_at'])
        || (int) $storedContext['issued_at'] < $contextTokenIssuedAt - 7200
    ) {
        unset($contactContextTokens[$storedToken]);
    }
}
while (count($contactContextTokens) >= 10) {
    $oldestToken = null;
    $oldestIssuedAt = PHP_INT_MAX;
    foreach ($contactContextTokens as $storedToken => $storedContext) {
        $issuedAt = is_array($storedContext) && isset($storedContext['issued_at']) && is_numeric($storedContext['issued_at'])
            ? (int) $storedContext['issued_at']
            : 0;
        if ($issuedAt < $oldestIssuedAt) {
            $oldestIssuedAt = $issuedAt;
            $oldestToken = $storedToken;
        }
    }
    if ($oldestToken === null) {
        break;
    }
    unset($contactContextTokens[$oldestToken]);
}
$contactContextToken = bin2hex(random_bytes(16));
$contactContextTokens[$contactContextToken] = [
    'type' => $contactContextType,
    'slug' => $contactContextSlug,
    'issued_at' => $contextTokenIssuedAt,
];
$_SESSION['contact_context_tokens'] = $contactContextTokens;

$errorKey = $_GET['error'] ?? '';
if (!is_string($errorKey)) {
    $errorKey = '';
}

$errorMessages = [
    'captcha' => 'The security check did not pass. Please complete it and try again.',
    'invalid' => 'Please check your name, email, and message, then try again.',
    'send' => 'We could not send your message. Please call or email the hotel instead.',
    'config' => 'The contact form is temporarily unavailable. Please call or email the hotel instead.',
    'storage' => 'We could not securely record this enquiry. Please call or message the hotel instead.',
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
                'telephone' => array_values(array_filter(
                    [$hotelPhoneDisplay, $hotelPhoneAltDisplay],
                    static function ($value) {
                        return is_string($value) && trim($value) !== '';
                    }
                )),
                'email' => array_values(array_filter(
                    [$hotelEmailAlt, $hotelEmail],
                    static function ($value) {
                        return is_string($value) && trim($value) !== '';
                    }
                )),
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
            <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a><br>
            <a href="tel:<?= htmlspecialchars($hotelPhoneAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneAltDisplay, ENT_QUOTES, 'UTF-8') ?></a>
          </p>
          <h2>Email</h2>
          <p>
            <?php if ($hotelEmailAlt !== ''): ?><a href="mailto:<?= htmlspecialchars($hotelEmailAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelEmailAlt, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
            <?php if ($hotelEmailAlt !== '' && $hotelEmail !== ''): ?><br><?php endif; ?>
            <?php if ($hotelEmail !== ''): ?><a href="mailto:<?= htmlspecialchars($hotelEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelEmail, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
          </p>
          <h2>Address</h2>
          <p>Hotel Garh Jaisal Haveli, Fort Kotari Para, Jaisalmer, Rajasthan 345001</p>
        </div>

        <form class="contact-form" action="mail.php" method="post">
          <h2>Send an enquiry</h2>
          <?php if ($contactExperience !== null): ?>
            <p class="contact-experience-context" data-experience-slug="<?= htmlspecialchars((string) $contactExperience['slug'], ENT_QUOTES, 'UTF-8') ?>">Experience enquiry: <strong><?= htmlspecialchars($contactExperience['h1'], ENT_QUOTES, 'UTF-8') ?></strong>. Review the draft message below and add your travel date or any specific questions.</p>
          <?php elseif ($contactRooms !== []): ?>
            <p class="contact-rooms-context" data-room-slugs="<?= htmlspecialchars(implode(',', $contactRoomSlugs), ENT_QUOTES, 'UTF-8') ?>">Room shortlist enquiry: <strong><?= htmlspecialchars(implode(', ', $contactRoomNames), ENT_QUOTES, 'UTF-8') ?></strong>. Review the draft message below and add your travel dates or any specific questions. The hotel will confirm current availability and rates.</p>
          <?php elseif ($contactTrip !== []): ?>
            <p class="contact-trip-context" data-trip-ids="<?= htmlspecialchars(implode(',', $contactTripIds), ENT_QUOTES, 'UTF-8') ?>">Trip shortlist: <strong><?= htmlspecialchars(implode(', ', array_column($contactTrip, 'label')), ENT_QUOTES, 'UTF-8') ?></strong>. Review the selected options in the draft message and add your travel dates or specific questions. The hotel will confirm current rates and arrangements; this form does not reserve anything.</p>
          <?php elseif ($contactRoom !== null): ?>
            <p class="contact-room-context" data-room-slug="<?= htmlspecialchars((string) $requestedRoom, ENT_QUOTES, 'UTF-8') ?>">Room enquiry: <strong><?= htmlspecialchars($contactRoom['name'], ENT_QUOTES, 'UTF-8') ?></strong>. Review the draft message below and add your travel dates or any specific questions.</p>
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
          <input type="hidden" name="enquiry_context_token" value="<?= htmlspecialchars($contactContextToken, ENT_QUOTES, 'UTF-8') ?>">
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
