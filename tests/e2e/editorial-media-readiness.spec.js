const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

const root = path.resolve(__dirname, '../..');
const reportPath = path.join(root, 'artifacts', 'editorial-media-readiness', 'editorial-media-readiness.json');
const routes = [
  { path: '/best-time-to-visit-jaisalmer.php', kind: 'editorial' },
  { path: '/how-to-reach-jaisalmer.php', kind: 'editorial' },
  { path: '/jaisalmer-desert-safari-packing-list.php', kind: 'editorial' },
  { path: '/jaisalmer-fort-guide.php', kind: 'editorial' },
  { path: '/desert-safari-guide.php', kind: 'editorial' },
  { path: '/jaisalmer-2-day-itinerary.php', kind: 'editorial' },
  { path: '/jaisalmer-3-day-itinerary.php', kind: 'editorial' },
  { path: '/jaisalmer-honeymoon-guide.php', kind: 'editorial' },
  { path: '/jaisalmer-havelis-guide.php', kind: 'editorial' },
  { path: '/jaisalmer-food-guide.php', kind: 'editorial' },
  { path: '/jaisalmer-beyond-the-fort-guide.php', kind: 'editorial' },
  { path: '/jaisalmer-markets-and-crafts-guide.php', kind: 'editorial' },
  { path: '/desert-safari.php', kind: 'experience' },
  { path: '/guided-jaisalmer-tour.php', kind: 'experience' },
  { path: '/sunset-camel-safari.php', kind: 'experience' },
  { path: '/sleep-under-the-stars.php', kind: 'experience' },
  { path: '/swiss-tent-safari.php', kind: 'experience' },
  { path: '/cultural-safari.php', kind: 'experience' },
  { path: '/half-day-jaisalmer-tour.php', kind: 'experience' },
  { path: '/full-day-jaisalmer-tour.php', kind: 'experience' },
];

