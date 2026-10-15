<?php
/**
 * The Trip tray stores only product selections in the current browser tab.
 * It does not collect personal data or submit an enquiry.
 */
$tripWhatsappBase = (string) ($footerWhatsapp ?? '');
?>
<div class="trip-tray-root" data-trip-tray>
  <p class="trip-tray-status" data-trip-status aria-live="polite" aria-atomic="true"></p>
  <button
    type="button"
    class="trip-tray-trigger"
    data-trip-toggle
    aria-controls="trip-tray-panel"
    aria-expanded="false"
    hidden
  >Your Trip <span class="trip-tray-count" data-trip-count>0</span></button>

  <aside class="trip-tray-panel" id="trip-tray-panel" data-trip-panel aria-labelledby="trip-tray-title" hidden>
    <div class="trip-tray-header">
      <div>
        <p class="eyebrow">Your shortlist</p>
        <h2 id="trip-tray-title">Build one Trip enquiry.</h2>
      </div>
      <button type="button" class="trip-tray-close" data-trip-close aria-label="Close Trip tray">×</button>
    </div>
    <p class="trip-tray-empty" data-trip-empty>Add a stay, desert experience, guided tour, transport arrangement or rooftop dining option.</p>
    <ul class="trip-tray-items" data-trip-items></ul>
    <div class="trip-tray-actions">
      <button type="button" class="button button-light trip-tray-clear" data-trip-clear hidden>Clear Trip</button>
      <a
        class="button button-primary trip-tray-send"
        href="<?= htmlspecialchars($tripWhatsappBase, ENT_QUOTES, 'UTF-8') ?>"
        data-trip-send
        data-whatsapp-base="<?= htmlspecialchars($tripWhatsappBase, ENT_QUOTES, 'UTF-8') ?>"
        target="_blank"
        rel="noopener"
        hidden
      >Prepare enquiry on WhatsApp</a>
    </div>
    <p class="trip-tray-privacy-note">Your selections stay in this browser tab. WhatsApp opens with a draft for you to review; nothing is sent by this website.</p>
  </aside>
</div>
<?php unset($tripWhatsappBase); ?>
