const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');
const { REPOSITORY_ROOT } = require('./helpers/sitemap-routes');

const BASE_URL = 'http://127.0.0.1:8080';
const OUTPUT_DIR = path.join(REPOSITORY_ROOT, 'artifacts/screenshots/home-hero-review');
const VIEWPORTS = [
  { width: 320, height: 700, expectedStem: 'facade', maxBytes: 200 * 1024 },
  { width: 390, height: 844, expectedStem: 'facade', maxBytes: 200 * 1024 },
  { width: 768, height: 900, expectedStem: 'fort-from-roof', maxBytes: 300 * 1024 },
  { width: 1280, height: 800, expectedStem: 'fort-from-roof', maxBytes: 400 * 1024 },
  { width: 1440, height: 900, expectedStem: 'fort-from-roof', maxBytes: 400 * 1024 },
];

test('homepage hero art direction selects responsive images within asset budgets', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The home hero review creates its own five viewport contexts');
  test.setTimeout(120_000);
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });

  for (const viewport of VIEWPORTS) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
      isMobile: viewport.width <= 390,
      hasTouch: viewport.width <= 390,
    });
    const page = await context.newPage();

    try {
      const response = await page.goto('/', { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, 'Homepage must respond at ' + viewport.width + 'px').not.toBeNull();
      expect(response.status(), 'Homepage must return a successful status at ' + viewport.width + 'px').toBeLessThan(400);

      const hero = page.locator('.hero-media picture img');
      await expect(hero).toBeVisible();
      await hero.evaluate(img => img.decode());

      const state = await hero.evaluate(img => {
        const current = new URL(img.currentSrc || img.src, document.baseURI);
        const pictureSources = Array.from(img.closest('picture')?.querySelectorAll('source[srcset]') || []);
        const activeSource = pictureSources.find(source => source.srcset.split(',').some(candidate => {
          try {
            return new URL(candidate.trim().split(/\s+/)[0], document.baseURI).pathname === current.pathname;
          } catch {
            return false;
          }
        }));
        const dimensionNode = activeSource
          && Number(activeSource.getAttribute('width')) > 0
          && Number(activeSource.getAttribute('height')) > 0
          ? activeSource
          : img;
        return {
          currentPath: current.pathname.slice(1),
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
          declaredWidth: Number(dimensionNode.getAttribute('width')),
          declaredHeight: Number(dimensionNode.getAttribute('height')),
          selectedSizes: dimensionNode.getAttribute('sizes'),
          selectedSrcset: dimensionNode.getAttribute('srcset') || '',
          alt: img.getAttribute('alt'),
          ariaHidden: img.getAttribute('aria-hidden'),
          fetchPriority: img.getAttribute('fetchpriority'),
          loaded: img.complete && img.naturalWidth > 0 && img.naturalHeight > 0,
        };
      });

      const imagePath = path.join(REPOSITORY_ROOT, state.currentPath);
      const responsiveMatch = state.currentPath.match(/^img\/responsive\/v2\/([^/]+)-(480|800|1200|1280|1366|1440|1800)\.webp$/);
      expect(state.currentPath, 'The selected hero should use the intended photo family at ' + viewport.width + 'px').toContain('img/responsive/');
      expect(responsiveMatch, 'A selected responsive hero must identify a supported width candidate').not.toBeNull();
      expect(responsiveMatch[1], 'The selected hero should follow art direction at ' + viewport.width + 'px').toBe(viewport.expectedStem);
      expect(fs.existsSync(imagePath), state.currentPath + ' must be generated before this test runs').toBe(true);
      const fileBytes = fs.statSync(imagePath).size;
      expect(state.loaded, 'The selected hero image must decode at ' + viewport.width + 'px').toBe(true);
      expect(state.naturalWidth, 'Selected responsive candidate must have positive intrinsic width').toBeGreaterThan(0);
      expect(state.naturalHeight, 'Selected responsive candidate must have positive intrinsic height').toBeGreaterThan(0);
      expect(state.declaredWidth, 'Responsive picture source must declare its width').toBeGreaterThan(0);
      expect(state.declaredHeight, 'Responsive picture source must declare its height').toBeGreaterThan(0);
      expect(Math.abs((state.declaredWidth / state.declaredHeight) - (state.naturalWidth / state.naturalHeight))).toBeLessThan(0.02);
      expect(state.selectedSizes).toBe('100vw');
      expect(state.selectedSrcset.split(',').some(item => item.trim().split(/\s+/)[0].replace(/^\//, '') === state.currentPath)).toBe(true);
      expect(state.fetchPriority).toBe('high');
      expect(state.alt).toBe('');
      expect(state.ariaHidden).toBe('true');
      expect(fileBytes, viewport.expected + ' must remain under its tracked-byte budget').toBeLessThanOrEqual(viewport.maxBytes);
      await expect(page.getByRole('heading', { level: 1, name: 'Wake up inside Jaisalmer Fort.', exact: true })).toBeVisible();
      await expect(page.locator('.hero-content > .eyebrow')).toContainText('Heritage stay');
      await expect(page.locator('.hero-content > p:not(.eyebrow)')).toContainText('individually styled rooms');
      const primaryCta = page.locator('.hero-content a.button-primary');
      await expect(primaryCta).toHaveText('Check rooms & rates');
      await expect(primaryCta).toHaveAttribute('href', /asiatech\.in\/booking_engine\/index3\?token=/);
      const secondaryCta = page.locator('.hero-content a.button-light');
      await expect(secondaryCta).toHaveText('Explore the rooms');
      await expect(secondaryCta).toHaveAttribute('href', 'room.php');

      const facts = page.locator('.home-hero-facts');
      await expect(facts).toBeVisible();
      await expect(facts).toContainText('heritage rooms');
      await expect(facts).toContainText('Rooftop dining');
      await expect(facts).toContainText('City & desert tours');

      const firstScreenState = await page.evaluate(() => {
        const actions = document.querySelector('.hero-content .actions');
        const facts = document.querySelector('.home-hero-facts');
        const dock = document.querySelector('.mobile-cta');
        if (!actions || !facts) return { actionsClear: false, factsClear: false, factsVisible: false };
        const limit = dock && getComputedStyle(dock).display !== 'none'
          ? dock.getBoundingClientRect().top
          : window.innerHeight;
        const actionBounds = actions.getBoundingClientRect();
        const factsBounds = facts.getBoundingClientRect();
        return {
          actionsClear: actionBounds.bottom <= limit + 1,
          factsClear: factsBounds.bottom <= limit + 1,
          factsVisible: factsBounds.top < window.innerHeight && factsBounds.bottom > 0,
          factsBottom: Math.round(factsBounds.bottom),
          dockTop: Math.round(limit),
          heroHeight: Math.round(document.querySelector('.hero')?.getBoundingClientRect().height || 0),
        };
      });
      expect(firstScreenState.actionsClear, 'The first booking decision must remain above any fixed mobile CTA dock at ' + viewport.width + 'px').toBe(true);
      expect(
        firstScreenState.factsClear,
        'Hero proof points must clear the fixed mobile CTA dock at ' + viewport.width +
          'px (facts bottom ' + firstScreenState.factsBottom + 'px, dock top ' + firstScreenState.dockTop +
          'px, hero height ' + firstScreenState.heroHeight + 'px)'
      ).toBe(true);
      expect(firstScreenState.factsVisible, 'The key stay/dine/explore facts must be visible on the initial screen at ' + viewport.width + 'px').toBe(true);

      const horizontalOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
      expect(horizontalOverflow, 'Homepage must not scroll horizontally at ' + viewport.width + 'px').toBe(false);

      const screenshotPath = path.join(OUTPUT_DIR, 'home-hero-' + viewport.width + '.jpg');
      await page.screenshot({ path: screenshotPath, type: 'jpeg', quality: 82, fullPage: false, animations: 'disabled' });
      await testInfo.attach('homepage-hero-' + viewport.width + 'px', {
        path: screenshotPath,
        contentType: 'image/jpeg',
      });
    } finally {
      await context.close();
    }
  }
});
