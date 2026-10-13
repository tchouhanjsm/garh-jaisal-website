const { test, expect } = require('@playwright/test');

test.describe('Contact layout and anti-spam regressions', () => {
  test('keeps the honeypot out of sight and avoids overlaying contact content', async ({ page }) => {
    const response = await page.goto('/contact.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Contact page must return an HTTP response').not.toBeNull();
    expect(response.status(), 'Contact page must be available').toBeLessThan(400);

    await expect(page.locator('.contact-dock')).toHaveCount(0);
    await expect(page.locator('.mobile-cta')).toHaveCount(0);

    const form = page.locator('form.contact-form');
    const honeypot = form.locator('.hp-field');
    await expect(honeypot).toHaveAttribute('aria-hidden', 'true');
    await expect(honeypot.locator('[name="website"]')).toHaveAttribute('tabindex', '-1');

    const honeypotBox = await honeypot.boundingBox();
    expect(honeypotBox, 'Honeypot remains in the form DOM for server-side detection').not.toBeNull();
    expect(honeypotBox.x, 'Honeypot is positioned outside the visible page').toBeLessThan(-1000);
    expect(honeypotBox.width).toBeLessThanOrEqual(1);
    expect(honeypotBox.height).toBeLessThanOrEqual(1);

    const whatsapp = page.locator('.contact-whatsapp-button');
    const configuredWhatsAppLink = page.locator('.nav-mega-foot a').filter({ hasText: /WhatsApp/i }).first();
    await expect(whatsapp).toHaveCount(1);
    await expect(whatsapp).toHaveAttribute('href', await configuredWhatsAppLink.getAttribute('href'));
    await expect(whatsapp).toHaveAttribute('target', '_blank');
    await expect(whatsapp).toHaveAttribute('rel', /noopener/);

    // The Contact page has inline Call, WhatsApp and booking paths; other pages retain persistent quick actions.
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.contact-dock')).toHaveCount(1);
    await expect(page.locator('.mobile-cta')).toHaveCount(1);
  });

  test('keeps Contact facts and the enquiry form aligned at the current viewport', async ({ page }) => {
    const response = await page.goto('/contact.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Contact page must return an HTTP response').not.toBeNull();
    expect(response.status(), 'Contact page must be available').toBeLessThan(400);

    const layout = await page.locator('.contact-layout').evaluate((root) => {
      const facts = root.querySelector('.contact-facts').getBoundingClientRect();
      const form = root.querySelector('.contact-form').getBoundingClientRect();
      return {
        viewportWidth: window.innerWidth,
        documentWidth: document.documentElement.scrollWidth,
        facts: {
          left: facts.left,
          right: facts.right,
          top: facts.top,
          bottom: facts.bottom,
          width: facts.width,
        },
        form: {
          left: form.left,
          right: form.right,
          top: form.top,
          bottom: form.bottom,
          width: form.width,
        },
      };
    });

    expect(layout.documentWidth, 'Contact page must not overflow horizontally').toBeLessThanOrEqual(layout.viewportWidth + 1);
    expect(layout.form.left, 'Form stays inside the visible viewport').toBeGreaterThanOrEqual(0);
    expect(layout.form.right, 'Form stays inside the visible viewport').toBeLessThanOrEqual(layout.viewportWidth + 1);
    expect(layout.facts.width, 'Contact information must have a measurable column').toBeGreaterThan(0);
    expect(layout.form.width, 'Enquiry form must have a measurable column').toBeGreaterThan(0);

    if (layout.viewportWidth > 800) {
      expect(layout.form.left, 'Desktop columns have clear separation').toBeGreaterThanOrEqual(layout.facts.right + 20);
      expect(Math.abs(layout.form.top - layout.facts.top), 'Desktop columns align at the top').toBeLessThanOrEqual(2);
    } else {
      expect(layout.form.top, 'Mobile stacks the form below contact information').toBeGreaterThan(layout.facts.bottom);
    }
  });
});
