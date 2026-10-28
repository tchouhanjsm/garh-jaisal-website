<?php
/**
 * Shared hero building block.
 * Input: $hero = [
 *   variant => home|page|photo, eyebrow, h1, lead,
 *   actions => [['label' => '...', 'href' => '...', 'class' => '...']],
 *   image => ['src' => '...', 'alt' => '...', 'width' => 1200, 'height' => 800],
 *   breadcrumbs => [['label' => '...', 'href' => '...'], ['label' => 'Current page']]
 * ].
 * Reuses the existing .hero, .page-hero and .experience-hero CSS; output is escaped.
 */
$uiHero = isset($hero) && is_array($hero) ? $hero : [];
$uiHeroEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$uiHeroVariant = $uiHero['variant'] ?? 'page';
if (!in_array($uiHeroVariant, ['home', 'page', 'photo'], true)) {
    $uiHeroVariant = 'page';
}
$uiHeroRootClass = ['home' => 'hero', 'page' => 'page-hero', 'photo' => 'experience-hero'][$uiHeroVariant];
$uiHeroInnerClass = ['home' => 'site-shell hero-content', 'page' => 'site-shell page-hero-inner', 'photo' => 'site-shell experience-hero-content'][$uiHeroVariant];
$uiHeroImage = isset($uiHero['image']) && is_array($uiHero['image']) ? $uiHero['image'] : [];
$uiHeroActions = isset($uiHero['actions']) && is_array($uiHero['actions']) ? $uiHero['actions'] : [];
$uiHeroBreadcrumbs = isset($uiHero['breadcrumbs']) && is_array($uiHero['breadcrumbs']) ? $uiHero['breadcrumbs'] : [];
?>
<section class="<?= $uiHeroEscape($uiHeroRootClass) ?>">
  <?php if (in_array($uiHeroVariant, ['home', 'photo'], true) && !empty($uiHeroImage['src'])): ?>
    <div class="<?= $uiHeroVariant === 'home' ? 'hero-media' : 'experience-hero-media' ?>">
      <img src="<?= $uiHeroEscape($uiHeroImage['src']) ?>" alt="<?= $uiHeroEscape($uiHeroImage['alt'] ?? '') ?>"<?php if (isset($uiHeroImage['width']) && is_numeric($uiHeroImage['width'])): ?> width="<?= (int) $uiHeroImage['width'] ?>"<?php endif; ?><?php if (isset($uiHeroImage['height']) && is_numeric($uiHeroImage['height'])): ?> height="<?= (int) $uiHeroImage['height'] ?>"<?php endif; ?><?php if ($uiHeroVariant === 'home'): ?> fetchpriority="high"<?php endif; ?> decoding="async">
      <div class="<?= $uiHeroVariant === 'home' ? 'hero-overlay' : 'experience-hero-overlay' ?>"></div>
    </div>
  <?php endif; ?>
  <div class="<?= $uiHeroEscape($uiHeroInnerClass) ?>">
    <?php if ($uiHeroBreadcrumbs): ?>
      <nav class="experience-breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($uiHeroBreadcrumbs as $uiHeroIndex => $uiHeroCrumb): ?>
          <?php if ($uiHeroIndex > 0): ?><span aria-hidden="true">/</span><?php endif; ?>
          <?php if (!empty($uiHeroCrumb['href']) && $uiHeroIndex !== count($uiHeroBreadcrumbs) - 1): ?>
            <a href="<?= $uiHeroEscape($uiHeroCrumb['href']) ?>"><?= $uiHeroEscape($uiHeroCrumb['label'] ?? '') ?></a>
          <?php else: ?>
            <span<?php if ($uiHeroIndex === count($uiHeroBreadcrumbs) - 1): ?> aria-current="page"<?php endif; ?>><?= $uiHeroEscape($uiHeroCrumb['label'] ?? '') ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
    <?php if (!empty($uiHero['eyebrow'])): ?><p class="eyebrow"><?= $uiHeroEscape($uiHero['eyebrow']) ?></p><?php endif; ?>
    <?php if (!empty($uiHero['h1'])): ?><h1><?= $uiHeroEscape($uiHero['h1']) ?></h1><?php endif; ?>
    <?php if (!empty($uiHero['lead'])): ?><p><?= $uiHeroEscape($uiHero['lead']) ?></p><?php endif; ?>
    <?php if ($uiHeroActions): ?>
      <div class="actions">
        <?php foreach ($uiHeroActions as $uiHeroAction): ?>
          <?php
          $uiHeroTarget = (string) ($uiHeroAction['target'] ?? '');
          $uiHeroRel = (string) ($uiHeroAction['rel'] ?? ($uiHeroTarget === '_blank' ? 'noopener' : ''));
          ?>
          <a class="<?= $uiHeroEscape($uiHeroAction['class'] ?? 'button button-primary') ?>" href="<?= $uiHeroEscape($uiHeroAction['href'] ?? '#') ?>"<?php if ($uiHeroTarget !== ''): ?> target="<?= $uiHeroEscape($uiHeroTarget) ?>"<?php endif; ?><?php if ($uiHeroRel !== ''): ?> rel="<?= $uiHeroEscape($uiHeroRel) ?>"<?php endif; ?>><?= $uiHeroEscape($uiHeroAction['label'] ?? '') ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
