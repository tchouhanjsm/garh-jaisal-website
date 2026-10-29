<?php
$activePage = 'gallery';
$siteConfig = require __DIR__ . '/include/site-config.php';
$bookingUrl = $siteConfig['booking_url'];
?>
<!doctype html>
<html lang="en">
<?php
$schemaSiteUrl = rtrim((string) $siteConfig['site_url'], '/');
$schemaPageUrl = $schemaSiteUrl . '/gallery.php';

// The hero media is full-bleed. Keep preload candidates and the rendered image's
// sizes hint aligned so a 1280px slot does not download the smaller 45vw candidate.
$galleryHeroSrcsetCandidates = [];
foreach ([480, 800, 1200] as $galleryHeroWidth) {
    $galleryHeroCandidate = '/img/responsive/v2/rooftop-dusk-' . $galleryHeroWidth . '.webp';
    $galleryHeroCandidatePath = __DIR__ . $galleryHeroCandidate;
    if (is_file($galleryHeroCandidatePath) && !is_link($galleryHeroCandidatePath)) {
        $galleryHeroSrcsetCandidates[] = $galleryHeroCandidate . ' ' . $galleryHeroWidth . 'w';
    }
}
$galleryHeroPreload = [
    'href' => '/img/v2/rooftop-dusk.jpg',
    'fetchpriority' => 'high',
];
if ($galleryHeroSrcsetCandidates) {
    $galleryHeroPreload['imagesrcset'] = implode(', ', $galleryHeroSrcsetCandidates);
    $galleryHeroPreload['imagesizes'] = '100vw';
}
$page = [
    'title' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
    'description' => 'Explore photos of heritage rooms, the haveli, rooftop dining and desert experiences at Garh Jaisal Haveli inside Jaisalmer Fort, Jaisalmer.',
    'path' => '/gallery.php',
    'preload' => [$galleryHeroPreload],
    'og_title' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
    'og_description' => 'See the rooms, haveli, desert experiences and dining at Garh Jaisal Haveli.',
    'og_image' => '/img/s1.jpg',
    'og_image_alt' => 'Interior of Garh Jaisal Haveli',
    'twitter_title' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
    'twitter_description' => 'See Garh Jaisal Haveli, its rooms, desert experiences and dining.',
    'twitter_image' => '/img/s1.jpg',
    'css' => ['css/gallery.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $schemaPageUrl . '#webpage',
            'url' => $schemaPageUrl,
            'name' => 'Gallery | Garh Jaisal Haveli, Jaisalmer Fort',
            'isPartOf' => ['@id' => $schemaSiteUrl . '/#website'],
            'about' => ['@id' => $schemaSiteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $schemaPageUrl . '#breadcrumb'],
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $schemaPageUrl . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $schemaSiteUrl . '/',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Gallery',
                    'item' => $schemaPageUrl,
                ],
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>

<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>

<main class="gallery-page" id="main-content" tabindex="-1">
  <section class="page-hero page-hero--photo gallery-hero">
    <div class="page-hero-media" aria-hidden="true">
      <img src="img/v2/rooftop-dusk.jpg" <?= function_exists('gj_responsive_image_srcset') ? gj_responsive_image_srcset('img/v2/rooftop-dusk.jpg') : '' ?> <?= gj_image_dimension_attributes('img/v2/rooftop-dusk.jpg', '100vw') ?> alt="" aria-hidden="true" fetchpriority="high" decoding="async">
    </div>
    <div class="site-shell page-hero-inner">
      <?php
      $breadcrumbs = [
          ['label' => 'Home', 'href' => '/'],
          ['label' => 'Gallery'],
      ];
      require __DIR__ . '/include/ui/breadcrumb.php';
      ?>
      <p class="eyebrow">Gallery</p>
      <h1>See Garh Jaisal Haveli.</h1>
      <p>
        Browse the haveli, rooms, desert experiences and dining spaces before you arrive.
      </p>
      <div class="actions">
        <a class="button button-primary" href="room.php">Explore rooms</a>
        <a class="button button-light" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Book your stay</a>
      </div>
    </div>
  </section>

  <section class="section gallery-intro">
    <div class="site-shell gallery-intro-inner">
      <div>
        <p class="eyebrow">The collection</p>
        <h2>A look at the place before your stay.</h2>
      </div>
      <p>
        Explore the spaces and experiences that make up a stay at Garh Jaisal Haveli.
        Select an image to open the full-size photograph.
      </p>
    </div>

    <div class="site-shell gallery-jump" aria-label="Gallery sections">
      <a href="#hotel">Fort &amp; haveli</a>
      <a href="#rooms">Rooms</a>
      <a href="#desert">Desert</a>
      <a href="#restaurant">Rooftop dining</a>
    </div>

    <div class="site-shell gallery-filters" hidden role="group" aria-label="Filter gallery photos">
      <button type="button" data-gallery-filter="all" aria-pressed="true">All photos</button>
      <button type="button" data-gallery-filter="hotel" aria-pressed="false">Fort &amp; haveli</button>
      <button type="button" data-gallery-filter="rooms" aria-pressed="false">Rooms</button>
      <button type="button" data-gallery-filter="desert" aria-pressed="false">Desert</button>
      <button type="button" data-gallery-filter="restaurant" aria-pressed="false">Rooftop dining</button>
    </div>
  </section>

  <?php
  $sections = require __DIR__ . '/include/gallery-data.php';

  foreach ($sections as $id => $section):
  ?>
    <section class="section gallery-section" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-gallery-category="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
      <div class="site-shell">
        <div class="section-head gallery-section-head">
          <div>
            <p class="eyebrow"><?= htmlspecialchars($section['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          </div>
          <p><?= htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <p class="gallery-count" data-gallery-count role="status" aria-live="polite" aria-atomic="true">
          <?= count($section['images']) ?> photos in this collection.
        </p>
        <div class="gallery-grid" id="gallery-grid-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
          <?php foreach ($section['images'] as $image): ?>
            <?php $src = $image['src']; $alt = $image['alt']; ?>
            <figure class="gallery-item gallery-item--<?= htmlspecialchars($image['crop'], ENT_QUOTES, 'UTF-8') ?>">
              <a href="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" data-gallery-open aria-label="Open full-size image: <?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>">
                <img
                  src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                  <?= function_exists('gj_responsive_image_srcset') ? gj_responsive_image_srcset($src) : '' ?> <?= gj_image_dimension_attributes($src, '(max-width: 500px) 100vw, (max-width: 760px) 50vw, (max-width: 1000px) 33vw, 25vw') ?>
                  alt="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                  loading="lazy"
                  decoding="async"
                >
                <span class="gallery-item-label">View photo</span>
              </a>
            </figure>
          <?php endforeach; ?>
        </div>
        <button type="button" class="button button-light gallery-more" data-gallery-more aria-controls="gallery-grid-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" hidden>
          Show more photos
        </button>
        <?php if (!empty($section['cta'])): ?>
          <div class="gallery-section-cta">
            <div class="gallery-section-cta-copy">
              <h3><?= htmlspecialchars($section['cta']['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($section['cta']['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="button button-primary" href="<?= htmlspecialchars($section['cta']['href'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($section['cta']['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <dialog class="gallery-lightbox" aria-label="Gallery photo viewer" aria-describedby="gallery-lightbox-instructions">
    <div class="gallery-lightbox-inner">
      <div class="gallery-lightbox-top">
        <p class="eyebrow">Photo viewer</p>
        <button type="button" class="gallery-lightbox-close" data-gallery-close>Close <span aria-hidden="true">×</span></button>
      </div>
      <p id="gallery-lightbox-instructions" class="gallery-lightbox-help">Use the left and right arrow keys to browse photos. Press Escape to close.</p>
      <figure class="gallery-lightbox-figure">
        <img data-gallery-lightbox-image src="img/v2/rooftop-dusk.jpg" <?= function_exists('gj_responsive_image_srcset') ? gj_responsive_image_srcset('img/v2/rooftop-dusk.jpg') : '' ?> <?= gj_image_dimension_attributes('img/v2/rooftop-dusk.jpg') ?> alt="Rooftop dining at Garh Jaisal Haveli" loading="lazy" decoding="async">
        <figcaption data-gallery-lightbox-caption></figcaption>
        <p class="gallery-lightbox-position" data-gallery-lightbox-position role="status" aria-live="polite" aria-atomic="true">Choose a photo to begin.</p>
      </figure>
      <div class="gallery-lightbox-actions">
        <button type="button" data-gallery-previous>Previous photo</button>
        <button type="button" data-gallery-next>Next photo</button>
      </div>
    </div>
  </dialog>

  <?php
$ctaBand = [
    'section_class' => 'section gallery-cta',
    'inner_class' => 'site-shell gallery-cta-inner',
    'eyebrow' => 'Plan your stay',
    'title' => 'See a room you like?',
    'text' => 'Explore the room collection or check availability for your dates.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'View rooms',
        'href' => 'room.php',
        'class' => 'button button-primary',
    ],
    'secondary' => [
        'label' => 'Book your stay',
        'href' => $bookingUrl,
        'class' => 'button gallery-cta-light',
        'target' => '_blank',
        'rel' => 'noopener',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>
</main>

<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
