const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

function getSitemapRoutes() {
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  return [...new Set(
    [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
      .map(([, value]) => new URL(value.trim()))
      .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
      .map(url => url.pathname || '/')
  )].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

test('priority images reserve their intrinsic aspect ratio on sitemap routes', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Image geometry regression runs once at desktop width');

  const problems = [];
  for (const route of getSitemapRoutes()) {
    await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    const images = await page.locator('img[fetchpriority="high"]').evaluateAll(async nodes => {
      await Promise.all(nodes.map(img => img.decode ? img.decode().catch(() => undefined) : Promise.resolve()));
      return nodes.map(img => {
        const currentPath = (() => {
          try {
            return new URL(img.currentSrc || img.src, document.baseURI).pathname.slice(1);
          } catch {
            return '';
          }
        })();
        const matchingSource = Array.from(img.closest('picture')?.querySelectorAll('source[srcset]') || []).find(source => (
          source.srcset.split(',').some(candidate => {
            try {
              const candidatePath = new URL(candidate.trim().split(' ')[0], document.baseURI).pathname.slice(1);
              return candidatePath === currentPath;
            } catch {
              return false;
            }
          })
        ));
        const dimensions = matchingSource
          && Number(matchingSource.getAttribute('width')) > 0
          && Number(matchingSource.getAttribute('height')) > 0
          ? matchingSource
          : img;
        return {
          src: img.currentSrc || img.src,
          width: Number(dimensions.getAttribute('width')),
          height: Number(dimensions.getAttribute('height')),
          dimensionsFrom: dimensions === img ? 'img' : 'picture-source',
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
          loaded: img.complete && img.naturalWidth > 0 && img.naturalHeight > 0,
        };
      });
    });

    for (const image of images) {
      if (!image.loaded) {
        problems.push({ route, ...image, issue: 'priority-image-failed-to-load' });
        continue;
      }
      if (!(image.width > 0 && image.height > 0)) {
        problems.push({ route, ...image, issue: 'missing-positive-dimensions' });
        continue;
      }
      if (image.naturalWidth > 0 && image.naturalHeight > 0) {
        const declaredRatio = image.width / image.height;
        const intrinsicRatio = image.naturalWidth / image.naturalHeight;
        if (Math.abs(declaredRatio - intrinsicRatio) > 0.02) {
          problems.push({ route, ...image, issue: 'declared-aspect-ratio-mismatch' });
        }
      }
    }
  }

  expect(problems, 'Every high-priority image should reserve geometry with dimensions matching its intrinsic aspect ratio').toEqual([]);
});
