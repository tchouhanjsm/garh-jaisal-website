const { test, expect } = require('@playwright/test');

const EXPECTED_ANALYTICS_IDS = ['G-0C646S1FQ0', 'G-6S5PCEVXH4'];

async function readTrackedEvents(page) {
  return page.evaluate(() => (window.dataLayer || [])
    .map(entry => Array.from(entry || []))
    .filter(entry => entry[0] === 'event')
    .map(entry => ({ name: entry[1], parameters: entry[2] || {} })));
}

function expectRoutedOnce(events, eventName) {
  const matches = events.filter(event => event.name === eventName);
  expect(matches, eventName + ' should be emitted once').toHaveLength(1);
  expect(matches[0].parameters.send_to).toEqual(EXPECTED_ANALYTICS_IDS);
  expect(matches[0].parameters.transport_type).toBe('beacon');
  expect(matches[0].parameters.page_path).toBe('/restaurant.php');
  expect(matches[0].parameters.page_context).toBe('dining');
  for (const field of ['name', 'email', 'whatsapp', 'date', 'time', 'guests', 'message', 'topic']) {
    expect(matches[0].parameters).not.toHaveProperty(field);
  }
  return matches[0].parameters;
}

test('server-rejected restaurant requests link each validation error to its field', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Server-side validation runs once on desktop-1280');

  await page.goto('/restaurant.php?utm_source=google&email=private%40example.com#reserve', {
    waitUntil: 'domcontentloaded'
  });
  const form = page.locator('#reserve form.restaurant-form');
  await expect(form).toBeVisible();

  // novalidate intentionally sends this synthetic invalid request to the PHP validators.
  await form.locator('#name').fill('');
  await form.locator('#whatsapp').fill('not-a-phone');
  await form.locator('#date').fill('2020-01-01');
  await form.locator('#time').fill('');
  await form.locator('#guests').fill('0');
  await form.locator('#note').fill('x'.repeat(501));
  await form.getByRole('button', { name: 'Request a table' }).click();

  const checks = [
    ['name', 'Enter your name.'],
    ['whatsapp', 'Enter a valid WhatsApp number.'],
    ['date', 'Choose today or a future date.'],
    ['time', 'Choose a preferred time on the hour or half-hour.'],
    ['guests', 'Guests must be between 1 and 20.'],
    ['note', 'Keep the special note to 500 characters or fewer.'],
    ['consent', 'Please agree that the hotel may use these details to respond to your request.']
  ];
  for (const [field, message] of checks) {
    const control = form.locator('#' + field);
    await expect(control).toHaveAttribute('aria-invalid', 'true');
    const errorId = await control.getAttribute('aria-describedby');
    expect(errorId, field + ' must reference its error message').toBeTruthy();
    const error = page.locator('#' + errorId);
    await expect(error).toHaveText(message);
    await expect(error).toHaveAttribute('id', errorId);
  }
  await expect(form.locator('#name')).toBeFocused();
  await expect(form.locator('#name')).toHaveAttribute('autocomplete', 'name');
  await expect(form.locator('#whatsapp')).toHaveAttribute('autocomplete', 'tel');
  await expect(form.locator('#email')).toHaveAttribute('autocomplete', 'email');
  await expect(form.locator('#time')).toHaveAttribute('step', '1800');
  await expect(form.locator('#note')).toHaveAttribute('maxlength', '500');
});

test('restaurant form start and submit attempt events exclude personal fields and never imply a sent booking', async ({ page }, testInfo) => {
  test.skip(process.env.GJ_RESTAURANT_MAIL_ENABLED !== '1', 'Run in a dedicated server with the gated restaurant form enabled.');
  test.skip(testInfo.project.name !== 'desktop-1280', 'Restaurant form events run once on desktop-1280');

  await page.route('https://www.googletagmanager.com/**', route => route.abort());
  await page.goto('/restaurant.php?utm_source=google&utm_medium=cpc&utm_campaign=restaurant_form&email=private%40example.com', {
    waitUntil: 'domcontentloaded'
  });
  const form = page.locator('#reserve form.restaurant-form');
  await expect(form).toBeVisible();

  await form.locator('#name').focus();
  await form.locator('#whatsapp').focus();
  let events = await readTrackedEvents(page);
  const start = expectRoutedOnce(events, 'restaurant_enquiry_start');
  expect(start.utm_source).toBe('google');
  expect(start.utm_medium).toBe('cpc');
  expect(start.utm_campaign).toBe('restaurant_form');

  // Prevent the POST after the delegated listener observes it. This verifies event semantics
  // without delivering synthetic form data or depending on mail() in CI.
  await form.evaluate(element => element.addEventListener('submit', event => event.preventDefault(), { capture: true }));
  await form.getByRole('button', { name: 'Request a table' }).click();
  events = await readTrackedEvents(page);
  expectRoutedOnce(events, 'restaurant_enquiry_start');
  const attempt = expectRoutedOnce(events, 'restaurant_request_attempt');
  expect(attempt).not.toHaveProperty('submission_status');
  expect(attempt).not.toHaveProperty('booking_confirmed');
});
