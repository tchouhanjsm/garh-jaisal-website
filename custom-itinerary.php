<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$whatsappUrl = rtrim((string) ($siteConfig['whatsapp_url'] ?? ''), '/');
$canonical = $siteUrl . '/custom-itinerary.php';
require_once __DIR__ . '/include/schema.php';

$isRajasthan = ($_GET['mode'] ?? '') === 'rajasthan';
$journeyIdeas = [
    'custom-desert' => 'Custom desert day: ask about sunrise or sunset, jeep travel or camel ride, a camel-free option, photography, village heritage, stargazing, private dinner or a longer desert outing.',
    'golden-city' => 'Golden City Escape: enquire about a heritage stay, guided sightseeing and a sunset safari.',
    'fort-desert' => 'Fort to Desert: enquire about a Fort stay and an overnight desert experience.',
    'romantic' => 'Romantic Jaisalmer: enquire about a heritage stay, private desert or walking experiences and rooftop dining.',
    'family' => 'Family Desert Holiday: enquire about a heritage stay, a relaxed pace, suitable transport and desert activities.',
    'festive' => 'Festive Jaisalmer: enquire about a heritage stay, dining and experiences for Diwali, Christmas or New Year.',
    'sunrise-walk' => 'Sunrise heritage or photography walk; ask about routes, timings and guide availability.',
    'food-crafts' => 'Local food, artisans and bazaars walk; ask about food stops, dietary needs, crafts and guide availability.',
    'desert-heritage' => 'Kuldhara, Khaba and desert heritage: ask about a feasible private car route and visitor access.',
    'temples-royal' => 'Lodurva, Amar Sagar and Bada Bagh: ask about a feasible private car route and visitor access.',
    'nature' => 'Ask about Akal Wood Fossil Park OR a Desert National Park nature excursion, separately as appropriate.',
];
$rajasthanIdeas = ['western-rajasthan' => 'Rajasthan Heritage Journey: Jaisalmer, Jodhpur and Bikaner, or suggest another multi-city route.'];
$ideaKey = is_string($_GET['idea'] ?? null) ? $_GET['idea'] : '';
$selectedIdea = ($isRajasthan ? $rajasthanIdeas : $journeyIdeas)[$ideaKey] ?? '';
$builderTitle = $isRajasthan
    ? 'Rajasthan Tour Enquiry | Garh Jaisal Haveli'
    : 'Build a Custom Jaisalmer Itinerary | Garh Jaisal Haveli';
$builderDescription = $isRajasthan
    ? 'Ask about a Rajasthan route using your preferred stops, duration and pickup details. Request a quote with inclusions for your travel dates.'
    : 'Shape a Jaisalmer itinerary around your interests. Combine ideas or add your own places, then ask Garh Jaisal Haveli to confirm what may be arranged.';

