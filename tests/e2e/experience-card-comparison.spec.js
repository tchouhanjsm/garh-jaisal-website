const { test, expect } = require('@playwright/test');

const hubRoutes = [
  { route: '/desert-safari.php', expectedCards: 4 },
  { route: '/guided-jaisalmer-tour.php', expectedCards: 2 },
];

test('tour discovery cards show registry-backed comparison facts and preserve planning actions', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Tour comparison card contracts run once on desktop-1280.');

  for (const item of hubRoutes) {
    const response = await page.goto(item.route, { waitUntil: 'domcontentloaded' });
    expect(response, item.route + ': HTTP response').not.toBeNull();
    expect(response.status(), item.route + ': published hub remains available').toBeLessThan(400);

    const cards = page.locator('.experience-card');
    await expect(cards, item.route + ': expected live offer cards').toHaveCount(item.expectedCards);

    if (item.route === '/guided-jaisalmer-tour.php') {
      const heroActions = page.locator('.experience-hero-actions');
      await expect(heroActions.locator('a, button')).toHaveCount(2);
      await expect(heroActions.locator('a.button-primary')).toHaveAttribute('href', 'contact.php?topic=visit-planning');
      await expect(heroActions.locator('a[href="#guided-options"]')).toHaveText('Compare tour options');
      await expect(heroActions.getByRole('link', { name: 'Build your trip shortlist' })).toHaveCount(0);
    }

    for (const card of await cards.all()) {
      const details = card.locator('dl.experience-card-facts');
      await expect(details, item.route + ': quick facts').toBeVisible();
      const labels = (await details.locator('dt').allTextContents()).map(value => value.trim()).filter(Boolean);
      const values = (await details.locator('dd').allTextContents()).map(value => value.trim()).filter(Boolean);
      expect(labels.length, item.route + ': two or three comparison attributes').toBeGreaterThanOrEqual(2);
      expect(labels.length).toBeLessThanOrEqual(3);
      expect(values).toHaveLength(labels.length);
      expect(values.every(value => value.length > 0), item.route + ': facts must not be empty').toBeTruthy();
      expect(labels.every(label => /^[A-Za-z ]+$/.test(label)), item.route + ': labels must be plain source keys').toBeTruthy();
      await expect(card).not.toContainText(/₹\s?\d/);
      await expect(card.locator('a').first()).toHaveAttribute('href', /^[a-z0-9-]+\.php$/);
    }

    if (item.route === '/guided-jaisalmer-tour.php') {
      await expect(cards.first().locator('.trip-add-button')).toBeVisible();
    }
  }
});

test('tour comparison hubs remain free of horizontal overflow at the supported widths', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Supported viewport coverage.');

  for (const route of ['/desert-safari.php', '/guided-jaisalmer-tour.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    const metrics = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(metrics.document, route + ': no horizontal overflow at ' + testInfo.project.name)
      .toBeLessThanOrEqual(metrics.viewport + 1);
  }
});
