<?php
/**
 * Reusable breadcrumb navigation.
 * Input: $breadcrumbs = [['label' => 'Home', 'href' => '/'], ['label' => 'Current page']].
 * The current page should be the final item and omit href. Output is escaped.
 */
$uiBreadcrumbItems = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : [];
$uiBreadcrumbEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<?php if ($uiBreadcrumbItems): ?>
<nav class="page-breadcrumb" aria-label="Breadcrumb">
  <?php foreach ($uiBreadcrumbItems as $uiBreadcrumbIndex => $uiBreadcrumbItem): ?>
    <?php if (!is_array($uiBreadcrumbItem)) { continue; } ?>
    <?php if ($uiBreadcrumbIndex > 0): ?><span aria-hidden="true">/</span><?php endif; ?>
    <?php if (!empty($uiBreadcrumbItem['href']) && $uiBreadcrumbIndex !== count($uiBreadcrumbItems) - 1): ?>
      <a href="<?= $uiBreadcrumbEscape($uiBreadcrumbItem['href']) ?>"><?= $uiBreadcrumbEscape($uiBreadcrumbItem['label'] ?? '') ?></a>
    <?php else: ?>
      <span<?php if ($uiBreadcrumbIndex === count($uiBreadcrumbItems) - 1): ?> aria-current="page"<?php endif; ?>><?= $uiBreadcrumbEscape($uiBreadcrumbItem['label'] ?? '') ?></span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
<?php endif; ?>
