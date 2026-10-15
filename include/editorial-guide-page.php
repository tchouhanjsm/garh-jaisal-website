<?php
/**
 * Data-driven renderer for the newer Journal guides.
 * Route files only select a reviewed guide slug; all output is escaped here.
 */
$siteConfig = require __DIR__ . '/site-config.php';
$editorialGuides = require __DIR__ . '/editorial-guides-data.php';
$editorialGuideSlug = isset($editorialGuideSlug) && is_string($editorialGuideSlug)
    ? $editorialGuideSlug
    : '';
$guide = $editorialGuides[$editorialGuideSlug] ?? null;
if (!is_array($guide)) {
    http_response_code(404);
    $guide = [
        'slug' => 'not-found',
        'title' => 'Travel guide not found | Garh Jaisal Haveli',
        'description' => 'The requested travel guide could not be found.',
        'h1' => 'Travel guide not found',
        'eyebrow' => 'Journal',
        'lede' => 'This guide is not available.',
        'hero_image' => 'img/v2/facade.jpg',
        'hero_alt' => 'Garh Jaisal Haveli inside Jaisalmer Fort',
        'date_published' => '2026-10-15',
        'date_modified' => '2026-10-15',
        'date_display' => '15 October 2026',
        'status' => 'draft',
        'toc' => [],
        'sections' => [],
        'sources' => [],
        'related' => [],
        'cta_title' => '',
        'cta_text' => '',
        'cta_primary_label' => '',
        'cta_primary_href' => 'contact.php',
        'cta_secondary_label' => '',
        'cta_secondary_href' => 'contact.php',
    ];
}
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$canonical = $siteUrl . '/' . $guide['slug'] . '.php';
$escapeGuide = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
/*
 * Article copy may contain simple, curated [label](relative-route.php) links.
 * Render only that narrow internal-link syntax; everything else is escaped.
 */
