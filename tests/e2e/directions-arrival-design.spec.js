const { test, expect } = require('@playwright/test');

test('Directions puts the arrival essentials and section links within immediate reach', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Arrival design assertions run once on desktop-1280');

  const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  expect(response, 'Directions should return an HTTP response').not.toBeNull();
  expect(response.status()).toBeLessThan(400);

  const hero = page.locator('.arrival-hero');
  await expect(hero.getByRole('heading', { level: 1, name: 'Arrive inside Jaisalmer Fort.' })).toBeVisible();

  const arrivalCard = page.locator('.arrival-quick-card');
  await expect(arrivalCard.getByRole('heading', { level: 2, name: 'Plan for the final walk.' })).toBeVisible();
  await expect(arrivalCard).toContainText('Start at Fort Parking');
  await expect(arrivalCard).toContainText('Cars cannot enter the fort');
  await expect(arrivalCard).toContainText('uneven cobbles and short stair sections');
  await expect(arrivalCard.getByRole('link', { name: /Open exact hotel pin/ }))
    .toHaveAttribute('href', /^https:\/\/maps\.app\.goo\.gl\//);

  const sectionNav = page.getByRole('navigation', { name: 'Directions page sections' });
  for (const item of [
    { label: 'Quick directions', href: '#arrival-overview-title' },
    { label: 'Travel options', href: '#travel-options-title' },
    { label: 'Walking & mobility', href: '#walking-access-title' },
    { label: 'Map & navigation', href: '#arrival-map-title' },
    { label: 'FAQs', href: '#arrival-faq-title' },
  ]) {
    await expect(sectionNav.getByRole('link', { name: item.label, exact: true }))
      .toHaveAttribute('href', item.href);
  }

  await sectionNav.getByRole('link', { name: 'Walking & mobility' }).click();
  await expect(page).toHaveURL(/#walking-access-title$/);
  await expect(page.locator('#walking-access-title')).toBeVisible();

  const routeSteps = page.locator('.arrival-route-steps > li');
  await expect(routeSteps).toHaveCount(4);
  await expect(routeSteps.nth(1)).toContainText('Stop at Fort Parking');
});

test('Directions hero, route cards and section navigation remain usable at key viewport widths', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive arrival layout checks run once');

  for (const viewport of [
    { width: 320, height: 740 },
    { width: 390, height: 844 },
    { width: 768, height: 900 },
    { width: 1280, height: 800 },
    { width: 1440, height: 900 },
  ]) {
    await page.setViewportSize(viewport);
    const response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Directions should return an HTTP response at ' + viewport.width + 'px').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const dimensions = await page.evaluate(() => ({
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document, 'Directions must not overflow horizontally at ' + dimensions.viewport + 'px')
      .toBeLessThanOrEqual(dimensions.viewport + 1);

    await page.waitForFunction(() => {
      const card = document.querySelector('.arrival-quick-card');
      const dock = document.querySelector('.mobile-cta');
      if (!card || !dock) return false;
      const compact = window.matchMedia('(max-width: 1080px)').matches;
      const cardBounds = card.getBoundingClientRect();
      const dockHeight = Math.max(58, Number.parseFloat(window.getComputedStyle(dock).height) || 0);
      const overlapsDock = compact &&
        cardBounds.left < window.innerWidth &&
        cardBounds.right > 0 &&
        cardBounds.top < window.innerHeight &&
        cardBounds.bottom > Math.max(0, window.innerHeight - dockHeight);
      const shouldSuppress = overlapsDock && !dock.contains(document.activeElement);
      return dock.classList.contains('is-suppressed-by-arrival-card') === shouldSuppress;
    });

    const dockState = await page.evaluate(() => {
      const dock = document.querySelector('.mobile-cta');
      const cardBounds = document.querySelector('.arrival-quick-card').getBoundingClientRect();
      const compact = window.matchMedia('(max-width: 1080px)').matches;
      const dockHeight = Math.max(58, Number.parseFloat(window.getComputedStyle(dock).height) || 0);
      return {
        compact,
        overlapsDock: compact &&
          cardBounds.left < window.innerWidth &&
          cardBounds.right > 0 &&
          cardBounds.top < window.innerHeight &&
          cardBounds.bottom > Math.max(0, window.innerHeight - dockHeight),
        suppressed: dock.classList.contains('is-suppressed-by-arrival-card'),
        display: window.getComputedStyle(dock).display,
        ariaHidden: dock.getAttribute('aria-hidden'),
        inert: dock.hasAttribute('inert'),
      };
    });

    if (dockState.compact && dockState.overlapsDock) {
      expect(dockState.suppressed, 'the fixed dock must not cover the Directions essentials card').toBe(true);
      expect(dockState.display).toBe('none');
      expect(dockState.ariaHidden).toBe('true');
      expect(dockState.inert).toBe(true);
      const targetScroll = await page.evaluate(() => {
        const card = document.querySelector('.arrival-quick-card');
        const dock = document.querySelector('.mobile-cta');
        const dockHeight = Math.max(58, dock.getBoundingClientRect().height);
        return Math.max(0, window.scrollY + card.getBoundingClientRect().bottom -
          (window.innerHeight - dockHeight) + 8);
      });
      await page.evaluate((top) => {
        document.documentElement.style.scrollBehavior = 'auto';
        window.scrollTo(0, top);
      }, targetScroll);
      await expect(page.locator('.mobile-cta')).toHaveCSS('display', 'grid');
      await expect(page.locator('.mobile-cta')).not.toHaveAttribute('aria-hidden', 'true');
    } else if (dockState.compact) {
      expect(dockState.suppressed).toBe(false);
      expect(dockState.ariaHidden).toBeNull();
      expect(dockState.inert).toBe(false);
    }

    await expect(page.locator('.arrival-quick-card')).toBeVisible();
    await expect(page.locator('.arrival-driver-card')).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Directions page sections' })).toBeVisible();
    await expect(page.locator('.arrival-route-steps > li')).toHaveCount(4);
  }
});


