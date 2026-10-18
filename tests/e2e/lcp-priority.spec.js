const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';
const RESPONSIVE_WIDTHS = [480, 800, 1200];
const HEROES = [
  {
    route: '/',
    selector: '.hero-media picture img',
    expected: width => width < 768 ? 'facade' : 'fort-from-roof',
  },
  {
    route: '/room.php',
    selector: '.rooms-hero .page-hero-media img',
    expected: () => 'sitting-room',
  },
  {
    route: '/catalogue.php',
    selector: '.catalogue-hero-media img',
    expected: () => 'desert3',
    approvedWebpFallback: '/img/2.webp',
  },
];

function responsiveCandidateForPath(pathname) {
  const match = pathname.match(/\/img\/responsive\/(?:v2\/)?([^/]+)-(480|800|1200)\.webp$/);
  return match ? { stem: match[1], width: Number(match[2]) } : null;
}

function pathMatchesPreload(preload, selectedPath) {
  if (!preload.imagesrcset) return preload.href === selectedPath;
  return preload.imagesrcset.split(',').some(candidate => {
    const url = candidate.trim().split(/\s+/)[0];
    try { return new URL(url, BASE_URL).pathname === selectedPath; } catch { return false; }
  });
}

test('high-intent LCP images are preloaded before render at mobile and desktop widths', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'This contract creates its own viewport contexts');
  test.setTimeout(120_000);

  for (const width of [390, 1280]) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width, height: width < 768 ? 844 : 800 },
      deviceScaleFactor: 1,
      isMobile: width <= 390,
      hasTouch: width <= 390,
    });
    const page = await context.newPage();

    try {
      for (const hero of HEROES) {
        const response = await page.goto(hero.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, hero.route + ' must respond at ' + width + 'px').not.toBeNull();
        expect(response.status(), hero.route + ' must return a successful response').toBeLessThan(400);

        const image = page.locator(hero.selector);
        await expect(image, hero.route + ' must expose its LCP image').toBeVisible();
        await image.evaluate(img => img.decode());

        const state = await image.evaluate(img => {
          const currentPath = new URL(img.currentSrc || img.src, document.baseURI).pathname;
          const preloads = Array.from(document.querySelectorAll('link[rel="preload"][as="image"]'))
            .filter(link => !link.media || window.matchMedia(link.media).matches)
            .map(link => ({
              href: new URL(link.href, document.baseURI).pathname,
              imagesrcset: link.getAttribute('imagesrcset') || '',
              imagesizes: link.getAttribute('imagesizes') || '',
            }));
          // For <picture>, currentSrc may come from an active <source>, not the
          // fallback <img>. Read the candidate list that actually owns this URL.
          const activeSource = Array.from(img.closest('picture')?.querySelectorAll('source[srcset]') || [])
            .find(source => source.srcset.split(',').some(candidate => {
              try {
                return new URL(candidate.trim().split(/\s+/)[0], document.baseURI).pathname === currentPath;
              } catch {
                return false;
              }
            }));
          const responsiveOwner = activeSource || img;
          return {
            currentPath,
            preloads,
            width: img.naturalWidth,
            height: img.naturalHeight,
            fetchPriority: img.getAttribute('fetchpriority'),
            declaredWidth: Number(responsiveOwner.getAttribute('width') || img.getAttribute('width')),
            declaredHeight: Number(responsiveOwner.getAttribute('height') || img.getAttribute('height')),
            sizes: responsiveOwner.getAttribute('sizes') || img.getAttribute('sizes') || '',
            srcset: responsiveOwner.getAttribute('srcset') || img.getAttribute('srcset') || '',
          };
        });
        const candidate = responsiveCandidateForPath(state.currentPath);
        const expectedStem = hero.expected(width);
        if (candidate) {
          expect(candidate.stem, hero.route + ' must use the intended image variant at ' + width + 'px').toBe(expectedStem);
          expect(RESPONSIVE_WIDTHS, 'Selected derivative suffix must belong to the supported variant widths').toContain(candidate.width);
          expect(state.srcset.split(',').some(item => {
            try { return new URL(item.trim().split(/\s+/)[0], BASE_URL).pathname === state.currentPath; } catch { return false; }
          }), hero.route + ' selected candidate must appear in its own srcset').toBe(true);
          expect(state.sizes, hero.route + ' responsive source must expose a sizes hint').not.toBe('');
        } else {
          if (hero.approvedWebpFallback) {
            expect(state.currentPath, hero.route + ' must use its explicitly configured WebP fallback').toBe(hero.approvedWebpFallback);
            expect(state.currentPath, hero.route + ' fallback must be a WebP image').toMatch(/\.webp$/);
          } else {
            expect(state.currentPath, hero.route + ' must use its configured optimized fallback').toContain('/img/optimized/');
          }
        }

        expect(state.width, hero.route + ' LCP image must decode').toBeGreaterThan(0);
        expect(state.height, hero.route + ' LCP image must decode').toBeGreaterThan(0);
        expect(state.declaredWidth, hero.route + ' must reserve intrinsic image width').toBeGreaterThan(0);
        expect(state.declaredHeight, hero.route + ' must reserve intrinsic image height').toBeGreaterThan(0);
        expect(Math.abs((state.declaredWidth / state.declaredHeight) - (state.width / state.height))).toBeLessThan(0.02);
        expect(state.fetchPriority, hero.route + ' LCP image should stay high priority').toBe('high');
        expect(
          state.preloads.some(preload => pathMatchesPreload(preload, state.currentPath)),
          hero.route + ' must preload the selected image or include it in the preload candidate list'
        ).toBe(true);

        const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
        expect(overflow, hero.route + ' must not create horizontal overflow at ' + width + 'px').toBe(false);
      }
    } finally {
      await context.close();
    }
  }
});

