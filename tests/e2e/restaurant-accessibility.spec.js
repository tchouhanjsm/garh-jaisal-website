const { test, expect } = require('@playwright/test');

const CHECKED_PROJECTS = ['mobile-320', 'mobile-390', 'desktop-1280'];

test('restaurant menu controls have usable targets, visible focus, correct location state and announced dietary filtering', async ({ page }, testInfo) => {
  test.skip(!CHECKED_PROJECTS.includes(testInfo.project.name), 'Restaurant menu accessibility is checked at 320px, 390px and 1280px.');

  await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });

  const menu = page.getByRole('navigation', { name: 'Menu sections' });
  const tabs = menu.locator('a[data-menu-tab]');
  const firstTab = tabs.first();
  const secondTab = tabs.nth(1);
  const vegetarianToggle = page.getByRole('button', { name: 'Vegetarian only', exact: true });
  const status = page.locator('[data-restaurant-menu-status]');

  await expect(menu).toBeVisible();
  expect(await tabs.count()).toBeGreaterThan(1);
  await expect(firstTab).toHaveAttribute('aria-current', 'location');
  await expect(status).toHaveAttribute('aria-live', 'polite');
  await expect(status).toHaveAttribute('aria-atomic', 'true');

  const targetSizes = await page.evaluate(() => {
    const elements = [
      document.querySelector('.restaurant-vegtoggle'),
      ...document.querySelectorAll('.restaurant-tabs a[data-menu-tab]'),
    ];
    return elements.map(element => {
      const rect = element.getBoundingClientRect();
      return {
        label: (element.textContent || '').trim(),
        width: Math.round(rect.width),
        height: Math.round(rect.height),
      };
    });
  });
  expect(targetSizes.length).toBeGreaterThan(1);
  for (const target of targetSizes) {
    expect(target.width, target.label + ' target width').toBeGreaterThanOrEqual(44);
    expect(target.height, target.label + ' target height').toBeGreaterThanOrEqual(44);
  }

  // Put the browser into keyboard modality, then inspect the focus-visible ring
  // on both the dietary filter and a menu category link.
  await firstTab.focus();
  await page.keyboard.press('Shift+Tab');
  await expect(vegetarianToggle).toBeFocused();
  const focusStyle = async locator => locator.evaluate(element => {
    const style = window.getComputedStyle(element);
    return { width: style.outlineWidth, color: style.outlineColor, token: getComputedStyle(document.documentElement).getPropertyValue('--focus').trim() };
  });
  const filterFocus = await focusStyle(vegetarianToggle);
  expect(filterFocus.width).toBe('3px');
  expect(filterFocus.color).toBe('rgb(29, 78, 216)');
  await page.keyboard.press('Tab');
  await expect(firstTab).toBeFocused();
  const tabFocus = await focusStyle(firstTab);
  expect(tabFocus.width).toBe('3px');
  expect(tabFocus.color).toBe(filterFocus.color);
  expect(tabFocus.token).toBe('#1d4ed8');

  await secondTab.click();
  await expect(secondTab).toHaveAttribute('aria-current', 'location');
  await expect(menu.locator('[aria-current="page"]')).toHaveCount(0);

  await vegetarianToggle.click();
  await expect(vegetarianToggle).toHaveAttribute('aria-pressed', 'true');
  await expect(status).toHaveText(/^Vegetarian filter on: showing \d+ dish(?:es)? across \d+ categor(?:y|ies)\.$/);
  const filteredDishes = await page.locator('.restaurant-dish:not([hidden])').evaluateAll(dishes =>
    dishes.every(dish => dish.dataset.veg === '1')
  );
  expect(filteredDishes, 'Only vegetarian dishes should remain visible while the filter is active').toBe(true);
  expect(await page.locator('.restaurant-dish:not([hidden])').count()).toBeGreaterThan(0);
  expect(await page.locator('.restaurant-category:not([hidden])').count()).toBeGreaterThan(0);

  await vegetarianToggle.click();
  await expect(vegetarianToggle).toHaveAttribute('aria-pressed', 'false');
  await expect(status).toHaveText(/^Vegetarian filter off: showing \d+ dish(?:es)? across \d+ categor(?:y|ies)\.$/);
  await expect(page.locator('.restaurant-dish:not([hidden])')).toHaveCount(await page.locator('.restaurant-dish').count());

  const viewport = await page.evaluate(() => ({
    width: window.innerWidth,
    documentWidth: document.documentElement.scrollWidth,
    menuWidth: document.querySelector('.restaurant-tabs').clientWidth,
    menuContentWidth: document.querySelector('.restaurant-tabs').scrollWidth,
    menuOverflowX: getComputedStyle(document.querySelector('.restaurant-tabs')).overflowX,
  }));
  expect(viewport.documentWidth, 'Restaurant page must not overflow horizontally').toBeLessThanOrEqual(viewport.width + 1);
  expect(viewport.menuOverflowX).toMatch(/auto|scroll/);
  expect(viewport.menuContentWidth).toBeGreaterThanOrEqual(viewport.menuWidth);
});
