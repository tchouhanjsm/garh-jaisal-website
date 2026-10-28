const { test, expect } = require('@playwright/test');

const desktopProject = 'desktop-1280';
const mobileProjects = new Set(['mobile-320', 'mobile-390']);
const routeInventory = [
  '/index.php',
  '/room.php',
  '/pack.php',
  '/roof.php',
  '/direction.php',
  '/contact.php',
  '/blog.php',
  '/gallery.php',
  '/about.php',
];
const mobileRouteInventory = ['/index.php', '/room.php', '/pack.php', '/contact.php'];

test.describe('Cross-page first-impression quality', () => {
  test('high-intent routes expose a valid page identity and do not overflow desktop', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== desktopProject, 'Route identity smoke test runs once at desktop width.');

    for (const route of routeInventory) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' should return a document response').not.toBeNull();
      expect(response.status(), route + ' should respond successfully').toBe(200);
      await expect(page).toHaveTitle(/\S/, { timeout: 8000 });

      const identity = await page.evaluate(() => {
        const visible = (element) => {
          if (!element) return false;
          const style = window.getComputedStyle(element);
          const bounds = element.getBoundingClientRect();
          return style.display !== 'none' && style.visibility !== 'hidden' &&
            bounds.width > 0 && bounds.height > 0;
        };
        const canonical = document.querySelector('link[rel="canonical"]');
        const headings = Array.from(document.querySelectorAll('main h1, h1')).filter(visible);
        return {
          title: document.title.trim(),
          canonical: canonical ? canonical.href : '',
          h1Count: headings.length,
          h1Text: headings[0] ? headings[0].textContent.trim() : '',
          viewportWidth: document.documentElement.clientWidth,
          documentWidth: document.documentElement.scrollWidth,
        };
      });

      expect(identity.title.length, route + ' should have a non-empty title').toBeGreaterThan(8);
      expect(identity.canonical, route + ' should expose an absolute HTTPS canonical').toMatch(/^https:\/\//);
      expect(identity.h1Count, route + ' should expose a visible H1').toBeGreaterThan(0);
      expect(identity.h1Text.length, route + ' H1 should contain useful text').toBeGreaterThan(2);
      expect(identity.documentWidth, route + ' should not overflow at 1280px')
        .toBeLessThanOrEqual(identity.viewportWidth + 1);
    }
  });

  test('priority routes remain inside small-screen viewports', async ({ page }, testInfo) => {
    test.skip(!mobileProjects.has(testInfo.project.name), 'Small-screen route smoke test runs on mobile projects only.');

    for (const route of mobileRouteInventory) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
      expect(response, route + ' should return a document response').not.toBeNull();
      expect(response.status(), route + ' should respond successfully').toBe(200);
      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(dimensions.document, route + ' should not overflow at ' + dimensions.viewport + 'px')
        .toBeLessThanOrEqual(dimensions.viewport + 1);
    }
  });

  test('homepage makes the booking action and hero image available in the first viewport', async ({ page }) => {
    const response = await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
    expect(response).not.toBeNull();
    expect(response.status()).toBe(200);

    const bookingAction = page.locator('main a.button-primary').filter({ hasText: /book/i }).first();
    await expect(bookingAction).toBeVisible();
    const bookingHref = await bookingAction.getAttribute('href');
    expect(bookingHref).toBeTruthy();

    const heroImage = page.locator('.hero img, .hero-media img').first();
    if (await heroImage.count()) {
      await expect.poll(async () => heroImage.evaluate((img) => img.complete && img.naturalWidth > 0), {
        message: 'Homepage hero image should load instead of showing a broken/empty visual',
        timeout: 10000,
      }).toBe(true);
      const imageState = await heroImage.evaluate((img) => ({
        currentSrc: img.currentSrc,
        naturalWidth: img.naturalWidth,
        loading: img.loading,
      }));
      expect(imageState.currentSrc).toMatch(/^https?:\/\//);
      expect(imageState.naturalWidth).toBeGreaterThan(0);
    } else {
      throw new Error('Homepage hero image selector found no image; review the hero markup instead of silently skipping image QA.');
    }

    const navigationTiming = await page.evaluate(() => {
      const entry = performance.getEntriesByType('navigation')[0];
      return entry ? {
        domContentLoadedMs: Math.round(entry.domContentLoadedEventEnd),
        loadEventMs: Math.round(entry.loadEventEnd),
        transferSize: entry.transferSize,
      } : null;
    });
    expect(navigationTiming).not.toBeNull();
    test.info().annotations.push({
      type: 'local-performance-diagnostic',
      description: JSON.stringify(navigationTiming),
    });
  });
});
