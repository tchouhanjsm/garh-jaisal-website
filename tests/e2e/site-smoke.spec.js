const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

function getSitemapRoutes() {
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  const locations = [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)];
  const routes = locations.map(([, value]) => {
    const url = new URL(value.trim());
    return url.pathname || '/';
  });
  return [...new Set(routes)].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

const routes = getSitemapRoutes();

test('sitemap contains at least one browser-test route', async () => {
  expect(routes.length).toBeGreaterThan(0);
});

for (const route of routes) {
  test(`renders ${route} and captures a viewport screenshot`, async ({ page }, testInfo) => {
    const pageErrors = [];
    page.on('pageerror', error => pageErrors.push(error.message));

    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for ${route}`).not.toBeNull();
    expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

    await expect(page.locator('html')).toHaveAttribute('lang', /.+/);
    await expect(page).toHaveTitle(/\S/);

    // Wait for web fonts where available without making external network idle a requirement.
    await page.evaluate(() => Promise.race([
      document.fonts.ready,
      new Promise(resolve => setTimeout(resolve, 1500)),
    ]));

    const routeName = route === '/' ? 'home' : route.replace(/^\//, '').replace(/[^a-zA-Z0-9._-]+/g, '-');
    const screenshotDir = path.join(process.cwd(), 'artifacts', 'screenshots', testInfo.project.name);
    fs.mkdirSync(screenshotDir, { recursive: true });
    const screenshotPath = path.join(screenshotDir, `${routeName}.png`);

    await page.screenshot({ path: screenshotPath, fullPage: true, animations: 'disabled' });
    await testInfo.attach(`screenshot-${routeName}`, {
      path: screenshotPath,
      contentType: 'image/png',
    });

    expect(pageErrors, `Uncaught browser errors on ${route}`).toEqual([]);
  });
}
