const { test, expect } = require('@playwright/test');

const routes = [
  { path: '/', context: 'home', topic: 'visit-planning' },
  { path: '/room.php', context: 'stay', topic: 'stay' },
  { path: '/restaurant.php', context: 'dining', topic: 'dining' },
  { path: '/direction.php', context: 'arrival', topic: 'arrival' },
  { path: '/contact.php', context: 'contact', topic: null },
];

const viewportWidths = [320, 375, 390, 430, 768, 1366, 1440, 1920];

test('guest decision panels render one truthful, actionable guide on every route', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Cross-route panel contract runs once on desktop-1280');

  for (const route of routes) {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
    expect(response, route.path + ' should return an HTTP response').not.toBeNull();
    expect(response.status(), route.path + ' should load successfully').toBeLessThan(400);

    const panel = page.locator('[data-guest-decision-context="' + route.context + '"]');
    await expect(panel, route.path + ' should render exactly one guest decision panel').toHaveCount(1);
    await expect(panel.getByText('Published on this site', { exact: true })).toBeVisible();
    await expect(panel.getByText('Confirm with the hotel', { exact: true })).toBeVisible();
    await expect(panel.getByRole('heading', { name: 'What you can review now' })).toBeVisible();
    await expect(panel.getByRole('heading', { name: 'What to confirm before you decide' })).toBeVisible();
    expect(await panel.locator('ul').first().locator('li').count()).toBeGreaterThan(0);
    expect(await panel.locator('ul').nth(1).locator('li').count()).toBeGreaterThan(0);

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

test('room directory summary follows the maintained room registry and keeps the booking engine', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Room decision handoff runs once on desktop-1280');

  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const panel = page.locator('[data-guest-decision-context="stay"]');
  await expect(panel).toContainText('7 named rooms');
  await expect(panel).toContainText('Ground floor');
  await expect(panel).toContainText('First floor');
  await expect(panel).toContainText('maximum occupancy');
  await expect(panel).toContainText('bed configuration');
  await expect(panel).toContainText('room dimensions');
  await expect(panel.locator('.guest-decision__actions a.guest-decision__secondary'))
    .toHaveAttribute('href', /^https:\/\/asiatech\.in\/booking_engine\/index3\?token=/);

  await panel.locator('a.button-primary').click();
  await expect(page).toHaveURL(/contact\.php\?topic=stay$/);
  await expect(page.locator('.contact-topic-context')).toContainText('Room details and booking terms');
  await expect(page.locator('#message')).toHaveValue(/maximum occupancy/);
  await expect(page.locator('#message')).toHaveValue(/cancellation policy/);
});

test('guest decision panels remain contained and switch layout across required viewports', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Viewport matrix runs once on desktop-1280');
  test.setTimeout(90000);

  for (const width of viewportWidths) {
    await page.setViewportSize({ width, height: 900 });
    for (const route of routes) {
      const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
      expect(response, route.path + ' should load at ' + width + 'px').not.toBeNull();
      expect(response.status(), route.path + ' should load at ' + width + 'px').toBeLessThan(400);

      const panel = page.locator('[data-guest-decision-context="' + route.context + '"]');
      await expect(panel).toBeVisible();
      const layout = await panel.evaluate(element => {
        const columns = element.querySelector('.guest-decision__columns');
        const primary = element.querySelector('.guest-decision__actions .button-primary');
        const panelStyle = getComputedStyle(columns);
        return {
          panelClientWidth: element.clientWidth,
          panelScrollWidth: element.scrollWidth,
          columnCount: panelStyle.gridTemplateColumns.trim().split(/\s+/).length,
          primaryVisible: Boolean(primary && primary.getBoundingClientRect().width > 0),
          primaryWidth: primary ? primary.getBoundingClientRect().width : 0,
          actionWidth: primary && primary.parentElement ? primary.parentElement.clientWidth : 0,
        };
      });
      expect(layout.panelScrollWidth, route.path + ' panel should not overflow at ' + width + 'px')
        .toBeLessThanOrEqual(layout.panelClientWidth + 1);
      expect(layout.columnCount, route.path + ' should use the correct column layout at ' + width + 'px')
        .toBe(width <= 760 ? 1 : 2);
      expect(layout.primaryVisible, route.path + ' primary CTA should remain visible at ' + width + 'px').toBe(true);
      expect(layout.primaryWidth, route.path + ' primary CTA should fit its action rail at ' + width + 'px')
        .toBeLessThanOrEqual(layout.actionWidth + 1);
    }
  }
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

test('room-card enquiry clicks use the same privacy-safe room intent event', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Room-card attribution contract runs once on desktop-1280');

  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await page.locator('.room-enquiry-link').first().evaluate(anchor => {
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
  expect(payload.cta_id).toBe('enquiry-room-card');
  expect(payload.cta_location).toBe('room-card');
  for (const personalField of ['name', 'email', 'phone', 'message', 'room', 'room_slug']) {
    expect(payload).not.toHaveProperty(personalField);
  }
});
