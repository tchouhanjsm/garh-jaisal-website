const { test, expect } = require('@playwright/test');
const routes = ['/', '/room.php', '/roof.php', '/gallery.php'];

test('shared reduced-motion preferences override route styling across public page families', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run shared preference checks once at desktop width.');
  await page.emulateMedia({ reducedMotion: 'reduce' });
  for (const route of routes) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ' should return a document response').not.toBeNull();
    expect(response.status(), route + ' should respond successfully').toBeLessThan(400);
    const state = await page.evaluate(() => {
      const stylesheets = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(link => new URL(link.href, document.baseURI).pathname);
      const preferenceIndex = stylesheets.indexOf('/css/accessibility.css');
      const otherIndexes = stylesheets.map((path, index) => ({ path, index })).filter(item => item.path.endsWith('.css') && item.path !== '/css/accessibility.css').map(item => item.index);
      const button = document.querySelector('.button-primary, .nav-book, button');
      const transitions = button ? getComputedStyle(button).transitionDuration.split(',').map(value => Number.parseFloat(value)) : [];
      return { loaded: preferenceIndex >= 0, last: preferenceIndex >= 0 && otherIndexes.every(index => preferenceIndex > index), transitions, scroll: getComputedStyle(document.documentElement).scrollBehavior };
    });
    expect(state.loaded, route + ' must load shared accessibility.css').toBe(true);
    expect(state.last, route + ' preference stylesheet must follow route and shared CSS').toBe(true);
    expect(state.transitions.length, route + ' should render an interactive control').toBeGreaterThan(0);
    expect(state.transitions.every(value => value === 0), route + ' controls must not transition with reduced motion').toBe(true);
    expect(state.scroll, route + ' must not smooth-scroll with reduced motion').toBe('auto');
    if (route === '/gallery.php') {
      const item = page.locator('.gallery-item').first();
      const image = item.locator('img');
      await item.hover();
      await expect.poll(() => image.evaluate(node => getComputedStyle(node).transform)).toBe('none');
    }
  }
});
