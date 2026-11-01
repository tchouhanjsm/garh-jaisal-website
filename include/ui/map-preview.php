<?php
/**
 * On-demand Google Maps preview.
 *
 * Input:
 * $mapPreview = [
 *   'class' => 'arrival-map-frame', // optional existing layout class
 *   'title' => 'Explore the location.',
 *   'description' => 'Load the map when you need it.',
 *   'embed_url' => 'https://www.google.com/maps?...',
 *   'frame_title' => 'Accessible iframe title.',
 * ];
 *
 * The iframe has no src until the visitor opens the native disclosure.
 * Route templates retain a direct Google Maps link or QR fallback beside it.
 */
$uiMapPreviewConfig = isset($mapPreview) && is_array($mapPreview) ? $mapPreview : [];
$uiMapPreviewEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$uiMapPreviewRawClass = isset($uiMapPreviewConfig['class']) && is_string($uiMapPreviewConfig['class'])
    ? trim($uiMapPreviewConfig['class'])
    : '';
$uiMapPreviewClass = $uiMapPreviewRawClass !== ''
    && preg_match('/^[A-Za-z0-9 _-]+$/D', $uiMapPreviewRawClass)
        ? $uiMapPreviewRawClass
        : '';
$uiMapPreviewTitle = isset($uiMapPreviewConfig['title']) && is_scalar($uiMapPreviewConfig['title'])
    ? trim((string) $uiMapPreviewConfig['title'])
    : '';
$uiMapPreviewDescription = isset($uiMapPreviewConfig['description']) && is_scalar($uiMapPreviewConfig['description'])
    ? trim((string) $uiMapPreviewConfig['description'])
    : '';
$uiMapPreviewFrameTitle = isset($uiMapPreviewConfig['frame_title']) && is_scalar($uiMapPreviewConfig['frame_title'])
    ? trim((string) $uiMapPreviewConfig['frame_title'])
    : '';
$uiMapPreviewEmbedUrl = isset($uiMapPreviewConfig['embed_url']) && is_string($uiMapPreviewConfig['embed_url'])
    ? trim($uiMapPreviewConfig['embed_url'])
    : '';
$uiMapPreviewUrlParts = $uiMapPreviewEmbedUrl !== '' ? parse_url($uiMapPreviewEmbedUrl) : false;
$uiMapPreviewUrlHost = is_array($uiMapPreviewUrlParts)
    ? strtolower((string) ($uiMapPreviewUrlParts['host'] ?? ''))
    : '';
$uiMapPreviewUrlPath = is_array($uiMapPreviewUrlParts)
    ? (string) ($uiMapPreviewUrlParts['path'] ?? '')
    : '';
$uiMapPreviewValidEmbed = is_array($uiMapPreviewUrlParts)
    && strtolower((string) ($uiMapPreviewUrlParts['scheme'] ?? '')) === 'https'
    && in_array($uiMapPreviewUrlHost, ['www.google.com', 'maps.google.com'], true)
    && preg_match('#^/maps(?:/|$)#D', $uiMapPreviewUrlPath) === 1;
$uiMapPreviewValid = $uiMapPreviewValidEmbed
    && $uiMapPreviewTitle !== ''
    && $uiMapPreviewDescription !== ''
    && $uiMapPreviewFrameTitle !== '';
$uiMapPreviewClasses = trim(($uiMapPreviewClass !== '' ? $uiMapPreviewClass . ' ' : '') . 'map-preview');
?>
<?php if ($uiMapPreviewValid): ?>
<div class="<?= $uiMapPreviewEscape($uiMapPreviewClasses) ?>">
  <div class="map-preview__intro">
    <p class="eyebrow">Interactive preview</p>
    <h3><?= $uiMapPreviewEscape($uiMapPreviewTitle) ?></h3>
    <p><?= $uiMapPreviewEscape($uiMapPreviewDescription) ?></p>
  </div>
  <details class="map-preview__details" data-map-preview>
    <summary class="map-preview__summary">
      <span data-map-preview-label>Load interactive map</span>
      <span class="map-preview__indicator" aria-hidden="true"></span>
    </summary>
    <div class="map-preview__frame">
      <iframe
        title="<?= $uiMapPreviewEscape($uiMapPreviewFrameTitle) ?>"
        width="600"
        height="450"
        data-map-src="<?= $uiMapPreviewEscape($uiMapPreviewEmbedUrl) ?>"
        loading="lazy"
        allowfullscreen
        referrerpolicy="strict-origin-when-cross-origin"
      ></iframe>
    </div>
    <noscript>
      <p class="map-preview__fallback">The interactive preview needs JavaScript. Use the verified Google Maps link or QR code beside this panel to open the location.</p>
    </noscript>
  </details>
</div>
<?php endif; ?>
