const { test, expect } = require('@playwright/test');

test('Rooftop hero prioritises the existing table enquiry journey', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Rooftop enquiry handoff is checked once.');

  for (const width of [390, 1280]) {
    await page.setViewportSize({ width, height: 900 });
    const response = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Rooftop page must return an HTTP response').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const hero = page.locator('main.roof-page .page-hero-inner');
    const enquiry = hero.getByRole('link', { name: 'Enquire about a table', exact: true });
    await expect(enquiry).toHaveAttribute('href', '#reserve');
    await expect(enquiry).toHaveAttribute('data-cta-id', 'enquiry-rooftop-table');
    await expect(enquiry).toHaveAttribute('data-cta-location', 'rooftop-dining-hero');
    await expect(hero.getByRole('link', { name: /^(?:View|Ask about) the menu$/ }))
      .toHaveAttribute('href', '#menu');
    await expect(hero.locator('[data-cta-id="booking-rooftop-dining"]')).toHaveCount(0);
    await expect(hero.locator('[data-trip-add][data-trip-id="rooftop-dining"]')).toHaveCount(0);
    const tripAction = page.locator('.roof-trip-action [data-trip-add][data-trip-id="rooftop-dining"]');
    await expect(tripAction).toBeVisible();
    await expect(tripAction).toHaveAttribute('data-trip-title', 'Rooftop dining');
  }

  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  await page.getByRole('link', { name: 'Enquire about a table', exact: true }).click();
  await expect(page).toHaveURL(/\/roof\.php#reserve$/);
  await expect(page.locator('#reserve h2')).toHaveText('Request a table.');
  await expect(page.locator('#reserve .restaurant-policy'))
    .toContainText('not a confirmed booking');
  await expect(page.locator('#reserve button[type="submit"]')).toHaveText('Request a table');
  const time = page.locator('#reserve select[name="time"]');
  await expect(time.locator('option')).toHaveCount(12);
  await expect(time.locator('option').nth(2)).toHaveAttribute('value', '17:30');
  await expect(time.locator('option').last()).toHaveAttribute('value', '22:00');

  const reserveTab = page.getByRole('tab', { name: 'Book a Table' });
  await expect(reserveTab).toHaveAttribute('aria-selected', 'true');
  await reserveTab.press('ArrowLeft');
  const menuTab = page.getByRole('tab', { name: 'Menu', exact: true });
  await expect(menuTab).toBeFocused();
  await menuTab.press('Space');
  await expect(page).toHaveURL(/\/roof\.php#menu$/);
  await expect(page.locator('#menu')).toBeVisible();
  await expect(page.locator('#reserve')).toBeHidden();
  await page.goBack();
  await expect(page.locator('#reserve')).toBeVisible();

  // A rejected local request exercises the shared PHP path without sending an enquiry.
  await page.locator('#reserve #name').fill('Rooftop Test');
  await time.selectOption('18:30');
  await page.locator('#reserve button[type="submit"]').click();
  await expect(page).toHaveURL(/\/roof\.php#reserve$/);
  await expect(page.locator('#reserve #whatsapp')).toHaveAttribute('aria-invalid', 'true');
  await expect(time).toHaveValue('18:30');
  await expect(page.locator('#reserve #whatsapp')).toBeFocused();
});

test('Rooftop links and the form work without JavaScript', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Progressive enhancement is checked once.');
  const context = await browser.newContext({ javaScriptEnabled: false, baseURL: testInfo.project.use.baseURL });
  try {
    const page = await context.newPage();
    await page.goto('/roof.php#reserve');
    await expect(page.locator('#reserve form')).toBeVisible();
    await page.locator('#roof-menu-tab').click();
    await expect(page).toHaveURL(/\/roof\.php#menu$/);
    await expect(page.locator('#menu')).toBeVisible();
  } finally {
    await context.close();
  }
});
