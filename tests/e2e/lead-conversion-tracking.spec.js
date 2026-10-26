const { test, expect } = require('@playwright/test');

test('contact enquiry-start analytics carry the selected experience without guest data', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Analytics context assertion runs once on desktop-1280');
  const response = await page.goto('/contact.php?experience=sunset-camel-safari', { waitUntil: 'domcontentloaded' });
  expect(response, 'Contextual service enquiry should return a response').not.toBeNull();
  expect(response.status(), 'Contextual service enquiry should load').toBeLessThan(400);
  const context = page.locator('.contact-experience-context');
  await expect(context).toBeVisible();
  await expect(context).toHaveAttribute('data-experience-slug', 'sunset-camel-safari');
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
