<?php
/**
 * Accessible previous/next photo carousel.
 *
 * Expected input:
 * $photoCarousel = [
 *   'label' => 'Descriptive gallery name',
 *   'variant' => 'experience|room|restaurant|editorial',
 *   'slides' => [['src' => 'img/photo.jpg', 'alt' => '...', 'caption' => '...']],
 * ];
 *
 * Without JavaScript every supplied photo remains visible. JavaScript only
 * enhances the group into a carousel; it never auto-advances or hides content
 * until initialization completes.
 */
$photoCarousel = isset($photoCarousel) && is_array($photoCarousel) ? $photoCarousel : [];
$photoCarouselLabel = trim((string) ($photoCarousel['label'] ?? 'Photo gallery'));
$photoCarouselVariant = (string) ($photoCarousel['variant'] ?? 'editorial');
if (!in_array($photoCarouselVariant, ['experience', 'room', 'restaurant', 'editorial'], true)) {
    $photoCarouselVariant = 'editorial';
}

switch ($photoCarouselVariant) {
    case 'room':
        // Room directory cards use one column through 1080px, then two columns.
        // At 1280px the active image slot is ~563px, not the previous 33vw (~422px).
        $photoCarouselSizes = '(max-width: 1080px) 100vw, min(45vw, 600px)';
        break;
    case 'restaurant':
        // The rooftop/dining gallery is explicitly capped at 980px in restaurant.css.
        $photoCarouselSizes = '(max-width: 640px) 100vw, (max-width: 1024px) 90vw, 980px';
        break;
    default:
        // Experience and editorial galleries fill the site shell rather than a 1000px slot.
        $photoCarouselSizes = '(max-width: 640px) 100vw, (max-width: 1024px) 90vw, 95vw';
        break;
}
$photoCarouselRoot = dirname(__DIR__, 2);
$photoCarouselImages = [];
$photoCarouselSourceSlides = isset($photoCarousel['slides']) && is_array($photoCarousel['slides'])
    ? $photoCarousel['slides']
    : [];

foreach ($photoCarouselSourceSlides as $photoCarouselSlide) {
    if (!is_array($photoCarouselSlide)) {
        continue;
    }
    $photoCarouselSrc = trim((string) ($photoCarouselSlide['src'] ?? ''));
    $photoCarouselAlt = trim((string) ($photoCarouselSlide['alt'] ?? ''));
    if (
        $photoCarouselAlt === ''
        || !preg_match('#^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$#iD', $photoCarouselSrc)
        || strpos($photoCarouselSrc, '..') !== false
        || strpos($photoCarouselSrc, '\\') !== false
    ) {
        continue;
    }

    $photoCarouselAbsolute = $photoCarouselRoot . '/' . $photoCarouselSrc;
    if (is_link($photoCarouselAbsolute) || !is_file($photoCarouselAbsolute)) {
        continue;
    }
    $photoCarouselCursor = dirname($photoCarouselAbsolute);
    $photoCarouselImageRoot = $photoCarouselRoot . '/img';
    $photoCarouselSafeDirectories = true;
    while (
        $photoCarouselCursor !== $photoCarouselImageRoot
        && strpos($photoCarouselCursor, $photoCarouselImageRoot . DIRECTORY_SEPARATOR) === 0
    ) {
        if (is_link($photoCarouselCursor) || !is_dir($photoCarouselCursor)) {
            $photoCarouselSafeDirectories = false;
            break;
        }
        $photoCarouselCursor = dirname($photoCarouselCursor);
    }
    if (!$photoCarouselSafeDirectories) {
        continue;
    }

    $photoCarouselImages[] = [
        'src' => $photoCarouselSrc,
        'alt' => $photoCarouselAlt,
        'caption' => trim((string) ($photoCarouselSlide['caption'] ?? $photoCarouselAlt)),
    ];
}
?>
<?php if ($photoCarouselImages !== []): ?>
  <div class="photo-carousel photo-carousel--<?= htmlspecialchars($photoCarouselVariant, ENT_QUOTES, 'UTF-8') ?>"
       data-photo-carousel
       role="region"
       aria-label="<?= htmlspecialchars($photoCarouselLabel, ENT_QUOTES, 'UTF-8') ?>">
    <div class="photo-carousel-stage" data-photo-carousel-stage>
      <?php foreach ($photoCarouselImages as $photoCarouselIndex => $photoCarouselImage): ?>
        <figure class="photo-carousel-slide" data-photo-carousel-slide>
          <img
            src="<?= htmlspecialchars($photoCarouselImage['src'], ENT_QUOTES, 'UTF-8') ?>"
            <?= function_exists('gj_image_dimension_attributes') ? gj_image_dimension_attributes($photoCarouselImage['src'], $photoCarouselSizes) : '' ?>
            alt="<?= htmlspecialchars($photoCarouselImage['alt'], ENT_QUOTES, 'UTF-8') ?>"
            loading="lazy"
            decoding="async">
          <?php if ($photoCarouselImage['caption'] !== ''): ?>
            <figcaption><?= htmlspecialchars($photoCarouselImage['caption'], ENT_QUOTES, 'UTF-8') ?></figcaption>
          <?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
    <?php if (count($photoCarouselImages) > 1): ?>
      <div class="photo-carousel-controls" data-photo-carousel-controls hidden>
        <button class="photo-carousel-arrow" type="button" data-photo-carousel-prev aria-label="Previous photo">
          <span aria-hidden="true">←</span><span>Previous</span>
        </button>
        <p class="photo-carousel-status" data-photo-carousel-status role="status" aria-live="polite" aria-atomic="true">
          Photo 1 of <?= count($photoCarouselImages) ?>
        </p>
        <button class="photo-carousel-arrow" type="button" data-photo-carousel-next aria-label="Next photo">
          <span>Next</span><span aria-hidden="true">→</span>
        </button>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php unset(
    $photoCarousel,
    $photoCarouselLabel,
    $photoCarouselVariant,
    $photoCarouselSizes,
    $photoCarouselRoot,
    $photoCarouselImages,
    $photoCarouselSourceSlides,
    $photoCarouselSlide,
    $photoCarouselSrc,
    $photoCarouselAlt,
    $photoCarouselAbsolute,
    $photoCarouselCursor,
    $photoCarouselImageRoot,
    $photoCarouselSafeDirectories,
    $photoCarouselIndex,
    $photoCarouselImage
); ?>
