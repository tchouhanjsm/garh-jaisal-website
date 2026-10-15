<?php
/**
 * Shared renderer for individual experience pages.
 * Set $experienceSlug before including this file.
 */

$siteConfig = require __DIR__ . '/site-config.php';
$experiences = require __DIR__ . '/experience-data.php';

$experienceSlug = $experienceSlug ?? '';
$experience = $experiences[$experienceSlug] ?? null;

if (!$experience) {
    http_response_code(404);
    $experience = [
        'slug' => 'not-found',
        'title' => 'Experience not found | Garh Jaisal Haveli',
        'description' => 'The requested experience could not be found.',
        'h1' => 'Experience not found',
        'eyebrow' => 'Garh Jaisal Haveli',
        'hero_image' => 'img/v2/facade.jpg',
        'hero_width' => 1333,
        'hero_height' => 2000,
        'hero_alt' => 'Garh Jaisal Haveli inside Jaisalmer Fort',
        'summary' => 'The experience you requested is not available.',
        'at_a_glance' => [],
        'timeline_label' => 'Itinerary',
        'timeline_note' => '',
        'timeline' => [],
        'included' => [],
        'not_included' => [],
        'best_for' => '',
        'before_book' => '',
        'related' => [],
    ];
}

$activePage = 'packages';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/' . $experience['slug'] . '.php';

$breadcrumbs = [
    ['name' => 'Home', 'url' => $siteUrl . '/'],
    ['name' => 'Packages', 'url' => $siteUrl . '/pack.php'],
];
$parentSlug = $experience['parent'] ?? 'pack';
if ($parentSlug !== 'pack' && isset($experiences[$parentSlug]) && ($experiences[$parentSlug]['status'] ?? 'live') === 'live') {
    $breadcrumbs[] = [
        'name' => $experiences[$parentSlug]['h1'],
        'url' => $siteUrl . '/' . $parentSlug . '.php',
    ];
}
$breadcrumbs[] = ['name' => $experience['h1'], 'url' => $canonical];
$isIndexableExperience = ($experience['status'] ?? 'live') === 'live';
$experienceContactUrl = 'contact.php';
$experienceContactLabel = 'Ask about availability';
$experienceBottomContactLabel = 'Contact us';
$isWhatsAppCityTour = in_array($experience['slug'] ?? '', ['half-day-jaisalmer-tour', 'full-day-jaisalmer-tour'], true);
if ($isWhatsAppCityTour) {
    $message = 'Hello, I would like to enquire about the ' . $experience['h1'] . '. '
        . 'Travel date: [add date]. Preferred guide language: [add language]. '
        . 'Please confirm the current price, route, guide availability, start time and any applicable entry fees.';
    $experienceContactUrl = rtrim($siteConfig['whatsapp_url'], '/') . '?text=' . rawurlencode($message);
    $experienceContactLabel = 'Ask on WhatsApp';
    $experienceBottomContactLabel = 'Ask on WhatsApp';
} elseif ($isIndexableExperience && ($experience['duration_type'] ?? 'hub') !== 'hub' && !empty($experience['slug'])) {
    $experienceContactUrl .= '?experience=' . rawurlencode((string) $experience['slug']);
}

$schemaGraph = [
    [
        '@type' => 'WebPage',
        '@id' => $canonical . '#webpage',
        'url' => $canonical,
        'name' => $experience['title'],
        'description' => $experience['description'],
        'isPartOf' => ['@id' => $siteUrl . '/#website'],
        'about' => ['@id' => $siteUrl . '/#hotel'],
        'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        'inLanguage' => 'en',
        'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $siteUrl . '/' . $experience['hero_image']],
    ],
    [
        '@type' => 'BreadcrumbList',
        '@id' => $canonical . '#breadcrumb',
        'itemListElement' => [],
    ],
];

