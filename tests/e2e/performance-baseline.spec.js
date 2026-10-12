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
const LARGE_RESOURCE_BYTES = 250 * 1024;
const LARGE_DOCUMENT_BYTES = 500 * 1024;

test('write rendered performance and asset baseline for sitemap routes', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Performance inventory runs once at desktop width');
  test.setTimeout(180_000);

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const routeReports = [];

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'load', timeout: 20_000 });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

      const documentMetrics = await page.evaluate(() => {
        const entry = performance.getEntriesByType('navigation')[0];
        return entry ? {
          domContentLoadedMs: Math.round(entry.domContentLoadedEventEnd),
          loadEventMs: Math.round(entry.loadEventEnd),
          responseStartMs: Math.round(entry.responseStart),
          documentTransferBytes: entry.transferSize || 0,
          documentEncodedBytes: entry.encodedBodySize || 0,
          documentDecodedBytes: entry.decodedBodySize || 0,
        } : null;
      });

      const images = await page.locator('img').evaluateAll(nodes => nodes.map((img, index) => {
        const rect = img.getBoundingClientRect();
        const style = window.getComputedStyle(img);
        return {
          index: index + 1,
          src: img.currentSrc || img.src || '',
          altPresent: img.hasAttribute('alt'),
          altText: img.getAttribute('alt'),
          widthAttribute: img.getAttribute('width'),
          heightAttribute: img.getAttribute('height'),
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
          renderedWidth: Math.round(rect.width),
          renderedHeight: Math.round(rect.height),
          loading: img.loading || 'eager',
          fetchPriority: img.fetchPriority || 'auto',
          visibleAtInitialViewport: rect.bottom > 0 && rect.top < window.innerHeight,
          display: style.display,
        };
      }));

      const resources = await page.evaluate(() => performance.getEntriesByType('resource').map(entry => ({
        url: entry.name,
        initiatorType: entry.initiatorType || 'other',
        durationMs: Math.round(entry.duration),
        transferBytes: entry.transferSize || 0,
        encodedBytes: entry.encodedBodySize || 0,
        decodedBytes: entry.decodedBodySize || 0,
      })).filter(resource => {
        try {
          return new URL(resource.url).origin === window.location.origin;
        } catch {
          return false;
        }
      }));

      routeReports.push({
        route,
        httpStatus: response.status(),
        document: documentMetrics,
        imageCount: images.length,
        images,
        sameOriginResourceCount: resources.length,
        sameOriginTransferBytes: resources.reduce((sum, resource) => sum + resource.transferBytes, 0),
        resources,
      });
    }
  } finally {
    await context.close();
  }

  const findings = [];
  const largeResources = [];
  for (const routeReport of routeReports) {
    if (routeReport.document?.documentTransferBytes > LARGE_DOCUMENT_BYTES) {
      findings.push({
        type: 'large-document-transfer',
        route: routeReport.route,
        bytes: routeReport.document.documentTransferBytes,
        thresholdBytes: LARGE_DOCUMENT_BYTES,
        severity: 'review',
      });
    }

    for (const image of routeReport.images) {
      if (!image.widthAttribute || !image.heightAttribute) {
        findings.push({
          type: 'image-missing-explicit-dimensions',
          route: routeReport.route,
          src: image.src,
          imageIndex: image.index,
          severity: 'review',
        });
      }
      if (!image.altPresent) {
        findings.push({
          type: 'image-missing-alt-attribute',
          route: routeReport.route,
          src: image.src,
          imageIndex: image.index,
          severity: 'review',
        });
      }
      if (!image.visibleAtInitialViewport && image.loading !== 'lazy') {
        findings.push({
          type: 'below-fold-image-not-lazy',
          route: routeReport.route,
          src: image.src,
          imageIndex: image.index,
          severity: 'review',
        });
      }
    }

    for (const resource of routeReport.resources) {
      if (resource.transferBytes > LARGE_RESOURCE_BYTES) {
        largeResources.push({
          route: routeReport.route,
          url: resource.url,
          initiatorType: resource.initiatorType,
          transferBytes: resource.transferBytes,
          durationMs: resource.durationMs,
        });
      }
    }
  }

  const findingsByType = findings.reduce((counts, finding) => {
    counts[finding.type] = (counts[finding.type] || 0) + 1;
    return counts;
  }, {});
  const summary = {
    routesChecked: routeReports.length,
    findingCount: findings.length,
    findingsByType,
    largeResourceCount: largeResources.length,
    largestResources: [...largeResources].sort((a, b) => b.transferBytes - a.transferBytes).slice(0, 30),
    routeTimings: routeReports.map(({ route, document }) => ({
      route,
      domContentLoadedMs: document?.domContentLoadedMs ?? null,
      loadEventMs: document?.loadEventMs ?? null,
      responseStartMs: document?.responseStartMs ?? null,
      documentTransferBytes: document?.documentTransferBytes ?? null,
      sameOriginResourceCount: routeReports.find(item => item.route === route)?.sameOriginResourceCount ?? 0,
      sameOriginTransferBytes: routeReports.find(item => item.route === route)?.sameOriginTransferBytes ?? 0,
    })),
    findings,
  };

  console.log('Rendered performance baseline summary:', JSON.stringify(summary, null, 2));

  const reportDir = path.join(process.cwd(), 'artifacts', 'performance-baseline');
  fs.mkdirSync(reportDir, { recursive: true });
  const reportPath = path.join(reportDir, 'rendered-performance-baseline.json');
  fs.writeFileSync(reportPath, JSON.stringify({
    generatedAt: new Date().toISOString(),
    source: 'local PHP site rendered by Playwright in Chromium; not a production measurement',
    viewport: { width: 1280, height: 800 },
    thresholds: {
      largeResourceBytes: LARGE_RESOURCE_BYTES,
      largeDocumentBytes: LARGE_DOCUMENT_BYTES,
      interpretation: 'Review thresholds for triage only; not pass/fail performance budgets.',
    },
    ...summary,
    routes: routeReports,
  }, null, 2) + '\n');

  await testInfo.attach('rendered-performance-baseline', {
    path: reportPath,
    contentType: 'application/json',
  });

  expect(routeReports.length, 'Every sitemap route should be represented in the performance baseline').toBe(routes.length);
});
