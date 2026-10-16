<?php
/**
 * Shared, escaped definition-list component for verified page facts.
 *
 * Input:
 * $atAGlance = [
 *   'class' => 'at-a-glance', // optional class list
 *   'items' => [
 *     ['label' => 'Location', 'value' => 'Inside Jaisalmer Fort'],
 *   ],
 * ];
 */
$uiAtAGlanceConfig = isset($atAGlance) && is_array($atAGlance) ? $atAGlance : [];
$uiAtAGlanceClass = isset($uiAtAGlanceConfig['class']) && is_string($uiAtAGlanceConfig['class'])
    ? trim($uiAtAGlanceConfig['class'])
    : 'at-a-glance';
if ($uiAtAGlanceClass === '' || !preg_match('/^[A-Za-z0-9 _-]+$/D', $uiAtAGlanceClass)) {
    $uiAtAGlanceClass = 'at-a-glance';
}
$uiAtAGlanceItems = [];
foreach (($uiAtAGlanceConfig['items'] ?? []) as $uiAtAGlanceItem) {
    if (!is_array($uiAtAGlanceItem)) {
        continue;
    }
    $uiAtAGlanceItemLabel = isset($uiAtAGlanceItem['label']) && is_scalar($uiAtAGlanceItem['label'])
        ? trim((string) $uiAtAGlanceItem['label'])
        : '';
    $uiAtAGlanceItemValue = isset($uiAtAGlanceItem['value']) && is_scalar($uiAtAGlanceItem['value'])
        ? trim((string) $uiAtAGlanceItem['value'])
        : '';
    if ($uiAtAGlanceItemLabel !== '' && $uiAtAGlanceItemValue !== '') {
        $uiAtAGlanceItems[] = [
            'label' => $uiAtAGlanceItemLabel,
            'value' => $uiAtAGlanceItemValue,
        ];
    }
}
$uiAtAGlanceEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<?php if ($uiAtAGlanceItems): ?>
<dl class="<?= $uiAtAGlanceEscape($uiAtAGlanceClass) ?>">
<?php foreach ($uiAtAGlanceItems as $uiAtAGlanceItem): ?>
  <div class="at-a-glance__item">
    <dt class="at-a-glance__label"><?= $uiAtAGlanceEscape($uiAtAGlanceItem['label']) ?></dt>
    <dd class="at-a-glance__value"><?= $uiAtAGlanceEscape($uiAtAGlanceItem['value']) ?></dd>
  </div>
<?php endforeach; ?>
</dl>
<?php endif; ?>
