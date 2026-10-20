<?php
$activePage = 'blog';
$siteConfig = require __DIR__ . '/include/site-config.php';
$siteUrl = rtrim($siteConfig['site_url'], '/');
$pageTitle = 'Jaisalmer Travel Guides | Garh Jaisal Haveli';
$pageDescription = 'Explore practical Jaisalmer travel guides for the Fort, desert safaris, 2- and 3-day itineraries, honeymoon planning and arrival at Garh Jaisal Haveli.';
$canonical = $siteUrl . '/blog.php';
$editorialGuides = require __DIR__ . '/include/editorial-guides-data.php';
$buildResponsivePreload = static function ($imageStem, $sizes, $fallback) {
    $srcset = [];
    foreach ([480, 800, 1200] as $candidateWidth) {
        $candidate = '/img/responsive/' . $imageStem . '-' . $candidateWidth . '.webp';
        if (is_file(__DIR__ . $candidate) && !is_link(__DIR__ . $candidate)) {
            $srcset[] = $candidate . ' ' . $candidateWidth . 'w';
        }
    }
    $preload = [
        'href' => $srcset ? strtok($srcset[0], ' ') : $fallback,
        'fetchpriority' => 'high',
    ];
    if ($srcset) {
        $preload['imagesrcset'] = implode(', ', $srcset);
        $preload['imagesizes'] = $sizes;
    }
    return $preload;
};
?>
<!doctype html>
<html lang="en">
<?php
require_once __DIR__ . '/include/schema.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Blog'],
];
$breadcrumbSchema = gj_schema_breadcrumb_list($siteUrl, '/blog.php', $breadcrumbs);
$page = [
    'title' => $pageTitle,
    'description' => $pageDescription,
    'path' => '/blog.php',
    'preload' => [
        $buildResponsivePreload('j7', '(max-width: 900px) 100vw, 60vw', '/img/j7.jpg'),
    ],
    'og_type' => 'website',
    'og_title' => $pageTitle,
    'og_description' => $pageDescription,
    'og_image' => '/img/j7.jpg',
    'twitter_title' => $pageTitle,
    'twitter_description' => $pageDescription,
    'twitter_image' => '/img/j7.jpg',
    'css' => ['css/article.css', 'css/thar-sunset-story.css'],
    'schema' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $pageTitle,
            'description' => $pageDescription,
            'isPartOf' => ['@id' => $siteUrl . '/#website'],
            'about' => ['@id' => $siteUrl . '/#hotel'],
            'breadcrumb' => ['@id' => $canonical . '#breadcrumb'],
        ],
        $breadcrumbSchema,
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<main class="article-page thar-sunset-story thar-sunset-story--journal" id="main-content" tabindex="-1">
  <section class="article-hero">
    <div class="site-shell article-hero-inner">
      <?php require __DIR__ . '/include/ui/breadcrumb.php'; ?>
      <p class="eyebrow">Blog</p>
      <h1>Travel guides for Jaisalmer’s Fort, food and desert.</h1>
      <p class="lede">Explore the living Fort, carved havelis, regional food, old-city markets and the landscapes beyond the city. Use these guides to plan your interests, then confirm date-sensitive access, menus and arrangements directly.</p>
    </div>
  </section>
  <section class="section" data-story-reveal>
    <div class="site-shell">
      <div class="section-head"><div><p class="eyebrow">Start with the useful stuff</p><h2>Guides written around an actual stay.</h2></div><p>These guides use the hotel's current guest information and are designed to answer practical questions before you arrive. Time-sensitive details should always be confirmed for your travel date.</p></div>
      <div class="blog-grid">
        <?php
        $defaultArchiveTitles = [
            'jaisalmer-fort-guide' => 'Jaisalmer Fort Guide',
            'desert-safari-guide' => 'How to Choose a Jaisalmer Desert Safari',
            'jaisalmer-2-day-itinerary' => 'Jaisalmer 2-Day Itinerary',
            'jaisalmer-3-day-itinerary' => 'Jaisalmer in 3 Days: Fort, Heritage & Desert',
            'jaisalmer-honeymoon-guide' => 'Jaisalmer Honeymoon Guide',
            'best-time-to-visit-jaisalmer' => 'Best Time to Visit Jaisalmer',
            'how-to-reach-jaisalmer' => 'How to Reach Jaisalmer',
            'jaisalmer-desert-safari-packing-list' => 'What to Pack for a Desert Safari',
            'jaisalmer-havelis-guide' => 'Jaisalmer Havelis: Sandstone Architecture',
            'jaisalmer-food-guide' => 'A Food Lover’s Guide to Jaisalmer',
            'jaisalmer-beyond-the-fort-guide' => 'Beyond Jaisalmer Fort',
            'jaisalmer-markets-and-crafts-guide' => 'Jaisalmer Markets and Crafts',
        ];
        $defaultArchiveImages = [
            'jaisalmer-fort-guide' => 'img/j7.jpg',
            'desert-safari-guide' => 'img/desert3.jpg',
            'jaisalmer-2-day-itinerary' => 'img/1.webp',
            'jaisalmer-3-day-itinerary' => 'img/s4.jpg',
            'jaisalmer-honeymoon-guide' => 'img/big6.jpg',
            'best-time-to-visit-jaisalmer' => 'img/6.webp',
            'how-to-reach-jaisalmer' => 'img/jcity.jpg',
            'jaisalmer-desert-safari-packing-list' => 'img/desert10.jpg',
            'jaisalmer-havelis-guide' => 'img/j5.jpg',
            'jaisalmer-food-guide' => 'img/Desert_Camp/camp_dinner.jpeg',
            'jaisalmer-beyond-the-fort-guide' => 'img/j8.jpg',
            'jaisalmer-markets-and-crafts-guide' => 'img/j9.jpg',
        ];
        $defaultArchiveImageAlts = [
            'jaisalmer-fort-guide' => 'Jaisalmer Fort and a public courtyard from street level',
            'desert-safari-guide' => 'Camel riders travelling over dunes near Jaisalmer',
            'jaisalmer-2-day-itinerary' => 'Panoramic view across Jaisalmer’s sandstone city',
            'jaisalmer-3-day-itinerary' => 'Jaisalmer’s old city illuminated after dark',
            'jaisalmer-honeymoon-guide' => 'Rooftop dining terrace at a sandstone heritage hotel',
            'best-time-to-visit-jaisalmer' => 'Rooftop seating with views over Jaisalmer',
            'how-to-reach-jaisalmer' => 'View over Jaisalmer’s historic city from the fort edge',
            'jaisalmer-desert-safari-packing-list' => 'Jeep crossing open sand on a desert route',
            'jaisalmer-havelis-guide' => 'Intricate sandstone balconies on a Jaisalmer haveli',
            'jaisalmer-food-guide' => 'A meal spread served at a desert camp near Jaisalmer',
            'jaisalmer-beyond-the-fort-guide' => 'Heritage buildings beside the water near Jaisalmer',
            'jaisalmer-markets-and-crafts-guide' => 'Colourful traditional puppets at a Jaisalmer market',
        ];
        $archiveGuides = array_filter($editorialGuides, static function ($guide) {
            return is_array($guide) && ($guide['status'] ?? 'live') === 'live';
        });
        foreach ($archiveGuides as $guide):
            $slug = (string) ($guide['slug'] ?? '');
            $cardTitle = (string) ($guide['archive_title'] ?? $defaultArchiveTitles[$slug] ?? $guide['h1'] ?? $guide['title'] ?? 'Jaisalmer travel guide');
            $cardDescription = (string) ($guide['archive_description'] ?? $guide['description'] ?? '');
            $cardCategory = (string) ($guide['archive_category'] ?? $guide['eyebrow'] ?? 'Travel guide');
            $cardImage = (string) ($guide['archive_image'] ?? $defaultArchiveImages[$slug] ?? $guide['hero_image'] ?? '');
            $cardImageAlt = (string) ($guide['archive_image_alt'] ?? $defaultArchiveImageAlts[$slug] ?? $guide['hero_alt'] ?? '');
            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) continue;
        ?>
          <article class="blog-card">
            <a href="<?= htmlspecialchars($slug . '.php', ENT_QUOTES, 'UTF-8') ?>">
              <?php if ($cardImage !== ''): ?>
                <div class="blog-card-image"><img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" <?= gj_image_dimension_attributes($cardImage, '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw') ?> alt="<?= htmlspecialchars($cardImageAlt, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async"></div>
              <?php endif; ?>
              <div class="blog-card-body">
                <p class="blog-card-meta"><?= htmlspecialchars($cardCategory, ENT_QUOTES, 'UTF-8') ?></p>
                <h2><?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($cardDescription, ENT_QUOTES, 'UTF-8') ?></p>
                <span class="text-link">Read the guide →</span>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
        <article class="blog-card"><a href="book-rooms-in-jaisalmer.php">
          <div class="blog-card-body"><p class="blog-card-meta">Staying inside the Fort</p><h2>Staying inside Jaisalmer Fort</h2><p>The last part of the journey is on foot, and a room, a rooftop meal and a desert night can fit together into one stay.</p><span class="text-link">Read the stay guide →</span></div>
        </a></article>
      </div>
    </div>
  </section>
  <?php
$ctaBand = [
    'section_class' => 'section article-footer-cta',
    'inner_class' => 'site-shell article-cta-inner',
    'eyebrow' => 'Plan from the Fort',
    'title' => 'Stay inside Jaisalmer Fort.',
    'text' => 'Explore rooms, arrival information and desert experiences before you book.',
    'actions_class' => 'actions',
    'primary' => [
        'label' => 'View rooms',
        'href' => 'room.php',
        'class' => 'button button-primary',
    ],
    'secondary' => [
        'label' => 'Explore experiences',
        'href' => 'pack.php',
        'class' => 'button button-secondary',
    ],
];
require __DIR__ . '/include/ui/cta-band.php';
?>
</main>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
