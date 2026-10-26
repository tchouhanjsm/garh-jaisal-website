<?php
/**
 * Room-first conversion note for guests exploring experiences.
 * Uses the established booking destination and published stay guide.
 */
$gjStayBookingConfig = require __DIR__ . '/../site-config.php';
$gjStayBookingUrl = (string) ($gjStayBookingConfig['booking_url'] ?? '');
?>
<section class="stay-booking-note" data-stay-booking-note aria-label="Book a room before planning experiences">
  <div class="site-shell stay-booking-note-inner">
    <div class="stay-booking-note-copy">
      <p class="eyebrow">Stay inside the Fort</p>
      <p><strong>Planning an overnight stay?</strong> Room booking is separate from tour enquiries. Check the current room rate and availability, then ask the hotel to confirm any tour route, timing and inclusions for your dates.</p>
    </div>
    <div class="stay-booking-note-actions">
      <?php if ($gjStayBookingUrl !== ''): ?>
        <a class="button button-primary" href="<?= htmlspecialchars($gjStayBookingUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Check room availability</a>
      <?php endif; ?>
      <a class="text-link" href="book-rooms-in-jaisalmer.php">How direct booking works <span aria-hidden="true">→</span></a>
    </div>
  </div>
</section>
<?php unset($gjStayBookingConfig, $gjStayBookingUrl); ?>
