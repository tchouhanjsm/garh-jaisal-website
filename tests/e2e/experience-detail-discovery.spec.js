const { test, expect } = require('@playwright/test');

test('experience hubs open full detail pages from More details, not WhatsApp', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Experience detail handoff runs once on desktop-1280.');

  const journeys = [
    {
      hub: '/desert-safari.php',
      detail: 'sunset-camel-safari.php',
      heading: 'Sunset Camel Safari in Jaisalmer',
    },
    {
      hub: '/guided-jaisalmer-tour.php',
      detail: 'half-day-jaisalmer-tour.php',
      heading: 'Half-Day Guided Jaisalmer Tour',
    },
  ];

  for (const journey of journeys) {
    await page.goto(journey.hub, { waitUntil: 'domcontentloaded' });
    const detailLink = page.locator('article.experience-card a[href="' + journey.detail + '"]');
    await expect(detailLink, journey.hub + ': experience detail link exists').toHaveCount(1);
    await expect(detailLink.locator('.experience-card-detail-cta')).toHaveText('More details →');
    await expect(detailLink).toHaveAttribute('href', journey.detail);
    expect(await detailLink.getAttribute('href')).not.toMatch(/wa\.me|whatsapp/i);

    await detailLink.locator('.experience-card-detail-cta').click();
    const escapedDetail = journey.detail.replace('.', '\\.');
    await expect(page).toHaveURL(new RegExp('/' + escapedDetail + '$'));
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(journey.heading);
    await expect(page.locator('#experience-itinerary')).toBeVisible();
    await expect(page.locator('#experience-details')).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Experience page sections' })).toBeVisible();
  }
});
