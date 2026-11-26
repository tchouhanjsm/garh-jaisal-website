const test = require('node:test');
const assert = require('node:assert/strict');
const { checkProduction, ORIGIN, PAGES } = require('../scripts/check-production-health');

function fixture(url) {
  const path = new URL(url).pathname;
  if (path === '/sitemap.xml') return '<urlset><url><loc>' + ORIGIN + '/</loc></url></urlset>';
  if (path === '/robots.txt') return 'Sitemap: ' + ORIGIN + '/sitemap.xml';
  return '<html><head><link rel="canonical" href="' + url + '"><meta name="robots" content="index,follow"></head><body><h1>Fixture</h1></body></html>';
}

test('production smoke uses only fixed public GETs with redirects disabled', async () => {
  const visited = [];
  const report = await checkProduction(async (url, options) => {
    assert.equal(new URL(url).origin, ORIGIN);
    assert.equal(options.redirect, 'manual');
    assert.equal(options.method, undefined);
    visited.push(new URL(url).pathname);
    return new Response(fixture(url));
  });
  assert.deepEqual(visited, [...PAGES, '/sitemap.xml', '/robots.txt']);
  assert.equal(report.passed, true);
});

test('production smoke fails for redirect, noindex, canonical mismatch and network error', async () => {
  const report = await checkProduction(async url => {
    const path = new URL(url).pathname;
    if (path === '/') return new Response(null, {status: 301, headers: {Location: 'https://example.test/'}});
    if (path === '/room.php') return new Response(fixture(url).replace('index,follow', 'noindex,follow'));
    if (path === '/restaurant.php') return new Response(fixture(url).replace(url, ORIGIN + '/'));
    if (path === '/contact.php') throw new Error('Synthetic network failure');
    return new Response(fixture(url));
  });
  assert.equal(report.passed, false);
  for (const path of ['/', '/room.php', '/restaurant.php', '/contact.php']) {
    assert.ok(report.results.find(row => row.path === path).issues.length);
  }
});
