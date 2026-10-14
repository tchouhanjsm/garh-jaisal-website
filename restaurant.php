<?php
$activePage = 'dining';
$siteConfig = require __DIR__ . '/include/site-config.php';
$hotelPhone = $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $siteConfig['hotel_phone_display'];
$whatsappUrl = $siteConfig['whatsapp_url'];
$hotelEmail = $siteConfig['hotel_email'];
$sendMail = getenv('GJ_RESTAURANT_MAIL_ENABLED') === '1';

$restaurant = [
    'name' => "Garh Jaisal's Kitchen",
    'tagline' => 'Rajasthani food on the rooftop.',
    'address' => 'Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001',
];

/* Menu/prices are from the supplied restaurant design. Verify the live menu before staging. */
$menu = [
    'Starters' => [
        ['n' => 'Laal Maas Tikka', 'd' => 'Mathania chilli-marinated lamb, charred in the tandoor.', 'p' => 420, 'veg' => false, 'sig' => false],
        ['n' => 'Pyaaz Kachori', 'd' => 'Flaky kachori with spiced onion, tamarind and mint chutney.', 'p' => 180, 'veg' => true, 'sig' => false],
        ['n' => 'Paneer Soola', 'd' => 'Cottage cheese skewers with smoked yoghurt and raw mango.', 'p' => 340, 'veg' => true, 'sig' => false],
    ],
    'Rajasthani Mains' => [
        ['n' => 'Laal Maas', 'd' => 'Slow-cooked mutton in a red chilli and garlic gravy.', 'p' => 560, 'veg' => false, 'sig' => true],
        ['n' => 'Ker Sangri', 'd' => 'Desert beans and berries with curd and dried spices.', 'p' => 380, 'veg' => true, 'sig' => false],
        ['n' => 'Gatte ki Sabzi', 'd' => 'Gram flour dumplings in a tangy yoghurt curry.', 'p' => 360, 'veg' => true, 'sig' => false],
        ['n' => 'Junglee Maas', 'd' => "Mutton, ghee, garlic and red chilli.", 'p' => 600, 'veg' => false, 'sig' => false],
    ],
    'Dal, Bati & Breads' => [
        ['n' => 'Dal Baati Churma', 'd' => 'Baked wheat balls, panchmel dal and sweet churma.', 'p' => 420, 'veg' => true, 'sig' => true],
        ['n' => 'Bajra Roti', 'd' => 'Millet flatbread with white butter and jaggery.', 'p' => 90, 'veg' => true, 'sig' => false],
        ['n' => 'Missi Roti', 'd' => 'Gram and wheat flatbread with ajwain.', 'p' => 80, 'veg' => true, 'sig' => false],
    ],
    'Desserts' => [
        ['n' => 'Ghevar', 'd' => 'Fried batter, saffron syrup and rabri.', 'p' => 260, 'veg' => true, 'sig' => true],
        ['n' => 'Mawa Kachori', 'd' => 'Sweet kachori with khoya, nuts and syrup.', 'p' => 220, 'veg' => true, 'sig' => false],
        ['n' => 'Kesar Kulfi', 'd' => 'Slow-reduced milk, saffron and pistachio.', 'p' => 200, 'veg' => true, 'sig' => false],
    ],
];

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
$old = [
    'name' => '',
    'email' => '',
    'whatsapp' => '',
    'date' => '',
    'time' => '',
    'guests' => '2',
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

    if ($old['name'] === '' || mb_strlen($old['name']) > 80) {
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
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $old['time'])) {
        $errors['time'] = 'Enter a valid preferred time; the hotel will confirm availability.';
    }
    if ((int) $old['guests'] < 1 || (int) $old['guests'] > 20) {
        $errors['guests'] = 'Guests must be between 1 and 20.';
    }

    if (!$errors) {
        if (!$sendMail) {
            $errors['form'] = 'Online table requests are not enabled yet. Please call or message us on WhatsApp.';
        } else {
            $safeName = str_replace(["\r", "\n"], '', $old['name']);
            $subject = 'Restaurant table request - ' . $safeName;
            $body = "New table request\n\n"
                . "Name: {$old['name']}\n"
                . "WhatsApp: {$phone}\n"
                . "Email: " . ($old['email'] ?: '-') . "\n"
                . "Date: {$old['date']}\n"
                . "Time: {$old['time']}\n"
                . "Guests: {$old['guests']}\n";

            $headers = 'Content-Type: text/plain; charset=UTF-8';
            if ($old['email'] !== '') {
                $replyTo = str_replace(["\r", "\n"], '', $old['email']);
                $headers .= "\r\nReply-To: {$replyTo}";
            }

            $sent = @mail($hotelEmail, $subject, $body, $headers);
            if ($sent) {
                $_SESSION['gjk_csrf'] = bin2hex(random_bytes(16));
            } else {
                $errors['form'] = 'We could not send the request. Please call or message us on WhatsApp.';
            }
        }
    }
}

