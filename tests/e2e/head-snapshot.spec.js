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

// These articles were added after the original frozen head baseline. They remain
// covered by rendered SEO metadata tests and editorial-content-cluster.spec.js.
const ROUTES_ADDED_AFTER_FROZEN_HEAD_BASELINE = [
  '/jaisalmer-3-day-itinerary.php',
  '/jaisalmer-honeymoon-guide.php',
  '/catalogue.php',
];

async function captureHead(page, route) {
  const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
  expect(response, 'No HTTP response for ' + route).not.toBeNull();
  expect(response.status(), 'Unexpected status for ' + route).toBeLessThan(400);

  const snapshot = await page.evaluate(({ route }) => {
    // Migrated pages now emit the shared Hotel and WebSite graph roots from
    // page-head.php. Keep the frozen fixture focused on the page-specific
    // schema from before migration; the next test asserts these shared nodes.
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
    const siteBase = new URL('/', document.querySelector('link[rel="canonical"]')?.href || window.location.href).href;
    const ignoreSharedRootsForBaseline = [
      '/about.php',
      '/room.php',
      '/contact.php',
      '/direction.php',
      '/gallery.php',
      '/pack.php',
      '/desert-safari.php',
      '/car-tours.php',
      '/guided-jaisalmer-tour.php',
      '/camping.php',
      '/car-rental.php',
      '/city-tour.php',
      '/book-rooms-in-jaisalmer.php',
      '/roof.php',
      '/blog.php',
      '/jaisalmer-fort-guide.php',
      '/desert-safari-guide.php',
      '/jaisalmer-2-day-itinerary.php',
      '/cultural-safari.php',
      '/full-day-jaisalmer-tour.php',
      '/half-day-jaisalmer-tour.php',
      '/sleep-under-the-stars.php',
      '/sunset-camel-safari.php',
      '/swiss-tent-safari.php',
    ].includes(route);
    const sharedRootIds = new Set([siteBase + '#hotel', siteBase + '#website']);
    let invalidJsonLdScripts = 0;
    const visitJsonLd = value => {
      if (Array.isArray(value)) {
        value.forEach(visitJsonLd);
        return;
      }
      if (!value || typeof value !== 'object') {
        return;
      }
      if (ignoreSharedRootsForBaseline && sharedRootIds.has(value['@id'])) {
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
  }, { route });

  return { route, ...snapshot };
}

function compactSnapshot(snapshot) {
  const strings = [];
  const stringIndexes = new Map();
  const intern = value => {
    if (value === null || value === undefined) return null;
    if (typeof value !== 'string') {
      throw new TypeError('Head snapshot string table only accepts strings or null.');
    }
    if (!stringIndexes.has(value)) {
      stringIndexes.set(value, strings.length);
      strings.push(value);
    }
    return stringIndexes.get(value);
  };

  return {
    schemaVersion: 2,
    strings,
    sitemapRoutes: snapshot.sitemapRoutes.map(intern),
    pageHeads: snapshot.pageHeads.map(head => [
      intern(head.route),
      intern(head.title),
      intern(head.description),
      intern(head.canonical),
      intern(head.robots),
      head.social.map(tag => [intern(tag.key), intern(tag.content)]),
      head.jsonLdTypes.map(intern),
      head.invalidJsonLdScripts,
      head.gtagScriptCount,
      head.gtagConfigIds.map(intern),
    ]),
    draftAndHiddenPages: snapshot.draftAndHiddenPages.map(page => [
      intern(page.route),
      intern(page.title),
      intern(page.robots),
    ]),
  };
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

  expect(
    fs.existsSync(FIXTURE_PATH),
    'Missing committed pre-refactor fixture; capture it before any shared-head migration.'
  ).toBe(true);
  const expected = JSON.parse(fs.readFileSync(FIXTURE_PATH, 'utf8'));
  const frozenBaseline = {
    ...snapshot,
    sitemapRoutes: snapshot.sitemapRoutes.filter(route => !ROUTES_ADDED_AFTER_FROZEN_HEAD_BASELINE.includes(route)),
    pageHeads: snapshot.pageHeads.filter(head => !ROUTES_ADDED_AFTER_FROZEN_HEAD_BASELINE.includes(head.route)),
  };
  const comparable = compactSnapshot(frozenBaseline);
  expect(
    comparable,
    'Existing route titles, metadata, canonicals, social tags, JSON-LD types, analytics and draft visibility changed'
  ).toEqual(expected);

  // Keep full current output as an artifact; only the fixture comparison omits
  // new articles whose metadata and visible-content contracts have their own tests.
  const fullComparable = compactSnapshot(snapshot);
  fs.mkdirSync(path.dirname(ARTIFACT_PATH), { recursive: true });
  const json = JSON.stringify(fullComparable, null, 2) + '\n';
  fs.writeFileSync(ARTIFACT_PATH, json);
  await testInfo.attach('current-head-snapshot', {
    path: ARTIFACT_PATH,
    contentType: 'application/json',
  });
});

test('migrated page heads contain one shared Hotel and WebSite schema node', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Shared schema is verified once on desktop-1280');
  test.setTimeout(120_000);

  const routes = getSitemapRoutes();
  const context = await browser.newContext({
    baseURL: BASE_URL,
    viewport: { width: 1280, height: 800 },
    deviceScaleFactor: 1,
  });
  await context.route('https://www.googletagmanager.com/**', route => route.abort());
  const page = await context.newPage();

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, 'No HTTP response for ' + route).not.toBeNull();
      expect(response.status(), 'Unexpected status for ' + route).toBeLessThan(400);

      const entities = await page.evaluate(() => {
        const base = new URL('/', document.querySelector('link[rel="canonical"]')?.href || window.location.href).href;
        const hotelId = base + '#hotel';
        const websiteId = base + '#website';
        const hotel = [];
        const website = [];
        const visit = value => {
          if (Array.isArray(value)) {
            value.forEach(visit);
            return;
          }
          if (!value || typeof value !== 'object') return;

          if (value['@id'] === hotelId && value['@type'] === 'Hotel') hotel.push(value);
          if (value['@id'] === websiteId && value['@type'] === 'WebSite') website.push(value);
          Object.values(value).forEach(visit);
        };

        for (const script of document.querySelectorAll('script[type="application/ld+json"]')) {
          try {
            visit(JSON.parse(script.textContent || 'null'));
          } catch {
            // The snapshot regression test separately reports invalid JSON-LD.
          }
        }
        return { base, hotel, website };
      });

      expect(entities.hotel, route + ': exactly one shared Hotel node').toHaveLength(1);
      expect(entities.website, route + ': exactly one shared WebSite node').toHaveLength(1);
      expect(entities.hotel[0]).toMatchObject({
        '@type': 'Hotel',
        '@id': entities.base + '#hotel',
        name: 'Hotel Garh Jaisal Haveli',
        url: entities.base,
        telephone: '+91-7727047567',
        image: [
          entities.base + 'img/v2/facade.jpg',
          entities.base + 'img/5.webp',
        ],
        address: {
          '@type': 'PostalAddress',
          streetAddress: 'Fort Kotari Para, Inside Jaisalmer Fort',
          addressLocality: 'Jaisalmer',
          addressRegion: 'Rajasthan',
          postalCode: '345001',
          addressCountry: 'IN',
        },
      });
      expect(entities.website[0]).toMatchObject({
        '@type': 'WebSite',
        '@id': entities.base + '#website',
        url: entities.base,
        name: 'Garh Jaisal Haveli',
        publisher: { '@id': entities.base + '#hotel' },
      });
    }
  } finally {
    await context.close();
  }
});
