const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

const root = path.resolve(__dirname, '../..');
const sitemapSource = fs.readFileSync(path.join(root, 'sitemap.xml'), 'utf8');
const sitemapLocations = [...sitemapSource.matchAll(/<loc>\s*([^<]+)\s*<\/loc>/gi)]
  .map(([, value]) => value.trim())
  .map(value => new URL(value));
const sitemapPaths = sitemapLocations
  .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
  .map(url => url.pathname || '/');
const sitemapPathSet = new Set(sitemapPaths);
const editorialRoutes = fs.readdirSync(root)
  .filter(file => file.endsWith('.php'))
  .filter(file => fs.readFileSync(path.join(root, file), 'utf8').includes('include/editorial-guide-page.php'))
  .map(file => '/' + file)
  .filter(route => sitemapPathSet.has(route))
  .sort();

const productionOrigin = 'https://www.hotelgarhjaisal.com';
const weakLabels = new Set(['here', 'click here', 'read more', 'learn more', 'more', 'details', 'click to read']);
const reportDir = path.join(process.cwd(), 'artifacts', 'seo-inventory');
const reportPath = path.join(reportDir, 'editorial-release-readiness.json');

function addFinding(findings, type, route, detail) {
  findings.push({ type, route, detail });
}

function parseJsonLd(blocks, route, findings) {
  const nodes = [];
  for (let index = 0; index < blocks.length; index++) {
    try {
      const parsed = JSON.parse(blocks[index]);
      if (Array.isArray(parsed['@graph'])) nodes.push(...parsed['@graph']);
      else nodes.push(parsed);
    } catch (error) {
      addFinding(findings, 'invalid-json-ld', route, 'Structured-data block ' + (index + 1) + ' is not valid JSON.');
    }
  }
  return nodes;
}

test('editorial release gate: robots and sitemap routes remain canonical and unique', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Publication route inventory runs once.');
  const findings = [];
  const robotsResponse = await page.request.get('/robots.txt');
  if (!robotsResponse.ok()) addFinding(findings, 'robots-http-status', '/robots.txt', 'Expected HTTP 2xx; got ' + robotsResponse.status());
  const robots = await robotsResponse.text();
  const sitemapDirectives = [...robots.matchAll(/^\s*Sitemap:\s*(\S+)\s*$/gim)].map(([, value]) => value.trim());
  if (sitemapDirectives.length !== 1 || sitemapDirectives[0] !== productionOrigin + '/sitemap.xml') {
    addFinding(findings, 'robots-sitemap-directive', '/robots.txt', 'Expected one canonical sitemap directive.');
  }
  if (/^\s*Disallow:\s*\/\s*(?:#.*)?$/im.test(robots)) {
    addFinding(findings, 'robots-blocks-site', '/robots.txt', 'A blanket Disallow prevents crawling of the public site.');
  }

  const sitemapResponse = await page.request.get('/sitemap.xml');
  if (!sitemapResponse.ok()) addFinding(findings, 'sitemap-http-status', '/sitemap.xml', 'Expected HTTP 2xx; got ' + sitemapResponse.status());
  const xml = await sitemapResponse.text();
  const blocks = [...xml.matchAll(/<url\b[^>]*>(.*?)<\/url>/gsi)].map(([, value]) => value);
  const locs = [];
  const routePaths = [];
  for (const [index, block] of blocks.entries()) {
    const locMatches = [...block.matchAll(/<loc>\s*([^<]+)\s*<\/loc>/gi)].map(([, value]) => value.trim());
    const dates = [...block.matchAll(/<lastmod>\s*([^<]+)\s*<\/lastmod>/gi)].map(([, value]) => value.trim());
    if (locMatches.length !== 1) {
      addFinding(findings, 'sitemap-loc-count', '/sitemap.xml', 'Entry ' + (index + 1) + ' must have one loc.');
      continue;
    }
    if (dates.length !== 1 || !/^\d{4}-\d{2}-\d{2}$/.test(dates[0] || '') || Number.isNaN(Date.parse(dates[0] || ''))) {
      addFinding(findings, 'sitemap-lastmod', '/sitemap.xml', 'Entry ' + (index + 1) + ' must have a valid date-only lastmod.');
    }
    try {
      const url = new URL(locMatches[0]);
      locs.push(url.href);
      routePaths.push(url.pathname || '/');
      if (url.protocol !== 'https:' || url.hostname !== 'www.hotelgarhjaisal.com' || url.search || url.hash || url.port) {
        addFinding(findings, 'sitemap-noncanonical-url', locMatches[0], 'Sitemap URLs must use the canonical HTTPS host without query/fragment/port.');
      }
    } catch (error) {
      addFinding(findings, 'sitemap-invalid-url', '/sitemap.xml', 'Entry ' + (index + 1) + ' has an invalid URL.');
    }
  }
  if (new Set(locs).size !== locs.length) addFinding(findings, 'duplicate-sitemap-url', '/sitemap.xml', 'Duplicate absolute loc URLs detected.');
  if (new Set(routePaths).size !== routePaths.length) addFinding(findings, 'duplicate-sitemap-route', '/sitemap.xml', 'Duplicate route paths detected.');
  for (const route of editorialRoutes) {
    if (!routePaths.includes(route)) addFinding(findings, 'live-editorial-route-missing-from-sitemap', route, 'A rendered editorial wrapper is not discoverable in the committed sitemap.');
  }
  if (editorialRoutes.length < 12) addFinding(findings, 'editorial-route-inventory-too-small', '/sitemap.xml', 'Expected at least the 12 established live editorial guides.');

  const report = {
    generatedAt: new Date().toISOString(),
    source: 'Local PHP application rendered by Playwright; this is not a production crawl.',
    editorialRoutesChecked: editorialRoutes.length,
    sitemapRoutesChecked: routePaths.length,
    sitemapPathDuplicates: routePaths.length - new Set(routePaths).size,
    findings,
  };
  fs.mkdirSync(reportDir, { recursive: true });
  fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
  await testInfo.attach('editorial-sitemap-readiness', { path: reportPath, contentType: 'application/json' });
  expect(findings, JSON.stringify(findings, null, 2)).toEqual([]);
});

