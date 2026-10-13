const { test, expect } = require('@playwright/test');

test.describe('operational contact readiness (F13)', () => {
  test('contact page exposes consistent booking, arrival, phone, email, and labelled enquiry paths', async ({ page }, testInfo) => {
    const response = await page.goto('/contact.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Contact page must return an HTTP response').not.toBeNull();
    expect(response.status(), 'Contact page must be available').toBeLessThan(400);

    const booking = page.locator('.contact-booking-button');
    await expect(booking).toHaveCount(1);
    await expect(booking).toHaveAttribute('href', await page.locator('.nav-book').getAttribute('href'));
    await expect(booking).toHaveAttribute('href', /^https:\/\//);
    await expect(booking).toHaveAttribute('target', '_blank');
    await expect(booking).toHaveAttribute('rel', /noopener/);

    await expect(page.locator('.contact-arrival-link')).toHaveAttribute('href', 'direction.php');
    await expect(page.locator('.contact-facts a[href^="tel:"]')).toHaveCount(2);
    await expect(page.locator('.contact-facts a[href^="mailto:"]')).toHaveCount(2);

    const form = page.locator('form.contact-form');
    await expect(form).toHaveCount(1);
    await expect(form).toHaveAttribute('action', 'mail.php');
    await expect(form).toHaveAttribute('method', 'post');

    for (const field of ['Name', 'Email', 'Phone', 'Message']) {
      await expect(page.getByLabel(field, { exact: true })).toBeVisible();
    }
    for (const field of ['name', 'email', 'message']) {
      await expect(form.locator('[name="' + field + '"]')).toHaveAttribute('required', '');
    }
    await expect(form.locator('[name="csrf_token"]')).toHaveValue(/\S+/);
    await expect(form.locator('.g-recaptcha')).toHaveAttribute('data-sitekey', /\S+/);
    await expect(form.locator('.hp-field')).toHaveAttribute('aria-hidden', 'true');
    await expect(form.locator('[name="website"]')).toHaveAttribute('tabindex', '-1');
    await expect(form.getByRole('button', { name: 'Send message' })).toHaveAttribute('type', 'submit');

    // This readiness test never submits the form and never sends an email or enquiry.
    await testInfo.attach('contact-readiness-' + testInfo.project.name, {
      body: Buffer.from(JSON.stringify({
        viewport: testInfo.project.name,
        route: '/contact.php',
        bookingPathPresent: true,
        arrivalPathPresent: true,
        phoneLinks: 2,
        emailLinks: 2,
        labelledFields: ['Name', 'Email', 'Phone', 'Message'],
        formSubmitted: false,
      }, null, 2)),
      contentType: 'application/json',
    });
  });

  test('arrival page offers contact paths without starting a live interaction', async ({ page }) => {
    const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Arrival guide must return an HTTP response').not.toBeNull();
    expect(response.status(), 'Arrival guide must be available').toBeLessThan(400);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Arrive inside Jaisalmer Fort');

    const contact = page.locator('.arrival-contact');
    await expect(contact.getByRole('link', { name: 'Contact the hotel' })).toHaveAttribute('href', 'contact.php?topic=arrival');

    const whatsapp = contact.getByRole('link', { name: 'WhatsApp' });
    await expect(whatsapp).toHaveAttribute('href', /^https:\/\//);
    await expect(whatsapp).toHaveAttribute('target', '_blank');
    await expect(whatsapp).toHaveAttribute('rel', /noopener/);
    await expect(contact.locator('a[href^="tel:"]').first()).toHaveCount(1);
  });
});