test('editorial and experience hero media is publish-ready', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Editorial media audit runs once at desktop width.');
  test.setTimeout(180_000);

  const inventory = [];
  const findings = [];
  for (const route of routes) {
    const response = await page.goto(route.path, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, route.path + ' should return a response').not.toBeNull();
    expect(response.status(), route.path + ' should remain public').toBe(200);

    const imageSelector = route.kind === 'editorial'
      ? 'article.article-content figure.article-image img'
      : '.experience-hero-media > img';
    const captionSelector = route.kind === 'editorial'
      ? 'article.article-content figure.article-image figcaption.article-caption'
      : '.experience-hero-caption';
    const image = page.locator(imageSelector);
    const caption = page.locator(captionSelector);

    await expect(image, route.path + ' has one hero image').toHaveCount(1);
    const altText = (await image.getAttribute('alt') || '').trim();
    expect(altText.length, route.path + ' hero image alt text should be descriptive').toBeGreaterThanOrEqual(15);
    await expect(caption, route.path + ' has a visible editorial media caption').toBeVisible();
    const captionId = await caption.getAttribute('id');
    expect(captionId, route.path + ' caption should have a stable id').toBe(route.kind === 'editorial' ? 'article-hero-caption' : 'experience-hero-caption');
    await expect(image, route.path + ' hero image should reference its visible caption for assistive technology').toHaveAttribute('aria-describedby', captionId);
    const captionText = (await caption.textContent() || '').trim();
    expect(captionText.length, route.path + ' caption should meet the 20-character minimum').toBeGreaterThanOrEqual(20);
    expect(captionText.length, route.path + ' caption should fit the 180-character editorial limit').toBeLessThanOrEqual(180);
    await image.evaluate(img => img.decode());
    const intrinsic = await image.evaluate(img => ({
      width: Number(img.getAttribute('width') || 0),
      height: Number(img.getAttribute('height') || 0),
      naturalWidth: img.naturalWidth,
      naturalHeight: img.naturalHeight,
      decoding: img.getAttribute('decoding'),
    }));
    expect(intrinsic.width, route.path + ' hero width should be declared').toBeGreaterThan(0);
    expect(intrinsic.height, route.path + ' hero height should be declared').toBeGreaterThan(0);
    const declaredAspectRatio = intrinsic.width / intrinsic.height;
    const loadedAspectRatio = intrinsic.naturalWidth / intrinsic.naturalHeight;
    expect(
      Math.abs(declaredAspectRatio / loadedAspectRatio - 1),
      route.path + ' declared dimensions should preserve the loaded responsive asset aspect ratio'
    ).toBeLessThanOrEqual(0.01);
    expect(intrinsic.decoding, route.path + ' should decode the hero asynchronously').toBe('async');

    const rendered = await page.evaluate(({ imageSelector, captionSelector }) => {
      const image = document.querySelector(imageSelector);
      const meta = selector => document.querySelector(selector)?.getAttribute('content') || '';
      const nodes = [];
      for (const script of document.querySelectorAll('script[type="application/ld+json"]')) {
        try {
          const parsed = JSON.parse(script.textContent || 'null');
          if (Array.isArray(parsed?.['@graph'])) nodes.push(...parsed['@graph']);
          else if (parsed && typeof parsed === 'object') nodes.push(parsed);
        } catch {}
      }
      const webPage = nodes.find(node => node['@type'] === 'WebPage');
      const article = nodes.find(node => node['@type'] === 'BlogPosting');
      const imageObject = webPage?.primaryImageOfPage || article?.image?.[0] || null;
      const heroSource = image?.getAttribute('src') || '';
      const absoluteHero = heroSource ? new URL(heroSource, document.baseURI).href : '';
      return {
        title: document.title,
        heroSource: absoluteHero,
        currentSource: image?.currentSrc || '',
        responsiveSrcset: image?.getAttribute('srcset') || '',
        responsiveSizes: image?.getAttribute('sizes') || '',
        alt: image?.getAttribute('alt') || '',
        caption: document.querySelector(captionSelector)?.textContent?.trim() || '',
        naturalWidth: image?.naturalWidth || 0,
        naturalHeight: image?.naturalHeight || 0,
        complete: Boolean(image?.complete),
        ogImage: meta('meta[property="og:image"]'),
        ogAlt: meta('meta[property="og:image:alt"]'),
        ogWidth: meta('meta[property="og:image:width"]'),
        ogHeight: meta('meta[property="og:image:height"]'),
        ogType: meta('meta[property="og:image:type"]'),
        twitterImage: meta('meta[name="twitter:image"]'),
        twitterAlt: meta('meta[name="twitter:image:alt"]'),
        imageObject,
        articleImage: article?.image?.[0] || null,
      };
    }, { imageSelector, captionSelector });

    const issues = [];
    const heroUrl = new URL(rendered.heroSource);
    const ogUrl = new URL(rendered.ogImage);
    if (heroUrl.pathname !== ogUrl.pathname) issues.push('social-image-does-not-match-hero');
    if (!rendered.complete || rendered.naturalWidth < 1 || rendered.naturalHeight < 1) issues.push('hero-image-failed-to-decode');
    if (rendered.ogAlt.trim() !== rendered.alt.trim()) issues.push('open-graph-alt-does-not-match-hero-alt');
    if (rendered.twitterImage !== rendered.ogImage) issues.push('twitter-image-does-not-match-open-graph-image');
    if (rendered.twitterAlt.trim() !== rendered.ogAlt.trim()) issues.push('twitter-alt-does-not-match-open-graph-alt');
    if (rendered.imageObject?.url !== rendered.ogImage) issues.push('image-object-url-does-not-match-social-image');
    if (rendered.imageObject?.caption !== rendered.caption) issues.push('image-object-caption-does-not-match-visible-caption');
    if (rendered.imageObject?.description !== rendered.alt) issues.push('image-object-description-does-not-match-alt');
    if (rendered.responsiveSrcset) {
      const candidates = rendered.responsiveSrcset.split(',').map(candidate => {
        const parts = candidate.trim().split(/\s+/);
        return { url: new URL(parts[0], rendered.heroSource), width: Number((parts[1] || '').replace(/w$/, '')) };
      });
      const widths = candidates.map(candidate => candidate.width);
      if (candidates.length < 2 || widths.some(width => !Number.isInteger(width) || width < 1)) {
        issues.push('responsive-srcset-has-insufficient-or-invalid-width-descriptors');
      }
      if (new Set(widths).size !== widths.length || widths.some((width, index) => index > 0 && width <= widths[index - 1])) {
        issues.push('responsive-srcset-widths-are-not-unique-and-ascending');
      }
      if (candidates.some(candidate => candidate.url.origin !== heroUrl.origin || !candidate.url.pathname.startsWith('/img/responsive/'))) {
        issues.push('responsive-srcset-contains-unsafe-or-unexpected-asset-path');
      }
      if (!rendered.responsiveSizes.trim()) issues.push('responsive-srcset-is-missing-sizes');
    }

    const sourceExtension = heroUrl.pathname.split('.').pop().toLowerCase();
    const supportsMetadataDimensions = sourceExtension !== 'avif';
    const hasWidth = /^[1-9][0-9]*$/.test(rendered.ogWidth);
    const hasHeight = /^[1-9][0-9]*$/.test(rendered.ogHeight);
    const hasType = /^image\/[a-z0-9.+-]+$/i.test(rendered.ogType);
    if (hasWidth !== hasHeight || hasWidth !== hasType) issues.push('social-image-dimensions-or-type-are-partial');
    if (supportsMetadataDimensions && (!hasWidth || !hasHeight || !hasType)) issues.push('supported-social-image-is-missing-intrinsic-metadata');
    if (hasWidth && (Number(rendered.imageObject?.width) !== Number(rendered.ogWidth)
      || Number(rendered.imageObject?.height) !== Number(rendered.ogHeight))) {
      issues.push('image-object-dimensions-do-not-match-open-graph');
    }
    if (hasType && rendered.imageObject?.encodingFormat !== rendered.ogType) issues.push('image-object-format-does-not-match-open-graph');
    if (route.kind === 'editorial' && rendered.articleImage?.url !== rendered.ogImage) {
      issues.push('blog-posting-image-does-not-match-social-image');
    }

    const entry = {
      route: route.path,
      kind: route.kind,
      title: rendered.title,
      source: heroUrl.pathname,
      currentSource: rendered.currentSource ? new URL(rendered.currentSource).pathname : '',
      responsiveSrcset: rendered.responsiveSrcset,
      responsiveSizes: rendered.responsiveSizes,
      naturalWidth: rendered.naturalWidth,
      naturalHeight: rendered.naturalHeight,
      alt: rendered.alt,
      caption: rendered.caption,
      ogImage: rendered.ogImage,
      ogDimensions: hasWidth && hasHeight ? { width: Number(rendered.ogWidth), height: Number(rendered.ogHeight) } : null,
      ogType: rendered.ogType,
      issues,
    };
    inventory.push(entry);
    if (issues.length) findings.push(entry);
  }

  fs.mkdirSync(path.dirname(reportPath), { recursive: true });
  fs.writeFileSync(reportPath, JSON.stringify({
    generatedAt: new Date().toISOString(),
    source: 'Local PHP rendering in Playwright; this is a repository readiness audit, not a production crawl.',
    routesChecked: routes.length,
    editorialRoutesChecked: routes.filter(route => route.kind === 'editorial').length,
    experienceRoutesChecked: routes.filter(route => route.kind === 'experience').length,
    findings: findings.length,
    routes: inventory,
  }, null, 2) + '\n');
  await testInfo.attach('editorial-media-readiness', { path: reportPath, contentType: 'application/json' });
  expect(findings, JSON.stringify(findings, null, 2)).toEqual([]);
});
