const { test, expect } = require('@playwright/test');
const fs = require('node:fs');
const path = require('node:path');

const desktopProject = 'desktop-1280';
const routes = [
  { path: '/index.php', name: 'home' },
  { path: '/room.php', name: 'rooms' },
  { path: '/pack.php', name: 'packages' },
  { path: '/roof.php', name: 'dining' },
  { path: '/direction.php', name: 'directions' },
  { path: '/contact.php', name: 'contact' },
  { path: '/gallery.php', name: 'gallery' },
  { path: '/blog.php', name: 'journal' },
];

test.describe('Visual asset and typography evidence', () => {
  test('capture route-level image delivery, semantics and font diagnostics', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== desktopProject, 'Asset inventory runs once at desktop width.');

    const report = {
      generatedAt: new Date().toISOString(),
      project: testInfo.project.name,
      viewport: testInfo.project.use.viewport,
      note: 'Browser diagnostics, not field Core Web Vitals or a substitute for human photo/crop review.',
      routes: [],
    };

    for (const route of routes) {
      const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
      expect(response, route.path + ' should return a document response').not.toBeNull();
      expect(response.status(), route.path + ' should respond successfully').toBe(200);

      await page.evaluate(() => document.fonts.ready);
      const routeReport = await page.evaluate(() => {
        const visible = (element) => {
          const style = getComputedStyle(element);
          const rect = element.getBoundingClientRect();
          return style.display !== 'none' && style.visibility !== 'hidden' &&
            rect.width > 0 && rect.height > 0;
        };
        const images = Array.from(document.images).map((img) => {
          const rect = img.getBoundingClientRect();
          return {
            src: img.getAttribute('src') || '',
            srcset: img.getAttribute('srcset') || '',
            sizes: img.getAttribute('sizes') || '',
            currentSrc: img.currentSrc || '',
            altPresent: img.hasAttribute('alt'),
            alt: img.getAttribute('alt'),
            complete: img.complete,
            loaded: img.complete && img.naturalWidth > 0,
            naturalWidth: img.naturalWidth,
            naturalHeight: img.naturalHeight,
            renderedWidth: Math.round(rect.width),
            renderedHeight: Math.round(rect.height),
            visibleInViewport: visible(img) && rect.bottom > 0 && rect.top < innerHeight &&
              rect.right > 0 && rect.left < innerWidth,
            loading: img.loading,
            fetchPriority: img.fetchPriority || '',
          };
        });
        const navigation = performance.getEntriesByType('navigation')[0];
        const resources = performance.getEntriesByType('resource').map((entry) => ({
          name: entry.name,
          initiatorType: entry.initiatorType,
          transferSize: entry.transferSize || 0,
          durationMs: Math.round(entry.duration),
          decodedBodySize: entry.decodedBodySize || 0,
          encodedBodySize: entry.encodedBodySize || 0,
        })).filter((entry) => /\.(?:avif|webp|jpe?g|png|gif|svg|woff2?)(?:[?#]|$)/i.test(entry.name));
        const h1 = document.querySelector('main h1, h1');
        const body = document.body;
        return {
          title: document.title,
          h1: h1 ? h1.textContent.trim() : '',
          canonical: document.querySelector('link[rel="canonical"]')?.href || '',
          typography: {
            h1FontFamily: h1 ? getComputedStyle(h1).fontFamily : '',
            h1FontSizePx: h1 ? Math.round(parseFloat(getComputedStyle(h1).fontSize) * 100) / 100 : null,
            bodyFontFamily: getComputedStyle(body).fontFamily,
            bodyFontSizePx: Math.round(parseFloat(getComputedStyle(body).fontSize) * 100) / 100,
            documentFontsStatus: document.fonts.status,
            sourceSerifReady: document.fonts.check('400 24px "Source Serif 4"'),
            interReady: document.fonts.check('400 16px Inter'),
          },
          images,
          resources,
          navigation: navigation ? {
            domContentLoadedMs: Math.round(navigation.domContentLoadedEventEnd),
            loadEventMs: Math.round(navigation.loadEventEnd),
            transferSize: navigation.transferSize || 0,
          } : null,
        };
      });

      const missingAlt = routeReport.images.filter((img) => !img.altPresent);
      const failedVisibleImages = routeReport.images.filter((img) => img.visibleInViewport && !img.loaded);
      expect(missingAlt, route.path + ' images must declare alt (empty is valid when decorative)').toEqual([]);
      expect(failedVisibleImages, route.path + ' must not show broken visible images').toEqual([]);
      expect(routeReport.typography.h1FontFamily, route.path + ' H1 should use the declared display stack')
        .toContain('Source Serif 4');
      expect(routeReport.typography.bodyFontFamily, route.path + ' body should use the declared body stack')
        .toContain('Inter');
      report.routes.push({ route: route.path, ...routeReport });
    }

    const outputDir = path.join(process.cwd(), 'artifacts', 'image-accessibility');
    fs.mkdirSync(outputDir, { recursive: true });
    const outputPath = path.join(outputDir, 'visual-asset-audit-' + testInfo.project.name + '.json');
    fs.writeFileSync(outputPath, JSON.stringify(report, null, 2) + '\n');
    testInfo.annotations.push({
      type: 'visual-asset-audit',
      description: outputPath,
    });
  });
});
