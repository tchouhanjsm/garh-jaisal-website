const { test, expect } = require('@playwright/test');

test('restaurant keeps an emotional hero and light, legible information surfaces', async ({ page }) => {
  await page.goto('/restaurant.php');
  const hero = page.locator('.restaurant-hero');
  await expect(hero.getByRole('heading', { level: 1, name: 'Rajasthani food on the rooftop.' })).toBeVisible();
  expect(await hero.evaluate(element => getComputedStyle(element).backgroundImage)).toContain('linear-gradient');

  const formCard = page.locator('.restaurant-form-card').first();
  await expect(formCard).toBeVisible();
  const formStyles = await formCard.evaluate(element => ({
    background: getComputedStyle(element).backgroundColor,
    radius: getComputedStyle(element).borderRadius
  }));
  expect(formStyles.background).toBe('rgb(247, 239, 224)');
  expect(formStyles.radius).toBe('6px');
  expect(await page.locator('.restaurant-feature-card').first().evaluate(element => getComputedStyle(element).borderRadius)).toBe('6px');
  expect(await page.locator('.restaurant-tabs a').first().evaluate(element => getComputedStyle(element).borderRadius)).toBe('6px');
  expect(await page.locator('.restaurant-vegtoggle').evaluate(element => getComputedStyle(element).borderRadius)).toBe('6px');
  await expect(page.locator('.restaurant-hero .button-primary')).toBeVisible();
  await expect(page.locator('.restaurant-instagram-panel a.button')).toBeVisible();

  const widths = await page.evaluate(() => ({
    client: document.documentElement.clientWidth,
    scroll: document.documentElement.scrollWidth
  }));
  expect(widths.scroll).toBeLessThanOrEqual(widths.client);
});
