const { test, expect } = require('@playwright/test');

const proposedTopics = [
  'heritage-havelis',
  'jain-temples',
  'lake-cenotaph-photo',
  'kuldhara-landscape',
  'natural-history',
  'longewala-history',
];

test('Car & City hub separates existing options from proposed itinerary ideas and offers clear next steps', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Car & City journey assertions run once on desktop-1280.');

  const response = await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await expect(page.getByRole('heading', { level: 1, name: 'Explore Jaisalmer and plan your route.' })).toBeVisible();
  await expect(page.locator('.car-city-route-grid .car-city-route')).toHaveCount(3);
  await expect(page.locator('.car-city-route[href="guided-jaisalmer-tour.php"]')).toBeVisible();
  await expect(page.locator('.car-city-route[href="car-rental.php"]')).toBeVisible();
  await expect(page.locator('.car-city-route[href="#proposed-itineraries"]')).toBeVisible();

  const ideaSection = page.locator('#proposed-itineraries');
  await expect(ideaSection.locator('.tour-idea-card')).toHaveCount(proposedTopics.length);
  await expect(ideaSection.locator('.tour-ideas-disclaimer')).toContainText('not bookable products');
  await expect(ideaSection.locator('.tour-ideas-disclaimer')).toContainText('does not reserve a tour');

  for (const topic of proposedTopics) {
    const link = ideaSection.locator('a[href="contact.php?topic=' + topic + '"]');
    await expect(link, topic + ': contextual enquiry remains available').toBeVisible();
    await expect(link).toHaveText('Enquire about this idea');
    await expect(link).toHaveAttribute('aria-label', /^Enquire about/);
  }

  await expect(page.locator('[data-stay-booking-note] a').filter({ hasText: 'Check room availability' }))
    .toHaveAttribute('href', /^https:\/\/asiatech\.in\/booking_engine\/index3\?/);
});

test('Car & City hub stays usable at supported widths and its local photos load', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Supported viewport coverage.');

  const response = await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const metrics = await page.evaluate(() => ({
    viewport: document.documentElement.clientWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(metrics.document, 'Car & City hub must not introduce horizontal overflow at ' + testInfo.project.name)
    .toBeLessThanOrEqual(metrics.viewport + 1);

  await page.evaluate(async () => {
    const images = Array.from(document.querySelectorAll('main img'));
    images.forEach(image => { image.loading = 'eager'; });
    await Promise.all(images.map(image => image.decode().catch(() => undefined)));
  });
  const imageStates = await page.locator('main img').evaluateAll(images => images.map(img => ({
    alt: img.getAttribute('alt'),
    loaded: img.complete && img.naturalWidth > 0,
  })));
  expect(imageStates.every(image => image.alt !== null && image.loaded), 'Car & City images have alt text and load').toBeTruthy();
});
