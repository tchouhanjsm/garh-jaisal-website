<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
require_once __DIR__ . '/include/schema.php';
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$hotelPhoneSchema = gj_schema_hotel($siteUrl, $siteConfig)['telephone'];
$hotelPhone = $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $siteConfig['hotel_phone_display'];
$whatsappUrl = $siteConfig['whatsapp_url'];
$hotelEmail = $siteConfig['hotel_email'];
$showReservationForm = true; // Keep enquiry capture visible independently of email availability.

$restaurant = [
    'name' => "Garh Jaisal's Kitchen",
    'tagline' => 'Rajasthani food on the rooftop.',
    'address' => 'Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001',
];
$googleMapsUrl = 'https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8';
$googleMapsEmbedUrl = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3557.6918725708306!2d70.9117074793457!3d26.913272400000007!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3947bd9e89a679cf%3A0xd196c3f044898c10!2sGarh%20Jaisal's%20Kitchen!5e0!3m2!1sen!2sin!4v1791990366078!5m2!1sen!2sin";
$instagramUrl = 'https://www.instagram.com/garhjaisal/';
require_once __DIR__ . '/include/lead-storage.php';

/* Menu/prices are from the supplied restaurant design. Verify the live menu before staging. */
$menuConfig = require __DIR__ . '/include/menu-data.php';
$menuConfig = is_array($menuConfig) ? $menuConfig : [];
$menu = isset($menuConfig['items']) && is_array($menuConfig['items']) ? $menuConfig['items'] : [];
$menuCategories = array_keys($menu);
$menuOwnerConfirmed = !empty($menuConfig['owner_confirmed']);
$restaurantGallerySections = require __DIR__ . '/include/gallery-data.php';
$restaurantPhotoSlides = $restaurantGallerySections['restaurant']['images'] ?? [];


function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $value): string {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));
    return $slug ?: 'section';
}

$waLink = $whatsappUrl . '?text=' . rawurlencode(
    'Hello, I would like to enquire about a table at ' . $restaurant['name']
    . '. Preferred date: [add date]. Preferred time: [add time]. Number of guests: [add number].'
    . ' Please confirm current service hours, table availability, menu options and prices,'
    . ' and available dietary options for my preferred date.'
);

$errors = [];
$sent = false;
$reservationReference = '';
$reservationEmailNotified = false;
$old = [
    'name' => '',
    'email' => '',
    'whatsapp' => '',
    'date' => '',
    'time' => '',
    'guests' => '2',
    'note' => '',
    'consent' => '',
];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

