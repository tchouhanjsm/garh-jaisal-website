const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');
const { getSitemapRoutes, REPOSITORY_ROOT } = require('./helpers/sitemap-routes');

const BASE_URL = 'http://127.0.0.1:8080';
const FIXTURE_PATH = path.join(REPOSITORY_ROOT, 'tests/fixtures/head-snapshot.json');
const ARTIFACT_PATH = path.join(REPOSITORY_ROOT, 'artifacts/head-snapshot/head-snapshot.json');
const DRAFT_AND_HIDDEN_ROUTES = [
  '/longewala-tour.php',
  '/rajasthan-tours.php',
  '/explore-the-desert.php',
];

async function captureHead(page, route) {
  const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
  expect(response, 'No HTTP response for ' + route).not.toBeNull();
  expect(response.status(), 'Unexpected status for ' + route).toBeLessThan(400);

  const snapshot = await page.evaluate(() => {
    const readMeta = selector => {
      const element = document.querySelector(selector);
      return element ? element.getAttribute('content') : null;
    };

    const social = Array.from(document.querySelectorAll('meta[property^="og:"], meta[name^="twitter:"]'))
      .map(element => ({
        key: element.getAttribute('property') || element.getAttribute('name') || '',
        content: element.getAttribute('content') || '',
      }))
      .sort((left, right) => left.key.localeCompare(right.key) || left.content.localeCompare(right.content));

    const jsonLdTypes = [];
    let invalidJsonLdScripts = 0;
    const visitJsonLd = value => {
      if (Array.isArray(value)) {
        value.forEach(visitJsonLd);
        return;
      }
      if (!value || typeof value !== 'object') {
        return;
      }
      if (value['@type']) {
        const values = Array.isArray(value['@type']) ? value['@type'] : [value['@type']];
        values.forEach(type => jsonLdTypes.push(String(type)));
      }
      Object.values(value).forEach(visitJsonLd);
    };

    for (const script of document.querySelectorAll('script[type="application/ld+json"]')) {
      try {
        visitJsonLd(JSON.parse(script.textContent || 'null'));
      } catch {
        invalidJsonLdScripts += 1;
      }
    }

    const inlineScriptText = Array.from(document.querySelectorAll('script:not([src])'))
      .map(script => script.textContent || '')
      .join('\n');
    const gtagConfigIds = [];
    const configPattern = /gtag\s*\(\s*['"]config['"]\s*,\s*['"]([^'"]+)['"]\s*\)/g;
    let match;
    while ((match = configPattern.exec(inlineScriptText)) !== null) {
      gtagConfigIds.push(match[1]);
    }

    return {
      title: document.querySelector('title')?.textContent?.trim() || null,
      description: readMeta('meta[name="description"]'),
      canonical: document.querySelector('link[rel="canonical"]')?.href || null,
      robots: readMeta('meta[name="robots"]'),
      social,
      jsonLdTypes: jsonLdTypes.sort(),
      invalidJsonLdScripts,
      gtagScriptCount: document.querySelectorAll('script[src*="googletagmanager.com/gtag/js"]').length,
      gtagConfigIds,
    };
  });

  return { route, ...snapshot };
}

test('sitemap head output matches the frozen pre-refactor baseline', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'SEO head snapshot is captured once on desktop-1280');
  test.setTimeout(240_000);

  const context = await browser.newContext({
    baseURL: BASE_URL,
    viewport: { width: 1280, height: 800 },
    deviceScaleFactor: 1,
  });
  // Measure server-rendered head output only. Third-party gtag.js can mutate the
  // document by adding another script element after navigation; that runtime side
  // effect is not part of the page's emitted HTML and makes this snapshot flaky.
  await context.route('https://www.googletagmanager.com/**', route => route.abort());
  const page = await context.newPage();
  const sitemapRoutes = getSitemapRoutes();
  const pageHeads = [];
  const draftAndHiddenPages = [];

  try {
    expect(sitemapRoutes.length, 'Sitemap must contain public routes').toBeGreaterThan(0);

    for (const route of sitemapRoutes) {
      pageHeads.push(await captureHead(page, route));
    }

    for (const route of DRAFT_AND_HIDDEN_ROUTES) {
      const head = await captureHead(page, route);
      draftAndHiddenPages.push({
        route: head.route,
        title: head.title,
        robots: head.robots,
      });
    }
  } finally {
    await context.close();
  }

  const snapshot = {
    schemaVersion: 1,
    sitemapRoutes,
    pageHeads,
    draftAndHiddenPages,
  };

  if (process.env.UPDATE_HEAD_SNAPSHOT === '1') {
    fs.mkdirSync(path.dirname(FIXTURE_PATH), { recursive: true });
    fs.mkdirSync(path.dirname(ARTIFACT_PATH), { recursive: true });
    const json = JSON.stringify(snapshot, null, 2) + '\n';
    fs.writeFileSync(FIXTURE_PATH, json);
    fs.writeFileSync(ARTIFACT_PATH, json);
    await testInfo.attach('pre-refactor-head-snapshot', {
      path: ARTIFACT_PATH,
      contentType: 'application/json',
    });
    expect(pageHeads.length).toBe(sitemapRoutes.length);
    expect(draftAndHiddenPages.length).toBe(DRAFT_AND_HIDDEN_ROUTES.length);
    return;
  }

  expect(
    fs.existsSync(FIXTURE_PATH),
    'Missing baseline fixture. Generate it on the unmodified site before refactoring page heads.'
  ).toBe(true);
  const expected = JSON.parse(fs.readFileSync(FIXTURE_PATH, 'utf8'));
  expect(snapshot, 'Titles, metadata, canonicals, social tags, JSON-LD types, analytics and draft visibility changed').toEqual(expected);
});