test('driver arrival note copies the approved drop-off, walking and exact map-pin details', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Driver-note interaction runs once on desktop-1280');
  await page.addInitScript(() => {
    Object.defineProperty(navigator, 'clipboard', {
      configurable: true,
      value: { writeText: async (value) => { window.__arrivalCopiedText = value; } }
    });
  });
  await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  const driverCard = page.locator('.arrival-driver-card');
  await expect(driverCard.getByRole('heading', { level: 3, name: 'Share the right drop-off point.' })).toBeVisible();
  await expect(driverCard).toContainText('Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001');
  const copyButton = driverCard.getByRole('button', { name: 'Copy arrival note for driver' });
  await expect(copyButton).toBeVisible();
  await copyButton.click();
  await expect(driverCard.locator('[data-arrival-copy-status]')).toHaveText(
    'Arrival note copied. Share it with your driver before you set out.'
  );
  const copied = await page.evaluate(() => window.__arrivalCopiedText);
  expect(copied).toContain('Garh Jaisal Haveli');
  expect(copied).toContain('Vehicles cannot enter Jaisalmer Fort');
  expect(copied).toContain('Fort Parking');
  expect(copied).toContain('300 metres (5–6 minutes)');
  expect(copied).toContain('https://maps.app.goo.gl/nAbRiZhv78FSbju48');
  expect(copied).not.toMatch(/email|phone|topic|private/i);
});

test('driver arrival details remain readable when the enhancement script is unavailable', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Progressive-enhancement check runs once on desktop-1280');
  await page.route('**/js/arrival-guide.js', route => route.abort());
  await page.goto('/direction.php', { waitUntil: 'domcontentloaded' });
  const driverCard = page.locator('.arrival-driver-card');
  await expect(driverCard).toContainText('Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001');
  await expect(driverCard).toContainText('Vehicles cannot enter Jaisalmer Fort');
  const copyButton = driverCard.locator('button[data-arrival-copy]');
  await expect(copyButton).toHaveAttribute('hidden', '');
  await expect(copyButton).toBeHidden();
});
