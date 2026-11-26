const { test, expect } = require('@playwright/test');

const proposedTopics = [
  'heritage-havelis',
  'jain-temples',
  'lake-cenotaph-photo',
  'kuldhara-landscape',
  'natural-history',
  'longewala-history',
];

test('Car & City hub separates existing options from proposed itinerary ideas and offers clear next steps', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Car & City journey assertions run once on desktop-1280.');

  const response = await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  await expect(page.getByRole('heading', { level: 1, name: 'Explore Jaisalmer and plan your route.' })).toBeVisible();
  await expect(page.locator('.car-city-route-grid .car-city-route')).toHaveCount(3);
  await expect(page.locator('.car-city-route[href="guided-jaisalmer-tour.php"]')).toBeVisible();
  await expect(page.locator('.car-city-route[href="car-rental.php"]')).toBeVisible();
  await expect(page.locator('.car-city-route[href="custom-itinerary.php"]')).toBeVisible();

  const ideaSection = page.locator('#proposed-itineraries');
  await expect(ideaSection.locator('.tour-idea-card')).toHaveCount(proposedTopics.length);
  await expect(ideaSection.locator('p.tour-ideas-disclaimer')).toContainText('not bookable products');
  await expect(ideaSection.locator('p.tour-ideas-disclaimer')).toContainText('not a complete list');
  await expect(ideaSection.locator('.tour-ideas-custom-builder a[href="custom-itinerary.php"]')).toBeVisible();
  await expect(ideaSection.locator('p.tour-ideas-disclaimer')).toContainText('does not reserve a tour');

  await expect(ideaSection.locator('details.tour-ideas-disclaimer summary')).toBeVisible();
  await ideaSection.locator('details.tour-ideas-disclaimer summary').click();
  await expect(ideaSection.locator('a[href="longewala-tour.php"]')).toBeVisible();
  await expect(ideaSection.locator('a[href="rajasthan-tours.php"]')).toBeVisible();

  for (const topic of proposedTopics) {
    const link = ideaSection.locator('a[href="contact.php?topic=' + topic + '"]');
    await expect(link, topic + ': contextual enquiry remains available').toBeVisible();
    await expect(link).toContainText('Enquire about this idea');
    await expect(link).toHaveAttribute('aria-label', /^Enquire about/);
  }

  await expect(page.locator('[data-stay-booking-note] a').filter({ hasText: 'Check room availability' }))
    .toHaveAttribute('href', /^https:\/\/asiatech\.in\/booking_engine\/index3\?/);
});

test('Car & City hub stays usable at supported widths and its local photos load', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Supported viewport coverage.');

  const response = await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const metrics = await page.evaluate(() => ({
    viewport: document.documentElement.clientWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(metrics.document, 'Car & City hub must not introduce horizontal overflow at ' + testInfo.project.name)
    .toBeLessThanOrEqual(metrics.viewport + 1);

  await page.evaluate(async () => {
    const images = Array.from(document.querySelectorAll('main img'));
    images.forEach(image => { image.loading = 'eager'; });
    await Promise.all(images.map(image => image.decode().catch(() => undefined)));
  });
  const imageStates = await page.locator('main img').evaluateAll(images => images.map(img => ({
    alt: img.getAttribute('alt'),
    loaded: img.complete && img.naturalWidth > 0,
  })));
  expect(imageStates.every(image => image.alt !== null && image.loaded), 'Car & City images have alt text and load').toBeTruthy();
});


test('custom itinerary rejects past dates and invalid traveller counts before handoff', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Test narrow mobile and desktop.');
  await page.goto('/custom-itinerary.php?idea=golden-city');
  const link = page.locator('#custom-itinerary-whatsapp');
  const date = page.locator('#custom-travel-date');
  const travellers = page.locator('#custom-group-size');
  await expect(link).toHaveAttribute('aria-disabled', 'false');
  await date.fill('2000-01-01');
  await expect(link).toHaveAttribute('aria-disabled', 'true');
  await date.fill('2099-01-01');
  await travellers.fill('0');
  await expect(link).toHaveAttribute('aria-disabled', 'true');
  await travellers.fill('2');
  await expect(link).toHaveAttribute('aria-disabled', 'false');
  expect(new URL(await link.getAttribute('href')).searchParams.get('text')).toContain('Group size: 2');
});

test('malformed contact arrays are rejected before CAPTCHA or email processing', async ({ request }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Server validation is viewport independent.');
  for (const field of ['csrf_token', 'enquiry_context_token', 'name', 'email', 'phone', 'message', 'consent', 'g-recaptcha-response']) {
    const response = await request.post('/mail.php', {
      headers: { 'content-type': 'application/x-www-form-urlencoded' },
      data: encodeURIComponent(field + '[]') + '=synthetic', maxRedirects: 0,
    });
    expect(response.status()).toBe(303);
    expect(response.headers().location).toBe('contact.php?error=invalid');
  }
});


test('Rajasthan enquiry clearly distinguishes car-only and car-with-hotels arrangements', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Test narrow mobile and desktop.');
  await page.goto('/custom-itinerary.php?mode=rajasthan&idea=western-rajasthan');
  const link = page.locator('#custom-itinerary-whatsapp');
  await expect(link).toHaveAttribute('data-cta-id', 'whatsapp-rajasthan-itinerary');
  const service = page.getByLabel('Travel arrangement');
  for (const option of ['Private car with driver', 'Private car with accommodation arranged']) {
    await service.selectOption(option);
    await expect(link).toHaveAttribute('aria-disabled', 'false');
    expect(new URL(await link.getAttribute('href')).searchParams.get('text')).toContain('Travel arrangement: ' + option);
  }
  await page.goto('/custom-itinerary.php?idea=golden-city');
  await expect(page.locator('#custom-itinerary-whatsapp')).toHaveAttribute('data-cta-id', 'whatsapp-jaisalmer-itinerary');
});


