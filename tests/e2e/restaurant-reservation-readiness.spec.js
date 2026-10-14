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
    await expect(page.locator('.restaurant-hours')).toContainText('Confirm current hours with the hotel');
    await expect(page.locator('.restaurant-hours')).not.toContainText(/7:30 am|10:30 am|12:30 pm|3:30 pm|7:00 pm|11:00 pm/i);
    await expect(reservation.locator('.restaurant-form')).toHaveCount(0);

    const callLink = fallback.getByRole('link', { name: 'Call the hotel' });
    await expect(callLink).toHaveAttribute('href', /^tel:/);

    const whatsappLink = fallback.getByRole('link', { name: 'Ask on WhatsApp' });
    await expect(whatsappLink).toHaveAttribute('href', /^https:/);
    await expect(whatsappLink).toHaveAttribute('target', '_blank');
    await expect(whatsappLink).toHaveAttribute('rel', /noopener/);
    const whatsappMessage = new URL(await whatsappLink.getAttribute('href')).searchParams.get('text') || '';
    expect(whatsappMessage).toContain('Preferred date: [add date].');
    expect(whatsappMessage).toContain('Preferred time: [add time].');
    expect(whatsappMessage).toContain('Number of guests: [add number].');
    expect(whatsappMessage).toContain('confirm current service hours, table availability');
    await expect(fallback.getByRole('link', { name: 'Send a dining enquiry' }))
      .toHaveAttribute('href', 'contact.php?topic=dining');

    // Attach rendered evidence for the reservation decision point at each configured viewport.
    await testInfo.attach(`reservation-fallback-${testInfo.project.name}`, {
      body: await reservation.screenshot(),
      contentType: 'image/png',
    });
  });
});
