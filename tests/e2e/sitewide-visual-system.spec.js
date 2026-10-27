const { test, expect } = require('@playwright/test');
const fs = require('node:fs');
const path = require('node:path');

const BASE_URL = 'http://127.0.0.1:8080';
const VIEWPORTS = [
  { width: 320, height: 700, mobile: true },
  { width: 390, height: 844, mobile: true },
  { width: 768, height: 900, mobile: false },
  { width: 1280, height: 800, mobile: false },
  { width: 1440, height: 900, mobile: false },
];

const PAGE_ARCHETYPES = [
  { route: '/', hero: 'main .hero' },
  { route: '/room.php', hero: 'main .rooms-hero', media: 'main .room-card-media' },
  { route: '/pack.php', hero: 'main .package-hero', media: 'main .package-card-media' },
  { route: '/catalogue.php', hero: 'main .catalogue-hero', media: 'main .product-card-media' },
  { route: '/desert-safari.php', hero: 'main .experience-hero', media: 'main .experience-card-media' },
  { route: '/restaurant.php', hero: 'main .restaurant-hero' },
  { route: '/gallery.php', hero: 'main .gallery-hero' },
  { route: '/blog.php', hero: 'main .article-hero', media: 'main .blog-card-image' },
  { route: '/direction.php', hero: 'main .page-hero' },
  { route: '/contact.php', hero: 'main .contact-hero' },
  { route: '/jaisalmer-fort-guide.php', hero: 'main .article-hero' },
];
const PHOTO_HERO_ROUTES = new Set(['/', '/room.php', '/pack.php', '/catalogue.php', '/desert-safari.php']);
const VISUAL_REVIEW_ROUTES = new Set(['/', '/room.php', '/pack.php', '/catalogue.php', '/restaurant.php', '/gallery.php']);
const VISUAL_REVIEW_WIDTHS = new Set([320, 768, 1440]);
const VISUAL_REVIEW_DIR = path.join('artifacts', 'screenshots', 'responsive-spotchecks');

function normalizePath(value) {
  try { return new URL(value, BASE_URL).pathname; } catch { return ''; }
}

