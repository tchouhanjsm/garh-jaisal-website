const { test, expect } = require('@playwright/test');
const { getSitemapRoutes } = require('./helpers/sitemap-routes');

test('published routes do not request retired local icon-font assets', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Asset request inventory runs once across sitemap routes');
  test.setTimeout(120_000);

  const origin = 'http://127.0.0.1:8080';
  const context = await browser.newContext({
    baseURL: origin,
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const localFontRequests = new Set();

  page.on('request', request => {
    let url;
    try {
      url = new URL(request.url());
    } catch {
      return;
    }
    if (url.origin === origin && url.pathname.startsWith('/fonts/')) {
      localFontRequests.add(url.pathname);
    }
  });

  try {
    for (const route of getSitemapRoutes()) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);
    }
    expect([...localFontRequests], 'Published pages must not request retired local font assets').toEqual([]);
  } finally {
    await context.close();
  }
});
