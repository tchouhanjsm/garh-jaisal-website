const { test, expect } = require('@playwright/test');

const FAQ_ROUTES = [
  {
    "route": "/room.php",
    "section": ".room-faq",
    "heading": "Questions before you choose a room.",
    "count": 5
  },
  {
    "route": "/roof.php",
    "section": ".roof-faq",
    "heading": "Rooftop dining questions.",
    "count": 4
  },
  {
    "route": "/restaurant.php",
    "section": ".restaurant-faq",
    "heading": "Before you request a table.",
    "count": 4
  },
  {
    "route": "/pack.php",
    "section": ".packages-faq",
    "heading": "Questions about Jaisalmer experiences.",
    "count": 3
  },
  {
    "route": "/desert-safari.php",
    "section": ".desert-faq",
    "heading": "Questions about choosing a desert safari.",
    "count": 4
  },
  {
    "route": "/car-tours.php",
    "section": ".car-tours-faq",
    "heading": "Questions about city tours and car arrangements.",
    "count": 3
  },
  {
    "route": "/guided-jaisalmer-tour.php",
    "section": ".guided-tours-faq",
    "heading": "Before choosing a guided tour.",
    "count": 3
  },
  {
    "route": "/city-tour.php",
    "section": ".fort-walk-faq",
    "heading": "Questions about the Fort walk.",
    "count": 4
  },
  {
    "route": "/car-rental.php",
    "section": ".car-rental-faq",
    "heading": "Before arranging a car.",
    "count": 4
  },
  {
    "route": "/camping.php",
    "section": ".camping-faq",
    "heading": "Questions about desert camping.",
    "count": 3
  }
];

async function readFaqSchema(page) {
  const blocks = await page.locator('script[type="application/ld+json"]').allTextContents();
  const nodes = [];
  for (const block of blocks) {
    const parsed = JSON.parse(block);
    if (Array.isArray(parsed['@graph'])) nodes.push(...parsed['@graph']);
    else nodes.push(parsed);
  }
  return nodes.find(node => node['@type'] === 'FAQPage');
}

test('pre-booking FAQs render matching FAQPage structured data', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'FAQ/schema content runs once on desktop-1280');
  test.setTimeout(120_000);
  for (const item of FAQ_ROUTES) {
    const response = await page.goto(item.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, `No HTTP response for ${item.route}`).not.toBeNull();
    expect(response.status(), `Unexpected status for ${item.route}`).toBeLessThan(400);
    const section = page.locator(item.section);
    await expect(section, `${item.route}: FAQ section should be visible`).toBeVisible();
    await expect(section.getByRole('heading', { level: 2, name: item.heading })).toBeVisible();
    const details = section.locator('.faq-list details.faq-item');
    await expect(details).toHaveCount(item.count);
    const visibleItems = await details.evaluateAll(elements => elements.map(element => ({
      question: element.querySelector('summary')?.textContent?.trim() || '',
      answer: element.querySelector('.faq-answer p')?.textContent?.trim() || '',
    })));
    const faqSchema = await readFaqSchema(page);
    expect(faqSchema, `${item.route}: FAQPage JSON-LD node`).toBeTruthy();
    expect(faqSchema.url.endsWith(item.route)).toBe(true);
    expect(faqSchema.mainEntity).toHaveLength(visibleItems.length);
    for (let index = 0; index < visibleItems.length; index += 1) {
      expect(faqSchema.mainEntity[index]['@type']).toBe('Question');
      expect(faqSchema.mainEntity[index].name).toBe(visibleItems[index].question);
      expect(faqSchema.mainEntity[index].acceptedAnswer['@type']).toBe('Answer');
      expect(faqSchema.mainEntity[index].acceptedAnswer.text).toBe(visibleItems[index].answer);
    }
    const firstSummary = details.first().locator('summary');
    await firstSummary.focus();
    await expect(firstSummary).toBeFocused();
    await firstSummary.press('Enter');
    await expect(details.first()).toHaveAttribute('open', '');
  }
});

test('FAQ accordions fit narrow screens without horizontal overflow', async ({ page }) => {
  for (const route of ['/room.php', '/roof.php', '/car-tours.php']) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response).not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    const section = page.locator('.site-faq');
    await expect(section).toBeVisible();
    const metrics = await page.evaluate(() => ({
      viewport: window.innerWidth,
      document: document.documentElement.scrollWidth,
      section: document.querySelector('.site-faq').scrollWidth,
      client: document.querySelector('.site-faq').clientWidth,
    }));
    expect(metrics.document, `${route}: no page-level horizontal overflow at ${metrics.viewport}px`).toBeLessThanOrEqual(metrics.viewport + 1);
    expect(metrics.section, `${route}: FAQ content stays within its container`).toBeLessThanOrEqual(metrics.client + 1);
    const summaryBox = await section.locator('details summary').first().boundingBox();
    expect(summaryBox?.height ?? 0, `${route}: FAQ summary touch target`).toBeGreaterThanOrEqual(44);
  }
});
