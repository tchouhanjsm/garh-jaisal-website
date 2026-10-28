const { test, expect } = require('@playwright/test');
const { getSitemapRoutes } = require('./helpers/sitemap-routes');

// Derive coverage from the committed sitemap so newly published routes inherit
// the same Open Graph and Twitter/X image-description contract automatically.
const routes = getSitemapRoutes().map(path => ({
  path,
  label: path === '/' ? 'Home' : path.slice(1),
}));

test('all sitemap routes expose accessible social preview image metadata', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Social metadata is viewport-independent');
  test.setTimeout(180_000);

  for (const route of routes) {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for ${route.path}`).not.toBeNull();
    expect(response.status(), `Unexpected HTTP status for ${route.path}`).toBeLessThan(400);

    await expect(page.locator('head meta[property="og:image"]'), `${route.label}: one Open Graph image`).toHaveCount(1);
    await expect(page.locator('head meta[property="og:image:alt"]'), `${route.label}: one Open Graph image description`).toHaveCount(1);
    await expect(page.locator('head meta[name="twitter:image"]'), `${route.label}: one Twitter/X image`).toHaveCount(1);
    await expect(page.locator('head meta[name="twitter:image:alt"]'), `${route.label}: one Twitter/X image description`).toHaveCount(1);

    const metadata = await page.evaluate(() => ({
      ogImage: document.querySelector('meta[property="og:image"]')?.content || '',
      ogAlt: document.querySelector('meta[property="og:image:alt"]')?.content || '',
      twitterImage: document.querySelector('meta[name="twitter:image"]')?.content || '',
      twitterAlt: document.querySelector('meta[name="twitter:image:alt"]')?.content || '',
    }));

    expect(metadata.ogImage, `${route.label}: Open Graph image must be absolute HTTPS`).toMatch(/^https:\/\//);
    expect(metadata.twitterImage, `${route.label}: Twitter/X image must be absolute HTTPS`).toMatch(/^https:\/\//);
    expect(metadata.ogAlt.trim(), `${route.label}: Open Graph image description cannot be empty`).not.toBe('');
    expect(metadata.twitterAlt.trim(), `${route.label}: Twitter/X image description cannot be empty`).not.toBe('');

    if (new URL(metadata.ogImage).href === new URL(metadata.twitterImage).href) {
      expect(metadata.twitterAlt, `${route.label}: a shared image must use a consistent description`).toBe(metadata.ogAlt);
    }
  }
});
