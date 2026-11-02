const { test, expect } = require('@playwright/test');

const routes = [
  { path: '/', context: 'home', topic: 'visit-planning' },
  { path: '/room.php', context: 'stay', topic: 'stay' },
  { path: '/restaurant.php', context: 'dining', topic: 'dining' },
  { path: '/direction.php', context: 'arrival', topic: 'arrival' },
  { path: '/contact.php', context: 'contact', topic: null },
];

test('guest decision panels separate published information from date-dependent confirmation', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Cross-route content contract runs once on desktop-1280');

  for (const route of routes) {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
    expect(response, route.path + ' should return an HTTP response').not.toBeNull();
    expect(response.status(), route.path + ' should load successfully').toBeLessThan(400);

    const panel = page.locator('[data-guest-decision-context="' + route.context + '"]');
    await expect(panel, route.path + ' should render its guest decision panel').toHaveCount(1);
    await expect(panel.getByText('Published on this site', { exact: true })).toBeVisible();
    await expect(panel.getByText('Confirm with the hotel', { exact: true })).toBeVisible();
    await expect(panel.getByRole('heading', { name: 'What you can review now' })).toBeVisible();
    await expect(panel.getByRole('heading', { name: 'What to confirm before you decide' })).toBeVisible();
    const shownCount = await panel.locator('ul').first().locator('li').count();
    const confirmCount = await panel.locator('ul').nth(1).locator('li').count();
    expect(shownCount).toBeGreaterThan(0);
    expect(confirmCount).toBeGreaterThan(0);

    const primary = panel.locator('.guest-decision__actions a.button-primary');
    await expect(primary).toBeVisible();
    await expect(primary).toHaveAttribute('data-cta-id', /^(?:[a-z0-9]+-){1,7}[a-z0-9]+$/);
    await expect(primary).toHaveAttribute('data-cta-location', /^(?:[a-z0-9]+-){1,7}[a-z0-9]+$/);
    if (route.context === 'contact') {
      await expect(primary).toHaveAttribute('href', '#contact-form');
      await expect(page.locator('#contact-form')).toHaveCount(1);
    } else {
      await expect(primary).toHaveAttribute('href', new RegExp('contact\\.php\\?topic=' + route.topic));
    }
  }
});

test('room decision path asks for missing room facts and preserves the live booking route', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Room decision handoff runs once on desktop-1280');

  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const panel = page.locator('[data-guest-decision-context="stay"]');
  await expect(panel).toContainText('maximum occupancy');
  await expect(panel).toContainText('bed configuration');
  await expect(panel).toContainText('room size');
  await expect(panel).toContainText('cancellation terms');
  await expect(panel.locator('.guest-decision__actions a.guest-decision__secondary'))
    .toHaveAttribute('href', /^https:\/\/asiatech\.in\/booking_engine\/index3\?token=/);

  await panel.locator('a.button-primary').click();
  await expect(page).toHaveURL(/contact\.php\?topic=stay$/);
  await expect(page.locator('.contact-topic-context')).toContainText('Room details and booking terms');
  await expect(page.locator('#message')).toHaveValue(/maximum occupancy/);
  await expect(page.locator('#message')).toHaveValue(/cancellation policy/);
});

test('guest decision panel stacks cleanly on narrow screens', async ({ page }) => {
  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  await page.setViewportSize({ width: 390, height: 844 });

  const measurements = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(measurements.document, 'guest decision content must not create horizontal overflow')
    .toBeLessThanOrEqual(measurements.viewport + 1);
  await expect(page.locator('[data-guest-decision-context="stay"] .guest-decision__actions a.button-primary'))
    .toBeVisible();
});

test('room details enquiry click is attributed as intent without collecting guest fields', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Analytics intent contract runs once on desktop-1280');

  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await page.locator('[data-guest-decision-context="stay"] a.button-primary').evaluate(anchor => {
    anchor.addEventListener('click', event => event.preventDefault(), { once: true });
    anchor.click();
  });

  await expect.poll(async () => page.evaluate(() => {
    const rows = Array.isArray(window.dataLayer) ? window.dataLayer : [];
    return rows.some(entry => entry && entry[0] === 'event' && entry[1] === 'room_details_enquiry_click');
  })).toBe(true);

  const payload = await page.evaluate(() => {
    const rows = Array.isArray(window.dataLayer) ? window.dataLayer : [];
    const entry = rows.find(item => item && item[0] === 'event' && item[1] === 'room_details_enquiry_click');
    return entry && entry[2] ? entry[2] : {};
  });
  expect(payload.cta_id).toBe('enquiry-room-details');
  expect(payload.cta_location).toBe('rooms-guest-decision');
  for (const personalField of ['name', 'email', 'phone', 'message']) {
    expect(payload).not.toHaveProperty(personalField);
  }
});
