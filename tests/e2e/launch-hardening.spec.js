const { test, expect } = require('@playwright/test');

test.describe('Sprint 3A launch hardening', () => {
  test('custom not-found page returns 404 and offers safe recovery paths', async ({ page }) => {
    const response = await page.goto('/404.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Custom not-found page must return an HTTP response').not.toBeNull();
    expect(response.status()).toBe(404);
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'noindex,follow');
    await expect(page.getByRole('heading', { level: 1 })).toContainText('We couldn’t find that page');
    await expect(page.getByRole('link', { name: 'Back to home' })).toHaveAttribute('href', 'index.php');
    await expect(page.getByRole('link', { name: 'Explore rooms' })).toHaveAttribute('href', 'room.php');
    await expect(page.getByRole('link', { name: 'Contact the hotel' })).toHaveAttribute('href', 'contact.php');
    await expect(page.locator('#main-content').getByRole('link', { name: 'WhatsApp' })).toHaveAttribute('href', /^https:\/\//);
    await expect(page.locator('#main-content').getByRole('link', { name: 'Check room availability' })).toHaveAttribute('href', /^https:\/\//);
  });

  test('mail endpoint redirects GET requests without processing a submission', async ({ request }) => {
    const response = await request.get('/mail.php', { maxRedirects: 0 });
    expect(response.status()).toBe(302);
    expect(response.headers().location).toBe('contact.php');
  });

  test('mail endpoint rejects a POST without a valid CSRF token before CAPTCHA or mail', async ({ request }) => {
    const response = await request.post('/mail.php', {
      maxRedirects: 0,
      form: {
        name: 'Automated test',
        email: 'test@example.com',
        message: 'This test must not send an enquiry.',
      },
    });
    expect(response.status()).toBe(302);
    expect(response.headers().location).toBe('contact.php?error=invalid');
  });

  test('honeypot submissions are rejected before CAPTCHA verification or email delivery', async ({ page }) => {
    await page.goto('/contact.php', { waitUntil: 'domcontentloaded' });
    const token = await page.locator('input[name="csrf_token"]').inputValue();
    const result = await page.evaluate(async (csrfToken) => {
      const body = new URLSearchParams({
        csrf_token: csrfToken,
        website: 'automated-bot',
        name: 'Automated bot',
        email: 'bot@example.com',
        message: 'This test must not send an enquiry.',
      });
      const response = await fetch('/mail.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        redirect: 'follow',
      });
      return {
        status: response.status,
        pathname: new URL(response.url).pathname,
        search: new URL(response.url).search,
      };
    }, token);
    expect(result.status).toBe(200);
    expect(result.pathname).toBe('/contact.php');
    expect(result.search).toBe('?error=send');
  });
});
