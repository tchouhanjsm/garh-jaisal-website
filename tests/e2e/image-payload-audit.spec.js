const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';

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
const ROOT = path.resolve(__dirname, '../..');
const RESPONSIVE_IMAGE_MANIFEST = JSON.parse(
  fs.readFileSync(path.join(ROOT, 'scripts/responsive-image-assets-manifest.json'), 'utf8')
);
const ENFORCED_RESPONSIVE_VARIANT_BUDGETS = Object.entries(
  RESPONSIVE_IMAGE_MANIFEST.variant_max_bytes || {}
).map(([path, maxBytes]) => ({ path, maxBytes }));
const responsiveBudgetFor = candidatePath => RESPONSIVE_IMAGE_MANIFEST.variant_max_bytes?.[candidatePath];

const VIEWPORTS = [
  { name: 'mobile-390', width: 390, height: 844 },
  { name: 'desktop-1280', width: 1280, height: 800 },
];
const IMAGE_ROUTE_SMOKE = [
  '/',
  '/gallery.php',
  '/room.php',
  '/pack.php',
  '/restaurant.php',
  '/roof.php',
  '/direction.php',
  '/guided-jaisalmer-tour.php',
  '/full-day-jaisalmer-tour.php',
  '/jaisalmer-fort-guide.php',
];

