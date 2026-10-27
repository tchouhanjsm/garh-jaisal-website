const { test, expect } = require('@playwright/test');

async function expectWhatsAppLink(link) {
  await expect(link).toHaveAttribute('href', /^https:\/\/wa\.me\//);
  await expect(link).toHaveAttribute('target', '_blank');
  await expect(link).toHaveAttribute('rel', /noopener/);
  const target = new URL(await link.getAttribute('href'));
  const message = target.searchParams.get('text') || '';
  expect(message).toMatch(/Travel date|Preferred date/);
  expect(message).toMatch(/current price|current quote/i);
}

test('Fort walk describes its intended stops and sends price enquiries to WhatsApp', async ({ page }) => {
  const response = await page.goto('/city-tour.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  await expect(page.getByRole('heading', { level: 1, name: 'Discover the stories inside Jaisalmer Fort.' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Jain temples inside Jaisalmer Fort' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Palace Museum' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Heritage street walk' })).toBeVisible();

  const languages = page.locator('.tour-language-list');
  for (const language of ['English', 'Hindi', 'Bengali', 'Italian', 'French', 'Spanish', 'German']) {
    await expect(languages).toContainText(language);
  }
  await expectWhatsAppLink(page.getByRole('link', { name: 'Ask about route, guide & price on WhatsApp' }));
  await expect(page.locator('main')).not.toContainText(/₹\s*[\d,]+|\bINR\s*[\d,]+/i);
});

test('car service page shows confirmed vehicles, regions and driver communication without rates', async ({ page }) => {
  const response = await page.goto('/car-rental.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  await expect(page.getByRole('heading', { level: 1, name: 'Car services across Rajasthan and Delhi.' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Desert pickup and drop-off' })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Intercity journeys' })).toBeVisible();
  await expect(page.locator('main')).toContainText('SUV');
  await expect(page.locator('main')).toContainText('sedan');
  await expect(page.locator('main')).toContainText('Hindi and English');
  await expectWhatsAppLink(page.getByRole('link', { name: 'Request a car quote on WhatsApp' }));
  await expect(page.locator('main')).not.toContainText(/₹\s*[\d,]+|\bINR\s*[\d,]+/i);
});

test('half-day and full-day tours do not publish rates and use WhatsApp for quotes', async ({ page }) => {
  for (const route of ['/half-day-jaisalmer-tour.php', '/full-day-jaisalmer-tour.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ' should return a response').not.toBeNull();
    expect(response.status(), route + ' should load').toBeLessThan(400);
    const cta = page.getByRole('link', { name: 'Ask on WhatsApp' }).first();
    await expect(cta).toBeVisible();
    await expectWhatsAppLink(cta);
    await expect(page.locator('.glance-grid')).toContainText('Guide languages');
    await expect(page.locator('main')).not.toContainText(/₹\s*[\d,]+|\bINR\s*[\d,]+/i);
  }
});

test('Directions has a responsive map and preserves the exact hotel Google Maps target', async ({ page }) => {
  const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  const map = page.locator('.arrival-map');
  await expect(map.getByRole('heading', { name: 'Find the hotel inside Jaisalmer Fort.' })).toBeVisible();
  await expect(map.locator('iframe')).toHaveAttribute('title', 'Map of Garh Jaisal Haveli near Fort Kotari Para inside Jaisalmer Fort');
  await expect(map.getByRole('link', { name: 'Open exact hotel pin in Google Maps' }))
    .toHaveAttribute('href', 'https://maps.app.goo.gl/nAbRiZhv78FSbju48');
});

test('Fort walk, car service and Directions pages fit all required responsive widths', async ({ page }) => {
  for (const route of ['/city-tour.php', '/car-rental.php', '/direction.php']) {
    for (const width of [320, 390, 1280, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' should return a response').not.toBeNull();
      expect(response.status(), route + ' should load at ' + width + 'px').toBeLessThan(400);
      const size = await page.evaluate(() => ({
        viewport: window.innerWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(size.document, route + ' must not overflow at ' + width + 'px')
        .toBeLessThanOrEqual(size.viewport + 1);
    }
  }
});
