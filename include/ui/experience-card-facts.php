<?php
/**
 * Compact comparison facts for a published experience card.
 *
 * Inputs:
 * - $experienceCardItem: one record from include/experience-data.php
 * - $experienceCardFactPriority: labels to consider, in display priority order
 * - $experienceCardFactLimit: maximum facts to render (default 3)
 *
 * This partial only renders values already stored in the experience registry.
 */
$experienceCardSourceFacts = isset($experienceCardItem['at_a_glance']) && is_array($experienceCardItem['at_a_glance'])
    ? $experienceCardItem['at_a_glance']
    : [];
$experienceCardPriorities = isset($experienceCardFactPriority) && is_array($experienceCardFactPriority)
    ? $experienceCardFactPriority
    : array_keys($experienceCardSourceFacts);
$experienceCardLimit = isset($experienceCardFactLimit)
    ? max(1, min(4, (int) $experienceCardFactLimit))
    : 3;
$experienceCardFacts = [];

foreach ($experienceCardPriorities as $experienceCardLabel) {
    if (!is_string($experienceCardLabel) || !array_key_exists($experienceCardLabel, $experienceCardSourceFacts)) {
        continue;
    }

    $experienceCardValue = $experienceCardSourceFacts[$experienceCardLabel];
    if (!is_scalar($experienceCardValue)) {
        continue;
    }
    $experienceCardValue = trim((string) $experienceCardValue);
    if ($experienceCardValue === '') {
        continue;
    }

    $experienceCardFacts[] = [
        'label' => $experienceCardLabel,
        'value' => $experienceCardValue,
    ];
    if (count($experienceCardFacts) >= $experienceCardLimit) {
        break;
    }
}
?>
<?php if ($experienceCardFacts !== []): ?>
  <dl class="experience-card-facts" aria-label="Quick facts">
    <?php foreach ($experienceCardFacts as $experienceCardFact): ?>
      <div class="experience-card-fact">
        <dt><?= htmlspecialchars($experienceCardFact['label'], ENT_QUOTES, 'UTF-8') ?></dt>
        <dd><?= htmlspecialchars($experienceCardFact['value'], ENT_QUOTES, 'UTF-8') ?></dd>
      </div>
    <?php endforeach; ?>
  </dl>
<?php endif; ?>
