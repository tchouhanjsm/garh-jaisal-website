<?php
/**
 * Shared hero-media renderer for bespoke experience hubs.
 *
 * The data-driven experience template uses the same class/id and metadata
 * contract; this partial keeps the two hand-crafted hub routes aligned with it.
 */
require_once dirname(__DIR__) . '/image-metadata.php';

$hubMedia = isset($experienceHubMedia) && is_array($experienceHubMedia)
    ? $experienceHubMedia
    : [];
$hubMediaSource = isset($hubMedia['source']) && is_string($hubMedia['source'])
    ? trim($hubMedia['source'])
    : '';
$hubMediaAlt = isset($hubMedia['alt']) && is_string($hubMedia['alt'])
    ? trim($hubMedia['alt'])
    : '';
$hubMediaCaption = isset($hubMedia['caption']) && is_string($hubMedia['caption'])
    ? trim($hubMedia['caption'])
    : '';
$hubMediaMetadata = $hubMediaSource !== '' ? gj_local_image_metadata($hubMediaSource) : null;

if (!is_array($hubMediaMetadata)) {
    throw new RuntimeException('Experience hub hero must reference a safe, decodable local image.');
}
if (strlen($hubMediaAlt) < 15) {
    throw new RuntimeException('Experience hub hero requires descriptive alternative text.');
}
if (strlen($hubMediaCaption) < 20 || strlen($hubMediaCaption) > 180) {
    throw new RuntimeException('Experience hub hero caption must contain 20–180 characters.');
}
?>
<div class="experience-hero-media">
  <img
    src="<?= htmlspecialchars($hubMediaSource, ENT_QUOTES, 'UTF-8') ?>"
    <?= gj_image_dimension_attributes($hubMediaSource, '100vw') ?>
    alt="<?= htmlspecialchars($hubMediaAlt, ENT_QUOTES, 'UTF-8') ?>"
    fetchpriority="high"
    decoding="async"
    aria-describedby="experience-hero-caption">
  <div class="experience-hero-overlay"></div>
  <p id="experience-hero-caption" class="experience-hero-caption"><?= htmlspecialchars($hubMediaCaption, ENT_QUOTES, 'UTF-8') ?></p>
</div>
