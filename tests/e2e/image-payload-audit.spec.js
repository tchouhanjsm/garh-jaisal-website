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

const routes = getSitemapRoutes();
const REVIEW_THRESHOLD_BYTES = 250 * 1024;
const ROOT = path.resolve(__dirname, "../..");
const ENFORCED_RESPONSIVE_VARIANT_BUDGETS = [
  { path: "img/responsive/v2/facade-1200.webp", maxBytes: 350 * 1024 },
  { path: "img/responsive/v2/courtyard-1200.webp", maxBytes: 350 * 1024 },
];

test('home and rooftop photo slots reserve dimensions and match their layouts', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Rooftop image-slot contract runs once.');
  const cases = [
    { route: '/', selector: '.home-rooftop .media img[src="img/roofslide.webp"]', sizes: '(max-width: 760px) 100vw, 50vw' },
    { route: '/roof.php', selector: '.cards .card-image img[src="img/roof.webp"]', sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
    { route: '/roof.php', selector: '.cards .card-image img[src="img/r2.webp"]', sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
    { route: '/roof.php', selector: '.cards .card-image img[src="img/r3.webp"]', sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
  ];
  for (const item of cases) {
    await page.goto(BASE_URL + item.route, { waitUntil: 'domcontentloaded' });
    const image = page.locator(item.selector);
    await expect(image).toHaveCount(1);
    await expect(image).toHaveAttribute('width', /^[1-9][0-9]*$/);
    await expect(image).toHaveAttribute('height', /^[1-9][0-9]*$/);
    await expect(image).toHaveAttribute('sizes', item.sizes);
  }
});

test('write deduplicated image payload audit for sitemap routes', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Image payload audit runs once at desktop width');
  test.setTimeout(180_000);

  const enforcedVariantBudgets = ENFORCED_RESPONSIVE_VARIANT_BUDGETS.map(budget => {
    const absolutePath = path.join(ROOT, budget.path);
    expect(fs.existsSync(absolutePath), `${budget.path} should be generated for the audit`).toBe(true);
    const bytes = fs.statSync(absolutePath).size;
    expect(bytes, `${budget.path} should stay within its 350 KiB transfer budget`).toBeLessThanOrEqual(budget.maxBytes);
    return { ...budget, bytes, status: 'pass' };
  });

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const usageByUrl = new Map();

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

      // Let local eager and initially visible images decode, without waiting on third-party frames/scripts.
      await page.locator('img').evaluateAll(async images => {
        const candidates = images.filter(img => {
          const rect = img.getBoundingClientRect();
          return img.loading !== 'lazy' || (rect.bottom > 0 && rect.top < window.innerHeight);
        });
        await Promise.all(candidates.map(img => img.decode ? img.decode().catch(() => undefined) : Promise.resolve()));
      });

      const imageUsages = await page.locator('img').evaluateAll(images => images.map((img, index) => ({
        url: img.currentSrc || img.src || '',
        route: window.location.pathname,
        alt: img.getAttribute('alt'),
        widthAttribute: img.getAttribute('width'),
        heightAttribute: img.getAttribute('height'),
        naturalWidth: img.naturalWidth,
        naturalHeight: img.naturalHeight,
        loading: img.loading || 'eager',
        fetchPriority: img.fetchPriority || 'auto',
        visibleAtInitialViewport: (() => {
          const rect = img.getBoundingClientRect();
          return rect.bottom > 0 && rect.top < window.innerHeight;
        })(),
        elementIndex: index + 1,
      })));

      const imageResources = await page.evaluate(() => performance.getEntriesByType('resource')
        .filter(entry => entry.initiatorType === 'img' || /\.(avif|gif|jpe?g|png|webp)(?:[?#]|$)/i.test(entry.name))
        .map(entry => ({
          url: entry.name,
          transferBytes: entry.transferSize || 0,
          encodedBytes: entry.encodedBodySize || 0,
          decodedBytes: entry.decodedBodySize || 0,
          durationMs: Math.round(entry.duration),
        })));

      const resourceByUrl = new Map();
      for (const resource of imageResources) {
        const current = resourceByUrl.get(resource.url);
        if (!current || resource.transferBytes > current.transferBytes) {
          resourceByUrl.set(resource.url, resource);
        }
      }

      for (const usage of imageUsages) {
        if (!usage.url) continue;
        const existing = usageByUrl.get(usage.url) || {
          url: usage.url,
          routes: [],
          usages: [],
          resource: resourceByUrl.get(usage.url) || null,
        };
        if (!existing.routes.includes(route)) existing.routes.push(route);
        existing.usages.push({
          route,
          altPresent: usage.alt !== null,
          alt: usage.alt,
          widthAttribute: usage.widthAttribute,
          heightAttribute: usage.heightAttribute,
          naturalWidth: usage.naturalWidth,
          naturalHeight: usage.naturalHeight,
          loading: usage.loading,
          fetchPriority: usage.fetchPriority,
          visibleAtInitialViewport: usage.visibleAtInitialViewport,
          elementIndex: usage.elementIndex,
        });
        const candidateResource = resourceByUrl.get(usage.url);
        if (candidateResource && (!existing.resource || candidateResource.transferBytes > existing.resource.transferBytes)) {
          existing.resource = candidateResource;
        }
        usageByUrl.set(usage.url, existing);
      }
    }
  } finally {
    await context.close();
  }

  const images = [...usageByUrl.values()].map(item => {
    const resource = item.resource;
    const extensions = item.url.match(/\.([a-z0-9]+)(?:[?#]|$)/i);
    return {
      ...item,
      format: extensions ? extensions[1].toLowerCase() : 'unknown',
      routeCount: item.routes.length,
      usageCount: item.usages.length,
      transferBytes: resource?.transferBytes ?? null,
      encodedBytes: resource?.encodedBytes ?? null,
      decodedBytes: resource?.decodedBytes ?? null,
      durationMs: resource?.durationMs ?? null,
      reviewCandidate: (resource?.transferBytes ?? 0) > REVIEW_THRESHOLD_BYTES,
      evidenceLimit: 'Chromium resource timing sizes may be zero or unavailable; verify actual file bytes before choosing an optimization.',
    };
  }).sort((a, b) => (b.transferBytes ?? -1) - (a.transferBytes ?? -1));

  const summary = {
    routesChecked: routes.length,
    uniqueImageUrls: images.length,
    measuredImages: images.filter(image => image.transferBytes !== null && image.transferBytes > 0).length,
    largeImageCandidates: images.filter(image => image.reviewCandidate).length,
    reviewThresholdBytes: REVIEW_THRESHOLD_BYTES,
    largestImages: images.slice(0, 25),
  };

  console.log('Deduplicated image payload audit:', JSON.stringify(summary, null, 2));

  const reportDir = path.join(process.cwd(), 'artifacts', 'image-payload-audit');
  fs.mkdirSync(reportDir, { recursive: true });
  const reportPath = path.join(reportDir, 'image-payload-audit.json');
  fs.writeFileSync(reportPath, JSON.stringify({
    generatedAt: new Date().toISOString(),
    source: 'local PHP site rendered by Playwright in Chromium; not production measurements',
    viewport: { width: 1280, height: 800 },
    enforcedVariantBudgets,
    summary,
    images,
  }, null, 2) + '\n');

  await testInfo.attach('image-payload-audit', {
    path: reportPath,
    contentType: 'application/json',
  });

  expect(routes.length, 'The image audit should cover all sitemap routes').toBeGreaterThan(0);
});