if (empty($_SESSION['gjk_csrf'])) {
    $_SESSION['gjk_csrf'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $_) {
        $old[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if (!empty($_POST['website'])) {
        http_response_code(400);
        exit;
    }

    if (!hash_equals($_SESSION['gjk_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors['form'] = 'Your session expired. Please reload the page and try again.';
    }

    $phone = preg_replace('/[^0-9+]/', '', $old['whatsapp']);
    $today = date('Y-m-d');

    $nameLength = function_exists('mb_strlen') ? mb_strlen($old['name'], 'UTF-8') : strlen($old['name']);
    if ($old['name'] === '' || $nameLength > 80) {
        $errors['name'] = 'Enter your name.';
    }
    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors['whatsapp'] = 'Enter a valid WhatsApp number.';
    }
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($old['date'] === '' || $old['date'] < $today) {
        $errors['date'] = 'Choose today or a future date.';
    }
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):(?:00|30)$/D', $old['time'])) {
        $errors['time'] = 'Choose a preferred time on the hour or half-hour.';
    }
    if ((int) $old['guests'] < 1 || (int) $old['guests'] > 20) {
        $errors['guests'] = 'Guests must be between 1 and 20.';
    }
    if (strlen($old['note']) > 500) {
        $errors['note'] = 'Keep the special note to 500 characters or fewer.';
    }
    if ($old['consent'] !== '1') {
        $errors['consent'] = 'Please agree that the hotel may use these details to respond to your request.';
    }

    // Enforce only schedule values explicitly supplied by the owner in private config.
    if ($old['date'] !== '' && $old['date'] >= $today) {
        $requestedWeekday = (int) date('w', strtotime($old['date']));
        $requestedWeekdayName = strtolower(date('l', strtotime($old['date'])));
        $closedDays = $siteConfig['restaurant_closed_days'] ?? [];
        if (!is_array($closedDays)) {
            $closedDays = [];
        }
        $closedDaysNormalized = array_map(static function ($day) {
            if (is_string($day)) {
                $day = strtolower(trim($day));
                return ctype_digit($day) ? (int) $day : $day;
            }
            return $day;
        }, $closedDays);
        if (in_array($requestedWeekday, $closedDaysNormalized, true)
            || in_array($requestedWeekdayName, $closedDaysNormalized, true)) {
            $errors['date'] = 'The restaurant is marked closed on that day. Choose another date or contact the hotel.';
        }
        $sameDayCutoff = max(0, (int) ($siteConfig['restaurant_same_day_cutoff_minutes'] ?? 0));
        if ($old['date'] === $today && $sameDayCutoff > 0
            && preg_match('/^(?:[01][0-9]|2[0-3]):(?:00|30)$/D', $old['time'])
            && strtotime($old['date'] . ' ' . $old['time']) < time() + ($sameDayCutoff * 60)) {
            $errors['time'] = 'That time is inside the same-day request cut-off. Please call or message the hotel.';
        }
    }

    if (!$errors) {
        $leadResult = gj_store_lead(
            'restaurant_reservation',
            [
                'name' => $old['name'],
                'phone' => $phone,
                'email' => $old['email'],
                'requested_date' => $old['date'],
                'requested_time' => $old['time'],
                'guests' => (int) $old['guests'],
                'note' => $old['note'],
                'consent' => true,
            ],
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            $phone,
            ['max_per_ip' => 8, 'max_per_phone' => 3]
        );

        if (empty($leadResult['ok'])) {
            $errors['form'] = ($leadResult['error'] ?? '') === 'rate_limited'
                ? 'Too many requests have been received from this connection or number today. Please call or message the hotel.'
                : 'We could not securely record the request. Please call or message the hotel so your enquiry is not lost.';
        } else {
            $reservationReference = $leadResult['reference'];
            $subject = 'Restaurant table enquiry [' . $reservationReference . ']';
            $body = "A new restaurant table enquiry was recorded. This is not a confirmed booking.\n\n"
                . "Reference: {$reservationReference}\n"
                . "Name: {$old['name']}\n"
                . "WhatsApp: {$phone}\n"
                . "Email: " . ($old['email'] ?: '-') . "\n"
                . "Preferred date: {$old['date']}\n"
                . "Preferred time: {$old['time']}\n"
                . "Guests: {$old['guests']}\n"
                . "Special note: " . ($old['note'] !== '' ? $old['note'] : '-') . "\n\n"
                . "Please confirm current service hours and availability directly with the guest.";

            $recipients = array_values(array_filter((array) ($siteConfig['inquiry_recipients'] ?? []), static function ($address) {
                return is_string($address) && !preg_match('/[\r\n]/', $address)
                    && filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
            }));
            if ($recipients === [] && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) !== false) {
                $recipients = [$hotelEmail];
            }
            $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            if (filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) !== false && !preg_match('/[\r\n]/', $hotelEmail)) {
                $headers .= 'From: ' . $hotelEmail . "\r\n";
            }
            if ($old['email'] !== '' && filter_var($old['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $headers .= 'Reply-To: ' . str_replace(["\r", "\n"], '', $old['email']) . "\r\n";
            }

            foreach ($recipients as $recipient) {
                if (@mail($recipient, $subject, $body, $headers)) {
                    $reservationEmailNotified = true;
                }
            }
            gj_record_lead_event($reservationReference, $reservationEmailNotified ? 'owner_notification_sent' : 'owner_notification_failed');

            if ($reservationEmailNotified && $old['email'] !== '' && filter_var($old['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $guestSubject = 'We received your table enquiry - ' . $reservationReference;
                $guestBody = "Thank you for contacting Garh Jaisal's Kitchen.\n\n"
                    . "Your enquiry reference is {$reservationReference}. This is not a confirmed booking. "
                    . "The hotel must confirm current service hours and availability before the table is reserved.\n\n"
                    . "For urgent confirmation, call {$hotelPhoneDisplay} or contact the hotel on WhatsApp.";
                $guestHeaders = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                if (filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) !== false) {
                    $guestHeaders .= 'From: ' . $hotelEmail . "\r\n";
                }
                $guestSent = @mail($old['email'], $guestSubject, $guestBody, $guestHeaders);
                gj_record_lead_event($reservationReference, $guestSent ? 'guest_acknowledgement_sent' : 'guest_acknowledgement_failed');
            }

            $sent = true;
            $_SESSION['gjk_csrf'] = bin2hex(random_bytes(16));
        }
    }
}

$pageUrl = $siteUrl . '/restaurant.php';
$heroImage = $siteUrl . '/img/rest2.jpg';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Hotel',
            '@id' => $siteUrl . '/#hotel',
            'name' => 'Hotel Garh Jaisal Haveli',
            'url' => $siteUrl . '/',
            'telephone' => $hotelPhoneSchema,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Fort Kotari Para, Inside Jaisalmer Fort',
                'addressLocality' => 'Jaisalmer',
                'addressRegion' => 'Rajasthan',
                'postalCode' => '345001',
                'addressCountry' => 'IN',
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '/#website',
            'url' => $siteUrl . '/',
            'name' => 'Garh Jaisal Haveli',
            'publisher' => ['@id' => $siteUrl . '/#hotel'],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $pageUrl . '#webpage',
            'url' => $pageUrl,
            'name' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $pageUrl . '#restaurant'],
            'primaryImageOfPage' => ['@id' => $pageUrl . '#primaryimage'],
        ],
        [
            '@type' => 'ImageObject',
            '@id' => $pageUrl . '#primaryimage',
            'url' => $heroImage,
        ],
        [
            '@type' => 'Restaurant',
            '@id' => $pageUrl . '#restaurant',
            'name' => $restaurant['name'],
            'url' => $pageUrl,
            'image' => [$heroImage],
            'servesCuisine' => 'Rajasthani',
            'telephone' => $hotelPhone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Fort Kotari Para, Inside Jaisalmer Fort',
                'addressLocality' => 'Jaisalmer',
                'addressRegion' => 'Rajasthan',
                'postalCode' => '345001',
                'addressCountry' => 'IN',
            ],
            'parentOrganization' => ['@id' => $siteUrl . '/#hotel'],
            'acceptsReservations' => true,
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $pageUrl . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $siteUrl . '/',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $restaurant['name'],
                    'item' => $pageUrl,
                ],
            ],
        ],
    ],
];
?>
<!doctype html>
<html lang="en">
<?php
$faqItems = [
    ['question' => "Where is Garh Jaisal’s Kitchen?", 'answer' => "Garh Jaisal’s Kitchen is located at Fort Kotari Para, inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001."],
    ['question' => "Can menu items and prices change?", 'answer' => "Yes. Menu items and listed prices may change; confirm the current menu and prices with the hotel before visiting."],
    ['question' => "How do I request a table?", 'answer' => "Use the table enquiry form when online requests are available, or contact the hotel by phone or WhatsApp. A request is not confirmed until the hotel replies. Availability can vary, especially for larger groups."],
    ['question' => "Can I ask about dietary requirements?", 'answer' => "Ask the hotel about current vegetarian, vegan, Jain and allergy options before confirming your meal."],
];
$page = [
    'title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'description' => "Rooftop dining at " . $restaurant['name'] . " inside Jaisalmer Fort. Ask the hotel about the current menu and request a table; confirm hours, dishes and prices before visiting.",
    'path' => '/restaurant.php',
    'og_type' => 'website',
    'og_title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'og_description' => 'Rajasthani food on the rooftop at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'og_image' => '/img/rest2.jpg',
    'og_image_alt' => 'Dining space at Garh Jaisal Haveli',
    'twitter_title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'twitter_description' => 'Rajasthani food on the rooftop at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'twitter_image' => '/img/rest2.jpg',
    'css' => ['css/restaurant.css'],
    'schema' => array_values(array_filter(
        $schema['@graph'],
        static function ($node) {
            return !in_array($node['@type'] ?? '', ['Hotel', 'WebSite'], true);
        }
    )),
];
$page['schema'][] = gj_schema_faq($faqItems, $pageUrl);
require __DIR__ . '/include/page-head.php';
?>
<body class="restaurant-page">
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" tabindex="-1">
  <section class="restaurant-hero">
    <div class="site-shell restaurant-hero-grid">
      <div class="restaurant-hero-copy">
        <p class="eyebrow">Garh Jaisal's Kitchen</p>
        <h1>Rooftop dining in the heart of Jaisalmer Fort.</h1>
        <p>Settle in above the sandstone rooftops at Garh Jaisal's Kitchen. Explore the menu, then ask us to confirm current dishes, dietary options, service hours and a table for your date.</p>
        <div class="actions">
          <a class="button button-primary" href="#reserve">Enquire about a table</a>
          <a class="button button-light" href="<?= e($waLink) ?>" target="_blank" rel="noopener">WhatsApp</a>
          <?php
          $tripItem = ['id' => 'rooftop-dining', 'title' => 'Rooftop dining', 'url' => 'roof.php', 'group' => 'dining'];
          $tripButtonClass = 'button button-light';
          require __DIR__ . '/include/ui/trip-add-button.php';
          ?>
        </div>
        <p class="restaurant-rooftop-link">Planning a meal or candlelight dinner? <a href="roof.php">Explore the rooftop dining space</a>.</p>
      </div>

      <div class="restaurant-hero-media">
        <img
          src="img/rest2.jpg"
          <?= gj_image_dimension_attributes('img/rest2.jpg', '(max-width: 760px) 100vw, 45vw') ?>
          alt="Rooftop dining area at Garh Jaisal's Kitchen in Jaisalmer"
          fetchpriority="high"
        >
      </div>
    </div>

    <div class="site-shell restaurant-hours" aria-label="Dining information">
      <div><span>Service hours</span><strong>Confirm current hours with the hotel</strong></div>
      <div><span>Menu</span><strong>Ask about current dishes and prices</strong></div>
      <div><span>Table availability</span><strong>Confirm for your preferred date and time</strong></div>
    </div>
  </section>

  <section class="section restaurant-intro">
    <div class="site-shell restaurant-intro-grid">
      <div>
        <p class="eyebrow">The kitchen</p>
        <h2>Rajasthani favourites, served above the Golden City.</h2>
      </div>
      <p>Garh Jaisal's Kitchen is the rooftop restaurant at Garh Jaisal Haveli, inside Jaisalmer Fort. Come for the rooftop setting and ask about the food available when you visit. Current dishes, preparation details, dietary options, service hours and prices are confirmed directly with the hotel for your date.</p>
    </div>

    <div class="site-shell restaurant-gallery-social">
      <div class="restaurant-gallery" aria-label="Restaurant and rooftop photo gallery">
        <?php
        $photoCarousel = [
            'label' => "Garh Jaisal's Kitchen restaurant and rooftop",
            'variant' => 'restaurant',
            'slides' => $restaurantPhotoSlides,
        ];
        require __DIR__ . '/include/ui/photo-carousel.php';
        unset($photoCarousel);
        ?>
      </div>
      <aside class="restaurant-instagram-panel" aria-labelledby="restaurant-instagram-title">
        <p class="eyebrow">Follow along</p>
        <p class="restaurant-instagram-wordmark" aria-hidden="true">Instagram</p>
        <h2 id="restaurant-instagram-title">More moments from the rooftop.</h2>
        <p>See new restaurant photos and reels on the Garh Jaisal Haveli Instagram profile.</p>
        <a class="button button-primary" href="<?= e($instagramUrl) ?>" target="_blank" rel="noopener">Follow @garhjaisal <span aria-hidden="true">↗</span></a>
        <p class="restaurant-instagram-note">A lightweight profile link keeps this page quick to load. Individual posts and reels can be featured when their direct URLs are approved.</p>
      </aside>
    </div>
  </section>

