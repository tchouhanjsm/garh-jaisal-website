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
  const categoryLinks = booklet.locator('[data-menu-tab]');
  const categories = await categoryLinks.allTextContents();
  const dishRows = booklet.locator('.restaurant-dish');
  await expect(dishRows).toHaveCount(142);
  await expect(booklet.locator('[data-menu-page-status]')).toHaveText(`Page 1 of ${categories.length}: ${categories[0]}`);
  await expect(booklet.locator('[data-menu-previous]')).toBeDisabled();
  await expect(booklet.locator('[data-menu-next]')).toBeEnabled();

  await categoryLinks.last().click();
  const lastCategoryHash = await categoryLinks.last().getAttribute('href');
  expect(new URL(page.url()).hash).toBe(lastCategoryHash);
  await expect(booklet.locator('[data-menu-page]:visible')).toHaveCount(1);
  await expect(booklet.locator(lastCategoryHash)).toBeVisible();
  await expect(booklet.locator('[data-menu-page]').first()).toBeHidden();
  await expect(booklet.locator('[data-menu-page-status]')).toHaveText(`Page ${categories.length} of ${categories.length}: ${categories.at(-1)}`);
  await expect(booklet.locator('[data-menu-next]')).toBeDisabled();

  await booklet.locator('[data-menu-previous]').click();
  await expect(booklet.locator('[data-menu-page-status]')).toHaveText(`Page ${categories.length - 1} of ${categories.length}: ${categories.at(-2)}`);
  await categoryLinks.first().click();

  const vegetarianOnly = page.getByRole('button', { name: 'Vegetarian only' });
  await vegetarianOnly.click();
  await expect(vegetarianOnly).toHaveAttribute('aria-pressed', 'true');
  await expect(booklet.locator('.restaurant-dish[data-veg="0"]:not([hidden])')).toHaveCount(0);
  expect(await booklet.locator('.restaurant-dish[data-veg="1"]:not([hidden])').count()).toBeGreaterThan(0);
  await expect(booklet.locator('.restaurant-dish .is-vegan')).toHaveCount(0);
  await expect(page.locator('#menu')).toContainText('Ask the kitchen to confirm vegan');

  await booklet.locator('[data-menu-print]').click();
  expect(await page.evaluate(() => window.__restaurantPrintCalled)).toBe(true);

  const dimensions = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, 'Restaurant menu booklet must not cause horizontal overflow').toBeLessThanOrEqual(dimensions.viewport + 1);

  const menuText = await dishRows.allTextContents();
  const dalFry = dishRows.filter({ has: page.getByRole('heading', { name: 'Dal Fry', exact: true, includeHidden: true }) });
  await expect(dalFry).toContainText('₹250');
  await expect(dalFry).toContainText('Vegetarian');
  const lalMaas = dishRows.filter({ has: page.getByRole('heading', { name: 'Lal Maas', exact: true, includeHidden: true }) });
  await expect(lalMaas).toContainText('₹700');
  await expect(lalMaas).toContainText('Non-vegetarian');
  const rooftopResponse = await page.goto('/roof.php#menu', { waitUntil: 'domcontentloaded' });
  expect(rooftopResponse.ok()).toBe(true);
  await expect(booklet).toHaveClass(/is-enhanced/);
  expect(await dishRows.allTextContents()).toEqual(menuText);
  await expect(booklet.locator('.restaurant-dish .is-vegetarian').first()).toBeAttached();
  await expect(booklet.locator('.restaurant-dish .is-non-vegetarian').first()).toBeAttached();
  await page.getByRole('button', { name: 'Vegetarian only' }).click();
  await page.emulateMedia({ media: 'print' });
  await expect(booklet.locator('[data-menu-page]:visible')).toHaveCount(categories.length);
  await expect(dishRows.filter({ visible: true })).toHaveCount(142);
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
