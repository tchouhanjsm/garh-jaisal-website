const { test, expect } = require('@playwright/test');

test('Car & City Tours uses the shared breadcrumb component with readable hero contrast', async ({ page }) => {
  const response = await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Car & City Tours should return an HTTP response').not.toBeNull();
  expect(response.status(), 'Car & City Tours should be reachable').toBe(200);

  const breadcrumb = page.locator('.package-hero-content nav.page-breadcrumb[aria-label="Breadcrumb"]');
  await expect(breadcrumb).toHaveCount(1);
  await expect(breadcrumb.locator('ol, li')).toHaveCount(0);
  await expect(breadcrumb.getByRole('link', { name: 'Home' })).toHaveAttribute('href', '/');
  await expect(breadcrumb.getByRole('link', { name: 'Packages' })).toHaveAttribute('href', '/pack.php');
  await expect(breadcrumb.locator('[aria-current="page"]')).toHaveText('Car & City Tours');
  await expect(breadcrumb).toHaveCSS('display', 'flex');
  await expect(breadcrumb).toHaveCSS('color', 'rgb(240, 223, 204)');
  await expect(breadcrumb.getByRole('link', { name: 'Home' })).toHaveCSS('color', 'rgb(255, 255, 255)');
});
