const { test, expect } = require('@playwright/test');

test.describe('Unified Trip shortlist to Contact', () => {
  test('carries mixed selected options into the existing contact form without leaking optional trip details into the URL', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Run the complete handoff interaction once.');

    await page.goto('/catalogue.php', { waitUntil: 'domcontentloaded' });
    const heritageStay = page.locator('[data-trip-add][data-trip-id="heritage-room-stay"]');
    const camelSafari = page.locator('[data-trip-add][data-trip-id="sunset-camel-safari"]');
    await expect(heritageStay).toBeVisible();
    await expect(camelSafari).toBeVisible();
    await heritageStay.click();
    await camelSafari.click();

    const trigger = page.locator('[data-trip-toggle]');
    await expect(trigger).toBeVisible();
    await expect(trigger).toContainText('2');
    await trigger.click();
    await page.locator('[data-trip-start-date]').fill('2026-12-10');
    await page.locator('[data-trip-end-date]').fill('2026-12-13');
    await page.locator('[data-trip-travellers]').fill('2');

    const contactLink = page.locator('[data-trip-contact]');
    await expect(contactLink).toBeVisible();
    const target = new URL(await contactLink.getAttribute('href'), 'http://127.0.0.1:8080');
    expect(target.pathname).toBe('/contact.php');
    expect(target.searchParams.getAll('trip[]')).toEqual(['heritage-room-stay', 'sunset-camel-safari']);
    expect(target.search).not.toMatch(/2026-12-10|2026-12-13|traveller|travellers|start-date/i);

    await contactLink.click();
    await expect(page).toHaveURL(/\/contact\.php\?trip/);
    const context = page.locator('.contact-trip-context');
    await expect(context).toBeVisible();
    await expect(context).toContainText('Heritage room stay');
    await expect(context).toContainText('Sunset Camel Safari in Jaisalmer');
    await expect(page.locator('#message')).toHaveValue(/these options for my Jaisalmer trip: Heritage room stay, Sunset Camel Safari in Jaisalmer/);
    await expect(page.locator('input[name="enquiry_context_token"]')).toHaveAttribute('value', /[a-f0-9]{32}/);
  });

  test('renders the selected Trip context without horizontal page overflow at all supported viewport widths', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Exercise all five supported widths in one browser project.');

    for (const width of [320, 390, 768, 1280, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/contact.php?trip%5B0%5D=heritage-room-stay&trip%5B1%5D=sunset-camel-safari', { waitUntil: 'domcontentloaded' });
      await expect(page.locator('.contact-trip-context')).toBeVisible();
      const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
      expect(scrollWidth, 'Contact page must not overflow horizontally at ' + width + 'px').toBeLessThanOrEqual(width + 1);
    }
  });
});
