<?php
$activePage = 'packages';
$siteConfig = require __DIR__ . '/include/site-config.php';
$whatsappUrl = rtrim($siteConfig['whatsapp_url'], '/');
$walkWhatsAppUrl = $whatsappUrl . '?text=' . rawurlencode(
    'Hello, I would like to enquire about the guided walk inside Jaisalmer Fort, including the Jain temples, Palace Museum and heritage street walk. '
    . 'Preferred date: [add date]. Preferred guide language: [add language]. '
    . 'Please confirm the current price, guide availability, route, opening/access rules and any entry fees.'
);
?>
<!doctype html>
<html lang="en">
<?php
$siteUrl = rtrim($siteConfig['site_url'], '/');
$canonical = $siteUrl . '/city-tour.php';
require_once __DIR__ . '/include/schema.php';
$faqItems = [
    ['question' => "Which places are part of the Fort walk?", 'answer' => "The planned walk focuses on the Jain temples inside Jaisalmer Fort, the Palace Museum and a heritage street walk. The final order and access depend on opening hours and current visitor rules."],
    ['question' => "Which guide languages can I request, and how do I confirm the current price?", 'answer' => "Ask about English, Hindi, Bengali, Italian, French, Spanish or German. Message the hotel on WhatsApp with your date and preferred guide language to confirm availability, current price and any applicable entry fees."],
    ['question' => "Can I compare it with a longer guided tour?", 'answer' => "Yes. The Guided Jaisalmer Tours page links to half-day and full-day options, which can be discussed separately."],
];
$page = [
    'title' => 'Jaisalmer Fort Guided Walk | Garh Jaisal Haveli',
    'description' => 'Walk Jaisalmer Fort on a planned heritage route. Confirm guide availability, access, price and any entry fees with the hotel for your date.',
    'path' => '/city-tour.php',
    'og_title' => 'Jaisalmer Fort Guided Walk | Garh Jaisal Haveli',
    'og_description' => 'Walk Jaisalmer Fort on a planned heritage route. Confirm guide availability, access, price and any entry fees with the hotel for your date.',
    'og_image' => '/img/1.webp',
    'og_image_alt' => 'Historic sandstone architecture inside Jaisalmer Fort',
    'twitter_title' => 'Jaisalmer Fort Guided Walk | Garh Jaisal Haveli',
    'twitter_description' => 'Walk Jaisalmer Fort on a planned heritage route. Confirm guide availability, access, price and any entry fees with the hotel for your date.',
    'twitter_image' => '/img/1.webp',
    'css' => ['css/packages.css', 'css/tour.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => 'Jaisalmer Fort Guided Walk | Garh Jaisal Haveli',
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'A walk inside Jaisalmer Fort', 'item' => $canonical],
            ],
        ],
    ],
];
$page['schema'][] = gj_schema_faq($faqItems, $canonical);
require __DIR__ . '/include/page-head.php';
?>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main id="main-content" tabindex="-1">
  <nav class="section" aria-label="Breadcrumb"><div class="site-shell"><a href="car-tours.php">← Car &amp; City Tours</a></div></nav>
  <section class="page-hero page-hero--photo tour-detail-hero">
    <div class="page-hero-media" aria-hidden="true">
      <img
        src="img/1.webp"
        <?= gj_image_dimension_attributes('img/1.webp', '100vw') ?>
        alt=""
        fetchpriority="high"
        decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <p class="eyebrow">Guided Fort walk</p>
      <h1>Discover the stories inside Jaisalmer Fort.</h1>
      <p>Explore the living Fort on foot, with a route that may include Jain temples, the Palace Museum and heritage streets subject to current access and opening rules. Confirm the guide, route and any entry fees before you set out.</p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($walkWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ask about route, guide &amp; price on WhatsApp</a>
        <a class="button button-outline" href="guided-jaisalmer-tour.php">Compare half-day and full-day tours</a>
      </div>
    </div>
  </section>

  <section class="section tour-service-section" aria-labelledby="fort-walk-stops-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">The planned route</p><h2 id="fort-walk-stops-title">Three ways to experience the Fort.</h2></div>
        <p>The order, entry access and any admission charges should be confirmed against opening hours and visitor rules for your date.</p>
      </div>
      <div class="tour-service-grid">
        <article class="tour-service-card">
          <span class="tour-service-index" aria-hidden="true">01</span>
          <h3>Jain temples inside Jaisalmer Fort</h3>
          <p>Explore the Fort's Jain temple heritage and architectural detail, subject to current visitor access and temple rules.</p>
        </article>
        <article class="tour-service-card">
          <span class="tour-service-index" aria-hidden="true">02</span>
          <h3>Palace Museum</h3>
          <p>Include the Palace Museum in the planned heritage walk; confirm opening hours, access and any admission fee before the visit.</p>
        </article>
        <article class="tour-service-card">
          <span class="tour-service-index" aria-hidden="true">03</span>
          <h3>Heritage street walk</h3>
          <p>Continue through the Fort's lanes and streets to take in the living-fort setting, sandstone architecture and local life.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section tour-language-section" aria-labelledby="fort-walk-language-title">
    <div class="site-shell tour-language-layout">
      <div>
        <p class="eyebrow">Guide languages</p>
        <h2 id="fort-walk-language-title">Choose the language that works for you.</h2>
        <p>Ask about guide availability in your preferred language for the date of your visit.</p>
      </div>
      <ul class="tour-language-list" aria-label="Guide languages to enquire about">
        <li>English</li><li>Hindi</li><li>Bengali</li><li>Italian</li><li>French</li><li>Spanish</li><li>German</li>
      </ul>
    </div>
  </section>

  <section class="section tour-enquiry-panel">
    <div class="site-shell tour-enquiry-panel-inner">
      <p class="eyebrow">Confirm before you go</p>
      <h2>Plan your Fort walk with the details confirmed.</h2>
      <p>Send your date and preferred guide language. We will confirm the available route, guide availability, access rules, current price and any applicable entry fees before you decide.</p>
      <div class="actions">
        <a class="button button-primary" href="<?= htmlspecialchars($walkWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Enquire on WhatsApp</a>
        <a class="button button-secondary" href="direction.php">Plan your arrival</a>
      </div>
    </div>
  </section>

  <section class="section site-faq fort-walk-faq" aria-labelledby="fort-walk-faq-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Common questions</p><h2 id="fort-walk-faq-title">Questions about the Fort walk.</h2></div>
        <p>The hotel can confirm current details and help you compare it with the guided city-tour options.</p>
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
<script src="js/garh-v2.js"></script>
</body>
</html>
