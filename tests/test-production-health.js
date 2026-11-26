const test = require('node:test');
const assert = require('node:assert/strict');
const { checkProduction, ORIGIN, PAGES } = require('../scripts/check-production-health');

function fixture(url) {
  const path = new URL(url).pathname;
  if (path === '/sitemap.xml') return '<urlset><url><loc>' + ORIGIN + '/</loc></url></urlset>';
  if (path === '/robots.txt') return 'Sitemap: ' + ORIGIN + '/sitemap.xml';
  return '<html><head><link rel="canonical" href="' + url + '"><meta name="robots" content="index,follow"></head><body><h1>Fixture</h1></body></html>';
}

function routingFixture(url) {
  const parsed = new URL(url);
  if (parsed.protocol === 'http:' || parsed.hostname === 'hotelgarhjaisal.com') {
    return new Response(null, {status: 301, headers: {Location: ORIGIN + parsed.pathname + parsed.search}});
  }
  if (parsed.pathname === '/index.php') return new Response(null, {status: 301, headers: {Location: '/'}});
  if (parsed.pathname === '/__gj_health_missing_route_7a5d.php') return new Response(null, {status: 404});
  return null;
}

test('production smoke uses only fixed public GETs with redirects disabled', async () => {
  const visited = [];
  const report = await checkProduction(async (url, options) => {
    assert.ok(['www.hotelgarhjaisal.com', 'hotelgarhjaisal.com'].includes(new URL(url).hostname));
    assert.equal(options.redirect, 'manual');
    assert.equal(options.method, undefined);
    visited.push(url);
    return routingFixture(url) || new Response(fixture(url));
  });
  assert.deepEqual(visited, [
    ...[...PAGES, '/sitemap.xml', '/robots.txt'].map(path => ORIGIN + path),
    'http://www.hotelgarhjaisal.com/room.php?health=1',
    'https://hotelgarhjaisal.com/room.php?health=1',
    ORIGIN + '/index.php',
    ORIGIN + '/__gj_health_missing_route_7a5d.php',
  ]);
  assert.equal(report.passed, true);
});

test('production smoke fails for redirect, noindex, canonical mismatch and network error', async () => {
  const report = await checkProduction(async url => {
    const routeResponse = routingFixture(url);
    if (routeResponse) return routeResponse;
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


test('production smoke catches redirect query loss, temporary redirects and soft 404', async () => {
  const report = await checkProduction(async url => {
    if (url.startsWith('http:')) return new Response(null, {status: 301, headers: {Location: ORIGIN + '/room.php'}});
    if (url.startsWith('https://hotelgarhjaisal.com/')) return new Response(null, {status: 302, headers: {Location: ORIGIN + '/room.php?health=1'}});
    if (url.includes('__gj_health_missing_route_7a5d')) return new Response('soft 404');
    return routingFixture(url) || new Response(fixture(url));
  });
  assert.equal(report.passed, false);
  assert.ok(report.routing[0].issues.some(issue => issue.includes('query loss')));
  assert.ok(report.routing[1].issues.some(issue => issue.includes('permanent redirect')));
  assert.ok(report.routing[3].issues.some(issue => issue.includes('HTTP 404')));
});
