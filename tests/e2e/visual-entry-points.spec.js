const { test, expect } = require('@playwright/test');

test('stay planning, Camping and Fort walk have contextual photo-led entry points', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Visual entry-point assertions run once on desktop-1280');

  const routes = [
    {
      path: '/book-rooms-in-jaisalmer.php',
      heading: 'Staying inside Jaisalmer Fort',
    },
    {
      path: '/camping.php',
      heading: 'Choose how you spend the night in the desert.',
    },
    {
      path: '/city-tour.php',
      heading: 'A walk through Jaisalmer Fort',
    },
  ];

  for (const route of routes) {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
    expect(response, route.path + ' should return an HTTP response').not.toBeNull();
    expect(response.status(), route.path + ' should not return an error').toBeLessThan(400);

    await expect(page.getByRole('heading', { level: 1, name: route.heading })).toBeVisible();

    const hero = page.locator('main .page-hero.page-hero--photo');
    await expect(hero, route.path + ' should render a photo-led hero').toBeVisible();
    const heroImage = hero.locator('.page-hero-media img');
    await expect(heroImage).toBeVisible();
    await expect(heroImage).toHaveAttribute('alt', '');
    await expect.poll(async () => heroImage.evaluate((image) => image.complete && image.naturalWidth > 0), {
      message: route.path + ' hero image should load successfully',
    }).toBe(true);
  }
});

test('Camping presents two distinct, responsive experience cards with working destinations', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Camping visual regression runs once with explicit viewport coverage');

  for (const viewport of [
    { width: 320, height: 740 },
    { width: 390, height: 844 },
    { width: 768, height: 900 },
    { width: 1280, height: 800 },
    { width: 1440, height: 900 },
  ]) {
    await page.setViewportSize(viewport);
    const response = await page.goto('/camping.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Camping should return an HTTP response at ' + viewport.width + 'px').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const dimensions = await page.evaluate(() => ({
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document, 'Camping must not overflow horizontally at ' + viewport.width + 'px')
      .toBeLessThanOrEqual(dimensions.viewport + 1);

    const cards = page.locator('.camping-option-card');
    await expect(cards).toHaveCount(2);
    await expect(cards.nth(0).getByRole('heading', { level: 3, name: 'Sleep Under the Stars' })).toBeVisible();
    await expect(cards.nth(1).getByRole('heading', { level: 3, name: 'Overnight Safari in a Swiss Tent' })).toBeVisible();

    await expect(cards.nth(0).getByRole('link', { name: 'Explore Sleep Under the Stars' }))
      .toHaveAttribute('href', 'sleep-under-the-stars.php');
    await expect(cards.nth(1).getByRole('link', { name: 'Explore Swiss Tent Safari' }))
      .toHaveAttribute('href', 'swiss-tent-safari.php');

    for (const card of [cards.nth(0), cards.nth(1)]) {
      const image = card.locator('img');
      await expect(image).toBeVisible();
      await expect.poll(async () => image.evaluate((node) => node.complete && node.naturalWidth > 0), {
        message: 'Camping card image should load at ' + viewport.width + 'px',
      }).toBe(true);
    }
  }
});

test('shared floating contact actions are exposed as labelled navigation landmarks', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Contact-dock landmark assertions run once on desktop-1280');

  await page.goto('/camping.php', { waitUntil: 'domcontentloaded' });

  const desktopDock = page.getByRole('navigation', { name: 'Contact Garh Jaisal' });
  const mobileDock = page.getByRole('navigation', { name: 'Quick contact actions' });

  await expect(desktopDock).toBeVisible();
  await expect(mobileDock).toBeHidden();
  await expect(desktopDock.getByRole('link', { name: /Call Garh Jaisal at/ })).toHaveAttribute('href', /^tel:/);
  await expect(desktopDock.getByRole('link', { name: 'WhatsApp Garh Jaisal' })).toHaveAttribute('href', /^https:/);

  await page.setViewportSize({ width: 390, height: 844 });
  await page.reload({ waitUntil: 'domcontentloaded' });

  await expect(mobileDock).toBeVisible();
  await expect(desktopDock).toBeHidden();
  await expect(mobileDock.getByRole('link', { name: 'Call', exact: true })).toHaveAttribute('href', /^tel:/);
  await expect(mobileDock.getByRole('link', { name: 'WhatsApp', exact: true })).toHaveAttribute('href', /^https:/);
  await expect(mobileDock.getByRole('link', { name: 'Book Now', exact: true })).toHaveAttribute('href', /^https:/);
});