$siteUrl = rtrim($siteConfig['site_url'], '/');
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
            'telephone' => '+91-7727047567',
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
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Where is Garh Jaisal’s Kitchen?", 'answer' => "Garh Jaisal’s Kitchen is located at Fort Kotari Para, inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001."],
    ['question' => "Can menu items and prices change?", 'answer' => "Yes. Menu items and listed prices may change; confirm the current menu and prices with the hotel before visiting."],
    ['question' => "How do I request a table?", 'answer' => "Use the table enquiry form when online requests are available, or contact the hotel by phone or WhatsApp. A request is not confirmed until the hotel replies. Availability can vary, especially for larger groups."],
    ['question' => "Can I ask about dietary requirements?", 'answer' => "Ask the hotel about current vegetarian, vegan, Jain and allergy options before confirming your meal."],
];
$page = [
    'title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'description' => "Rajasthani food at " . $restaurant['name'] . ", the rooftop restaurant at Garh Jaisal Haveli inside Jaisalmer Fort.",
    'path' => '/restaurant.php',
    'og_type' => 'website',
    'og_title' => $restaurant['name'] . ' | Restaurant in Jaisalmer',
    'og_description' => 'Rajasthani food on the rooftop at Garh Jaisal Haveli inside Jaisalmer Fort.',
    'og_image' => '/img/rest2.jpg',
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
        <h1>Rajasthani food on the rooftop.</h1>
        <p>Vegetarian and non-vegetarian dishes served at Garh Jaisal Haveli, inside Jaisalmer Fort.</p>
        <div class="actions">
          <a class="button button-primary" href="#reserve">Reserve a table</a>
          <a class="button button-light" href="<?= e($waLink) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
        <p class="restaurant-rooftop-link">Planning a meal or candlelight dinner? <a href="roof.php">Explore the rooftop dining space</a>.</p>
      </div>

      <div class="restaurant-hero-media">
        <img
          src="img/rest2.jpg"
          width="900"
          height="600"
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
        <h2>A simple menu with Rajasthani favourites.</h2>
      </div>
      <p>Try dishes such as Laal Maas, Ker Sangri, Gatte ki Sabzi and Dal Baati Churma, alongside breads and desserts.</p>
    </div>

    <div class="site-shell restaurant-gallery" aria-label="Restaurant and rooftop views">
      <figure><img src="img/rest3.jpg" alt="Rooftop view from Garh Jaisal's Kitchen" loading="lazy" width="1200" height="800"></figure>
      <figure><img src="img/rest4.jpg" alt="Evening rooftop setting at Garh Jaisal's Kitchen" loading="lazy" width="1200" height="800"></figure>
      <figure><img src="img/rest5.jpg" alt="Rooftop table at Garh Jaisal's Kitchen" loading="lazy" width="1200" height="800"></figure>
    </div>
  </section>

  <section class="section restaurant-featured" aria-labelledby="featured-heading">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">From the menu</p>
          <h2 id="featured-heading">Featured dishes.</h2>
        </div>
        <p>Some of the dishes included in the current restaurant design.</p>
      </div>

      <div class="restaurant-feature-grid">
        <?php foreach ($menu as $dishes): foreach ($dishes as $dish): if (!$dish['sig']) continue; ?>
          <article class="restaurant-feature-card">
            <span class="restaurant-diet <?= $dish['veg'] ? 'is-veg' : 'is-nonveg' ?>">
              <?= $dish['veg'] ? 'Vegetarian' : 'Non-vegetarian' ?>
            </span>
            <h3><?= e($dish['n']) ?></h3>
            <p><?= e($dish['d']) ?></p>
            <span class="restaurant-price">₹<?= number_format($dish['p']) ?></span>
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

      <nav class="restaurant-tabs" aria-label="Menu sections">
        <?php foreach (array_keys($menu) as $category): ?>
          <a href="#<?= e(slugify($category)) ?>"><?= e($category) ?></a>
        <?php endforeach; ?>
      </nav>

      <?php foreach ($menu as $category => $dishes): ?>
        <section class="restaurant-category" id="<?= e(slugify($category)) ?>">
          <h3><?= e($category) ?></h3>
          <ul>
            <?php foreach ($dishes as $dish): ?>
              <li class="restaurant-dish" data-veg="<?= $dish['veg'] ? '1' : '0' ?>">
                <span class="restaurant-mark <?= $dish['veg'] ? 'is-veg' : 'is-nonveg' ?>" aria-hidden="true"></span>
                <div>
                  <h4><?= e($dish['n']) ?></h4>
                  <p><?= e($dish['d']) ?></p>
                </div>
                <span class="restaurant-price">₹<?= number_format($dish['p']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endforeach; ?>

      <p class="restaurant-note">Menu items and listed prices may change. Please confirm the current menu and prices with the hotel.</p>
    </div>
  </section>

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
            <h3>Request sent.</h3>
            <p>We will contact you to confirm the table.</p>
          </div>
        <?php elseif (!$sendMail): ?>
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
                ['date', 'Date *', 'date', 'required min="' . date('Y-m-d') . '"'],
                ['time', 'Preferred time *', 'time', 'required'],
                ['guests', 'Guests *', 'number', 'required min="1" max="20" inputmode="numeric"'],
            ];
            foreach ($fields as [$id, $label, $type, $attrs]): ?>
              <div class="restaurant-field <?= isset($errors[$id]) ? 'has-error' : '' ?>">
                <label for="<?= e($id) ?>"><?= e($label) ?></label>
                <input id="<?= e($id) ?>" name="<?= e($id) ?>" type="<?= e($type) ?>" value="<?= e($old[$id]) ?>" <?= $attrs ?> autocomplete="off">
                <?php if (isset($errors[$id])): ?><small><?= e($errors[$id]) ?></small><?php endif; ?>
              </div>
            <?php endforeach; ?>

            <p class="restaurant-policy">This is a request, not a confirmed booking. The hotel will contact you to confirm availability for your preferred date and time. For same-day enquiries, calling or WhatsApp may be quicker.</p>
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
