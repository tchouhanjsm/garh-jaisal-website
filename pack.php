<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
$experiences = require __DIR__ . '/include/experience-data.php';
$activePage = 'packages';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$analyticsIds = array_values(array_filter((array) ($siteConfig['analytics_ids'] ?? []), 'is_string'));
$canonical = $siteUrl . '/pack.php';
$visibleExperiences = array_filter($experiences, static function ($item) {
    return ($item['status'] ?? 'live') === 'live' && ($item['duration_type'] ?? '') !== 'hub';
});
$desertItems = array_keys(array_filter($visibleExperiences, static function ($item) {
    return ($item['category'] ?? '') === 'desert';
}));
$guideItems = array_keys(array_filter($visibleExperiences, static function ($item) {
    return ($item['category'] ?? '') === 'city';
}));
$sortExperienceSlugs = static function (&$slugs) use ($visibleExperiences) {
    usort($slugs, static function ($a, $b) use ($visibleExperiences) {
        return ($visibleExperiences[$a]['order'] ?? 999) <=> ($visibleExperiences[$b]['order'] ?? 999);
    });
};
$sortExperienceSlugs($desertItems);
$sortExperienceSlugs($guideItems);

$experienceCardCount = count($desertItems) + count($guideItems);
$experienceCategories = [];
$experienceDurations = [];
foreach (array_merge($desertItems, $guideItems) as $slug) {
    $item = $experiences[$slug];
    $category = (string) ($item['category'] ?? '');
    $duration = (string) ($item['duration_type'] ?? '');
    if ($category !== '' && !in_array($category, $experienceCategories, true)) {
        $experienceCategories[] = $category;
    }
    if ($duration !== '' && $duration !== 'hub' && !in_array($duration, $experienceDurations, true)) {
        $experienceDurations[] = $duration;
    }
}
$durationOrder = ['half-day' => 10, 'full-day' => 20, 'overnight' => 30];
usort($experienceDurations, static function ($left, $right) use ($durationOrder) {
    return ($durationOrder[$left] ?? 999) <=> ($durationOrder[$right] ?? 999);
});
$experienceDurationLabel = static function ($duration) {
    $labels = [
        'half-day' => 'Half-day',
        'full-day' => 'Full-day',
        'overnight' => 'Overnight',
    ];
    return $labels[$duration] ?? ucwords(str_replace('-', ' ', (string) $duration));
};
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Packages'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($siteUrl, '/pack.php', $breadcrumbs);
$faqItems = [
    ['question' => "How should I choose an experience?", 'answer' => "Start with how much time you have and whether you want to return to Jaisalmer the same evening or stay overnight."],
    ['question' => "How do I confirm current prices and availability?", 'answer' => "Contact the hotel for the current price, availability, route, pickup arrangement, guide availability and exact inclusions for your travel date."],
    ['question' => "Can I combine a city tour with a desert safari?", 'answer' => "The page lists city and desert options separately. Contact the hotel to check the timing and arrangements for your dates before planning both."],
];
$page = [
    'title' => 'Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli',
    'description' => 'Explore desert safaris and guided Jaisalmer tours from Garh Jaisal Haveli, including camel safaris, overnight stays and half-day or full-day city tours.',
    'path' => '/pack.php',
    'og_title' => 'Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli',
    'og_description' => 'Choose from desert safari experiences and guided Jaisalmer tours arranged from Garh Jaisal Haveli inside Jaisalmer Fort.',
    'og_image' => '/img/desert11.jpg',
    'og_image_alt' => 'A camel rider travelling across sand dunes in the Thar Desert',
    'twitter_title' => 'Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli',
    'twitter_description' => 'Choose from desert safari experiences and guided Jaisalmer tours arranged from Garh Jaisal Haveli inside Jaisalmer Fort.',
    'twitter_image' => '/img/desert11.jpg',
    'css' => ['css/packages.css', 'css/experiences.css', 'css/thar-sunset-story.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Jaisalmer Desert Experiences & Tours | Garh Jaisal Haveli',
            'description' => 'Explore desert safari and guided Jaisalmer tour options arranged from Garh Jaisal Haveli.',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        $breadcrumbSchema,
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" class="thar-sunset-story thar-sunset-story--experiences" tabindex="-1">
  <section class="package-hero">
    <div class="package-hero-media">
      <img
        src="img/desert11.jpg"
        alt="A camel rider travelling across sand dunes in the Thar Desert"
        <?= gj_image_dimension_attributes('img/desert11.jpg', '100vw') ?>
        fetchpriority="high"
       decoding="async">
      <div class="package-hero-overlay"></div>
    </div>

    <div class="site-shell package-hero-content">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <p class="eyebrow">Packages and experiences</p>
      <h1>Jaisalmer desert experiences and tours.</h1>
      <p>
        Stay inside Jaisalmer Fort, then choose a desert safari or a guided tour
        that fits the time you have in Jaisalmer.
      </p>
      <div class="actions">
        <a class="button button-primary" href="#experience-categories">Explore tour categories</a>
        <a class="button button-light" href="catalogue.php">Build your trip</a>
      </div>
    </div>
  </section>

  <section class="section experience-map-section" id="experience-categories" data-story-reveal aria-labelledby="experience-categories-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Choose your experience</p>
          <h2 id="experience-categories-title">Start with desert or city experiences.</h2>
        </div>
        <p>Open a category to compare the available options, then contact us to confirm current details for your dates.</p>
      </div>

      <div class="package-category-grid" role="group" aria-label="Tour categories">
        <article class="package-category-card package-category-card--desert">
          <p class="package-category-kicker">01 / Desert experiences</p>
          <h3>Explore the Thar Desert</h3>
          <p>Compare sunset camel safaris, cultural desert evenings and overnight stays under the stars or in a Swiss tent.</p>
          <a class="package-category-link" href="desert-safari.php">Explore Desert Tours <span aria-hidden="true">→</span></a>
        </article>
        <article class="package-category-card package-category-card--city">
          <p class="package-category-kicker">02 / City experiences</p>
          <h3>Discover Jaisalmer</h3>
          <p>Explore guided half-day or full-day city tours, walk inside the Fort, and ask about car arrangements.</p>
          <a class="package-category-link" href="car-tours.php">Explore Car &amp; City Tours <span aria-hidden="true">→</span></a>
        </article>
      </div>
    </div>
  </section>

  <section class="section experience-finder-section" data-experience-finder hidden aria-labelledby="experience-finder-title">
    <div class="site-shell">
      <div class="experience-finder-panel">
        <div class="experience-finder-heading">
          <p class="eyebrow">Narrow your options</p>
          <h2 id="experience-finder-title">Find an experience that fits your trip.</h2>
          <p>Search the published tour descriptions, then narrow by category or the duration type listed for each experience.</p>
        </div>

        <div class="experience-finder-controls">
          <label class="experience-finder-search">
            <span>Search experiences</span>
            <input type="search" data-experience-search placeholder="Try “camel safari” or “Swiss tent”" autocomplete="off">
          </label>

          <label class="experience-finder-category">
            <span>Category</span>
            <select data-experience-category>
              <option value="all">All categories</option>
              <?php foreach ($experienceCategories as $category): ?>
                <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($category), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label class="experience-finder-duration">
            <span>Duration type</span>
            <select data-experience-duration>
              <option value="all">Any duration</option>
              <?php foreach ($experienceDurations as $duration): ?>
                <option value="<?= htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($experienceDurationLabel($duration), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <button class="experience-finder-reset" type="button" data-experience-reset>Clear filters</button>
        </div>

        <p class="experience-finder-status" data-experience-result-status role="status" aria-live="polite"><?= $experienceCardCount ?> experiences to explore.</p>
        <div class="experience-finder-empty" data-experience-empty hidden>
          <p><strong>No experiences match those filters.</strong> Try a different search or clear the filters to see all published options.</p>
          <button class="experience-finder-reset" type="button" data-experience-reset>Clear filters</button>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="desert-options" data-story-reveal aria-labelledby="desert-options-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Desert safari</p>
          <h2 id="desert-options-title">Explore the desert options.</h2>
        </div>
        <a class="text-link" href="desert-safari.php">View all desert options →</a>
      </div>

      <div class="experience-card-grid">
        <?php foreach ($desertItems as $slug): $item = $experiences[$slug]; ?>
          <article class="experience-card" data-experience-filter-card data-experience-category="<?= htmlspecialchars((string) ($item['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-experience-duration="<?= htmlspecialchars((string) ($item['duration_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <a href="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>.php">
              <img src="<?= htmlspecialchars($item['hero_image'], ENT_QUOTES, 'UTF-8') ?>" <?= gj_image_dimension_attributes($item['hero_image'], '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="<?= htmlspecialchars($item['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
              <div>
                <p class="experience-duration-label"><?= htmlspecialchars($experienceDurationLabel((string) ($item['duration_type'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="experience-card-kicker"><?= htmlspecialchars($item['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
                <h3><?= htmlspecialchars($item['h1'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                <span>View itinerary →</span>
              </div>
            </a>
            <?php
              $tripItem = ['id' => $slug, 'title' => $item['h1'], 'url' => $slug . '.php'];
              $tripButtonClass = 'button button-secondary';
              require __DIR__ . '/include/ui/trip-add-button.php';
            ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section route-planner" id="city-options" data-story-reveal aria-labelledby="city-options-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Car &amp; City Tours</p>
          <h2 id="city-options-title">Choose a guided walk or ask about car arrangements.</h2>
        </div>
        <a class="text-link" href="car-tours.php">View Car &amp; City Tours →</a>
      </div>

      <div class="experience-card-grid experience-card-grid--two">
        <?php foreach ($guideItems as $slug): $item = $experiences[$slug]; ?>
          <article class="experience-card" data-experience-filter-card data-experience-category="<?= htmlspecialchars((string) ($item['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-experience-duration="<?= htmlspecialchars((string) ($item['duration_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <a href="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>.php">
              <img src="<?= htmlspecialchars($item['hero_image'], ENT_QUOTES, 'UTF-8') ?>" <?= gj_image_dimension_attributes($item['hero_image'], '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="<?= htmlspecialchars($item['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
              <div>
                <p class="experience-duration-label"><?= htmlspecialchars($experienceDurationLabel((string) ($item['duration_type'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                <p class="experience-card-kicker"><?= htmlspecialchars($item['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
                <h3><?= htmlspecialchars($item['h1'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                <span>View itinerary →</span>
              </div>
            </a>
            <?php
              $tripItem = ['id' => $slug, 'title' => $item['h1'], 'url' => $slug . '.php'];
              $tripButtonClass = 'button button-secondary';
              require __DIR__ . '/include/ui/trip-add-button.php';
            ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section packages-planning" id="plan-by-time" data-story-reveal>
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Plan your stay</p>
          <h2>Start with the time you have.</h2>
        </div>
        <p>
          You do not need to fit every experience into one trip. Use the route
          below to narrow down the options, then ask us for the current details.
        </p>
      </div>

      <div class="planner-grid">
        <article><strong>4 hours</strong><p>Choose the half-day guided Jaisalmer tour when your time is limited.</p></article>
        <article><strong>One evening</strong><p>Choose a sunset camel safari or cultural desert safari and return to Jaisalmer.</p></article>
        <article><strong>One night</strong><p>Choose sleeping under the stars or a Swiss tent depending on the overnight setup you prefer.</p></article>
        <article><strong>Full day</strong><p>Choose the full-day guided city tour or a broader desert experience.</p></article>
      </div>

      <div class="package-note">
        <p><strong>Current rates and timings:</strong> Confirm the price, availability, route, pickup arrangement, guide availability and exact inclusions for your travel date before booking.</p>
      </div>
    </div>
  </section>



  <section class="section package-guide-section" id="planning-guides" data-story-reveal aria-labelledby="planning-guides-title">
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">Plan before you enquire</p>
          <h2 id="planning-guides-title">Use a guide to shape your Jaisalmer visit.</h2>
        </div>
        <p>These practical guides help you compare the Fort, desert and short-stay options. Confirm travel-date details with the hotel before relying on timings, routes or inclusions.</p>
      </div>

      <div class="experience-card-grid package-guide-grid">
        <article class="experience-card">
          <a href="jaisalmer-fort-guide.php">
            <img src="img/j7.jpg" <?= gj_image_dimension_attributes('img/j7.jpg', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="Jaisalmer Fort rising above a public courtyard" loading="lazy" decoding="async">
            <div>
              <p class="experience-card-kicker">Fort &amp; arrival</p>
              <h3>Explore Jaisalmer Fort</h3>
              <p>Understand the living Fort, the final walk from Fort Parking, and ways to combine heritage sightseeing with your stay.</p>
              <span>Read the Fort guide →</span>
            </div>
          </a>
        </article>
        <article class="experience-card">
          <a href="desert-safari-guide.php">
            <img src="img/desert3.jpg" <?= gj_image_dimension_attributes('img/desert3.jpg', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="Camel riders travelling over the dunes near Jaisalmer" loading="lazy" decoding="async">
            <div>
              <p class="experience-card-kicker">Desert choices</p>
              <h3>Compare desert experiences</h3>
              <p>Compare same-evening and overnight options, then confirm the route and package inclusions for your travel date.</p>
              <span>Compare desert options →</span>
            </div>
          </a>
        </article>
        <article class="experience-card">
          <a href="jaisalmer-2-day-itinerary.php">
            <img src="img/1.webp" <?= gj_image_dimension_attributes('img/1.webp', '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="Panoramic view across Jaisalmer&#x27;s sandstone city" loading="lazy" decoding="async">
            <div>
              <p class="experience-card-kicker">Short-stay planning</p>
              <h3>Plan two days in Jaisalmer</h3>
              <p>Use a flexible outline for Fort time, guided sightseeing, rooftop dining and a desert experience.</p>
              <span>Read the 2-day itinerary →</span>
            </div>
          </a>
        </article>
      </div>

      <aside class="package-guide-service" aria-labelledby="package-guide-service-title">
        <div class="package-guide-service-copy">
          <p class="eyebrow">Need a vehicle?</p>
          <h3 id="package-guide-service-title">Ask about SUV and sedan arrangements.</h3>
          <p>Share your travel date, route, pickup point, travellers and luggage so the hotel can confirm vehicle availability and a current quote.</p>
        </div>
        <div class="actions">
          <a class="button button-primary" href="car-rental.php">Explore car arrangements</a>
        </div>
      </aside>
    </div>
  </section>

  <?php require __DIR__ . '/include/ui/stay-booking-note.php'; ?>

  <section class="section site-faq packages-faq" data-story-reveal aria-labelledby="packages-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="packages-faq-title">Questions about Jaisalmer experiences.</h2></div>
        <p>Use these answers as a starting point; confirm the arrangements for your own travel dates.</p>
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
<section class="section packages-cta" data-story-reveal>
    <div class="site-shell">
      <div class="packages-cta-inner">
        <p class="eyebrow">Need help choosing?</p>
        <h2>Tell us how much time you have in Jaisalmer.</h2>
        <p>We can help you compare the desert and city experiences available for your dates.</p>
        <div class="actions">
          <a class="button button-primary" href="contact.php">Contact us</a>
          <a class="button button-secondary" href="direction.php">Plan your arrival</a>
        </div>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/include/ui/planning-path.php'; ?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
