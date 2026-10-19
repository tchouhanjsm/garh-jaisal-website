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

    await expect(page.locator('.arrival-quick-card')).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Directions page sections' })).toBeVisible();
    await expect(page.locator('.arrival-route-steps > li')).toHaveCount(4);
  }
});
