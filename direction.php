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
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Directions to Garh Jaisal Haveli | Jaisalmer Fort</title>
  <meta
    name="description"
    content="Find Garh Jaisal Haveli inside Jaisalmer Fort. See the walk from Fort Parking, arrival instructions, reception hours and hotel contact details."
  >
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="https://www.hotelgarhjaisal.com/direction.php">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Getting to Garh Jaisal Haveli inside Jaisalmer Fort">
  <meta
    property="og:description"
    content="Find Garh Jaisal Haveli inside Jaisalmer Fort. See the walk from Fort Parking, arrival instructions, reception hours and hotel contact details."
  >
  <meta property="og:url" content="https://www.hotelgarhjaisal.com/direction.php">
  <meta property="og:image" content="https://www.hotelgarhjaisal.com/img/jcity.jpg">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Getting to Garh Jaisal Haveli inside Jaisalmer Fort">
  <meta
    name="twitter:description"
    content="Find Garh Jaisal Haveli inside Jaisalmer Fort. Walk from Fort Parking, call for luggage help, and see reception hours, phone numbers and the address."
  >
  <meta name="twitter:image" content="https://www.hotelgarhjaisal.com/img/jcity.jpg">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/arrival.css">

  <script async src="https://www.googletagmanager.com/gtag/js?id=G-0C646S1FQ0"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-0C646S1FQ0');
    gtag('config', 'G-6S5PCEVXH4');
  </script>

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Place",
    "name": "Garh Jaisal Haveli",
    "url": "https://www.hotelgarhjaisal.com/direction.php",
    "telephone": ["+91 77270 47567", "+91 2992 253836"],
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Fort Kotari Para, Inside Jaisalmer Fort",
      "addressLocality": "Jaisalmer",
      "addressRegion": "Rajasthan",
      "postalCode": "345001",
      "addressCountry": "IN"
    }
  }
  </script>
</head>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="arrival-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/courtyard.jpg" alt="" width="1333" height="2000" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Getting here</p>
      <h1>Arrive inside Jaisalmer Fort.</h1>
      <p>
        Garh Jaisal Haveli is inside the fort. Cars stop at Fort Parking, then it is a short walk through the lanes. Call ahead and the team can meet you and help with luggage.
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
          Typical door-to-door time from Jaisalmer Railway Station or the airport is about 5–20 minutes. The final part is on foot.
        </p>
      </div>

      <div class="arrival-facts">
        <article class="arrival-fact">
          <span>From Fort Parking</span>
          <strong>5–6 min walk</strong>
          <p>The team can meet you at the parking area and help with luggage. The walk is roughly 300 metres and can take longer when the fort is busy.</p>
        </article>
        <article class="arrival-fact">
          <span>From the fort entrance</span>
          <strong>3–4 min walk</strong>
          <p>About 200 metres from the fort entrance to the haveli.</p>
        </article>
        <article class="arrival-fact">
          <span>Reception</span>
          <strong>7:00 AM–10:30 PM</strong>
          <p>Porter and escort help is available during these hours. Share your ETA by call or WhatsApp. Please share your ETA by phone or WhatsApp before arrival.</p>
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
          <strong>Meet the team</strong>
          <span>With notice, or when you call on arrival, staff can meet you and help with luggage.</span>
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
          <p>A private sedan to Fort Parking is listed at about ₹1100 and about 20 minutes. A porter can meet you when you reach the parking area.</p>
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
            In those hours, rickshaws are often not allowed inside the fort because the lanes are narrow and crowded. The team can meet you at Fort Parking, walk you in, and help with luggage. Outside those hours, rickshaws often go inside the fort.
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
        <p>The fort lanes are narrow and uneven in places. Porter and escort help is available from 7:00 AM to 10:30 PM. Asking ahead is recommended.</p>
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
          <strong>Help offered</strong>
          <span>Luggage help and a meet-and-assist at Fort Parking.</span>
        </li>
      </ul>
    </div>
  </section>

  <section class="section arrival-contact">
    <div class="site-shell arrival-split">
      <div>
        <p class="eyebrow">Check-in</p>
        <h2>Reception is open <span>7:00 AM–10:30 PM.</span></h2>
        <p><strong>Check-in is at 13:00.</strong> If you arrive earlier, you can use the rooftop terrace while the room is prepared. For arrivals between 10:30 PM and 6:30 AM, tell the hotel at least one day ahead so someone can meet you. If a late train is unexpected, call.</p>
        <div class="actions">
          <a class="button button-primary" href="contact.php">Contact the hotel</a>
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
        <p>If your train or flight changes, call, WhatsApp, or send a note through the contact page.</p>
      </div>
      <div class="faq-list">
        <details>
          <summary>Can my car enter the fort?</summary>
          <p>No. Park at Fort Parking. The hotel is about a 4 minute walk from there.</p>
        </details>
        <details>
          <summary>Can you meet me at the parking area?</summary>
          <p>Yes. Meet-and-assist is available with prior notice, or when you call on arrival.</p>
        </details>
        <details>
          <summary>Are rickshaws allowed inside?</summary>
          <p>Not during busy hours, about 8:00 AM–8:00 PM. Outside those times, rickshaws often go inside the fort.</p>
        </details>
        <details>
          <summary>Can I check in early if I arrive before 13:00?</summary>
          <p>Early arrivals are welcome. If the room is ready, you can check in then. If it is not, use the rooftop terrace or leave your luggage until the room is ready.</p>
        </details>
        <details>
          <summary>What if I arrive very early in the morning?</summary>
          <p>Leave your bags and spend time in the fort. Tell the hotel ahead if you want an early check-in. The team will try to arrange it.</p>
        </details>
        <details>
          <summary>Is there a restaurant on site?</summary>
          <p>The rooftop restaurant serves light meals, snacks, and drinks until 10:30 PM. Breakfast is available daily. See the <a href="roof.php">rooftop dining page</a> for the dining space.</p>
        </details>
        <details>
          <summary>I am arriving late at night by train. What should I do?</summary>
          <p>If you expect to arrive between 10:30 PM and 6:30 AM, tell the hotel 24 hours ahead so staff can meet you. For an unexpected late arrival, call.</p>
        </details>
        <details>
          <summary>Do you help with luggage?</summary>
          <p>Yes. Luggage help is available to and from Fort Parking. Your driver can call a few minutes before you arrive.</p>
        </details>
        <details>
          <summary>Can I get a ride from the station or airport?</summary>
          <p>A rickshaw from the railway station is listed at about ₹300. A private sedan from the airport to Fort Parking is listed at about ₹1100. Call or WhatsApp to arrange it.</p>
        </details>
      </div>
    </div>
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

  <section class="section final-cta">
    <div class="site-shell final-cta-inner">
      <p class="eyebrow">Plan your stay</p>
      <h2>Stay at Garh Jaisal Haveli.</h2>
      <p>Check availability, or ask the team about your arrival time.</p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
        <a class="button final-contact-button" href="contact.php">Contact us</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