<?php if ($menuOwnerConfirmed && $menu !== []): ?>
  <section class="section restaurant-featured" aria-labelledby="featured-heading">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">From the menu</p>
          <h2 id="featured-heading">Featured dishes.</h2>
        </div>
        <p>Menu descriptions are a preview. Please contact the hotel to confirm the current dishes and prices.</p>
      </div>

      <div class="restaurant-feature-grid">
        <?php foreach ($menu as $dishes): foreach ($dishes as $dish): if (!$dish['sig']) continue; ?>
          <article class="restaurant-feature-card">
            <span class="restaurant-diet <?= $dish['veg'] ? 'is-veg' : 'is-nonveg' ?>">
              <?= $dish['veg'] ? 'Vegetarian' : 'Non-vegetarian' ?>
            </span>
            <h3><?= e($dish['n']) ?></h3>
            <p><?= e($dish['d']) ?></p>
            <?php if ($menuOwnerConfirmed): ?><span class="restaurant-price">₹<?= number_format($dish['p']) ?></span><?php else: ?><span class="restaurant-price">Price on request</span><?php endif; ?>
          </article>
        <?php endforeach; endforeach; ?>
      </div>
    </div>
  </section>

  <section id="menu" class="section restaurant-menu" aria-labelledby="menu-heading">
    <div class="site-shell">
      <div class="section-head restaurant-menu-head">
        <div>
          <p class="eyebrow">Menu</p>
          <h2 id="menu-heading">What is on the menu.</h2>
        </div>
        <button class="restaurant-vegtoggle" type="button" aria-pressed="false">Vegetarian only</button>
      </div>

      <div class="restaurant-menu-booklet" data-menu-booklet>
        <p class="sr-only" data-restaurant-menu-status role="status" aria-live="polite" aria-atomic="true"></p>
        <nav class="restaurant-tabs" aria-label="Menu sections">
          <?php foreach ($menuCategories as $menuIndex => $category): ?>
            <a href="#<?= e(slugify($category)) ?>" data-menu-tab aria-controls="<?= e(slugify($category)) ?>" <?= $menuIndex === 0 ? 'aria-current="location"' : '' ?>><?= e($category) ?></a>
          <?php endforeach; ?>
        </nav>

        <div class="restaurant-booklet-controls" aria-label="Menu booklet controls">
          <button type="button" data-menu-previous disabled>← Previous</button>
          <p data-menu-page-status aria-live="polite" role="status">Page 1 of <?= count($menuCategories) ?>: <?= e($menuCategories[0] ?? 'Menu') ?></p>
          <button type="button" data-menu-next>Next →</button>
          <button type="button" data-menu-print>Print menu</button>
        </div>

        <div class="restaurant-menu-pages">
          <?php foreach ($menu as $category => $dishes): ?>
            <section class="restaurant-category" id="<?= e(slugify($category)) ?>" data-menu-page data-menu-page-label="<?= e($category) ?>" aria-labelledby="restaurant-menu-heading-<?= e(slugify($category)) ?>">
              <h3 id="restaurant-menu-heading-<?= e(slugify($category)) ?>"><?= e($category) ?></h3>
              <ul>
                <?php foreach ($dishes as $dish): ?>
                  <li class="restaurant-dish" data-veg="<?= $dish['veg'] ? '1' : '0' ?>">
                    <span class="restaurant-mark <?= $dish['veg'] ? 'is-veg' : 'is-nonveg' ?>" aria-hidden="true"></span>
                    <div>
                      <h4><?= e($dish['n']) ?></h4>
                      <p><?= e($dish['d']) ?></p>
                    </div>
                    <?php if ($menuOwnerConfirmed): ?><span class="restaurant-price">₹<?= number_format($dish['p']) ?></span><?php else: ?><span class="restaurant-price">Price on request</span><?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endforeach; ?>
        </div>

        <p class="restaurant-note">Menu items and listed prices may change. Please confirm the current menu and prices with the hotel.</p>
      </div>
    </div>
  </section>

