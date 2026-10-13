const { test, expect } = require('@playwright/test');

test.describe('Sprint 2B content and layout', () => {
  test('Gallery filters categories and opens an accessible lightbox', async ({ page }) => {
    const response = await page.goto('/gallery.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const filters = page.locator('.gallery-filters [data-gallery-filter]');
    await expect(filters).toHaveCount(5);
    await page.getByRole('button', { name: 'Rooms', exact: true }).click();
    await expect(page.locator('#rooms')).toBeVisible();
    await expect(page.locator('#hotel')).toBeHidden();
    await expect(page.locator('[data-gallery-filter="rooms"]')).toHaveAttribute('aria-pressed', 'true');

    await page.locator('#rooms [data-gallery-open]').first().click();
    const dialog = page.locator('.gallery-lightbox');
    await expect(dialog).toBeVisible();
    await expect(dialog.locator('[data-gallery-lightbox-image]')).toHaveAttribute('alt', /Room/i);
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();

    await page.getByRole('button', { name: 'Rooftop dining', exact: true }).click();
    await expect(page.locator('#restaurant')).toBeVisible();
    await expect(page.locator('#desert')).toBeHidden();
  });

  test('Blog features the Fort guide and editorial pages have visible breadcrumbs', async ({ page }) => {
    await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    const featured = page.locator('.blog-card--featured');
    await expect(featured).toBeVisible();
    await expect(featured.locator('a')).toHaveAttribute('href', 'jaisalmer-fort-guide.php');
    await expect(page.locator('.page-breadcrumb')).toContainText('Blog');

    for (const route of ['/gallery.php', '/about.php', '/jaisalmer-fort-guide.php', '/desert-safari-guide.php', '/jaisalmer-2-day-itinerary.php']) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route).not.toBeNull();
      expect(response.status(), route).toBeLessThan(400);
      await expect(page.locator('main h1'), route).toHaveCount(1);
      await expect(page.locator('nav[aria-label="Breadcrumb"]'), route).toBeVisible();
    }
  });

  test('Dining pages cross-link at the start of each guest journey', async ({ page }) => {
    await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('a[href="restaurant.php#menu"]').first()).toBeVisible();
    await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.restaurant-rooftop-link a[href="roof.php"]')).toBeVisible();
  });

  for (const width of [320, 390, 1280, 1440]) {
    test(`has no horizontal overflow at ${width}px across Sprint 2B pages`, async ({ browser }) => {
      test.setTimeout(180_000);
      const context = await browser.newContext({ viewport: { width, height: 844 } });
      const page = await context.newPage();
      const routes = [
        '/gallery.php',
        '/about.php',
        '/blog.php',
        '/jaisalmer-fort-guide.php',
        '/desert-safari-guide.php',
        '/jaisalmer-2-day-itinerary.php',
        '/roof.php',
        '/restaurant.php',
      ];
      for (const route of routes) {
        const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, route).not.toBeNull();
        expect(response.status(), route).toBeLessThan(400);
        const sizes = await page.evaluate(() => ({
          viewport: window.innerWidth,
          document: document.documentElement.scrollWidth,
        }));
        expect(sizes.document, `${route} at ${width}px`).toBeLessThanOrEqual(sizes.viewport + 1);
      }
      await context.close();
    });
  }
});
