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
const screenshotRoutes = new Set(['/', '/room.php', '/contact.php', '/direction.php']);

test('sitemap contains at least one browser-test route', async () => {
  expect(routes.length).toBeGreaterThan(0);
});

for (const route of routes) {
  test(`renders ${route} without horizontal overflow`, async ({ page }, testInfo) => {
    const pageErrors = [];
    page.on('pageerror', error => pageErrors.push(error.message));

    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for ${route}`).not.toBeNull();
    expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

    await expect(page.locator('html')).toHaveAttribute('lang', /.+/);
    await expect(page).toHaveTitle(/\S/);

    const dimensions = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document, `Horizontal overflow on ${route}: ${JSON.stringify(dimensions)}`)
      .toBeLessThanOrEqual(dimensions.viewport + 1);

    // Wait for web fonts where available without making external network idle a requirement.
    await page.evaluate(() => Promise.race([
      document.fonts.ready,
      new Promise(resolve => setTimeout(resolve, 1500)),
    ]));

    if (screenshotRoutes.has(route)) {
      const routeName = route === '/' ? 'home' : route.replace(/^\//, '').replace(/[^a-zA-Z0-9._-]+/g, '-');
      const screenshotDir = path.join(process.cwd(), 'artifacts', 'screenshots', testInfo.project.name);
      fs.mkdirSync(screenshotDir, { recursive: true });
      const screenshotPath = path.join(screenshotDir, `${routeName}.jpg`);
  
      await page.screenshot({ path: screenshotPath, type: 'jpeg', quality: 72, fullPage: true, animations: 'disabled' });
      await testInfo.attach(`screenshot-${routeName}`, {
        path: screenshotPath,
        contentType: 'image/jpeg',
      });
    }


    expect(pageErrors, `Uncaught browser errors on ${route}`).toEqual([]);
  });
}


test('mobile navigation opens and closes with Escape', async ({ page }, testInfo) => {
  test.skip(!testInfo.project.name.startsWith('mobile-'), 'Mobile navigation is only shown at mobile/tablet widths');

  await page.goto('/');
  const toggle = page.getByRole('button', { name: 'Open navigation' });
  const navigation = page.getByRole('navigation', { name: 'Primary navigation' });

  await toggle.click();
  const menuButton = page.locator('.nav-toggle');
  await expect(menuButton).toHaveAttribute('aria-expanded', 'true');
  await expect(navigation).toBeVisible();

  await page.keyboard.press('Escape');
  await expect(menuButton).toHaveAttribute('aria-expanded', 'false');
  await expect(navigation).toBeHidden();
});
