const { test, expect } = require('@playwright/test');

test('prepares a Delhi Airport return quote with the selected package', async ({ page, context, baseURL }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Check the quote on narrow mobile and desktop.');
  const origin = new URL(baseURL).origin;
  await context.route(url => url.origin !== origin, route => route.abort());
  let quoteIntent;
  await page.exposeFunction('recordQuoteIntent', event => { quoteIntent = event; });
  await page.addInitScript(() => {
    window.addEventListener('garh:car-quote-ready', () => {
      const event = window.dataLayer.find(entry => entry[0] === 'event' && entry[1] === 'click_whatsapp');
      if (event) window.recordQuoteIntent(event[2]);
    });
  });
  await page.goto('/car-rental.php', { waitUntil: 'domcontentloaded' });
  await page.getByRole('link', { name: 'Enquire about hotels and a car' }).click();

  const form = page.locator('[data-car-quote-form]');
  await expect(form.getByLabel('What would you like?')).toHaveValue('Car with hotels');
  await form.getByLabel('Stops or other details (optional)').fill('Jaipur and Jaisalmer');
  await page.getByRole('link', { name: 'Delhi Airport → Jaipur → Jodhpur → Jaisalmer → Delhi Airport' }).click();
  await expect(form.getByLabel('Sample route (optional)')).toHaveValue('Jaipur → Jodhpur → Jaisalmer');
  await expect(form.getByLabel('Pickup location')).toHaveValue('Delhi Airport');
  await expect(form.getByLabel('Drop-off location')).toHaveValue('Delhi Airport');
  await expect(form.getByLabel('Stops or other details (optional)')).toHaveValue('Jaipur and Jaisalmer');
  await form.getByLabel('Pickup date').fill('2099-01-10');
  await form.getByLabel('Drop-off date').fill('2099-01-09');
  await form.getByLabel('Passengers').fill('3');
  const dock = page.locator('.mobile-cta');
  if (testInfo.project.name === 'mobile-320') {
    await form.getByLabel('Passengers').blur();
    await expect(dock).toBeVisible();
  }
  await form.getByLabel('Luggage (optional)').fill('2 suitcases');
  if (testInfo.project.name === 'mobile-320') {
    await expect(dock).toBeHidden();
  }
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
  await expect.poll(() => quoteIntent).toMatchObject({
    cta_id: 'whatsapp-car-quote',
    page_context: 'experiences',
    send_to: ['G-0C646S1FQ0', 'G-6S5PCEVXH4']
  });
  expect(JSON.stringify(quoteIntent)).not.toMatch(/suitcases|jaipur|2099-01-10/i);
  for (const line of ['Package: Car with hotels', 'Pickup: Delhi Airport on 2099-01-10',
    'Drop-off: Delhi Airport on 2099-01-16', 'Passengers: 3', 'Luggage: 2 suitcases',
    'Sample route: Jaipur → Jodhpur → Jaisalmer', 'Stops or other details: Jaipur and Jaisalmer', 'the total price']) {
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
