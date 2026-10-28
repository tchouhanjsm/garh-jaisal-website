const { test, expect } = require('@playwright/test');

const routes = [
  { path: '/desert-safari.php', kind: 'hub', contact: 'contact' },
  { path: '/guided-jaisalmer-tour.php', kind: 'hub', contact: 'contact' },
  { path: '/sunset-camel-safari.php', kind: 'experience', slug: 'sunset-camel-safari', contact: 'contact' },
  { path: '/sleep-under-the-stars.php', kind: 'experience', slug: 'sleep-under-the-stars', contact: 'contact' },
  { path: '/swiss-tent-safari.php', kind: 'experience', slug: 'swiss-tent-safari', contact: 'contact' },
  { path: '/cultural-safari.php', kind: 'experience', slug: 'cultural-safari', contact: 'contact' },
  { path: '/half-day-jaisalmer-tour.php', kind: 'experience', slug: 'half-day-jaisalmer-tour', contact: 'whatsapp', title: 'Half-Day Guided Jaisalmer Tour' },
  { path: '/full-day-jaisalmer-tour.php', kind: 'experience', slug: 'full-day-jaisalmer-tour', contact: 'whatsapp', title: 'Full-Day Guided Jaisalmer Tour' },
];

test('live experience pages show a contextual checkpoint after the itinerary and inclusions', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The route contract runs once on desktop-1280');
  test.setTimeout(120_000);

  for (const route of routes) {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
    expect(response, route.path + ': should return an HTTP response').not.toBeNull();
    expect(response.status(), route.path + ': should remain available').toBe(200);

    if (route.kind === 'hub') {
      const heroActions = page.locator('.experience-hero-actions');
      await expect(heroActions.locator('a, button'), route.path + ': hero has two deliberate actions').toHaveCount(2);
      if (route.path === '/desert-safari.php') {
        await expect(heroActions.locator('a.button-primary'))
          .toHaveAttribute('href', '#desert-options');
        await expect(heroActions.getByRole('link', { name: 'Compare safari options' }))
          .toBeVisible();
        await expect(heroActions.getByRole('link', { name: 'Check dates & prices' }))
          .toHaveAttribute('href', 'contact.php?topic=desert-safari');
        await expect(page.getByRole('link', { name: 'Build your trip shortlist' }), route.path + ': hub keeps a clear next step')
          .toHaveAttribute('href', 'catalogue.php');
      } else {
        await expect(page.getByRole('link', { name: 'Plan my guided tour' }), route.path + ': city-tour hub keeps a contextual enquiry')
          .toHaveAttribute('href', 'contact.php?topic=fort-guide');
        await expect(heroActions.locator('a[href="#guided-options"]')).toHaveText('Compare tour options');
        await expect(heroActions.getByRole('link', { name: 'Build your trip shortlist' })).toHaveCount(0);
      }
      continue;
    }

    const checkpoint = page.locator('[data-experience-conversion-checkpoint]');
    await expect(checkpoint, route.path + ': contextual checkpoint').toBeVisible();
    await expect(checkpoint.getByRole('heading', {
      level: 2,
      name: 'Ready to plan this experience?',
    })).toBeVisible();

    const afterDetails = await page.evaluate(() => {
      const details = document.querySelector('.experience-details');
      const checkpointNode = document.querySelector('[data-experience-conversion-checkpoint]');
      if (!details || !checkpointNode) return false;
      return Boolean(details.compareDocumentPosition(checkpointNode) & Node.DOCUMENT_POSITION_FOLLOWING);
    });
    expect(afterDetails, route.path + ': checkpoint follows itinerary and included/excluded details').toBe(true);

    const contact = checkpoint.locator('a.experience-conversion-contact');
    await expect(contact).toBeVisible();
    const href = await contact.getAttribute('href');
    expect(href).toBeTruthy();
    await expect(page.locator('.experience-hero-actions a.button-primary'), route.path + ': hero and post-detail enquiry destinations match')
      .toHaveAttribute('href', href);
    const destination = new URL(href, page.url());

    if (route.contact === 'whatsapp') {
      await expect(contact).toHaveText('Ask on WhatsApp');
      await expect(contact).toHaveAttribute('target', '_blank');
      await expect(contact).toHaveAttribute('rel', /noopener/);
      expect(destination.origin + destination.pathname).toBe('https://wa.me/917727047567');
      expect(destination.searchParams.get('text')).toContain(route.title);
      expect(destination.searchParams.get('text')).toContain('current price');
      expect(destination.searchParams.get('text')).toContain('guide availability');
    } else {
      expect(destination.pathname).toBe('/contact.php');
      if (route.kind === 'experience') {
        expect(destination.searchParams.get('experience')).toBe(route.slug);
      }
    }

    if (route.kind === 'experience') {
      const addToTrip = checkpoint.locator('[data-trip-add]');
      await expect(addToTrip).toBeVisible();
      await expect(addToTrip).toHaveAttribute('data-trip-id', route.slug);
      await expect(addToTrip).toHaveAttribute('aria-pressed', 'false');
    } else {
      await expect(checkpoint.locator('[data-trip-add]')).toHaveCount(0);
      await expect(checkpoint.getByRole('link', { name: 'Build your trip shortlist' })).toHaveAttribute('href', 'catalogue.php');
    }
  }
});

test('hero and checkpoint Add to Trip buttons stay synchronized', async ({ page }, testInfo) => {
  test.skip(!['desktop-1280', 'mobile-390'].includes(testInfo.project.name), 'Trip action synchronization runs at desktop and mobile widths');

  await page.goto('/swiss-tent-safari.php', { waitUntil: 'domcontentloaded' });
  const heroActions = page.locator('.experience-hero-actions');
  await expect(heroActions.locator('a, button')).toHaveCount(2);
  await expect(heroActions.getByRole('link', { name: 'Build your trip shortlist' })).toHaveCount(0);
  const heroAdd = page.locator('.experience-hero [data-trip-add][data-trip-id="swiss-tent-safari"]');
  const checkpointAdd = page.locator('[data-experience-conversion-checkpoint] [data-trip-add][data-trip-id="swiss-tent-safari"]');
  await expect(heroAdd).toBeVisible();
  await expect(checkpointAdd).toBeVisible();

  await checkpointAdd.click();
  await expect(checkpointAdd).toHaveAttribute('aria-pressed', 'true');
  await expect(heroAdd).toHaveAttribute('aria-pressed', 'true');
  await expect(page.locator('[data-trip-count]')).toHaveText('1');

  await heroAdd.click();
  await expect(checkpointAdd).toHaveAttribute('aria-pressed', 'false');
  await expect(heroAdd).toHaveAttribute('aria-pressed', 'false');
  await expect(page.locator('[data-trip-count]')).toHaveText('0');
});

test('experience conversion checkpoint stays within viewport and keeps usable actions', async ({ page }) => {
  const response = await page.goto('/swiss-tent-safari.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBe(200);

  const metrics = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth
  }));
  expect(metrics.document, 'Experience detail must not overflow horizontally at ' + metrics.viewport + 'px')
    .toBeLessThanOrEqual(metrics.viewport + 1);

  const checkpoint = page.locator('[data-experience-conversion-checkpoint]');
  await expect(checkpoint).toBeVisible();
  for (const control of await checkpoint.locator('a.button, button.trip-add-button').all()) {
    const box = await control.boundingBox();
    expect(box?.height ?? 0, 'checkpoint actions must meet a 44px touch target').toBeGreaterThanOrEqual(44);
  }
});