$renderGuideText = static function ($value) use ($escapeGuide) {
    $source = (string) $value;
    $pattern = '/\[([^\]]+)\]\(([A-Za-z0-9][A-Za-z0-9._\/?=#%&-]*)\)/';
    if (!preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
        return $escapeGuide($source);
    }
    $html = '';
    $offset = 0;
    foreach ($matches[0] as $index => $wholeMatch) {
        $matchText = $wholeMatch[0];
        $matchOffset = $wholeMatch[1];
        $label = $matches[1][$index][0];
        $href = $matches[2][$index][0];
        $html .= $escapeGuide(substr($source, $offset, $matchOffset - $offset));
        if (strpos($href, '..') !== false || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $href)) {
            $html .= $escapeGuide($matchText);
        } else {
            $html .= '<a href="' . $escapeGuide($href) . '">' . $escapeGuide($label) . '</a>';
        }
        $offset = $matchOffset + strlen($matchText);
    }
    $html .= $escapeGuide(substr($source, $offset));
    return $html;
};
$schemaGraph = [
    [
        '@type' => 'BlogPosting',
        '@id' => $canonical . '#article',
        'headline' => $guide['h1'],
        'description' => $guide['description'],
        'image' => [$siteUrl . '/' . ltrim($guide['hero_image'], '/')],
        'datePublished' => $guide['date_published'],
        'dateModified' => $guide['date_modified'],
        'author' => ['@type' => 'Organization', 'name' => 'Hotel Garh Jaisal Haveli', 'url' => $siteUrl . '/'],
        'publisher' => ['@id' => $siteUrl . '/#hotel'],
        'mainEntityOfPage' => ['@id' => $canonical . '#webpage'],
        'inLanguage' => 'en-IN',
    ],
    [
        '@type' => 'WebPage',
        '@id' => $canonical . '#webpage',
        'url' => $canonical,
        'name' => $guide['title'],
        'description' => $guide['description'],
        'isPartOf' => ['@id' => $siteUrl . '/#website'],
        'about' => ['@id' => $siteUrl . '/#hotel'],
        'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        'inLanguage' => 'en-IN',
    ],
    [
        '@type' => 'BreadcrumbList',
        '@id' => $canonical . '#breadcrumb',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $siteUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => $siteUrl . '/blog.php'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $guide['h1'], 'item' => $canonical],
        ],
    ],
];
$isDraft = ($guide['status'] ?? 'live') !== 'live';
if ($isDraft) {
    $guide['title'] = $guide['title'] . ' | Draft';
}
$page = [
    'title' => $guide['title'],
    'description' => $guide['description'],
    'path' => '/' . $guide['slug'] . '.php',
    'robots' => $isDraft ? 'noindex,follow' : 'index,follow',
    'og_type' => 'article',
    'og_title' => $guide['title'],
    'og_description' => $guide['description'],
    'og_image' => '/' . ltrim($guide['hero_image'], '/'),
    'og_image_alt' => $guide['hero_alt'],
    'twitter_title' => $guide['title'],
    'twitter_description' => $guide['description'],
    'twitter_image' => '/' . ltrim($guide['hero_image'], '/'),
    'css' => ['css/article.css', 'css/thar-sunset-story.css'],
    'schema' => $schemaGraph,
];
require __DIR__ . '/page-head.php';
?>
<body>
<?php include __DIR__ . '/header-v2.php'; ?>
<main class="article-page thar-sunset-story thar-sunset-story--journal" id="main-content" tabindex="-1">
  <header class="article-hero" data-story-reveal>
    <div class="site-shell article-hero-inner">
      <?php
      $breadcrumbs = [
          ['label' => 'Home', 'href' => '/'],
          ['label' => 'Journal', 'href' => '/blog.php'],
          ['label' => $guide['h1']],
      ];
      require __DIR__ . '/ui/breadcrumb.php';
      ?>
      <p class="eyebrow"><?= $escapeGuide($guide['eyebrow']) ?></p>
      <div class="article-meta">
        <span>Published <?= $escapeGuide($guide['date_display']) ?></span>
        <span>Prepared from official travel guidance and current hotel information</span>
      </div>
      <h1><?= $escapeGuide($guide['h1']) ?></h1>
      <p class="lede"><?= $escapeGuide($guide['lede']) ?></p>
    </div>
  </header>

  <section class="section" data-story-reveal>
    <div class="site-shell article-layout">
      <nav class="article-toc" aria-label="On this page">
        <?php foreach ($guide['toc'] as $item): ?>
          <a href="#<?= $escapeGuide($item['id']) ?>"><?= $escapeGuide($item['label']) ?></a>
        <?php endforeach; ?>
        <a href="#official-sources">Sources and further reading</a>
      </nav>
      <article class="article-content">
        <figure class="article-image">
          <img
            src="<?= $escapeGuide($guide['hero_image']) ?>"
            <?= function_exists('gj_image_dimension_attributes') ? gj_image_dimension_attributes($guide['hero_image']) : '' ?>
            alt="<?= $escapeGuide($guide['hero_alt']) ?>"
            fetchpriority="high"
          >
        </figure>
        <?php foreach ($guide['sections'] as $section): ?>
          <section id="<?= $escapeGuide($section['id']) ?>" data-story-reveal>
            <h2><?= $escapeGuide($section['heading']) ?></h2>
            <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
              <p><?= $renderGuideText($paragraph) ?></p>
            <?php endforeach; ?>
            <?php if (!empty($section['items'])): ?>
              <ul>
                <?php foreach ($section['items'] as $item): ?>
                  <li><?= $renderGuideText($item) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <?php if (!empty($section['callout'])): ?>
              <div class="article-callout"><strong><?= $escapeGuide($section['callout']) ?></strong></div>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>

        <section id="official-sources" data-story-reveal>
          <h2>Sources and further reading</h2>
          <p>Use official references for broad destination guidance. Re-check time-sensitive transport, access and weather details close to your travel date.</p>
          <ul>
            <?php foreach ($guide['sources'] as $source): ?>
              <li>
                <?php $externalSource = preg_match('#^https://#i', (string) $source['url']) === 1; ?>
                <a href="<?= $escapeGuide($source['url']) ?>"<?php if ($externalSource): ?> target="_blank" rel="noopener"<?php endif; ?>><?= $escapeGuide($source['label']) ?></a>
                — <?= $escapeGuide($source['note']) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      </article>
    </div>
  </section>

  <section class="section" data-story-reveal aria-labelledby="editorial-next-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">Keep planning</p><h2 id="editorial-next-title">Useful next steps.</h2></div>
        <p>Choose the next question you need to answer, then confirm anything that depends on your dates.</p>
      </div>
      <div class="article-next-grid">
        <?php foreach ($guide['related'] as $item): ?>
          <a class="article-next-card" href="<?= $escapeGuide($item['href']) ?>">
            <span><?= $escapeGuide($item['label']) ?></span>
            <strong><?= $escapeGuide($item['title']) ?></strong>
            <p><?= $escapeGuide($item['text']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php
  $ctaBand = [
      'section_class' => 'section article-footer-cta',
      'inner_class' => 'site-shell article-cta-inner',
      'eyebrow' => 'Plan with Garh Jaisal Haveli',
      'title' => $guide['cta_title'],
      'text' => $guide['cta_text'],
      'actions_class' => 'actions',
      'primary' => [
          'label' => $guide['cta_primary_label'],
          'href' => $guide['cta_primary_href'],
          'class' => 'button button-primary',
      ],
      'secondary' => [
          'label' => $guide['cta_secondary_label'],
          'href' => $guide['cta_secondary_href'],
          'class' => 'button button-secondary',
      ],
  ];
  require __DIR__ . '/ui/cta-band.php';
  ?>
</main>
<?php include __DIR__ . '/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
