const { test, expect } = require('@playwright/test');

test.describe('restaurant reservation enquiry readiness', () => {
  test('shows a request form with half-hour times, note, consent and direct-contact fallback', async ({ page }, testInfo) => {
    await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });
    const reservation = page.locator('#reserve');
    const form = reservation.locator('form.restaurant-form');

    await expect(reservation).toBeVisible();
    await expect(page.getByRole('link', { name: 'Enquire about a table' })).toHaveAttribute('href', '#reserve');
    await expect(form).toBeVisible();
    for (const selector of ['#name', '#whatsapp', '#email', '#date', '#time', '#guests', '#note', '#consent']) {
      await expect(form.locator(selector), selector + ' is part of the enquiry form').toBeVisible();
    }
    await expect(form.locator('#time option').nth(1)).toHaveAttribute('value', '17:00');
    await expect(form.locator('#time option').last()).toHaveAttribute('value', '22:00');
    await expect(form.locator('#guests')).toHaveAttribute('max', '20');
    await expect(form.locator('#note')).toHaveAttribute('maxlength', '500');
    await expect(form.locator('#consent')).toHaveAttribute('required', '');
    await expect(reservation).toContainText('not a confirmed booking');
    await expect(reservation.getByRole('heading', { name: 'Request a table.' })).toBeVisible();

    await expect(page.locator('.restaurant-hours')).toContainText('5:00 PM–10:30 PM');
    await expect(page.locator('.restaurant-hours')).toContainText('Last seating 10:00 PM');

    await expect(reservation.locator('a[href^="tel:"]').first()).toHaveAttribute('href', /^tel:/);
    const whatsappLink = reservation.locator('a[href^="https://wa.me/"]').first();
    await expect(whatsappLink).toHaveAttribute('href', /^https:/);
    await expect(whatsappLink).toHaveAttribute('target', '_blank');
    await expect(whatsappLink).toHaveAttribute('rel', /noopener/);

    await testInfo.attach('reservation-form-' + testInfo.project.name, {
      body: await reservation.screenshot(),
      contentType: 'image/png',
    });
  });
});
