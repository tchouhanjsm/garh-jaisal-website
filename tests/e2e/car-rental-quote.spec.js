const { test, expect } = require('@playwright/test');

test('prepares a Delhi Airport return quote with the selected package', async ({ page, context, baseURL }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Check the quote on narrow mobile and desktop.');
  const origin = new URL(baseURL).origin;
  await context.route(url => url.origin !== origin, route => route.abort());
  await page.goto('/car-rental.php', { waitUntil: 'domcontentloaded' });
  await page.getByRole('link', { name: 'Enquire about hotels and a car' }).click();

  const form = page.locator('[data-car-quote-form]');
  await expect(form.getByLabel('What would you like?')).toHaveValue('Car with hotels');
  await form.getByLabel('Pickup location').fill('Delhi Airport');
  await form.getByLabel('Pickup date').fill('2099-01-10');
  await form.getByLabel('Drop-off location').fill('Delhi Airport');
  await form.getByLabel('Drop-off date').fill('2099-01-09');
  await form.getByLabel('Passengers').fill('3');
  await form.getByLabel('Luggage (optional)').fill('2 suitcases');
  await form.getByLabel('Stops or other details (optional)').fill('Jaipur and Jaisalmer');

  const submit = form.getByRole('button', { name: 'Continue to WhatsApp' });
  await submit.click();
  expect(await form.getByLabel('Drop-off date').evaluate(input => input.validity.rangeUnderflow)).toBe(true);
  await form.getByLabel('Drop-off date').fill('2099-01-16');

  const target = new URL(await form.getAttribute('data-whatsapp-url'));
  let draftUrl;
  await page.route(url => url.origin === target.origin, async route => {
    draftUrl = new URL(route.request().url());
    await route.fulfill({ body: 'Synthetic WhatsApp draft' });
  });
  await submit.click();
  await expect.poll(() => draftUrl?.pathname).toBe(target.pathname);
  for (const line of ['Package: Car with hotels', 'Pickup: Delhi Airport on 2099-01-10',
    'Drop-off: Delhi Airport on 2099-01-16', 'Passengers: 3', 'Luggage: 2 suitcases',
    'Stops or other details: Jaipur and Jaisalmer', 'the total price']) {
    expect(draftUrl.searchParams.get('text')).toContain(line);
  }
});

test('vehicle-only choice is preselected and direct WhatsApp remains available without JavaScript', async ({ page, browser, baseURL }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Check the alternate choice and no-script fallback once.');
  const origin = new URL(baseURL).origin;
  await page.route(url => url.origin !== origin, route => route.abort());
  await page.goto('/car-rental.php', { waitUntil: 'domcontentloaded' });
  await page.getByRole('link', { name: 'Enquire about a vehicle' }).click();
  await expect(page.getByLabel('What would you like?')).toHaveValue('Vehicle only');

  const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
  try {
    const page = await context.newPage();
    await context.route(url => url.origin !== origin, route => route.abort());
    await page.goto('/car-rental.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-car-quote-form]')).toBeHidden();
    const fallback = page.getByRole('link', { name: 'Request a car quote on WhatsApp' });
    await expect(fallback).toBeVisible();
    const target = new URL(await fallback.getAttribute('href'));
    expect(target.searchParams.get('text')).toContain('car with hotels or vehicle only');
  } finally {
    await context.close();
  }
});
