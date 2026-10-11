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
$analyticsIds = array_values(array_filter((array) ($siteConfig['analytics_ids'] ?? []), 'is_string'));
$canonical = $siteUrl . '/' . $experience['slug'] . '.php';

$breadcrumbs = [
    ['name' => 'Home', 'url' => $siteUrl . '/'],
    ['name' => 'Packages', 'url' => $siteUrl . '/pack.php'],
    ['name' => $experience['h1'], 'url' => $canonical],
];

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
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($experience['title'], ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($experience['description'], ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= htmlspecialchars($experience['title'], ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($experience['description'], ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($siteUrl . '/' . $experience['hero_image'], ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image:alt" content="<?= htmlspecialchars($experience['hero_alt'], ENT_QUOTES, 'UTF-8') ?>">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($experience['title'], ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($experience['description'], ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($siteUrl . '/' . $experience['hero_image'], ENT_QUOTES, 'UTF-8') ?>">

  <link rel="icon" type="image/png" href="img/jaisalmerlogo.png">
  <link rel="stylesheet" href="css/garh-v2.css">
  <link rel="stylesheet" href="css/experiences.css">

  <?php if ($analyticsIds): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($analyticsIds[0], ENT_QUOTES, 'UTF-8') ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    <?php foreach ($analyticsIds as $analyticsId): ?>
    gtag('config', '<?= htmlspecialchars($analyticsId, ENT_QUOTES, 'UTF-8') ?>');
    <?php endforeach; ?>
  </script>
  <?php endif; ?>

  <script type="application/ld+json">
  <?= json_encode([
      '@context' => 'https://schema.org',
      '@graph' => $schemaGraph,
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
</head>

<body>
<?php include __DIR__ . '/header-v2.php'; ?>

<main>
  <section class="experience-hero">
    <div class="experience-hero-media">
      <img
        src="<?= htmlspecialchars($experience['hero_image'], ENT_QUOTES, 'UTF-8') ?>"
        alt="<?= htmlspecialchars($experience['hero_alt'], ENT_QUOTES, 'UTF-8') ?>"
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
        <a class="button button-primary" href="contact.php">Ask about availability</a>
        <a class="button button-light" href="pack.php">View all experiences</a>
      </div>
    </div>
  </section>

  <section class="section experience-overview">
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

  <section class="section experience-itinerary">
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

  <section class="section experience-details">
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
    <section class="section experience-faq">
      <div class="site-shell">
        <div class="section-head">
          <div>
            <p class="eyebrow">Questions</p>
            <h2>Questions about this experience.</h2>
          </div>
          <p>These answers describe the current experience at a practical level. Confirm final timings, availability and inclusions for your travel date.</p>
        </div>

        <div class="faq-list">
          <?php foreach ($experience['faq'] as $faq): ?>
            <details class="faq-item">
              <summary><?= htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8') ?></summary>
              <div class="faq-answer">
                <p><?= htmlspecialchars($faq['answer'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($experience['related'])): ?>
    <section class="section related-experiences">
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
            <?php if (!isset($experiences[$relatedSlug])) { continue; } ?>
            <?php $related = $experiences[$relatedSlug]; ?>
            <article class="related-card">
              <a href="<?= htmlspecialchars($related['slug'], ENT_QUOTES, 'UTF-8') ?>.php">
                <span class="related-card-image">
                  <img src="<?= htmlspecialchars($related['hero_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($related['hero_alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
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

  <section class="section experience-cta">
    <div class="site-shell experience-cta-inner">
      <p class="eyebrow">Plan your stay</p>
      <h2>Stay inside Jaisalmer Fort, then explore beyond it.</h2>
      <p>Ask the hotel about the latest experience details, availability and arrangements for your travel date.</p>
      <div class="actions">
        <a class="button button-primary" href="contact.php">Contact us</a>
        <a class="button button-secondary" href="room.php">View rooms</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
