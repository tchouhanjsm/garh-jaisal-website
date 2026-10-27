const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

test('sitemap gives every published URL a valid Git-derived lastmod date', async ({}, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Sitemap metadata check runs once');
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  const entries = [...sitemap.matchAll(/<url>\s*<loc>([^<]+)<\/loc>\s*<lastmod>([^<]+)<\/lastmod>\s*<priority>([^<]+)<\/priority>\s*<\/url>/gi)]
    .map(([, loc, lastmod, priority]) => ({ loc, lastmod, priority }));

  expect(entries.length, 'All published sitemap entries must be parsed').toBe(37);
  expect(entries.some(entry => entry.loc.endsWith('/catalogue.php')), 'Trip catalogue must be indexable').toBe(true);
  for (const route of [
    '/best-time-to-visit-jaisalmer.php',
    '/how-to-reach-jaisalmer.php',
    '/jaisalmer-desert-safari-packing-list.php',
    '/jaisalmer-havelis-guide.php',
    '/jaisalmer-food-guide.php',
    '/jaisalmer-beyond-the-fort-guide.php',
    '/jaisalmer-markets-and-crafts-guide.php',
    '/custom-itinerary.php',
  ]) {
    expect(entries.some(entry => entry.loc.endsWith(route)), route + ' must be indexable').toBe(true);
  }
  for (const entry of entries) {
    expect(entry.lastmod, 'lastmod must be an ISO calendar date for ' + entry.loc).toMatch(/^\d{4}-\d{2}-\d{2}$/);
    const date = new Date(entry.lastmod + 'T00:00:00Z');
    expect(Number.isNaN(date.getTime()), 'lastmod must parse for ' + entry.loc).toBe(false);
    expect(date.toISOString().slice(0, 10), 'lastmod must be a real calendar date for ' + entry.loc).toBe(entry.lastmod);
    expect(Number(entry.priority), 'Existing priority must remain numeric for ' + entry.loc).toBeGreaterThanOrEqual(0);
  }
  expect(sitemap, 'lastmod should be generated from Git history and checked in').not.toContain('<lastmod></lastmod>');
});
