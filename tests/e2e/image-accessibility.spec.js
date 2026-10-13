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

test('rendered sitemap pages provide alt attributes and intentionally hide only decorative images', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Image accessibility inventory runs once across sitemap routes');
  test.setTimeout(180_000);

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const inventory = [];
  const findings = [];

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, 'No HTTP response for ' + route).not.toBeNull();
      expect(response.status(), 'Unexpected HTTP status for ' + route).toBeLessThan(400);

      const images = await page.locator('img').evaluateAll(nodes => nodes.map((image, index) => {
        const alt = image.getAttribute('alt');
        const hidden = image.getAttribute('aria-hidden') === 'true'
          || Boolean(image.closest('[aria-hidden="true"]'));
        const link = image.closest('a[href]');
        const linkedName = link
          ? [
              link.getAttribute('aria-label') || '',
              link.getAttribute('title') || '',
              (link.innerText || '').trim(),
            ].some(value => value.trim() !== '')
          : false;
        return {
          route: window.location.pathname,
          index: index + 1,
          src: image.currentSrc || image.src || '',
          altPresent: alt !== null,
          alt: alt,
          explicitlyHidden: hidden,
          insideNamedLink: linkedName,
        };
      }));

      for (const image of images) {
        inventory.push(image);
        if (!image.altPresent) {
          findings.push({ ...image, issue: 'missing-alt-attribute' });
        } else if (image.alt.trim() === '' && !image.explicitlyHidden && !image.insideNamedLink) {
          findings.push({ ...image, issue: 'empty-alt-not-marked-decorative-or-contained-by-named-link' });
        }
      }
    }
  } finally {
    await context.close();
  }

  const reportDir = path.join(process.cwd(), 'artifacts', 'image-accessibility');
  fs.mkdirSync(reportDir, { recursive: true });
  const report = {
    generatedAt: new Date().toISOString(),
    source: 'Rendered local PHP site in Chromium; sitemap routes only, not a production crawl.',
    routesChecked: routes.length,
    imageUsages: inventory.length,
    missingAltAttributes: findings.filter(item => item.issue === 'missing-alt-attribute').length,
    unqualifiedEmptyAltAttributes: findings.filter(item => item.issue !== 'missing-alt-attribute').length,
    findings,
    images: inventory,
  };
  const reportPath = path.join(reportDir, 'image-alt-audit.json');
  fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
  await testInfo.attach('image-alt-audit', { path: reportPath, contentType: 'application/json' });

  expect(findings, 'Rendered images must have alt attributes; empty alt is reserved for explicitly decorative images or images inside named links').toEqual([]);
});
