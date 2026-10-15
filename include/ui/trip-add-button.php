<?php
/**
 * Accessible add/remove toggle for the session-only Trip tray.
 * Expected: $tripItem = ['id' => ..., 'title' => ..., 'url' => ..., 'group' => optional]
 * Optional: $tripButtonClass.
 */
$tripItem = isset($tripItem) && is_array($tripItem) ? $tripItem : [];
$tripId = (string) ($tripItem['id'] ?? '');
$tripTitle = (string) ($tripItem['title'] ?? '');
$tripUrl = (string) ($tripItem['url'] ?? '');
$tripGroup = (string) ($tripItem['group'] ?? '');
$tripButtonClass = isset($tripButtonClass) && is_string($tripButtonClass)
    ? $tripButtonClass
    : 'button button-secondary trip-add-button';

if (
    $tripId !== ''
    && preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/D', $tripId)
    && $tripTitle !== ''
    && $tripUrl !== ''
    && preg_match('/^[a-z0-9-]+\.php(?:#[a-z0-9-]+)?$/iD', $tripUrl)
):
?>
<button
  type="button"
  class="<?= htmlspecialchars($tripButtonClass, ENT_QUOTES, 'UTF-8') ?> trip-add-button"
  data-trip-add
  data-trip-id="<?= htmlspecialchars($tripId, ENT_QUOTES, 'UTF-8') ?>"
  data-trip-title="<?= htmlspecialchars($tripTitle, ENT_QUOTES, 'UTF-8') ?>"
  data-trip-url="<?= htmlspecialchars($tripUrl, ENT_QUOTES, 'UTF-8') ?>"
  data-trip-group="<?= htmlspecialchars($tripGroup, ENT_QUOTES, 'UTF-8') ?>"
  aria-pressed="false"
  aria-label="Add <?= htmlspecialchars($tripTitle, ENT_QUOTES, 'UTF-8') ?> to your Trip"
>Add to Trip</button>
<?php
endif;
unset($tripItem, $tripId, $tripTitle, $tripUrl, $tripGroup, $tripButtonClass);
?>
