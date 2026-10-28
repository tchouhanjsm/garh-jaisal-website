<?php
/**
 * Renderer for Content Studio landing pages.
 *
 * Route wrappers set $studioPageSlug. User-editable values are JSON data, every
 * text value is escaped, every href is validated as an internal route, and no
 * authored HTML/CSS/JavaScript is executed.
 */
$siteConfig = require __DIR__ . '/site-config.php';
$studioPageSlug = isset($studioPageSlug) && is_string($studioPageSlug) ? $studioPageSlug : '';
if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $studioPageSlug)) {
    http_response_code(404);
    echo 'Page not found';
    return;
}

$studioPages = require __DIR__ . '/studio-pages-data.php';
$studioPage = $studioPages[$studioPageSlug] ?? null;
if (!is_array($studioPage)) {
    http_response_code(404);
    echo 'Page not found';
    return;
}

$studioPageStatus = $studioPage['status'] ?? 'draft';
$studioPagePreviewToken = $studioPage['studio_preview_token'] ?? null;
$studioPagePreviewExpiresAt = $studioPage['studio_preview_expires_at'] ?? null;
// Only the private preview entry point can opt in to rendering a temporary Draft.
// A public slug route must never reveal content before it has been approved and made Live.
$studioPageIsPrivatePreview = isset($studioPageAllowDraftPreview)
    && $studioPageAllowDraftPreview === true
    && $studioPageStatus === 'draft'
    && is_string($studioPagePreviewToken)
    && preg_match('/^[a-f0-9]{24}$/D', $studioPagePreviewToken)
    && is_int($studioPagePreviewExpiresAt)
    && $studioPagePreviewExpiresAt > (int) floor(microtime(true) * 1000);
if ($studioPageStatus !== 'live' && !$studioPageIsPrivatePreview) {
    http_response_code(404);
    echo 'Page not found';
    return;
}
$studioPageIsDraft = $studioPageStatus !== 'live';
$studioPageTitle = (string) ($studioPage['title'] ?? $studioPage['h1'] ?? 'Garh Jaisal Haveli');
$studioPageDescription = (string) ($studioPage['description'] ?? '');
$studioPageH1 = (string) ($studioPage['h1'] ?? $studioPageTitle);
$studioPageHero = (string) ($studioPage['hero_image'] ?? '');
$studioPageHeroAlt = (string) ($studioPage['hero_alt'] ?? '');
$studioPageEyebrow = (string) ($studioPage['eyebrow'] ?? 'Garh Jaisal Haveli');
$studioPageLayout = in_array(($studioPage['hero_layout'] ?? 'split'), ['split', 'centered'], true)
    ? $studioPage['hero_layout']
    : 'split';
$studioPageTheme = in_array(($studioPage['theme'] ?? 'heritage'), ['heritage', 'sandstone', 'desert-night'], true)
    ? $studioPage['theme']
    : 'heritage';
$studioPageEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$studioPageHref = static function ($value) {
    $href = trim((string) $value);
    if ($href === '/' || preg_match('~^/[a-z0-9-]+\.php(?:#[a-z0-9-]+)?$~D', $href) || preg_match('/^#[a-z0-9][a-z0-9-]*$/D', $href)) {
        return $href;
    }
    return '';
};
$studioPageImage = static function ($value) {
    $src = trim((string) $value);
    if (!preg_match('#^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$#iD', $src) || strpos($src, '..') !== false || strpos($src, '\\') !== false) {
        return '';
    }
    $absolute = dirname(__DIR__) . '/' . $src;
    if (is_link($absolute) || !is_file($absolute)) {
        return '';
    }
    $cursor = dirname($absolute);
    $imageRoot = dirname(__DIR__) . '/img';
    while ($cursor !== $imageRoot && strpos($cursor, $imageRoot . DIRECTORY_SEPARATOR) === 0) {
        if (is_link($cursor) || !is_dir($cursor)) {
            return '';
        }
        $cursor = dirname($cursor);
    }
    return $src;
};
$studioPageParagraphs = static function ($value) {
    $paragraphs = preg_split('/\R\s*\R/u', trim((string) $value));
    return is_array($paragraphs) ? array_values(array_filter(array_map('trim', $paragraphs), static function ($item) {
        return $item !== '';
    })) : [];
};

if ($studioPageHero === '' || $studioPageImage($studioPageHero) === '') {
    http_response_code(404);
    echo 'Page not found';
    return;
}

