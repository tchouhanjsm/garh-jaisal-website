const { test, expect } = require('@playwright/test');

const experienceHubRoutes = [
  '/pack.php',
  '/desert-safari.php',
  '/guided-jaisalmer-tour.php',
  '/car-tours.php',
];

test('the stay guide is reachable from shared navigation, the homepage and room selection', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Direct-booking discovery assertions run once on desktop-1280.');

  const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await expect(page.locator('.site-header a[href="book-rooms-in-jaisalmer.php"]')).toHaveCount(1);
  await expect(page.locator('.site-footer a[href="book-rooms-in-jaisalmer.php"]')).toHaveCount(1);
  await expect(page.locator('#start-planning')).toContainText('Read our guide to staying inside the Fort');

  await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('.rooms-hero-guide a[href="book-rooms-in-jaisalmer.php"]')).toBeVisible();
  await expect(page.locator('.rooms-cta-guide-link[href="book-rooms-in-jaisalmer.php"]')).toBeVisible();
});

test('the stay guide has an above-the-fold booking path with the configured booking destination', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Direct-booking path assertions run once on desktop-1280.');

  const response = await page.goto('/book-rooms-in-jaisalmer.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await expect(page.getByRole('heading', { level: 1, name: 'Staying inside Jaisalmer Fort' })).toBeVisible();
  const bookingLink = page.locator('.guide-hero-actions a').filter({ hasText: 'Check room availability' });
  await expect(bookingLink).toBeVisible();
  await expect(bookingLink).toHaveAttribute('href', /^https:\/\/asiatech\.in\/booking_engine\/index3\?/);
  const bookingUrl = new URL(await bookingLink.getAttribute('href'));
  expect(bookingUrl.searchParams.get('token')).toBe('NjA2Ng==');
  await expect(page.locator('.guide-hero-actions a[href="room.php"]')).toHaveText('Compare the rooms');
  await expect(page.locator('.guide-hero-contact a[href="contact.php"]')).toBeVisible();
  await expect(page.locator('.booking-path-steps > li')).toHaveCount(3);
  await expect(page.locator('.booking-path-steps')).toContainText('Plan the extras');
});

test('the experience and tour hubs explain the separate room-booking step and preserve the existing booking action', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Experience-to-stay crosslinks run once on desktop-1280.');

  for (const route of experienceHubRoutes) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ': HTTP response').not.toBeNull();
    expect(response.status(), route + ': public route remains available').toBeLessThan(400);

    const note = page.locator('[data-stay-booking-note]');
    await expect(note, route + ': room-first note').toBeVisible();
    await expect(note).toContainText('Room booking is separate from tour enquiries');
    const checkRooms = note.locator('a').filter({ hasText: 'Check room availability' });
    await expect(checkRooms).toBeVisible();
    await expect(checkRooms).toHaveAttribute('href', /^https:\/\/asiatech\.in\/booking_engine\/index3\?/);
    await expect(note.locator('a[href="book-rooms-in-jaisalmer.php"]')).toBeVisible();
  }
});

test('the new stay-first path stays inside the supported viewport at mobile and desktop sizes', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Supported viewport coverage.');

  for (const route of ['/book-rooms-in-jaisalmer.php', ...experienceHubRoutes]) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const metrics = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(metrics.document, route + ': no horizontal overflow at ' + testInfo.project.name)
      .toBeLessThanOrEqual(metrics.viewport + 1);
  }
});
