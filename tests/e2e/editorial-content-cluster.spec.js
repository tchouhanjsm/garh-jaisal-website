const { test, expect } = require('@playwright/test');

test.describe('Sprint 21 editorial intent cluster', () => {
  test('blog archive publishes both new guides as discoverable cards', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial archive contract is checked once.');
    await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    const grid = page.locator('.blog-grid');
    await expect(page.locator('main h1')).toHaveCount(1);
    const itinerary = grid.locator('a[href="jaisalmer-3-day-itinerary.php"]');
    const honeymoon = grid.locator('a[href="jaisalmer-honeymoon-guide.php"]');
    await expect(itinerary).toBeVisible();
    await expect(itinerary.locator('img')).toHaveAttribute('alt', /\S+/);
    await expect(itinerary).toContainText('Jaisalmer in 3 Days');
    await expect(honeymoon).toBeVisible();
    await expect(honeymoon.locator('img')).toHaveAttribute('alt', /\S+/);
    await expect(honeymoon).toContainText('Jaisalmer Honeymoon Guide');
  });

  test('three-day itinerary links into existing enquiry and experience journeys', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Itinerary content contract is checked once.');
    await page.goto('/jaisalmer-3-day-itinerary.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('main h1')).toHaveText('Jaisalmer in 3 Days: A Flexible Fort, Heritage and Desert Itinerary');
    await expect(page.locator('#day1')).toBeVisible();
    await expect(page.locator('#day2')).toContainText('Jain temples');
    await expect(page.locator('#day3')).toContainText('return-transfer arrangement');
    await expect(page.locator('a[href="jaisalmer-2-day-itinerary.php"]')).toBeVisible();
    await expect(page.locator('#day2 a[href="city-tour.php"]')).toBeVisible();
    await expect(page.locator('#day3 a[href="car-rental.php"]')).toBeVisible();
    await expect(page.locator('.article-footer-cta a.button-primary')).toHaveAttribute('target', '_blank');
    await expect(page.locator('.article-footer-cta a.button-primary')).toHaveAttribute('rel', /noopener/);
  });

  test('honeymoon guide explains confirmable arrangements instead of a fixed package', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Honeymoon content contract is checked once.');
    await page.goto('/jaisalmer-honeymoon-guide.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('main h1')).toHaveText('Plan a Jaisalmer Honeymoon Around a Fort Stay and Desert Night');
    await expect(page.locator('#dining')).toContainText('Candlelight dinners can be arranged');
    await expect(page.locator('#dining')).toContainText('should be confirmed directly with the hotel');
    await expect(page.locator('#desert')).toContainText('king-size bed');
    await expect(page.locator('#desert')).toContainText('attached bathroom');
    await expect(page.locator('#desert a[href="sunset-camel-safari.php"]')).toBeVisible();
    await expect(page.locator('#desert a[href="sleep-under-the-stars.php"]')).toBeVisible();
    await expect(page.locator('#desert a[href="swiss-tent-safari.php"]')).toBeVisible();
    await expect(page.locator('a[href="contact.php?topic=dining"]')).toBeVisible();
    await expect(page.locator('.article-footer-cta a.button-primary')).toHaveAttribute('target', '_blank');
  });

  test('existing guides link contextually into the new content cluster', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial cross-links are checked once.');
    for (const route of ['/jaisalmer-fort-guide.php', '/desert-safari-guide.php', '/book-rooms-in-jaisalmer.php']) {
      await page.goto(route, { waitUntil: 'domcontentloaded' });
      await expect(page.locator('a[href="jaisalmer-honeymoon-guide.php"]')).toBeVisible();
    }
    await page.goto('/jaisalmer-2-day-itinerary.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('a[href="jaisalmer-3-day-itinerary.php"]')).toBeVisible();
  });

  test('new seasonal, arrival and packing guides are discoverable from the Journal', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Journal discovery is checked once.');
    await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    const grid = page.locator('.blog-grid');
    const expectedCards = [
      ['best-time-to-visit-jaisalmer.php', 'Best Time to Visit Jaisalmer'],
      ['how-to-reach-jaisalmer.php', 'How to Reach Jaisalmer'],
      ['jaisalmer-desert-safari-packing-list.php', 'What to Pack for a Desert Safari'],
    ];
    for (const [href, title] of expectedCards) {
      const card = grid.locator('a[href="' + href + '"]');
      await expect(card, href + ': visible archive card').toBeVisible();
      await expect(card.locator('img'), href + ': existing approved photo with alt text').toHaveAttribute('alt', /\S+/);
      await expect(card).toContainText(title);
    }
  });

  test('new editorial guides share metadata, official references and contextual next steps', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Guide content and metadata are checked once.');
    test.setTimeout(90_000);
    const guides = [
      {
        route: '/best-time-to-visit-jaisalmer.php',
        title: 'Best Time to Visit Jaisalmer: Choose a Season for Your Trip',
        section: '#cooler-months',
        expected: 'October through March',
      },
      {
        route: '/how-to-reach-jaisalmer.php',
        title: 'How to Reach Jaisalmer and Arrive Inside the Fort',
        section: '#fort-arrival',
        expected: 'cars cannot enter the lanes inside Jaisalmer Fort',
      },
      {
        route: '/jaisalmer-desert-safari-packing-list.php',
        title: 'What to Pack for a Jaisalmer Desert Safari',
        section: '#overnight',
        expected: 'king-size bed',
      },
    ];

    for (const guide of guides) {
      const response = await page.goto(guide.route, { waitUntil: 'domcontentloaded' });
      expect(response, guide.route + ': expected an HTTP response').not.toBeNull();
      expect(response.status(), guide.route + ': route should be public').toBeLessThan(400);
      await expect(page.locator('main h1')).toHaveText(guide.title);
      await expect(page.locator('main.thar-sunset-story--journal')).toHaveCount(1);
      await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'https://www.hotelgarhjaisal.com' + guide.route);
      await expect(page.locator(guide.section)).toContainText(guide.expected);
      await expect(page.locator('#official-sources a')).not.toHaveCount(0);
      await expect(page.locator('.article-next-card')).toHaveCount(3);
      await expect(page.locator('.article-footer-cta a.button-primary')).toBeVisible();
      await expect(page.locator('.article-content')).not.toContainText(/\[[^\]]+\]\(/);

      const outbound = page.locator('#official-sources a[target="_blank"]');
      expect(await outbound.count(), guide.route + ': external source links exist').toBeGreaterThan(0);
      for (const link of await outbound.all()) {
        await expect(link).toHaveAttribute('rel', /noopener/);
      }
    }
  });

});