foreach ($breadcrumbs as $index => $crumb) {
    $schemaGraph[1]['itemListElement'][] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $crumb['name'],
        'item' => $crumb['url'],
    ];
}
?>
<!doctype html>
<html lang="en">
<?php
$page = [
    'title' => $experience['title'],
    'description' => $experience['description'],
    'path' => '/' . $experience['slug'] . '.php',
    'robots' => $isIndexableExperience ? 'index,follow' : 'noindex,follow',
    'og_type' => 'website',
    'og_title' => $experience['title'],
    'og_description' => $experience['description'],
    'og_image' => '/' . ltrim($experience['hero_image'], '/'),
    'og_image_alt' => $experience['hero_alt'],
    'twitter_title' => $experience['title'],
    'twitter_description' => $experience['description'],
    'twitter_image' => '/' . ltrim($experience['hero_image'], '/'),
    'css' => ['css/experiences.css', 'css/thar-sunset-story.css'],
    'schema' => $schemaGraph,
];
require __DIR__ . '/page-head.php';
?>

<body>
<?php include __DIR__ . '/header-v2.php'; ?>

<main id="main-content" class="thar-sunset-story thar-sunset-story--experience" tabindex="-1">
  <section class="experience-hero">
    <div class="experience-hero-media">
      <img
        src="<?= htmlspecialchars($experience['hero_image'], ENT_QUOTES, 'UTF-8') ?>"
        alt="<?= htmlspecialchars($experience['hero_alt'], ENT_QUOTES, 'UTF-8') ?>"
        <?= gj_image_dimension_attributes($experience['hero_image']) ?>
        fetchpriority="high"
      >
      <div class="experience-hero-overlay"></div>
    </div>

    <div class="site-shell experience-hero-content">
      <nav class="experience-breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
          <?php if ($index > 0): ?><span aria-hidden="true">/</span><?php endif; ?>
          <?php if ($index === count($breadcrumbs) - 1): ?>
            <span aria-current="page"><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php else: ?>
            <a href="<?= htmlspecialchars(str_replace($siteUrl . '/', '', $crumb['url']) ?: './', ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>

      <p class="eyebrow"><?= htmlspecialchars($experience['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars($experience['h1'], ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($experience['summary'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($experienceContactUrl, ENT_QUOTES, 'UTF-8') ?>" <?= $isWhatsAppCityTour ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($experienceContactLabel, ENT_QUOTES, 'UTF-8') ?></a>
<?php if ($isIndexableExperience && ($experience['duration_type'] ?? 'hub') !== 'hub'): ?>
        <?php
        $tripItem = ['id' => $experience['slug'], 'title' => $experience['h1'], 'url' => $experience['slug'] . '.php'];
        $tripButtonClass = 'button button-light';
        require __DIR__ . '/ui/trip-add-button.php';
        ?>
<?php endif; ?>
        <a class="button button-light" href="catalogue.php">Build your trip</a>
      </div>
    </div>
  </section>

  <section class="section experience-overview" data-story-reveal>
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow">At a glance</p>
          <h2>Know what to expect.</h2>
        </div>
        <p><?= htmlspecialchars($experience['best_for'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <div class="glance-grid">
        <?php foreach ($experience['at_a_glance'] as $label => $value): ?>
          <div class="glance-item">
            <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($experience['gallery'])): ?>
    <section class="section experience-photo-gallery" data-story-reveal aria-labelledby="experience-photo-gallery-title">
      <div class="site-shell">
        <div class="section-head">
          <div><p class="eyebrow">Photo gallery</p><h2 id="experience-photo-gallery-title"><?= htmlspecialchars($experience['gallery_title'] ?? 'Explore the experience', ENT_QUOTES, 'UTF-8') ?></h2></div>
          <p><?= htmlspecialchars($experience['gallery_intro'] ?? 'Take a closer look at this experience.', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="experience-photo-grid" data-experience-gallery>
          <?php foreach ($experience['gallery'] as $photo): ?>
            <figure class="experience-photo-card">
              <img
                src="<?= htmlspecialchars($photo['src'], ENT_QUOTES, 'UTF-8') ?>"
                <?= gj_image_dimension_attributes($photo['src']) ?>
                alt="<?= htmlspecialchars($photo['alt'], ENT_QUOTES, 'UTF-8') ?>"
                loading="lazy"
                decoding="async"
              >
              <figcaption><?= htmlspecialchars($photo['caption'] ?? $photo['alt'], ENT_QUOTES, 'UTF-8') ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($experience['facilities'])): ?>
    <section class="section experience-facilities" data-story-reveal aria-labelledby="experience-facilities-title">
      <div class="site-shell">
        <div class="section-head">
          <div><p class="eyebrow">Comforts included</p><h2 id="experience-facilities-title">Facilities in every Swiss tent</h2></div>
          <p>Each tent is equipped with the everyday comforts you need for an overnight stay in the desert.</p>
        </div>
        <ul class="experience-facility-grid">
          <?php foreach ($experience['facilities'] as $facility): ?>
            <li><span class="experience-facility-check" aria-hidden="true">✓</span><span><?= htmlspecialchars($facility, ENT_QUOTES, 'UTF-8') ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
  <?php endif; ?>

  <section class="section experience-itinerary" data-story-reveal>
    <div class="site-shell">
      <div class="section-head">
        <div>
          <p class="eyebrow"><?= htmlspecialchars($experience['timeline_label'], ENT_QUOTES, 'UTF-8') ?></p>
          <h2>Follow the experience.</h2>
        </div>
        <p><?= htmlspecialchars($experience['timeline_note'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <ol class="experience-timeline">
        <?php foreach ($experience['timeline'] as $item): ?>
          <li>
            <span class="timeline-step"><?= htmlspecialchars($item['step'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
              <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>

  <section class="section experience-details" data-story-reveal>
    <div class="site-shell experience-details-grid">
      <div class="experience-detail-card">
        <p class="eyebrow">Included</p>
        <h2>What's included</h2>
        <ul>
          <?php foreach ($experience['included'] as $item): ?>
            <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="experience-detail-card">
        <p class="eyebrow">Before you book</p>
        <h2>Good to know</h2>
        <p><?= htmlspecialchars($experience['before_book'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (!empty($experience['not_included'])): ?>
          <h3>Not included</h3>
          <ul>
            <?php foreach ($experience['not_included'] as $item): ?>
              <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($experience['faq'])): ?>
    <section class="section experience-faq" data-story-reveal>
      <div class="site-shell">
        <?php
        $sectionHead = [
            'eyebrow' => 'Questions',
            'h2' => 'Questions about this experience.',
            'intro' => 'These answers describe the current experience at a practical level. Confirm final timings, availability and inclusions for your travel date.',
        ];
        require __DIR__ . '/ui/section-head.php';

        $faq = [
            'items' => $experience['faq'],
            'list_class' => 'faq-list',
            'item_class' => 'faq-item',
            'answer_wrapper_class' => 'faq-answer',
        ];
        require __DIR__ . '/ui/faq.php';
        ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($experience['related'])): ?>
    <section class="section related-experiences" data-story-reveal>
      <div class="site-shell">
        <div class="section-head">
          <div>
            <p class="eyebrow">Related experiences</p>
            <h2>Compare other ways to explore.</h2>
          </div>
          <a class="text-link" href="pack.php">See all packages →</a>
        </div>

        <div class="related-grid">
          <?php foreach ($experience['related'] as $relatedSlug): ?>
            <?php if (!isset($experiences[$relatedSlug]) || ($experiences[$relatedSlug]['status'] ?? 'live') !== 'live') { continue; } ?>
            <?php $related = $experiences[$relatedSlug]; ?>
            <article class="related-card">
              <a href="<?= htmlspecialchars($related['slug'], ENT_QUOTES, 'UTF-8') ?>.php">
                <span class="related-card-image">
                  <img src="<?= htmlspecialchars($related['hero_image'], ENT_QUOTES, 'UTF-8') ?>" <?= gj_image_dimension_attributes($related['hero_image']) ?> alt="<?= htmlspecialchars($related['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                </span>
                <span class="related-card-body">
                  <span class="related-card-kicker"><?= htmlspecialchars($related['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
                  <strong><?= htmlspecialchars($related['h1'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span>View experience →</span>
                </span>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="section experience-cta" data-story-reveal>
    <div class="site-shell experience-cta-inner">
      <p class="eyebrow">Plan your stay</p>
      <h2>Stay inside Jaisalmer Fort, then explore beyond it.</h2>
      <p>Ask the hotel about the latest experience details, availability and arrangements for your travel date.</p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($experienceContactUrl, ENT_QUOTES, 'UTF-8') ?>" <?= $isWhatsAppCityTour ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($experienceBottomContactLabel, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="button button-secondary" href="room.php">View rooms</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
