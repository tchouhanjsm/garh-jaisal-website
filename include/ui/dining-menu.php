<?php
function gj_dining_menu_slug(string $value): string {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));
    return $slug ?: 'section';
}
?>
<?php if ($menuOwnerConfirmed && $menu !== []): ?>
  <section id="menu" class="section restaurant-menu" aria-labelledby="menu-heading">
    <div class="site-shell">
      <div class="section-head restaurant-menu-head">
        <div>
          <p class="eyebrow">Menu</p>
          <h2 id="menu-heading">What is on the menu.</h2>
        </div>
        <button class="restaurant-vegtoggle" type="button" aria-pressed="false">Vegetarian only</button>
      </div>

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
              <ul>
                <?php foreach ($dishes as $dish): ?>
                  <li class="restaurant-dish" data-veg="<?= $dish['veg'] ? '1' : '0' ?>">
                    <span class="restaurant-mark <?= $dish['veg'] ? 'is-veg' : 'is-nonveg' ?>" aria-hidden="true"></span>
                    <div>
                      <h4><?= e($dish['n']) ?></h4>
                      <p><?= e($dish['d']) ?></p>
                    </div>
                    <?php if ($menuOwnerConfirmed): ?><span class="restaurant-price">₹<?= number_format($dish['p']) ?></span><?php else: ?><span class="restaurant-price">Price on request</span><?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
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
