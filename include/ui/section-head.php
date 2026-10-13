<?php
/**
 * Reusable section heading.
 * Input: $sectionHead = ['eyebrow' => '...', 'h2' => '...', 'intro' => '...'].
 * Emits the existing .section-head structure; all output is escaped.
 */
$uiSectionHead = isset($sectionHead) && is_array($sectionHead) ? $sectionHead : [];
$uiSectionEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<div class="section-head">
  <div>
    <?php if (isset($uiSectionHead['eyebrow']) && $uiSectionHead['eyebrow'] !== ''): ?>
      <p class="eyebrow"><?= $uiSectionEscape($uiSectionHead['eyebrow']) ?></p>
    <?php endif; ?>
    <?php if (isset($uiSectionHead['h2']) && $uiSectionHead['h2'] !== ''): ?>
      <h2><?= $uiSectionEscape($uiSectionHead['h2']) ?></h2>
    <?php endif; ?>
  </div>
  <?php if (isset($uiSectionHead['intro']) && $uiSectionHead['intro'] !== ''): ?>
    <p><?= $uiSectionEscape($uiSectionHead['intro']) ?></p>
  <?php endif; ?>
</div>
