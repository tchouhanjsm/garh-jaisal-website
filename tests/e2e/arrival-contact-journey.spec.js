const { test, expect } = require('@playwright/test');

test('Directions arrival CTA opens a contextual, prefilled Contact enquiry', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Arrival contact journey assertions run once on desktop-1280');

  const directionsResponse = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  expect(directionsResponse, 'Directions should return an HTTP response').not.toBeNull();
  expect(directionsResponse.status()).toBeLessThan(400);

  const arrival = page.locator('.arrival-contact');
  const contactLink = arrival.getByRole('link', { name: 'Contact the hotel', exact: true });
  await expect(contactLink).toHaveAttribute('href', 'contact.php?topic=arrival');
  await contactLink.click();

  await expect(page).toHaveURL(/\/contact\.php\?topic=arrival$/);
  await expect(page.getByRole('heading', { level: 2, name: 'Send an enquiry' })).toBeVisible();

  const topicContext = page.locator('.contact-topic-context');
  await expect(topicContext).toContainText('Arrival arrangements');
  await expect(topicContext).toContainText('check-in date and expected arrival time');
  await expect(topicContext).toContainText('until the hotel confirms it');

  const message = page.locator('#message');
  const draft = await message.inputValue();
  expect(draft).toContain('My check-in date is: [add date].');
  expect(draft).toContain('My expected arrival time is: [add time].');
  expect(draft).toContain('Please confirm current reception hours');
  expect(draft).toContain('whether meet-and-assist or luggage help can be arranged');
  expect(draft).toContain('official check-in/check-out times');
  expect(draft).not.toMatch(/meet-and-assist is available|luggage help is available/i);

  // Check the handoff only; this regression never submits a real enquiry.
  await expect(page.locator('form.contact-form')).toHaveAttribute('action', 'mail.php');
  await expect(page.getByRole('button', { name: 'Send message' })).toBeVisible();
});
