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

      const robots = (await page.locator('head meta[name="robots"]').getAttribute('content')) || '';
      expect(robots.toLowerCase(), 'Sitemap routes must not be explicitly noindex').not.toContain('noindex');

      const structuredData = await page.locator('script[type="application/ld+json"]').allTextContents();
      for (const [index, source] of structuredData.entries()) {
        expect(() => JSON.parse(source), `Invalid JSON-LD block ${index + 1} on ${route}`).not.toThrow();
      }
    });
  }
});

test('experience hubs render valid structured data and tour breadcrumbs', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Experience architecture checks run once');
  for (const route of ['/pack.php', '/desert-safari.php', '/guided-jaisalmer-tour.php', '/car-tours.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator('main h1')).toHaveCount(1);
    if (route !== '/pack.php') {
      await expect(page.locator('nav[aria-label="Breadcrumb"]')).toBeVisible();
    }
    const blocks = await page.locator('script[type="application/ld+json"]').allTextContents();
    for (const source of blocks) {
      const parsed = JSON.parse(source);
      const graph = Array.isArray(parsed['@graph']) ? parsed['@graph'] : [];
      const breadcrumb = graph.find(node => node['@type'] === 'BreadcrumbList');
      if (route !== '/pack.php') {
        expect(breadcrumb, `Expected BreadcrumbList structured data on ${route}`).toBeTruthy();
      }
      if (breadcrumb) {
        expect(breadcrumb.itemListElement.length).toBeGreaterThanOrEqual(2);
        expect(breadcrumb.itemListElement[breadcrumb.itemListElement.length - 1].name).toBeTruthy();
      }
    }
  }
});

test('Packages hub presents two clear, responsive experience categories', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Category architecture checks run once');
  const response = await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await expect(page.locator('#experience-categories')).toBeVisible();
  const categories = page.locator('#experience-categories .package-category-card');
  await expect(categories).toHaveCount(2);
  await expect(categories.nth(0)).toContainText('Explore the Thar Desert');
  await expect(categories.nth(0).locator('a')).toHaveAttribute('href', 'desert-safari.php');
  await expect(categories.nth(1)).toContainText('Discover Jaisalmer');
  await expect(categories.nth(1).locator('a')).toHaveAttribute('href', 'car-tours.php');

  const heroLink = page.locator('.package-hero-content a[href="#experience-categories"]');
  await expect(heroLink).toContainText('Explore tour categories');
  await expect(page.locator('#experience-categories')).toHaveCSS('scroll-margin-top', '96px');
});


test('Packages hub keeps a concise category-first discovery sequence', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Packages layout sequence checks run once');
  const response = await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await expect(page.locator('.packages-intro')).toHaveCount(0);
  await expect(page.locator('#experience-categories')).toBeVisible();
  await expect(page.locator('#desert-options')).toBeVisible();
  await expect(page.locator('#city-options')).toBeVisible();
  await expect(page.locator('#plan-by-time')).toBeVisible();
  await expect(page.locator('.packages-cta')).toBeVisible();

  const sequence = await page.locator('main > section').evaluateAll(sections =>
    sections.map(section => section.id || section.classList[0])
  );
  const positions = ['experience-categories', 'desert-options', 'city-options', 'plan-by-time']
    .map(id => sequence.indexOf(id));
  expect(positions.every(position => position >= 0)).toBeTruthy();
  expect(positions).toEqual([...positions].sort((a, b) => a - b));

  const categoryLinks = page.locator('#experience-categories .package-category-link');
  await expect(categoryLinks).toHaveCount(2);
  await expect(categoryLinks.nth(0)).toHaveAttribute('href', 'desert-safari.php');
  await expect(categoryLinks.nth(1)).toHaveAttribute('href', 'car-tours.php');
  await expect(page.locator('#desert-options .experience-card').first()).toBeVisible();
  await expect(page.locator('#city-options .experience-card').first()).toBeVisible();
});

test('home page exposes clear primary discovery paths', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Home discovery checks run once');
  const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  const discovery = page.locator('.home-discovery');
  await expect(discovery).toBeVisible();
  await expect(discovery.locator('a')).toHaveCount(3);
  await expect(discovery.locator('a[href="room.php"]')).toContainText('Stay inside the Fort');
  await expect(discovery.locator('a[href="pack.php"]')).toContainText('Plan an experience');
  await expect(discovery.locator('a[href="roof.php"]')).toContainText('Dine on the rooftop');
});

test('home page surfaces fort arrival constraints and keeps key discovery sections addressable', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Homepage content architecture checks run once');
  const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const arrivalNote = page.locator('.home-arrival-note');
  await expect(arrivalNote).toBeVisible();
  await expect(arrivalNote.getByRole('heading', { name: 'Arriving inside the fort' })).toBeVisible();
  await expect(arrivalNote).toContainText('Vehicles cannot enter Jaisalmer Fort');
  await expect(arrivalNote).toContainText('7:00 AM–10:30 PM');
  await expect(arrivalNote.getByRole('link', { name: 'Read the arrival guide' })).toHaveAttribute('href', 'direction.php');

  for (const [id, heading] of [
    ['rooms-preview', 'Rooms shaped by Jaisalmer.'],
    ['rooftop-dining', 'Meals with the fort around you.'],
    ['desert-experiences', 'From the fort to the Thar.'],
    ['arrival-guide', 'Arriving at the fort is part of the experience.'],
    ['property-gallery', 'A glimpse of Garh Jaisal and its surroundings.'],
  ]) {
    const section = page.locator(`#${id}`);
    await expect(section, `Expected homepage section #${id}`).toHaveCount(1);
    await expect(section.getByRole('heading', { name: heading })).toBeVisible();
  }

  const bookingLink = page.locator('.hero-content a.button-primary');
  await expect(bookingLink).toBeVisible();
  await expect(bookingLink).toHaveAttribute('href', /\S/);
  await expect(bookingLink).toHaveAttribute('target', '_blank');
  await expect(bookingLink).toHaveAttribute('rel', /noopener/);
});

