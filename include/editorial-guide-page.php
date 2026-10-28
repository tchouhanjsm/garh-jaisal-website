<?php
/**
 * Data-driven renderer for the newer Journal guides.
 * Route files only select a reviewed guide slug; all output is escaped here.
 */
$siteConfig = require __DIR__ . '/site-config.php';
$editorialGuides = require __DIR__ . '/editorial-guides-data.php';
$editorialTopicTaxonomy = require __DIR__ . '/editorial-topic-taxonomy.php';
$studioAllowDraftPreview = isset($studioAllowDraftPreview) && $studioAllowDraftPreview === true;
$editorialGuideSlug = isset($editorialGuideSlug) && is_string($editorialGuideSlug)
    ? $editorialGuideSlug
    : '';
$guide = $editorialGuides[$editorialGuideSlug] ?? null;
if (is_array($guide) && ($guide['status'] ?? 'live') !== 'live' && !$studioAllowDraftPreview) {
    $guide = null;
}
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
        'status' => 'hidden',
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
$guideHeroAlt = trim((string) ($guide['hero_alt'] ?? ''));
if ($guideHeroAlt === '') {
    throw new RuntimeException('Editorial guide requires a non-empty hero_alt: ' . (string) ($guide['slug'] ?? 'unknown'));
}

