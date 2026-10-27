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
  await expect(arrivalFaq).toContainText('Confirm the route, vehicle, pickup point, current quote, timing and availability for your date before travelling.');

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
    '₹300',
    '₹1100',
    '₹120–₹150',
    'Typical door-to-door time',
    'within 8 minutes of the train arriving',
    'about 12 minutes of travel in total',
    'about 20 minutes',
  ]) {
    expect(renderedCopy, 'Directions should not publish unconfirmed claim: ' + unconfirmedClaim)
      .not.toContain(unconfirmedClaim);
  }
});

test('home and stay guides do not republish unconfirmed arrival promises', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Cross-page arrival copy is checked once on desktop-1280');

  const pages = [
    { path: '/', label: 'Home' },
    { path: '/book-rooms-in-jaisalmer.php', label: 'Stay guide' },
    { path: '/jaisalmer-fort-guide.php', label: 'Fort guide' },
  ];
  const forbiddenClaims = [
    { pattern: /7:00\s*AM\s*(?:to|[–-])\s*10:30\s*PM/i, label: 'fixed reception hours' },
    { pattern: /reception is open/i, label: 'an unconfirmed published reception schedule' },
    { pattern: /our team can meet you near Fort Parking and assist with luggage/i, label: 'a guaranteed meet-and-assist or luggage service' },
    { pattern: /guests are met near Fort Parking/i, label: 'a guaranteed meeting at Fort Parking' },
    { pattern: /can arrange meet-and-assist and luggage help/i, label: 'a guaranteed meet-and-assist or luggage service' },
    { pattern: /we can meet you and assist with your luggage/i, label: 'a guaranteed luggage service' },
  ];

  for (const entry of pages) {
    const response = await page.goto(entry.path, { waitUntil: 'domcontentloaded' });
    expect(response, entry.label + ' must return an HTTP response').not.toBeNull();
    expect(response.status(), entry.label + ' must load successfully').toBeLessThan(400);

    const main = page.locator('main#main-content');
    const renderedCopy = await main.innerText();
    for (const claim of forbiddenClaims) {
      expect(renderedCopy, entry.label + ' should not publish ' + claim.label)
        .not.toMatch(claim.pattern);
    }

    if (entry.path === '/') {
      const rooftop = page.locator('section:has(#rooftop-dining-title)');
      const rooftopCopy = await rooftop.innerText();
      expect(renderedCopy, 'Home must not promise breakfast service in any section or card').not.toMatch(/\benjoy breakfast\b/i);
      expect(renderedCopy, 'Home must not imply an unverified drinks offering in any section or card').not.toMatch(/quiet drink/i);
      expect(rooftopCopy, 'Home rooftop section must not promise unconfirmed breakfast service').not.toMatch(/\bbreakfast\b/i);
      expect(rooftopCopy, 'Home rooftop section must not imply an unverified drinks offering').not.toMatch(/quiet drink/i);
      expect(rooftopCopy).toMatch(/Ask about the current dishes,\s+service hours and table availability\s+before you make the evening's plan\./);
    }

    expect(
      await main.locator('a[href="contact.php?topic=arrival"]').count(),
      entry.label + ' offers a contextual arrival enquiry'
    ).toBeGreaterThan(0);
    expect(
      await main.locator('a[href="direction.php"]').count(),
      entry.label + ' retains a link to the full arrival guide'
    ).toBeGreaterThan(0);
  }
});

test('rooftop dining copy does not imply unconfirmed sunrise or all-day meal service', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Rooftop dining copy is checked once on desktop-1280');

  const response = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Rooftop dining page must return an HTTP response').not.toBeNull();
  expect(response.status(), 'Rooftop dining page must load successfully').toBeLessThan(400);

  const rooftop = page.locator('main.roof-page');
  const renderedCopy = await rooftop.innerText();
  expect(renderedCopy).not.toMatch(/stay for the sunrise or sunset while you eat/i);
  expect(renderedCopy).not.toMatch(/breakfast is available daily|breakfast service/i);
  expect(renderedCopy).toContain('Contact the hotel to confirm current dining service times before visiting.');
  await expect(rooftop.getByRole('link', { name: 'View the menu' })).toHaveAttribute('href', 'restaurant.php#menu');
  await expect(rooftop.locator('.page-hero-inner').getByRole('link', { name: 'Contact the hotel' })).toHaveAttribute('href', 'contact.php?topic=dining');
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
