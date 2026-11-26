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
  <div class="site-shell arrival-shell">
    <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>

    <section class="arrival-card arrival-hero page-hero" aria-labelledby="arrival-title">
      <div class="arrival-hero-copy">
        <h1 id="arrival-title">Getting here</h1>
        <p>Garh Jaisal Haveli is inside Jaisalmer Fort. Cars stop at Fort Parking.</p>
        <dl class="arrival-facts" aria-label="Arrival at a glance">
          <div><dt>Drop-off</dt><dd>Fort Parking</dd></div>
          <div><dt>Walk</dt><dd>300 metres<br>About 5–6 min</dd></div>
          <div><dt>Terrain</dt><dd>Cobbles and steps</dd></div>
        </dl>
        <a class="button button-primary arrival-quick-map" href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open hotel pin in Google Maps <span aria-hidden="true">↗</span></a>
      </div>

      <aside class="arrival-quick-card arrival-contact" id="arrivals" aria-labelledby="arrival-quick-card-title">
        <h2 id="arrival-quick-card-title">Need help arriving?</h2>
        <p>Share your expected arrival time and ask about luggage or mobility help.</p>
        <div class="arrival-contact-actions">
          <a class="button button-light" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" data-cta-id="call-arrival-hero" data-cta-location="arrival-hero">Call</a>
          <a class="button button-light" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-arrival-hero" data-cta-location="arrival-hero">WhatsApp</a>
        </div>
        <a class="arrival-help-link" href="contact.php?topic=arrival" data-cta-id="enquiry-arrival-footer" data-cta-location="arrival-contact-panel">Contact the hotel <span aria-hidden="true">→</span></a>
      </aside>
    </section>

    <nav class="arrival-section-nav" aria-label="Directions page sections">
      <a href="#arrival-overview-title">Walk</a>
      <a href="#arrival-map-title">Map</a>
      <a href="#walking-access-title">Access</a>
      <a href="#travel-options-title">Transfers</a>
      <a href="#arrival-faq-title">FAQs</a>
    </nav>

    <div class="arrival-layout">
      <section class="arrival-card arrival-overview" aria-labelledby="arrival-overview-title">
        <h2 id="arrival-overview-title">Walk from Fort Parking</h2>
        <ol class="arrival-route-steps" aria-label="Steps from Fort Parking to the hotel">
          <li>
            <span class="arrival-route-number" aria-hidden="true">1</span>
            <div><h3>Get dropped at Fort Parking</h3><p>Ask your driver to stop here. Cars cannot enter Jaisalmer Fort.</p></div>
          </li>
          <li>
            <span class="arrival-route-number" aria-hidden="true">2</span>
            <div><h3>Walk through the fort</h3><p>Follow the <a href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">hotel pin</a> on foot to Fort Kotari Para.</p></div>
          </li>
          <li>
            <span class="arrival-route-number" aria-hidden="true">3</span>
            <div><h3>Find the haveli entrance</h3><p>Look for the carved sandstone doorway and the Hotel Garh Jaisal sign beside it.</p></div>
          </li>
        </ol>

        <figure class="arrival-entrance">
          <img src="img/v2/facade.jpg" <?= gj_image_dimension_attributes('img/v2/facade.jpg', '(max-width: 520px) 128px, 200px') ?> alt="Carved sandstone doorway beside the Hotel Garh Jaisal sign" loading="lazy" decoding="async">
          <figcaption><strong>The hotel entrance</strong><span>Garh Jaisal Haveli, Fort Kotari Para</span><a href="img/v2/facade.jpg">View larger photo</a></figcaption>
        </figure>

        <div class="arrival-access">
          <h3 id="walking-access-title">Cobblestones and stairs</h3>
          <p>The walk includes narrow lanes, uneven cobbles and two short stair sections. <a href="contact.php?topic=arrival" data-cta-id="enquiry-arrival-confirm" data-cta-location="arrival-access">Ask about mobility or luggage help</a> before travelling.</p>
        </div>

        <div class="arrival-driver-card" aria-labelledby="arrival-driver-title">
          <h3 id="arrival-driver-title">Note for your driver</h3>
          <p>Cars cannot enter Jaisalmer Fort. Drop off at <strong>Fort Parking</strong>.</p>
          <p class="arrival-driver-card__address"><?= htmlspecialchars($arrivalAddress, ENT_QUOTES, 'UTF-8') ?></p>
          <a href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Hotel pin for the final walk</a>
          <div class="arrival-driver-card__action">
            <button class="button button-secondary arrival-driver-copy-button" type="button" data-arrival-copy="<?= htmlspecialchars($arrivalDriverNote, ENT_QUOTES, 'UTF-8') ?>" hidden>Copy arrival note for driver</button>
            <p class="arrival-driver-card__status" data-arrival-copy-status role="status" aria-live="polite"></p>
          </div>
        </div>
      </section>

      <aside class="arrival-sidebar" aria-label="Hotel map and contact details">
        <section class="arrival-card arrival-map" aria-labelledby="arrival-map-title">
          <h2 id="arrival-map-title">Map &amp; address</h2>
          <?php
          $mapPreview = [
              'class' => 'arrival-map-frame',
              'title' => 'Preview the hotel location',
              'description' => 'See the hotel and surrounding fort lanes.',
              'embed_url' => $hotelMapsEmbedUrl,
              'frame_title' => 'Map of Garh Jaisal Haveli near Fort Kotari Para inside Jaisalmer Fort',
          ];
          require __DIR__ . '/include/ui/map-preview.php';
          ?>
          <div class="arrival-map-copy">
            <p><strong>Garh Jaisal Haveli</strong><br><?= htmlspecialchars($arrivalAddress, ENT_QUOTES, 'UTF-8') ?></p>
            <a class="button button-primary" href="<?= htmlspecialchars($hotelMapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open exact hotel pin in Google Maps</a>
          </div>
          <address class="arrival-address">
            <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" data-cta-id="call-arrival-footer" data-cta-location="arrival-contact-panel"><?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="tel:<?= htmlspecialchars($hotelPhoneAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelPhoneAltDisplay, ENT_QUOTES, 'UTF-8') ?></a>
            <?php if ($hotelEmail !== ''): ?><a href="mailto:<?= htmlspecialchars($hotelEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelEmail, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
            <?php if ($hotelEmailAlt !== ''): ?><a href="mailto:<?= htmlspecialchars($hotelEmailAlt, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hotelEmailAlt, ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?>
          </address>
        </section>
      </aside>

      <section class="arrival-card arrival-transfer" aria-labelledby="travel-options-title">
        <h2 id="travel-options-title">Getting to Fort Parking</h2>
        <dl class="arrival-travel-options">
          <div><dt>By train</dt><dd>Ask for a transfer from Jaisalmer Railway Station.</dd></div>
          <div><dt>By plane</dt><dd>Ask for a transfer from Jaisalmer Airport.</dd></div>
          <div><dt>By road</dt><dd>Give your driver Fort Parking as the drop-off.</dd></div>
        </dl>
        <a class="button button-secondary" href="car-rental.php#car-travel-quote">Ask for a transfer quote</a>
        <p class="arrival-rickshaw-note">Rickshaw access inside the fort varies. <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" data-cta-id="call-arrival-notice" data-cta-location="arrival-notice">Call to check access</a>.</p>
      </section>

      <section class="arrival-card arrival-faq planning-path" aria-labelledby="arrival-faq-title">
        <h2 id="arrival-faq-title">Before you arrive</h2>
        <?php
        $faq = [
            'items' => [
                ['question' => 'Can I check in early or leave luggage before check-in?', 'answer' => 'Ask the hotel to confirm check-in times and whether early access or luggage storage can be arranged.'],
                ['question' => 'What if I arrive outside reception hours?', 'answer' => 'Confirm current reception hours. If your train or flight is delayed, share your updated arrival time by phone or WhatsApp.'],
                ['question' => 'Is there a restaurant on site?', 'answer' => [
                    ['text' => 'The '],
                    ['link' => ['label' => 'rooftop restaurant', 'href' => 'roof.php']],
                    ['text' => ' is at the hotel. Confirm current service times, breakfast availability, and menu options.'],
                ]],
            ],
            'list_class' => 'faq-list',
        ];
        require __DIR__ . '/include/ui/faq.php';
        ?>
        <nav class="arrival-planning-links" aria-label="Related planning pages">
          <a href="how-to-reach-jaisalmer.php">Travel to Jaisalmer</a>
          <a href="car-rental.php">Transfer options</a>
          <a href="contact.php?topic=arrival">Arrival enquiry form</a>
        </nav>
      </section>
    </div>

    <details class="arrival-arrangements">
      <summary>More about arrival arrangements</summary>
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
    </details>

    <div class="arrival-stay final-cta">
      <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" data-cta-id="booking-arrival-final" data-cta-location="arrival-final-cta">Check rooms &amp; rates</a>
      <a href="contact.php?topic=arrival" class="final-contact-button">Contact us</a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
<script src="js/arrival-guide.js"></script>
<script src="<?= htmlspecialchars($versionedAssetUrl('js/map-preview.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