test('editorial release gate: rendered metadata, schema, links and reader anchors align', async ({ page, browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial route audit runs once at desktop width.');
  test.setTimeout(180_000);
  const findings = [];
  const inventory = [];
  const titles = new Map();
  const descriptions = new Map();
  const internalTargets = new Set();

  for (const route of editorialRoutes) {
    let response;
    try {
      response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    } catch (error) {
      addFinding(findings, 'route-navigation-failed', route, String(error));
      continue;
    }
    if (!response || response.status() !== 200) {
      addFinding(findings, 'editorial-route-http-status', route, 'Expected HTTP 200; got ' + (response ? response.status() : 'no response'));
      continue;
    }

    const rendered = await page.evaluate(() => {
      const meta = selector => [...document.querySelectorAll(selector)].map(node => node.getAttribute('content') || '');
      const attrs = selector => [...document.querySelectorAll(selector)].map(node => node.getAttribute('href') || '');
      return {
        title: document.title.trim(),
        descriptions: meta('meta[name="description"]'),
        robots: meta('meta[name="robots"]'),
        canonicals: attrs('link[rel="canonical"]'),
        ogType: meta('meta[property="og:type"]'),
        ogTitle: meta('meta[property="og:title"]'),
        ogDescription: meta('meta[property="og:description"]'),
        ogUrl: meta('meta[property="og:url"]'),
        ogImage: meta('meta[property="og:image"]'),
        twitterCard: meta('meta[name="twitter:card"]'),
        twitterTitle: meta('meta[name="twitter:title"]'),
        twitterDescription: meta('meta[name="twitter:description"]'),
        twitterImage: meta('meta[name="twitter:image"]'),
        h1: [...document.querySelectorAll('main h1')].map(node => node.textContent.trim()).filter(Boolean),
        articleVisible: Boolean(document.querySelector('article.article-content')),
        articleImageAlt: document.querySelector('article.article-content .article-image img')?.getAttribute('alt') || '',
        toc: [...document.querySelectorAll('nav.article-toc a[href^="#"]')].map(node => ({ href: node.getAttribute('href'), text: node.textContent.trim() })),
        contentIds: [...document.querySelectorAll('article.article-content [id]')].map(node => node.id),
        sourceLinks: [...document.querySelectorAll('#official-sources a[href]')].map(node => ({ href: node.getAttribute('href') || '', text: node.textContent.trim(), target: node.getAttribute('target') || '', rel: node.getAttribute('rel') || '' })),
        relatedLinks: [...document.querySelectorAll('.article-next-grid a[href], .article-topic-links a[href]')].map(node => ({ href: node.getAttribute('href') || '', text: node.textContent.trim() })),
        allMainLinks: [...document.querySelectorAll('main a[href]')].map(node => ({ href: node.getAttribute('href') || '', text: node.textContent.trim(), target: node.getAttribute('target') || '', rel: node.getAttribute('rel') || '' })),
        jsonLd: [...document.querySelectorAll('script[type="application/ld+json"]')].map(node => node.textContent || ''),
      };
    });

    const canonical = rendered.canonicals.length === 1 ? rendered.canonicals[0] : '';
    const description = rendered.descriptions.length === 1 ? rendered.descriptions[0].trim() : '';
    const titleKey = rendered.title.replace(/\s+/g, ' ').toLocaleLowerCase();
    const descriptionKey = description.replace(/\s+/g, ' ').toLocaleLowerCase();
    if (titleKey) titles.set(titleKey, [...(titles.get(titleKey) || []), route]);
    if (descriptionKey) descriptions.set(descriptionKey, [...(descriptions.get(descriptionKey) || []), route]);

    if (!rendered.title || rendered.title.length > 90) addFinding(findings, 'title-invalid', route, 'Title must be present and at most 90 characters.');
    if (rendered.descriptions.length !== 1 || description.length < 40 || description.length > 180) addFinding(findings, 'description-invalid', route, 'Expected one non-empty description between 40 and 180 characters.');
    if (rendered.h1.length !== 1) addFinding(findings, 'h1-count', route, 'Expected exactly one main H1.');
    if (!rendered.articleVisible) addFinding(findings, 'article-content-missing', route, 'Expected the shared article content region.');
    if (!rendered.articleImageAlt.trim()) addFinding(findings, 'article-image-alt-missing', route, 'The article hero image needs visible alternative text.');
    if (rendered.canonicals.length !== 1) addFinding(findings, 'canonical-count', route, 'Expected exactly one canonical link.');
    try {
      const canonicalUrl = new URL(canonical);
      if (canonicalUrl.href !== productionOrigin + route || canonicalUrl.search || canonicalUrl.hash) {
        addFinding(findings, 'canonical-mismatch', route, 'Canonical must equal the configured production URL for this route.');
      }
    } catch (error) {
      addFinding(findings, 'canonical-invalid', route, 'Canonical is missing or not an absolute URL.');
    }
    if (rendered.robots.length !== 1 || /\bnoindex\b/i.test(rendered.robots[0] || '') || !/\bindex\b/i.test(rendered.robots[0] || '')) {
      addFinding(findings, 'live-route-indexability', route, 'Sitemap-listed live guides must render indexable robots metadata.');
    }

    const social = [
      ['og:type', rendered.ogType, 'article'],
      ['og:title', rendered.ogTitle, rendered.title],
      ['og:description', rendered.ogDescription, description],
      ['og:url', rendered.ogUrl, canonical],
      ['twitter:card', rendered.twitterCard, 'summary_large_image'],
      ['twitter:title', rendered.twitterTitle, rendered.title],
      ['twitter:description', rendered.twitterDescription, description],
    ];
    for (const [field, values, expected] of social) {
      if (values.length !== 1 || (expected && values[0].trim() !== expected)) {
        addFinding(findings, 'social-metadata-' + field.replace(/[:.]/g, '-'), route, 'Expected one consistent ' + field + ' value.');
      }
    }
    for (const [field, values] of [['og:image', rendered.ogImage], ['twitter:image', rendered.twitterImage]]) {
      if (values.length !== 1) {
        addFinding(findings, 'social-image-count', route, 'Expected one ' + field + ' value.');
      } else {
        try {
          const imageUrl = new URL(values[0]);
          if (imageUrl.protocol !== 'https:' || imageUrl.hostname !== 'www.hotelgarhjaisal.com') {
            addFinding(findings, 'social-image-origin', route, field + ' must use the canonical HTTPS image origin.');
          }
        } catch (error) {
          addFinding(findings, 'social-image-invalid', route, field + ' must be an absolute URL.');
        }
      }
    }

    const graph = parseJsonLd(rendered.jsonLd, route, findings);
    const blogPosting = graph.find(node => node && (node['@type'] === 'BlogPosting' || (Array.isArray(node['@type']) && node['@type'].includes('BlogPosting'))));
    const breadcrumb = graph.find(node => node && (node['@type'] === 'BreadcrumbList' || (Array.isArray(node['@type']) && node['@type'].includes('BreadcrumbList'))));
    if (!blogPosting) {
      addFinding(findings, 'blogposting-schema-missing', route, 'Expected BlogPosting JSON-LD.');
    } else {
      if (!blogPosting.headline || !blogPosting.description || blogPosting.description !== description) {
        addFinding(findings, 'blogposting-visible-content-parity', route, 'Schema headline and description must be populated and description must match the rendered meta description.');
      }
      if (!/^\d{4}-\d{2}-\d{2}$/.test(blogPosting.datePublished || '') || Number.isNaN(Date.parse(blogPosting.datePublished || ''))) {
        addFinding(findings, 'date-published-invalid', route, 'BlogPosting datePublished must be an ISO date.');
      }
      if (!/^\d{4}-\d{2}-\d{2}$/.test(blogPosting.dateModified || '') || Number.isNaN(Date.parse(blogPosting.dateModified || ''))) {
        addFinding(findings, 'date-modified-invalid', route, 'BlogPosting dateModified must be an ISO date.');
      }
      if (Date.parse(blogPosting.datePublished || '') > Date.parse(blogPosting.dateModified || '')) {
        addFinding(findings, 'schema-date-order', route, 'dateModified must not precede datePublished.');
      }
      if (blogPosting.mainEntityOfPage?.['@id'] !== canonical + '#webpage') {
        addFinding(findings, 'blogposting-canonical-reference', route, 'mainEntityOfPage must reference the canonical WebPage entity.');
      }
    }
    if (!breadcrumb || !Array.isArray(breadcrumb.itemListElement) || breadcrumb.itemListElement.length < 2) {
      addFinding(findings, 'breadcrumb-schema-invalid', route, 'Expected BreadcrumbList with at least two items.');
    } else {
      const items = breadcrumb.itemListElement;
      if (items.some((item, index) => item.position !== index + 1 || !String(item.name || '').trim())) {
        addFinding(findings, 'breadcrumb-schema-order', route, 'Breadcrumb positions and names must be populated and sequential.');
      }
      if (items[items.length - 1].item !== canonical) {
        addFinding(findings, 'breadcrumb-canonical-reference', route, 'Last breadcrumb item must match the canonical URL.');
      }
    }

    if (!rendered.sourceLinks.some(link => /^https:\/\//i.test(link.href))) {
      addFinding(findings, 'official-source-links-missing', route, 'Expected at least one HTTPS source link.');
    }
    for (const link of rendered.sourceLinks) {
      if (!link.text) addFinding(findings, 'source-anchor-empty', route, 'Source link text must be descriptive.');
      if (link.target === '_blank' && !/\bnoopener\b/i.test(link.rel)) {
        addFinding(findings, 'external-link-rel', route, 'External links opened in a new tab must include rel=noopener.');
      }
    }
    const ids = rendered.contentIds;
    if (new Set(ids).size !== ids.length) addFinding(findings, 'duplicate-content-ids', route, 'Article content contains duplicate IDs.');
    for (const item of rendered.toc) {
      const targetId = item.href.slice(1);
      if (!item.text || !ids.includes(targetId)) addFinding(findings, 'toc-target-invalid', route, 'TOC link ' + item.href + ' has no labelled target.');
    }
    const relatedRoutes = [];
    for (const link of rendered.relatedLinks) {
      if (!link.text.trim()) addFinding(findings, 'related-anchor-empty', route, 'Related-guide link text must not be empty.');
      if (weakLabels.has(link.text.replace(/\s+/g, ' ').trim().toLocaleLowerCase())) addFinding(findings, 'related-anchor-generic', route, 'Related-guide anchor text is too generic: ' + link.text);
      if (link.href) {
        try {
          const targetUrl = new URL(link.href, productionOrigin + route);
          if (targetUrl.hostname === 'www.hotelgarhjaisal.com' && targetUrl.pathname.endsWith('.php')) {
            if (targetUrl.pathname === route) addFinding(findings, 'related-self-link', route, 'Related-guide section links to its current article.');
            relatedRoutes.push(targetUrl.pathname);
          }
        } catch (error) {
          addFinding(findings, 'related-href-invalid', route, 'Related-guide href is not a valid URL: ' + link.href);
        }
      }
    }
    if (new Set(relatedRoutes).size !== relatedRoutes.length) addFinding(findings, 'related-duplicate-destination', route, 'Related cards and discovery links repeat a route destination.');

    const serverOrigin = new URL(page.url()).origin;
    for (const link of rendered.allMainLinks) {
      if (!link.href || link.href.startsWith('#') || link.href.startsWith('mailto:') || link.href.startsWith('tel:')) continue;
      try {
        const target = new URL(link.href, page.url());
        const internal = target.origin === serverOrigin || target.hostname === 'www.hotelgarhjaisal.com';
        if (internal && /\.php$/i.test(target.pathname) && target.pathname !== route) {
          internalTargets.add(target.pathname + target.search);
        }
      } catch (error) {
        addFinding(findings, 'invalid-main-link', route, 'Malformed main-content link: ' + link.href);
      }
    }

    inventory.push({
      route,
      status: response.status(),
      title: rendered.title,
      titleLength: rendered.title.length,
      descriptionLength: description.length,
      canonical,
      robots: rendered.robots[0] || '',
      jsonLdTypes: graph.map(node => node['@type']).filter(Boolean),
      tocLinks: rendered.toc.length,
      sourceLinks: rendered.sourceLinks.length,
      relatedLinks: rendered.relatedLinks.length,
    });
  }

  for (const [title, matchingRoutes] of titles) {
    if (matchingRoutes.length > 1) addFinding(findings, 'duplicate-editorial-title', matchingRoutes.join(', '), title);
  }
  for (const [description, matchingRoutes] of descriptions) {
    if (matchingRoutes.length > 1) addFinding(findings, 'duplicate-editorial-description', matchingRoutes.join(', '), matchingRoutes.join(', '));
  }

  const brokenLinks = [];
  for (const target of internalTargets) {
    const response = await page.request.get(new URL(target, 'http://127.0.0.1:8080').href);
    if (response.status() >= 400) brokenLinks.push({ target, status: response.status() });
  }
  for (const broken of brokenLinks) {
    addFinding(findings, 'internal-route-unresolved', broken.target, 'Internal PHP reference returned HTTP ' + broken.status);
  }

  const report = {
    generatedAt: new Date().toISOString(),
    source: 'Local PHP application rendered by Playwright; not a production crawl.',
    editorialRoutesChecked: editorialRoutes.length,
    internalPhpDestinationsChecked: internalTargets.size,
    brokenInternalDestinations: brokenLinks,
    routeInventory: inventory,
    findingCount: findings.length,
    findings,
  };
  fs.mkdirSync(reportDir, { recursive: true });
  fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
  await testInfo.attach('editorial-rendered-seo-readiness', { path: reportPath, contentType: 'application/json' });
  expect(editorialRoutes.length, 'Live editorial wrappers in sitemap').toBeGreaterThanOrEqual(12);
  expect(inventory.length, 'Every live editorial wrapper should be audited').toBe(editorialRoutes.length);
  expect(findings, JSON.stringify(findings, null, 2)).toEqual([]);
});

test('editorial release gate: archive and Fort guide remain responsive and keyboard reachable', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive reader checks run once per viewport matrix.');
  const widths = [320, 390, 768, 1280, 1440];
  for (const width of widths) {
    await page.setViewportSize({ width, height: 900 });
    for (const route of ['/blog.php', '/jaisalmer-fort-guide.php']) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' should respond at ' + width + 'px').not.toBeNull();
      expect(response.status(), route + ' should remain public at ' + width + 'px').toBe(200);
      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        scroll: document.documentElement.scrollWidth,
      }));
      expect(dimensions.scroll, route + ' horizontal overflow at ' + width + 'px').toBeLessThanOrEqual(dimensions.viewport + 1);
      const visibleLinkFocus = await page.locator('main a[href]').evaluateAll(links => {
        const firstVisible = links.find(link => link.getClientRects().length > 0);
        if (!firstVisible) return false;
        firstVisible.focus();
        return document.activeElement === firstVisible;
      });
      expect(visibleLinkFocus, route + ' must retain keyboard focus at ' + width + 'px').toBe(true);
    }
  }
});
