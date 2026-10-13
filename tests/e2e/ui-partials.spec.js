const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';

const CTA_CASES = [
  {
    route: '/',
    section: '.final-cta',
    heading: 'Stay at Garh Jaisal Haveli.',
    links: [
      { label: 'Book your stay', opensNewTab: true },
      { label: 'Contact us', href: 'contact.php' },
    ],
  },
  {
    route: '/room.php',
    section: '.rooms-cta',
    heading: 'Choose your room.',
    links: [{ label: 'Book your stay', opensNewTab: true }],
  },
  {
    route: '/direction.php',
    section: '.final-cta',
    heading: 'Stay at Garh Jaisal Haveli.',
    links: [
      { label: 'Book your stay', opensNewTab: true },
      { label: 'Contact us', href: 'contact.php?topic=arrival' },
    ],
  },
  {
    route: '/gallery.php',
    section: '.gallery-cta',
    heading: 'See a room you like?',
    links: [
      { label: 'View rooms', href: 'room.php' },
      { label: 'Book your stay', opensNewTab: true },
    ],
  },
  {
    route: '/about.php',
    section: '.about-cta',
    heading: 'Come for the fort, stay for the house.',
    links: [
      { label: 'Book your stay', opensNewTab: true },
      { label: 'Contact the hotel', href: 'contact.php' },
    ],
  },
  {
    route: '/blog.php',
    section: '.article-footer-cta',
    heading: 'Stay inside Jaisalmer Fort.',
    links: [
      { label: 'View rooms', href: 'room.php' },
      { label: 'Explore experiences', href: 'pack.php' },
    ],
  },
];

test('shared CTA partials preserve existing copy, classes and destinations', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'CTA assertions run once on desktop-1280');

  const context = await browser.newContext({
    baseURL: BASE_URL,
    viewport: { width: 1280, height: 800 },
  });
  await context.route('https://www.googletagmanager.com/**', route => route.abort());
  const page = await context.newPage();

  try {
    for (const item of CTA_CASES) {
      const response = await page.goto(item.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, 'No response for ' + item.route).not.toBeNull();
      expect(response.status(), 'Unexpected status for ' + item.route).toBeLessThan(400);

      const section = page.locator(item.section);
      await expect(section, item.route + ': CTA section').toHaveCount(1);
      await expect(section.locator('h2')).toHaveText(item.heading);

      for (const expectedLink of item.links) {
        const link = section.getByRole('link', { name: expectedLink.label, exact: true });
        await expect(link, item.route + ': ' + expectedLink.label).toHaveCount(1);
        if (expectedLink.href) {
          await expect(link).toHaveAttribute('href', expectedLink.href);
        } else {
          await expect(link).toHaveAttribute('href', /\S+/);
        }
        if (expectedLink.opensNewTab) {
          await expect(link).toHaveAttribute('target', '_blank');
          await expect(link).toHaveAttribute('rel', /noopener/);
        }
      }
    }
  } finally {
    await context.close();
  }
});

test('shared FAQ partial preserves arrival links and experience FAQ structure', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'FAQ assertions run once on desktop-1280');

  const context = await browser.newContext({
    baseURL: BASE_URL,
    viewport: { width: 1280, height: 800 },
  });
  await context.route('https://www.googletagmanager.com/**', route => route.abort());
  const page = await context.newPage();

  try {
    let response = await page.goto('/direction.php', { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, 'No response for /direction.php').not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator('.arrival-faq .faq-list details')).toHaveCount(9);
    await expect(page.locator('.arrival-faq .faq-list')).toContainText('Can my car enter the fort?');
    await expect(page.locator('.arrival-faq .faq-list a[href="roof.php"]')).toHaveText('rooftop dining page');
    await expect(page.locator('.arrival-faq a[href="contact.php?topic=arrival"]')).toHaveCount(1);

    response = await page.goto('/sunset-camel-safari.php', { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, 'No response for /sunset-camel-safari.php').not.toBeNull();
    expect(response.status()).toBeLessThan(400);
    await expect(page.locator('.experience-faq .section-head h2')).toHaveText('Questions about this experience.');
    await expect(page.locator('.experience-faq .faq-list details.faq-item')).toHaveCount(3);
    await expect(page.locator('.experience-faq .faq-answer p')).toHaveCount(3);
  } finally {
    await context.close();
  }
});
