const { test, expect } = require('@playwright/test');

const channels = {
  primaryPhone: { href: 'tel:+917727047567', text: '+91 77270 47567' },
  alternatePhone: { href: 'tel:+912992253836', text: '+91 2992 253836' },
  primaryEmail: { href: 'mailto:info@hotelgarhjaisal.com', text: 'info@hotelgarhjaisal.com' },
  alternateEmail: { href: 'mailto:mukeshkila@yahoo.com', text: 'mukeshkila@yahoo.com' },
};
const pages = [
  { route: '/contact.php', phones: ['primaryPhone', 'alternatePhone'], emails: ['alternateEmail', 'primaryEmail'] },
  { route: '/direction.php', phones: ['primaryPhone', 'alternatePhone'], emails: ['primaryEmail', 'alternateEmail'] },
  { route: '/roof.php', phones: ['primaryPhone'], emails: [] },
];

test('Contact, Directions and Rooftop retain the existing public contact channels', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Contact channel parity runs once on desktop-1280.');
  for (const contract of pages) {
    const response = await page.goto(contract.route, { waitUntil: 'domcontentloaded' });
    expect(response, contract.route + ': HTTP response').not.toBeNull();
    expect(response.status(), contract.route + ': page remains available').toBeLessThan(400);
    const main = page.locator('main');
    for (const key of contract.phones) {
      const channel = channels[key];
      const link = main.locator('a[href="' + channel.href + '"]').first();
      await expect(link, contract.route + ': ' + key).toBeVisible();
      await expect(link).toContainText(channel.text);
    }
    for (const key of contract.emails) {
      const channel = channels[key];
      const link = main.locator('a[href="' + channel.href + '"]').first();
      await expect(link, contract.route + ': ' + key).toBeVisible();
      await expect(link).toHaveText(channel.text);
    }
  }
});

test('contact routes remain usable without horizontal overflow at all supported widths', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Responsive contact-channel coverage.');
  for (const route of ['/contact.php', '/direction.php', '/roof.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ': HTTP response').not.toBeNull();
    expect(response.status(), route + ': page remains available').toBeLessThan(400);
    const metrics = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(metrics.document, route + ': no horizontal overflow at ' + testInfo.project.name)
      .toBeLessThanOrEqual(metrics.viewport + 1);
  }
});
