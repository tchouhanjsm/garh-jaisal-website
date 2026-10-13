const fs = require('node:fs');
const path = require('node:path');

const REPOSITORY_ROOT = path.resolve(__dirname, '../../..');

function getSitemapRoutes() {
  const sitemapPath = path.join(REPOSITORY_ROOT, 'sitemap.xml');
  const sitemap = fs.readFileSync(sitemapPath, 'utf8');
  const routes = [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
    .map(([, value]) => new URL(value.trim()))
    .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
    .map(url => url.pathname || '/');

  return [...new Set(routes)].sort((a, b) => {
    if (a === '/') return -1;
    if (b === '/') return 1;
    return a.localeCompare(b);
  });
}

module.exports = { getSitemapRoutes, REPOSITORY_ROOT };
