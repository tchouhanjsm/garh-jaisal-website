const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

const root = path.join(__dirname, '../..');
const sitemap = fs.readFileSync(path.join(root, 'sitemap.xml'), 'utf8');
const sitemapRoutes = [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
  .map(([, href]) => new URL(href.trim()))
  .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
  .map(url => url.pathname || '/');

const topics = [
  ['Trip planning', 'trip-planning'],
  ['Fort & heritage', 'fort-and-heritage'],
  ['Itineraries', 'itineraries'],
  ['Desert planning', 'desert-planning'],
  ['Food & crafts', 'food-and-crafts'],
];

const editorialRoutes = [
  '/best-time-to-visit-jaisalmer.php',
  '/how-to-reach-jaisalmer.php',
  '/jaisalmer-desert-safari-packing-list.php',
  '/jaisalmer-fort-guide.php',
  '/desert-safari-guide.php',
  '/jaisalmer-2-day-itinerary.php',
  '/jaisalmer-3-day-itinerary.php',
  '/jaisalmer-honeymoon-guide.php',
  '/jaisalmer-havelis-guide.php',
  '/jaisalmer-food-guide.php',
  '/jaisalmer-beyond-the-fort-guide.php',
  '/jaisalmer-markets-and-crafts-guide.php',
];

test.describe('Editorial publication and reader journey', () => {
  test('Journal index exposes topic navigation and publication links', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial archive contract runs once.');
    const response = await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);
    await expect(page.locator('main h1')).toHaveText('Travel guides for Jaisalmer’s Fort, food and desert.');
    const topicNav = page.getByRole('navigation', { name: 'Browse guide topics' });
    await expect(topicNav).toBeVisible();
    for (const [label, anchor] of topics) {
      await expect(topicNav.getByRole('link', { name: label })).toHaveAttribute('href', '#' + anchor);
      const target = page.locator('#' + anchor);
      await expect(target).toBeVisible();
      await expect(target).toHaveClass(/blog-card/);
      await expect(target.locator(':scope > a[href]')).toHaveCount(1);
    }

    const guideCards = page.locator('.blog-grid > .blog-card');
    expect(await guideCards.count()).toBeGreaterThanOrEqual(12);
    for (const route of editorialRoutes) {
      expect(sitemapRoutes, route + ' is included in the committed sitemap').toContain(route);
      const slug = path.posix.basename(route);
      const journalLink = page.locator('.blog-grid a[href="' + slug + '"]');
      await expect(journalLink, slug + ' is linked from the Journal exactly once').toHaveCount(1);
      await expect(journalLink).toBeVisible();
    }
    const archiveDestinations = await page.locator('.blog-grid > .blog-card > a[href$=".php"]').evaluateAll(
      links => links.map(link => link.getAttribute('href'))
    );
    expect(new Set(archiveDestinations).size, 'Journal cards should not repeat route destinations').toBe(archiveDestinations.length);
    const canonicals = await page.locator('head link[rel="canonical"]').count();
    expect(canonicals).toBe(1);
    await expect(page.locator('head meta[name="description"]')).toHaveCount(1);
    await expect(page.locator('head meta[property="og:url"]')).toHaveAttribute('content', 'https://www.hotelgarhjaisal.com/blog.php');
  });

  test('article reader journey includes sources, topic discovery and accurate schema dates', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Article templates are checked once.');
    const articleRoutes = sitemapRoutes.filter(route =>
      /\/(?:jaisalmer-[a-z0-9-]+|desert-safari-guide|best-time-to-visit-jaisalmer|how-to-reach-jaisalmer)\.php$/.test(route)
      && !route.endsWith('/jaisalmer-fort-guide.php')
    );
    expect(articleRoutes.length).toBeGreaterThanOrEqual(10);
    for (const route of articleRoutes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' has an HTTP response').not.toBeNull();
      expect(response.status(), route + ' is a public route').toBe(200);
      await expect(page.locator('main h1')).toHaveCount(1);
      await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'https://www.hotelgarhjaisal.com' + route);
      await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', /\S+/);
      await expect(page.locator('meta[property="og:type"]')).toHaveAttribute('content', 'article');
      await expect(page.locator('article.article-content')).toBeVisible();
      await expect(page.locator('article.article-content .article-image img')).toHaveAttribute('alt', /\S+/);
      await expect(page.locator('#official-sources')).toBeVisible();
      await expect(page.locator('#official-sources a[href^="https://"]')).not.toHaveCount(0);
      const discoveryNav = page.getByRole('navigation', { name: 'More Jaisalmer travel guides' });
      await expect(discoveryNav).toBeVisible();
      const discoveryLinks = discoveryNav.locator('a[href]');
      expect(await discoveryLinks.count(), route + ' exposes at least one additional guide').toBeGreaterThan(0);
      const currentTopic = await discoveryNav.getAttribute('data-topic');
      const candidateTopics = await discoveryLinks.evaluateAll(links => links.map(link => link.getAttribute('data-topic')));
      let crossTopicSeen = false;
      for (const candidateTopic of candidateTopics) {
        if (candidateTopic !== currentTopic) crossTopicSeen = true;
        else expect(crossTopicSeen, route + ' prioritises same-topic links before cross-topic links').toBe(false);
      }
      for (const link of await discoveryLinks.all()) {
        const href = await link.getAttribute('href');
        expect(href, route + ' discovery link uses a destination').toBeTruthy();
        await expect(page.locator('a[href="' + href + '"]'), route + ' avoids duplicate navigation to ' + href).toHaveCount(1);
      }
      await expect(page.locator('.article-next-card')).toHaveCount(3);
      await expect(page.locator('.article-footer-cta')).toBeVisible();

      const graphBlocks = await page.locator('script[type="application/ld+json"]').allTextContents();
      const graph = graphBlocks.map(text => JSON.parse(text)).flatMap(value => value['@graph'] || []);
      const article = graph.find(node => node['@type'] === 'BlogPosting');
      expect(article, route + ' emits BlogPosting JSON-LD').toBeTruthy();
      expect(article.datePublished).toMatch(/^\d{4}-\d{2}-\d{2}$/);
      expect(article.dateModified).toMatch(/^\d{4}-\d{2}-\d{2}$/);
      expect(article.mainEntityOfPage['@id']).toBe('https://www.hotelgarhjaisal.com' + route + '#webpage');
      const breadcrumb = graph.find(node => node['@type'] === 'BreadcrumbList');
      expect(breadcrumb, route + ' emits BreadcrumbList JSON-LD').toBeTruthy();
      expect(breadcrumb.itemListElement.length).toBeGreaterThanOrEqual(2);
    }
  });

  test('new topic discovery stays usable across mobile, tablet and desktop widths', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive topic layouts run once per viewport matrix.');
    const widths = [320, 390, 768, 1280, 1440];
    for (const width of widths) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        scroll: document.documentElement.scrollWidth,
      }));
      expect(dimensions.scroll, 'Journal horizontal overflow at ' + width + 'px').toBeLessThanOrEqual(dimensions.viewport + 1);
      await expect(page.getByRole('navigation', { name: 'Browse guide topics' })).toBeVisible();
      await expect(page.locator('.blog-card').first()).toBeVisible();

      await page.goto('/jaisalmer-fort-guide.php', { waitUntil: 'domcontentloaded' });
      const articleDimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        scroll: document.documentElement.scrollWidth,
      }));
      expect(articleDimensions.scroll, 'Article horizontal overflow at ' + width + 'px').toBeLessThanOrEqual(articleDimensions.viewport + 1);
      await expect(page.getByRole('navigation', { name: 'More Jaisalmer travel guides' })).toBeVisible();
      const focused = await page.locator('body').evaluate(el => {
        const links = [...el.querySelectorAll('a[href]')].filter(a => a.offsetParent !== null);
        links[0]?.focus();
        return document.activeElement === links[0];
      });
      expect(focused, 'Keyboard focus should reach a visible article link at ' + width + 'px').toBe(true);
    }
  });
});
