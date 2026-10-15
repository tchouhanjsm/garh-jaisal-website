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
    await expect(page.locator('a[href="car-rental.php"]')).toBeVisible();
    await expect(page.locator('a.button-primary')).toHaveAttribute('target', '_blank');
    await expect(page.locator('a.button-primary')).toHaveAttribute('rel', /noopener/);
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
    await expect(page.locator('a.button-primary')).toHaveAttribute('target', '_blank');
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
});
