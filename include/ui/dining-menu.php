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
          <p class="eyebrow">Garh Jaisal’s Kitchen</p>
          <h2 id="menu-heading">Our menu</h2>
        </div>
      </div>
      <ul class="restaurant-diet-key" aria-label="Dietary symbols">
        <?php foreach ($dietLabels as $diet => $label): ?>
          <li class="restaurant-diet-badge">
            <span class="restaurant-mark is-<?= e($diet) ?>" aria-hidden="true"></span>
            <?php if ($diet === 'vegan'): ?>
              <a href="contact.php?topic=dining">Ask about vegan options</a>
            <?php else: ?>
              <?= e($label) ?>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="restaurant-diet-note">Please tell us about Jain dietary needs or allergies.</p>

      <div class="restaurant-menu-booklet" data-menu-booklet>
        <div class="restaurant-menu-search" data-menu-search-wrap hidden>
          <label for="menu-search">Find a dish</label>
          <input type="search" id="menu-search" data-menu-search aria-controls="all-dishes" placeholder="e.g. pancake or chai">
        </div>
        <nav class="restaurant-tabs" aria-label="Menu sections">
          <?php foreach ($menuCategories as $category): ?>
            <a href="#<?= e(gj_dining_menu_slug($category)) ?>" data-menu-tab><?= e($category) ?></a>
          <?php endforeach; ?>
        </nav>
        <div class="restaurant-booklet-controls" hidden>
          <div class="restaurant-booklet-turn" data-menu-top role="group" aria-label="Turn menu pages">
            <button type="button" data-menu-turn="-1" disabled>← Previous</button>
            <p data-menu-page-status role="status" aria-atomic="true"></p>
            <button type="button" data-menu-turn="1">Next →</button>
          </div>
          <div class="restaurant-booklet-actions">
            <button type="button" data-menu-all aria-controls="all-dishes">View all dishes</button>
            <button type="button" data-menu-print>Print menu</button>
          </div>
        </div>

        <div class="restaurant-menu-pages" id="all-dishes">
          <?php foreach ($menu as $category => $dishes): ?>
            <section class="restaurant-category" id="<?= e(gj_dining_menu_slug($category)) ?>" data-menu-page data-menu-page-label="<?= e($category) ?>" aria-labelledby="restaurant-menu-heading-<?= e(gj_dining_menu_slug($category)) ?>">
              <h3 id="restaurant-menu-heading-<?= e(gj_dining_menu_slug($category)) ?>" tabindex="-1"><?= e($category) ?></h3>
              <?php
              $groups = [];
              foreach ($dishes as $dish) {
                  $groups[$dish['group'] ?? ''][] = $dish;
              }
              ?>
              <div class="restaurant-menu-columns">
              <?php foreach ($groups as $group => $groupDishes): ?>
                <div class="restaurant-menu-group" data-menu-group="<?= e($group) ?>">
                  <?php if ($group !== ''): ?><h4><?= e($group) ?></h4><?php endif; ?>
                  <ul>
                    <?php foreach ($groupDishes as $dish):
                      $dishDiet = $dish['diet'] ?? null;
                      if ($dishDiet === null && is_bool($dish['veg'] ?? null)) {
                          $dishDiet = $dish['veg'] ? 'vegetarian' : 'non-vegetarian';
                      }
                      $dietLabel = $dietLabels[$dishDiet] ?? null;
                    ?>
                      <li class="restaurant-dish" data-menu-dish="<?= e($dish['n']) ?>">
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
              </div>
            </section>
          <?php endforeach; ?>
        </div>

        <div class="restaurant-booklet-turn restaurant-booklet-footer" data-menu-footer hidden role="group" aria-label="Turn menu pages after dishes">
          <button type="button" data-menu-turn="-1" disabled>← Previous</button>
          <button type="button" data-menu-turn="1">Next →</button>
        </div>
        <p class="restaurant-note">Prices in INR. Dishes and prices may change.</p>
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
