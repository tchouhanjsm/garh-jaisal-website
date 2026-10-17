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
    expected: () => 'purple-room',
  },
  {
    route: '/catalogue.php',
    selector: '.catalogue-hero-media img',
    expected: () => null,
  },
];

function responsiveCandidateForWidth(path, naturalWidth) {
  const match = path.match(/\/img\/responsive\/(?:v2\/)?([^/]+)-(480|800|1200)\.webp$/);
  if (!match) return null;
  return { stem: match[1], width: Number(match[2]), naturalWidth };
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
          const activePreloads = Array.from(document.querySelectorAll('link[rel="preload"][as="image"]'))
            .filter(link => !link.media || window.matchMedia(link.media).matches)
            .map(link => new URL(link.href, document.baseURI).pathname);
          return {
            currentPath,
            activePreloads,
            width: img.naturalWidth,
            height: img.naturalHeight,
            fetchPriority: img.getAttribute('fetchpriority'),
            declaredWidth: Number(img.getAttribute('width')),
            declaredHeight: Number(img.getAttribute('height')),
          };
        });
        const expectedStem = hero.expected(width);
        const candidate = responsiveCandidateForWidth(state.currentPath, state.width);
        if (expectedStem && candidate) {
          expect(candidate.stem, hero.route + ' uses the intended responsive photo at ' + width + 'px').toBe(expectedStem + (expectedStem === 'facade' || expectedStem === 'fort-from-roof' || expectedStem === 'purple-room' ? '' : ''));
          expect(candidate.width, hero.route + ' candidate width must match its filename').toBe(state.width);
        } else if (expectedStem) {
          expect(state.currentPath, hero.route + ' keeps the approved WebP fallback when no candidate is selected').toContain('/img/optimized/');
        }
        expect(state.width, hero.route + ' LCP image must decode').toBeGreaterThan(0);
        expect(state.height, hero.route + ' LCP image must decode').toBeGreaterThan(0);
        expect(state.declaredWidth, hero.route + ' must reserve intrinsic image width').toBeGreaterThan(0);
        expect(state.declaredHeight, hero.route + ' must reserve intrinsic image height').toBeGreaterThan(0);
        expect(Math.abs((state.declaredWidth / state.declaredHeight) - (state.width / state.height))).toBeLessThan(0.02);
        expect(state.fetchPriority, hero.route + ' LCP image should stay high priority').toBe('high');
        if (candidate) {
          expect(state.activePreloads, hero.route + ' must preload a matching responsive candidate set').not.toHaveLength(0);
        } else {
          expect(state.activePreloads, hero.route + ' must preload its selected high-intent image').toContain(state.currentPath);
        }

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
      preloads: Array.from(document.querySelectorAll('link[rel="preload"][as="image"]'))
        .filter(link => !link.media || window.matchMedia(link.media).matches)
        .map(link => new URL(link.href, document.baseURI).pathname),
    }));
    expect(state.path).toBe('/img/optimized/v2/fort-from-roof.webp');
    expect(state.preloads).toContain(state.path);
    expect(state.width).toBeGreaterThan(0);
    expect(state.height).toBeGreaterThan(0);
    expect(state.width).toBe(state.decodedWidth);
    expect(state.height).toBe(state.decodedHeight);
  } finally {
    await context.close();
  }
});
