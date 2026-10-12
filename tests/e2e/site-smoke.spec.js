const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

function getSitemapRoutes() {
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  const locations = [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)];
  const routes = locations.map(([, value]) => {
    const url = new URL(value.trim());
    return url.pathname || '/';
  });
  return [...new Set(routes)].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

const routes = getSitemapRoutes();
const screenshotRoutes = new Set(['/', '/room.php', '/contact.php', '/direction.php']);

test('sitemap contains at least one browser-test route', async () => {
  expect(routes.length).toBeGreaterThan(0);
});

for (const route of routes) {
  test(`renders ${route} without horizontal overflow`, async ({ page }, testInfo) => {
    const pageErrors = [];
    page.on('pageerror', error => pageErrors.push(error.message));

    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, `No HTTP response for ${route}`).not.toBeNull();
    expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

    await expect(page.locator('html')).toHaveAttribute('lang', /.+/);
    await expect(page).toHaveTitle(/\S/);

    // Every public route shares a keyboard skip link and one focusable main landmark.
    await expect(page.locator('.skip-link')).toHaveCount(1);
    await expect(page.locator('.skip-link')).toHaveAttribute('href', '#main-content');
    await expect(page.locator('main#main-content[tabindex="-1"]')).toHaveCount(1);
    await expect(page.getByRole('main')).toHaveCount(1);

    const footerBooking = page.locator('.footer-booking-button');
    await expect(footerBooking).toHaveCount(1);
    await expect(footerBooking).toHaveAttribute('href', await page.locator('.nav-book').getAttribute('href'));
    await expect(footerBooking).toHaveAttribute('target', '_blank');
    await expect(footerBooking).toHaveAttribute('rel', /noopener/);

    const dimensions = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      document: document.documentElement.scrollWidth,
    }));
    expect(dimensions.document, `Horizontal overflow on ${route}: ${JSON.stringify(dimensions)}`)
      .toBeLessThanOrEqual(dimensions.viewport + 1);

    // Wait for web fonts where available without making external network idle a requirement.
    await page.evaluate(() => Promise.race([
      document.fonts.ready,
      new Promise(resolve => setTimeout(resolve, 1500)),
    ]));

    if (screenshotRoutes.has(route)) {
      const routeName = route === '/' ? 'home' : route.replace(/^\//, '').replace(/[^a-zA-Z0-9._-]+/g, '-');
      const screenshotDir = path.join(process.cwd(), 'artifacts', 'screenshots', testInfo.project.name);
      fs.mkdirSync(screenshotDir, { recursive: true });
      const screenshotPath = path.join(screenshotDir, `${routeName}.jpg`);
  
      await page.screenshot({ path: screenshotPath, type: 'jpeg', quality: 72, fullPage: true, animations: 'disabled' });
      await testInfo.attach(`screenshot-${routeName}`, {
        path: screenshotPath,
        contentType: 'image/jpeg',
      });
    }


    expect(pageErrors, `Uncaught browser errors on ${route}`).toEqual([]);
  });
}


test('mobile navigation opens and closes with Escape', async ({ page }, testInfo) => {
  test.skip(!testInfo.project.name.startsWith('mobile-'), 'Mobile navigation is only shown at mobile/tablet widths');

  await page.goto('/');
  const toggle = page.getByRole('button', { name: 'Open navigation' });
  const navigation = page.getByRole('navigation', { name: 'Primary navigation' });

  await toggle.click();
  const menuButton = page.locator('.nav-toggle');
  await expect(menuButton).toHaveAttribute('aria-expanded', 'true');
  await expect(navigation).toBeVisible();

  await page.keyboard.press('Escape');
  await expect(menuButton).toHaveAttribute('aria-expanded', 'false');
  await expect(navigation).toBeHidden();
  await expect(menuButton).toBeFocused();
});

test('skip link is the first tab stop and transfers focus to main content', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Skip-link keyboard behavior is checked once');
  await page.goto('/');
  await page.keyboard.press('Tab');
  await expect(page.locator('.skip-link')).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(page.locator('main#main-content')).toBeFocused();
});

test('desktop submenu Escape closes the panel and restores focus to its toggle', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Desktop submenu keyboard behavior is checked once');
  await page.goto('/');
  const item = page.locator('[data-nav-item]').first();
  const toggle = item.locator('.nav-sub-toggle');
  await toggle.focus();
  await expect(toggle).toHaveAttribute('aria-expanded', 'true');
  await page.keyboard.press('Escape');
  await expect(toggle).toBeFocused();
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await expect(item.locator('.nav-sub')).toBeHidden();
});

