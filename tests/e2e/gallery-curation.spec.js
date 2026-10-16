const { test, expect } = require('@playwright/test');

const expectedCounts = {
  hotel: 7,
  rooms: 14,
  desert: 10,
  restaurant: 5,
};

test('Gallery keeps all curated photos available when JavaScript is disabled', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run the no-JavaScript fallback once');
  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    javaScriptEnabled: false,
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const response = await page.goto('/gallery.php', { waitUntil: 'domcontentloaded' });

  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);
  await expect(page.getByRole('heading', { level: 1, name: 'See Garh Jaisal Haveli.' })).toBeVisible();
  await expect(page.locator('[data-gallery-open]')).toHaveCount(36);
  await expect(page.locator('[data-gallery-open]:visible')).toHaveCount(36);
  await expect(page.locator('.gallery-filters')).toBeHidden();
  await expect(page.locator('[data-gallery-more]').first()).toBeHidden();

  for (const [category, count] of Object.entries(expectedCounts)) {
    await expect(page.locator('[data-gallery-category="' + category + '"] [data-gallery-open]')).toHaveCount(count);
  }
  await context.close();
});

test('Gallery filters, six-photo reveal and keyboard-operable lightbox stay coherent', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run functional Gallery assertions once');
  const response = await page.goto('/gallery.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);
  await expect(page.locator('.gallery-filters')).toBeVisible();
  await expect(page.locator('[data-gallery-open]')).toHaveCount(36);

  for (const [category, count] of Object.entries(expectedCounts)) {
    const section = page.locator('[data-gallery-category="' + category + '"]');
    await expect(section.locator('.gallery-item:visible')).toHaveCount(Math.min(6, count));
    await expect(section.locator('[data-gallery-count]')).toHaveText('Showing ' + Math.min(6, count) + ' of ' + count + ' curated photos.');
    const more = section.locator('[data-gallery-more]');
    if (count > 6) {
      await expect(more).toBeVisible();
      await more.click();
      const firstExpandedCount = Math.min(12, count);
      await expect(section.locator('.gallery-item:visible')).toHaveCount(firstExpandedCount);
      await expect(section.locator('[data-gallery-count]')).toHaveText('Showing ' + firstExpandedCount + ' of ' + count + ' curated photos.');
      if (firstExpandedCount < count) {
        await expect(more).toBeVisible();
        await more.click();
      }
      await expect(section.locator('.gallery-item:visible')).toHaveCount(count);
      await expect(more).toBeHidden();
      await expect(section.locator('[data-gallery-count]')).toHaveText('Showing ' + count + ' of ' + count + ' curated photos.');
    } else {
      await expect(more).toBeHidden();
    }
  }

  await page.getByRole('button', { name: 'Rooms', exact: true }).click();
  await expect(page.locator('[data-gallery-category]:visible')).toHaveCount(1);
  const rooms = page.locator('[data-gallery-category="rooms"]');
  await expect(rooms).toBeVisible();
  await expect(page.locator('[data-gallery-category="hotel"]')).toBeHidden();
  await expect(page.getByRole('button', { name: 'Rooms', exact: true })).toHaveAttribute('aria-pressed', 'true');
  await expect(page.locator('.gallery-jump a[href="#rooms"]')).toBeVisible();
  await expect(page.locator('.gallery-jump a[href="#hotel"]')).toBeHidden();
  await expect(rooms.locator('.gallery-item:visible')).toHaveCount(6);

  const firstPhoto = rooms.locator('[data-gallery-open]').first();
  const firstAlt = await firstPhoto.locator('img').getAttribute('alt');
  await firstPhoto.click();
  const dialog = page.locator('.gallery-lightbox');
  await expect(dialog).toBeVisible();
  await expect(dialog.locator('[data-gallery-lightbox-image]')).toHaveAttribute('alt', firstAlt);
  const firstCaption = await dialog.locator('[data-gallery-lightbox-caption]').textContent();
  await dialog.getByRole('button', { name: 'Next photo' }).click();
  await expect(dialog.locator('[data-gallery-lightbox-caption]')).not.toHaveText(firstCaption || '');
  await dialog.getByRole('button', { name: 'Previous photo' }).click();
  await expect(dialog.locator('[data-gallery-lightbox-caption]')).toHaveText(firstCaption || '');
  await dialog.getByRole('button', { name: /Close/ }).click();
  await expect(dialog).toBeHidden();
});

test('Gallery has no horizontal page overflow at configured responsive widths', async ({ page }) => {
  const response = await page.goto('/gallery.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);
  await expect(page.getByRole('heading', { level: 1, name: 'See Garh Jaisal Haveli.' })).toBeVisible();
  const dimensions = await page.evaluate(() => ({
    viewport: document.documentElement.clientWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, 'Gallery page must not overflow horizontally').toBeLessThanOrEqual(dimensions.viewport);
});
