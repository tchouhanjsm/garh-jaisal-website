const { test, expect } = require('@playwright/test');

test('restaurant only publishes approved menu content and keeps the booklet usable when enabled', async ({ page }) => {
  await page.addInitScript(() => {
    window.__restaurantPrintCalled = false;
    window.print = () => { window.__restaurantPrintCalled = true; };
  });
  const response = await page.goto('/restaurant.php#menu', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const booklet = page.locator('[data-menu-booklet]');
  if (await booklet.count() === 0) {
    const html = await page.content();
    for (const unapprovedValue of [
      'Starters',
      'Laal Maas Tikka',
      'Pyaaz Kachori',
      'Dal Baati Churma',
      'Price on request',
    ]) {
      expect(html, 'Unapproved menu content must not reach rendered HTML: ' + unapprovedValue)
        .not.toContain(unapprovedValue);
    }
    await expect(page.locator('.restaurant-feature-card')).toHaveCount(0);
    await expect(page.locator('#menu')).toContainText('We are confirming the current dishes, descriptions, prices and dietary options');
    await expect(page.getByRole('link', { name: 'Ask about the current menu' }))
      .toHaveAttribute('href', 'contact.php?topic=dining');
    const dimensions = await page.evaluate(() => ({
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document, 'Pending-menu fallback must not cause horizontal overflow')
      .toBeLessThanOrEqual(dimensions.viewport + 1);
    return;
  }
  await expect(booklet).toHaveClass(/is-enhanced/);
  await expect(booklet.locator('[data-menu-page]:visible')).toHaveCount(1);
  await expect(booklet.locator('[data-menu-page-status]')).toHaveText('Page 1 of 4: Starters');
  await expect(booklet.locator('[data-menu-previous]')).toBeDisabled();
  await expect(booklet.locator('[data-menu-next]')).toBeEnabled();

  await booklet.getByRole('link', { name: 'Desserts' }).click();
  await expect(page).toHaveURL(/#desserts$/);
  await expect(booklet.locator('[data-menu-page]:visible')).toHaveCount(1);
  await expect(booklet.locator('#desserts')).toBeVisible();
  await expect(booklet.locator('#starters')).toBeHidden();
  await expect(booklet.locator('[data-menu-page-status]')).toHaveText('Page 4 of 4: Desserts');
  await expect(booklet.locator('[data-menu-next]')).toBeDisabled();

  await booklet.locator('[data-menu-previous]').click();
  await expect(booklet.locator('[data-menu-page-status]')).toHaveText('Page 3 of 4: Dal, Bati & Breads');
  await booklet.locator('[data-menu-tab][href="#starters"]').click();

  const vegetarianOnly = page.getByRole('button', { name: 'Vegetarian only' });
  await vegetarianOnly.click();
  await expect(vegetarianOnly).toHaveAttribute('aria-pressed', 'true');
  await expect(page.getByRole('heading', { name: 'Laal Maas Tikka' })).toBeHidden();
  await expect(page.getByRole('heading', { name: 'Pyaaz Kachori' })).toBeVisible();

  await booklet.locator('[data-menu-print]').click();
  expect(await page.evaluate(() => window.__restaurantPrintCalled)).toBe(true);

  const dimensions = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, 'Restaurant menu booklet must not cause horizontal overflow').toBeLessThanOrEqual(dimensions.viewport + 1);
});

test('restaurant directions QR, map and Instagram link use the verified destinations', async ({ page }) => {
  const response = await page.goto('/restaurant.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const archFigure = page.locator('.restaurant-gallery figure:first-child');
  const archRadius = await archFigure.evaluate(element => getComputedStyle(element).borderTopLeftRadius);
  expect(archRadius, 'The first restaurant gallery image uses the approved arch crop').toContain('48%');

  const directions = page.locator('.restaurant-find-us');
  const qr = directions.getByRole('img', { name: "QR code to open Google Maps directions to Garh Jaisal's Kitchen" });
  await expect(qr).toHaveAttribute('src', 'img/restaurant-maps-qr.svg');
  await expect(qr).toHaveAttribute('width', '370');
  await expect(qr).toHaveAttribute('height', '370');
  const qrResponse = await page.request.get('/img/restaurant-maps-qr.svg');
  expect(qrResponse.ok()).toBe(true);
  const qrSvg = await qrResponse.text();
  expect(qrSvg).toContain('https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8');
  expect(qrSvg).toContain('viewBox="0 0 37 37"');
  expect(qrSvg).toContain('shape-rendering="crispEdges"');
  expect(qrSvg).toContain('<path');

  await expect(directions.locator('iframe')).toHaveAttribute('title', 'Google Maps location of Garh Jaisal’s Kitchen inside Jaisalmer Fort');
  await expect(directions.getByRole('link', { name: 'Open directions in Google Maps ↗' }))
    .toHaveAttribute('href', 'https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8');

  const follow = page.getByRole('link', { name: /Follow @garhjaisal/ });
  await expect(follow).toHaveAttribute('href', 'https://www.instagram.com/garhjaisal/');
  await expect(follow).toHaveAttribute('target', '_blank');
  await expect(follow).toHaveAttribute('rel', /noopener/);

  const dimensions = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, 'Restaurant discovery sections must not cause horizontal overflow').toBeLessThanOrEqual(dimensions.viewport + 1);
});