test('shared visual system stays consistent across five viewports and eleven page archetypes', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'This contract creates its own five viewport contexts');
  test.setTimeout(180_000);

  for (const viewport of VIEWPORTS) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
      isMobile: viewport.mobile,
      hasTouch: viewport.mobile,
    });
    await context.route(/https:\/\/(fonts\.googleapis\.com|fonts\.gstatic\.com|www\.googletagmanager\.com)\//, route => route.abort());

    const page = await context.newPage();
    try {
      for (const archetype of PAGE_ARCHETYPES) {
        const response = await page.goto(archetype.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, archetype.route + ' must respond at ' + viewport.width + 'px').not.toBeNull();
        expect(response.status(), archetype.route + ' must return a successful response').toBeLessThan(400);

        const state = await page.evaluate(() => {
          const main = document.querySelector('main');
          const h1 = main?.querySelector('h1') || null;
          const shell = main?.querySelector('.site-shell') || document.querySelector('.site-shell');
          const cssLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .map(link => new URL(link.href, document.baseURI).pathname)
            .filter(path => path.endsWith('.css'));
          const rootStyles = window.getComputedStyle(document.documentElement);
          const baseStylesheetIndex = cssLinks.findIndex(path => path.endsWith('/css/garh-v2.css'));
          const tokenStylesheetIndex = cssLinks.findIndex(path => path.endsWith('/css/tokens.css'));
          const conversionStylesheetIndex = cssLinks.findIndex(path => path.endsWith('/css/conversion-system.css'));
          const routeStylesheets = cssLinks.filter(path => ![
            '/css/garh-v2.css', '/css/tokens.css', '/css/conversion-system.css'
          ].includes(path));
          const h1Styles = h1 ? window.getComputedStyle(h1) : null;
          const shellRect = shell?.getBoundingClientRect() || null;
          return {
            hasMain: Boolean(main),
            h1Count: main?.querySelectorAll('h1').length || 0,
            h1Visible: Boolean(h1 && h1.getBoundingClientRect().width > 0 && h1.getBoundingClientRect().height > 0),
            h1FontSize: h1Styles ? parseFloat(h1Styles.fontSize) : 0,
            h1LineHeight: h1Styles ? parseFloat(h1Styles.lineHeight) : 0,
            h1FontFamily: h1Styles?.fontFamily || '',
            hasShell: Boolean(shell),
            shellLeft: shellRect?.left ?? -1,
            shellRight: shellRect?.right ?? -1,
            tokensAfterRouteCSS: baseStylesheetIndex >= 0
              && tokenStylesheetIndex > baseStylesheetIndex
              && routeStylesheets.every(path => tokenStylesheetIndex > cssLinks.indexOf(path)),
            conversionSystemAfterTokens: tokenStylesheetIndex >= 0 && conversionStylesheetIndex > tokenStylesheetIndex,
            tokensPresent: Boolean(rootStyles.getPropertyValue('--page-gutter').trim())
              && Boolean(rootStyles.getPropertyValue('--section-space').trim())
              && Boolean(rootStyles.getPropertyValue('--fs-hero').trim()),
            horizontalOverflow: document.documentElement.scrollWidth > window.innerWidth,
          };
        });

        expect(state.hasMain, archetype.route + ' must have a main landmark').toBe(true);
        expect(state.h1Count, archetype.route + ' must have one page H1').toBe(1);
        expect(state.h1Visible, archetype.route + ' H1 must remain visible').toBe(true);
        expect(state.h1FontSize, archetype.route + ' uses the shared responsive title scale').toBeGreaterThanOrEqual(33);
        expect(state.h1FontSize, archetype.route + ' uses the shared responsive title scale').toBeLessThanOrEqual(61);
        expect(state.h1LineHeight, archetype.route + ' title line-height stays compact and readable').toBeGreaterThanOrEqual(32);
        expect(state.h1FontFamily, archetype.route + ' keeps the approved display typeface in its CSS stack').toContain('Source Serif 4');
        expect(state.hasShell, archetype.route + ' uses the shared content rail').toBe(true);
        expect(state.shellLeft, archetype.route + ' content rail must remain inside the viewport').toBeGreaterThanOrEqual(-0.5);
        expect(state.shellRight, archetype.route + ' content rail must remain inside the viewport').toBeLessThanOrEqual(viewport.width + 0.5);
        expect(state.tokensAfterRouteCSS, archetype.route + ' loads shared tokens after route-specific CSS').toBe(true);
        expect(state.conversionSystemAfterTokens, archetype.route + ' loads conversion components after shared tokens').toBe(true);
        expect(state.tokensPresent, archetype.route + ' receives the shared scale tokens').toBe(true);
        expect(state.horizontalOverflow, archetype.route + ' must not introduce horizontal page overflow').toBe(false);

        if (archetype.route === '/catalogue.php') {
          const preloadState = await page.evaluate(() => {
            const heroImage = document.querySelector('main .catalogue-hero-media img');
            const selectedPath = heroImage ? new URL(heroImage.currentSrc || heroImage.src, document.baseURI).pathname : null;
            const activePreloads = Array.from(document.querySelectorAll('link[rel="preload"][as="image"]'))
              .filter(link => !link.media || window.matchMedia(link.media).matches);
            const matchesCandidate = activePreloads.some(link => {
              const candidates = link.getAttribute('imagesrcset');
              if (!candidates) return new URL(link.href, document.baseURI).pathname === selectedPath;
              return candidates.split(',').some(item => {
                try { return new URL(item.trim().split(/\s+/)[0], document.baseURI).pathname === selectedPath; }
                catch { return false; }
              });
            });
            return { selectedPath, matchesCandidate, preloadCount: activePreloads.length };
          });
          expect(preloadState.selectedPath, '/catalogue.php must expose its rendered hero image').not.toBeNull();
          expect(preloadState.matchesCandidate, '/catalogue.php must preload the selected hero asset or candidate set at ' + viewport.width + 'px').toBe(true);
        }

        if (PHOTO_HERO_ROUTES.has(archetype.route)) {
          const heroState = await page.locator(archetype.hero).first().evaluate(element => {
            const style = window.getComputedStyle(element);
            const rect = element.getBoundingClientRect();
            return { minHeight: parseFloat(style.minHeight), height: rect.height };
          });
          expect(heroState.minHeight, archetype.route + ' photo hero minimum height is bounded').toBeLessThanOrEqual(640.5);
          expect(heroState.height, archetype.route + ' must not grow into an oversized image slab').toBeLessThanOrEqual(720);
        }

        if (archetype.route === '/room.php' && viewport.width <= 380) {
          const factsBounds = await page.locator('main .rooms-hero-summary').boundingBox();
          const dockBounds = await page.locator('.mobile-cta').boundingBox();
          expect(factsBounds, 'Rooms quick facts must be rendered at ' + viewport.width + 'px').not.toBeNull();
          expect(dockBounds, 'The mobile booking dock must be rendered at ' + viewport.width + 'px').not.toBeNull();
          expect(
            factsBounds.y + factsBounds.height,
            'At ' + viewport.width + 'px, all four Rooms facts must clear the fixed mobile booking dock'
          ).toBeLessThanOrEqual(dockBounds.y + 1);
          await expect(page.locator('main .rooms-hero-note')).toBeHidden();
        }

        if (archetype.route === '/room.php' && viewport.width >= 381 && viewport.width <= 760) {
          const actions = await page.locator('main .rooms-hero-actions').evaluate(container => {
            const containerRect = container.getBoundingClientRect();
            const buttons = Array.from(container.querySelectorAll('a.button'));
            const rects = buttons.map(button => {
              const rect = button.getBoundingClientRect();
              return { top: rect.top, left: rect.left, right: rect.right, height: rect.height };
            });
            return {
              count: rects.length,
              rowDelta: rects.length === 2 ? Math.abs(rects[0].top - rects[1].top) : Number.POSITIVE_INFINITY,
              minHeight: rects.length ? Math.min(...rects.map(rect => rect.height)) : 0,
              withinContainer: rects.every(rect => rect.left >= containerRect.left - 1 && rect.right <= containerRect.right + 1),
            };
          });
          expect(actions.count, 'Rooms must retain both hero actions at ' + viewport.width + 'px').toBe(2);
          expect(actions.rowDelta, 'Rooms hero actions should share one row at ' + viewport.width + 'px').toBeLessThanOrEqual(1);
          expect(actions.minHeight, 'Rooms hero actions must retain 44px targets').toBeGreaterThanOrEqual(44);
          expect(actions.withinContainer, 'Rooms hero actions must not escape their content rail').toBe(true);
        }

        if (archetype.media) {
          const media = page.locator(archetype.media).first();
          if (await media.count()) {
            const ratio = await media.evaluate(element => {
              const rect = element.getBoundingClientRect();
              return rect.height > 0 ? rect.width / rect.height : 0;
            });
            expect(ratio, archetype.route + ' repeated card media should use a landscape frame').toBeGreaterThanOrEqual(1.4);
            expect(ratio, archetype.route + ' repeated card media should use a landscape frame').toBeLessThanOrEqual(1.8);
          }
        }

        if (VISUAL_REVIEW_ROUTES.has(archetype.route) && VISUAL_REVIEW_WIDTHS.has(viewport.width)) {
          const hero = page.locator(archetype.hero).first();
          const heroImage = hero.locator('img').first();
          if (await heroImage.count()) {
            await heroImage.evaluate(image => image.decode().catch(() => undefined));
          }
          fs.mkdirSync(VISUAL_REVIEW_DIR, { recursive: true });
          const routeName = archetype.route === '/' ? 'home' : archetype.route.slice(1).replace(/\.php$/, '');
          await page.screenshot({
            path: path.join(VISUAL_REVIEW_DIR, routeName + '-' + viewport.width + '.png'),
            fullPage: false,
            animations: 'disabled',
          });
        }
      }
    } finally {
      await context.close();
    }
  }
});
