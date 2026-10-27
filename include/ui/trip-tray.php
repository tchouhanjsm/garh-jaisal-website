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
    <section class="trip-tray-details" data-trip-details aria-labelledby="trip-tray-details-title">
      <div class="trip-tray-details-heading">
        <h3 id="trip-tray-details-title">Add your travel details</h3>
        <p id="trip-tray-details-help">Optional. These details are added to your WhatsApp draft only; this website does not save them.</p>
      </div>
      <div class="trip-tray-details-grid">
        <label class="trip-tray-field" for="trip-tray-start-date">
          <span>Travel starts</span>
          <input id="trip-tray-start-date" type="date" data-trip-start-date autocomplete="off" aria-describedby="trip-tray-details-help trip-tray-details-error">
        </label>
        <label class="trip-tray-field" for="trip-tray-end-date">
          <span>Travel ends</span>
          <input id="trip-tray-end-date" type="date" data-trip-end-date autocomplete="off" aria-describedby="trip-tray-details-help trip-tray-details-error">
        </label>
        <label class="trip-tray-field trip-tray-field--travellers" for="trip-tray-travellers">
          <span>Number of travellers</span>
          <input id="trip-tray-travellers" type="number" min="1" step="1" inputmode="numeric" data-trip-travellers aria-describedby="trip-tray-details-help trip-tray-details-error">
        </label>
      </div>
      <p class="trip-tray-details-error" id="trip-tray-details-error" data-trip-details-error role="status" aria-live="polite" aria-atomic="true" hidden></p>
    </section>
    <div class="trip-tray-actions">
      <button type="button" class="button button-light trip-tray-clear" data-trip-clear hidden>Clear Trip</button>
      <a
        class="button button-secondary trip-tray-contact"
        href="contact.php"
        data-trip-contact
        hidden
      >Continue to contact form</a>
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
    <p class="trip-tray-privacy-note">Your selected options stay in this browser tab. Choose WhatsApp to review a draft with your optional travel details, or continue to the contact form to carry the selected options into a message. Opening the form does not send an enquiry or reserve anything.</p>
  </aside>
</div>
<?php unset($tripWhatsappBase); ?>
