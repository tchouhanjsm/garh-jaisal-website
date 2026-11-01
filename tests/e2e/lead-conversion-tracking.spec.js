const { test, expect } = require('@playwright/test');

test('contact enquiry-start analytics carry the selected experience without guest data', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Analytics context assertion runs once on desktop-1280');
  const response = await page.goto('/contact.php?experience=sunset-camel-safari', { waitUntil: 'domcontentloaded' });
  expect(response, 'Contextual service enquiry should return a response').not.toBeNull();
  expect(response.status(), 'Contextual service enquiry should load').toBeLessThan(400);
  const context = page.locator('.contact-experience-context');
  await expect(context).toBeVisible();
  await expect(context).toHaveAttribute('data-experience-slug', 'sunset-camel-safari');
  await expect(page.locator('input[name="enquiry_context_token"]')).toHaveValue(/^[a-f0-9]{32}$/);
  await page.evaluate(() => {
    window.__gjAnalyticsEvents = [];
    window.gtag = (...args) => window.__gjAnalyticsEvents.push(args);
  });
  await page.getByLabel('Name', { exact: true }).focus();
  const calls = await page.evaluate(() => window.__gjAnalyticsEvents);
  const eventCall = calls.find(call => call[0] === 'event' && call[1] === 'enquiry_start');
  expect(eventCall, 'Focusing the contextual form should start one measured enquiry').toBeTruthy();
  expect(eventCall[2]).toMatchObject({
    page_context: 'contact',
    enquiry_context: 'experience',
    experience_slug: 'sunset-camel-safari',
    utm_source: 'hotel_website',
    utm_medium: 'website',
  });
  expect(eventCall[2]).not.toHaveProperty('name');
  expect(eventCall[2]).not.toHaveProperty('email');
  expect(eventCall[2]).not.toHaveProperty('phone');
  expect(eventCall[2]).not.toHaveProperty('message');
  // Never submit a form or send an enquiry from this regression test.
});

test('thank-you page without a successful enquiry does not emit generate_lead', async ({ page }) => {
  const response = await page.goto('/thank-you.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Thank-you page should return a response').not.toBeNull();
  expect(response.status(), 'Thank-you page should load').toBeLessThan(400);
  await expect(page.locator('h1')).toHaveText('Thank you.');
  await expect(page.getByText('Thank you for visiting Hotel Garh Jaisal Haveli in Jaisalmer Fort.')).toBeVisible();
  const leadEvents = await page.evaluate(() => Array.from(window.dataLayer || []).some((entry) => {
    const args = Array.from(entry);
    return args[0] === 'event' && args[1] === 'generate_lead';
  }));
  expect(leadEvents, 'A bare page visit must not be counted as a lead').toBe(false);
});


test('rooftop table CTA emits a privacy-safe table enquiry intent with stable attribution', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Rooftop CTA analytics assertion runs once on desktop-1280.');

  const capturedEvents = [];
  await page.exposeFunction('__captureGtagEvent', (...args) => {
    capturedEvents.push(args);
  });

  const response = await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Rooftop dining page should return a response').not.toBeNull();
  expect(response.status(), 'Rooftop dining page should load').toBeLessThan(400);

  const cta = page.locator('.page-hero-inner').getByRole('link', { name: 'Enquire about a table', exact: true });
  await expect(cta).toHaveAttribute('href', 'restaurant.php#reserve');
  await expect(cta).toHaveAttribute('data-cta-id', 'enquiry-rooftop-table');
  await expect(cta).toHaveAttribute('data-cta-location', 'rooftop-dining-hero');

  await page.evaluate(() => {
    window.gtag = (...args) => window.__captureGtagEvent(...args);
  });
  await cta.click();
  await expect(page).toHaveURL(/\/restaurant\.php#reserve$/);
  await expect.poll(() => capturedEvents.some(call => call[0] === 'event' && call[1] === 'table_enquiry_click')).toBe(true);

  const eventCall = capturedEvents.find(call => call[0] === 'event' && call[1] === 'table_enquiry_click');
  expect(eventCall[2]).toMatchObject({
    page_path: '/roof.php',
    page_context: 'dining',
    cta_id: 'enquiry-rooftop-table',
    cta_location: 'rooftop-dining-hero',
    cta_label: 'Enquire about a table',
  });
  expect(eventCall[2]).not.toHaveProperty('name');
  expect(eventCall[2]).not.toHaveProperty('email');
  expect(eventCall[2]).not.toHaveProperty('phone');
  expect(eventCall[2]).not.toHaveProperty('message');
  // This measures a click to the table request form, not a confirmed reservation.
});

test('restaurant hero table enquiry emits a privacy-safe intent with stable attribution', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Restaurant hero attribution assertion runs once on desktop-1280.');
  const capturedEvents = [];
  await page.exposeFunction('__captureRestaurantGtag', (...args) => capturedEvents.push(args));

  const response = await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Restaurant page should return a response').not.toBeNull();
  expect(response.status(), 'Restaurant page should load').toBeLessThan(400);
  const cta = page.locator('.restaurant-hero').getByRole('link', { name: 'Enquire about a table', exact: true });
  await expect(cta).toHaveAttribute('href', '#reserve');
  await expect(cta).toHaveAttribute('data-cta-id', 'enquiry-restaurant-table');
  await expect(cta).toHaveAttribute('data-cta-location', 'restaurant-hero');

  await page.evaluate(() => {
    window.gtag = (...args) => window.__captureRestaurantGtag(...args);
  });
  await cta.click();
  await expect(page).toHaveURL(/\\/restaurant\\.php#reserve$/);
  await expect.poll(() => capturedEvents.some(call => call[0] === 'event' && call[1] === 'table_enquiry_click')).toBe(true);

  const eventCall = capturedEvents.find(call => call[0] === 'event' && call[1] === 'table_enquiry_click');
  expect(eventCall[2]).toMatchObject({
    page_path: '/restaurant.php',
    page_context: 'dining',
    cta_id: 'enquiry-restaurant-table',
    cta_location: 'restaurant-hero',
    cta_label: 'Enquire about a table',
  });
  expect(eventCall[2]).not.toHaveProperty('name');
  expect(eventCall[2]).not.toHaveProperty('email');
  expect(eventCall[2]).not.toHaveProperty('phone');
  expect(eventCall[2]).not.toHaveProperty('message');
  // The event records a click to the request form, never a confirmed reservation.
});
