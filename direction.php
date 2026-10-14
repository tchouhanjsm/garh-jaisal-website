<?php
$activePage = 'directions';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
$hotelPhone = $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $siteConfig['hotel_phone_display'];
$hotelPhoneAlt = $siteConfig['hotel_phone_alt'];
$hotelPhoneAltDisplay = $siteConfig['hotel_phone_alt_display'];
$whatsappUrl = $siteConfig['whatsapp_url'];
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/direction.php';
$page = [
    'title' => 'Directions to Garh Jaisal Haveli | Jaisalmer Fort',
    'description' => 'Find Garh Jaisal Haveli inside Jaisalmer Fort. See the walk from Fort Parking, arrival guidance and hotel contact details.',
    'path' => '/direction.php',
    'og_title' => 'Getting to Garh Jaisal Haveli inside Jaisalmer Fort',
    'og_description' => 'Find Garh Jaisal Haveli inside Jaisalmer Fort. See the walk from Fort Parking, arrival guidance and hotel contact details.',
    'og_image' => '/img/jcity.jpg',
    'twitter_title' => 'Getting to Garh Jaisal Haveli inside Jaisalmer Fort',
    'twitter_description' => 'Find Garh Jaisal Haveli inside Jaisalmer Fort. Check the walking route from Fort Parking, travel notes, and hotel contact details.',
    'twitter_image' => '/img/jcity.jpg',
    'css' => ['css/arrival.css'],
    'schema' => [
        [
            '@type' => 'Place',
            'name' => 'Garh Jaisal Haveli',
            'url' => $schemaPageUrl,
            'telephone' => ['+91 77270 47567', '+91 2992 253836'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Fort Kotari Para, Inside Jaisalmer Fort',
                'addressLocality' => 'Jaisalmer',
                'addressRegion' => 'Rajasthan',
                'postalCode' => '345001',
                'addressCountry' => 'IN',
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="arrival-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/courtyard.jpg" <?= gj_image_dimension_attributes('img/v2/courtyard.jpg') ?> alt="" aria-hidden="true" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Getting here</p>
      <h1>Arrive inside Jaisalmer Fort.</h1>
      <p>
        Garh Jaisal Haveli is inside the fort. Cars stop at Fort Parking, then it is a short walk through the lanes. Contact the hotel before travel to ask whether arrival or luggage assistance can be arranged.
      </p>
      <div class="actions">
        <a class="button button-primary" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>">Call <?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="button button-light" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">WhatsApp the hotel</a>
      </div>
    </div>
  </section>

  <section class="section arrival-overview">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">The last stretch</p>
          <h2>How guests reach the haveli.</h2>
        </div>
        <p>
          From Fort Parking, allow a 5–6 minute walk (roughly 300 metres). Typical door-to-door time from Jaisalmer Railway Station or the airport is about 5–20 minutes; the final part is on foot.
        </p>
      </div>

      <div class="arrival-facts">
        <article class="arrival-fact">
          <span>From Fort Parking</span>
          <strong>5–6 min walk</strong>
          <p>The walk is roughly 300 metres and can take longer when the fort is busy. Contact the hotel before arrival to ask whether assistance can be arranged.</p>
        </article>
        <article class="arrival-fact">
          <span>From the fort entrance</span>
          <strong>3–4 min walk</strong>
          <p>About 200 metres from the fort entrance to the haveli.</p>
        </article>
        <article class="arrival-fact">
          <span>Reception and arrival help</span>
          <strong>Contact to confirm</strong>
          <p>Call or WhatsApp the hotel before arrival to confirm current reception hours, share your ETA, and ask what arrival assistance can be arranged.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">On the day</p>
          <h2>From the parking area to the door.</h2>
        </div>
        <p>Most guests find the short walk part of staying in the living fort.</p>
      </div>

      <div class="steps">
        <div class="step">
          <span>01</span>
          <strong>Share your ETA</strong>
          <span>Call or WhatsApp when you are on the way, or about 10 minutes before you reach the fort from the city centre.</span>
        </div>
        <div class="step">
          <span>02</span>
          <strong>Stop at Fort Parking</strong>
          <span>Cars cannot enter the fort. Use Fort Parking as the drop-off.</span>
        </div>
        <div class="step">
          <span>03</span>
          <strong>Ask about assistance</strong>
          <span>Contact the hotel before travel to ask whether staff can meet you or help with luggage. Wait for confirmation before relying on any arrangement.</span>
        </div>
        <div class="step">
          <span>04</span>
          <strong>Walk to the haveli</strong>
          <span>A guided walk through the lanes to Garh Jaisal. From the city centre, the meeting point is just past the main gate.</span>
        </div>
      </div>
    </div>
  </section>

  <section class="section arrival-travel">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Stations and the airport</p>
          <h2>Ways to reach Fort Parking.</h2>
        </div>
        <p>These are the transfer notes already given for the hotel. Ask the team to confirm them for your arrival time.</p>
      </div>

      <div class="travel-grid">
        <article class="travel-card">
          <h3>Railway station</h3>
          <p>A rickshaw pickup is listed within 8 minutes of the train arriving, with about 12 minutes of travel in total. A rickshaw from the station is listed at about ₹300.</p>
        </article>
        <article class="travel-card">
          <h3>Jaisalmer airport</h3>
          <p>A private sedan to Fort Parking is listed at about ₹1100 and about 20 minutes. Contact the hotel ahead of time to confirm availability and ask about assistance from the parking area.</p>
        </article>
        <article class="travel-card">
          <h3>Inside the fort</h3>
          <p>When a rickshaw is permitted, the ride from the parking area is listed at ₹120–₹150. During busy hours that option often is not available.</p>
        </article>
      </div>

      <div class="arrival-notice">
        <div>
          <p class="eyebrow">Busy hours</p>
          <h3>Rickshaws often stay outside from 8:00 AM to 8:00 PM.</h3>
          <p>
            In those hours, rickshaws are often not allowed inside the fort because the lanes are narrow and crowded. Contact the hotel to ask whether walking or luggage assistance can be arranged, and confirm before relying on it. Outside those hours, rickshaws often go inside the fort.
          </p>
        </div>
        <a class="button button-primary" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>">Call when you arrive</a>
      </div>
    </div>
  </section>

  <section class="section arrival-access">
    <div class="site-shell arrival-split">
      <div>
        <p class="eyebrow">Walking in</p>
        <h2>The route is cobbled, with two short stair sections.</h2>
        <p>The fort lanes are narrow and uneven in places. Contact the hotel before arrival to ask whether luggage or escort help can be arranged, and confirm any service hours in advance.</p>
      </div>
      <ul class="access-list">
        <li>
          <strong>Vehicle access</strong>
          <span>Cars cannot enter. Fort Parking is the drop-off.</span>
        </li>
        <li>
          <strong>Surface and steps</strong>
          <span>Uneven cobbles and short stair sections along the route.</span>
        </li>
        <li>
          <strong>Arrival assistance</strong>
          <span>Ask the hotel whether luggage help or meet-and-assist can be arranged for your date.</span>
        </li>
      </ul>
    </div>
  </section>

  <section class="section arrival-contact">
    <div class="site-shell arrival-split">
      <div>
        <p class="eyebrow">Arrival arrangements</p>
        <h2>Confirm your arrival plan.</h2>
        <p>Please contact the hotel before travel to confirm current reception hours, check-in and check-out times, early-arrival access, luggage storage, and arrangements for arrivals outside reception hours. Share your expected arrival time so the team can advise what may be arranged for your date.</p>
        <div class="actions">
          <a class="button button-primary" href="contact.php?topic=arrival">Contact the hotel</a>
          <a class="button button-light" href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">WhatsApp</a>
          <a class="button button-light" href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>">Call</a>
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
          <a href="mailto:info@hotelgarhjaisal.com">info@hotelgarhjaisal.com</a><br>
          <a href="mailto:mukeshkila@yahoo.com">mukeshkila@yahoo.com</a>
        </p>
        <p>
          <a href="https://maps.app.goo.gl/nAbRiZhv78FSbju48" target="_blank" rel="noopener">Open in Google Maps</a>
        </p>
      </address>
    </div>
  </section>

  <section class="section arrival-faq">
    <div class="site-shell arrival-split">
      <div>
        <p class="eyebrow">Questions</p>
        <h2>Before you set out.</h2>
        <p>If your train or flight changes, call, WhatsApp, or send a note through the <a href="contact.php?topic=arrival">arrival enquiry form</a>.</p>
      </div>
      <?php
      $faq = [
          'items' => [
              ['question' => 'Can my car enter the fort?', 'answer' => 'No. Park at Fort Parking. The hotel is about a 4 minute walk from there.'],
              ['question' => 'Can you meet me at the parking area?', 'answer' => 'Contact the hotel before travel with your expected arrival time and ask whether staff can meet you. Do not assume meet-and-assist is confirmed until the hotel replies.'],
              ['question' => 'Are rickshaws allowed inside?', 'answer' => 'Not during busy hours, about 8:00 AM–8:00 PM. Outside those times, rickshaws often go inside the fort.'],
              ['question' => 'Can I check in early or leave luggage before check-in?', 'answer' => 'Contact the hotel before travel to confirm check-in times, whether early check-in is available, and whether luggage storage can be arranged.'],
              ['question' => 'What if I arrive outside reception hours?', 'answer' => 'Call or WhatsApp the hotel before travel to confirm current reception hours and ask whether a meet-and-assist arrangement can be made for your expected arrival time.'],
              ['question' => 'Is there a restaurant on site?', 'answer' => [
                  ['text' => 'The rooftop restaurant is on site. Confirm current service times, breakfast availability, and menu options with the hotel before visiting. See the '],
                  ['link' => ['label' => 'rooftop dining page', 'href' => 'roof.php']],
                  ['text' => ' for the dining space.'],
              ]],
              ['question' => 'I am arriving late at night by train. What should I do?', 'answer' => 'Call or WhatsApp the hotel before travel with your expected arrival time. Confirm current reception hours and whether late-arrival assistance can be arranged; do not assume a late-night meet is confirmed until the team confirms it.'],
              ['question' => 'Can I get help with luggage at Fort Parking?', 'answer' => 'Call or WhatsApp before arrival to confirm what luggage assistance can be arranged at Fort Parking. Your driver can also call the hotel when you are close.'],
              ['question' => 'Can I get a ride from the station or airport?', 'answer' => 'A rickshaw from the railway station is listed at about ₹300. A private sedan from the airport to Fort Parking is listed at about ₹1100. Call or WhatsApp to arrange it.'],
          ],
          'list_class' => 'faq-list',
      ];
      require __DIR__ . '/include/ui/faq.php';
      ?>   </div>
  </section>

  <section class="section arrival-more">
    <div class="site-shell section-head">
      <div>
        <p class="eyebrow">After you arrive</p>
        <h2>Desert experiences are arranged from the hotel.</h2>
      </div>
      <p>
        Camel safaris, sunset trips, and overnight desert stays are described on the desert page. Confirm the price and timing for your date there.
        <a class="text-link" href="pack.php">View desert experiences</a>
      </p>
    </div>
  </section>

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
</body>
</html>
