const { test, expect } = require('@playwright/test');

test.describe('Sprint 2A page-group layout', () => {
  test('Contact has a clear hero and keeps its direct actions', async ({ page }) => {
    const response = await page.goto('/contact.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator('.contact-hero h1')).toHaveText('Contact Garh Jaisal Haveli in Jaisalmer Fort.');
    await expect(page.locator('.contact-whatsapp-button')).toHaveAttribute('href', /https?:/);
    await expect(page.locator('.contact-booking-button')).toHaveAttribute('target', '_blank');
    await expect(page.locator('.contact-layout .contact-facts')).toBeVisible();
    await expect(page.locator('.contact-layout .contact-form')).toBeVisible();
  });

  test('Dining hub links to the menu page', async ({ page }) => {
    await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('a[href="restaurant.php#menu"]').first()).toBeVisible();
  });

  test('Directions leads with the parking walk answer', async ({ page }) => {
    await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.arrival-overview .section-head')).toContainText('5–6 minute walk');
    await expect(page.locator('.arrival-facts')).toBeVisible();
  });

  for (const width of [320, 390, 1280]) {
    test(`has no horizontal overflow at ${width}px`, async ({ browser }) => {
      const context = await browser.newContext({ viewport: { width, height: 844 } });
      const page = await context.newPage();
      for (const route of ['/roof.php', '/contact.php', '/direction.php']) {
        const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
        expect(response, route).not.toBeNull();
        expect(response.status(), route).toBeLessThan(400);
        const sizes = await page.evaluate(() => ({ viewport: innerWidth, document: document.documentElement.scrollWidth }));
        expect(sizes.document, route).toBeLessThanOrEqual(sizes.viewport + 1);
      }
      await context.close();
    });
  }
});
