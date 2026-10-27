const { test, expect } = require('@playwright/test');

const routes = [
  '/sunset-camel-safari.php',
  '/sleep-under-the-stars.php',
  '/swiss-tent-safari.php',
  '/cultural-safari.php',
  '/half-day-jaisalmer-tour.php',
  '/full-day-jaisalmer-tour.php',
];

test('live experience detail pages provide working section links to their actual content', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Section-link interaction runs once on desktop-1280.');

  for (const route of routes) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ': HTTP response').not.toBeNull();
    expect(response.status(), route + ': live experience page should load').toBeLessThan(400);

    const nav = page.getByRole('navigation', { name: 'Experience page sections' });
    await expect(nav, route + ': section index should be visible').toBeVisible();
    const requiredLinks = [
      { name: 'At a glance', href: '#experience-overview', target: '#experience-overview' },
      { name: 'Itinerary', href: '#experience-itinerary', target: '#experience-itinerary' },
      { name: "What's included", href: '#experience-details', target: '#experience-details' },
      { name: 'Questions', href: '#experience-faq', target: '#experience-faq' },
    ];
    for (const item of requiredLinks) {
      const link = nav.getByRole('link', { name: item.name, exact: true });
      await expect(link, route + ': ' + item.name).toBeVisible();
      await expect(link).toHaveAttribute('href', item.href);
      await expect(page.locator(item.target)).toHaveCount(1);
    }

    const galleryLink = nav.getByRole('link', { name: 'Photo gallery', exact: true });
    if (await galleryLink.count()) {
      await expect(galleryLink).toHaveAttribute('href', '#experience-photo-gallery');
      await expect(page.locator('#experience-photo-gallery')).toHaveCount(1);
    } else {
      await expect(page.locator('#experience-photo-gallery')).toHaveCount(0);
    }

    await nav.getByRole('link', { name: 'Itinerary', exact: true }).click();
    await expect(page).toHaveURL(/#experience-itinerary$/);
    await expect(page.locator('#experience-itinerary h2')).toHaveText('Follow the experience.');
    await expect(page.locator('#experience-itinerary')).toBeVisible();
  }
});

test('experience section navigation wraps without overflow across mobile, tablet and desktop', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive navigation layout runs once with explicit viewport sizes.');
  const sampleRoutes = ['/sunset-camel-safari.php', '/sleep-under-the-stars.php', '/full-day-jaisalmer-tour.php'];
  for (const route of sampleRoutes) {
    for (const viewport of [
      { width: 320, height: 740 },
      { width: 390, height: 844 },
      { width: 768, height: 900 },
      { width: 1280, height: 800 },
      { width: 1440, height: 900 },
    ]) {
      await page.setViewportSize(viewport);
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ': response at ' + viewport.width).not.toBeNull();
      expect(response.status(), route + ': page available at ' + viewport.width).toBeLessThan(400);
      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(dimensions.document, route + ': no horizontal overflow at ' + viewport.width)
        .toBeLessThanOrEqual(dimensions.viewport + 1);
      await expect(page.getByRole('navigation', { name: 'Experience page sections' })).toBeVisible();
    }
  }
});
