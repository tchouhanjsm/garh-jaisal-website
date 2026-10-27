const { test, expect } = require('@playwright/test');

async function expectDiningEnquiry(page) {
  await expect(page).toHaveURL(/\/contact\.php\?topic=dining$/);
  await expect(page.getByRole('heading', { level: 2, name: 'Send an enquiry' })).toBeVisible();

  const context = page.locator('.contact-topic-context');
  await expect(context).toContainText('Rooftop dining');
  await expect(context).toContainText('preferred date, time and number of guests');
  await expect(context).toContainText('does not confirm a table');
  await expect(context).toContainText('confirm service hours and availability');

  const draft = await page.locator('#message').inputValue();
  expect(draft).toContain('Preferred date: [add date].');
  expect(draft).toContain('Preferred time: [add time].');
  expect(draft).toContain('Number of guests: [add number].');
  expect(draft).toContain('confirm current service hours, table availability');
  expect(draft).toContain('menu options and prices');
  expect(draft).toContain('available dietary options');
  expect(draft).not.toMatch(/your table is booked|reservation is confirmed/i);

  // Cover the handoff only; never submit a real enquiry from Browser E2E.
  await expect(page.locator('form.contact-form')).toHaveAttribute('action', 'mail.php');
  await expect(page.getByRole('button', { name: 'Send message' })).toBeVisible();
}

test('rooftop and restaurant entry points reach the contextual dining enquiry', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Dining journey runs once on desktop-1280');

  const roofResponse = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  expect(roofResponse).not.toBeNull();
  expect(roofResponse.status()).toBeLessThan(400);
  await page.locator('.page-hero-inner').getByRole('link', { name: 'Contact the hotel', exact: true }).click();
  await expectDiningEnquiry(page);

  const restaurantResponse = await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });
  expect(restaurantResponse).not.toBeNull();
  expect(restaurantResponse.status()).toBeLessThan(400);
  await expect(page.locator('#reserve form.restaurant-form')).toBeVisible();
  await page.getByRole('link', { name: 'Send a dining enquiry →', exact: true }).click();
  await expectDiningEnquiry(page);
});

