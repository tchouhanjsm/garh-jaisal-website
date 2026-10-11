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
  )];
}

const routes = getSitemapRoutes();
const ignoredProtocols = ['mailto:', 'tel:', 'javascript:', 'data:'];

test.describe('rendered internal links', () => {
  for (const route of routes) {
    test(`${route} has no broken same-site anchor links`, async ({ page, request }, testInfo) => {
      test.skip(testInfo.project.name !== 'desktop-1280', 'Link validation runs once per route');

      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

      const hrefs = await page.locator('a[href]').evaluateAll(anchors =>
        anchors
          .filter(anchor => !anchor.hasAttribute('download'))
          .map(anchor => anchor.getAttribute('href'))
          .filter(href => typeof href === 'string' && href.trim() !== '')
      );

      const base = new URL(page.url());
      const targets = [...new Set(hrefs.flatMap(href => {
        const trimmed = href.trim();
        if (trimmed.startsWith('#') || ignoredProtocols.some(protocol => trimmed.toLowerCase().startsWith(protocol))) {
          return [];
        }

        let target;
        try {
          target = new URL(trimmed, base);
        } catch {
          return [`INVALID_URL:${trimmed}`];
        }

        if (target.origin !== base.origin) return [];
        target.hash = '';
        return [target.href];
      }))];

      const failures = [];
      for (const target of targets) {
        if (target.startsWith('INVALID_URL:')) {
          failures.push(target);
          continue;
        }

        try {
          const linkResponse = await request.get(target, { maxRedirects: 0, timeout: 15000 });
          if (linkResponse.status() >= 400) {
            failures.push(`${linkResponse.status()} ${target}`);
          }
        } catch (error) {
          failures.push(`REQUEST_ERROR ${target}: ${error.message}`);
        }
      }

      expect(failures, `Broken internal links found on ${route}`).toEqual([]);
    });
  }
});
