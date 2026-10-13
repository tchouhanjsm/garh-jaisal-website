<?php
if (!function_exists('gj_image_dimension_attributes')) {
    /**
     * Return dimensions for a trusted local image and advertise a same-dimension WebP derivative when safe.
     * Original paths remain the fallback; derivatives are used only when valid and smaller.
     */
    function gj_image_dimension_attributes($source)
    {
        static $dimensionCache = [];
        static $deliveryCache = [];

        if (
            !is_string($source)
            || !preg_match('~^img/[A-Za-z0-9._/-]+$~D', $source)
            || strpos($source, '..') !== false
        ) {
            return '';
        }

        if (!array_key_exists($source, $dimensionCache)) {
            $imagePath = __DIR__ . '/../' . $source;
            $size = is_file($imagePath) ? @getimagesize($imagePath) : false;
            $dimensionCache[$source] = (
                is_array($size)
                && isset($size[0], $size[1])
                && $size[0] > 0
                && $size[1] > 0
            ) ? [(int) $size[0], (int) $size[1]] : null;
        }

        if ($dimensionCache[$source] === null) {
            return '';
        }

        [$width, $height] = $dimensionCache[$source];
        $attributes = 'width="' . $width . '" height="' . $height . '"';

        if (!array_key_exists($source, $deliveryCache)) {
            $imagePath = __DIR__ . '/../' . $source;
            $relativeSource = substr($source, 4);
            $sourceInfo = pathinfo($relativeSource);
            $sourceDirectory = ($sourceInfo['dirname'] ?? '.') === '.'
                ? ''
                : $sourceInfo['dirname'] . '/';
            $optimizedSource = 'img/optimized/' . $sourceDirectory . $sourceInfo['filename'] . '.webp';
            $optimizedPath = __DIR__ . '/../' . $optimizedSource;
            $optimizedSize = is_file($optimizedPath) ? @getimagesize($optimizedPath) : false;
            $sourceBytes = is_file($imagePath) ? @filesize($imagePath) : false;
            $optimizedBytes = is_file($optimizedPath) ? @filesize($optimizedPath) : false;

            $deliveryCache[$source] = (
                is_array($optimizedSize)
                && isset($optimizedSize[0], $optimizedSize[1], $optimizedSize[2])
                && $optimizedSize[0] === $width
                && $optimizedSize[1] === $height
                && defined('IMAGETYPE_WEBP')
                && $optimizedSize[2] === IMAGETYPE_WEBP
                && $sourceBytes !== false
                && $optimizedBytes !== false
                && $optimizedBytes > 0
                && $optimizedBytes < $sourceBytes
            ) ? $optimizedSource : null;
        }

        if ($deliveryCache[$source] !== null) {
            $attributes .= ' srcset="' . htmlspecialchars($deliveryCache[$source], ENT_QUOTES, 'UTF-8') . ' 1x"';
        }

        return $attributes;
    }
}

$siteConfig = require __DIR__ . '/site-config.php';
$bookingUrl = $bookingUrl ?? $siteConfig['booking_url'];
$hotelPhone = $hotelPhone ?? $siteConfig['hotel_phone'];
$hotelPhoneDisplay = $hotelPhoneDisplay ?? $siteConfig['hotel_phone_display'];
$whatsappUrl = $whatsappUrl ?? $siteConfig['whatsapp_url'];
$activePage = $activePage ?? '';
$navData = require __DIR__ . '/nav-data.php';
$navMain = $navData['main'];
$currentFile = basename((string) parse_url((string) ($_SERVER['SCRIPT_NAME'] ?? ''), PHP_URL_PATH));
$e = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<a class="skip-link" href="#main-content">Skip to main content</a>

<header class="site-header">
  <div class="nav-wrap">
    <div class="site-shell nav-inner">

      <a class="brand" href="index.php" aria-label="Garh Jaisal Haveli home">
        <img
          src="img/jaisalmerlogo.png"
          alt="Garh Jaisal Haveli"
          width="42"
          height="42"
        >
        <span class="brand-copy">
          <strong>Garh Jaisal Haveli</strong>
          <small>Jaisalmer Fort</small>
        </span>
      </a>

      <button
        class="nav-toggle"
        type="button"
        aria-expanded="false"
        aria-controls="main-nav"
        aria-label="Open navigation"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav id="main-nav" class="main-nav" aria-label="Primary navigation">
        <a class="nav-link nav-home-mobile<?= $activePage === 'home' ? ' is-active' : '' ?>" href="index.php"<?= $currentFile === 'index.php' ? ' aria-current="page"' : '' ?>>Home</a>