if (trim($studioPageHeroAlt) === '') {
    throw new RuntimeException('Content Studio landing page requires a non-empty hero_alt: ' . $studioPageSlug);
}

$studioPageCanonical = rtrim((string) ($siteConfig['site_url'] ?? ''), '/') . '/' . $studioPageSlug . '.php';
$studioPageSchema = [
    '@type' => 'WebPage',
    '@id' => $studioPageCanonical . '#webpage',
    'url' => $studioPageCanonical,
    'name' => $studioPageTitle,
    'description' => $studioPageDescription,
    'inLanguage' => 'en',
];
$page = [
    'title' => $studioPageTitle . ($studioPageIsDraft ? ' | Draft' : ''),
    'description' => $studioPageDescription,
    'path' => '/' . $studioPageSlug . '.php',
    'robots' => $studioPageIsDraft ? 'noindex,follow' : 'index,follow',
    'og_type' => 'website',
    'og_title' => $studioPageTitle,
    'og_description' => $studioPageDescription,
    'og_image' => '/' . ltrim($studioPageHero, '/'),
    'og_image_alt' => $studioPageHeroAlt,
    'twitter_title' => $studioPageTitle,
    'twitter_description' => $studioPageDescription,
    'twitter_image' => '/' . ltrim($studioPageHero, '/'),
    'twitter_image_alt' => $studioPageHeroAlt,
    'css' => [
        'css/page-builder.css',
        'css/thar-sunset-story.css',
        'css/pages/' . $studioPageSlug . '.css',
    ],
    'schema' => [$studioPageSchema],
];
require __DIR__ . '/page-head.php';
include __DIR__ . '/header-v2.php';
?>
<main class="studio-page studio-page--<?= $studioPageEscape($studioPageSlug) ?>"
      data-page-theme="<?= $studioPageEscape($studioPageTheme) ?>"
      data-page-layout="<?= $studioPageEscape($studioPageLayout) ?>"
      id="main-content">
  <section class="studio-page-hero studio-page-hero--<?= $studioPageEscape($studioPageLayout) ?>">
    <div class="studio-page-hero-inner">
      <div class="studio-page-hero-copy">
        <p class="eyebrow"><?= $studioPageEscape($studioPageEyebrow) ?></p>
        <h1><?= $studioPageEscape($studioPageH1) ?></h1>
        <?php if (trim((string) ($studioPage['hero_intro'] ?? '')) !== ''): ?>
          <p class="studio-page-hero-intro"><?= $studioPageEscape($studioPage['hero_intro']) ?></p>
        <?php endif; ?>
        <?php
        $studioPageHeroCtaHref = $studioPageHref($studioPage['hero_cta_href'] ?? '');
        $studioPageHeroCtaLabel = trim((string) ($studioPage['hero_cta_label'] ?? ''));
        if ($studioPageHeroCtaHref !== '' && $studioPageHeroCtaLabel !== ''):
        ?>
          <a class="studio-page-button studio-page-button--primary" href="<?= $studioPageEscape($studioPageHeroCtaHref) ?>"><?= $studioPageEscape($studioPageHeroCtaLabel) ?></a>
        <?php endif; ?>
      </div>
      <figure class="studio-page-hero-media">
        <img src="<?= $studioPageEscape($studioPageHero) ?>" alt="<?= $studioPageEscape($studioPageHeroAlt) ?>" width="1600" height="1000" fetchpriority="high">
      </figure>
    </div>
  </section>

  <?php foreach (($studioPage['sections'] ?? []) as $studioPageIndex => $studioPageSection): ?>
    <?php
    if (!is_array($studioPageSection)) {
        continue;
    }
    $studioPageSectionType = (string) ($studioPageSection['type'] ?? '');
    $studioPageSectionHeading = trim((string) ($studioPageSection['heading'] ?? ''));
    $studioPageSectionId = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', (string) ($studioPageSection['id'] ?? ''))
        ? (string) $studioPageSection['id']
        : 'section-' . ((int) $studioPageIndex + 1);
    ?>
    <?php if ($studioPageSectionType === 'intro'): ?>
      <section class="studio-page-section studio-page-section--intro" id="<?= $studioPageEscape($studioPageSectionId) ?>">
        <div class="studio-page-section-inner studio-page-copy">
          <?php if ($studioPageSectionHeading !== ''): ?><h2><?= $studioPageEscape($studioPageSectionHeading) ?></h2><?php endif; ?>
          <?php foreach ($studioPageParagraphs($studioPageSection['body'] ?? '') as $studioPageParagraph): ?>
            <p><?= $studioPageEscape($studioPageParagraph) ?></p>
          <?php endforeach; ?>
        </div>
      </section>
    <?php elseif ($studioPageSectionType === 'image_text'): ?>
      <?php $studioPageSectionImage = $studioPageImage($studioPageSection['image'] ?? ''); ?>
      <?php if ($studioPageSectionImage !== ''): ?>
        <section class="studio-page-section studio-page-image-text<?= ($studioPageSection['image_side'] ?? 'left') === 'right' ? ' studio-page-image-text--image-right' : '' ?>" id="<?= $studioPageEscape($studioPageSectionId) ?>">
          <div class="studio-page-section-inner studio-page-image-text-grid">
            <figure><img src="<?= $studioPageEscape($studioPageSectionImage) ?>" alt="<?= $studioPageEscape($studioPageSection['alt'] ?? '') ?>" loading="lazy" width="1200" height="900"></figure>
            <div class="studio-page-copy">
              <?php if ($studioPageSectionHeading !== ''): ?><h2><?= $studioPageEscape($studioPageSectionHeading) ?></h2><?php endif; ?>
              <?php foreach ($studioPageParagraphs($studioPageSection['body'] ?? '') as $studioPageParagraph): ?><p><?= $studioPageEscape($studioPageParagraph) ?></p><?php endforeach; ?>
              <?php $studioPageSectionHref = $studioPageHref($studioPageSection['link_href'] ?? ''); ?>
              <?php if ($studioPageSectionHref !== '' && trim((string) ($studioPageSection['link_label'] ?? '')) !== ''): ?>
                <a class="studio-page-text-link" href="<?= $studioPageEscape($studioPageSectionHref) ?>"><?= $studioPageEscape($studioPageSection['link_label']) ?></a>
              <?php endif; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>
    <?php elseif ($studioPageSectionType === 'feature_list'): ?>
      <section class="studio-page-section studio-page-section--features" id="<?= $studioPageEscape($studioPageSectionId) ?>">
        <div class="studio-page-section-inner">
          <?php if ($studioPageSectionHeading !== ''): ?><h2><?= $studioPageEscape($studioPageSectionHeading) ?></h2><?php endif; ?>
          <ul class="studio-page-feature-list">
            <?php foreach (($studioPageSection['items'] ?? []) as $studioPageItem): ?><li><?= $studioPageEscape($studioPageItem) ?></li><?php endforeach; ?>
          </ul>
        </div>
      </section>
    <?php elseif ($studioPageSectionType === 'faq'): ?>
      <section class="studio-page-section studio-page-section--faq" id="<?= $studioPageEscape($studioPageSectionId) ?>">
        <div class="studio-page-section-inner studio-page-copy">
          <?php if ($studioPageSectionHeading !== ''): ?><h2><?= $studioPageEscape($studioPageSectionHeading) ?></h2><?php endif; ?>
          <?php foreach (($studioPageSection['items'] ?? []) as $studioPageFaq): ?>
            <?php if (!is_array($studioPageFaq)) { continue; } ?>
            <details class="studio-page-faq-item">
              <summary><?= $studioPageEscape($studioPageFaq['question'] ?? '') ?></summary>
              <p><?= $studioPageEscape($studioPageFaq['answer'] ?? '') ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </section>
    <?php elseif ($studioPageSectionType === 'cta'): ?>
      <section class="studio-page-section studio-page-cta" id="<?= $studioPageEscape($studioPageSectionId) ?>">
        <div class="studio-page-cta-inner">
          <?php if ($studioPageSectionHeading !== ''): ?><h2><?= $studioPageEscape($studioPageSectionHeading) ?></h2><?php endif; ?>
          <?php foreach ($studioPageParagraphs($studioPageSection['body'] ?? '') as $studioPageParagraph): ?><p><?= $studioPageEscape($studioPageParagraph) ?></p><?php endforeach; ?>
          <?php $studioPageCtaHref = $studioPageHref($studioPageSection['button_href'] ?? ''); ?>
          <?php if ($studioPageCtaHref !== '' && trim((string) ($studioPageSection['button_label'] ?? '')) !== ''): ?>
            <a class="studio-page-button studio-page-button--primary" href="<?= $studioPageEscape($studioPageCtaHref) ?>"><?= $studioPageEscape($studioPageSection['button_label']) ?></a>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>
  <?php endforeach; ?>
</main>
<?php include __DIR__ . '/footer-v2.php'; ?>
