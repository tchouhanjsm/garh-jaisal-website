const { test, expect } = require('@playwright/test');

const EXPECTED_ANALYTICS_IDS = ['G-0C646S1FQ0', 'G-6S5PCEVXH4'];

async function preventTrackedNavigation(page) {
  await page.addInitScript(() => {
    document.addEventListener('click', event => {
      const anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
      if (!anchor) return;
      try {
        const destination = new URL(anchor.href, window.location.href);
        if (destination.protocol === 'tel:' || destination.origin !== window.location.origin) {
          event.preventDefault();
        }
      } catch {
        // Invalid destinations are outside this tracking regression.
      }
    }, true);
  });
}

async function readTrackedEvents(page) {
  return page.evaluate(() => (window.dataLayer || [])
    .map(entry => Array.from(entry || []))
    .filter(entry => entry[0] === 'event')
    .map(entry => ({ name: entry[1], parameters: entry[2] || {} })));
}

function expectRoutedOnce(events, eventName, expectedPath, expectedContext) {
  const matches = events.filter(event => event.name === eventName);
  expect(matches, eventName + ' should be tracked once per interaction').toHaveLength(1);
  expect(matches[0].parameters.send_to).toEqual(EXPECTED_ANALYTICS_IDS);
  expect(matches[0].parameters.transport_type).toBe('beacon');
  expect(matches[0].parameters.page_path).toBe(expectedPath);
  if (expectedContext) {
    expect(matches[0].parameters.page_context).toBe(expectedContext);
  }
  return matches[0].parameters;
}

test('booking, call and WhatsApp intent events route to both approved GA4 IDs', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Conversion event routing runs once on desktop-1280');
  await page.route('https://www.googletagmanager.com/**', route => route.abort());
  await preventTrackedNavigation(page);

  await page.goto('/contact.php', { waitUntil: 'domcontentloaded' });
  const config = await page.evaluate(() => window.gjAnalyticsConfig || null);
  expect(config, 'shared page head exposes analytics routing from site config').not.toBeNull();
  expect(config.destinations).toEqual(EXPECTED_ANALYTICS_IDS);
  expect(new URL(config.bookingUrl).hostname).toBe('asiatech.in');

  await page.locator('a.contact-booking-button').click();
  let events = await readTrackedEvents(page);
  const booking = expectRoutedOnce(events, 'booking_outbound_click', '/contact.php', 'contact');
  expect(booking.cta_label).toMatch(/availability/i);

  await page.locator('a[href^="tel:"]').first().click();
  events = await readTrackedEvents(page);
  const call = expectRoutedOnce(events, 'click_to_call', '/contact.php', 'contact');
  expect(call.cta_label).toBe('call');

  await page.locator('a.contact-whatsapp-button').click();
  events = await readTrackedEvents(page);
  const whatsapp = expectRoutedOnce(events, 'click_whatsapp', '/contact.php', 'contact');
  expect(whatsapp.cta_label).toBe('whatsapp');
});

test('enquiry_start reports room, dining, arrival or general on first form interaction only', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Enquiry context regression runs once on desktop-1280');
  await page.route('https://www.googletagmanager.com/**', route => route.abort());

  await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  const roomEnquiryPath = await page.locator('a.room-enquiry-link').first().getAttribute('href');
  expect(roomEnquiryPath, 'room cards provide a contextual enquiry link').toBeTruthy();

  const cases = [
    { path: roomEnquiryPath, expectedContext: 'room' },
    { path: '/contact.php?topic=dining', expectedContext: 'dining' },
    { path: '/contact.php?topic=arrival', expectedContext: 'arrival' },
    { path: '/contact.php', expectedContext: 'general' }
  ];

  for (const entry of cases) {
    await page.goto(entry.path, { waitUntil: 'domcontentloaded' });
    await page.locator('#name').focus();
    await page.locator('#email').focus();

    const events = await readTrackedEvents(page);
    const parameters = expectRoutedOnce(events, 'enquiry_start', '/contact.php', 'contact');
    expect(parameters.enquiry_context).toBe(entry.expectedContext);
    expect(parameters).not.toHaveProperty('name');
    expect(parameters).not.toHaveProperty('email');
    expect(parameters).not.toHaveProperty('message');
    expect(parameters).not.toHaveProperty('link_url');
  }
});
