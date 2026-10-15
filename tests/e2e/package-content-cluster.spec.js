const { test, expect } = require('@playwright/test');

test('Packages connects planning guides and vehicle enquiries to their existing destinations', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The content-cluster navigation contract is checked once.');

  await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });

  const section = page.locator('#planning-guides');
  await expect(page.locator('h1')).toHaveCount(1);
  await expect(section.getByRole('heading', {
    level: 2,
    name: 'Use a guide to shape your Jaisalmer visit.',
  })).toBeVisible();
  await expect(section.locator('.experience-card')).toHaveCount(3);

  const targets = [
    {
      href: 'jaisalmer-fort-guide.php',
      heading: 'Jaisalmer Fort Guide',
    },
    {
      href: 'desert-safari-guide.php',
      heading: 'Desert Safari in Jaisalmer: How to Choose an Experience',
    },
    {
      href: 'jaisalmer-2-day-itinerary.php',
      heading: 'Jaisalmer 2-Day Itinerary',
    },
  ];

  for (const target of targets) {
    const cardLink = section.locator(`a[href="${target.href}"]`);
    await expect(cardLink).toBeVisible();
    await expect(cardLink.locator('img')).toHaveAttribute('alt', /\S+/);
    await cardLink.click();
    await expect(page).toHaveURL(new RegExp(`/${target.href.replace('.', '\\.')}($|[?#])`));
    await expect(page.locator('h1')).toHaveText(target.heading);
    await page.goto('/pack.php', { waitUntil: 'domcontentloaded' });
  }

  const carLink = section.getByRole('link', { name: 'Explore car arrangements' });
  await expect(carLink).toBeVisible();
  await expect(carLink).toHaveAttribute('href', 'car-rental.php');
  await carLink.click();
  await expect(page).toHaveURL(/\/car-rental\.php$/);
  await expect(page.locator('h1')).toBeVisible();
});
