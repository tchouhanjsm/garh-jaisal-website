const { test, expect } = require('@playwright/test');

test.describe('Premium homepage storytelling', () => {
  test('desktop journey connects the Fort, city discovery, planning and transparent decision support', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Homepage editorial hierarchy runs once at desktop width.');

    const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);

    await expect(page.getByRole('heading', { level: 1, name: 'Wake up inside Jaisalmer Fort.' })).toBeVisible();
    await expect(page.locator('.hero-content a.button-primary')).toHaveText('Check rooms & rates');
    await expect(page.locator('.hero-content a.button-primary')).toHaveAttribute('href', /asiatech\.in\/booking_engine\/index3\?token=/);

    const roomBooking = page.locator('#rooms-preview a.home-room-section-booking');
    await expect(roomBooking).toBeVisible();
    await expect(roomBooking).toHaveText('Check dates & rates');
    await expect(roomBooking).toHaveAttribute('target', '_blank');
    await expect(roomBooking).toHaveAttribute('rel', /noopener/);
    await expect(roomBooking).toHaveAttribute('data-cta-id', 'booking-home-rooms');

    const city = page.locator('#city-discovery');
    await expect(city).toBeVisible();
    await expect(city.getByRole('heading', { level: 2, name: 'The Golden City has more stories to tell.' })).toBeVisible();
    await expect(city.getByRole('link', { name: 'Explore guided city walks' })).toHaveAttribute('href', 'guided-jaisalmer-tour.php');
    await expect(city.getByRole('link', { name: 'Explore car & city tours' })).toHaveAttribute('href', 'car-tours.php');
    await expect(city.locator('img')).toHaveAttribute('alt', 'Jaisalmer city seen from the fort');

    const confidence = page.locator('#planning-confidence');
    await expect(confidence.getByRole('heading', { level: 2, name: 'Know what to expect before you book.' })).toBeVisible();
    await expect(confidence.locator('.home-planning-confidence__item')).toHaveCount(3);
    await expect(confidence).toContainText('Vehicles cannot enter');
    await expect(confidence).toContainText('Room facilities vary');
    await expect(confidence).toContainText('An enquiry is not a confirmed booking');

    const sectionOrder = await page.locator('main > section').evaluateAll(sections =>
      sections.map(section => section.id || section.classList[0])
    );
    const requiredOrder = ['hero', 'city-discovery', 'start-planning', 'arrival-guide', 'property-gallery', 'planning-confidence'];
    const positions = requiredOrder.map(name => sectionOrder.indexOf(name));
    expect(positions.every(position => position >= 0)).toBe(true);
    expect(positions).toEqual([...positions].sort((a, b) => a - b));

    const styleLink = page.locator('head link[rel="stylesheet"][href*="home-premium-story.css"]');
    await expect(styleLink).toHaveCount(1);
  });

  test('mobile homepage storytelling remains readable and does not overflow', async ({ page }, testInfo) => {
    test.skip(!['mobile-320', 'mobile-390'].includes(testInfo.project.name), 'Mobile layout checks run on 320px and 390px projects.');
    const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);

    await expect(page.locator('#city-discovery')).toBeVisible();
    await expect(page.locator('#planning-confidence .home-planning-confidence__item')).toHaveCount(3);

    const layout = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
      cityColumns: getComputedStyle(document.querySelector('.home-city-discovery__grid')).gridTemplateColumns,
      confidenceColumns: getComputedStyle(document.querySelector('.home-planning-confidence__grid')).gridTemplateColumns,
    }));
    expect(layout.document, 'Homepage must not overflow the mobile viewport').toBeLessThanOrEqual(layout.viewport + 1);
    expect(layout.cityColumns.trim().split(/\s+/)).toHaveLength(1);
    expect(layout.confidenceColumns.trim().split(/\s+/)).toHaveLength(1);
  });
});