<?php foreach ($navMain as $navItem):
    $hasGroups = !empty($navItem['groups']);
    $hasChildren = !empty($navItem['children']);
    $hasSub = $hasGroups || $hasChildren;
    $isActive = in_array($activePage, $navItem['active'] ?? [], true);
    $itemClass = 'nav-item' . ($hasSub ? ' has-sub' : '') . ($hasGroups ? ' has-mega' : '') . ($isActive ? ' is-active' : '');
    $subId = 'nav-sub-' . $navItem['key'];
?>
<?php if (!$hasSub): ?>
        <a class="nav-link<?= $isActive ? ' is-active' : '' ?>" href="<?= $e($navItem['url']) ?>"<?= $currentFile === $navItem['url'] ? ' aria-current="page"' : '' ?>><?= $e($navItem['label']) ?></a>
<?php else: ?>
        <div class="<?= $e($itemClass) ?>" data-nav-item>
          <a class="nav-link<?= $isActive ? ' is-active' : '' ?>" href="<?= $e($navItem['url']) ?>"<?= $currentFile === $navItem['url'] ? ' aria-current="page"' : '' ?>><?= $e($navItem['label']) ?></a>
          <button class="nav-sub-toggle" type="button" aria-expanded="false" aria-controls="<?= $e($subId) ?>" aria-label="Show <?= $e($navItem['label']) ?> submenu" data-label="<?= $e($navItem['label']) ?>"><span aria-hidden="true"></span></button>
          <div class="nav-sub<?= $hasGroups ? ' nav-mega' : '' ?>" id="<?= $e($subId) ?>">
<?php if ($hasGroups): ?>
            <div class="nav-mega-grid">
<?php foreach ($navItem['groups'] as $group): ?>
              <div class="nav-group">
                <a class="nav-group-title" href="<?= $e($group['url']) ?>"<?= $currentFile === $group['url'] ? ' aria-current="page"' : '' ?>><?= $e($group['label']) ?></a>
<?php foreach ($group['children'] as $child): ?>
                <a href="<?= $e($child['url']) ?>"<?= $currentFile === $child['url'] ? ' aria-current="page"' : '' ?>><?= $e($child['label']) ?></a>
<?php endforeach; ?>
              </div>
<?php endforeach; ?>
            </div>
<?php if (!empty($navItem['footer_link'])): ?>
            <div class="nav-mega-foot">
              <a href="<?= $e($navItem['footer_link']['url']) ?>"><?= $e($navItem['footer_link']['label']) ?> &rarr;</a>
              <a href="<?= $e($whatsappUrl) ?>" target="_blank" rel="noopener">Not sure? Ask us on WhatsApp</a>
            </div>
<?php endif; ?>
<?php else: ?>
<?php foreach ($navItem['children'] as $child): ?>
            <a href="<?= $e($child['url']) ?>"<?= $currentFile === $child['url'] ? ' aria-current="page"' : '' ?>><?= $e($child['label']) ?></a>
<?php endforeach; ?>
<?php endif; ?>
          </div>
        </div>
<?php endif; ?>
<?php endforeach; ?>
        <a
          class="nav-book"
          href="<?= $e($bookingUrl) ?>"
          target="_blank"
          rel="noopener"
        >
          Book Now
        </a>
      </nav>
    </div>
  </div>
</header>

<div class="contact-dock" aria-label="Contact Garh Jaisal">
  <a
    class="contact-dock-item"
    href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>"
    aria-label="Call Garh Jaisal at <?= htmlspecialchars($hotelPhoneDisplay, ENT_QUOTES, 'UTF-8') ?>"
  >
    <span class="contact-icon" aria-hidden="true">☎</span>
    <span>Call</span>
  </a>

  <a
    class="contact-dock-item whatsapp"
    href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>"
    target="_blank"
    rel="noopener"
    aria-label="WhatsApp Garh Jaisal"
  >
    <img src="img/whatsapp.png" alt="" width="22" height="22">
    <span>WhatsApp</span>
  </a>
</div>

<div class="mobile-cta" aria-label="Quick contact actions">
  <a href="tel:<?= htmlspecialchars($hotelPhone, ENT_QUOTES, 'UTF-8') ?>" class="mobile-cta-contact">Call</a>
  <a href="<?= htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="mobile-cta-whatsapp">WhatsApp</a>
  <a href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="mobile-cta-book">Book Now</a>
</div>
