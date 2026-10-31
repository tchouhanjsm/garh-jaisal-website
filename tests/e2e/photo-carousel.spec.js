const { test, expect } = require('@playwright/test');

const experienceRoutes = [
  '/desert-safari.php',
  '/guided-jaisalmer-tour.php',
  '/sunset-camel-safari.php',
  '/sleep-under-the-stars.php',
  '/swiss-tent-safari.php',
  '/cultural-safari.php',
  '/half-day-jaisalmer-tour.php',
  '/full-day-jaisalmer-tour.php',
];

test('published experience and tour hubs provide an accessible next/previous photo journey', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Experience route contracts run once on desktop-1280.');
  test.setTimeout(120_000);

  for (const route of experienceRoutes) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ': HTTP response').not.toBeNull();
    expect(response.status(), route + ': published route remains available').toBeLessThan(400);

    const gallery = page.locator('[data-photo-carousel]').first();
    await expect(gallery, route + ': shared photo gallery').toBeVisible();
    await expect(gallery.locator('[data-photo-carousel-controls]')).toBeVisible();

    const slides = gallery.locator('[data-photo-carousel-slide]');
    const slideCount = await slides.count();
    expect(slideCount, route + ': must have multiple photos').toBeGreaterThan(2);
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 1 of ' + slideCount);
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveAttribute('aria-live', 'polite');
    await expect(gallery.locator('[data-photo-carousel-slide]:visible')).toHaveCount(1);
    await expect(slides.first()).toHaveAttribute('aria-hidden', 'false');
    await expect(slides.nth(1)).toHaveAttribute('aria-hidden', 'true');

    const mediaPolicy = await slides.locator('img').evaluateAll(images => images.map(image => ({
      loading: image.getAttribute('loading'),
      decoding: image.getAttribute('decoding'),
      hasAlt: Boolean((image.getAttribute('alt') || '').trim()),
    })));
    expect(mediaPolicy.every(image => image.loading === 'lazy' && image.decoding === 'async' && image.hasAlt)).toBe(true);

    await gallery.locator('[data-photo-carousel-next]').click();
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 2 of ' + slideCount);
    await expect(gallery.locator('[data-photo-carousel-slide]:visible')).toHaveCount(1);
    await expect(slides.nth(0)).toHaveAttribute('aria-hidden', 'true');
    await expect(slides.nth(1)).toHaveAttribute('aria-hidden', 'false');
    await expect.poll(
      () => slides.nth(1).locator('img').evaluate(image => image.complete && image.naturalWidth > 0),
      { timeout: 15_000 }
    ).toBe(true);

    const nextButton = gallery.locator('[data-photo-carousel-next]');
    await nextButton.focus();
    await page.keyboard.press('ArrowRight');
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 3 of ' + slideCount);
    await expect(gallery.locator('[data-photo-carousel-slide]:visible')).toHaveCount(1);
    await page.keyboard.press('ArrowLeft');
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 2 of ' + slideCount);
    await gallery.locator('[data-photo-carousel-prev]').click();
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 1 of ' + slideCount);
    await gallery.locator('[data-photo-carousel-prev]').click();
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo ' + slideCount + ' of ' + slideCount);
    await expect(gallery.locator('[data-photo-carousel-slide]:visible')).toHaveCount(1);
    await expect(slides.nth(slideCount - 1)).toHaveAttribute('aria-hidden', 'false');
    await gallery.locator('[data-photo-carousel-next]').click();
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 1 of ' + slideCount);

    const blankAltCount = await slides.locator('img').evaluateAll(images =>
      images.filter(image => !image.getAttribute('alt') || !image.getAttribute('alt').trim()).length
    );
    expect(blankAltCount, route + ': all photo slides have meaningful alt text').toBe(0);
  }
});

test('room cards show two-photo room stories without changing booking and enquiry links', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Room card contracts run once on desktop-1280.');
  const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const cards = page.locator('.room-card');
  await expect(cards).toHaveCount(7);

  for (const card of await cards.all()) {
    const name = (await card.locator('h3').innerText()).trim();
    const gallery = card.locator('[data-photo-carousel]');
    await expect(gallery, name + ': room gallery').toBeVisible();
    await expect(gallery.locator('[data-photo-carousel-slide]')).toHaveCount(2);
    await expect(gallery.locator('[data-photo-carousel-slide]:visible')).toHaveCount(1);

    const booking = card.locator('a.room-book');
    const bookingUrl = new URL(await booking.getAttribute('href'));
    expect(bookingUrl.origin + bookingUrl.pathname).toBe('https://asiatech.in/booking_engine/index3');
    expect(bookingUrl.searchParams.get('token')).toBe('NjA2Ng==');
    await expect(card.locator('a.room-enquiry-link')).toHaveAttribute('href', /^contact\.php\?room=/);

    await gallery.locator('[data-photo-carousel-next]').click();
    await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 2 of 2');
    await expect(gallery.locator('[data-photo-carousel-slide]:visible')).toHaveCount(1);
    const secondImage = gallery.locator('[data-photo-carousel-slide]').nth(1).locator('img');
    await expect(secondImage).toBeVisible();
    await expect.poll(() => secondImage.evaluate(image => image.complete && image.naturalWidth > 0), { timeout: 15_000 }).toBe(true);
  }
});

test('restaurant carousel reuses the five curated photos and does not publish unconfirmed menu prices', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Restaurant content contract runs once on desktop-1280.');
  const response = await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const gallery = page.locator('.restaurant-gallery [data-photo-carousel]');
  await expect(gallery).toBeVisible();
  await expect(gallery.locator('[data-photo-carousel-slide]')).toHaveCount(5);
  await expect(gallery.locator('[data-photo-carousel-controls]')).toBeVisible();

  await expect(page.locator('main')).toContainText('Ask about the current menu.');
  await expect(page.locator('main')).not.toContainText(/₹\s?\d/);
  await gallery.locator('[data-photo-carousel-next]').click();
  await expect(gallery.locator('[data-photo-carousel-status]')).toHaveText('Photo 2 of 5');
});

test('photo carousels preserve the full image list when JavaScript is disabled', async ({ browser }) => {
  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    javaScriptEnabled: false,
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
  });
  try {
    const page = await context.newPage();
    const response = await page.goto('/swiss-tent-safari.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    const gallery = page.locator('[data-photo-carousel]').first();
    const slides = gallery.locator('[data-photo-carousel-slide]');
    expect(await slides.count()).toBeGreaterThan(2);
    await expect(slides.first()).toBeVisible();
    await expect(gallery.locator('[data-photo-carousel-controls]')).toBeHidden();
    expect(await slides.evaluateAll(nodes => nodes.filter(node => getComputedStyle(node).display !== 'none').length))
      .toBeGreaterThan(2);
  } finally {
    await context.close();
  }
});

test('carousel layout does not cause horizontal overflow at the supported viewports', async ({ page }, testInfo) => {
  test.skip(!['mobile-320', 'mobile-390', 'desktop-1280', 'desktop-1440'].includes(testInfo.project.name), 'Supported viewport coverage.');
  for (const route of ['/swiss-tent-safari.php', '/desert-safari.php', '/guided-jaisalmer-tour.php', '/room.php', '/restaurant.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    const metrics = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(metrics.document, route + ': no carousel overflow at ' + testInfo.project.name)
      .toBeLessThanOrEqual(metrics.viewport + 1);
  }
});