<?php else: ?>
  <section id="menu" class="section restaurant-menu" aria-labelledby="menu-heading">
    <div class="site-shell">
      <div class="section-head restaurant-menu-head">
        <div>
          <p class="eyebrow">Menu update</p>
          <h2 id="menu-heading">Ask about the current menu.</h2>
        </div>
      </div>
      <p class="restaurant-note">We are confirming the current dishes, descriptions, prices and dietary options, so an unverified menu is not shown here. Contact the hotel for current details for your visit.</p>
      <p class="restaurant-contact-help">
        <a class="button button-primary" href="contact.php?topic=dining">Ask about the current menu</a>
      </p>
      <p class="restaurant-contact-help">
        <a class="text-link" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Ask on WhatsApp ↗</a>
      </p>
    </div>
  </section>
<?php endif; ?>

  <section class="section restaurant-info-section">
    <div class="site-shell restaurant-info-grid">
      <div>
        <p class="eyebrow">Good to know</p>
        <h2>Before you dine.</h2>
      </div>
      <div class="restaurant-info-list">
        <article><h3>Location</h3><p>Fort Kotari Para, inside Jaisalmer Fort.</p></article>
        <article><h3>Dietary requests</h3><p>Ask the hotel about current vegetarian, vegan, Jain and allergy options.</p></article>
        <article><h3>Walk-ins</h3><p>Availability can vary. Contact the hotel ahead of time, especially for larger groups.</p></article>
        <p class="restaurant-contact-help">Questions about current menu options or dietary requests? <a class="text-link" href="contact.php?topic=dining">Send a dining enquiry →</a></p>
      </div>
    </div>
  </section>

  <section class="section restaurant-find-us" aria-labelledby="restaurant-find-us-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Find us</p>
          <h2 id="restaurant-find-us-title">Find Garh Jaisal's Kitchen.</h2>
        </div>
        <p>Open the verified Google Maps destination or scan the QR code to get directions to the restaurant.</p>
      </div>
      <div class="restaurant-find-grid">
        <div class="restaurant-map-frame">
          <iframe
            src="<?= e($googleMapsEmbedUrl) ?>"
            title="Google Maps location of Garh Jaisal's Kitchen"
            width="600"
            height="450"
            style="border:0;"
            allowfullscreen
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
          ></iframe>
        </div>
        <aside class="restaurant-map-qr-card">
          <img
            src="img/restaurant-maps-qr.svg"
            alt="QR code to open Google Maps directions to Garh Jaisal's Kitchen"
            width="370"
            height="370"
            loading="lazy"
            decoding="async"
          >
          <h3>Scan for directions</h3>
          <p>Use your phone camera to open the restaurant's Google Maps destination.</p>
          <a class="text-link" href="<?= e($googleMapsUrl) ?>" target="_blank" rel="noopener">Open directions in Google Maps ↗</a>
        </aside>
      </div>
    </div>
  </section>

  <section id="reserve" class="section restaurant-reserve">
    <div class="site-shell restaurant-reserve-grid">
      <div>
        <p class="eyebrow eyebrow-light">Reserve</p>
        <h2>Plan your table.</h2>
        <p>For same-day enquiries, call <a href="tel:<?= e($hotelPhone) ?>"><?= e($hotelPhoneDisplay) ?></a> or message us on WhatsApp.</p>
        <a class="button button-light" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Message on WhatsApp</a>
      </div>

      <div class="restaurant-form-card">
        <?php if ($sent): ?>
          <div class="restaurant-notice" role="status">
            <h3>Request recorded.</h3>
            <p>Your table enquiry reference is <strong><?= e($reservationReference) ?></strong>. This is not a confirmed booking; the hotel must confirm service hours and availability.</p>
            <?php if ($reservationEmailNotified): ?>
              <p>The hotel was notified by email. For urgent confirmation, call or message us on WhatsApp.</p>
            <?php else: ?>
              <p>Automated email notification could not be confirmed. Please call <a href="tel:<?= e($hotelPhone) ?>"><?= e($hotelPhoneDisplay) ?></a> or message the hotel on WhatsApp and quote your reference to make sure the request is seen.</p>
            <?php endif; ?>
          </div>
        <?php elseif (!$showReservationForm): ?>
          <div class="restaurant-notice restaurant-reservation-unavailable" aria-labelledby="reservation-unavailable-heading">
            <h3 id="reservation-unavailable-heading">Enquire about a table</h3>
            <p>Online table requests aren't available on this page right now. Please call or message the hotel to ask about table availability.</p>
            <div class="restaurant-reservation-actions">
              <a class="button button-primary" href="tel:<?= e($hotelPhone) ?>">Call the hotel</a>
              <a class="button button-secondary" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Ask on WhatsApp</a>
              <a class="button button-secondary" href="contact.php?topic=dining">Send a dining enquiry</a>
            </div>
          </div>
        <?php else: ?>
          <?php if (isset($errors['form'])): ?>
            <p class="restaurant-error" role="alert"><?= e($errors['form']) ?></p>
          <?php endif; ?>

          <form class="restaurant-form" method="post" action="#reserve" novalidate>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['gjk_csrf']) ?>">
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="restaurant-hp" aria-hidden="true">

            <?php
            $fields = [
                ['name', 'Name *', 'text', 'required autocomplete="name"'],
                ['whatsapp', 'WhatsApp number *', 'tel', 'required autocomplete="tel" inputmode="tel"'],
                ['email', 'Email', 'email', 'autocomplete="email"'],
                ['date', 'Date *', 'date', 'required min="' . date('Y-m-d') . '" autocomplete="off"'],
                ['time', 'Preferred time *', 'time', 'required step="1800" min="00:00" max="23:30" autocomplete="off"'],
                ['guests', 'Guests *', 'number', 'required min="1" max="20" inputmode="numeric" autocomplete="off"'],
            ];
            foreach ($fields as [$id, $label, $type, $attrs]):
                $hasFieldError = isset($errors[$id]);
                $fieldErrorId = 'restaurant-error-' . $id;
            ?>
              <div class="restaurant-field <?= $hasFieldError ? 'has-error' : '' ?>">
                <label for="<?= e($id) ?>"><?= e($label) ?></label>
                <input
                  id="<?= e($id) ?>"
                  name="<?= e($id) ?>"
                  type="<?= e($type) ?>"
                  value="<?= e($old[$id]) ?>"
                  <?= $attrs ?>
                  <?= $hasFieldError ? 'aria-invalid="true" aria-describedby="' . e($fieldErrorId) . '"' : '' ?>
                >
                <?php if ($hasFieldError): ?>
                  <small id="<?= e($fieldErrorId) ?>"><?= e($errors[$id]) ?></small>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>

            <div class="restaurant-field <?= isset($errors['note']) ? 'has-error' : '' ?>">
              <label for="note">Special note (optional)</label>
              <textarea id="note" name="note" maxlength="500" rows="3" <?= isset($errors['note']) ? 'aria-invalid="true" aria-describedby="restaurant-error-note"' : 'aria-describedby="restaurant-note-help"' ?>><?= e($old['note']) ?></textarea>
              <?php if (isset($errors['note'])): ?><small id="restaurant-error-note"><?= e($errors['note']) ?></small><?php else: ?><small id="restaurant-note-help">Up to 500 characters. Please do not include payment details.</small><?php endif; ?>
            </div>

            <div class="restaurant-field restaurant-consent <?= isset($errors['consent']) ? 'has-error' : '' ?>">
              <label for="consent">
                <input id="consent" name="consent" type="checkbox" value="1" required <?= $old['consent'] === '1' ? 'checked' : '' ?> <?= isset($errors['consent']) ? 'aria-invalid="true" aria-describedby="restaurant-error-consent"' : '' ?>>
                I agree that the hotel may use these details to respond to my table enquiry.
              </label>
              <?php if (isset($errors['consent'])): ?><small id="restaurant-error-consent"><?= e($errors['consent']) ?></small><?php endif; ?>
            </div>

            <p class="restaurant-policy">Your request is not a confirmed booking. The hotel must confirm current service hours and availability before a table is reserved. If you need an immediate answer, call or message the hotel.</p>
            <button class="button button-primary" type="submit">Request a table</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section site-faq restaurant-faq" aria-labelledby="restaurant-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="restaurant-faq-title">Before you request a table.</h2></div>
        <p>The team can confirm current menu details, dietary options and table availability.</p>
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
<script src="js/restaurant.js" defer></script>
<script src="js/garh-v2.js"></script>
</body>
</html>
