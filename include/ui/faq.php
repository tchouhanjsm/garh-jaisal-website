<?php
/**
 * Reusable FAQ list.
 * Input: $faq = [
 *   items => [['question' => '...', 'answer' => '...' | [
 *     ['text' => '...'], ['link' => ['label' => '...', 'href' => '...']]
 *   ]]],
 *   list_class (default faq-list), item_class, answer_wrapper_class
 * ].
 * Question, answer segments, link labels and hrefs are always escaped.
 */
$uiFaq = isset($faq) && is_array($faq) ? $faq : [];
$uiFaqItems = isset($uiFaq['items']) && is_array($uiFaq['items']) ? $uiFaq['items'] : [];
$uiFaqEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$uiFaqListClass = (string) ($uiFaq['list_class'] ?? 'faq-list');
$uiFaqItemClass = (string) ($uiFaq['item_class'] ?? '');
$uiFaqAnswerClass = (string) ($uiFaq['answer_wrapper_class'] ?? '');
?>
<?php if ($uiFaqItems): ?>
<div class="<?= $uiFaqEscape($uiFaqListClass) ?>">
  <?php foreach ($uiFaqItems as $uiFaqItem): ?>
    <?php
    if (!is_array($uiFaqItem)) {
        continue;
    }
    $uiFaqAnswer = $uiFaqItem['answer'] ?? '';
    $uiFaqParts = is_array($uiFaqAnswer) ? $uiFaqAnswer : [['text' => $uiFaqAnswer]];
    ?>
    <details<?php if ($uiFaqItemClass !== ''): ?> class="<?= $uiFaqEscape($uiFaqItemClass) ?>"<?php endif; ?>>
      <summary><?= $uiFaqEscape($uiFaqItem['question'] ?? '') ?></summary>
      <?php if ($uiFaqAnswerClass !== ''): ?>
        <div class="<?= $uiFaqEscape($uiFaqAnswerClass) ?>">
      <?php endif; ?>
      <p><?php foreach ($uiFaqParts as $uiFaqPart): ?><?php
          if (!is_array($uiFaqPart)) {
              echo $uiFaqEscape($uiFaqPart);
          } elseif (isset($uiFaqPart['link']) && is_array($uiFaqPart['link'])) {
              $uiFaqLink = $uiFaqPart['link'];
              $uiFaqHref = (string) ($uiFaqLink['href'] ?? '#');
              $uiFaqLabel = (string) ($uiFaqLink['label'] ?? '');
              echo '<a href="' . $uiFaqEscape($uiFaqHref) . '">' . $uiFaqEscape($uiFaqLabel) . '</a>';
          } else {
              echo $uiFaqEscape($uiFaqPart['text'] ?? '');
          }
      ?><?php endforeach; ?></p>
      <?php if ($uiFaqAnswerClass !== ''): ?>
        </div>
      <?php endif; ?>
    </details>
  <?php endforeach; ?>
</div>
<?php endif; ?>
