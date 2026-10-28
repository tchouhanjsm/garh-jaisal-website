<?php
/**
 * Contextual conversion checkpoint after the itinerary and inclusion details.
 * Uses the same enquiry and trip-shortlist state as the experience hero.
 */
if (
    !isset($experience, $experienceContactUrl, $experienceContactLabel)
    || !is_array($experience)
    || ($experience['status'] ?? 'live') !== 'live'
) {
    return;
}
?>
<section class="section experience-conversion-checkpoint" data-experience-conversion-checkpoint data-story-reveal aria-labelledby="experience-conversion-checkpoint-title">
  <div class="site-shell">
    <div class="experience-conversion-checkpoint-panel">
      <div class="experience-conversion-checkpoint-copy">
        <p class="eyebrow">Your next step</p>
        <h2 id="experience-conversion-checkpoint-title">Ready to plan this experience?</h2>
        <p>Ask us to confirm current availability, route, timings and exact inclusions for your travel date. An enquiry is not a confirmed booking.</p>
      </div>
      <div class="actions experience-conversion-checkpoint-actions">
        <a class="button button-primary experience-conversion-contact" <?= empty($isWhatsAppCityTour) ? 'data-contact-handoff="experience"' : '' ?> href="<?= htmlspecialchars($experienceContactUrl, ENT_QUOTES, 'UTF-8') ?>" <?= !empty($isWhatsAppCityTour) ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($experienceContactLabel, ENT_QUOTES, 'UTF-8') ?></a>
        <?php if (!empty($isIndexableExperience) && ($experience['duration_type'] ?? 'hub') !== 'hub'): ?>
          <?php
          $tripItem = [
              'id' => (string) $experience['slug'],
              'title' => (string) $experience['h1'],
              'url' => (string) $experience['slug'] . '.php',
          ];
          $tripButtonClass = 'button button-secondary';
          require __DIR__ . '/trip-add-button.php';
          ?>
        <?php else: ?>
          <a class="button button-secondary" href="catalogue.php">Build your trip shortlist</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php unset($tripItem, $tripButtonClass); ?>
