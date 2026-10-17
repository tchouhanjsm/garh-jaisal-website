const { test, expect } = require('@playwright/test');

async function expectDiningEnquiry(page) {
  await expect(page).toHaveURL(/\/contact\.php\?topic=dining$/);
  await expect(page.getByRole('heading', { level: 2, name: 'Send an enquiry' })).toBeVisible();

  const context = page.locator('.contact-topic-context');
  await expect(context).toContainText('Rooftop dining');
  await expect(context).toContainText('preferred date, time and number of guests');
  await expect(context).toContainText('does not confirm a table');
  await expect(context).toContainText('confirm service hours and availability');

  const draft = await page.locator('#message').inputValue();
  expect(draft).toContain('Preferred date: [add date].');
  expect(draft).toContain('Preferred time: [add time].');
  expect(draft).toContain('Number of guests: [add number].');
  expect(draft).toContain('confirm current service hours, table availability');
  expect(draft).toContain('menu options and prices');
  expect(draft).toContain('available dietary options');
  expect(draft).not.toMatch(/your table is booked|reservation is confirmed/i);

  // Cover the handoff only; never submit a real enquiry from Browser E2E.
  await expect(page.locator('form.contact-form')).toHaveAttribute('action', 'mail.php');
  await expect(page.getByRole('button', { name: 'Send message' })).toBeVisible();
}

test('rooftop and restaurant entry points reach the contextual dining enquiry', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Dining journey runs once on desktop-1280');

  const roofResponse = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  expect(roofResponse).not.toBeNull();
  expect(roofResponse.status()).toBeLessThan(400);
  await page.locator('.page-hero-inner').getByRole('link', { name: 'Contact the hotel', exact: true }).click();
  await expectDiningEnquiry(page);

  const restaurantResponse = await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });
  expect(restaurantResponse).not.toBeNull();
  expect(restaurantResponse.status()).toBeLessThan(400);
  await expect(page.locator('#reserve form.restaurant-form')).toBeVisible();
  await page.locator('.restaurant-contact-help a[href="contact.php?topic=dining"]').click();
  await expectDiningEnquiry(page);
});

test('dining guidance, the active reservation form and direct-contact links fit mobile and desktop viewports', async ({ page }) => {
  const response = await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const hours = page.locator('.restaurant-hours');
  await expect(hours).toBeVisible();
  await expect(hours).toContainText('Confirm current hours with the hotel');
  await expect(hours).toContainText('Confirm for your preferred date and time');
  await expect(hours).not.toContainText(/7:30 am|10:30 am|12:30 pm|3:30 pm|7:00 pm|11:00 pm/i);

  const reserve = page.locator('#reserve');
  const form = reserve.locator('form.restaurant-form');
  await expect(form).toBeVisible();
  await expect(form.getByRole('button', { name: 'Request a table' })).toBeVisible();

  const callLink = reserve.locator('a[href^="tel:"]').first();
  await expect(callLink).toBeVisible();
  await expect(callLink).toHaveAttribute('href', /^tel:/);

  const whatsappLink = reserve.locator('a[href^="https://wa.me/"]').first();
  await expect(whatsappLink).toBeVisible();
  await expect(whatsappLink).toHaveAttribute('target', '_blank');
  await expect(whatsappLink).toHaveAttribute('rel', /noopener/);

  const diningEnquiry = page.locator('.restaurant-contact-help a[href="contact.php?topic=dining"]');
  await expect(diningEnquiry).toBeVisible();

  const metrics = await page.evaluate(() => {
    const reserveGrid = document.querySelector('#reserve .restaurant-reserve-grid');
    return {
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
      reserveGrid: reserveGrid.scrollWidth,
      reserveGridClient: reserveGrid.clientWidth,
    };
  });
  expect(metrics.document, 'Restaurant page has no horizontal overflow').toBeLessThanOrEqual(metrics.viewport + 1);
  expect(metrics.reserveGrid, 'The reservation section fits its layout container').toBeLessThanOrEqual(metrics.reserveGridClient + 1);

  const whatsappBox = await whatsappLink.boundingBox();
  expect(whatsappBox?.height ?? 0, 'WhatsApp action remains large enough to tap').toBeGreaterThanOrEqual(44);
});
