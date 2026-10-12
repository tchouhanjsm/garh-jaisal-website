const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

function getSitemapRoutes() {
  const sitemap = fs.readFileSync(path.join(__dirname, '../../sitemap.xml'), 'utf8');
  return [...new Set(
    [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
      .map(([, value]) => new URL(value.trim()))
      .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
      .map(url => url.pathname || '/')
  )].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

const routes = getSitemapRoutes();

test('write rendered page layout inventory for sitemap routes', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Layout inventory runs once at desktop width');
  test.setTimeout(180_000);

  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();
  const inventory = [];

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected HTTP status for ${route}`).toBeLessThan(400);

      const layout = await page.evaluate(() => {
        const text = element => (element?.innerText || element?.textContent || '').replace(/\s+/g, ' ').trim();
        const visible = element => {
          const style = window.getComputedStyle(element);
          const rect = element.getBoundingClientRect();
          return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
        };
        const main = document.querySelector('main');
        const sections = main ? [...main.querySelectorAll(':scope > section, :scope > div')] : [];
        const firstSection = sections[0] || main?.firstElementChild || null;
        const headingText = selector => [...document.querySelectorAll(selector)].map(text).filter(Boolean);
        const ctaLinks = [...document.querySelectorAll('main a[href]')]
          .filter(visible)
          .map(anchor => ({
            text: text(anchor),
            href: anchor.getAttribute('href') || '',
            classes: typeof anchor.className === 'string' ? anchor.className : '',
          }))
          .filter(link => link.text || /button|cta/i.test(link.classes))
          .slice(0, 30);
        const imageElements = [...document.querySelectorAll('main img')];
        const forms = [...document.querySelectorAll('main form')];

        return {
          title: document.title,
          h1: headingText('main h1'),
          h2: headingText('main h2'),
          h3: headingText('main h3'),
          mainPresent: Boolean(main),
          mainChildCount: main?.children.length || 0,
          topLevelSectionCount: sections.length,
          firstSectionTag: firstSection?.tagName.toLowerCase() || null,
          firstSectionClasses: typeof firstSection?.className === 'string' ? firstSection.className : '',
          layoutSignals: {
            hero: Boolean(document.querySelector('main .hero, main [class*="hero"]')),
            cardGroups: document.querySelectorAll('main .cards, main [class*="card-grid"], main [class*="card-list"]').length,
            galleries: document.querySelectorAll('main .gallery, main [class*="gallery"]').length,
            forms: forms.length,
            tables: document.querySelectorAll('main table').length,
            accordions: document.querySelectorAll('main details, main [aria-expanded]').length,
            breadcrumb: Boolean(document.querySelector('main nav[aria-label*="breadcrumb" i], main [class*="breadcrumb"]')),
            finalCta: Boolean(document.querySelector('main .final-cta, main [class*="final-cta"], main [class*="contact-cta"]')),
          },
          images: imageElements.map(img => ({
            src: img.currentSrc || img.src || '',
            alt: img.getAttribute('alt'),
            width: img.getAttribute('width'),
            height: img.getAttribute('height'),
            loading: img.loading || 'eager',
          })),
          imageCount: imageElements.length,
          missingAltCount: imageElements.filter(img => !img.hasAttribute('alt')).length,
          formsSummary: forms.map(form => ({
            method: (form.getAttribute('method') || 'get').toLowerCase(),
            action: form.getAttribute('action') || '',
            controlCount: form.querySelectorAll('input, select, textarea, button').length,
            submitText: text(form.querySelector('[type="submit"], button:not([type])')),
          })),
          visibleCtas: ctaLinks,
          navigationLinks: [...document.querySelectorAll('header nav a[href], nav[aria-label] a[href]')]
            .map(anchor => ({ text: text(anchor), href: anchor.getAttribute('href') || '' }))
            .filter(link => link.text || link.href)
            .slice(0, 40),
        };
      });

      inventory.push({ route, httpStatus: response.status(), ...layout });
    }
  } finally {
    await context.close();
  }

  const summary = {
    routesChecked: inventory.length,
    routesMissingMain: inventory.filter(item => !item.mainPresent).map(item => item.route),
    routesWithoutH1: inventory.filter(item => item.h1.length === 0).map(item => item.route),
    routesWithMultipleH1: inventory.filter(item => item.h1.length > 1).map(item => item.route),
    routesWithForms: inventory.filter(item => item.formsSummary.length).map(item => item.route),
    routesWithHero: inventory.filter(item => item.layoutSignals.hero).map(item => item.route),
    routesWithNoImages: inventory.filter(item => item.imageCount === 0).map(item => item.route),
  };

  console.log('Rendered page layout inventory summary:', JSON.stringify(summary, null, 2));

  const reportDir = path.join(process.cwd(), 'artifacts', 'page-layout-inventory');
  fs.mkdirSync(reportDir, { recursive: true });
  const reportPath = path.join(reportDir, 'page-layout-inventory.json');
  fs.writeFileSync(reportPath, JSON.stringify({
    generatedAt: new Date().toISOString(),
    source: 'local PHP site rendered by Playwright in Chromium; structure inventory, not a visual-quality verdict',
    viewport: { width: 1280, height: 800 },
    summary,
    pages: inventory,
  }, null, 2) + '\n');

  await testInfo.attach('page-layout-inventory', {
    path: reportPath,
    contentType: 'application/json',
  });

  expect(inventory.length, 'Every sitemap route should appear in the layout inventory').toBe(routes.length);
});