test('legacy high-intent guides use the shared renderer and retain their public contracts', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Shared renderer coverage runs once on desktop-1280');
  const cases = [
    { route: '/jaisalmer-fort-guide.php', h1: 'Jaisalmer Fort Guide', facts: 2, cards: 3 },
    { route: '/desert-safari-guide.php', h1: 'Desert Safari in Jaisalmer: How to Choose an Experience', tableRows: 4, cards: 3 },
    { route: '/jaisalmer-2-day-itinerary.php', h1: 'Jaisalmer 2-Day Itinerary', subheadings: 5, cards: 3 },
    { route: '/jaisalmer-3-day-itinerary.php', h1: 'Jaisalmer in 3 Days: A Flexible Fort, Heritage and Desert Itinerary', cards: 3 },
    { route: '/jaisalmer-honeymoon-guide.php', h1: 'Plan a Jaisalmer Honeymoon Around a Fort Stay and Desert Night', cards: 3 },
  ];
  for (const entry of cases) {
    await page.goto(entry.route, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('main h1')).toHaveText(entry.h1);
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'https://www.hotelgarhjaisal.com' + entry.route);
    await expect(page.locator('.article-content > section').first()).toBeVisible();
    await expect(page.locator('#official-sources')).toBeVisible();
    await expect(page.locator('.article-next-grid a')).toHaveCount(entry.cards);
    await expect(page.locator('.article-footer-cta a.button-primary')).toHaveAttribute('target', '_blank');
    await expect(page.locator('.article-footer-cta a.button-primary')).toHaveAttribute('rel', /noopener/);
    expect(await page.locator('script[type="application/ld+json"]').textContent()).toContain('BlogPosting');
    if (entry.facts) await expect(page.locator('.article-facts .article-fact')).toHaveCount(entry.facts);
    if (entry.tableRows) await expect(page.locator('.article-table tbody tr')).toHaveCount(entry.tableRows);
    if (entry.subheadings) await expect(page.locator('.article-content h3')).toHaveCount(entry.subheadings);
  }
});
