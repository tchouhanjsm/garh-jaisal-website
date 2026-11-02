<?php
/**
 * Reusable, data-backed guest decision panel.
 * Set $guestDecisionContext before including this partial.
 */
require_once dirname(__DIR__) . '/guest-decision-links.php';

$guestDecisionRegistry = require dirname(__DIR__) . '/guest-decision-content.php';
$guestDecisionContext = is_string($guestDecisionContext ?? null) ? $guestDecisionContext : '';
if (!preg_match('/^[a-z0-9-]{1,32}$/D', $guestDecisionContext)) {
    return;
}

$guestDecisionPanel = $guestDecisionRegistry[$guestDecisionContext] ?? null;
if (!is_array($guestDecisionPanel)) {
    return;
}

$guestDecisionEscape = static function ($value) {
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
};
$guestDecisionItems = static function ($items) {
    if (!is_array($items)) {
        return [];
    }
    $valid = [];
    foreach ($items as $item) {
        if (is_array($item)
            && is_string($item['label'] ?? null) && trim($item['label']) !== ''
            && is_string($item['detail'] ?? null) && trim($item['detail']) !== '') {
            $valid[] = $item;
        }
    }
    return $valid;
};

$guestDecisionShown = $guestDecisionItems($guestDecisionPanel['shown'] ?? []);
$guestDecisionConfirm = $guestDecisionItems($guestDecisionPanel['confirm'] ?? []);
$guestDecisionTitle = is_string($guestDecisionPanel['title'] ?? null) ? trim($guestDecisionPanel['title']) : '';
$guestDecisionIntro = is_string($guestDecisionPanel['intro'] ?? null) ? trim($guestDecisionPanel['intro']) : '';
if ($guestDecisionTitle === '' || $guestDecisionIntro === '' || !$guestDecisionShown || !$guestDecisionConfirm) {
    return;
}

$guestDecisionSiteConfig = is_array($siteConfig ?? null)
    ? $siteConfig
    : require dirname(__DIR__) . '/site-config.php';

$guestDecisionRequestedPrimaryHref = is_string($guestDecisionPrimaryHref ?? null) && $guestDecisionPrimaryHref !== ''
    ? $guestDecisionPrimaryHref
    : (string) ($guestDecisionPanel['cta_href'] ?? '');
$guestDecisionPrimaryHref = gj_guest_decision_safe_internal_href($guestDecisionRequestedPrimaryHref);
$guestDecisionPrimaryLabel = is_string($guestDecisionPrimaryLabel ?? null) && trim($guestDecisionPrimaryLabel) !== ''
    ? trim($guestDecisionPrimaryLabel)
    : (string) ($guestDecisionPanel['cta_label'] ?? 'Contact the hotel');

$guestDecisionRequestedSecondaryHref = is_string($guestDecisionSecondaryHref ?? null) ? $guestDecisionSecondaryHref : '';
$guestDecisionSecondaryLabel = is_string($guestDecisionSecondaryLabel ?? null) ? trim($guestDecisionSecondaryLabel) : '';
$guestDecisionSecondaryExternal = !empty($guestDecisionSecondaryExternal);
$guestDecisionSecondaryHref = $guestDecisionSecondaryExternal
    ? gj_guest_decision_safe_external_href($guestDecisionRequestedSecondaryHref, $guestDecisionSiteConfig)
    : gj_guest_decision_safe_internal_href($guestDecisionRequestedSecondaryHref);

$guestDecisionId = 'guest-decision-' . $guestDecisionEscape($guestDecisionContext);
$guestDecisionCtaId = (string) ($guestDecisionPanel['cta_id'] ?? '');
$guestDecisionCtaLocation = (string) ($guestDecisionPanel['cta_location'] ?? '');
$guestDecisionCtaId = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+){0,7}$/D', $guestDecisionCtaId) ? $guestDecisionCtaId : '';
$guestDecisionCtaLocation = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+){0,7}$/D', $guestDecisionCtaLocation) ? $guestDecisionCtaLocation : '';
?>
<aside class="guest-decision" data-guest-decision-context="<?= $guestDecisionEscape($guestDecisionContext) ?>" aria-labelledby="<?= $guestDecisionId ?>-title">
  <div class="guest-decision__inner">
    <header class="guest-decision__header">
      <p class="eyebrow">Guest decision guide</p>
      <h2 id="<?= $guestDecisionId ?>-title"><?= $guestDecisionEscape($guestDecisionTitle) ?></h2>
      <p><?= $guestDecisionEscape($guestDecisionIntro) ?></p>
    </header>

    <div class="guest-decision__columns">
      <section class="guest-decision__column guest-decision__column--shown" aria-labelledby="<?= $guestDecisionId ?>-shown">
        <p class="guest-decision__status">Published on this site</p>
        <h3 id="<?= $guestDecisionId ?>-shown">What you can review now</h3>
        <ul>
          <?php foreach ($guestDecisionShown as $item): ?>
            <li><strong><?= $guestDecisionEscape($item['label']) ?></strong><p><?= $guestDecisionEscape($item['detail']) ?></p></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <section class="guest-decision__column guest-decision__column--confirm" aria-labelledby="<?= $guestDecisionId ?>-confirm">
        <p class="guest-decision__status">Confirm with the hotel</p>
        <h3 id="<?= $guestDecisionId ?>-confirm">What to confirm before you decide</h3>
        <ul>
          <?php foreach ($guestDecisionConfirm as $item): ?>
            <li><strong><?= $guestDecisionEscape($item['label']) ?></strong><p><?= $guestDecisionEscape($item['detail']) ?></p></li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>

    <?php if (($guestDecisionPrimaryHref !== '' && $guestDecisionPrimaryLabel !== '') || ($guestDecisionSecondaryHref !== '' && $guestDecisionSecondaryLabel !== '')): ?>
      <div class="guest-decision__actions">
        <?php if ($guestDecisionPrimaryHref !== '' && $guestDecisionPrimaryLabel !== ''): ?>
          <a class="button button-primary" href="<?= $guestDecisionEscape($guestDecisionPrimaryHref) ?>"<?= $guestDecisionCtaId !== '' ? ' data-cta-id="' . $guestDecisionEscape($guestDecisionCtaId) . '"' : '' ?><?= $guestDecisionCtaLocation !== '' ? ' data-cta-location="' . $guestDecisionEscape($guestDecisionCtaLocation) . '"' : '' ?>><?= $guestDecisionEscape($guestDecisionPrimaryLabel) ?></a>
        <?php endif; ?>
        <?php if ($guestDecisionSecondaryHref !== '' && $guestDecisionSecondaryLabel !== ''): ?>
          <a class="guest-decision__secondary" href="<?= $guestDecisionEscape($guestDecisionSecondaryHref) ?>"<?= $guestDecisionSecondaryExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= $guestDecisionEscape($guestDecisionSecondaryLabel) ?> <span aria-hidden="true">→</span></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</aside>