$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Packages', 'href' => '/pack.php'],
    ['label' => 'Car & City Tours', 'href' => '/car-tours.php'],
    ['label' => $isRajasthan ? 'Rajasthan tour enquiry' : 'Build your own route'],
];
$page = [
    'title' => $builderTitle,
    'description' => $builderDescription,
    'path' => '/custom-itinerary.php',
    'robots' => $isRajasthan ? 'noindex,follow' : 'index,follow',
    'og_title' => $builderTitle,
    'og_description' => $isRajasthan ? $builderDescription : 'Describe your places, interests and time available. Prepare an enquiry for the hotel to review; this is not a booking or confirmed tour.',
    'og_image' => '/img/jcity.jpg',
    'og_image_alt' => 'View over Jaisalmer’s historic city from the fort edge',
    'twitter_title' => $builderTitle,
    'twitter_description' => $isRajasthan ? $builderDescription : 'Combine interests and suggest your own places for a custom Jaisalmer itinerary enquiry.',
    'twitter_image' => '/img/jcity.jpg',
    'css' => ['css/custom-itinerary.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $builderTitle,
            'description' => $isRajasthan ? $builderDescription : 'Describe a custom itinerary enquiry around places and interests in Jaisalmer. All routes and arrangements require confirmation.',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
            'inLanguage' => 'en',
        ],
        gj_schema_breadcrumb_list($siteUrl, '/custom-itinerary.php', $breadcrumbs),
    ],
];
?>
<!doctype html>
<html lang="en">
<?php require __DIR__ . '/include/page-head.php'; ?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main id="main-content" class="custom-itinerary-page" tabindex="-1" data-custom-itinerary-builder data-itinerary-mode="<?= $isRajasthan ? 'rajasthan' : 'jaisalmer' ?>" data-whatsapp-url="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>">
  <section class="custom-itinerary-hero" aria-labelledby="custom-itinerary-title">
    <div class="site-shell">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <div class="custom-itinerary-hero-grid">
        <div>
          <p class="eyebrow">A route shaped around you</p>
          <?php if ($isRajasthan): ?>
            <h1 id="custom-itinerary-title">Enquire about a Rajasthan tour.</h1>
            <p class="custom-itinerary-lead">Share the places you want to visit, how many days you have and where the trip should start and end. Ask the hotel which route is possible and what the quote would include.</p>
          <?php else: ?>
            <h1 id="custom-itinerary-title">Build a Jaisalmer route around your interests.</h1>
            <p class="custom-itinerary-lead">Choose a few things you care about, add places you have in mind, and tell the hotel how much time you have. The examples on the previous page are only a starting point—not a fixed tour menu.</p>
          <?php endif; ?>
          <div class="actions">
            <a class="button button-primary" href="#custom-itinerary-form"><?= $isRajasthan ? 'Start your enquiry' : 'Shape your route' ?></a>
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
        <div><p class="eyebrow">Plan the conversation</p><h2 id="custom-itinerary-builder-title"><?= $isRajasthan ? 'What would you like your trip to include?' : 'What would you like to explore?' ?></h2></div>
        <p><?= $isRajasthan ? 'Fill in the details you know. You can ask the hotel to suggest the rest.' : 'Pick more than one interest or write your own request. You do not need to choose from the suggested themes only.' ?></p>
      </div>
      <div class="custom-itinerary-content-grid">
        <form id="custom-itinerary-form" class="custom-itinerary-form" autocomplete="off">
          <?php if ($isRajasthan): ?>
            <div class="custom-itinerary-fields">
              <div class="custom-itinerary-field">
                <label for="rajasthan-dates">Travel dates or month</label>
                <input id="rajasthan-dates" name="travel-dates" type="text" maxlength="100">
              </div>
              <div class="custom-itinerary-field">
                <label for="rajasthan-duration">Duration</label>
                <input id="rajasthan-duration" name="duration" type="text" maxlength="80" placeholder="e.g. 5 days or flexible">
              </div>
              <div class="custom-itinerary-field">
                <label for="rajasthan-pickup">Pickup city or location</label>
                <input id="rajasthan-pickup" name="pickup" type="text" maxlength="120">
              </div>
              <div class="custom-itinerary-field">
                <label for="rajasthan-dropoff">Drop-off city or location</label>
                <input id="rajasthan-dropoff" name="dropoff" type="text" maxlength="120">
              </div>
              <div class="custom-itinerary-field">
                <label for="custom-group-size">Number of travellers (optional)</label>
                <input id="custom-group-size" name="group-size" type="number" min="1" step="1" inputmode="numeric">
              </div>
              <div class="custom-itinerary-field">
                <label for="rajasthan-budget">Budget or pricing questions (optional)</label>
                <input id="rajasthan-budget" name="budget" type="text" maxlength="120" placeholder="Include currency if you have a budget">
              </div>
            </div>
            <div class="custom-itinerary-field">
              <label for="custom-places">Preferred route or places to visit</label>
              <textarea id="custom-places" name="places" rows="3" maxlength="700" placeholder="List any cities or stops you have in mind."><?= htmlspecialchars($selectedIdea, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="custom-itinerary-field">
              <label for="rajasthan-service">Travel arrangement</label>
              <select id="rajasthan-service" name="service">
                <option value="">Not decided yet</option>
                <option value="Private car with driver">Private car with driver</option>
                <option value="Private car with accommodation arranged">Private car with accommodation arranged</option>
              </select>
              <label for="rajasthan-inclusions">What should the quote include?</label>
              <textarea id="rajasthan-inclusions" name="inclusions" rows="3" maxlength="500" placeholder="Ask about transport, a guide, accommodation or entry fees."></textarea>
            </div>
          <?php else: ?>
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
            <textarea id="custom-places" name="places" rows="4" maxlength="700" placeholder="Add places, markets, architecture, landscapes or another idea. You are not limited to the examples above."><?= htmlspecialchars($selectedIdea, ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <div class="custom-itinerary-field">
            <label for="custom-preferences">Preferred pace or other requests (optional)</label>
            <textarea id="custom-preferences" name="preferences" rows="3" maxlength="500" placeholder="For example, time for photography, a relaxed pace or other preferences."></textarea>
          </div>
          <?php endif; ?>
          <p class="custom-itinerary-privacy-note">Your choices are used in this page to prepare a message. This builder does not send or save them to the website.</p>
        </form>
        <aside class="custom-itinerary-draft-panel" aria-labelledby="custom-itinerary-draft-title">
          <p class="eyebrow">Your enquiry draft</p>
          <h2 id="custom-itinerary-draft-title">Review before opening WhatsApp.</h2>
          <p>The message updates as you fill in the form. Check the details below before you open WhatsApp.</p>
          <label for="custom-itinerary-draft">Message preview</label>
          <textarea id="custom-itinerary-draft" rows="14" readonly aria-live="polite" aria-describedby="custom-itinerary-status"></textarea>
          <p id="custom-itinerary-status" class="custom-itinerary-status" role="status" aria-live="polite"><?= $isRajasthan ? 'Add at least one trip detail to prepare the message link.' : 'Select an interest or add a place/idea to prepare the message link.' ?></p>
          <a id="custom-itinerary-whatsapp" class="button button-primary custom-itinerary-whatsapp" href="#" target="_blank" rel="noopener noreferrer" aria-disabled="true" tabindex="-1">Open WhatsApp with this enquiry</a>
          <a class="custom-itinerary-contact-link" href="<?= $isRajasthan ? 'contact.php?topic=rajasthan-tours' : 'contact.php?topic=custom-itinerary' ?>">Use the contact page instead</a>
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
        <p><?= $isRajasthan ? 'Explore existing Jaisalmer tours while you plan the rest of your Rajasthan trip.' : 'Review the published guided city, Fort walk, desert and car-arrangement pages first, then use this builder to ask about combinations or other places.' ?></p>
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
