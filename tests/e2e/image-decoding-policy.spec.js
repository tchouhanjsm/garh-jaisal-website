const { test, expect } = require('@playwright/test');

const ROUTES = [
  '/',
  '/room.php',
  '/pack.php',
  '/catalogue.php',
  '/desert-safari.php',
  '/restaurant.php',
  '/roof.php',
  '/direction.php',
  '/about.php',
  '/blog.php',
  '/gallery.php',
  '/car-tours.php',
  '/guided-jaisalmer-tour.php',
  '/contact.php',
];

test('public page images use asynchronous decoding without delaying source selection', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Image decoding policy is checked once at desktop width.');
  test.setTimeout(120_000);
  const report = {
    generatedAt: new Date().toISOString(),
    scope: 'Rendered local PHP routes in Chromium; async decoding hint audit, not a field performance claim.',
    routes: [],
  };

  for (const route of ROUTES) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, route + ' should return a document response').not.toBeNull();
    expect(response.status(), route + ' should return a successful response').toBeLessThan(400);
    const images = await page.locator('img').evaluateAll((nodes) => nodes.map((image) => ({
      src: image.currentSrc || image.src || '',
      decoding: image.getAttribute('decoding') || '',
      loading: image.getAttribute('loading') || '',
      fetchPriority: image.getAttribute('fetchpriority') || '',
      altPresent: image.hasAttribute('alt'),
    })));
    expect(images.length, route + ' should exercise the shared image surface').toBeGreaterThan(0);
    const undecodedLazyImages = images.filter((image) => image.loading === 'lazy' && image.decoding !== 'async');
    expect(
      undecodedLazyImages,
      route + ' must mark lazy-loaded image decoding as asynchronous'
    ).toEqual([]);
    const contradictoryPriority = images.filter((image) => image.loading === 'lazy' && image.fetchPriority === 'high');
    expect(
      contradictoryPriority,
      route + ' must not combine lazy loading with high fetch priority'
    ).toEqual([]);
    const missingAlt = images.filter((image) => !image.altPresent);
    expect(missingAlt, route + ' images must retain an explicit alt attribute').toEqual([]);
    report.routes.push({
      route,
      imageCount: images.length,
      lazyImages: images.filter((image) => image.loading === 'lazy').length,
      asynchronousDecodeImages: images.filter((image) => image.decoding === 'async').length,
    });
  }

  const fs = require('node:fs');
  const path = require('node:path');
  const outputDir = path.join(process.cwd(), 'artifacts', 'image-accessibility');
  fs.mkdirSync(outputDir, { recursive: true });
  fs.writeFileSync(path.join(outputDir, 'image-decoding-policy.json'), JSON.stringify(report, null, 2) + '\n');
});
