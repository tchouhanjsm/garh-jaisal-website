const { test, expect } = require('@playwright/test');

const viewportWidths = [
  { width: 320, height: 700, mobile: true },
  { width: 390, height: 844, mobile: true },
  { width: 768, height: 900, mobile: false },
  { width: 1280, height: 800, mobile: false },
  { width: 1440, height: 900, mobile: false },
];

const pages = [
  {
    route: '/',
    selector: '.home-quick-facts',
    facts: [
      ['Location', 'Inside Jaisalmer Fort'],
      ['Stay', '7 heritage rooms'],
      ['Dining', 'Rooftop restaurant'],
      ['Experiences', 'Desert and guided city tours'],
    ],
  },
  {
    route: '/room.php',
    selector: '.rooms-hero-summary',
    facts: [
      ['Room selection', '7 individually styled rooms'],
      ['Floor layout', 'Ground floor and first floor'],
      ['Room facilities', 'Vary by room'],
      ['Rates and availability', 'Check current rates for your dates'],
    ],
  },
  {
    route: '/catalogue.php',
    selector: '.catalogue-hero-facts',
    facts: [
      ['Trip options', 'Rooms, tours, transport and rooftop dining'],
      ['Shortlist', 'Saved in this tab'],
      ['Enquiry', 'Review the WhatsApp draft'],
      ['Booking status', 'Enquiry only; not confirmed'],
    ],
  },
];

test('verified quick facts remain accurate, semantic and responsive on high-intent pages', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'The facts contract creates its own responsive contexts');
  test.setTimeout(180_000);

  for (const viewport of viewportWidths) {
    const context = await browser.newContext({
      baseURL: 'http://127.0.0.1:8080',
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
      isMobile: viewport.mobile,
      hasTouch: viewport.mobile,
    });
    const page = await context.newPage();

    try {
      for (const route of pages) {
        const response = await page.goto(route.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, route.route + ' must respond at ' + viewport.width + 'px').not.toBeNull();
        expect(response.status(), route.route + ' must return success at ' + viewport.width + 'px').toBeLessThan(400);

        const region = page.locator(route.selector);
        await expect(region, route.route + ' quick facts must be rendered').toBeVisible();
        const facts = region.locator('dl.at-a-glance');
        await expect(facts).toBeVisible();
        await expect(facts.locator('dt')).toHaveCount(4);
        await expect(facts.locator('dd')).toHaveCount(4);

        const renderedFacts = await facts.evaluate(node => ({
          labels: Array.from(node.querySelectorAll('dt')).map(item => item.textContent.trim()),
          values: Array.from(node.querySelectorAll('dd')).map(item => item.textContent.trim()),
        }));
        expect(renderedFacts.labels, route.route + ' fact labels at ' + viewport.width + 'px')
          .toEqual(route.facts.map(fact => fact[0]));
        expect(renderedFacts.values, route.route + ' fact values at ' + viewport.width + 'px')
          .toEqual(route.facts.map(fact => fact[1]));

        const dimensions = await page.evaluate(() => ({
          viewport: document.documentElement.clientWidth,
          document: document.documentElement.scrollWidth,
        }));
        expect(dimensions.document, route.route + ' must not overflow at ' + viewport.width + 'px')
          .toBeLessThanOrEqual(dimensions.viewport + 1);
      }
    } finally {
      await context.close();
    }
  }
});
