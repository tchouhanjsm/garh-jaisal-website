const { test, expect } = require('@playwright/test');

const CHECKED_PROJECTS = ['mobile-320', 'mobile-390', 'desktop-1280'];

test('restaurant menu controls or pending-menu fallback are accessible and responsive', async ({ page }, testInfo) => {
  test.skip(!CHECKED_PROJECTS.includes(testInfo.project.name), 'Restaurant menu accessibility is checked at 320px, 390px and 1280px.');

  await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });

  const menu = page.getByRole('navigation', { name: 'Menu sections' });
  const tabs = menu.locator('a[data-menu-tab]');
  const firstTab = tabs.first();
  const secondTab = tabs.nth(1);
  const vegetarianToggle = page.getByRole('button', { name: 'Vegetarian only', exact: true });
  const status = page.locator('[data-restaurant-menu-status]');

  if (await menu.count() === 0) {
    const pendingMenu = page.locator('#menu');
    await expect(pendingMenu.getByRole('heading', { name: 'Ask about the current menu.' })).toBeVisible();
    await expect(pendingMenu).toContainText('We are confirming the current dishes, descriptions, prices and dietary options');
    await expect(pendingMenu.getByRole('link', { name: 'Ask about the current menu' }))
      .toHaveAttribute('href', 'contact.php?topic=dining');
    const dimensions = await page.evaluate(() => ({
      width: document.documentElement.clientWidth,
      documentWidth: document.documentElement.scrollWidth,
    }));
    expect(dimensions.documentWidth, 'Pending-menu fallback must not overflow horizontally')
      .toBeLessThanOrEqual(dimensions.width + 1);
    return;
  }
  const expectedFilterSummary = async vegetarianOnly => {
    const counts = await page.evaluate(isVegetarianOnly => {
      const categories = Array.from(document.querySelectorAll('.restaurant-category'));
      const matches = dish => !isVegetarianOnly || dish.dataset.veg === '1';
      const dishes = categories.flatMap(category => Array.from(category.querySelectorAll('.restaurant-dish')));
      return {
        dishes: dishes.filter(matches).length,
        categories: categories.filter(category => Array.from(category.querySelectorAll('.restaurant-dish')).some(matches)).length,
      };
    }, vegetarianOnly);
    const dishLabel = counts.dishes === 1 ? 'dish' : 'dishes';
    const categoryLabel = counts.categories === 1 ? 'category' : 'categories';
    return (vegetarianOnly ? 'Vegetarian filter on' : 'Vegetarian filter off')
      + ': ' + counts.dishes + ' matching ' + dishLabel
      + ' across ' + counts.categories + ' menu ' + categoryLabel + '.';
  };

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
  expect(tabFocus.token.toLowerCase()).toBe('#1d4ed8');

  await secondTab.click();
  await expect(secondTab).toHaveAttribute('aria-current', 'location');
  await expect(menu.locator('[aria-current="page"]')).toHaveCount(0);
  const activeSectionSelector = await secondTab.getAttribute('href');
  const activeSection = page.locator(activeSectionSelector);
  await expect(activeSection).toBeVisible();

  await vegetarianToggle.click();
  await expect(vegetarianToggle).toHaveAttribute('aria-pressed', 'true');
  await expect(status).toHaveText(await expectedFilterSummary(true));
  await expect(secondTab).toHaveAttribute('aria-current', 'location');
  await expect(activeSection).toBeVisible();
  const filteredDishes = await page.locator('.restaurant-dish:not([hidden])').evaluateAll(dishes =>
    dishes.every(dish => dish.dataset.veg === '1')
  );
  expect(filteredDishes, 'Only vegetarian dishes should remain visible while the filter is active').toBe(true);
  expect(await page.locator('.restaurant-dish:not([hidden])').count()).toBeGreaterThan(0);
  expect(await page.locator('.restaurant-category:not([hidden])').count()).toBeGreaterThan(0);

  await vegetarianToggle.click();
  await expect(vegetarianToggle).toHaveAttribute('aria-pressed', 'false');
  await expect(status).toHaveText(await expectedFilterSummary(false));
  await expect(secondTab).toHaveAttribute('aria-current', 'location');
  await expect(activeSection).toBeVisible();
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
