const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

function getSitemapRoutes() {
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  return [...new Set(
    [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
      .map(([, value]) => new URL(value.trim()))
      .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
      .map(url => url.pathname || '/')
  )].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

test('rendered images reserve layout geometry on every sitemap route', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Image geometry inventory runs once');
  test.setTimeout(180_000);

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const findings = [];
  const routes = getSitemapRoutes();

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

      const images = await page.locator('img').evaluateAll(nodes => nodes.map((image, index) => ({
        index: index + 1,
        src: image.currentSrc || image.src || '',
        width: image.getAttribute('width'),
        height: image.getAttribute('height'),
      })));

      for (const image of images) {
        const hasPositiveWidth = /^\d+$/.test(image.width || '') && Number(image.width) > 0;
        const hasPositiveHeight = /^\d+$/.test(image.height || '') && Number(image.height) > 0;
        if (!hasPositiveWidth || !hasPositiveHeight) {
          findings.push({
            route,
            src: image.src,
            imageIndex: image.index,
            width: image.width,
            height: image.height,
            issue: 'missing-positive-width-or-height-attribute',
          });
        }
      }
    }
  } finally {
    await context.close();
  }

  console.log('Rendered image-layout stability findings:', JSON.stringify({
    routesChecked: routes.length,
    imagesMissingPositiveDimensions: findings.length,
    findings,
  }, null, 2));

  expect(findings, 'Every rendered sitemap image must reserve layout geometry with positive width and height attributes').toEqual([]);
});
