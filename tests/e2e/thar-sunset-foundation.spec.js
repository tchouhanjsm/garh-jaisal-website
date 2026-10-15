const { test, expect } = require('@playwright/test');

test('Thar Sunset tokens, CTA contrast and responsive navigation remain usable', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });

  const tokens = await page.evaluate(() => {
    const root = getComputedStyle(document.documentElement);
    return {
      night: root.getPropertyValue('--night').trim().toLowerCase(),
      saffron: root.getPropertyValue('--saffron').trim().toLowerCase(),
      parchment: root.getPropertyValue('--parchment').trim().toLowerCase(),
      peacock: root.getPropertyValue('--peacock').trim().toLowerCase()
    };
  });
  expect(tokens).toEqual({
    night: '#14122b',
    saffron: '#b94a0e',
    parchment: '#f7efe0',
    peacock: '#1d7472'
  });

  const eyebrowSize = await page.locator('.eyebrow').first().evaluate(element => parseFloat(getComputedStyle(element).fontSize));
  expect(eyebrowSize).toBeGreaterThanOrEqual(13);

  const primaryAction = page.locator('.button-primary').first();
  const primaryBox = await primaryAction.boundingBox();
  expect(primaryBox).not.toBeNull();
  expect(primaryBox.height).toBeGreaterThanOrEqual(44);
  const primaryRadius = await primaryAction.evaluate(element => getComputedStyle(element).borderTopLeftRadius);
  expect(primaryRadius).toBe('6px');

  const motifBackground = await page.locator('main.thar-sunset-story .section-head .eyebrow').first().evaluate(element => getComputedStyle(element, '::before').backgroundImage);
  expect(motifBackground).toContain('bastion-line.svg');

  const width = page.viewportSize().width;
  const navToggle = page.locator('.nav-toggle');
  const nav = page.locator('#main-nav');
  if (width <= 1366) {
    await expect(navToggle).toBeVisible();
    const toggleBox = await navToggle.boundingBox();
    expect(toggleBox).not.toBeNull();
    expect(toggleBox.width).toBeGreaterThanOrEqual(44);
    expect(toggleBox.height).toBeGreaterThanOrEqual(44);

    await navToggle.click();
    await expect(nav).toBeVisible();
    const firstItem = page.locator('#main-nav [data-nav-item]').first();
    await firstItem.locator('.nav-sub-toggle').click();
    await expect(firstItem.locator('.nav-sub-toggle')).toHaveAttribute('aria-expanded', 'true');
    await expect(firstItem.locator('.nav-sub')).toBeVisible();
  } else {
    await expect(navToggle).toBeHidden();
    await expect(nav).toBeVisible();
  }

  const noOverflow = await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);
  expect(noOverflow, 'foundation must not introduce horizontal overflow').toBe(true);
});
