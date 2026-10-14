const { test, expect } = require('@playwright/test');

const REVIEW_PAGES = [
  { route: '/room.php', faq: '.room-faq', heading: 'Questions before you choose a room.' },
  { route: '/roof.php', faq: '.roof-faq', heading: 'Rooftop dining questions.' },
  { route: '/car-tours.php', faq: '.car-tours-faq', heading: 'Questions about city tours and car arrangements.' },
];

const TOUR_IDEAS = [
  {
    "topic": "heritage-havelis",
    "label": "Havelis and architecture itinerary idea",
    "phrase": "a Jaisalmer havelis and architecture route"
  },
  {
    "topic": "jain-temples",
    "label": "Jain temples and Fort architecture itinerary idea",
    "phrase": "a visit focused on Jaisalmer Fort Jain temples and architecture"
  },
  {
    "topic": "lake-cenotaph-photo",
    "label": "Gadisar Lake and cenotaph photography itinerary idea",
    "phrase": "a photography-friendly sightseeing route involving Gadisar Lake"
  },
  {
    "topic": "kuldhara-landscape",
    "label": "Kuldhara and desert landscape itinerary idea",
    "phrase": "a Kuldhara heritage and nearby desert landscape outing"
  },
  {
    "topic": "natural-history",
    "label": "Akal Wood Fossil Park or desert ecology itinerary idea",
    "phrase": "a natural-history outing to Akal Wood Fossil Park"
  },
  {
    "topic": "longewala-history",
    "label": "Longewala War Memorial itinerary idea",
    "phrase": "a day excursion to Longewala War Memorial"
  }
];

test('Rooms, Rooftop Dining and Car & City preserve the booking/FAQ experience', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'CTA and content checks run once on desktop-1280');
  for (const item of REVIEW_PAGES) {
    const response = await page.goto(item.route, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for ${item.route}`).not.toBeNull();
    expect(response.status(), `Unexpected status for ${item.route}`).toBeLessThan(400);
    await expect(page.locator('h1').first()).toBeVisible();
    await expect(page.locator(item.faq)).toBeVisible();
    await expect(page.locator(item.faq).getByRole('heading', { level: 2, name: item.heading })).toBeVisible();
    await expect(page.locator(`${item.faq} details.faq-item`).first()).toBeVisible();
  }

  await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('.rooms-hero-actions a').filter({ hasText: 'Check availability' })).toBeVisible();

  await page.goto('/roof.php', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('.page-hero-inner .actions a').filter({ hasText: 'View the menu' })).toHaveAttribute('href', 'restaurant.php#menu');
  await expect(page.locator('.page-hero-inner .actions a').filter({ hasText: 'Contact the hotel' })).toHaveAttribute('href', 'contact.php?topic=dining');

  await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('main nav[aria-label="Breadcrumb"]')).toHaveCount(1);
  await expect(page.getByRole('heading', { level: 2, name: 'More ways to discover Jaisalmer.' })).toBeVisible();
  await expect(page.locator('.tour-ideas-disclaimer')).toContainText('not confirmed Garh Jaisal packages');
});

test('Car & City tour card media stays at 16:10 and the page has no horizontal overflow', async ({ page }) => {
  const response = await page.goto('/car-tours.php', { waitUntil: 'domcontentloaded' });
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(400);
  const media = page.locator('.experience-card-media');
  await expect(media).toHaveCount(3);
  const ratios = await media.evaluateAll(nodes => nodes.map(node => {
    const rect = node.getBoundingClientRect();
    return rect.width / rect.height;
  }));
  for (const ratio of ratios) {
    expect(Math.abs(ratio - 1.6), `expected 16:10 crop, received ratio ${ratio}`).toBeLessThan(0.04);
  }
  const dimensions = await page.evaluate(() => ({
    viewport: window.innerWidth,
    document: document.documentElement.scrollWidth,
  }));
  expect(dimensions.document, `no horizontal overflow at ${dimensions.viewport}px`).toBeLessThanOrEqual(dimensions.viewport + 1);
  await expect(page.locator('.tour-idea-card')).toHaveCount(TOUR_IDEAS.length);
  for (const idea of TOUR_IDEAS) {
    await expect(page.locator(`.tour-idea-card a[href="contact.php?topic=${idea.topic}"]`)).toBeVisible();
  }
});

test('each proposed tour idea opens a prefilled enquiry rather than a booking', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Contact prefill checks run once on desktop-1280');
  for (const idea of TOUR_IDEAS) {
    const response = await page.goto(`/contact.php?topic=${idea.topic}`, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for topic ${idea.topic}`).not.toBeNull();
    expect(response.status(), `Unexpected status for topic ${idea.topic}`).toBeLessThan(400);
    await expect(page.locator('.contact-topic-context strong')).toHaveText(idea.label);
    const message = await page.locator('#message').inputValue();
    expect(message).toContain(idea.phrase);
    await expect(page.locator('form.contact-form button[type="submit"]')).toHaveText('Send message');
  }
});
