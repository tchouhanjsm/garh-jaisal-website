const { test, expect } = require('@playwright/test');

const heroScenarios = [
  { route: '/pack.php', selector: '.package-hero-content > h1', label: 'Experiences hub' },
  { route: '/car-tours.php', selector: '.package-hero-content > h1', label: 'Car & City Tours' },
  { route: '/sunset-camel-safari.php', selector: '.experience-hero-content > h1', label: 'Experience detail' },
  { route: '/city-tour.php', selector: '.tour-detail-hero .page-hero-inner > h1', label: 'Fort walk detail' },
];
const viewports = [
  { width: 320, height: 740 },
  { width: 390, height: 844 },
  { width: 768, height: 900 },
  { width: 1280, height: 800 },
  { width: 1440, height: 900 },
];

test('tour and experience hero titles use the editorial display treatment without overflow', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial hero typography runs once with explicit responsive viewports.');

  for (const scenario of heroScenarios) {
    for (const viewport of viewports) {
      await page.setViewportSize(viewport);
      const response = await page.goto(scenario.route, { waitUntil: 'domcontentloaded' });
      expect(response, scenario.label + ': response at ' + viewport.width + 'px').not.toBeNull();
      expect(response.status(), scenario.label + ': public route at ' + viewport.width + 'px').toBeLessThan(400);

      const heading = page.locator(scenario.selector);
      await expect(heading, scenario.label + ': primary hero heading').toBeVisible();
      const treatment = await heading.evaluate(element => {
        const style = window.getComputedStyle(element);
        const accent = window.getComputedStyle(element, '::after');
        return {
          fontFamily: style.fontFamily,
          fontWeight: style.fontWeight,
          lineHeight: style.lineHeight,
          accentContent: accent.content,
          accentWidth: parseFloat(accent.width),
          accentHeight: parseFloat(accent.height),
          accentBackground: accent.backgroundImage,
          accentDisplay: accent.display,
        };
      });

      expect(treatment.fontFamily, scenario.label + ': approved display typeface').toContain('Source Serif 4');
      expect(treatment.fontWeight, scenario.label + ': stronger editorial headline weight').toBe('600');
      expect(parseFloat(treatment.lineHeight), scenario.label + ': compact readable headline line-height').toBeGreaterThan(0);
      expect(treatment.accentContent, scenario.label + ': decorative heading rule exists').not.toBe('none');
      expect(treatment.accentDisplay, scenario.label + ': rule occupies its own line').toBe('block');
      expect(treatment.accentWidth, scenario.label + ': rule remains visibly sized').toBeGreaterThanOrEqual(48);
      expect(treatment.accentHeight, scenario.label + ': rule has visible weight').toBeGreaterThanOrEqual(2);
      expect(treatment.accentBackground, scenario.label + ': rule uses the brand gold gradient').toContain('linear-gradient');

      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        document: document.documentElement.scrollWidth,
      }));
      expect(
        dimensions.document,
        scenario.label + ': no horizontal overflow at ' + viewport.width + 'px'
      ).toBeLessThanOrEqual(dimensions.viewport + 1);
    }
  }
});
