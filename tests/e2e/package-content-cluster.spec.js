const { test, expect } = require('@playwright/test');

test('Packages connects planning guides and vehicle enquiries to their existing destinations', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The content-cluster navigation contract is checked once.');

  await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });

  const section = page.locator('#planning-guides');
  await expect(page.locator('h1')).toHaveCount(1);
  await expect(section.getByRole('heading', {
    level: 2,
    name: 'Use a guide to shape your Jaisalmer visit.',
  })).toBeVisible();
  await expect(section.locator('.experience-card')).toHaveCount(3);

  const targets = [
    {
      href: 'jaisalmer-fort-guide.php',
      heading: 'Jaisalmer Fort Guide',
    },
    {
      href: 'desert-safari-guide.php',
      heading: 'Desert Safari in Jaisalmer: How to Choose an Experience',
    },
    {
      href: 'jaisalmer-2-day-itinerary.php',
      heading: 'Jaisalmer 2-Day Itinerary',
    },
  ];

  for (const target of targets) {
    const cardLink = section.locator(`a[href="${target.href}"]`);
    await expect(cardLink).toBeVisible();
    await expect(cardLink.locator('img')).toHaveAttribute('alt', /\S+/);
    await cardLink.click();
    await expect(page).toHaveURL(new RegExp(`/${target.href.replace('.', '\\.')}($|[?#])`));
    await expect(page.locator('h1')).toHaveText(target.heading);
    await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });
  }

  const carLink = section.getByRole('link', { name: 'Explore car arrangements' });
  await expect(carLink).toBeVisible();
  await expect(carLink).toHaveAttribute('href', 'car-rental.php');
  await carLink.click();
  await expect(page).toHaveURL(/\/car-rental\.php$/);
  await expect(page.locator('h1')).toBeVisible();
});


test('Packages finder searches published experiences and combines category and duration filters', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Experience finder interactions run once on desktop-1280');

  const response = await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Packages should load').not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const finder = page.locator('[data-experience-finder]');
  const cards = page.locator('[data-experience-filter-card]:visible');
  await expect(finder).toBeVisible();
  await expect(cards).toHaveCount(6);
  await expect(finder.locator('[data-experience-result-status]')).toHaveText('Showing 6 of 6 experiences.');

  const search = finder.locator('[data-experience-search]');
  await search.fill('Swiss tent');
  await expect(cards).toHaveCount(1);
  await expect(cards.locator('h3')).toHaveText('Overnight Safari in a Swiss Tent');

  await search.fill('this option does not exist');
  await expect(cards).toHaveCount(0);
  await expect(finder.locator('[data-experience-empty]')).toBeVisible();
  await expect(finder.locator('[data-experience-result-status]')).toHaveText('Showing 0 of 6 experiences.');
  await finder.locator('[data-experience-reset]').first().click();
  await expect(cards).toHaveCount(6);
  await expect(finder.locator('[data-experience-empty]')).toBeHidden();

  await finder.locator('[data-experience-category]').selectOption('city');
  await finder.locator('[data-experience-duration]').selectOption('full-day');
  await expect(cards).toHaveCount(1);
  await expect(cards.locator('h3')).toHaveText('Full-Day Guided Jaisalmer Tour');

  await finder.locator('[data-experience-duration]').selectOption('overnight');
  await expect(cards).toHaveCount(0);
  await expect(finder.locator('[data-experience-empty]')).toBeVisible();

  await finder.locator('[data-experience-reset]').last().click();
  await expect(cards).toHaveCount(6);
  await expect(finder.locator('[data-experience-category]')).toHaveValue('all');
  await expect(finder.locator('[data-experience-duration]')).toHaveValue('all');
  await expect(search).toHaveValue('');
});

test('Packages finder remains responsive and all filter controls meet touch-target minimums', async ({ page }) => {
  const response = await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const metrics = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth
  }));
  expect(metrics.document, 'Packages must not overflow horizontally at ' + metrics.viewport + 'px')
    .toBeLessThanOrEqual(metrics.viewport + 1);

  const finder = page.locator('[data-experience-finder]');
  await expect(finder).toBeVisible();
  for (const selector of ['[data-experience-search]', '[data-experience-category]', '[data-experience-duration]', '[data-experience-reset]']) {
    const control = finder.locator(selector).first();
    const box = await control.boundingBox();
    expect(box?.height ?? 0, selector + ' should have a usable 44px touch target')
      .toBeGreaterThanOrEqual(44);
  }
});

test('Packages experience list remains complete when JavaScript is unavailable', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'No-JavaScript fallback contract runs once on desktop-1280');

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    javaScriptEnabled: false,
    viewport: { width: 1280, height: 800 }
  });
  try {
    const noJsPage = await context.newPage();
    const noJsResponse = await noJsPage.goto('/pack.php', { waitUntil: 'domcontentloaded' });
    expect(noJsResponse).not.toBeNull();
    expect(noJsResponse.status()).toBeLessThan(400);
    await expect(noJsPage.locator('[data-experience-finder]')).toBeHidden();
    await expect(noJsPage.locator('[data-experience-filter-card]')).toHaveCount(6);
    await expect(noJsPage.locator('#desert-options [data-experience-filter-card]')).toHaveCount(4);
    await expect(noJsPage.locator('#city-options [data-experience-filter-card]')).toHaveCount(2);
    await expect(noJsPage.locator('[data-experience-filter-card] a[href$=".php"]')).toHaveCount(6);
  } finally {
    await context.close();
  }
});
