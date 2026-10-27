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
    await expect(form.locator('#time')).toHaveAttribute('step', '1800');
    await expect(form.locator('#time')).toHaveAttribute('max', '23:30');
    await expect(form.locator('#guests')).toHaveAttribute('max', '20');
    await expect(form.locator('#note')).toHaveAttribute('maxlength', '500');
    await expect(form.locator('#consent')).toHaveAttribute('required', '');
    await expect(reservation).toContainText('not a confirmed booking');

    await expect(page.locator('.restaurant-hours')).toContainText('Confirm current hours with the hotel');
    await expect(page.locator('.restaurant-hours')).not.toContainText(/7:30 am|10:30 am|12:30 pm|3:30 pm|7:00 pm|11:00 pm/i);

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