test('rooms page floor navigation targets each rendered room section', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Room floor navigation checks run once');
  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  const nav = page.locator('.rooms-floor-nav');
  await expect(nav).toBeVisible();
  const links = nav.locator('.rooms-floor-nav-links a');
  const count = await links.count();
  expect(count).toBeGreaterThan(0);
  for (let i = 0; i < count; i += 1) {
    const href = await links.nth(i).getAttribute('href');
    expect(href).toMatch(/^#rooms-[a-z0-9-]+$/);
    await expect(page.locator(href)).toHaveCount(1);
  }
  await expect(nav.locator('.rooms-floor-nav-book')).toHaveAttribute('target', '_blank');
  await expect(nav.locator('.rooms-floor-nav-book')).toHaveAttribute('rel', /noopener/);
});

test('Car & City discovery paths remain connected without retiring legacy URLs', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Information architecture checks run once');
  const cases = [
    {
      route: '/car-tours.php',
      links: [
        ['View guided city tours', '/guided-jaisalmer-tour.php'],
        ['Explore the Fort walk', '/city-tour.php'],
        ['Ask about car arrangements', '/car-rental.php'],
      ],
    },
    {
      route: '/car-rental.php',
      links: [['Car & City Tours', '/car-tours.php']],
    },
    {
      route: '/city-tour.php',
      links: [['Car & City Tours', '/car-tours.php']],
    },
    {
      route: '/camping.php',
      links: [['Desert Tours', '/desert-safari.php']],
    },
    {
      route: '/desert-safari.php',
      links: [['camping', '/camping.php']],
    },
  ];

  for (const item of cases) {
    const response = await page.goto(item.route, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for ${item.route}`).not.toBeNull();
    expect(response.status(), `Unexpected HTTP status for ${item.route}`).toBeLessThan(400);

    for (const [label, href] of item.links) {
      const matchingLinks = await page.locator('main a[href]').evaluateAll((anchors, expected) =>
        anchors.filter(anchor => {
          const target = new URL(anchor.getAttribute('href'), document.baseURI);
          return target.pathname === expected.href && anchor.textContent.includes(expected.label);
        }).length,
        { href, label }
      );
      expect(
        matchingLinks,
        `Expected ${item.route} to link to ${label} (${href})`
      ).toBeGreaterThan(0);
    }
  }
});

test('draft tour entries remain absent from public navigation and footer', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Publication boundary checks run once');
  for (const route of ['/', '/pack.php', '/car-tours.php', '/desert-safari.php', '/guided-jaisalmer-tour.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator('header a[href="longewala-tour.php"], footer a[href="longewala-tour.php"]')).toHaveCount(0);
    await expect(page.locator('header a[href="rajasthan-tours.php"], footer a[href="rajasthan-tours.php"]')).toHaveCount(0);
  }
});

test('draft tour pages are noindex and excluded from the sitemap', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Publication status checks run once');
  const sitemapRoutes = getSitemapEntries();
  for (const route of ['/longewala-tour.php', '/rajasthan-tours.php']) {
    expect(sitemapRoutes, `${route} must stay out of sitemap.xml until approved`).not.toContain(route);
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for draft ${route}`).not.toBeNull();
    expect(response.status(), `Draft route ${route} should render for owner review`).toBeLessThan(400);
    await expect(page.locator('head meta[name="robots"]')).toHaveAttribute('content', /noindex\s*,\s*follow/i);
    await expect(page.locator('main h1')).toContainText(/draft|confirm/i);
  }
});

test('hidden overlap pages stay out of the sitemap and public discovery until the owner decides', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Publication status checks run once');
  const hiddenRoute = '/explore-the-desert.php';
  expect(getSitemapEntries(), `${hiddenRoute} must stay out of sitemap.xml while hidden`).not.toContain(hiddenRoute);

  for (const route of ['/desert-safari.php', '/pack.php', '/sunset-camel-safari.php']) {
    await page.goto(route, { waitUntil: 'domcontentloaded' });
    const hiddenLinks = await page.locator('a[href]').evaluateAll((anchors, expected) =>
      anchors.filter(anchor => new URL(anchor.getAttribute('href'), document.baseURI).pathname === expected).length,
      hiddenRoute
    );
    expect(hiddenLinks, `${route} must not link to the hidden page ${hiddenRoute}`).toBe(0);
  }

  const response = await page.goto(hiddenRoute, { waitUntil: 'domcontentloaded' });
  expect(response, `No HTTP response for hidden ${hiddenRoute}`).not.toBeNull();
  expect(response.status(), 'Hidden page keeps its URL (no 404, no redirect) until reviewed').toBeLessThan(400);
  await expect(page.locator('head meta[name="robots"]')).toHaveAttribute('content', /noindex\s*,\s*follow/i);
});
