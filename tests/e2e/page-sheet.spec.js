const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');
const { getSitemapRoutes, REPOSITORY_ROOT } = require('./helpers/sitemap-routes');

const BASE_URL = 'http://127.0.0.1:8080';
const OUTPUT_DIR = path.join(REPOSITORY_ROOT, 'artifacts/page-sheet');
const VIEWPORTS = [
  { width: 390, height: 844, label: '390px', mobile: true },
  { width: 1280, height: 800, label: '1280px', mobile: false },
];

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

test('builds a full-page screenshot sheet at 390px and 1280px for every sitemap route', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The page sheet creates its own 390px and 1280px contexts');
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

    try {
      for (const route of routes) {
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

  expect(records.length).toBe(routes.length * VIEWPORTS.length);

  const cards = routes.map(route => {
    const slug = routeSlug(route);
    const mobile = slug + '-390.jpg';
    const desktop = slug + '-1280.jpg';
    return '<article class="route-card"><h2>' + escapeHtml(route) + '</h2>' +
      '<div class="preview-grid">' +
      '<figure><a href="' + mobile + '"><img src="' + mobile + '" loading="lazy" alt="' + escapeHtml(route) + ' full-page screenshot at 390px"></a><figcaption>390 × 844</figcaption></figure>' +
      '<figure><a href="' + desktop + '"><img src="' + desktop + '" loading="lazy" alt="' + escapeHtml(route) + ' full-page screenshot at 1280px"></a><figcaption>1280 × 800</figcaption></figure>' +
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
    '<p>' + routes.length + ' sitemap routes × 2 viewports. Select a preview to open the full image.</p>\n' +
    cards + '\n</body>\n</html>\n';

  const indexPath = path.join(OUTPUT_DIR, 'index.html');
  fs.writeFileSync(indexPath, indexHtml);
  await testInfo.attach('page-sheet-index', { path: indexPath, contentType: 'text/html' });
});
