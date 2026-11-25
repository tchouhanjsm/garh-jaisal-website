const fs = require('node:fs');
const path = require('node:path');
const { createHash } = require('node:crypto');
const { test, expect } = require('@playwright/test');

test('restaurant only publishes approved menu content and keeps the booklet usable when enabled', async ({ page }) => {
  await page.addInitScript(() => {
    window.__restaurantPrintCalled = false;
    window.print = () => { window.__restaurantPrintCalled = true; };
  });
  const response = await page.goto('/restaurant.php#menu', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  for (const asset of ['css/restaurant.css', 'js/restaurant.js']) {
    const hash = createHash('sha256').update(fs.readFileSync(path.join(__dirname, '../..', asset))).digest('hex').slice(0, 12);
    await expect(page.locator(`[href="${asset}?v=${hash}"],[src="${asset}?v=${hash}"]`)).toHaveCount(1);
  }

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
  const categories = ['Breakfast', 'Drinks & Light Bites', 'Mains & Sides', 'Chicken & Mutton'];
  const links = booklet.getByRole('navigation', { name: 'Menu sections' }).getByRole('link');
  const pages = booklet.locator('[data-menu-page]');
  const dishRows = booklet.locator('.restaurant-dish');
  const status = booklet.locator('[data-menu-page-status]');
  const top = booklet.getByRole('group', { name: 'Turn menu pages', exact: true });
  const footer = booklet.getByRole('group', { name: 'Turn menu pages after dishes' });
  await expect(links).toHaveText(categories);
  await expect(dishRows).toHaveCount(142);
  await expect(dishRows.filter({ visible: true })).toHaveCount(38);
  await expect(pages.first()).toHaveAttribute('id', 'breakfast');
  await expect(pages.first().getByRole('heading', { name: 'Plain Paratha', exact: true })).toBeVisible();
  await expect(pages.first().getByRole('heading', { name: 'Omelette Plain', exact: true })).toBeVisible();
  await expect(status).toHaveText('Page 1 of 4 · Breakfast');
  await expect(top.getByRole('button', { name: /Previous/ })).toBeDisabled();
  await expect(top.getByRole('button', { name: /Next/ })).toBeEnabled();
  const firstDish = dishRows.first();
  const nameBox = await firstDish.getByRole('heading').boundingBox();
  const priceBox = await firstDish.locator('.restaurant-price').boundingBox();
  expect(nameBox.x + nameBox.width).toBeLessThanOrEqual(priceBox.x);
  expect(nameBox.width).toBeGreaterThan(priceBox.width);

  await links.last().click();
  expect(new URL(page.url()).hash).toBe('#chicken-mutton');
  await expect(pages.filter({ visible: true })).toHaveCount(1);
  await expect(dishRows.filter({ visible: true })).toHaveCount(9);
  await expect(status).toHaveText('Page 4 of 4 · Chicken & Mutton');
  await expect(top.getByRole('button', { name: /Next/ })).toBeDisabled();

  await footer.getByRole('button', { name: /Previous/ }).click();
  await expect(booklet.getByRole('heading', { name: 'Mains & Sides', exact: true })).toBeFocused();
  await expect(status).toHaveText('Page 3 of 4 · Mains & Sides');
  await expect(page.getByRole('button', { name: 'Vegetarian only' })).toHaveCount(0);
  await booklet.getByRole('button', { name: 'View all dishes' }).click();
  await expect(status).toHaveText('All dishes');
  await expect(dishRows.filter({ visible: true })).toHaveCount(142);
  await expect(footer).toBeHidden();
  await expect(booklet.locator('.restaurant-dish .is-vegan')).toHaveCount(0);
  await expect(page.locator('#menu').getByRole('link', { name: 'Ask about vegan options' }))
    .toHaveAttribute('href', 'contact.php?topic=dining');
  await booklet.getByRole('button', { name: 'Back to booklet' }).click();
  await expect(status).toHaveText('Page 3 of 4 · Mains & Sides');
  await expect(dishRows.filter({ visible: true })).toHaveCount(46);

  await booklet.getByRole('button', { name: 'Print menu' }).click();
  expect(await page.evaluate(() => window.__restaurantPrintCalled)).toBe(true);

  expect(await page.evaluate(() => document.documentElement.scrollWidth))
    .toBeLessThanOrEqual(await page.evaluate(() => window.innerWidth + 1));

  const menuText = await dishRows.allTextContents();
  const dalFry = dishRows.filter({ has: page.getByRole('heading', { name: 'Dal Fry', exact: true, includeHidden: true }) });
  await expect(dalFry).toContainText('₹250');
  await expect(dalFry).toContainText('Vegetarian');
  const lalMaas = dishRows.filter({ has: page.getByRole('heading', { name: 'Lal Maas', exact: true, includeHidden: true }) });
  await expect(lalMaas).toContainText('₹700');
  await expect(lalMaas).toContainText('Non-vegetarian');
  const rooftopResponse = await page.goto('/roof.php#drinks-light-bites', { waitUntil: 'domcontentloaded' });
  expect(rooftopResponse.ok()).toBe(true);
  await expect(booklet).toHaveClass(/is-enhanced/);
  expect(await dishRows.allTextContents()).toEqual(menuText);
  await expect(booklet.locator('.restaurant-dish .is-vegetarian').first()).toBeAttached();
  await expect(booklet.locator('.restaurant-dish .is-non-vegetarian').first()).toBeAttached();
  await expect(status).toHaveText('Page 2 of 4 · Drinks & Light Bites');
  await expect(dishRows.filter({ visible: true })).toHaveCount(49);
  await page.emulateMedia({ media: 'print' });
  await expect(booklet.locator('[data-menu-page]:visible')).toHaveCount(categories.length);
  await expect(dishRows.filter({ visible: true })).toHaveCount(142);
});

test('both dining pages show the full menu with JavaScript disabled', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The fallback uses the same markup at every viewport.');
  const context = await browser.newContext({ javaScriptEnabled: false });
  try {
    const page = await context.newPage();
    for (const route of ['/restaurant.php', '/roof.php']) {
      await page.goto(testInfo.project.use.baseURL + route + '#menu', { waitUntil: 'domcontentloaded' });
      await expect(page.locator('.restaurant-dish:visible')).toHaveCount(142);
      await expect(page.getByRole('button', { name: 'Print menu' })).toBeHidden();
      await expect(page.locator('[data-menu-footer]')).toBeHidden();
      await page.getByRole('navigation', { name: 'Menu sections' }).getByRole('link', { name: 'Breakfast' }).click();
      await expect(page).toHaveURL(/#breakfast$/);
    }
  } finally {
    await context.close();
  }
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
