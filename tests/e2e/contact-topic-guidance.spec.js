const { test, expect } = require('@playwright/test');

test('allowlisted contact topics provide concise, specific enquiry guidance', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Contact topic guidance runs once on desktop-1280');

  const cases = [
    {
      topic: 'room-availability',
      label: 'Room availability and facilities',
      message: /My travel dates are:/,
      details: /Preferred room or facilities you need to confirm/,
    },
    {
      topic: 'car-arrangements',
      label: 'Car and transfer arrangements',
      message: /Pickup and destination:/,
      details: /Number of passengers and luggage/,
    },
    {
      topic: 'arrival',
      label: 'Arrival arrangements',
      message: /My check-in date is:/,
      details: /Travel date and expected arrival time/,
    },
    {
      topic: 'dining',
      label: 'Rooftop dining',
      message: /Preferred date:/,
      details: /Number of guests/,
    },
  ];

  for (const entry of cases) {
    const response = await page.goto('/contact.php?topic=' + entry.topic, { waitUntil: 'domcontentloaded' });
    expect(response, entry.topic + ': expected HTTP response').not.toBeNull();
    expect(response.status(), entry.topic + ': contact route is available').toBe(200);

    await expect(page.locator('.contact-topic-context strong')).toHaveText(entry.label);
    await expect(page.locator('#contact-topic-details-title')).toHaveText('Helpful details to include');
    const details = page.locator('.contact-topic-details li');
    await expect(details).toHaveCount(3);
    await expect(page.locator('#message')).toHaveValue(entry.message);
    await expect(page.locator('.contact-topic-details')).toContainText(entry.details);
    await expect(page.locator('form.contact-form')).toBeVisible();
    await expect(page.locator('#name')).toBeEmpty();
    await expect(page.locator('#email')).toBeEmpty();
  }
});

test('unknown contact topics fall back safely without rendering invented guidance', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Unknown topic safety runs once on desktop-1280');
  const response = await page.goto('/contact.php?topic=not-a-published-topic', { waitUntil: 'domcontentloaded' });
  expect(response, 'Contact route returns a response').not.toBeNull();
  expect(response.status()).toBe(200);
  await expect(page.locator('.contact-topic-details')).toHaveCount(0);
  await expect(page.locator('.contact-topic-context')).toHaveCount(0);
  await expect(page.locator('form.contact-form')).toBeVisible();
});
