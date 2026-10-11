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

test('write rendered SEO inventory for sitemap routes', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Inventory runs once at desktop width');
  test.setTimeout(120_000);

  const context = await browser.newContext({ baseURL: 'http://127.0.0.1:8080', viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();
  const inventory = [];

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);
      const pageUrl = new URL(page.url());
      const descriptions = await page.locator('head meta[name="description"]').evaluateAll(nodes =>
        nodes.map(node => node.getAttribute('content') || '')
      );
      const canonicals = await page.locator('head link[rel="canonical"]').evaluateAll(nodes =>
        nodes.map(node => node.getAttribute('href') || '')
      );
      const headings = await page.locator('main h1').allTextContents();
      const robots = await page.locator('head meta[name="robots"]').getAttribute('content').catch(() => null);
      const links = await page.locator('a[href]').evaluateAll((anchors, origin) => {
        const internal = new Set();
        const external = new Set();
        for (const anchor of anchors) {
          const raw = anchor.getAttribute('href');
          if (!raw || !raw.trim() || anchor.hasAttribute('download')) continue;
          try {
            const target = new URL(raw.trim(), window.location.href);
            if (!['http:', 'https:'].includes(target.protocol)) continue;
            target.hash = '';
            (target.origin === origin ? internal : external).add(target.href);
          } catch {
            // Invalid and non-URL anchors are reported separately by the link regression test.
          }
        }
        return { internal: [...internal], external: [...external] };
      }, pageUrl.origin);

      const structuredData = await page.locator('script[type="application/ld+json"]').allTextContents();
      const jsonLd = structuredData.map((source, index) => {
        try {
          const parsed = JSON.parse(source);
          return {
            block: index + 1,
            validJson: true,
            types: Array.isArray(parsed['@type']) ? parsed['@type'] : [parsed['@type']].filter(Boolean),
          };
        } catch {
          return { block: index + 1, validJson: false, types: [] };
        }
      });

      inventory.push({
        route,
        httpStatus: response ? response.status() : null,
        title: await page.title(),
        metaDescriptions: descriptions,
        canonicalUrls: canonicals,
        canonicalPathMatchesRoute: canonicals.length === 1 && (() => {
          try {
            const canonical = new URL(canonicals[0]);
            return canonical.protocol === 'https:' && canonical.pathname === route && canonical.search === '';
          } catch {
            return false;
          }
        })(),
        mainH1: headings.map(text => text.trim()).filter(Boolean),
        robotsMeta: robots,
        uniqueInternalLinkCount: links.internal.length,
        internalLinks: links.internal.sort(),
        uniqueExternalLinkCount: links.external.length,
        jsonLd,
      });
    }
  } finally {
    await context.close();
  }

  const normalize = value => value.trim().replace(/\s+/g, ' ').toLocaleLowerCase();
  const findings = [];

  const titlesByValue = new Map();
  const descriptionsByValue = new Map();
  for (const route of inventory) {
    const title = normalize(route.title || '');
    if (title) titlesByValue.set(title, [...(titlesByValue.get(title) || []), route.route]);

    for (const description of route.metaDescriptions) {
      const normalizedDescription = normalize(description);
      if (normalizedDescription) {
        descriptionsByValue.set(normalizedDescription, [
          ...(descriptionsByValue.get(normalizedDescription) || []),
          route.route,
        ]);
      }
    }

    if (!route.title.trim()) findings.push({ type: 'missing-title', routes: [route.route], severity: 'review' });
    if (route.metaDescriptions.length !== 1 || !route.metaDescriptions[0]?.trim()) {
      findings.push({ type: 'description-missing-or-duplicate-tag', routes: [route.route], severity: 'review' });
    }
    if (route.mainH1.length !== 1) findings.push({ type: 'main-h1-count-not-one', routes: [route.route], severity: 'review' });
    if (!route.canonicalPathMatchesRoute) findings.push({ type: 'canonical-mismatch', routes: [route.route], severity: 'review' });
    if (route.jsonLd.some(block => !block.validJson)) {
      findings.push({ type: 'invalid-json-ld', routes: [route.route], severity: 'review' });
    }
    if (route.robotsMeta && /(?:^|,)\s*noindex\b/i.test(route.robotsMeta)) {
      findings.push({ type: 'noindex-directive', routes: [route.route], severity: 'review' });
    }
  }

  for (const [title, matchingRoutes] of titlesByValue) {
    if (matchingRoutes.length > 1) {
      findings.push({ type: 'duplicate-title', value: title, routes: matchingRoutes, severity: 'review' });
    }
  }
  for (const [description, matchingRoutes] of descriptionsByValue) {
    if (matchingRoutes.length > 1) {
      findings.push({ type: 'duplicate-meta-description', value: description, routes: matchingRoutes, severity: 'review' });
    }
  }

  const findingCounts = findings.reduce((counts, finding) => {
    counts[finding.type] = (counts[finding.type] || 0) + 1;
    return counts;
  }, {});
  console.log('Rendered SEO inventory summary:', JSON.stringify({
    routesChecked: inventory.length,
    findingCount: findings.length,
    findingCounts,
    findings,
  }, null, 2));

  const reportDir = path.join(process.cwd(), 'artifacts', 'seo-inventory');
  fs.mkdirSync(reportDir, { recursive: true });
  const reportPath = path.join(reportDir, 'rendered-seo-inventory.json');
  fs.writeFileSync(reportPath, JSON.stringify({
    generatedAt: new Date().toISOString(),
    source: 'local PHP site rendered by Playwright; not a production crawl',
    routeCount: inventory.length,
    findingCount: findings.length,
    findingCounts,
    findings,
    routes: inventory,
  }, null, 2) + '\n');

  await testInfo.attach('rendered-seo-inventory', {
    path: reportPath,
    contentType: 'application/json',
  });

  expect(inventory.length, 'Every sitemap route should be represented in the inventory').toBe(routes.length);
});
