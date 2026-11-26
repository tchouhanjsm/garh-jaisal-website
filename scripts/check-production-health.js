'use strict';

// Read-only production smoke: fixed public URLs; no credentials, forms,
// booking-engine requests, analytics calls or redirect following.
const ORIGIN = 'https://www.hotelgarhjaisal.com';
const PAGES = ['/', '/room.php', '/restaurant.php', '/pack.php', '/car-rental.php', '/contact.php'];
async function checkProduction(fetcher = fetch) {
  const results = [];
  for (const path of [...PAGES, '/sitemap.xml', '/robots.txt']) {
    const result = { path, status: null, issues: [] };
    try {
      const response = await fetcher(ORIGIN + path, {
        redirect: 'manual', signal: AbortSignal.timeout(15000),
        headers: { 'User-Agent': 'GarhJaisal-ReadOnly-HealthCheck/1.0' },
      });
      result.status = response.status;
      if (response.status !== 200) {
        result.issues.push('Expected direct HTTP 200');
        if (response.body) await response.body.cancel();
      } else {
        const text = await response.text();
        if (PAGES.includes(path)) {
          const expected = ORIGIN + path;
          const canonical = [...text.matchAll(/<link\b[^>]*>/gi)].filter(([tag]) => /\brel\s*=\s*["']canonical["']/i.test(tag));
          if (canonical.length !== 1 || !canonical[0][0].includes('href="' + expected + '"')) result.issues.push('Missing or incorrect canonical');
          if (!/<h1\b/i.test(text)) result.issues.push('Missing H1');
          if (/<meta\b[^>]*\bname=["']robots["'][^>]*\bcontent=["'][^"']*noindex/i.test(text)) result.issues.push('Unexpected noindex');
        } else if (path === '/sitemap.xml' && !text.includes('<loc>' + ORIGIN + '/</loc>')) {
          result.issues.push('Production homepage absent from sitemap');
        } else if (path === '/robots.txt' && !text.includes(ORIGIN + '/sitemap.xml')) {
          result.issues.push('Production sitemap reference missing');
        }
      }
    } catch {
      result.issues.push('Network or response error');
    }
    results.push(result);
  }
  // Separate transport/routing contracts from content checks. Never follow a
  // Location header or send data to a destination discovered in a response.
  const routing = [];
  const expectedCanonical = ORIGIN + '/room.php?health=1';
  const redirectCases = [
    ['http://www.hotelgarhjaisal.com/room.php?health=1', expectedCanonical],
    ['https://hotelgarhjaisal.com/room.php?health=1', expectedCanonical],
    [ORIGIN + '/index.php', ORIGIN + '/'],
  ];
  for (const [url, expected] of redirectCases) {
    const result = { url, status: null, issues: [] };
    try {
      const response = await fetcher(url, {
        redirect: 'manual', signal: AbortSignal.timeout(15000),
        headers: { 'User-Agent': 'GarhJaisal-ReadOnly-HealthCheck/1.0' },
      });
      result.status = response.status;
      const location = response.headers.get('location');
      if (![301, 308].includes(response.status)) result.issues.push('Expected permanent redirect');
      if (!location || new URL(location, url).href !== expected) result.issues.push('Unexpected canonical redirect destination or query loss');
      if (response.body) await response.body.cancel();
    } catch {
      result.issues.push('Network or invalid redirect response');
    }
    routing.push(result);
  }
  const missing = { url: ORIGIN + '/__gj_health_missing_route_7a5d.php', status: null, issues: [] };
  try {
    const response = await fetcher(missing.url, {
      redirect: 'manual', signal: AbortSignal.timeout(15000),
      headers: { 'User-Agent': 'GarhJaisal-ReadOnly-HealthCheck/1.0' },
    });
    missing.status = response.status;
    if (response.status !== 404) missing.issues.push('Unknown route must return real HTTP 404');
    if (response.body) await response.body.cancel();
  } catch {
    missing.issues.push('Network or response error');
  }
  routing.push(missing);
  return { checked_at: new Date().toISOString(), results, routing, passed: [...results, ...routing].every(result => result.issues.length === 0) };
}
if (require.main === module) {
  checkProduction().then(report => {
    console.log(JSON.stringify(report, null, 2));
    process.exitCode = report.passed ? 0 : 1;
  }).catch(() => { process.exitCode = 1; });
}
module.exports = { checkProduction, ORIGIN, PAGES };
