const { test, expect } = require('@playwright/test');

const guides = [
  {
    route: '/jaisalmer-havelis-guide.php',
    title: 'Jaisalmer Havelis: A Guide to Sandstone Architecture',
    card: 'Jaisalmer Havelis: Sandstone Architecture',
    section: '#patwon-ki-haveli',
    content: 'cluster of five havelis',
  },
  {
    route: '/jaisalmer-food-guide.php',
    title: 'A Food Lover’s Guide to Jaisalmer and Rajasthan',
    card: 'A Food Lover’s Guide to Jaisalmer',
    section: '#hearty-dishes',
    content: 'Dal baati choorma',
  },
  {
    route: '/jaisalmer-beyond-the-fort-guide.php',
    title: 'Beyond Jaisalmer Fort: Places to Explore Around the Golden City',
    card: 'Beyond Jaisalmer Fort',
    section: '#gadisar-lake',
    content: 'traditional water source for the city',
  },
  {
    route: '/jaisalmer-markets-and-crafts-guide.php',
    title: 'Jaisalmer Markets and Crafts: What to Look For',
    card: 'Jaisalmer Markets and Crafts',
    section: '#shop-thoughtfully',
    content: 'Treat vendors as people',
  },
];

test.describe('Sprint 28 source-backed Journal expansion', () => {
  test('Journal surfaces every new guide as an accessible card', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Journal archive assertions run once.');
    await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('main h1')).toHaveText('Travel guides for Jaisalmer’s Fort, food and desert.');

    const grid = page.locator('.blog-grid');
    for (const guide of guides) {
      const card = grid.locator('a[href="' + guide.route.slice(1) + '"]');
      await expect(card, guide.route + ': card is visible').toBeVisible();
      await expect(card).toContainText(guide.card);
      await expect(card.locator('img')).toHaveAttribute('alt', /\S+/);
    }
  });

  test('each new guide preserves metadata, source links and contextual next steps', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial page assertions run once.');
    for (const guide of guides) {
      const response = await page.goto(guide.route, { waitUntil: 'domcontentloaded' });
      expect(response, guide.route + ': expected HTTP response').not.toBeNull();
      expect(response.status(), guide.route + ': route should be public').toBeLessThan(400);

      await expect(page.locator('main h1')).toHaveText(guide.title);
      await expect(page.locator('main.thar-sunset-story--journal')).toHaveCount(1);
      await expect(page.locator('link[rel="canonical"]'))
        .toHaveAttribute('href', 'https://www.hotelgarhjaisal.com' + guide.route);
      await expect(page.locator(guide.section)).toContainText(guide.content);
      await expect(page.locator('#official-sources a')).not.toHaveCount(0);
      await expect(page.locator('.article-next-card')).toHaveCount(3);
      await expect(page.locator('.article-footer-cta a.button-primary')).toBeVisible();
      await expect(page.locator('.article-footer-cta a.button-secondary')).toBeVisible();
      await expect(page.locator('.article-content')).not.toContainText(/\[[^\]]+\]\(/);
      expect(await page.locator('script[type="application/ld+json"]').allTextContents())
        .toEqual(expect.arrayContaining([expect.stringContaining('BlogPosting')]));

      const externalSources = page.locator('#official-sources a[target="_blank"]');
      expect(await externalSources.count(), guide.route + ': official external sources exist').toBeGreaterThan(0);
      for (const link of await externalSources.all()) {
        await expect(link).toHaveAttribute('rel', /noopener/);
        await expect(link).toHaveAttribute('href', /^https:\/\//);
      }
    }
  });

  test('new guides do not introduce unconfirmed operational promises', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Operational-claim guard runs once.');
    const cases = [
      {
        route: '/jaisalmer-food-guide.php',
        checks: [/current menu/i, /prices/i, /not a table reservation/i],
      },
      {
        route: '/jaisalmer-beyond-the-fort-guide.php',
        checks: [/confirm current access/i, /cannot be guaranteed/i, /confirm.*price/i],
      },
      {
        route: '/jaisalmer-markets-and-crafts-guide.php',
        checks: [/not independent certification/i, /written description/i, /return or exchange terms/i],
      },
      {
        route: '/jaisalmer-havelis-guide.php',
        checks: [/confirm.*access/i, /not a confirmed tour booking/i],
      },
    ];
    for (const entry of cases) {
      await page.goto(entry.route, { waitUntil: 'domcontentloaded' });
      const body = (await page.locator('main').innerText()).replace(/\s+/g, ' ');
      for (const check of entry.checks) expect(body).toMatch(check);
    }
  });
});
