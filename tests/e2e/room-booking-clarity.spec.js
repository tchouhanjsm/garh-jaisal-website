const { test, expect } = require('@playwright/test');
const EXPECTED_ROOM_COUNT = 7;
const BOOKING_URL = 'https://asiatech.in/booking_engine/index3?token=NjA2Ng==';

function roomSlug(name) {
  return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

test('every room card preserves the primary booking and secondary selected-room enquiry journeys', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Full room-directory assertion runs once on desktop-1280');
  test.setTimeout(120_000);

  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Rooms should return an HTTP response').not.toBeNull();
  expect(response.status(), 'Rooms should load successfully').toBeLessThan(400);

  const cards = page.locator('.room-card');
  await expect(cards).toHaveCount(EXPECTED_ROOM_COUNT);

  const destinations = [];
  for (let index = 0; index < EXPECTED_ROOM_COUNT; index += 1) {
    const card = cards.nth(index);
    const name = (await card.locator('h3').innerText()).trim();
    const slug = roomSlug(name);

    const booking = card.locator('a.room-book');
    await expect(booking, name + ': primary availability action').toBeVisible();
    await expect(booking).toHaveText('Check availability');
    const bookingHref = await booking.getAttribute('href');
    expect(bookingHref, name + ': booking token and original serialization are preserved')
      .toContain(BOOKING_URL);
    const bookingUrl = new URL(bookingHref);
    expect(bookingUrl.origin + bookingUrl.pathname).toBe('https://asiatech.in/booking_engine/index3');
    expect(bookingUrl.searchParams.get('token')).toBe('NjA2Ng==');
    expect(bookingUrl.searchParams.get('utm_source')).toBe('hotel_website');
    expect(bookingUrl.searchParams.get('utm_medium')).toBe('website');
    expect(bookingUrl.searchParams.get('utm_campaign')).toBe('direct_booking');
    expect(bookingUrl.searchParams.get('utm_content')).toBe('room');
    await expect(booking).toHaveAttribute('target', '_blank');
    await expect(booking).toHaveAttribute('rel', /noopener/);

    const enquiry = card.locator('a.room-enquiry-link');
    await expect(enquiry, name + ': secondary enquiry action').toBeVisible();
    await expect(enquiry).toHaveAttribute('href', 'contact.php?room=' + slug);
    await expect(enquiry).toContainText('Ask about this room');

    const actionOrder = await card.locator('.room-card-body').evaluate(body => {
      const primary = body.querySelector('a.room-book');
      const secondary = body.querySelector('a.room-enquiry-link');
      return Boolean(
        primary && secondary &&
        (primary.compareDocumentPosition(secondary) & Node.DOCUMENT_POSITION_FOLLOWING)
      );
    });
    expect(actionOrder, name + ': booking must precede the secondary enquiry').toBe(true);

    destinations.push({ name: name, slug });
  }

  expect(new Set(destinations.map(item => item.slug)).size, 'room enquiry slugs should be unique').toBe(EXPECTED_ROOM_COUNT);

  // Verify the selected-room enquiry renders the matching context and a useful
  // prefilled message. The test does not submit the form or send an email.
  for (const room of destinations) {
    const enquiryResponse = await page.goto('/contact.php?room=' + room.slug, { waitUntil: 'domcontentloaded' });
    expect(enquiryResponse, room.name + ': Contact should return an HTTP response').not.toBeNull();
    expect(enquiryResponse.status(), room.name + ': Contact should load successfully').toBeLessThan(400);
    await expect(page.locator('.contact-room-context strong'), room.name + ': enquiry context').toHaveText(room.name);
    await expect(page.locator('input[name="enquiry_context_token"]'), room.name + ': opaque context token')
      .toHaveValue(/^[a-f0-9]{32}$/);
    const message = await page.locator('#message').inputValue();
    expect(message).toContain(room.name);
    expect(message).toContain('current availability, rate and room features');
    await expect(page.locator('form.contact-form button[type="submit"]')).toHaveText('Send message');
  }
});

test('room booking and enquiry actions remain usable without horizontal overflow', async ({ page }) => {
  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await page.setViewportSize({ width: 390, height: 844 });
  const mobileMetrics = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(mobileMetrics.document, 'Rooms must not overflow horizontally at mobile width')
    .toBeLessThanOrEqual(mobileMetrics.viewport + 1);
  await page.setViewportSize({ width: 1280, height: 800 });

  const metrics = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(metrics.document, 'Rooms must not overflow horizontally at ' + metrics.viewport + 'px')
    .toBeLessThanOrEqual(metrics.viewport + 1);

  const firstCard = page.locator('.room-card').first();
  await expect(firstCard.locator('a.room-book')).toBeVisible();
  await expect(firstCard.locator('a.room-enquiry-link')).toBeVisible();

  const primaryBox = await firstCard.locator('a.room-book').boundingBox();
  const secondaryBox = await firstCard.locator('a.room-enquiry-link').boundingBox();
  expect(primaryBox?.height ?? 0, 'primary availability action should have a usable hit area').toBeGreaterThanOrEqual(44);
  expect(secondaryBox?.height ?? 0, 'secondary enquiry action should have a usable hit area').toBeGreaterThanOrEqual(44);

  await firstCard.locator('a.room-book').focus();
  await expect(firstCard.locator('a.room-book')).toBeFocused();
  await firstCard.locator('a.room-enquiry-link').focus();
  await expect(firstCard.locator('a.room-enquiry-link')).toBeFocused();
});
