const { test, expect } = require('@playwright/test');

test('Rooftop hero prioritises the existing table enquiry journey', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Rooftop enquiry handoff is checked once.');

  for (const width of [390, 1280]) {
    await page.setViewportSize({ width, height: 900 });
    const response = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Rooftop page must return an HTTP response').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const hero = page.locator('main.roof-page .page-hero-inner');
    const enquiry = hero.getByRole('link', { name: 'Enquire about a table', exact: true });
    await expect(enquiry).toHaveAttribute('href', 'restaurant.php#reserve');
    await expect(enquiry).toHaveAttribute('data-cta-id', 'enquiry-rooftop-table');
    await expect(enquiry).toHaveAttribute('data-cta-location', 'rooftop-dining-hero');
    await expect(hero.getByRole('link', { name: 'View the menu', exact: true }))
      .toHaveAttribute('href', 'restaurant.php#menu');
    await expect(hero.locator('[data-cta-id="booking-rooftop-dining"]')).toHaveCount(0);
    await expect(hero.locator('[data-trip-add][data-trip-id="rooftop-dining"]')).toHaveCount(0);
    const tripAction = page.locator('.roof-trip-action [data-trip-add][data-trip-id="rooftop-dining"]');
    await expect(tripAction).toBeVisible();
    await expect(tripAction).toHaveAttribute('data-trip-title', 'Rooftop dining');
  }

  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  await page.getByRole('link', { name: 'Enquire about a table', exact: true }).click();
  await expect(page).toHaveURL(/\/restaurant\.php#reserve$/);
  await expect(page.locator('#reserve h2')).toHaveText('Request a table.');
  await expect(page.locator('#reserve .restaurant-policy'))
    .toContainText('not a confirmed booking');
  await expect(page.locator('#reserve button[type="submit"]')).toHaveText('Request a table');
});
