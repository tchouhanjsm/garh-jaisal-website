const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');
const { getSitemapRoutes, REPOSITORY_ROOT } = require('./helpers/sitemap-routes');

const BASE_URL = 'http://127.0.0.1:8080';
const OUTPUT_DIR = path.join(REPOSITORY_ROOT, 'artifacts/page-sheet');
const VIEWPORTS = [
  { width: 390, height: 844, label: '390px', mobile: true },
  { width: 1280, height: 800, label: '1280px', mobile: false },
  { width: 768, height: 900, label: '768px', mobile: false },
];
const TABLET_ROUTES = new Set([
  '/',
  '/room.php',
  '/catalogue.php',
  '/car-tours.php',
  '/roof.php',
  '/restaurant.php',
]);

function routeSlug(route) {
  const slug = route === '/' ? 'home' : route.replace(/^\/+|\/+$/g, '').replace(/\.php$/i, '');
  return slug.replace(/[^a-z0-9_-]+/gi, '-') || 'home';
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

test('builds a full-page screenshot sheet at 390px and 1280px for every sitemap route plus a 768px high-intent sample', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The page sheet creates its own responsive contexts');
  test.setTimeout(600_000);

  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  const routes = getSitemapRoutes();
  const records = [];

  for (const viewport of VIEWPORTS) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
      isMobile: viewport.mobile,
      hasTouch: viewport.mobile,
    });
    const page = await context.newPage();

    const viewportRoutes = viewport.width === 768
      ? routes.filter(route => TABLET_ROUTES.has(route))
      : routes;

    try {
      for (const route of viewportRoutes) {
        const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, 'No HTTP response for ' + route).not.toBeNull();
        expect(response.status(), 'Unexpected status for ' + route).toBeLessThan(400);

        // Capture the resting visual state, not a focus outline or an in-flight
        // smooth scroll from the previous route. This affects the test context only.
        await page.evaluate(() => {
          document.documentElement.style.scrollBehavior = 'auto';
          document.body.style.scrollBehavior = 'auto';
          if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
          }
          window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        });
        await page.waitForFunction(() => window.scrollY === 0);

        let offset = 0;
        let steps = 0;
        while (true) {
          const height = await page.evaluate(() => Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
          ));
          if (offset > height) break;

          await page.evaluate(y => window.scrollTo(0, y), offset);
          await page.waitForTimeout(120);
          offset += 300;
          steps += 1;
          if (steps > 400) {
            throw new Error('Page exceeded safe screenshot scroll limit: ' + route);
          }
        }

        await page.evaluate(() => window.scrollTo(0, Math.max(
          document.body.scrollHeight,
          document.documentElement.scrollHeight
        )));
        await page.waitForTimeout(120);
        await page.waitForTimeout(800);

        // Lazy images can remain blank in full-page screenshots even after scrolling.
        // Only this test context is changed: eagerly settle local images and decode them.
        const unreadyImages = await page.locator('img').evaluateAll(async images => {
          const localImages = images.filter(img => {
            if (!img.getAttribute('src')) return false;
            try {
              const source = img.currentSrc || img.src;
              return source && new URL(source, document.baseURI).origin === window.location.origin;
            } catch {
              return false;
            }
          });
          await Promise.all(localImages.map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(resolve => {
              const settle = () => resolve();
              img.addEventListener('load', settle, { once: true });
              img.addEventListener('error', settle, { once: true });
              img.loading = 'eager';
            });
          }));
          await Promise.all(localImages
            .filter(img => img.complete && img.naturalWidth > 0 && typeof img.decode === 'function')
            .map(img => img.decode().catch(() => undefined)));
          return localImages
            .filter(img => !img.complete || img.naturalWidth <= 0 || img.naturalHeight <= 0)
            .map(img => img.currentSrc || img.src);
        });
        expect(unreadyImages, 'Same-origin images must load before the screenshot sheet captures ' + route).toEqual([]);

        await page.evaluate(() => {
          if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
          }
          window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        });
        await page.waitForFunction(() => window.scrollY === 0);
        await page.waitForTimeout(100);

        const filename = routeSlug(route) + '-' + viewport.width + '.jpg';
        const screenshotPath = path.join(OUTPUT_DIR, filename);
        await page.screenshot({
          path: screenshotPath,
          type: 'jpeg',
          quality: 60,
          fullPage: true,
          animations: 'disabled',
        });

        records.push({
          route,
          viewport: viewport.width,
          label: viewport.label,
          filename,
          screenshotPath,
        });
      }
    } finally {
      await context.close();
    }
  }

  const tabletRouteCount = routes.filter(route => TABLET_ROUTES.has(route)).length;
  expect(records.length).toBe(routes.length * 2 + tabletRouteCount);

  // Produce a focused Gallery review set at every width in the brand constitution.
  // Expand the Desert section so the full owner-provided photo selection is visible
  // for subject/alt review, then capture the same responsive page at each viewport.
  const galleryReviewViewports = [
    { width: 320, height: 700, mobile: true },
    { width: 390, height: 844, mobile: true },
    { width: 768, height: 900, mobile: false },
    { width: 1280, height: 800, mobile: false },
    { width: 1440, height: 900, mobile: false },
  ];
  const galleryReviewRecords = [];
  for (const viewport of galleryReviewViewports) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
      isMobile: viewport.mobile,
      hasTouch: viewport.mobile,
    });
    const page = await context.newPage();
    try {
      const response = await page.goto('/gallery.php', { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, 'No HTTP response for Gallery at ' + viewport.width + 'px').not.toBeNull();
      expect(response.status(), 'Unexpected Gallery status at ' + viewport.width + 'px').toBe(200);

      const desert = page.locator('[data-gallery-category="desert"]');
      const more = desert.locator('[data-gallery-more]');
      if (await more.isVisible()) await more.click();
      await expect(desert.locator('[data-gallery-open]')).toHaveCount(10);
      await expect(desert.locator('.gallery-item:visible')).toHaveCount(10);

      const desertAltAudit = await desert.locator('[data-gallery-open]').evaluateAll(anchors =>
        anchors.map(anchor => ({
          source: new URL(anchor.href, document.baseURI).pathname,
          alt: anchor.querySelector('img')?.getAttribute('alt') || '',
        }))
      );
      expect(desertAltAudit).toHaveLength(10);
      expect(desertAltAudit.every(photo => photo.alt.trim().length > 0)).toBe(true);

      await page.locator('img').evaluateAll(async images => {
        const localImages = images.filter(img => {
          try {
            return new URL(img.currentSrc || img.src, document.baseURI).origin === window.location.origin;
          } catch {
            return false;
          }
        });
        await Promise.all(localImages.map(img => {
          img.loading = 'eager';
          if (img.complete) return Promise.resolve();
          return new Promise(resolve => {
            img.addEventListener('load', resolve, { once: true });
            img.addEventListener('error', resolve, { once: true });
          });
        }));
        await Promise.all(localImages
          .filter(img => img.complete && img.naturalWidth > 0 && typeof img.decode === 'function')
          .map(img => img.decode().catch(() => undefined)));
      });
      await page.evaluate(() => {
        document.documentElement.style.scrollBehavior = 'auto';
        document.body.style.scrollBehavior = 'auto';
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
      });
      await page.waitForFunction(() => window.scrollY === 0);
      await page.waitForTimeout(100);

      const filename = 'gallery-review-' + viewport.width + '.jpg';
      const screenshotPath = path.join(OUTPUT_DIR, filename);
      await page.screenshot({
        path: screenshotPath,
        type: 'jpeg',
        quality: 75,
        fullPage: true,
        animations: 'disabled',
      });
      galleryReviewRecords.push({ width: viewport.width, filename, screenshotPath, desertAltAudit });
    } finally {
      await context.close();
    }
  }

  fs.writeFileSync(
    path.join(OUTPUT_DIR, 'gallery-desert-alt-audit.json'),
    JSON.stringify(galleryReviewRecords.map(record => ({
      viewport: record.width,
      desertPhotos: record.desertAltAudit,
    })), null, 2) + '\\n'
  );
  fs.writeFileSync(
    path.join(OUTPUT_DIR, 'gallery-review.html'),
    '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
      '<title>Gallery responsive and Desert image-alt review</title></head><body><h1>Gallery review — expanded Desert section</h1>' +
      '<p>Five viewport captures show all ten Desert photos expanded. The companion JSON records each source path and rendered alt text; compare the photos against the owner-provided originals before release.</p>' +
      galleryReviewRecords.map(record => '<figure><h2>' + record.width + 'px</h2><a href="' + record.filename + '"><img src="' + record.filename + '" alt="Gallery page at ' + record.width + 'px with all Desert photos expanded" style="max-width:100%;height:auto"></a></figure>').join('\\n') +
      '</body></html>\\n'
  );

  const cards = routes.map(route => {
    const slug = routeSlug(route);
    const mobile = slug + '-390.jpg';
    const desktop = slug + '-1280.jpg';
    const hasTablet = records.some(record => record.route === route && record.viewport === 768);
    const tabletPreview = hasTablet
      ? '<figure><a href="' + slug + '-768.jpg"><img src="' + slug + '-768.jpg" loading="lazy" alt="' + escapeHtml(route) + ' full-page screenshot at 768px"></a><figcaption>768 × 900</figcaption></figure>'
      : '';
    return '<article class="route-card"><h2>' + escapeHtml(route) + '</h2>' +
      '<div class="preview-grid">' +
      '<figure><a href="' + mobile + '"><img src="' + mobile + '" loading="lazy" alt="' + escapeHtml(route) + ' full-page screenshot at 390px"></a><figcaption>390 × 844</figcaption></figure>' +
      '<figure><a href="' + desktop + '"><img src="' + desktop + '" loading="lazy" alt="' + escapeHtml(route) + ' full-page screenshot at 1280px"></a><figcaption>1280 × 800</figcaption></figure>' +
      tabletPreview +
      '</div></article>';
  }).join('\n');

  const indexHtml = '<!doctype html>\n<html lang="en">\n<head>\n' +
    '<meta charset="utf-8">\n<meta name="viewport" content="width=device-width, initial-scale=1">\n' +
    '<title>Garh Jaisal Haveli — Page Screenshot Sheet</title>\n' +
    '<style>body{margin:0;padding:24px;font:16px/1.5 system-ui,sans-serif;background:#fbf8f1;color:#29231e}' +
    'h1{margin-top:0}.route-card{padding:18px;margin:0 0 24px;border:1px solid #d9d0c3;border-radius:12px;background:#fff}' +
    '.preview-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}' +
    'figure{margin:0}img{display:block;width:100%;height:220px;object-fit:contain;object-position:top;background:#eee9df}' +
    'figcaption{font-size:14px;color:#63584d;margin-top:6px}@media(max-width:600px){body{padding:12px}.preview-grid{grid-template-columns:1fr}img{height:280px}}</style>\n' +
    '</head>\n<body>\n<h1>Full-page screenshot sheet</h1>\n' +
    '<p>' + routes.length + ' sitemap routes at 390px and 1280px, plus ' + routes.filter(route => TABLET_ROUTES.has(route)).length + ' high-intent routes at 768px. Select a preview to open the full image.</p>\n' +
    cards + '\n</body>\n</html>\n';

  const indexPath = path.join(OUTPUT_DIR, 'index.html');
  fs.writeFileSync(indexPath, indexHtml);
  await testInfo.attach('page-sheet-index', { path: indexPath, contentType: 'text/html' });
});
