const { test, expect } = require('@playwright/test');

test('Directions avoids unconfirmed check-in, reception and arrival promises', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Arrival copy assertions run once on desktop-1280');

  const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Directions should return an HTTP response').not.toBeNull();
  expect(response.status(), 'Directions should load successfully').toBeLessThan(400);

  const arrival = page.locator('.arrival-contact');
  await expect(arrival.getByRole('heading', { level: 2, name: 'Confirm your arrival plan.' })).toBeVisible();
  await expect(arrival).toContainText('confirm current reception hours, check-in and check-out times, early-arrival access, luggage storage');
  await expect(arrival).toContainText('Share your expected arrival time');
  await expect(arrival.getByRole('link', { name: 'Call', exact: true })).toBeVisible();
  await expect(arrival.getByRole('link', { name: 'WhatsApp', exact: true })).toBeVisible();

  const arrivalFaq = page.locator('.arrival-faq');
  await expect(arrivalFaq).toContainText('Can I check in early or leave luggage before check-in?');
  await expect(arrivalFaq).toContainText('What if I arrive outside reception hours?');
  await expect(arrivalFaq).toContainText('Confirm current reception hours');
  await expect(arrivalFaq).toContainText('Confirm current service times, breakfast availability, and menu options');

  const renderedCopy = await page.locator('main.arrival-page').innerText();
  for (const unconfirmedClaim of [
    '13:00',
    '7:00 AM–10:30 PM',
    '7:00 AM to 10:30 PM',
    '10:30 PM and 6:30 AM',
    '24 hours ahead',
    'Early arrivals are welcome',
    'Breakfast is available daily',
    'until 10:30 PM',
    'use the rooftop terrace while the room is prepared',
    'team can meet you and help with luggage',
    'team can meet you at the parking area and help with luggage',
    'staff can meet you and help with luggage',
    'A porter can meet you when you reach the parking area',
    'Luggage help and a meet-and-assist at Fort Parking',
    'Meet-and-assist is available with prior notice',
    'Meet the team',
  ]) {
    expect(renderedCopy, 'Directions should not publish unconfirmed claim: ' + unconfirmedClaim)
      .not.toContain(unconfirmedClaim);
  }
});

test('arrival guidance remains within the viewport at mobile and desktop widths', async ({ page }) => {
  for (const viewport of [
    { width: 390, height: 844 },
    { width: 1280, height: 800 },
  ]) {
    await page.setViewportSize(viewport);
    const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Directions should return an HTTP response').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const dimensions = await page.evaluate(() => ({
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document, 'Directions must not overflow horizontally at ' + dimensions.viewport + 'px')
      .toBeLessThanOrEqual(dimensions.viewport + 1);
  }
});
