const { test, expect } = require('@playwright/test');

test('read-only media library reports usage, Gallery records and duplicate evidence', async ({ page }) => {
  const response = await page.goto('/admin/media-library.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);

  await expect(page.getByRole('heading', { level: 1, name: 'Media library intelligence' })).toBeVisible();
  await expect(page.getByText(/never delete from this report alone/i)).toBeVisible();
  await expect(page.getByRole('link', { name: 'Open Sveltia CMS' })).toHaveAttribute('href', '/admin/');
  await expect(page.locator('[data-media-card]')).not.toHaveCount(0);

  const reportResponse = await page.request.get('/admin/media-library.php?format=json');
  expect(reportResponse.status()).toBe(200);
  const report = await reportResponse.json();
  expect(report.summary.total_assets).toBeGreaterThan(0);
  expect(report.summary.source_files_scanned).toBeGreaterThan(0);
  expect(Array.isArray(report.assets)).toBe(true);
  expect(report.assets.some((asset) => asset.gallery_entries && asset.gallery_entries.length > 0)).toBe(true);

  await page.locator('#media-search').fill('facade');
  await expect(page.locator('#media-grid [data-media-card]')).not.toHaveCount(0);

  await page.locator('#media-search').fill('');
  await page.locator('#media-category').selectOption('rooms');
  const roomCards = page.locator('#media-grid [data-media-card]');
  await expect(roomCards).not.toHaveCount(0);

  await page.setViewportSize({ width: 390, height: 844 });
  const dimensions = await page.evaluate(() => ({
    viewport: document.documentElement.clientWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, 'Media inventory must not overflow on mobile').toBeLessThanOrEqual(dimensions.viewport);
});

test('media inventory JSON remains read-only and excludes private configuration paths', async ({ page }) => {
  const response = await page.request.get('/admin/media-library.php?format=json');
  expect(response.status()).toBe(200);
  const report = await response.json();
  const usagePaths = report.assets.flatMap((asset) => asset.usage_files || []);
  expect(usagePaths).not.toContain('include/site-config.local.php');

  const pageResponse = await page.goto('/admin/media-library.php');
  expect(pageResponse.status()).toBe(200);
  await expect(page.getByText(/does not upload, replace, hide or delete files/i)).toBeVisible();
  await expect(page.getByRole('link', { name: 'View public gallery' })).toHaveAttribute('href', '/gallery.php');
});
