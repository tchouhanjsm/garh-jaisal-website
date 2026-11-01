const { test, expect } = require('@playwright/test');

const MAP_CASES = [
  {
    route: '/direction.php',
    pinSelector: '.arrival-map-copy a[href^="https://maps.app.goo.gl/"]',
    expectedPin: 'https://maps.app.goo.gl/nAbRiZhv78FSbju48',
  },
  {
    route: '/restaurant.php',
    pinSelector: '.restaurant-map-qr-card a[href^="https://maps.app.goo.gl/"]',
    expectedPin: 'https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8',
  },
];

test('map previews remain unloaded until requested and fit the responsive layout', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The map preview viewport matrix runs once.');

  const mapRequests = [];
  page.on('request', request => {
    if (/^https:\/\/www\.google\.com\/maps(?:\/embed)?\?/.test(request.url())) {
      mapRequests.push(request.url());
    }
  });

  for (const width of [375, 390, 768, 1024, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    for (const item of MAP_CASES) {
      const response = await page.goto(item.route, { waitUntil: 'domcontentloaded' });
      expect(response, 'No response for ' + item.route + ' at ' + width + 'px').not.toBeNull();
      expect(response.status()).toBeLessThan(400);

      const preview = page.locator('[data-map-preview]');
      await expect(preview).toHaveCount(1);
      await expect(preview).toBeVisible();
      await expect(preview.locator('summary')).toContainText('Load interactive map');

      const iframe = preview.locator('iframe');
      const embedUrl = await iframe.getAttribute('data-map-src');
      expect(embedUrl).toMatch(/^https:\/\/www\.google\.com\/maps(?:\/embed)?\?/);
      await expect(iframe).not.toHaveAttribute('src', /.+/);

      const pin = page.locator(item.pinSelector);
      await expect(pin).toHaveAttribute('href', item.expectedPin);
      await expect(pin).toBeVisible();

      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(dimensions.document, 'Map route overflow at ' + width + 'px: ' + JSON.stringify(dimensions))
        .toBeLessThanOrEqual(dimensions.viewport + 1);
    }
  }

  expect(mapRequests, 'Third-party map requests must not start during initial route rendering').toHaveLength(0);

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  const preview = page.locator('[data-map-preview]');
  const iframe = preview.locator('iframe');
  const embedUrl = await iframe.getAttribute('data-map-src');

  await preview.locator('summary').click();
  await expect(preview).toHaveAttribute('open', '');
  await expect(iframe).toHaveAttribute('src', embedUrl);
  await expect(preview.locator('[data-map-preview-label]')).toHaveText('Hide interactive map');
  await expect.poll(() => mapRequests.filter(url => url === embedUrl).length).toBe(1);

  await preview.locator('summary').click();
  await expect(preview).not.toHaveAttribute('open', '');
  await expect(preview.locator('[data-map-preview-label]')).toHaveText('Show interactive map');
  await expect(iframe).toHaveAttribute('src', embedUrl);
  expect(mapRequests.filter(url => url === embedUrl)).toHaveLength(1);
});

test('map location links remain available without JavaScript enhancement', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'No-JavaScript fallback is checked once.');

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    javaScriptEnabled: false,
    viewport: { width: 390, height: 844 },
  });
  const page = await context.newPage();

  try {
    for (const item of MAP_CASES) {
      await page.goto(item.route, { waitUntil: 'domcontentloaded' });
      const preview = page.locator('[data-map-preview]');
      const iframe = preview.locator('iframe');
      await expect(iframe).not.toHaveAttribute('src', /.+/);
      await preview.locator('summary').click();
      await expect(preview).toHaveAttribute('open', '');
      await expect(preview.locator('noscript')).toContainText('needs JavaScript');
      await expect(iframe).not.toHaveAttribute('src', /.+/);
      await expect(page.locator(item.pinSelector)).toHaveAttribute('href', item.expectedPin);
      await expect(page.locator(item.pinSelector)).toBeVisible();
    }
  } finally {
    await context.close();
  }
});
