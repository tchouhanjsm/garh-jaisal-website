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
  const fallback = page.locator('#reserve .restaurant-reservation-unavailable');
  await fallback.getByRole('link', { name: 'Send a dining enquiry', exact: true }).click();
  await expectDiningEnquiry(page);
});

test('dining guidance and reservation fallbacks fit mobile and desktop viewports', async ({ page }) => {
  const response = await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const hours = page.locator('.restaurant-hours');
  await expect(hours).toBeVisible();
  await expect(hours).toContainText('Confirm current hours with the hotel');
  await expect(hours).toContainText('Confirm for your preferred date and time');
  await expect(hours).not.toContainText(/7:30 am|10:30 am|12:30 pm|3:30 pm|7:00 pm|11:00 pm/i);

  const actions = page.locator('#reserve .restaurant-reservation-actions');
  await expect(actions.getByRole('link', { name: 'Call the hotel' })).toBeVisible();
  await expect(actions.getByRole('link', { name: 'Ask on WhatsApp' })).toBeVisible();
  await expect(actions.getByRole('link', { name: 'Send a dining enquiry' })).toBeVisible();

  const metrics = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
    actions: document.querySelector('#reserve .restaurant-reservation-actions').scrollWidth,
    actionsClient: document.querySelector('#reserve .restaurant-reservation-actions').clientWidth,
  }));
  expect(metrics.document, 'Restaurant page has no horizontal overflow').toBeLessThanOrEqual(metrics.viewport + 1);
  expect(metrics.actions, 'Dining contact actions fit their container').toBeLessThanOrEqual(metrics.actionsClient + 1);

  for (const link of await actions.getByRole('link').all()) {
    const box = await link.boundingBox();
    expect(box?.height ?? 0, 'Dining contact links remain large enough to tap').toBeGreaterThanOrEqual(44);
  }
});