test('Journal featured image is eager, dimension-stable and preloaded on desktop', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'This contract creates its own desktop context');
  const context = await browser.newContext({
    baseURL: BASE_URL,
    viewport: { width: 1280, height: 800 },
    deviceScaleFactor: 1,
  });
  const page = await context.newPage();

  try {
    const response = await page.goto('/blog.php', { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, 'Journal must respond').not.toBeNull();
    expect(response.status(), 'Journal must return a successful response').toBeLessThan(400);

    const image = page.locator('.blog-card--featured img');
    await expect(image).toBeVisible();
    await expect(image).toHaveAttribute('loading', 'eager');
    await image.evaluate(img => img.decode());

    const state = await image.evaluate(img => ({
      path: new URL(img.currentSrc || img.src, document.baseURI).pathname,
      width: Number(img.getAttribute('width')),
      height: Number(img.getAttribute('height')),
      decodedWidth: img.naturalWidth,
      decodedHeight: img.naturalHeight,
      srcset: img.getAttribute('srcset') || '',
      sizes: img.getAttribute('sizes') || '',
      preloads: Array.from(document.querySelectorAll('link[rel="preload"][as="image"]'))
        .filter(link => !link.media || window.matchMedia(link.media).matches)
        .map(link => ({
          href: new URL(link.href, document.baseURI).pathname,
          srcset: link.getAttribute('imagesrcset') || '',
          sizes: link.getAttribute('imagesizes') || '',
        })),
    }));
    expect(state.path).toMatch(/^\/img\/responsive\/j7-(480|800|1200)\.webp$/);
    expect(state.srcset).not.toBe('');
    expect(state.srcset.split(',').some(candidate => {
      try { return new URL(candidate.trim().split(/\s+/)[0], BASE_URL).pathname === state.path; } catch { return false; }
    })).toBe(true);
    expect(state.sizes).toBe('(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw');
    expect(state.preloads.some(preload =>
      preload.srcset.split(',').some(candidate => {
        try { return new URL(candidate.trim().split(/\s+/)[0], BASE_URL).pathname === state.path; } catch { return false; }
      }) && preload.sizes === state.sizes
    )).toBe(true);
    expect(state.width).toBeGreaterThan(0);
    expect(state.height).toBeGreaterThan(0);
    expect(state.decodedWidth).toBeGreaterThan(0);
    expect(state.decodedHeight).toBeGreaterThan(0);
    expect(Math.abs((state.width / state.height) - (state.decodedWidth / state.decodedHeight))).toBeLessThan(0.02);
  } finally {
    await context.close();
  }
});


test('high-intent page photography selects generated responsive candidates with sizes hints', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive image delivery spotcheck creates its own viewport contexts');
  test.setTimeout(120_000);
  const cases = [
    { route: '/about.php', selector: '.wide-feature .media img', expectedStem: 'big6' },
    { route: '/blog.php', selector: '.blog-card--featured img', expectedStem: 'j7' },
    { route: '/gallery.php', selector: '.gallery-grid img', expectedStem: 'facade' },
    { route: '/car-tours.php', selector: '.experience-card img', expectedStem: 'j10' },
  ];
  for (const width of [390, 1280]) {
    const context = await browser.newContext({ baseURL: BASE_URL, viewport: { width, height: width < 768 ? 844 : 800 }, deviceScaleFactor: 1, isMobile: width <= 390, hasTouch: width <= 390 });
    const page = await context.newPage();
    try {
      for (const item of cases) {
        const response = await page.goto(item.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, item.route + ' must respond at ' + width + 'px').not.toBeNull();
        expect(response.status(), item.route + ' must return a successful response').toBeLessThan(400);
        const image = page.locator(item.selector).first();
        await image.scrollIntoViewIfNeeded();
        await image.evaluate(img => img.decode());
        const state = await image.evaluate(img => ({
          path: new URL(img.currentSrc || img.src, document.baseURI).pathname,
          srcset: img.getAttribute('srcset') || '',
          sizes: img.getAttribute('sizes') || '',
          width: Number(img.getAttribute('width')),
          height: Number(img.getAttribute('height')),
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
        }));
        expect(state.path, item.route + ' should select a resized WebP candidate').toMatch(new RegExp('^/img/responsive/(?:v2/)?' + item.expectedStem + '-(480|800|1200)\\.webp$'));
        expect(state.srcset, item.route + ' must publish a responsive candidate list').not.toBe('');
        expect(state.srcset.split(',').some(candidate => {
          try { return new URL(candidate.trim().split(/\s+/)[0], BASE_URL).pathname === state.path; } catch { return false; }
        }), item.route + ' selected image must appear in srcset').toBe(true);
        expect(state.sizes, item.route + ' must publish a sizes hint').not.toBe('');
        expect(state.width).toBeGreaterThan(0);
        expect(state.height).toBeGreaterThan(0);
        expect(state.naturalWidth).toBeGreaterThan(0);
        expect(state.naturalHeight).toBeGreaterThan(0);
        expect(Math.abs((state.width / state.height) - (state.naturalWidth / state.naturalHeight))).toBeLessThan(0.02);
      }
    } finally { await context.close(); }
  }
});