test('home and rooftop photo slots reserve dimensions and match their layouts', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run once with controlled viewport contexts.');
  const cases = [
    { route: '/', selector: '.home-rooftop .media img[src="img/roofslide.webp"]', sizes: '(max-width: 760px) 100vw, 50vw' },
    { route: '/roof.php', selector: '.cards .card-image img[src="img/roof.webp"]', sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
    { route: '/roof.php', selector: '.cards .card-image img[src="img/r2.webp"]', sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
    { route: '/roof.php', selector: '.cards .card-image img[src="img/r3.webp"]', sizes: '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw' },
  ];
  for (const viewport of VIEWPORTS) {
    const context = await browser.newContext({ baseURL: BASE_URL, viewport: { width: viewport.width, height: viewport.height }, deviceScaleFactor: 1 });
    try {
      const page = await context.newPage();
      for (const item of cases) {
        await page.goto(item.route, { waitUntil: 'domcontentloaded' });
        const image = page.locator(item.selector);
        await expect(image, `${item.route}: ${item.selector}`).toHaveCount(1);
        await expect(image).toHaveAttribute('width', /^[1-9][0-9]*$/);
        await expect(image).toHaveAttribute('height', /^[1-9][0-9]*$/);
        await expect(image).toHaveAttribute('sizes', item.sizes);
      }
    } finally {
      await context.close();
    }
  }
});

test('Directions LCP hero matches its preload and selects width-appropriate candidates', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Directions hero selection runs once with controlled mobile/desktop viewports.');
  test.setTimeout(45_000);
  const cases = [
    { viewport: { width: 390, height: 844 }, file: 'img/responsive/v2/courtyard-480.webp', maxBytes: responsiveBudgetFor('img/responsive/v2/courtyard-480.webp') },
    { viewport: { width: 1280, height: 800 }, file: 'img/responsive/v2/courtyard-1200.webp', maxBytes: responsiveBudgetFor('img/responsive/v2/courtyard-1200.webp') },
  ];
  for (const item of cases) {
    const context = await browser.newContext({ baseURL: BASE_URL, viewport: item.viewport, deviceScaleFactor: 1 });
    try {
      const page = await context.newPage();
      const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
      expect(response).not.toBeNull();
      expect(response.status()).toBe(200);
      const hero = page.locator('.page-hero-media img').first();
      await expect(hero).toBeVisible();
      await expect.poll(() => hero.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
      const state = await page.evaluate(() => {
        const img = document.querySelector('.page-hero-media img');
        const preloads = [...document.querySelectorAll('link[rel="preload"][as="image"]')];
        const preload = preloads.find(link => link.hasAttribute('imagesrcset'));
        return {
          srcset: img?.getAttribute('srcset') || '',
          sizes: img?.getAttribute('sizes') || '',
          currentSrc: img?.currentSrc || '',
          naturalWidth: img?.naturalWidth || 0,
          naturalHeight: img?.naturalHeight || 0,
          fetchPriority: img?.fetchPriority || '',
          loading: img?.getAttribute('loading') || '',
          preloadCount: preloads.length,
          preloadHref: preload?.getAttribute('href') || '',
          preloadSrcset: preload?.getAttribute('imagesrcset') || '',
          preloadSizes: preload?.getAttribute('imagesizes') || '',
          renderedWidth: img?.getBoundingClientRect().width || 0,
        };
      });
      expect(state.sizes).toBe('100vw');
      expect(state.fetchPriority).toBe('high');
      expect(state.loading).toBe('eager');
      expect(state.preloadCount).toBe(1);
      expect(state.preloadHref).toBe('/img/v2/courtyard.jpg');
      expect(state.preloadSizes).toBe('100vw');
      expect(state.currentSrc).toContain('/' + item.file);
      expect(state.naturalWidth).toBeGreaterThan(0);
      expect(state.naturalHeight).toBeGreaterThan(0);
      expect(state.renderedWidth).toBeGreaterThan(0);
      expect(Math.abs(state.naturalWidth / state.naturalHeight - 1333 / 2000)).toBeLessThanOrEqual(0.01);
      const normalizeSrcset = value => value.split(',').map(candidate => candidate.trim().replace(/^\/+/, '')).join(', ');
      expect(normalizeSrcset(state.preloadSrcset)).toBe(normalizeSrcset(state.srcset));
      expect(item.maxBytes, item.file + ' must have a manifest-owned byte budget').toBeGreaterThan(0);
      const bytes = fs.statSync(path.join(ROOT, item.file)).size;
      expect(bytes, item.file + ' should stay under its viewport-specific transfer budget').toBeLessThanOrEqual(item.maxBytes);
    } finally {
      await context.close();
    }
  }
});

test('audit every published route for image slots and payloads at mobile and desktop viewports', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Create controlled mobile and desktop browser contexts only once.');
  test.setTimeout(360_000);

  const enforcedVariantBudgets = ENFORCED_RESPONSIVE_VARIANT_BUDGETS.map(budget => {
    const absolutePath = path.join(ROOT, budget.path);
    expect(fs.existsSync(absolutePath), `${budget.path} should be generated for the audit`).toBe(true);
    const bytes = fs.statSync(absolutePath).size;
    expect(budget.maxBytes, `${budget.path} must have a positive manifest-owned budget`).toBeGreaterThan(0);
    expect(bytes, `${budget.path} should stay within its ${Math.round(budget.maxBytes / 1024)} KiB transfer budget`).toBeLessThanOrEqual(budget.maxBytes);
    return { ...budget, bytes, headroomBytes: budget.maxBytes - bytes, status: 'pass' };
  });

  const imageUsages = [];
  const imageResources = [];
  const routeResults = [];
  for (const viewport of VIEWPORTS) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
    });
    try {
      const page = await context.newPage();
      for (const route of routes) {
        const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, `No HTTP response for ${route} at ${viewport.name}`).not.toBeNull();
        expect(response.status(), `Unexpected HTTP status for ${route} at ${viewport.name}`).toBeLessThan(400);
        // Decode only images in the initial viewport. Off-screen eager images are recorded
        // but must not hold the route audit open; this phase targets first-render delivery.
        await page.locator('img').evaluateAll(async images => {
          const initiallyVisible = images.filter(img => {
            const rect = img.getBoundingClientRect();
            return rect.width > 0 && rect.height > 0 && rect.bottom > 0 && rect.top < window.innerHeight;
          });
          await Promise.all(initiallyVisible.map(async img => {
            if (!img.decode) return;
            // Broken or stalled image requests must not hold this route-wide audit indefinitely.
            // The final visible-image assertions still fail if the deadline expires before load.
            await Promise.race([
              img.decode().catch(() => undefined),
              new Promise(resolve => window.setTimeout(resolve, 1500)),
            ]);
          }));
        });
        const usage = await page.locator('img').evaluateAll((images, viewportName) => images.map(img => {
          const rect = img.getBoundingClientRect();
          const style = window.getComputedStyle(img);
          const currentPath = new URL(img.currentSrc || img.src, document.baseURI).pathname;
          // <picture> can own the selected candidate; otherwise use the img srcset.
          const activeSource = Array.from(img.closest('picture')?.querySelectorAll('source[srcset]') || [])
            .find(source => source.srcset.split(',').some(candidate => {
              try {
                return new URL(candidate.trim().split(/\\s+/)[0], document.baseURI).pathname === currentPath;
              } catch {
                return false;
              }
            }));
          const responsiveOwner = activeSource || img;
          const sourceSet = responsiveOwner.getAttribute('srcset') || img.getAttribute('srcset') || '';
          const selectedCandidate = sourceSet.split(',').map(candidate => {
            const parts = candidate.trim().split(/\\s+/);
            try {
              return new URL(parts[0], document.baseURI).pathname === currentPath
                ? { descriptor: parts[1] || '' }
                : null;
            } catch {
              return null;
            }
          }).find(Boolean);
          const widthDescriptor = (selectedCandidate?.descriptor || '').match(/^([1-9][0-9]*)w$/);
          const selectedSourceWidth = widthDescriptor ? Number(widthDescriptor[1]) : img.naturalWidth;
          const visibleAtInitialViewport = rect.width > 0 && rect.height > 0
            && rect.right > 0 && rect.left < window.innerWidth
            && rect.bottom > 0 && rect.top < window.innerHeight
            && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0;
          return {
            viewportName,
            route: window.location.pathname,
            viewport: { width: window.innerWidth, height: window.innerHeight },
            src: img.getAttribute('src') || '',
            srcset: img.getAttribute('srcset') || '',
            sizes: img.getAttribute('sizes') || '',
            currentSrc: img.currentSrc || '',
            selectedSourceWidth,
            selectedSourceDescriptor: selectedCandidate?.descriptor || '',
            alt: img.getAttribute('alt'),
            widthAttribute: img.getAttribute('width'),
            heightAttribute: img.getAttribute('height'),
            naturalWidth: img.naturalWidth,
            naturalHeight: img.naturalHeight,
            complete: img.complete,
            renderedWidth: Math.round(rect.width * 100) / 100,
            renderedHeight: Math.round(rect.height * 100) / 100,
            loading: img.loading || 'eager',
            fetchPriority: img.fetchPriority || 'auto',
            visibleAtInitialViewport,
          };
        }), viewport.name);
        imageUsages.push(...usage);
        const resources = await page.evaluate(() => performance.getEntriesByType('resource')
          .filter(entry => /\.(?:avif|gif|jpe?g|png|webp)(?:[?#]|$)/i.test(entry.name))
          .map(entry => ({
            url: entry.name,
            transferBytes: entry.transferSize || 0,
            encodedBytes: entry.encodedBodySize || 0,
            decodedBytes: entry.decodedBodySize || 0,
            durationMs: Math.round(entry.duration),
            initiatorType: entry.initiatorType,
          })));
        imageResources.push(...resources.map(resource => ({ ...resource, route, viewport: viewport.name })));
        const preloads = await page.locator('link[rel="preload"][as="image"]').evaluateAll(links => links.map(link => ({
          route: window.location.pathname,
          href: link.getAttribute('href') || '',
          imagesrcset: link.getAttribute('imagesrcset') || '',
          imagesizes: link.getAttribute('imagesizes') || '',
          media: link.getAttribute('media') || '',
          fetchpriority: link.getAttribute('fetchpriority') || 'auto',
        })));
        const visibleUnprioritized = usage.filter(img => img.visibleAtInitialViewport && img.renderedWidth > 300 && img.fetchPriority !== 'high');
        const visibleLazy = usage.filter(img => img.visibleAtInitialViewport && img.renderedWidth > 300 && img.loading === 'lazy');
        routeResults.push({
          route,
          viewport: viewport.name,
          imageCount: usage.length,
          visibleUnprioritized: visibleUnprioritized.map(img => ({ src: img.src, currentSrc: img.currentSrc, width: img.renderedWidth, loading: img.loading, fetchPriority: img.fetchPriority })),
          visibleLazy: visibleLazy.map(img => ({ src: img.src, currentSrc: img.currentSrc, width: img.renderedWidth, loading: img.loading })),
          preloads,
        });
      }
    } finally {
      await context.close();
    }
  }

  const usageByUrl = new Map();
  for (const usage of imageUsages) {
    const key = `${usage.viewportName}|${usage.currentSrc || usage.src}`;
    const existing = usageByUrl.get(key) || { url: usage.currentSrc || usage.src, routes: [], usages: [] };
    if (!existing.routes.includes(usage.route)) existing.routes.push(usage.route);
    existing.usages.push(usage);
    usageByUrl.set(key, existing);
  }
  const resourceByKey = new Map();
  for (const resource of imageResources) {
    const key = `${resource.viewport}|${resource.url}`;
    const current = resourceByKey.get(key);
    if (!current || resource.transferBytes > current.transferBytes) resourceByKey.set(key, resource);
  }

  const images = [...usageByUrl.entries()].map(([key, item]) => {
    const viewportName = key.split('|')[0];
    const resource = resourceByKey.get(`${viewportName}|${item.url}`);
    const usages = item.usages;
    const knownSlots = usages.filter(usage => usage.renderedWidth > 0);
    return {
      ...item,
      routeCount: item.routes.length,
      usageCount: usages.length,
      format: (item.url.match(/\.([a-z0-9]+)(?:[?#]|$)/i) || [null, 'unknown'])[1].toLowerCase(),
      transferBytes: resource?.transferBytes ?? null,
      encodedBytes: resource?.encodedBytes ?? null,
      decodedBytes: resource?.decodedBytes ?? null,
      durationMs: resource?.durationMs ?? null,
      renderedSlotWidthMin: knownSlots.length ? Math.min(...knownSlots.map(x => x.renderedWidth)) : null,
      renderedSlotWidthMax: knownSlots.length ? Math.max(...knownSlots.map(x => x.renderedWidth)) : null,
      selectedSourcePixelsPerCssPixel: usages.filter(x => x.selectedSourceWidth && x.renderedWidth > 0).map(x => ({
        route: x.route,
        viewportWidth: x.viewport.width,
        selectedSourceWidth: x.selectedSourceWidth,
        descriptor: x.selectedSourceDescriptor,
        ratio: Math.round((x.selectedSourceWidth / x.renderedWidth) * 100) / 100,
      })),
      densityCorrectedNaturalWidthPerCssPixel: usages.filter(x => x.naturalWidth && x.renderedWidth > 0).map(x => ({
        route: x.route,
        viewportWidth: x.viewport.width,
        ratio: Math.round((x.naturalWidth / x.renderedWidth) * 100) / 100,
      })),
      reviewCandidate: (resource?.transferBytes ?? 0) > REVIEW_THRESHOLD_BYTES,
      evidenceLimit: 'Local Chromium/resource timing only; not production traffic or field Core Web Vitals.',
    };
  }).sort((a, b) => (b.transferBytes ?? -1) - (a.transferBytes ?? -1));

  const summary = {
    routesChecked: routes.length,
    viewportsChecked: VIEWPORTS.length,
    routeViewportChecks: routeResults.length,
    uniqueImageViewportUrls: images.length,
    measuredImageResources: imageResources.filter(item => item.transferBytes > 0).length,
    largeImageCandidates: images.filter(image => image.reviewCandidate).length,
    imagesBelow90PercentSourceCoverage: images.flatMap(image => image.selectedSourcePixelsPerCssPixel
      .filter(usage => usage.ratio < 0.9)
      .map(usage => ({ url: image.url, transferBytes: image.transferBytes, ...usage }))),
    imagesBelow90PercentSourceCoverageCount: images.reduce((count, image) =>
      count + image.selectedSourcePixelsPerCssPixel.filter(usage => usage.ratio < 0.9).length, 0),
    reviewThresholdBytes: REVIEW_THRESHOLD_BYTES,
    routesWithVisibleLargeImagesNotHighPriority: routeResults.filter(result => result.visibleUnprioritized.length > 0).map(result => ({
      route: result.route,
      viewport: result.viewport,
      images: result.visibleUnprioritized,
    })),
    routesWithVisibleLargeLazyImages: routeResults.filter(result => result.visibleLazy.length > 0).map(result => ({
      route: result.route,
      viewport: result.viewport,
      images: result.visibleLazy,
    })),
  };

  const report = {
    generatedAt: new Date().toISOString(),
    source: 'local PHP site rendered by Playwright in Chromium; synthetic local measurements, not production RUM or field Core Web Vitals',
    viewports: VIEWPORTS,
    enforcedVariantBudgets,
    enforcedVariantBudgetHeadroomBytes: enforcedVariantBudgets.reduce((sum, item) => sum + item.headroomBytes, 0),
    summary,
    routeResults,
    images,
  };
  const reportDir = path.join(process.cwd(), 'artifacts', 'image-payload-audit');
  fs.mkdirSync(reportDir, { recursive: true });
  const reportPath = path.join(reportDir, 'image-payload-audit.json');
  fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
  await testInfo.attach('image-payload-audit', { path: reportPath, contentType: 'application/json' });

  expect(routes.length, 'The image audit should cover all sitemap routes').toBeGreaterThan(0);
  expect(routeResults.length, 'Every published route must be checked at both viewport sizes').toBe(routes.length * VIEWPORTS.length);
  expect(
    summary.largeImageCandidates,
    'No selected responsive image resource should exceed the 250 KiB review threshold after hero budgets are enforced.'
  ).toBe(0);
  const failedLoads = imageUsages.filter(image => image.visibleAtInitialViewport && image.renderedWidth > 0 && image.src && (!image.complete || image.naturalWidth < 1));
  expect(failedLoads.map(image => `${image.route} @${image.viewport.width}px: ${image.src}`), 'Visible images must load at initial viewport').toEqual([]);
});

test('representative guest routes decode their LCP image and keep below-fold photos lazy', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run controlled representative route matrix once.');
  for (const viewport of VIEWPORTS) {
    const context = await browser.newContext({ baseURL: BASE_URL, viewport: { width: viewport.width, height: viewport.height }, deviceScaleFactor: 1 });
    try {
      const page = await context.newPage();
      for (const route of IMAGE_ROUTE_SMOKE) {
        await page.goto(route, { waitUntil: 'domcontentloaded' });
        const hero = page.locator('main img').first();
        await expect(hero, `${route} should render its first main image`).toHaveCount(1);
        await expect.poll(() => hero.evaluate(img => img.complete && img.naturalWidth > 0), { timeout: 10_000 }).toBe(true);
        const state = await hero.evaluate(img => ({
          src: img.currentSrc || img.src,
          loading: img.loading,
          fetchPriority: img.fetchPriority,
          alt: img.getAttribute('alt'),
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
        }));
        expect(state.naturalWidth, `${route}: hero should decode`).toBeGreaterThan(0);
        expect(state.naturalHeight, `${route}: hero dimensions should be valid`).toBeGreaterThan(0);
        expect(state.alt, `${route}: main image should expose alt text`).not.toBeNull();
        expect(state.loading, `${route}: hero should not be marked lazy`).not.toBe('lazy');
      }
    } finally {
      await context.close();
    }
  }
});

