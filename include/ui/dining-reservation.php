  <section id="reserve" class="section restaurant-reserve">
    <div class="site-shell restaurant-reserve-grid">
      <div>
        <p class="eyebrow eyebrow-light">Table enquiry</p>
        <h2>Request a table.</h2>
        <p>For same-day enquiries, call <a href="tel:<?= e($hotelPhone) ?>"><?= e($hotelPhoneDisplay) ?></a> or message us on WhatsApp.</p>
        <a class="button button-light" href="<?= e($waLink) ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-restaurant-reserve" data-cta-location="restaurant-reservation">Message on WhatsApp</a>
      </div>

      <div class="restaurant-form-card">
        <?php if ($sent): ?>
          <div class="restaurant-notice" role="status">
            <h3>Request recorded.</h3>
            <p>Your table enquiry reference is <strong><?= e($reservationReference) ?></strong>. This is not a confirmed booking; the hotel must confirm service hours and availability.</p>
            <?php if ($reservationEmailNotified): ?>
              <p>The hotel was notified by email. For urgent confirmation, call or message us on WhatsApp.</p>
            <?php else: ?>
              <p>Automated email notification could not be confirmed. Please call <a href="tel:<?= e($hotelPhone) ?>"><?= e($hotelPhoneDisplay) ?></a> or message the hotel on WhatsApp and quote your reference to make sure the request is seen.</p>
            <?php endif; ?>
          </div>
        <?php elseif (!$showReservationForm): ?>
          <div class="restaurant-notice restaurant-reservation-unavailable" aria-labelledby="reservation-unavailable-heading">
            <h3 id="reservation-unavailable-heading">Enquire about a table</h3>
            <p>Online table requests aren't available on this page right now. Please call or message the hotel to ask about table availability.</p>
            <div class="restaurant-reservation-actions">
              <a class="button button-primary" href="tel:<?= e($hotelPhone) ?>" data-cta-id="call-restaurant-reserve" data-cta-location="restaurant-reservation">Call the hotel</a>
              <a class="button button-secondary" href="<?= e($waLink) ?>" target="_blank" rel="noopener" data-cta-id="whatsapp-restaurant-follow-up" data-cta-location="restaurant-request-status">Ask on WhatsApp</a>
              <a class="button button-secondary" href="contact.php?topic=dining">Send a dining enquiry</a>
            </div>
          </div>
        <?php else: ?>
          <?php if (isset($errors['form'])): ?>
            <p class="restaurant-error" role="alert"><?= e($errors['form']) ?></p>
          <?php endif; ?>

          <form class="restaurant-form" method="post" action="#reserve" novalidate>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['gjk_csrf']) ?>">
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="restaurant-hp" aria-hidden="true">

            <?php
            $fields = [
                ['name', 'Name *', 'text', 'required autocomplete="name"'],
                ['whatsapp', 'WhatsApp number *', 'tel', 'required autocomplete="tel" inputmode="tel"'],
                ['email', 'Email', 'email', 'autocomplete="email"'],
                ['date', 'Date *', 'date', 'required min="' . (new DateTimeImmutable('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d') . '" autocomplete="off"'],
                ['time', 'Preferred time *', 'time', 'required step="1800" min="00:00" max="23:30" autocomplete="off"'],
                ['guests', 'Guests *', 'number', 'required min="1" max="20" inputmode="numeric" autocomplete="off"'],
            ];
            foreach ($fields as [$id, $label, $type, $attrs]):
                $hasFieldError = isset($errors[$id]);
                $fieldErrorId = 'restaurant-error-' . $id;
            ?>
              <div class="restaurant-field <?= $hasFieldError ? 'has-error' : '' ?>">
                <label for="<?= e($id) ?>"><?= e($label) ?></label>
                <?php if ($id === 'time' && !empty($showReservationTimeSlots)): ?>
                <select id="time" name="time" required <?= $hasFieldError ? 'aria-invalid="true" aria-describedby="' . e($fieldErrorId) . '"' : '' ?>>
                  <option value="">Select a preferred time</option>
                  <?php for ($minutes = 0; $minutes < 24 * 60; $minutes += 30):
                      $slot = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
                  ?>
                    <option value="<?= e($slot) ?>"<?= $old[$id] === $slot ? ' selected' : '' ?>><?= e($slot) ?></option>
                  <?php endfor; ?>
                </select>
                <?php else: ?>
                <input
                  id="<?= e($id) ?>"
                  name="<?= e($id) ?>"
                  type="<?= e($type) ?>"
                  value="<?= e($old[$id]) ?>"
                  <?= $attrs ?>
                  <?= $hasFieldError ? 'aria-invalid="true" aria-describedby="' . e($fieldErrorId) . '"' : '' ?>
                >
                <?php endif; ?>
                <?php if ($hasFieldError): ?>
                  <small id="<?= e($fieldErrorId) ?>"><?= e($errors[$id]) ?></small>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>

            <div class="restaurant-field <?= isset($errors['note']) ? 'has-error' : '' ?>">
              <label for="note">Special note (optional)</label>
              <textarea id="note" name="note" maxlength="500" rows="3" <?= isset($errors['note']) ? 'aria-invalid="true" aria-describedby="restaurant-error-note"' : 'aria-describedby="restaurant-note-help"' ?>><?= e($old['note']) ?></textarea>
              <?php if (isset($errors['note'])): ?><small id="restaurant-error-note"><?= e($errors['note']) ?></small><?php else: ?><small id="restaurant-note-help">Up to 500 characters. Please do not include payment details.</small><?php endif; ?>
            </div>

            <div class="restaurant-field restaurant-consent <?= isset($errors['consent']) ? 'has-error' : '' ?>">
              <label for="consent">
                <input id="consent" name="consent" type="checkbox" value="1" required <?= $old['consent'] === '1' ? 'checked' : '' ?> <?= isset($errors['consent']) ? 'aria-invalid="true" aria-describedby="restaurant-error-consent"' : '' ?>>
                I agree that the hotel may use these details to respond to my table enquiry.
              </label>
              <?php if (isset($errors['consent'])): ?><small id="restaurant-error-consent"><?= e($errors['consent']) ?></small><?php endif; ?>
            </div>

            <p class="restaurant-policy">Your request is not a confirmed booking. The hotel must confirm current service hours and availability before a table is reserved. If you need an immediate answer, call or message the hotel.</p>
            <button class="button button-primary" type="submit">Request a table</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </section>
