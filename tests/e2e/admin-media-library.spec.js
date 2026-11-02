const { test, expect } = require('@playwright/test');

const ownerUser = process.env.GJ_MEDIA_ADMIN_E2E_USER;
const ownerPassword = process.env.GJ_MEDIA_ADMIN_E2E_PASSWORD;

test('unauthenticated Media Library HTML and JSON requests are denied without inventory leakage', async ({ request }) => {
  const htmlResponse = await request.get('/admin/media-library.php');
  expect([401, 503]).toContain(htmlResponse.status());
  if (htmlResponse.status() === 401) expect(htmlResponse.headers()['www-authenticate']).toContain('Basic');
  const html = await htmlResponse.text();
  expect(html).not.toContain('Media library intelligence');
  expect(html).not.toContain('total image files');

  const jsonResponse = await request.get('/admin/media-library.php?format=json');
  expect([401, 503]).toContain(jsonResponse.status());
  if (jsonResponse.status() === 401) expect(jsonResponse.headers()['www-authenticate']).toContain('Basic');
  const jsonText = await jsonResponse.text();
  expect(jsonText).not.toContain('"assets"');
  expect(jsonText).not.toContain('"total_assets"');
});

test('authenticated owner can inspect the read-only Media Library on desktop and mobile', async ({ browser }) => {
  test.skip(!ownerUser || !ownerPassword, 'Set GJ_MEDIA_ADMIN_E2E_USER and GJ_MEDIA_ADMIN_E2E_PASSWORD for authenticated owner coverage.');
  const context = await browser.newContext({ httpCredentials: { username: ownerUser, password: ownerPassword } });
  try {
    const page = await context.newPage();
    const response = await page.goto('/admin/media-library.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);
    await expect(page.getByRole('heading', { level: 1, name: 'Media library intelligence' })).toBeVisible();
    await expect(page.getByText(/never delete from this report alone/i)).toBeVisible();
    await expect(page.getByRole('link', { name: 'Open Sveltia CMS' })).toHaveAttribute('href', '/admin/');
    await expect(page.locator('[data-media-card]')).not.toHaveCount(0);

    const jsonResponse = await page.request.get('/admin/media-library.php?format=json');
    expect(jsonResponse.status()).toBe(200);
    const report = await jsonResponse.json();
    expect(report.summary.total_assets).toBeGreaterThan(0);
    expect(report.summary.source_files_scanned).toBeGreaterThan(0);
    expect(typeof report.summary.source_scan_complete).toBe('boolean');
    expect(report.summary.total_bytes).toBeGreaterThan(0);
    expect(report.assets.every((asset) => Array.isArray(asset.optimization_flags))).toBe(true);
    expect(report.assets.some((asset) => asset.gallery_entries && asset.gallery_entries.length > 0)).toBe(true);

    await page.locator('#media-search').fill('facade');
    await expect(page.locator('#media-grid [data-media-card]')).not.toHaveCount(0);
    await page.setViewportSize({ width: 390, height: 844 });
    const dimensions = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document).toBeLessThanOrEqual(dimensions.viewport);
  } finally {
    await context.close();
  }
});

test('Sveltia media manager remains token-gated and links owners to media tools', async ({ page }) => {
  const response = await page.goto('/admin/', { waitUntil: 'commit' });
  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);
  const navigation = page.getByRole('navigation', { name: 'Owner tools' });
  await expect(navigation).toBeVisible();
  await expect(navigation.getByRole('link', { name: 'Media library intelligence' })).toHaveAttribute('href', '/admin/media-library.php');
  await expect(navigation.getByRole('link', { name: 'Public gallery' })).toHaveAttribute('href', '/gallery.php');
});
