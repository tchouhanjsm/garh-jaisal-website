<?php
$activePage = 'directions';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$hotelPhone = $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $siteConfig['hotel_phone_display'];
$hotelPhoneAlt = $siteConfig['hotel_phone_alt'];
$hotelPhoneAltDisplay = $siteConfig['hotel_phone_alt_display'];
$hotelEmail = (string) ($siteConfig['hotel_email'] ?? '');
$hotelEmailAlt = (string) ($siteConfig['hotel_email_alt'] ?? '');
$whatsappUrl = $siteConfig['whatsapp_url'];
$hotelMapsUrl = $siteConfig['hotel_map_url'];
$hotelMapsEmbedUrl = 'https://www.google.com/maps?q=Garh+Jaisal+Haveli%2C+Fort+Kotari+Para%2C+Inside+Jaisalmer+Fort%2C+Jaisalmer%2C+Rajasthan+345001&output=embed';
$arrivalAddress = 'Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001';
$arrivalDriverNote = implode("\n", [
    'Garh Jaisal Haveli',
    $arrivalAddress,
    'Cars cannot enter Jaisalmer Fort. Please use Fort Parking as the drop-off point.',
    'From Fort Parking, the final walk is approximately 300 metres (5–6 minutes) through narrow fort lanes with uneven cobbles and short stair sections.',
    'Hotel pin for the final walk: ' . $hotelMapsUrl,
]);
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/direction.php';
require_once __DIR__ . '/include/schema.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Directions'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($schemaSiteUrl, '/direction.php', $breadcrumbs);
$page = [
    'title' => 'Directions to Garh Jaisal Haveli | Jaisalmer Fort',
    'description' => 'Reach Garh Jaisal Haveli inside Jaisalmer Fort from Fort Parking. See the 300-metre walk, the hotel map pin and how to ask about transfers.',
    'path' => '/direction.php',
    'og_title' => 'Getting to Garh Jaisal Haveli inside Jaisalmer Fort',
    'og_description' => 'Reach Garh Jaisal Haveli inside Jaisalmer Fort from Fort Parking. See the 300-metre walk, the hotel map pin and how to ask about transfers.',
    'og_image' => '/img/jcity.jpg',
    'og_image_alt' => 'View over Jaisalmer’s historic city from the fort edge',
    'twitter_title' => 'Getting to Garh Jaisal Haveli inside Jaisalmer Fort',
    'twitter_description' => 'Reach Garh Jaisal Haveli inside Jaisalmer Fort from Fort Parking. See the 300-metre walk, the hotel map pin and how to ask about transfers.',
    'twitter_image' => '/img/jcity.jpg',
    'preload' => [
        [
            'href' => 'img/v2/courtyard.jpg',
            'fetchpriority' => 'high',
            'imagesrcset' => '/img/responsive/v2/courtyard-480.webp 480w, /img/responsive/v2/courtyard-800.webp 800w, /img/responsive/v2/courtyard-1200.webp 1200w',
            'imagesizes' => '100vw',
        ],
    ],
    'css' => ['css/arrival.css', 'css/arrival-experience.css', 'css/map-preview.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'Directions to Garh Jaisal Haveli | Jaisalmer Fort',
            'description' => 'Reach Garh Jaisal Haveli inside Jaisalmer Fort from Fort Parking. See the 300-metre walk, the hotel map pin and how to ask about transfers.',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'mainEntity' => ['@id' => $schemaSiteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $schemaPageUrl . '#breadcrumb'],
        ],
        $breadcrumbSchema,
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="arrival-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo arrival-hero">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/courtyard.jpg" <?= gj_image_dimension_attributes('img/v2/courtyard.jpg', '100vw') ?> alt="" aria-hidden="true" loading="eager" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner arrival-hero-inner">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <div class="arrival-hero-layout">
        <div class="arrival-hero-copy">
          <p class="eyebrow">Getting here</p>
          <h1>Arrive inside Jaisalmer Fort.</h1>
          <p>
            Cars stop at Fort Parking. From there, walk about 300 metres (5–6 minutes) through the fort lanes to Garh Jaisal Haveli.
          </p>
          <div class="actions">
            <a class="button button-primary" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" data-cta-id="call-arrival-hero" data-cta-location="arrival-hero">Call the hotel</a>
            <a class="button button-light" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-arrival-hero" data-cta-location="arrival-hero">WhatsApp the hotel</a>
          </div>
        </div>

        <aside class="arrival-quick-card" aria-labelledby="arrival-quick-card-title">
          <h2 id="arrival-quick-card-title">Plan for the final walk.</h2>
          <p>Expect uneven cobbles and short stair sections. Contact us if you need help with the walk.</p>
          <a class="button button-primary arrival-quick-map" href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open exact hotel pin <span aria-hidden="true">↗</span></a>
        </aside>
      </div>
    </div>
  </section>

  <nav class="arrival-section-nav" aria-label="Directions page sections">
    <div class="site-shell">
      <a href="#arrival-overview-title">Quick directions</a>
      <a href="#travel-options-title">Travel options</a>
      <a href="#walking-access-title">Walking &amp; mobility</a>
      <a href="#arrival-map-title">Map &amp; navigation</a>
      <a href="#arrival-faq-title">FAQs</a>
    </div>
  </nav>

  <section class="section arrival-overview" aria-labelledby="arrival-overview-title">
    <div class="site-shell">
      <h2 id="arrival-overview-title">From Fort Parking to the haveli</h2>
      <ol class="steps arrival-route-steps" aria-label="Steps from Fort Parking to the hotel">
        <li class="step">
          <span class="arrival-route-number" aria-hidden="true">1</span>
          <div>
            <h3>Stop at Fort Parking</h3>
            <p>Use Fort Parking as your drop-off if you are coming by car or taxi.</p>
          </div>
        </li>
        <li class="step">
          <span class="arrival-route-number" aria-hidden="true">2</span>
          <div>
            <h3 id="walking-access-title">Walk through the Fort</h3>
            <p>Allow about 5–6 minutes for 300 metres on narrow, uneven cobbles with two short stair sections.</p>
          </div>
        </li>
        <li class="step">
          <span class="arrival-route-number" aria-hidden="true">3</span>
          <div>
            <h3>Find Garh Jaisal Haveli</h3>
            <p>Follow the <a href="#arrival-map-title">hotel pin</a> on foot to Fort Kotari Para.</p>
          </div>
        </li>
      </ol>

      <aside class="arrival-driver-card" aria-labelledby="arrival-driver-title">
        <div class="arrival-driver-card__copy">
          <p class="eyebrow">For your driver</p>
          <h3 id="arrival-driver-title">Share the right drop-off point.</h3>
          <p>Cars cannot enter Jaisalmer Fort. Drop off at Fort Parking and use the hotel pin for the final walk.</p>
          <p class="arrival-driver-card__address"><?= htmlspecialchars($arrivalAddress, ENT_QUOTES, 'UTF-8') ?></p>
          <a href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Hotel pin for the final walk</a>
        </div>
        <div class="arrival-driver-card__action">
          <button class="button button-primary arrival-driver-copy-button" type="button" data-arrival-copy="<?= htmlspecialchars($arrivalDriverNote, ENT_QUOTES, 'UTF-8') ?>" hidden>Copy arrival note for driver</button>
          <p class="arrival-driver-card__status" data-arrival-copy-status role="status" aria-live="polite"></p>
        </div>
      </aside>

      <div class="arrival-transfer">
        <h3 id="travel-options-title">Coming by train or plane?</h3>
        <p><a href="car-rental.php#car-travel-quote">Ask for a transfer quote</a> from the station or airport to Fort Parking. Rickshaw access inside the fort varies. Confirm current access with the hotel before relying on a ride.</p>
        <a class="arrival-transfer-call" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" data-cta-id="call-arrival-notice" data-cta-location="arrival-notice">Call about current access</a>
      </div>
    </div>
  </section>

  <section class="section arrival-map" aria-labelledby="arrival-map-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Map and navigation</p><h2 id="arrival-map-title">Find the hotel inside Jaisalmer Fort.</h2></div>
        <p>Use the hotel pin for the walking portion from Fort Parking.</p>
      </div>
      <div class="arrival-map-grid">
        <?php
        $mapPreview = [
            'class' => 'arrival-map-frame',
            'title' => 'Preview the hotel location',
            'description' => 'Open the interactive map to see the hotel and surrounding fort lanes.',
            'embed_url' => $hotelMapsEmbedUrl,
            'frame_title' => 'Map of Garh Jaisal Haveli near Fort Kotari Para inside Jaisalmer Fort',
        ];
        require __DIR__ . '/include/ui/map-preview.php';
        ?>
        <div class="arrival-map-copy">
          <h3>Garh Jaisal Haveli</h3>
          <p><?= htmlspecialchars($arrivalAddress, ENT_QUOTES, 'UTF-8') ?></p>
          <div class="actions">
            <a class="button button-primary" href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open exact hotel pin in Google Maps</a>
            <a class="button button-secondary" href="contact.php?topic=arrival" data-cta-id="enquiry-arrival-confirm" data-cta-location="arrival-access">Confirm arrival arrangements</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section arrival-contact" id="arrivals">
    <div class="site-shell arrival-split">
      <div>
        <p class="eyebrow">Arrival arrangements</p>
        <h2>Confirm your arrival plan.</h2>
        <p>Share your expected arrival time. Ask the hotel to confirm reception hours, luggage or mobility assistance, and what to do if you arrive early or late.</p>
        <div class="actions">
          <a class="button button-primary" href="contact.php?topic=arrival" data-cta-id="enquiry-arrival-footer" data-cta-location="arrival-contact-panel">Contact the hotel</a>
          <a class="button button-light" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-arrival-footer" data-cta-location="arrival-contact-panel">WhatsApp</a>
          <a class="button button-light" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" data-cta-id="call-arrival-footer" data-cta-location="arrival-contact-panel">Call</a>
        </div>
      </div>
      <address class="arrival-address">
        <p class="eyebrow">Hotel</p>
        <h3>Garh Jaisal Haveli</h3>
        <p>Fort Kotari Para<br>Inside Jaisalmer Fort<br>Jaisalmer, Rajasthan 345001</p>
        <p>
          <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a><br>
          <a href="tel:<?= htmlspecialchars($hotelPhoneAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneAltDisplay, ENT_QUOTES, 'UTF-8') ?></a>
        </p>
        <p>
          <?php if ($hotelEmail !== ''): ?><a href="mailto:<?= htmlspecialchars($hotelEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelEmail, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
          <?php if ($hotelEmail !== '' && $hotelEmailAlt !== ''): ?><br><?php endif; ?>
          <?php if ($hotelEmailAlt !== ''): ?><a href="mailto:<?= htmlspecialchars($hotelEmailAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelEmailAlt, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
        </p>
        <p>
          <a href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open in Google Maps</a>
        </p>
      </address>
    </div>
  </section>

  <section class="section arrival-faq">
    <div class="site-shell arrival-split">
      <div>
        <p class="eyebrow">Questions</p>
        <h2 id="arrival-faq-title">Before you set out.</h2>
        <p>If your train or flight changes, call, WhatsApp, or send a note through the <a href="contact.php?topic=arrival">arrival enquiry form</a>.</p>
      </div>
      <?php
      $faq = [
          'items' => [
              ['question' => 'Can I check in early or leave luggage before check-in?', 'answer' => 'Ask the hotel to confirm check-in times and whether early access or luggage storage can be arranged.'],
              ['question' => 'What if I arrive outside reception hours?', 'answer' => 'Confirm current reception hours and share your arrival time by phone or WhatsApp. Ask what assistance can be arranged before relying on it.'],
              ['question' => 'Is there a restaurant on site?', 'answer' => [
                  ['text' => 'The '],
                  ['link' => ['label' => 'rooftop restaurant', 'href' => 'roof.php']],
                  ['text' => ' is at the hotel. Confirm current service times, breakfast availability, and menu options.'],
              ]],
          ],
          'list_class' => 'faq-list',
      ];
      require __DIR__ . '/include/ui/faq.php';
      ?>   </div>
  </section>

  <div class="site-shell arrival-decision">
      <?php
      $guestDecisionContext = 'arrival';
      $guestDecisionPrimaryHref = 'contact.php?topic=arrival';
      $guestDecisionPrimaryLabel = 'Confirm arrival arrangements';
      $guestDecisionSecondaryHref = $whatsappUrl;
      $guestDecisionSecondaryLabel = 'WhatsApp the hotel';
      $guestDecisionSecondaryExternal = true;
      require __DIR__ . '/include/ui/guest-decision-panel.php';
      unset($guestDecisionContext, $guestDecisionPrimaryHref, $guestDecisionPrimaryLabel, $guestDecisionSecondaryHref, $guestDecisionSecondaryLabel, $guestDecisionSecondaryExternal);
      ?>
  </div>

  <?php
$ctaBand = [
    'section_class' => 'section final-cta',
    'inner_class' => 'site-shell final-cta-inner',
    'eyebrow' => 'Plan your stay',
    'title' => 'Stay at Garh Jaisal Haveli.',
    'text' => 'Check availability, or ask the team about your arrival time.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'Book your stay',
        'href' => $bookingUrl,
        'class' => 'button button-primary',
        'target' => '_blank',
        'rel' => 'noopener',
        'tracking_id' => 'booking-arrival-final',
        'tracking_location' => 'arrival-final-cta',
    ],
    'secondary' => [
        'label' => 'Contact us',
        'href' => 'contact.php?topic=arrival',
        'class' => 'button final-contact-button',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>

</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
<script src="js/arrival-guide.js"></script>
<script src="js/map-preview.js" defer></script>
</body>
</html>
