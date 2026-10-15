const { test, expect } = require('@playwright/test');

test('Sleep Under the Stars renders the curated nine-photo gallery accessibly', async ({ page }) => {
  const response = await page.goto('/sleep-under-the-stars.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Sleep Under the Stars route should return a response').not.toBeNull();
  expect(response.status(), 'Sleep Under the Stars route should remain published').toBe(200);
  await expect(page.getByRole('heading', { level: 1, name: 'Sleep Under the Stars in the Jaisalmer Desert' })).toBeVisible();

  const gallerySection = page.locator('.experience-photo-gallery');
  await expect(gallerySection.getByRole('heading', { level: 2, name: 'A closer look at Sleep Under the Stars' })).toBeVisible();
  const gallery = page.locator('[data-experience-gallery]');
  await expect(gallery).toBeVisible();

  const imagePaths = await gallery.locator('img').evaluateAll(images =>
    images.map(image => new URL(image.getAttribute('src'), document.baseURI).pathname.split('/').filter(Boolean).join('/'))
  );
  expect(imagePaths).toEqual([
    'img/sleeping_under_stars/Tour_1.jpeg',
    'img/sleeping_under_stars/tour_2.jpeg',
    'img/sleeping_under_stars/tour_3.jpeg',
    'img/sleeping_under_stars/tour_4.jpeg',
    'img/sleeping_under_stars/tour_6.jpeg',
    'img/sleeping_under_stars/tour_7.jpeg',
    'img/sleeping_under_stars/tour_8.jpeg',
    'img/sleeping_under_stars/tour_9.jpeg',
    'img/sleeping_under_stars/tour_10.jpeg',
  ]);
  await expect(gallery.locator('img')).toHaveCount(9);
  for (const caption of [
    'The open-air sleeping setup',
    'An evening around the campfire',
    'A camel ride through the dunes',
    'Bedding beneath the open sky',
    'The camp after sunset',
    'Food served during the overnight experience',
  ]) {
    await expect(gallery.getByText(caption, { exact: true })).toBeVisible();
  }

  const altTexts = await gallery.locator('img').evaluateAll(images => images.map(image => image.getAttribute('alt')));
  expect(altTexts.every(alt => typeof alt === 'string' && alt.trim() !== '')).toBe(true);

  const hasHorizontalOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
  expect(hasHorizontalOverflow, 'Sleep Under the Stars must not introduce horizontal scrolling').toBe(false);
});
