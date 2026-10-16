const { test, expect } = require('@playwright/test');

test('catalogue renders the reviewed product choices without unapproved prices', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Catalogue content contract is checked once.');

  const response = await page.goto('/catalogue.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  await expect(page).toHaveTitle('Jaisalmer Trip Planner | Rooms, Desert Stays, Tours & Transport');
  await expect(page.locator('main h1')).toHaveText('Build a Jaisalmer trip around your stay.');

  const canonical = await page.locator('head link[rel="canonical"]').getAttribute('href');
  expect(canonical).toBe('https://www.hotelgarhjaisal.com/catalogue.php');

  const cards = page.locator('[data-product-card]');
  await expect(cards).toHaveCount(9);
  for (const image of await cards.locator('img').all()) {
    await expect(image).toHaveAttribute('alt', /\S+/);
  }
  await expect(cards.locator('[data-trip-add]')).toHaveCount(9);
  for (const card of await cards.all()) {
    await expect(card).toContainText('Current price on request');
  }
  await expect(page.locator('#camp-compare-title')).toHaveText('Open sky or a Swiss tent?');
  const campComparisons = page.locator('.catalogue-compare-card');
  await expect(campComparisons).toHaveCount(2);
  await expect(campComparisons.nth(0).locator('.catalogue-compare-fact[data-comparison-field="Duration"] dd')).toHaveText('1 night');
  await expect(campComparisons.nth(0).locator('.catalogue-compare-fact[data-comparison-field="Sleep setup"] dd')).toHaveText('Open-air, with a mattress and blankets.');
  await expect(campComparisons.nth(0).locator('.catalogue-compare-fact[data-comparison-field="Bathroom"] dd')).toContainText('Not the same setup as a Swiss Deluxe Tent');
  await expect(campComparisons.nth(1).locator('.catalogue-compare-fact[data-comparison-field="Sleep setup"] dd')).toHaveText('Swiss Deluxe Tent accommodation.');
  await expect(campComparisons.nth(1).locator('.catalogue-compare-fact[data-comparison-field="Bathroom"] dd')).toHaveText('Attached bathroom with hot and cold shower.');
  await expect(page.locator('#transport-compare-title')).toHaveText('Choose the journey you need.');
  await expect(page.locator('main')).not.toContainText('₹');
  await expect(page.locator('.product-card-planning-note')).toHaveCount(9);
});

test('Trip tray combines a room and a desert stay without collecting guest details', async ({ page }, testInfo) => {
  test.skip(!['desktop-1280', 'mobile-390'].includes(testInfo.project.name), 'Trip flow runs at desktop and mobile widths.');
  test.setTimeout(60_000);

  await page.goto('/catalogue.php', { waitUntil: 'domcontentloaded' });
  await page.locator('[data-product-id="heritage-room-stay"] [data-trip-add]').click();
  await expect(page.locator('[data-trip-toggle]')).toBeVisible();
  await expect(page.locator('[data-trip-count]')).toHaveText('1');

  await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  const selectedRoom = page.locator('.room-card').first();
  const roomName = (await selectedRoom.locator('h3').innerText()).trim();
  await selectedRoom.locator('[data-trip-add]').click();
  await expect(page.locator('[data-trip-count]')).toHaveText('1');

  await page.goto('/swiss-tent-safari.php', { waitUntil: 'domcontentloaded' });
  await page.locator('[data-trip-add][data-trip-id="swiss-tent-safari"]').click();
  await expect(page.locator('[data-trip-count]')).toHaveText('2');
  await page.locator('[data-trip-toggle]').click();
  await expect(page.locator('[data-trip-panel]')).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(page.locator('[data-trip-panel]')).toBeHidden();
  await expect(page.locator('[data-trip-toggle]')).toHaveAttribute('aria-expanded', 'false');
  await expect(page.locator('[data-trip-toggle]')).toBeFocused();
  await page.locator('[data-trip-toggle]').click();
  await expect(page.locator('[data-trip-panel]')).toBeVisible();
  await expect(page.locator('[data-trip-items]')).toContainText(roomName);
  await expect(page.locator('[data-trip-items]')).toContainText('Overnight Safari in a Swiss Tent');
  await expect(page.locator('[data-trip-items]')).not.toContainText('Heritage room stay');

  const messageHref = await page.locator('[data-trip-send]').getAttribute('href');
  const message = new URL(messageHref).searchParams.get('text');
  expect(message).toContain(roomName);
  expect(message).toContain('Overnight Safari in a Swiss Tent');
  expect(message).toContain('Travel dates: [add dates]');
  expect(message).not.toMatch(/@example\.com|\b\d{10}\b/i);

  const stored = await page.evaluate(() => sessionStorage.getItem('gj-trip-items-v1'));
  expect(stored).toContain('swiss-tent-safari');
  expect(stored).toContain(roomName);
  expect(stored).not.toMatch(/email|mobile|phone|contactName/i);

  await page.locator('[data-trip-clear]').click();
  await expect(page.locator('[data-trip-toggle]')).toBeHidden();
  await expect(page.locator('[data-trip-panel]')).toBeHidden();
});


test('experience discovery hubs let guests shortlist verified options without turning ideas into offers', async ({ page }, testInfo) => {
  test.skip(!['desktop-1280', 'mobile-390'].includes(testInfo.project.name), 'Discovery journey checked at desktop and mobile widths.');
  for (const route of ['/desert-safari.php', '/car-tours.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    const canonical = await page.locator('head link[rel="canonical"]').getAttribute('href');
    expect(canonical).toBe('https://www.hotelgarhjaisal.com' + route);
    await expect(page.locator('main')).toContainText('Build your trip shortlist');
    await expect(page.locator('main')).not.toContainText('Book instantly');
    await expect(page.locator('main')).not.toContainText('Confirmed availability');
    await expect(page.locator('main')).not.toContainText('₹');
  }

  await page.goto('/desert-safari.php', { waitUntil: 'domcontentloaded' });
  const desertCards = page.locator('.experience-card');
  await expect(desertCards).toHaveCount(4);
  await expect(desertCards.locator('[data-trip-add]')).toHaveCount(4);
  await page.locator('.experience-card [data-trip-add]').first().click();
  await expect(page.locator('[data-trip-count]')).toHaveText('1');

  await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  const firstThreeOffers = page.locator('.experience-card').filter({ has: page.locator('[data-trip-add]') });
  await expect(firstThreeOffers).toHaveCount(3);
  await expect(page.locator('.tour-idea-card [data-trip-add]')).toHaveCount(0);
  await page.locator('.experience-card [data-trip-add]').nth(1).click();
  await expect(page.locator('[data-trip-count]')).toHaveText('2');
  await page.locator('[data-trip-toggle]').click();
  await expect(page.locator('[data-trip-items]')).toContainText('A Walk inside Jaisalmer Fort');
  await expect(page.locator('[data-trip-items]')).toContainText('Sunset Camel Safari in Jaisalmer');
  const stored = await page.evaluate(() => sessionStorage.getItem('gj-trip-items-v1'));
  expect(stored).toContain('fort-walk');
  expect(stored).not.toMatch(/email|mobile|phone|contactName/i);
});
