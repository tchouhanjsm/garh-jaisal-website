const { test, expect } = require('@playwright/test');

const STORY_ROUTES = [
  '/',
  '/room.php',
  '/pack.php',
  '/catalogue.php',
  '/roof.php',
  '/blog.php',
  '/swiss-tent-safari.php',
];

test('Thar Sunset storytelling layers load across high-intent guest journeys', async ({ page }) => {
  for (const route of STORY_ROUTES) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ': expected an HTTP response').not.toBeNull();
    expect(response.status(), route + ': route should remain available').toBeLessThan(400);

    const main = page.locator('main.thar-sunset-story');
    await expect(main, route + ': scoped story layer').toHaveCount(1);
    await expect(main.locator('h1'), route + ': one clear page heading').toHaveCount(1);
    await expect(page.locator('link[rel="stylesheet"][href*="thar-sunset-story.css"]'))
      .toHaveCount(1);
    expect(
      await main.locator('[data-story-reveal]').count(),
      route + ': progressive enhancement has a reveal target'
    ).toBeGreaterThan(0);

    const metrics = await page.evaluate(() => ({
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(
      metrics.document,
      route + ': no horizontal overflow at ' + metrics.viewport + 'px'
    ).toBeLessThanOrEqual(metrics.viewport + 1);

    const primaryAction = main.locator('a.button').first();
    await expect(primaryAction, route + ': primary action remains present').toBeVisible();
    const actionBox = await primaryAction.boundingBox();
    expect(actionBox?.height ?? 0, route + ': primary action keeps a 44px hit area')
      .toBeGreaterThanOrEqual(44);
  }
});

test('story pages stay within width and retain usable primary actions at 320–1440px', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Five-width responsive sweep runs once.');
  const widths = [320, 390, 768, 1280, 1440];

  for (const route of STORY_ROUTES) {
    for (const width of widths) {
      await page.setViewportSize({ width, height: 900 });
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' at ' + width + 'px: expected response').not.toBeNull();
      expect(response.status(), route + ' at ' + width + 'px: route should remain available').toBeLessThan(400);

      const dimensions = await page.evaluate(() => ({
        viewport: window.innerWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(
        dimensions.document,
        route + ': no horizontal overflow at ' + width + 'px'
      ).toBeLessThanOrEqual(dimensions.viewport + 1);

      const action = page.locator('main.thar-sunset-story a.button').first();
      const box = await action.boundingBox();
      expect(box?.height ?? 0, route + ': action target at ' + width + 'px').toBeGreaterThanOrEqual(44);
    }
  }
});

test('Thar Sunset treatment has no motion when reduced motion is requested', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Reduced-motion contract runs once.');
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('/', { waitUntil: 'domcontentloaded' });

  const target = page.locator('main.thar-sunset-story [data-story-reveal]').first();
  await expect(target).toBeVisible();
  const motion = await target.evaluate((element) => {
    const style = window.getComputedStyle(element);
    return {
      opacity: style.opacity,
      transform: style.transform,
      animationDuration: style.animationDuration,
    };
  });
  expect(motion.opacity).toBe('1');
  expect(motion.transform).toBe('none');
  expect(motion.animationDuration).toBe('0s');
});

test('story reveals run once when targets enter the viewport', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Scroll reveal interaction runs once.');
  await page.goto('/', { waitUntil: 'domcontentloaded' });

  const target = page.locator('main.thar-sunset-story [data-story-reveal]').first();
  await target.scrollIntoViewIfNeeded();
  await expect(target).toHaveClass(/story-is-visible/);

  const opacity = await target.evaluate((element) => window.getComputedStyle(element).opacity);
  expect(Number(opacity)).toBeGreaterThan(0.8);
});

test('catalogue and Car & City experience cards use the controlled arch image crop', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Card art direction runs once at desktop width');

  await page.goto('/catalogue.php', { waitUntil: 'domcontentloaded' });
  const productMedia = page.locator('main.thar-sunset-story--catalogue .product-card-media').first();
  await expect(productMedia).toBeVisible();
  const productRadius = await productMedia.evaluate(element => getComputedStyle(element).borderTopLeftRadius);
  expect(productRadius, 'Catalogue image frame uses a soft arch, not a rounded card').toContain('48%');

  await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  const experienceMedia = page.locator('main .experience-card > a > .experience-card-media').first();
  await expect(experienceMedia).toBeVisible();
  const experienceRadius = await experienceMedia.evaluate(element => getComputedStyle(element).borderTopLeftRadius);
  expect(experienceRadius, 'Experience image frame uses a soft arch').toContain('48%');

  const noOverflow = await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);
  expect(noOverflow, 'Experience arch treatment must not create horizontal overflow').toBe(true);
});
