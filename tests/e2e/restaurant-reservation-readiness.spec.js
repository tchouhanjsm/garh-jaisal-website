const { test, expect } = require('@playwright/test');

test.describe('restaurant reservation readiness', () => {
  test('offers direct contact instead of showing a non-submitting form when online requests are disabled', async ({ page }, testInfo) => {
    await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });

    const reservation = page.locator('#reserve');
    const fallback = reservation.locator('.restaurant-reservation-unavailable');

    await expect(reservation).toBeVisible();
    await expect(page.getByRole('link', { name: 'Reserve a table' })).toHaveAttribute('href', '#reserve');
    await expect(fallback).toBeVisible();
    await expect(fallback.getByRole('heading', { name: 'Enquire about a table' })).toBeVisible();
    await expect(fallback).toContainText("Online table requests aren't available on this page right now.");
    await expect(reservation.locator('.restaurant-form')).toHaveCount(0);

    const callLink = fallback.getByRole('link', { name: 'Call the hotel' });
    await expect(callLink).toHaveAttribute('href', /^tel:/);

    const whatsappLink = fallback.getByRole('link', { name: 'Ask on WhatsApp' });
    await expect(whatsappLink).toHaveAttribute('href', /^https:/);
    await expect(whatsappLink).toHaveAttribute('target', '_blank');
    await expect(whatsappLink).toHaveAttribute('rel', /noopener/);

    // Attach rendered evidence for the reservation decision point at each configured viewport.
    await testInfo.attach(`reservation-fallback-${testInfo.project.name}`, {
      body: await reservation.screenshot(),
      contentType: 'image/png',
    });
  });
});
