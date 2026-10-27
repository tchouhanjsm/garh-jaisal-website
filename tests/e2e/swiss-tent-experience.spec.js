const { test, expect } = require('@playwright/test');

test('Swiss Tent Safari shows confirmed facilities, owner-provided photos and clear package exclusions', async ({ page }) => {
  const response = await page.goto('/swiss-tent-safari.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Swiss tent route should return a response').not.toBeNull();
  expect(response.status(), 'Swiss tent route should remain published').toBe(200);
  await expect(page.getByRole('heading', { level: 1, name: 'Overnight Safari in a Swiss Tent' })).toBeVisible();

  const gallery = page.locator('[data-experience-gallery]');
  await expect(gallery).toBeVisible();
  const imagePaths = await gallery.locator('img').evaluateAll(images =>
    images.map(image => new URL(image.getAttribute('src'), document.baseURI).pathname.split('/').filter(Boolean).join('/'))
  );
  expect(imagePaths).toEqual([
    'img/Desert_Camp/camp_from_above.jpeg',
    'img/Desert_Camp/camp_evening.jpeg',
    'img/Desert_Camp/CampingInJsm.jpeg',
    'img/Desert_Camp/tent_deluxe.jpeg',
    'img/Desert_Camp/camp_dinner.jpeg',
    'img/Desert_Camp/Bathroom.jpeg',
    'img/Desert_Camp/Outtent.jpeg',
    'img/Desert_Camp/Tent.jpeg',
    'img/Desert_Camp/Tentout.jpeg',
    'img/Desert_Camp/tentout1.jpeg',
  ]);
  await expect(gallery.locator('img')).toHaveCount(10);
  await expect(gallery.getByText('Swiss Deluxe Tent accommodation')).toHaveCount(1);
  await expect(gallery.getByText('The attached bathroom', { exact: true })).toHaveCount(1);
  await expect(page.getByText('Cultural programme and folk music', { exact: true }).first()).toBeVisible();
  await expect(gallery.getByText('Inside the Swiss Deluxe Tent', { exact: true })).toHaveCount(1);

  const facilities = page.locator('.experience-facilities');
  await expect(facilities.getByText('King-size bed', { exact: true })).toBeVisible();
  await expect(facilities.getByText('Attached bathroom with hot and cold shower', { exact: true })).toBeVisible();
  await expect(facilities.getByText('Tea and coffee maker', { exact: true })).toBeVisible();
  await expect(facilities.getByText('Toiletries', { exact: true })).toBeVisible();
  await expect(facilities.getByText('Study table', { exact: true })).toBeVisible();
  await expect(facilities.getByText('Full-length mirror', { exact: true })).toBeVisible();
  await expect(facilities.getByText('Towels', { exact: true })).toBeVisible();

  const details = page.locator('.experience-detail-card');
  await expect(details.nth(0).getByText('High tea with snacks', { exact: true })).toBeVisible();
  await expect(details.nth(1).getByText('Alcoholic beverages', { exact: true })).toBeVisible();
  await expect(details.nth(1).getByText('Soft drinks', { exact: true })).toBeVisible();
  await expect(details.nth(1).getByText('Separate jeep safari excursion (must be booked separately)', { exact: true })).toBeVisible();

  const hasHorizontalOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
  expect(hasHorizontalOverflow, 'Swiss tent route must not introduce horizontal scrolling').toBe(false);
});
