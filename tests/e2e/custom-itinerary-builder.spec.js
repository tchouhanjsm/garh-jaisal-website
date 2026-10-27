const { test, expect } = require('@playwright/test');

test('custom itinerary builder combines interests and prepares an explicit WhatsApp draft', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Builder interaction runs once on desktop-1280.');
  await page.goto('/custom-itinerary.php', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: 'Build a Jaisalmer route around your interests.' })).toBeVisible();
  await expect(page.getByRole('group', { name: 'Interests to explore' })).toBeVisible();
  await expect(page.locator('.custom-itinerary-booking-note')).toContainText('does not book a tour');
  const whatsapp = page.locator('#custom-itinerary-whatsapp');
  await expect(whatsapp).toHaveAttribute('aria-disabled', 'true');
  const initialUrl = page.url();
  // The anchor is intentionally disabled until the draft has valid content; invoke its handler directly to verify the guard.
  await whatsapp.evaluate(link => link.click());
  await expect(page).toHaveURL(initialUrl);

  await page.getByLabel('Fort & historic architecture').check();
  await page.getByLabel('Markets, crafts & local life').check();
  await page.locator('#custom-travel-date').fill('2027-02-15');
  await page.locator('#custom-time-available').selectOption({ label: 'Half a day' });
  await page.locator('#custom-group-size').fill('4');
  await page.locator('#custom-travel-style').selectOption({ label: 'A mix of walking and transport' });
  await page.locator('#custom-places').fill('Gadisar Lake, local crafts and any other suitable stops');
  await page.locator('#custom-preferences').fill('A relaxed pace with time for photos');

  const preview = page.locator('#custom-itinerary-draft');
  await expect(preview).toHaveValue(/Fort and historic architecture/);
  await expect(preview).toHaveValue(/Markets, crafts and local life/);
  await expect(preview).toHaveValue(/Travel date or period: 2027-02-15/);
  await expect(preview).toHaveValue(/Time available: Half a day/);
  await expect(preview).toHaveValue(/Group size: 4/);
  await expect(preview).toHaveValue(/Gadisar Lake, local crafts and any other suitable stops/);
  await expect(preview).toHaveValue(/enquiry only, not a booking/);
  await expect(whatsapp).toHaveAttribute('aria-disabled', 'false');

  const destination = await whatsapp.evaluate(link => ({ href: link.href, target: link.target }));
  const targetUrl = new URL(destination.href);
  expect(targetUrl.hostname).toMatch(/^(?:wa\.me|api\.whatsapp\.com)$/);
  expect(destination.target).toBe('_blank');
  expect(targetUrl.searchParams.get('text')).toContain('Fort and historic architecture');
  expect(targetUrl.searchParams.get('text')).toContain('Markets, crafts and local life');
  expect(targetUrl.searchParams.get('text')).toContain('Gadisar Lake, local crafts and any other suitable stops');
  await expect(page).toHaveURL(initialUrl);

  const popupPromise = page.waitForEvent('popup');
  await whatsapp.click();
  const popup = await popupPromise;
  const popupDestination = new URL(popup.url());
  expect(popupDestination.hostname).toMatch(/^(?:wa\.me|api\.whatsapp\.com|web\.whatsapp\.com|www\.whatsapp\.com)$/);
  expect(popupDestination.searchParams.get('text')).toContain('enquiry only, not a booking');
});

test('custom itinerary page stays responsive without horizontal overflow', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Responsive custom itinerary coverage.');
  const response = await page.goto('/custom-itinerary.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  await expect(page.getByRole('heading', { level: 1, name: 'Build a Jaisalmer route around your interests.' })).toBeVisible();
  await expect(page.locator('#custom-itinerary-form')).toBeVisible();
  await expect(page.locator('#custom-itinerary-draft')).toBeVisible();
  const dimensions = await page.evaluate(() => ({
    viewport: document.documentElement.clientWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, 'Custom itinerary page must not overflow horizontally at ' + testInfo.project.name)
    .toBeLessThanOrEqual(dimensions.viewport + 1);
  await expect(page.getByRole('link', { name: 'Use the contact page instead' })).toHaveAttribute('href', 'contact.php?topic=custom-itinerary');
});
