<?php
/**
 * Data-driven product card used by the unified catalogue.
 * Expected: $product is a resolved catalogue item.
 */
$product = isset($product) && is_array($product) ? $product : [];
$productTitle = (string) ($product['title'] ?? '');
$productUrl = (string) ($product['url'] ?? '');
$productImage = (string) ($product['image'] ?? '');
$productAlt = (string) ($product['alt'] ?? '');
$productSummary = (string) ($product['description'] ?? '');
$productCategory = (string) ($product['category'] ?? 'Experience');
$productDuration = (string) ($product['duration'] ?? '');
$productWhatsapp = (string) ($product['whatsapp_url'] ?? '');
$productBookUrl = (string) ($product['book_url'] ?? '');
?>
<article class="product-card" data-product-card data-product-id="<?= htmlspecialchars((string) ($product['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
  <a class="product-card-media" href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>">
    <img
      src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
      <?= function_exists('gj_image_dimension_attributes') ? gj_image_dimension_attributes($productImage) : '' ?>
      alt="<?= htmlspecialchars($productAlt, ENT_QUOTES, 'UTF-8') ?>"
      loading="lazy"
      decoding="async"
    >
    <span class="product-card-category"><?= htmlspecialchars($productCategory, ENT_QUOTES, 'UTF-8') ?></span>
  </a>
  <div class="product-card-body">
    <h3><a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($productTitle, ENT_QUOTES, 'UTF-8') ?></a></h3>
    <p class="product-card-summary"><?= htmlspecialchars($productSummary, ENT_QUOTES, 'UTF-8') ?></p>
    <div class="product-card-meta">
      <?php if ($productDuration !== ''): ?>
        <span><?= htmlspecialchars($productDuration, ENT_QUOTES, 'UTF-8') ?></span>
      <?php endif; ?>
      <span>Current price on request</span>
    </div>
    <div class="product-card-actions">
      <?php
      $tripItem = [
          'id' => $product['id'] ?? '',
          'title' => $productTitle,
          'url' => $productUrl,
          'group' => $product['trip_group'] ?? '',
      ];
      $tripButtonClass = 'button button-primary';
      require __DIR__ . '/trip-add-button.php';
      ?>
      <a class="button button-light product-card-whatsapp" href="<?= htmlspecialchars($productWhatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ask on WhatsApp</a>
    </div>
    <?php if ($productBookUrl !== ''): ?>
      <a class="product-card-book" href="<?= htmlspecialchars($productBookUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a>
    <?php endif; ?>
  </div>
</article>
<?php
unset($productTitle, $productUrl, $productImage, $productAlt, $productSummary, $productCategory, $productDuration, $productWhatsapp, $productBookUrl, $tripItem, $tripButtonClass);
?>
