const { test, expect } = require('@playwright/test');

test.describe('Home and Rooms redesign', () => {
  test('home presents verified quick facts and a clear room-discovery path', async ({ page }) => {
    const response = await page.goto('/', { waitUntil: 'domcontentloaded' });
    expect(response, 'Home should return an HTTP response').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    await expect(page.locator('main h1')).toHaveCount(1);
    await expect(page.locator('main h1')).toHaveText('Stay inside Jaisalmer Fort.');

    const facts = page.locator('.home-facts .at-a-glance__item');
    await expect(facts).toHaveCount(4);
    await expect(facts.nth(0)).toContainText('Inside Jaisalmer Fort');
    await expect(facts.nth(1)).toContainText('Individually styled heritage rooms');
    await expect(facts.nth(2)).toContainText('Rooftop dining with city views');
    await expect(facts.nth(3)).toContainText('Desert experiences arranged from the hotel');

    const heroBooking = page.locator('.hero-content a.button-primary');
    await expect(heroBooking).toHaveAttribute('href', /^https?:\/\//);
    await expect(heroBooking).toHaveAttribute('target', '_blank');
    await expect(heroBooking).toHaveAttribute('rel', /noopener/);
    await expect(page.locator('#home-rooms .home-room-cta')).toHaveAttribute('href', 'room.php');
  });

  test('Rooms hero has visible breadcrumbs and working availability and arrival destinations', async ({ page }) => {
    const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Rooms should return an HTTP response').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    await expect(page.locator('main h1')).toHaveCount(1);
    await expect(page.locator('nav[aria-label="Breadcrumb"]')).toBeVisible();
    await expect(page.locator('nav[aria-label="Breadcrumb"] a')).toHaveAttribute('href', 'index.php');

    const availability = page.locator('.page-hero-actions a.button-primary');
    await expect(availability).toHaveAttribute('href', /^https?:\/\//);
    await expect(availability).toHaveAttribute('target', '_blank');
    await expect(availability).toHaveAttribute('rel', /noopener/);
    await expect(page.locator('.page-hero-actions a[href="direction.php"]')).toContainText('Plan your arrival');
  });

  test('all seven room cards keep their existing imagery and readable feature disclosures', async ({ page }) => {
    const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const cards = page.locator('.room-card');
    await expect(cards).toHaveCount(7);
    await expect(page.locator('.room-floor-heading h2')).toHaveCount(2);
    await expect(page.locator('.room-book')).toHaveCount(7);
    await expect(page.locator('.room-feature-details')).toHaveCount(7);

    for (const card of await cards.all()) {
      await expect(card.locator('img')).toHaveAttribute('src', /\S/);
      await expect(card.locator('img')).toHaveAttribute('alt', /\S/);
      await expect(card.locator('.room-features:not(.room-features-extra) li')).toHaveCount(3);
      await expect(card.locator('.room-feature-details summary')).toHaveText('More room features');
      await expect(card.locator('.room-feature-details .room-features-extra li').first()).toBeAttached();
    }
  });

  test('Rooms FAQ answers the main booking and arrival questions accessibly', async ({ page }) => {
    const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const faq = page.locator('.rooms-faq-list details');
    await expect(faq).toHaveCount(4);
    await expect(faq.nth(0).locator('summary')).toHaveText('Are all rooms the same?');
    await expect(faq.nth(1).locator('summary')).toHaveText('Is Garh Jaisal Haveli inside Jaisalmer Fort?');
    await expect(faq.nth(2).locator('summary')).toHaveText('Can a vehicle reach the haveli entrance?');
    await expect(faq.nth(3).locator('summary')).toHaveText('How do I check current availability and rates?');
    await expect(faq.nth(2)).toContainText('Vehicles cannot enter the fort lanes');
  });
});
