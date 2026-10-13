<?php
/**
 * Reusable conversion band.
 * Input: $ctaBand = [section_class, inner_class, eyebrow, title, text,
 * actions_class (optional), primary (required), secondary (optional)].
 * Buttons accept label, href, class, target and rel. All output is escaped.
 */
$uiCta = isset($ctaBand) && is_array($ctaBand) ? $ctaBand : [];
$uiCtaEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$uiCtaLinks = [];
foreach (['primary', 'secondary'] as $uiCtaKey) {
    if (isset($uiCta[$uiCtaKey]) && is_array($uiCta[$uiCtaKey])) {
        $uiCtaLinks[] = $uiCta[$uiCtaKey];
    }
}
$uiCtaSectionClass = (string) ($uiCta['section_class'] ?? 'section cta-band');
$uiCtaInnerClass = (string) ($uiCta['inner_class'] ?? 'site-shell cta-band-inner');
?>
<section class="<?= $uiCtaEscape($uiCtaSectionClass) ?>">
  <div class="<?= $uiCtaEscape($uiCtaInnerClass) ?>">
    <?php if (isset($uiCta['eyebrow']) && $uiCta['eyebrow'] !== ''): ?>
      <p class="eyebrow"><?= $uiCtaEscape($uiCta['eyebrow']) ?></p>
    <?php endif; ?>
    <?php if (isset($uiCta['title']) && $uiCta['title'] !== ''): ?>
      <h2><?= $uiCtaEscape($uiCta['title']) ?></h2>
    <?php endif; ?>
    <?php if (isset($uiCta['text']) && $uiCta['text'] !== ''): ?>
      <p><?= $uiCtaEscape($uiCta['text']) ?></p>
    <?php endif; ?>
    <?php if ($uiCtaLinks): ?>
      <?php if (!empty($uiCta['actions_class'])): ?>
        <div class="<?= $uiCtaEscape($uiCta['actions_class']) ?>">
      <?php endif; ?>
      <?php foreach ($uiCtaLinks as $uiCtaLink): ?>
        <?php
        $uiCtaTarget = (string) ($uiCtaLink['target'] ?? '');
        $uiCtaRel = (string) ($uiCtaLink['rel'] ?? ($uiCtaTarget === '_blank' ? 'noopener' : ''));
        ?>
        <a class="<?= $uiCtaEscape($uiCtaLink['class'] ?? 'button button-primary') ?>" href="<?= $uiCtaEscape($uiCtaLink['href'] ?? '#') ?>"<?php if ($uiCtaTarget !== ''): ?> target="<?= $uiCtaEscape($uiCtaTarget) ?>"<?php endif; ?><?php if ($uiCtaRel !== ''): ?> rel="<?= $uiCtaEscape($uiCtaRel) ?>"<?php endif; ?>><?= $uiCtaEscape($uiCtaLink['label'] ?? '') ?></a>
      <?php endforeach; ?>
      <?php if (!empty($uiCta['actions_class'])): ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
