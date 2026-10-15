const { test, expect } = require('@playwright/test');

const guideCases = [
  { route: '/best-time-to-visit-jaisalmer.php', topic: 'visit-planning', label: 'Jaisalmer visit planning', messageMatch: /seasonal considerations/i, whatsapp: 'planning when to visit jaisalmer' },
  { route: '/how-to-reach-jaisalmer.php', topic: 'arrival', label: 'Arrival arrangements', messageMatch: /check-in date/i, whatsapp: 'how to reach jaisalmer and enter the fort', context: 'arrival' },
  { route: '/jaisalmer-desert-safari-packing-list.php', topic: 'desert-packing', label: 'Desert safari preparation', messageMatch: /packing considerations/i, whatsapp: 'packing for a jaisalmer desert safari' },
  { route: '/jaisalmer-fort-guide.php', topic: 'fort-guide', label: 'Jaisalmer Fort visit', messageMatch: /guide availability/i, whatsapp: 'planning a visit to jaisalmer fort' },
  { route: '/desert-safari-guide.php', topic: 'desert-safari', label: 'Jaisalmer desert safari', messageMatch: /Preferred style: sunset return or overnight/i, whatsapp: 'choosing a jaisalmer desert safari' },
  { route: '/jaisalmer-2-day-itinerary.php', topic: 'two-day-itinerary', label: 'Two-day Jaisalmer itinerary', messageMatch: /two-day visit/i, whatsapp: 'a two-day jaisalmer itinerary' },
  { route: '/jaisalmer-3-day-itinerary.php', topic: 'three-day-itinerary', label: 'Three-day Jaisalmer itinerary', messageMatch: /three-day visit/i, whatsapp: 'a three-day jaisalmer itinerary' },
  { route: '/jaisalmer-honeymoon-guide.php', topic: 'honeymoon', label: 'Couples and honeymoon planning', messageMatch: /this enquiry does not reserve anything/i, whatsapp: 'a couples trip to jaisalmer' },
];

const whatsappCases = [
  ...guideCases.map(({ route, whatsapp }) => ({ route, whatsapp })),
  { route: '/book-rooms-in-jaisalmer.php', whatsapp: 'a stay inside jaisalmer fort' },
  { route: '/sunset-camel-safari.php', whatsapp: 'the sunset camel safari' },
  { route: '/cultural-safari.php', whatsapp: 'the cultural desert safari' },
  { route: '/sleep-under-the-stars.php', whatsapp: 'the sleep under the stars desert stay' },
  { route: '/swiss-tent-safari.php', whatsapp: 'the swiss tent safari' },
  { route: '/guided-jaisalmer-tour.php', whatsapp: 'a guided jaisalmer tour' },
  { route: '/city-tour.php', whatsapp: 'the guided fort walk' },
  { route: '/half-day-jaisalmer-tour.php', whatsapp: 'a half-day jaisalmer tour' },
  { route: '/full-day-jaisalmer-tour.php', whatsapp: 'a full-day jaisalmer tour' },
];

test('editorial guides pass reader intent into the contact form without auto-confirmation', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial enquiry journey runs once on desktop-1280');
  await page.route('https://www.googletagmanager.com/**', route => route.abort());

  for (const entry of guideCases) {
    await page.goto(entry.route, { waitUntil: 'domcontentloaded' });
    const whatsappLink = page.locator('.nav-mega-foot a[href*="wa.me"]').first();
    const whatsappUrl = new URL(await whatsappLink.getAttribute('href'));
    expect((whatsappUrl.searchParams.get('text') || '').toLowerCase()).toContain(entry.whatsapp);

    const contactLink = page.locator('.article-footer-cta a.button-secondary');
    await expect(contactLink).toHaveAttribute('href', 'contact.php?topic=' + entry.topic);
    await contactLink.click();
    await expect(page.locator('.contact-topic-context')).toContainText(entry.label);
    await expect(page.locator('#message')).toHaveValue(entry.messageMatch);
    await page.locator('#name').focus();
    const events = await page.evaluate(() => Array.from(window.dataLayer || [])
      .map(entry => Array.from(entry || []))
      .filter(entry => entry[0] === 'event' && entry[1] === 'enquiry_start'));
    expect(events).toHaveLength(1);
    expect(events[0][2].enquiry_context).toBe(entry.context || 'travel_planning');
    expect(events[0][2]).not.toHaveProperty('name');
    expect(events[0][2]).not.toHaveProperty('email');
    expect(events[0][2]).not.toHaveProperty('message');
  }
});

test('WhatsApp prefill names the page or experience being enquired about', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'WhatsApp route context runs once on desktop-1280');
  for (const entry of whatsappCases) {
    await page.goto(entry.route, { waitUntil: 'domcontentloaded' });
    const link = page.locator('.nav-mega-foot a[href*="wa.me"]').first();
    await expect(link).toHaveAttribute('target', '_blank');
    await expect(link).toHaveAttribute('rel', /noopener/);
    const address = new URL(await link.getAttribute('href'));
    expect((address.searchParams.get('text') || '').toLowerCase(), entry.route).toContain(entry.whatsapp);
  }
});
