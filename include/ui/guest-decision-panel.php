<?php
/**
 * Reusable, data-backed guest decision panel.
 * Set $guestDecisionContext before including this partial.
 */
$guestDecisionRegistry = require dirname(__DIR__) . '/guest-decision-content.php';
$guestDecisionPanel = is_string($guestDecisionContext ?? null)
    ? ($guestDecisionRegistry[$guestDecisionContext] ?? null)
    : null;

if (!is_array($guestDecisionPanel)) {
    return;
}

$guestDecisionEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$guestDecisionPrimaryHref = is_string($guestDecisionPrimaryHref ?? null) && $guestDecisionPrimaryHref !== ''
    ? $guestDecisionPrimaryHref
    : (string) ($guestDecisionPanel['cta_href'] ?? '');
$guestDecisionPrimaryLabel = is_string($guestDecisionPrimaryLabel ?? null) && $guestDecisionPrimaryLabel !== ''
    ? $guestDecisionPrimaryLabel
    : (string) ($guestDecisionPanel['cta_label'] ?? 'Contact the hotel');
$guestDecisionSecondaryHref = is_string($guestDecisionSecondaryHref ?? null) ? $guestDecisionSecondaryHref : '';
$guestDecisionSecondaryLabel = is_string($guestDecisionSecondaryLabel ?? null) ? $guestDecisionSecondaryLabel : '';
$guestDecisionSecondaryExternal = !empty($guestDecisionSecondaryExternal);
$guestDecisionId = 'guest-decision-' . $guestDecisionEscape($guestDecisionContext);
?>
<aside class="guest-decision" data-guest-decision-context="<?= $guestDecisionEscape($guestDecisionContext) ?>" aria-labelledby="<?= $guestDecisionId ?>-title">
  <div class="guest-decision__inner">
    <header class="guest-decision__header">
      <p class="eyebrow">Guest decision guide</p>
      <h2 id="<?= $guestDecisionId ?>-title"><?= $guestDecisionEscape($guestDecisionPanel['title'] ?? '') ?></h2>
      <p><?= $guestDecisionEscape($guestDecisionPanel['intro'] ?? '') ?></p>
    </header>

    <div class="guest-decision__columns">
      <section class="guest-decision__column guest-decision__column--shown" aria-labelledby="<?= $guestDecisionId ?>-shown">
        <p class="guest-decision__status">Published on this site</p>
        <h3 id="<?= $guestDecisionId ?>-shown">What you can review now</h3>
        <ul>
          <?php foreach (($guestDecisionPanel['shown'] ?? []) as $item): ?>
            <li><strong><?= $guestDecisionEscape($item['label'] ?? '') ?></strong><p><?= $guestDecisionEscape($item['detail'] ?? '') ?></p></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <section class="guest-decision__column guest-decision__column--confirm" aria-labelledby="<?= $guestDecisionId ?>-confirm">
        <p class="guest-decision__status">Confirm with the hotel</p>
        <h3 id="<?= $guestDecisionId ?>-confirm">What to confirm before you decide</h3>
        <ul>
          <?php foreach (($guestDecisionPanel['confirm'] ?? []) as $item): ?>
            <li><strong><?= $guestDecisionEscape($item['label'] ?? '') ?></strong><p><?= $guestDecisionEscape($item['detail'] ?? '') ?></p></li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>

    <div class="guest-decision__actions">
      <?php if ($guestDecisionPrimaryHref !== '' && $guestDecisionPrimaryLabel !== ''): ?>
        <a class="button button-primary" href="<?= $guestDecisionEscape($guestDecisionPrimaryHref) ?>"><?= $guestDecisionEscape($guestDecisionPrimaryLabel) ?></a>
      <?php endif; ?>
      <?php if ($guestDecisionSecondaryHref !== '' && $guestDecisionSecondaryLabel !== ''): ?>
        <a class="guest-decision__secondary" href="<?= $guestDecisionEscape($guestDecisionSecondaryHref) ?>"<?= $guestDecisionSecondaryExternal ? ' target="_blank" rel="noopener"' : '' ?>><?= $guestDecisionEscape($guestDecisionSecondaryLabel) ?> <span aria-hidden="true">→</span></a>
      <?php endif; ?>
    </div>
  </div>
</aside>