test('dining guidance, the active reservation form and direct-contact links fit mobile and desktop viewports', async ({ page }) => {
  const response = await page.goto('/restaurant.php#reserve', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const hours = page.locator('.restaurant-hours');
  await expect(hours).toBeVisible();
  await expect(hours).toContainText('Confirm current hours with the hotel');
  await expect(hours).toContainText('Confirm for your preferred date and time');
  await expect(hours).not.toContainText(/7:30 am|10:30 am|12:30 pm|3:30 pm|7:00 pm|11:00 pm/i);

  const reserve = page.locator('#reserve');
  const form = reserve.locator('form.restaurant-form');
  await expect(form).toBeVisible();
  await expect(form.getByRole('button', { name: 'Request a table' })).toBeVisible();

  const callLink = reserve.locator('a[href^="tel:"]').first();
  await expect(callLink).toBeVisible();
  await expect(callLink).toHaveAttribute('href', /^tel:/);

  const whatsappLink = reserve.locator('a[href^="https://wa.me/"]').first();
  await expect(whatsappLink).toBeVisible();
  await expect(whatsappLink).toHaveAttribute('target', '_blank');
  await expect(whatsappLink).toHaveAttribute('rel', /noopener/);

  const diningEnquiry = page.getByRole('link', { name: 'Send a dining enquiry →', exact: true });
  await expect(diningEnquiry).toBeVisible();

  const metrics = await page.evaluate(() => {
    const reserveGrid = document.querySelector('#reserve .restaurant-reserve-grid');
    return {
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
      reserveGrid: reserveGrid.scrollWidth,
      reserveGridClient: reserveGrid.clientWidth,
    };
  });
  expect(metrics.document, 'Restaurant page has no horizontal overflow').toBeLessThanOrEqual(metrics.viewport + 1);
  expect(metrics.reserveGrid, 'The reservation section fits its layout container').toBeLessThanOrEqual(metrics.reserveGridClient + 1);

  const whatsappBox = await whatsappLink.boundingBox();
  expect(whatsappBox?.height ?? 0, 'WhatsApp action remains large enough to tap').toBeGreaterThanOrEqual(44);
});


test('restaurant and rooftop entry points share one dining Trip option through Contact', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Cross-page dining Trip flow runs once on desktop-1280.');
  test.setTimeout(60_000);

  const restaurantResponse = await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });
  expect(restaurantResponse).not.toBeNull();
  expect(restaurantResponse.status()).toBeLessThan(400);

  const restaurantAdd = page.locator('.restaurant-hero [data-trip-add][data-trip-id="rooftop-dining"]');
  await expect(restaurantAdd).toBeVisible();
  await expect(restaurantAdd).toHaveAttribute('data-trip-title', 'Rooftop dining');
  await expect(restaurantAdd).toHaveAttribute('data-trip-url', 'roof.php');
  await expect(restaurantAdd).toHaveAttribute('data-trip-group', 'dining');
  await restaurantAdd.click();

  await expect(page.locator('[data-trip-count]')).toHaveText('1');
  await expect(page.locator('[data-trip-items]')).toContainText('Rooftop dining');
  // Trip selections update the count without opening the tray automatically; open it as a guest would.
  await page.locator('[data-trip-toggle]').click();
  await expect(page.locator('[data-trip-panel]')).toBeVisible();

  // The rooftop page uses the identical ID and dining group, so navigating between pages retains the selection.
  const roofResponse = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  expect(roofResponse).not.toBeNull();
  expect(roofResponse.status()).toBeLessThan(400);
  const roofAdd = page.locator('.page-hero-inner [data-trip-add][data-trip-id="rooftop-dining"]');
  await expect(roofAdd).toBeVisible();
  await expect(roofAdd).toHaveAttribute('data-trip-title', 'Rooftop dining');
  await expect(roofAdd).toHaveAttribute('data-trip-group', 'dining');
  await expect(roofAdd).toHaveAttribute('aria-pressed', 'true');
  await expect(page.locator('[data-trip-count]')).toHaveText('1');

  await page.locator('[data-trip-toggle]').click();
  const contactLink = page.locator('[data-trip-contact]');
  await expect(contactLink).toBeVisible();
  const contactTarget = new URL(await contactLink.getAttribute('href'), 'http://127.0.0.1:8080');
  expect(contactTarget.pathname).toBe('/contact.php');
  expect(contactTarget.searchParams.getAll('trip[]')).toEqual(['rooftop-dining']);
  expect(contactTarget.search).not.toMatch(/date|time|guests|email|phone/i);

  await contactLink.click();
  const submittedTarget = new URL(page.url());
  expect(submittedTarget.pathname).toBe('/contact.php');
  expect(submittedTarget.searchParams.getAll('trip[]')).toEqual(['rooftop-dining']);
  await expect(page.locator('.contact-trip-context')).toContainText('Rooftop dining');
  await expect(page.locator('#message')).toHaveValue(/these options for my Jaisalmer trip: Rooftop dining/);
  await expect(page.locator('form.contact-form')).toBeVisible();
  await expect(page.locator('#message')).not.toHaveValue(/table is booked|reservation is confirmed/i);
});

test('dining Trip actions and Contact context fit 320–1440px without horizontal overflow', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive dining Trip layout runs once with explicit viewports.');

  for (const width of [320, 390, 768, 1280, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    for (const route of ['/restaurant.php', '/roof.php', '/contact.php?trip%5B0%5D=rooftop-dining']) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' response at ' + width).not.toBeNull();
      expect(response.status(), route + ' is available at ' + width).toBeLessThan(400);
      if (route === '/restaurant.php') {
        await expect(page.locator('.restaurant-hero [data-trip-add][data-trip-id="rooftop-dining"]')).toBeVisible();
      } else if (route === '/roof.php') {
        await expect(page.locator('.page-hero-inner [data-trip-add][data-trip-id="rooftop-dining"]')).toBeVisible();
      } else {
        await expect(page.locator('.contact-trip-context')).toContainText('Rooftop dining');
      }
      const metrics = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(metrics.document, route + ' must not overflow at ' + width + 'px').toBeLessThanOrEqual(metrics.viewport + 1);
    }
  }
});