test('shared design tokens and primitives are defined with responsive and reduced-motion guards', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Design-system invariants are checked once');
  await page.goto('/');
  const tokens = await page.evaluate(() => {
    const root = getComputedStyle(document.documentElement);
    const names = [
      '--fs-hero', '--fs-h2', '--fs-h3', '--fs-body', '--fs-small',
      '--sp-1', '--sp-2', '--sp-3', '--sp-4', '--sp-5', '--sp-6',
      '--radius', '--radius-sm', '--shadow-sm', '--accent', '--accent-hover', '--focus'
    ];
    return Object.fromEntries(names.map(name => [name, root.getPropertyValue(name).trim()]));
  });
  for (const [name, value] of Object.entries(tokens)) {
    expect(value, `Missing design token ${name}`).not.toBe('');
  }

  const primitives = await page.evaluate(() => {
    const sheetText = [...document.styleSheets].map(sheet => {
      try { return [...sheet.cssRules].map(rule => rule.cssText).join('\n'); }
      catch { return ''; }
    }).join('\n');
    return {
      designCard: sheetText.includes('.design-card'),
      atAGlance: sheetText.includes('.at-a-glance'),
      ctaBand: sheetText.includes('.cta-band'),
      reducedMotion: sheetText.includes('prefers-reduced-motion: reduce') || sheetText.includes('prefers-reduced-motion:reduce'),
      focusVisible: sheetText.includes(':focus-visible'),
      smallScreenRules: sheetText.includes('max-width: 600px') || sheetText.includes('max-width:600px'),
    };
  });
  expect(primitives).toEqual({
    designCard: true, atAGlance: true, ctaBand: true,
    reducedMotion: true, focusVisible: true, smallScreenRules: true
  });
});

test('primary booking button contrast meets WCAG AA for normal text', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Contrast token check runs once');
  await page.goto('/');
  const colors = await page.locator('.nav-book').evaluate(element => {
    const style = getComputedStyle(element);
    return { foreground: style.color, background: style.backgroundColor };
  });
  const rgb = value => {
    const match = value.match(/^rgba?\(([^)]+)\)$/);
    if (!match) throw new Error(`Unsupported computed color: ${value}`);
    return match[1].split(',').slice(0, 3).map(part => Number(part.trim()) / 255);
  };
  const luminance = value => {
    const channels = rgb(value).map(channel => {
      const linear = channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4;
      return linear;
    });
    return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
  };
  const [foreground, background] = [luminance(colors.foreground), luminance(colors.background)];
  const ratio = (Math.max(foreground, background) + 0.05) / (Math.min(foreground, background) + 0.05);
  expect(ratio, `Book Now foreground/background contrast is ${ratio.toFixed(2)}:1`).toBeGreaterThanOrEqual(4.5);
});


test('dark-surface secondary actions expose the intended white text and outline styles', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Dark-surface style contract is checked once');
  const selectors = [
    '.experience-hero .button-light', '.package-hero .button-light', '.about-cta .button-light',
    '.arrival-contact .button-light', '.restaurant-reserve .button-light', '.wide-feature .button-light',
    '.article-footer-cta .button-secondary', '.experience-cta .button-secondary'
  ];
  const candidates = new Set();
  for (const route of routes) {
    await page.goto(route, { waitUntil: 'domcontentloaded' });
    for (const selector of selectors) {
      const matches = await page.locator(selector).evaluateAll(elements => elements.filter(el => el.offsetParent !== null).map(el => ({
        selector: el.className, color: getComputedStyle(el).color, borderColor: getComputedStyle(el).borderTopColor,
        borderWidth: getComputedStyle(el).borderTopWidth, text: (el.textContent || '').trim()
      })));
      for (const item of matches) candidates.add(JSON.stringify({ route, ...item }));
    }
  }
  const failures = [...candidates].map(item => JSON.parse(item)).filter(item =>
    item.color !== 'rgb(255, 255, 255)' || Number.parseFloat(item.borderWidth) <= 0 || item.borderColor === 'rgba(0, 0, 0, 0)'
  );
  expect(failures, `Dark-surface secondary button style failures: ${JSON.stringify(failures, null, 2)}`).toEqual([]);
});


test('homepage room previews use registered room content and stable detail anchors', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Homepage room-preview consistency is checked once');
  await page.goto('/');
  const cards = page.locator('.home-room-card');
  await expect(cards).toHaveCount(3);

  const preview = await cards.evaluateAll(elements => elements.map(card => ({
    name: (card.querySelector('h3')?.textContent || '').trim(),
    alt: card.querySelector('img')?.getAttribute('alt') || '',
    href: card.querySelector('.home-room-detail-link')?.getAttribute('href') || '',
  })));
  for (const room of preview) {
    expect(room.name).not.toBe('');
    expect(room.alt).not.toBe('');
    expect(room.href).toMatch(/^room\.php#room-[a-z0-9-]+$/);
  }

  await page.goto('/room.php');
  for (const room of preview) {
    const anchor = room.href.split('#')[1];
    await expect(page.locator('#' + anchor)).toHaveCount(1);
  }
});


test('contact page presents direct booking and arrival paths beside the enquiry form', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Contact conversion path is checked once');
  await page.goto('/contact.php');
  const booking = page.locator('.contact-booking-button');
  await expect(booking).toHaveAttribute('href', await page.locator('.nav-book').getAttribute('href'));
  await expect(booking).toHaveAttribute('target', '_blank');
  await expect(booking).toHaveAttribute('rel', /noopener/);
  await expect(page.locator('.contact-arrival-link')).toHaveAttribute('href', 'direction.php');
  await expect(page.getByRole('heading', { name: 'Send an enquiry' })).toBeVisible();
});

test('rooftop menu CTA opens the restaurant menu section directly', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Rooftop-to-menu journey is checked once');
  await page.goto('/roof.php');
  const menuLink = page.getByRole('link', { name: 'View the menu' });
  await expect(menuLink).toHaveAttribute('href', 'restaurant.php#menu');
  await page.goto('restaurant.php#menu');
  await expect(page.locator('#menu')).toBeVisible();
  await expect(page.locator('#menu').getByRole('heading', { name: 'What is on the menu.' })).toBeVisible();
});