test('travel agents can request a rate sheet or tailored quotation from Contact', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Verify narrow mobile and desktop.');
  await page.goto('/contact.php');
  await page.locator('[data-cta-id="enquiry-travel-trade"]').click();
  await expect(page.locator('.contact-topic-context')).toContainText('Travel trade enquiry');
  const message = page.locator('#message');
  await expect(message).toHaveValue(/FIT \/ group \/ seasonal rate sheet \/ multi-service quotation/);
  await expect(message).toHaveValue(/Meal plan:/);
  await expect(message).toHaveValue(/payment and cancellation terms/);
  await expect(page.locator('[data-cta-id="enquiry-travel-trade"]')).toHaveCount(0);
});


test('itinerary draft can be copied without WhatsApp or persisting guest text', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Test narrow mobile and desktop.');
  await page.addInitScript(() => {
    Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText: async (value) => { window.__copiedDraft = value; } } });
  });
  await page.goto('/custom-itinerary.php?idea=golden-city');
  const copy = page.getByRole('button', { name: 'Copy enquiry draft' });
  await expect(copy).toBeEnabled();
  await copy.click();
  await expect(page.locator('#custom-itinerary-status')).toContainText('Draft copied');
  expect(await page.evaluate(() => window.__copiedDraft)).toBe(await page.locator('#custom-itinerary-draft').inputValue());
  await page.locator('#custom-group-size').fill('0');
  await expect(copy).toBeDisabled();
  await expect(page.getByRole('link', { name: 'Use the contact page instead' })).toHaveAttribute('href', 'contact.php?topic=custom-itinerary');
});


test('holiday discovery offers six visible enquiry choices with accessible touch targets', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'desktop-1280'].includes(testInfo.project.name), 'Test compact mobile and desktop.');
  await page.goto('/pack.php');
  const section = page.locator('#holiday-ideas');
  await expect(section.getByRole('heading', { name: 'Tailor-made holiday ideas' })).toBeVisible();
  await expect(section).toContainText('not fixed packages or guaranteed inclusions');
  const cards = section.locator('.holiday-ideas-grid a');
  await expect(cards).toHaveCount(6);
  const sizes = await cards.evaluateAll(links => links.map(link => ({ width: link.getBoundingClientRect().width, height: link.getBoundingClientRect().height })));
  expect(sizes.every(size => size.width >= 44 && size.height >= 44)).toBeTruthy();
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1)).toBeTruthy();
  await cards.filter({ hasText: 'Romantic Jaisalmer' }).click();
  await expect(page.locator('#custom-places')).toHaveValue(/Romantic Jaisalmer/);
  await page.goto('/pack.php');
  await section.locator('[data-cta-id="holiday-rajasthan"]').click();
  await expect(page.locator('#custom-places')).toHaveValue(/Rajasthan Heritage Journey/);
});

test('room planning cross-links to the existing holiday discovery page', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Link destination independent of viewport.');
  await page.goto('/room.php');
  await expect(page.locator('.planning-path').getByRole('link', { name: /Tailor-made holidays/ })).toHaveAttribute('href', 'pack.php');
});
