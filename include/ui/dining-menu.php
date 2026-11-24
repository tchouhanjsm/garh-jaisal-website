<?php
function gj_dining_menu_slug(string $value): string {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));
    return $slug ?: 'section';
}
$dietLabels = [
    'vegan' => 'Vegan',
    'vegetarian' => 'Vegetarian',
    'non-vegetarian' => 'Non-vegetarian',
];
?>
<?php if ($menuOwnerConfirmed && $menu !== []): ?>
  <section id="menu" class="section restaurant-menu" aria-labelledby="menu-heading">
    <div class="site-shell">
      <div class="section-head restaurant-menu-head">
        <div>
          <p class="eyebrow">Menu</p>
          <h2 id="menu-heading">What is on the menu.</h2>
        </div>
        <button class="restaurant-vegtoggle" type="button" aria-pressed="false" hidden>Vegetarian only</button>
      </div>
      <ul class="restaurant-diet-key" aria-label="Dietary symbols">
        <?php foreach ($dietLabels as $diet => $label): ?>
          <li class="restaurant-diet-badge">
            <span class="restaurant-mark is-<?= e($diet) ?>" aria-hidden="true"></span><?= e($label) ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="restaurant-diet-note">Some dishes have no dietary label. Ask the kitchen to confirm vegan options, Jain options or allergy requirements before ordering.</p>

      <div class="restaurant-menu-booklet" data-menu-booklet>
        <p class="sr-only" data-restaurant-menu-status role="status" aria-live="polite" aria-atomic="true"></p>
        <nav class="restaurant-tabs" aria-label="Menu sections">
          <?php foreach ($menuCategories as $menuIndex => $category): ?>
            <a href="#<?= e(gj_dining_menu_slug($category)) ?>" data-menu-tab aria-controls="<?= e(gj_dining_menu_slug($category)) ?>" <?= $menuIndex === 0 ? 'aria-current="location"' : '' ?>><?= e($category) ?></a>
          <?php endforeach; ?>
        </nav>

        <div class="restaurant-booklet-controls" aria-label="Menu booklet controls">
          <button type="button" data-menu-previous disabled>← Previous</button>
          <p data-menu-page-status aria-live="polite" role="status">Page 1 of <?= count($menuCategories) ?>: <?= e($menuCategories[0] ?? 'Menu') ?></p>
          <button type="button" data-menu-next>Next →</button>
          <button type="button" data-menu-print>Print menu</button>
        </div>

        <div class="restaurant-menu-pages">
          <?php foreach ($menu as $category => $dishes): ?>
            <section class="restaurant-category" id="<?= e(gj_dining_menu_slug($category)) ?>" data-menu-page data-menu-page-label="<?= e($category) ?>" aria-labelledby="restaurant-menu-heading-<?= e(gj_dining_menu_slug($category)) ?>">
              <h3 id="restaurant-menu-heading-<?= e(gj_dining_menu_slug($category)) ?>"><?= e($category) ?></h3>
              <?php
              $groups = [];
              foreach ($dishes as $dish) {
                  $groups[$dish['group'] ?? ''][] = $dish;
              }
              ?>
              <?php foreach ($groups as $group => $groupDishes): ?>
                <div class="restaurant-menu-group">
                  <?php if ($group !== ''): ?><h4><?= e($group) ?></h4><?php endif; ?>
                  <ul>
                    <?php foreach ($groupDishes as $dish):
                      $dishDiet = $dish['diet'] ?? null;
                      if ($dishDiet === null && is_bool($dish['veg'] ?? null)) {
                          $dishDiet = $dish['veg'] ? 'vegetarian' : 'non-vegetarian';
                      }
                      $dietLabel = $dietLabels[$dishDiet] ?? null;
                      $vegetarian = in_array($dishDiet, ['vegetarian', 'vegan'], true);
                    ?>
                      <li class="restaurant-dish" data-veg="<?= $vegetarian ? '1' : '0' ?>">
                        <div>
                          <?php $dishHeading = $group === '' ? 'h4' : 'h5'; ?>
                          <<?= $dishHeading ?>><?= e($dish['n']) ?></<?= $dishHeading ?>>
                          <?php if (!empty($dish['d'])): ?><p><?= e($dish['d']) ?></p><?php endif; ?>
                          <?php if ($dietLabel !== null): ?>
                            <span class="restaurant-diet-badge">
                              <span class="restaurant-mark is-<?= e($dishDiet) ?>" aria-hidden="true"></span><?= e($dietLabel) ?>
                            </span>
                          <?php endif; ?>
                        </div>
                        <span class="restaurant-price">₹<?= number_format($dish['p']) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endforeach; ?>
              <p class="restaurant-note" data-menu-filter-empty hidden>No dishes in this section are marked vegetarian or vegan.</p>
            </section>
          <?php endforeach; ?>
        </div>

        <p class="restaurant-note">Menu items and listed prices may change. Please confirm the current menu and prices with the hotel.</p>
      </div>
    </div>
  </section>

<?php else: ?>
  <section id="menu" class="section restaurant-menu" aria-labelledby="menu-heading">
    <div class="site-shell">
      <div class="section-head restaurant-menu-head">
        <div>
          <p class="eyebrow">Menu update</p>
          <h2 id="menu-heading">Ask about the current menu.</h2>
        </div>
      </div>
      <p class="restaurant-note">We are confirming the current dishes, descriptions, prices and dietary options, so an unverified menu is not shown here. Contact the hotel for current details for your visit.</p>
      <p class="restaurant-contact-help">
        <a class="button button-primary" href="contact.php?topic=dining">Ask about the current menu</a>
      </p>
      <p class="restaurant-contact-help">
        <a class="text-link" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Ask on WhatsApp ↗</a>
      </p>
    </div>
  </section>
<?php endif; ?>
