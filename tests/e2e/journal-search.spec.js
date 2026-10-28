const { test, expect } = require('@playwright/test');

const desktopProject = 'desktop-1280';
const mobileProjects = new Set(['mobile-320', 'mobile-390']);

test.describe('Journal search and topic filtering', () => {
  test('search, topic intersection, recovery and Fort-guide prominence work together', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== desktopProject, 'Full Journal filter contract runs once at desktop width.');
    const response = await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);

    const form = page.getByRole('search', { name: 'Search Jaisalmer travel guides' });
    await expect(form).toBeVisible();
    const cards = page.locator('[data-journal-grid] > [data-journal-card]');
    const total = await cards.count();
    expect(total).toBeGreaterThanOrEqual(13);
    await expect(page.locator('[data-journal-results]')).toHaveText('Showing all ' + total + ' results.');
    await expect(cards.first().locator('a')).toHaveAttribute('href', 'jaisalmer-fort-guide.php');
    await expect(cards.first()).toHaveClass(/blog-card--featured/);

    const search = page.getByRole('searchbox', { name: 'Search guides' });
    const topic = page.getByLabel('Filter by topic');
    await search.fill('  honeymoon  ');
    const honeymoonCard = cards.filter({ has: page.locator('a[href="jaisalmer-honeymoon-guide.php"]') });
    await expect(honeymoonCard).toBeVisible();
    const visibleTextMatches = page.locator('[data-journal-card]:visible');
    expect(await visibleTextMatches.count()).toBeGreaterThan(0);
    for (const card of await visibleTextMatches.all()) {
      expect((await card.innerText()).toLowerCase()).toContain('honeymoon');
    }
    await expect(page.locator('[data-journal-results]')).toContainText(' of ' + total + ' results.');

    await topic.selectOption('food-and-crafts');
    await expect(page.locator('[data-journal-empty]')).toBeVisible();
    await expect(page.locator('[data-journal-results]')).toHaveText('Showing 0 of ' + total + ' results.');
    await expect(page.getByRole('button', { name: 'Clear filters' })).toBeVisible();

    await page.getByRole('button', { name: 'Clear filters' }).click();
    await expect(search).toHaveValue('');
    await expect(topic).toHaveValue('all');
    await expect(page.locator('[data-journal-empty]')).toBeHidden();
    await expect(page.locator('[data-journal-results]')).toHaveText('Showing all ' + total + ' results.');
    await expect(page.locator('[data-journal-card]:visible')).toHaveCount(total);
    await expect(search).toBeFocused();

    await topic.selectOption('food-and-crafts');
    const foodCards = page.locator('[data-journal-card]:visible');
    await expect(foodCards).toHaveCount(2);
    for (const card of await foodCards.all()) {
      await expect(card).toHaveAttribute('data-journal-topic', 'food-and-crafts');
    }

    await search.fill('honeymoon');
    await expect(page.locator('[data-journal-empty]')).toBeVisible();
    await page.getByRole('link', { name: 'Food & crafts' }).click();
    await expect(topic).toHaveValue('all');
    await expect(search).toHaveValue('');
    await expect(page.locator('#food-and-crafts')).toBeVisible();

    await search.fill('  packing   list  ');
    await search.press('Enter');
    await expect(page).toHaveURL(/\/blog\.php$/);
    await expect(page.locator('[data-journal-card]:visible')).toHaveCount(1);
    await expect(page.locator('[data-journal-card]:visible a[href="jaisalmer-desert-safari-packing-list.php"]')).toBeVisible();
  });

  test('search controls fit small viewports without horizontal overflow', async ({ page }, testInfo) => {
    test.skip(!mobileProjects.has(testInfo.project.name), 'Small-screen Journal controls run on both mobile projects.');
    await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('search', { name: 'Search Jaisalmer travel guides' })).toBeVisible();
    const sizes = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
      input: document.querySelector('[data-journal-search-input]').getBoundingClientRect().width,
      inputHeight: document.querySelector('[data-journal-search-input]').getBoundingClientRect().height,
      topicHeight: document.querySelector('[data-journal-topic-filter]').getBoundingClientRect().height,
    }));
    expect(sizes.document).toBeLessThanOrEqual(sizes.viewport + 1);
    expect(sizes.input).toBeGreaterThan(0);
    expect(sizes.inputHeight).toBeGreaterThanOrEqual(44);
    expect(sizes.topicHeight).toBeGreaterThanOrEqual(44);
    await page.getByRole('searchbox', { name: 'Search guides' }).fill('havelis');
    expect(await page.locator('[data-journal-card]:visible').count()).toBeGreaterThan(0);
  });

  test('all Journal cards and links remain available when JavaScript is disabled', async ({ browser }, testInfo) => {
    test.skip(testInfo.project.name !== desktopProject, 'No-JavaScript fallback is checked once.');
    const context = await browser.newContext({
      baseURL: 'http://127.0.0.1:8080',
      javaScriptEnabled: false,
      viewport: { width: 1280, height: 800 },
    });
    try {
      const page = await context.newPage();
      await page.goto('/blog.php', { waitUntil: 'domcontentloaded' });
      await expect(page.locator('[data-journal-search-form]')).toBeHidden();
      const cards = page.locator('[data-journal-grid] > [data-journal-card]');
      const count = await cards.count();
      expect(count).toBeGreaterThanOrEqual(13);
      await expect(page.locator('[data-journal-card]:visible')).toHaveCount(count);
      await expect(cards.first().locator('a')).toHaveAttribute('href', 'jaisalmer-fort-guide.php');
    } finally {
      await context.close();
    }
  });
});
