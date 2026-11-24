const { test, expect } = require('@playwright/test');

const CHECKED_PROJECTS = ['mobile-320', 'mobile-390', 'desktop-1280'];

test('restaurant menu controls or pending-menu fallback are accessible and responsive', async ({ page }, testInfo) => {
  test.skip(!CHECKED_PROJECTS.includes(testInfo.project.name), 'Restaurant menu accessibility is checked at 320px, 390px and 1280px.');

  await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });

  const menu = page.getByRole('navigation', { name: 'Menu sections' });
  const tabs = menu.getByRole('link');
  const firstTab = tabs.first();
  const toggle = page.getByRole('button', { name: 'View all dishes' });
  const status = page.locator('[data-menu-page-status]');

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
  await expect(firstTab).toHaveAttribute('aria-current', 'location');
  await expect(status).toHaveAttribute('role', 'status');
  await expect(status).toHaveAttribute('aria-atomic', 'true');
  const controls = page.locator('[data-menu-booklet]').locator('a:visible, button:visible');
  for (const control of await controls.all()) {
    const box = await control.boundingBox();
    expect(box.width).toBeGreaterThanOrEqual(44);
    expect(box.height).toBeGreaterThanOrEqual(44);
    expect(box.x + box.width).toBeLessThanOrEqual(page.viewportSize().width);
  }

  const focusStyle = locator => locator.evaluate(element => {
    const style = getComputedStyle(element);
    return { width: style.outlineWidth, color: style.outlineColor };
  });
  await firstTab.focus();
  await page.keyboard.press('Tab');
  await page.keyboard.press('Shift+Tab');
  await expect(firstTab).toBeFocused();
  expect(await focusStyle(firstTab)).toEqual({ width: '3px', color: 'rgb(40, 91, 187)' });

  await tabs.last().focus();
  await page.keyboard.press('Tab');
  await page.keyboard.press('Tab');
  await expect(toggle).toBeFocused();
  expect(await focusStyle(toggle)).toEqual({ width: '3px', color: 'rgb(40, 91, 187)' });
  await page.keyboard.press('Enter');
  await expect(status).toHaveText('All dishes');
  await expect(page.getByRole('button', { name: 'Back to booklet' })).toBeFocused();
  await expect(page.locator('.restaurant-dish:visible')).toHaveCount(142);
});
