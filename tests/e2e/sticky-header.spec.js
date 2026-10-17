const { test, expect } = require('@playwright/test');

test('outer site header remains pinned after scrolling on mobile and desktop', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run this shared header contract once.');
  const routes = ['/', '/room.php', '/restaurant.php', '/contact.php'];
  for (const width of [390, 1280]) {
    await page.setViewportSize({ width, height: width < 500 ? 844 : 800 });
    for (const route of routes) {
      await page.goto(route, { waitUntil: 'domcontentloaded' });
      await page.evaluate(() => window.scrollTo(0, 1800));
      const rect = await page.locator('header.site-header').evaluate(element => {
        const bounds = element.getBoundingClientRect();
        return { top: bounds.top, position: getComputedStyle(element).position, navPosition: getComputedStyle(element.querySelector('.nav-wrap')).position };
      });
      expect(rect.position, route + ' outer header should be sticky at ' + width + 'px').toBe('sticky');
      expect(rect.top, route + ' header top at ' + width + 'px').toBeGreaterThanOrEqual(-0.5);
      expect(rect.top, route + ' header top at ' + width + 'px').toBeLessThanOrEqual(0.5);
      expect(rect.navPosition, route + ' inner navigation should not use nested sticky positioning').toBe('relative');
    }
  }
});
