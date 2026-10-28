<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$whatsappUrl = rtrim((string) ($siteConfig['whatsapp_url'] ?? ''), '/');
$canonical = $siteUrl . '/custom-itinerary.php';
require_once __DIR__ . '/include/schema.php';

$faqItems = [
    ['question' => 'Are these confirmed tours or bookable packages?', 'answer' => 'No. The page helps you describe an itinerary enquiry. The hotel must confirm whether a route can be arranged, current access and visitor rules, transport and guide availability, timings, applicable fees and the final price. Sending an enquiry does not reserve a tour.'],
    ['question' => 'Can I ask about places beyond the suggested themes?', 'answer' => 'Yes. The interest choices are starting points, not a complete catalogue. Add any other places or ideas in the notes so the hotel can review the request.'],
    ['question' => 'Does the form submit or save my travel details?', 'answer' => 'No. Your choices stay in this browser while you use the page. It prepares a WhatsApp message only when you choose to open it; nothing is submitted or reserved by this page.'],
];
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Packages', 'href' => '/pack.php'],
    ['label' => 'Car & City Tours', 'href' => '/car-tours.php'],
    ['label' => 'Build your own route'],
];
$page = [
    'title' => 'Build a Custom Jaisalmer Itinerary | Garh Jaisal Haveli',
    'description' => 'Shape a Jaisalmer itinerary around your interests. Combine ideas or add your own places, then ask Garh Jaisal Haveli to confirm what may be arranged.',
    'path' => '/custom-itinerary.php',
    'og_title' => 'Build a Custom Jaisalmer Itinerary | Garh Jaisal Haveli',
    'og_description' => 'Describe your places, interests and time available. Prepare an enquiry for the hotel to review; this is not a booking or confirmed tour.',
    'og_image' => '/img/jcity.jpg',
    'og_image_alt' => 'View over Jaisalmer’s historic city from the fort edge',
    'twitter_title' => 'Build a Custom Jaisalmer Itinerary | Garh Jaisal Haveli',
    'twitter_description' => 'Combine interests and suggest your own places for a custom Jaisalmer itinerary enquiry.',
    'twitter_image' => '/img/jcity.jpg',
    'css' => ['css/custom-itinerary.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Build a Custom Jaisalmer Itinerary | Garh Jaisal Haveli',
            'description' => 'Describe a custom itinerary enquiry around places and interests in Jaisalmer. All routes and arrangements require confirmation.',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
            'inLanguage' => 'en',
        ],
        gj_schema_breadcrumb_list($siteUrl, '/custom-itinerary.php', $breadcrumbs),
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/include/page-head.php'; ?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main id="main-content" class="custom-itinerary-page" tabindex="-1" data-custom-itinerary-builder data-whatsapp-url="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>">
  <section class="custom-itinerary-hero" aria-labelledby="custom-itinerary-title">
    <div class="site-shell">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <div class="custom-itinerary-hero-grid">
        <div>
          <p class="eyebrow">A route shaped around you</p>
          <h1 id="custom-itinerary-title">Build a Jaisalmer route around your interests.</h1>
          <p class="custom-itinerary-lead">Choose a few things you care about, add places you have in mind, and tell the hotel how much time you have. The examples on the previous page are only a starting point—not a fixed tour menu.</p>
          <div class="actions">
            <a class="button button-primary" href="#custom-itinerary-form">Shape your route</a>
            <a class="button button-secondary" href="car-tours.php">Explore current tour pages</a>
          </div>
        </div>
        <aside class="custom-itinerary-hero-note" aria-labelledby="custom-itinerary-note-title">
          <p class="eyebrow">Before you enquire</p>
          <h2 id="custom-itinerary-note-title">A useful starting point, not a reservation.</h2>
          <p>Your selections prepare a message for the hotel. The hotel must confirm route feasibility, visitor access, transport and guide availability, timings, applicable fees and price for your date.</p>
        </aside>
      </div>
    </div>
  </section>
  <section class="section custom-itinerary-builder" aria-labelledby="custom-itinerary-builder-title" id="route-builder">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Plan the conversation</p><h2 id="custom-itinerary-builder-title">What would you like to explore?</h2></div>
        <p>Pick more than one interest or write your own request. You do not need to choose from the suggested themes only.</p>
      </div>
      <div class="custom-itinerary-content-grid">
        <form id="custom-itinerary-form" class="custom-itinerary-form" autocomplete="off">
          <fieldset class="custom-itinerary-fieldset">
            <legend>Interests to explore</legend>
            <p class="custom-itinerary-field-help" id="custom-itinerary-interest-help">These are broad interests, not confirmed tour products. Select any combination.</p>
            <div class="custom-itinerary-interest-grid">
              <label class="custom-itinerary-interest" for="interest-fort"><input id="interest-fort" type="checkbox" name="interests" value="Fort and historic architecture"><span>Fort &amp; historic architecture</span></label>
              <label class="custom-itinerary-interest" for="interest-havelis"><input id="interest-havelis" type="checkbox" name="interests" value="Havelis and old-city lanes"><span>Havelis &amp; old-city lanes</span></label>
              <label class="custom-itinerary-interest" for="interest-temples"><input id="interest-temples" type="checkbox" name="interests" value="Jain temples and sacred architecture"><span>Jain temples &amp; sacred architecture</span></label>
              <label class="custom-itinerary-interest" for="interest-markets"><input id="interest-markets" type="checkbox" name="interests" value="Markets, crafts and local life"><span>Markets, crafts &amp; local life</span></label>
              <label class="custom-itinerary-interest" for="interest-photography"><input id="interest-photography" type="checkbox" name="interests" value="Lakes, cenotaphs and photography"><span>Lakes, cenotaphs &amp; photography</span></label>
              <label class="custom-itinerary-interest" for="interest-desert"><input id="interest-desert" type="checkbox" name="interests" value="Desert landscapes and heritage"><span>Desert landscapes &amp; heritage</span></label>
              <label class="custom-itinerary-interest" for="interest-nature"><input id="interest-nature" type="checkbox" name="interests" value="Nature and geology"><span>Nature &amp; geology</span></label>
              <label class="custom-itinerary-interest" for="interest-history"><input id="interest-history" type="checkbox" name="interests" value="Military and regional history"><span>Military &amp; regional history</span></label>
              <label class="custom-itinerary-interest" for="interest-food"><input id="interest-food" type="checkbox" name="interests" value="Food and local flavours"><span>Food &amp; local flavours</span></label>
              <label class="custom-itinerary-interest" for="interest-other"><input id="interest-other" type="checkbox" name="interests" value="A different idea or a mix of interests"><span>A different idea or a mix</span></label>
            </div>
          </fieldset>
          <div class="custom-itinerary-fields">
            <div class="custom-itinerary-field">
              <label for="custom-travel-date">Travel date (optional)</label>
              <input id="custom-travel-date" name="travel-date" type="date">
            </div>
            <div class="custom-itinerary-field">
              <label for="custom-time-available">How much time do you have?</label>
              <select id="custom-time-available" name="time-available">
                <option value="">Choose if you know</option>
                <option value="A few hours">A few hours</option><option value="Half a day">Half a day</option><option value="A full day">A full day</option><option value="Two days or more">Two days or more</option><option value="Flexible">Flexible / not sure yet</option>
              </select>
            </div>
            <div class="custom-itinerary-field">
              <label for="custom-group-size">Number of travellers (optional)</label>
              <input id="custom-group-size" name="group-size" type="number" min="1" step="1" inputmode="numeric" placeholder="e.g. 2">
            </div>
            <div class="custom-itinerary-field">
              <label for="custom-travel-style">Preferred travel style</label>
              <select id="custom-travel-style" name="travel-style">
                <option value="">Choose if you know</option><option value="Mostly on foot">Mostly on foot</option><option value="A mix of walking and transport">A mix of walking and transport</option><option value="Ask about vehicle arrangements">Ask about vehicle arrangements</option><option value="Open to suggestions">Open to suggestions</option>
              </select>
              <p class="custom-itinerary-field-help">Vehicle and guide arrangements are subject to confirmation.</p>
            </div>
          </div>
          <div class="custom-itinerary-field">
            <label for="custom-places">Places or ideas you want to include</label>
            <textarea id="custom-places" name="places" rows="4" maxlength="700" placeholder="Add places, markets, architecture, landscapes or another idea. You are not limited to the examples above."></textarea>
          </div>
          <div class="custom-itinerary-field">
            <label for="custom-preferences">Preferred pace or other requests (optional)</label>
            <textarea id="custom-preferences" name="preferences" rows="3" maxlength="500" placeholder="For example, time for photography, a relaxed pace or other preferences."></textarea>
          </div>
          <p class="custom-itinerary-privacy-note">Your choices are used in this page to prepare a message. This builder does not send or save them to the website.</p>
        </form>
        <aside class="custom-itinerary-draft-panel" aria-labelledby="custom-itinerary-draft-title">
          <p class="eyebrow">Your enquiry draft</p>
          <h2 id="custom-itinerary-draft-title">Review before opening WhatsApp.</h2>
          <p>The message updates as you fill in the form. Check the details below before you open WhatsApp.</p>
          <label for="custom-itinerary-draft">Message preview</label>
          <textarea id="custom-itinerary-draft" rows="14" readonly aria-live="polite" aria-describedby="custom-itinerary-status"></textarea>
          <p id="custom-itinerary-status" class="custom-itinerary-status" role="status" aria-live="polite">Select an interest or add a place/idea to prepare the message link.</p>
          <a id="custom-itinerary-whatsapp" class="button button-primary custom-itinerary-whatsapp" href="#" target="_blank" rel="noopener noreferrer" aria-disabled="true" tabindex="-1">Open WhatsApp with this enquiry</a>
          <a class="custom-itinerary-contact-link" href="contact.php?topic=custom-itinerary">Use the contact page instead</a>
          <p class="custom-itinerary-booking-note">This is an enquiry only. It does not book a tour or confirm availability.</p>
        </aside>
      </div>
    </div>
  </section>
  <section class="section custom-itinerary-next" aria-labelledby="custom-itinerary-next-title">
    <div class="site-shell custom-itinerary-next-inner">
      <div>
        <p class="eyebrow">Compare before you decide</p>
        <h2 id="custom-itinerary-next-title">Prefer to start with an existing page?</h2>
        <p>Review the published guided city, Fort walk, desert and car-arrangement pages first, then use this builder to ask about combinations or other places.</p>
      </div>
      <div class="actions"><a class="button button-secondary" href="guided-jaisalmer-tour.php">Guided city tours</a><a class="button button-secondary" href="desert-safari.php">Desert experiences</a><a class="button button-secondary" href="car-rental.php">Car arrangements</a></div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
<script src="js/custom-itinerary.js"></script>
</body>
</html>
