const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

function getSitemapEntries() {
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  return [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
    .map(([, value]) => new URL(value.trim()))
    .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
    .map(url => url.pathname || '/');
}

const routes = [...new Set(getSitemapEntries())];

test.describe('technical SEO metadata', () => {
  for (const route of routes) {
    test(`${route} has valid indexable page metadata`, async ({ page }, testInfo) => {
      test.skip(testInfo.project.name !== 'desktop-1280', 'SEO metadata is viewport-independent');

      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

      await expect(page).toHaveTitle(/\S/);

      const description = page.locator('head meta[name="description"]');
      await expect(description, 'Expected exactly one meta description').toHaveCount(1);
      await expect(description).toHaveAttribute('content', /\S/);

      const canonical = page.locator('head link[rel="canonical"]');
      await expect(canonical, 'Expected exactly one canonical link').toHaveCount(1);
      const canonicalHref = await canonical.getAttribute('href');
      expect(canonicalHref, 'Canonical URL must be absolute HTTPS').toMatch(/^https:\/\//);
      const canonicalUrl = new URL(canonicalHref);
      expect(canonicalUrl.pathname, 'Canonical path must match the sitemap route').toBe(route);
      expect(canonicalUrl.search, 'Canonical URL should not include tracking/query parameters').toBe('');

      await expect(page.locator('main h1'), 'Expected one primary H1 inside main').toHaveCount(1);

      const structuredData = await page.locator('script[type="application/ld+json"]').allTextContents();
      for (const [index, source] of structuredData.entries()) {
        expect(() => JSON.parse(source), `Invalid JSON-LD block ${index + 1} on ${route}`).not.toThrow();
      }
    });
  }
});

test('experience hubs render breadcrumb navigation and valid structured data', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Experience architecture checks run once');
  for (const route of ['/pack.php', '/desert-safari.php', '/guided-jaisalmer-tour.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator('main h1')).toHaveCount(1);
    await expect(page.locator('nav[aria-label="Breadcrumb"]')).toBeVisible();
    const blocks = await page.locator('script[type="application/ld+json"]').allTextContents();
    for (const source of blocks) {
      const parsed = JSON.parse(source);
      const graph = Array.isArray(parsed['@graph']) ? parsed['@graph'] : [];
      const breadcrumb = graph.find(node => node['@type'] === 'BreadcrumbList');
      if (breadcrumb) {
        expect(breadcrumb.itemListElement.length).toBeGreaterThanOrEqual(2);
        expect(breadcrumb.itemListElement[breadcrumb.itemListElement.length - 1].name).toBeTruthy();
      }
    }
  }
});
