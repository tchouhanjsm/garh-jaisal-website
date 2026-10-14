const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';

function roomSlug(name) {
  return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

test('every room card keeps booking primary and selected-room enquiry secondary', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Complete room-directory assertions run once on desktop-1280');
  test.setTimeout(120_000);

  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Rooms should return an HTTP response').not.toBeNull();
  expect(response.status(), 'Rooms should load successfully').toBeLessThan(400);

  const cards = page.locator('.room-card');
  const roomCount = await cards.count();
  expect(roomCount, 'all room cards should render').toBe(7);

  const destinations = [];
  for (let index = 0; index < roomCount; index += 1) {
    const card = cards.nth(index);
    const name = (await card.locator('h3').innerText()).trim();
    const slug = roomSlug(name);
    const booking = card.locator('a.room-book');
    const enquiry = card.locator('a.room-enquiry-link');

    await expect(booking, name + ': primary availability action').toBeVisible();
    await expect(booking).toHaveText('Check availability');
    await expect(booking).toHaveAttribute('target', '_blank');
    await expect(booking).toHaveAttribute('rel', /noopener/);
    await expect(enquiry, name + ': secondary enquiry action').toBeVisible();
    await expect(enquiry).toHaveAttribute('href', 'contact.php?room=' + slug);
    await expect(enquiry).toContainText('Ask about this room');

    const actionOrder = await card.locator('.room-card-body').evaluate(body => {
      const primary = body.querySelector('a.room-book');
      const secondary = body.querySelector('a.room-enquiry-link');
      return Boolean(primary && secondary && primary.compareDocumentPosition(secondary) & Node.DOCUMENT_POSITION_FOLLOWING);
    });
    expect(actionOrder, name + ': booking must precede the secondary enquiry').toBe(true);

    destinations.push({ name, slug });
  }

  expect(new Set(destinations.map(item => item.slug)).size, 'room enquiry slugs should be unique').toBe(roomCount);

  // Check the room-specific enquiry renders the correct room context and a
  // useful prefilled message without submitting the form or sending email.
  for (const room of destinations) {
    const enquiryResponse = await page.goto('/contact.php?room=' + room.slug, { waitUntil: 'domcontentloaded' });
    expect(enquiryResponse, room.name + ': Contact should return an HTTP response').not.toBeNull();
    expect(enquiryResponse.status(), room.name + ': Contact should load successfully').toBeLessThan(400);
    await expect(page.locator('.contact-topic-context strong'), room.name + ': enquiry context').toHaveText(room.name);
    await expect(page.locator('#message'), room.name + ': prefilled enquiry').toHaveValue(new RegExp(room.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
    await expect(page.locator('form.contact-form button[type="submit"]')).toHaveText('Send message');
  }
});

test('room booking and enquiry actions remain usable without narrow-screen overflow', async ({ page }) => {
  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

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