$guideTopic = (string) ($guide['topic'] ?? 'trip-planning');
if (!isset($editorialTopicTaxonomy[$guideTopic])) $guideTopic = 'trip-planning';
$guideTopicLabel = $editorialTopicTaxonomy[$guideTopic] ?? 'Trip planning';
$siteUrl = rtrim((string) ($siteConfig['site_url'] ?? ''), '/');
$canonical = $siteUrl . '/' . $guide['slug'] . '.php';
$escapeGuide = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$guideBreadcrumbLabel = (string) ($guide['breadcrumb_label'] ?? $guide['h1']);
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Journal', 'href' => '/blog.php'],
    ['label' => $guideBreadcrumbLabel],
];
require_once __DIR__ . '/schema.php';
$breadcrumbSchema = gj_schema_breadcrumb_list($siteUrl, '/' . $guide['slug'] . '.php', $breadcrumbs);
$resolveGuideHref = static function ($value) use ($siteConfig) {
    if ((string) $value === '@booking') return (string) ($siteConfig['booking_url'] ?? '#');
    return (string) $value;
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
        'headline' => $guide['schema_headline'] ?? $guide['h1'],
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
    $breadcrumbSchema,
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
    'og_image_alt' => $guideHeroAlt,
    'twitter_title' => $guide['title'],
    'twitter_description' => $guide['description'],
    'twitter_image' => '/' . ltrim($guide['hero_image'], '/'),
    'twitter_image_alt' => $guideHeroAlt,
    'css' => (isset($guide['css']) && is_array($guide['css'])) ? $guide['css'] : ['css/article.css', 'css/thar-sunset-story.css'],
    'schema' => $schemaGraph,
];
?>
<!doctype html>
<html lang="en">
<?php
require __DIR__ . '/page-head.php';
?>
<body>
<?php include __DIR__ . '/header-v2.php'; ?>
<main class="article-page<?= (!array_key_exists('story_style', $guide) || !empty($guide['story_style'])) ? ' thar-sunset-story thar-sunset-story--journal' : '' ?>" id="main-content" tabindex="-1">
  <header class="article-hero" data-story-reveal>
    <div class="site-shell article-hero-inner">
      <?php require __DIR__ . '/ui/breadcrumb.php'; ?>
      <p class="eyebrow"><?= $escapeGuide($guide['eyebrow']) ?></p>
      <div class="article-meta">
        <span>Published <?= $escapeGuide($guide['date_display']) ?></span>
        <span><?= $escapeGuide($guide['meta_note'] ?? 'Prepared from official travel guidance and current hotel information') ?></span>
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
            <?= function_exists('gj_image_dimension_attributes') ? gj_image_dimension_attributes($guide['hero_image'], '(max-width: 768px) 100vw, 60vw') : '' ?>
            alt="<?= $escapeGuide($guide['hero_alt']) ?>"
            fetchpriority="high"
           decoding="async">
        </figure>
        <?php foreach ($guide['sections'] as $section): ?>
          <section id="<?= $escapeGuide($section['id']) ?>" data-story-reveal>
            <?php if (!empty($section['eyebrow'])): ?><p class="eyebrow"><?= $escapeGuide($section['eyebrow']) ?></p><?php endif; ?>
            <h2><?= $escapeGuide($section['heading']) ?></h2>
            <?php if (isset($section['blocks']) && is_array($section['blocks'])): ?>
              <?php foreach ($section['blocks'] as $block): ?>
                <?php if (($block['type'] ?? '') === 'paragraph'): ?><p><?= $renderGuideText($block['text'] ?? '') ?></p>
                <?php elseif (($block['type'] ?? '') === 'subheading'): ?><h3><?= $escapeGuide($block['text'] ?? '') ?></h3>
                <?php elseif (($block['type'] ?? '') === 'list' && !empty($block['items'])): ?>
                  <?php if (!empty($block['ordered'])): ?><ol><?php else: ?><ul><?php endif; ?><?php foreach ($block['items'] as $item): ?><li><?= $renderGuideText($item) ?></li><?php endforeach; ?><?php if (!empty($block['ordered'])): ?></ol><?php else: ?></ul><?php endif; ?>
                <?php elseif (($block['type'] ?? '') === 'facts' && !empty($block['items'])): ?>
                  <div class="article-facts"><?php foreach ($block['items'] as $fact): ?><div class="article-fact"><span><?= $escapeGuide($fact['label'] ?? '') ?></span><strong><?= $escapeGuide($fact['value'] ?? '') ?></strong></div><?php endforeach; ?></div>
                <?php elseif (($block['type'] ?? '') === 'table' && !empty($block['headers'])): ?>
                  <div class="article-table-wrap"><table class="article-table"><thead><tr><?php foreach ($block['headers'] as $header): ?><th scope="col"><?= $escapeGuide($header) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach (($block['rows'] ?? []) as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= $renderGuideText($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
                <?php elseif (($block['type'] ?? '') === 'callout'): ?><div class="article-callout"><strong><?= $escapeGuide($block['text'] ?? '') ?></strong></div>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?><p><?= $renderGuideText($paragraph) ?></p><?php endforeach; ?>
              <?php if (!empty($section['items'])): ?><ul><?php foreach ($section['items'] as $item): ?><li><?= $renderGuideText($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
              <?php if (!empty($section['callout'])): ?><div class="article-callout"><strong><?= $escapeGuide($section['callout']) ?></strong></div><?php endif; ?>
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
        <div><p class="eyebrow"><?= $escapeGuide($guide['related_eyebrow'] ?? 'Keep planning') ?></p><?php if (!array_key_exists('related_title', $guide) || $guide['related_title'] !== ''): ?><h2 id="editorial-next-title"><?= $escapeGuide($guide['related_title'] ?? 'Useful next steps.') ?></h2><?php endif; ?></div>
        <?php if (!array_key_exists('related_intro', $guide) || $guide['related_intro'] !== ''): ?><p><?= $escapeGuide($guide['related_intro'] ?? 'Choose the next question you need to answer, then confirm anything that depends on your dates.') ?></p><?php endif; ?>
      </div>
      <div class="article-next-grid">
        <?php foreach ($guide['related'] as $item): ?>
          <a class="article-next-card" href="<?= $escapeGuide($item['href']) ?>">
            <span><?= $escapeGuide($item['label']) ?></span>
            <strong><?= $escapeGuide($item['title']) ?></strong>
            <?php if (!empty($item['text'])): ?><p><?= $escapeGuide($item['text']) ?></p><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section article-related-discovery" data-story-reveal aria-labelledby="journal-discovery-title">
    <div class="site-shell">
      <div class="section-head">
        <div><p class="eyebrow">From the journal</p><h2 id="journal-discovery-title">Keep exploring Jaisalmer.</h2></div>
        <p>Follow the question you still have, from arriving inside the Fort to choosing a season or shaping time beyond the city.</p>
      </div>
      <?php
      // Do not repeat destinations already linked in the article body, its
      // related cards, sources or enquiry CTAs. This avoids redundant links
      // while keeping a useful cross-guide rail on every published article.
      $journalDiscoveryCandidates = [];
      $journalCandidateOrder = 0;
      foreach ($editorialGuides as $candidateSlug => $candidateGuide) {
          if (
              !is_string($candidateSlug)
              || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $candidateSlug)
              || !is_array($candidateGuide)
              || ($candidateGuide['status'] ?? 'live') !== 'live'
              || $candidateSlug === (string) $guide['slug']
          ) {
              continue;
          }
          $candidateTopic = (string) ($candidateGuide['topic'] ?? 'trip-planning');
          if (!isset($editorialTopicTaxonomy[$candidateTopic])) $candidateTopic = 'trip-planning';
          $candidateLabel = trim((string) (
              $candidateGuide['discovery_label']
              ?? $candidateGuide['breadcrumb_label']
              ?? $candidateGuide['h1']
              ?? $candidateGuide['title']
              ?? ''
          ));
          if ($candidateLabel === '') continue;
          $journalDiscoveryCandidates[] = [
              'href' => $candidateSlug . '.php',
              'label' => $candidateLabel,
              'topic' => $candidateTopic,
              'same_topic' => $candidateTopic === $guideTopic,
              'order' => $journalCandidateOrder++,
          ];
      }
      usort($journalDiscoveryCandidates, static function ($left, $right) {
          if ($left['same_topic'] !== $right['same_topic']) {
              return $left['same_topic'] ? -1 : 1;
          }
          return $left['order'] <=> $right['order'];
      });
      $journalExistingRoutes = [];
      $journalValuesToScan = [
          $guide['sections'] ?? [],
          $guide['related'] ?? [],
          $guide['sources'] ?? [],
          $guide['cta_primary_href'] ?? '',
          $guide['cta_secondary_href'] ?? '',
      ];
      while ($journalValuesToScan) {
          $journalValue = array_pop($journalValuesToScan);
          if (is_array($journalValue)) {
              foreach ($journalValue as $nestedJournalValue) {
                  $journalValuesToScan[] = $nestedJournalValue;
              }
              continue;
          }
          if (!is_string($journalValue) || preg_match('#^https?://#i', trim($journalValue))) {
              continue;
          }
          if (preg_match_all('~(?<![A-Za-z0-9])([A-Za-z0-9][A-Za-z0-9._/-]*\.php(?:\?[A-Za-z0-9._=%&-]+)?)~', $journalValue, $journalRouteMatches)) {
              foreach ($journalRouteMatches[1] as $journalRoute) {
                  $journalRoutePath = parse_url($journalRoute, PHP_URL_PATH);
                  $journalRouteFile = basename(is_string($journalRoutePath) ? $journalRoutePath : $journalRoute);
                  $journalExistingRoutes[$journalRouteFile] = true;
              }
          }
      }
      $journalExistingRoutes[basename((string) $guide['slug'] . '.php')] = true;
      $journalDiscoveryLinks = array_slice(array_values(array_filter($journalDiscoveryCandidates, static function ($candidate) use ($journalExistingRoutes) {
          return !isset($journalExistingRoutes[basename($candidate['href'])]);
      })), 0, 8);
      ?>
      <nav class="article-topic-links" aria-label="More Jaisalmer travel guides" data-topic="<?= $escapeGuide($guideTopic) ?>">
        <?php foreach ($journalDiscoveryLinks as $journalLink): ?>
          <a href="<?= $escapeGuide($journalLink['href']) ?>" data-topic="<?= $escapeGuide($journalLink['topic']) ?>"><?= $escapeGuide($journalLink['label']) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </section>

  <?php
  $ctaBand = [
      'section_class' => 'section article-footer-cta',
      'inner_class' => 'site-shell article-cta-inner',
      'eyebrow' => $guide['cta_eyebrow'] ?? 'Plan with Garh Jaisal Haveli',
      'title' => $guide['cta_title'],
      'text' => $guide['cta_text'],
      'actions_class' => 'actions',
      'primary' => [
          'label' => $guide['cta_primary_label'],
          'href' => $resolveGuideHref($guide['cta_primary_href']),
          'class' => 'button button-primary',
          'target' => $guide['cta_primary_target'] ?? '',
          'rel' => $guide['cta_primary_rel'] ?? '',
      ],
      'secondary' => [
          'label' => $guide['cta_secondary_label'],
          'href' => $resolveGuideHref($guide['cta_secondary_href']),
          'class' => 'button button-secondary',
          'target' => $guide['cta_secondary_target'] ?? '',
          'rel' => $guide['cta_secondary_rel'] ?? '',
      ],
  ];
  require __DIR__ . '/ui/cta-band.php';
  ?>
<?php require __DIR__ . '/ui/planning-path.php'; ?>
</main>
<?php include __DIR__ . '/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
