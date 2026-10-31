const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';
const GALLERY_THUMB_SIZES = '(max-width: 500px) 100vw, (max-width: 760px) 50vw, (max-width: 1000px) 33vw, 25vw';
const ROOM_CAROUSEL_SIZES = '(max-width: 1080px) 100vw, min(45vw, 600px)';
const EXPERIENCE_CAROUSEL_SIZES = '(max-width: 640px) 100vw, (max-width: 1024px) 90vw, 95vw';
const CARD_SIZES = '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw';
const NARROW_CARD_SIZES = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw';

const responsiveSlots = [
  { route: '/about.php', selector: '.intro-grid img[src="img/v2/courtyard.jpg"]', sizes: '(max-width: 760px) 100vw, 50vw' },
  { route: '/about.php', selector: '.wide-feature .media img[src="img/big6.jpg"]', sizes: '(max-width: 760px) 100vw, 50vw' },
  { route: '/blog.php', selector: '.blog-card--featured img[src="img/j7.jpg"]', sizes: '(max-width: 900px) 100vw, 60vw' },
  { route: '/blog.php', selector: '.blog-card img[src="img/desert3.jpg"]', sizes: CARD_SIZES },
  { route: '/blog.php', selector: '.blog-card img[src="img/s4.jpg"]', sizes: CARD_SIZES },
  { route: '/blog.php', selector: '.blog-card img[src="img/big6.jpg"]', sizes: CARD_SIZES },
  { route: '/blog.php', selector: '.blog-card img[src="img/jcity.jpg"]', sizes: CARD_SIZES },
  { route: '/car-tours.php', selector: '.package-hero-media img[src="img/jcity.jpg"]', sizes: '(max-width: 1000px) 100vw, 1000px' },
  { route: '/car-tours.php', selector: '.experience-card-grid img[src="img/j10.jpg"]', sizes: CARD_SIZES },
  { route: '/', selector: '.home-experiences img[src="img/tent.jpg"]', sizes: NARROW_CARD_SIZES },
  { route: '/', selector: '.gallery-strip figure:first-child img[src="img/jcity.jpg"]', sizes: '(max-width: 760px) 100vw, 50vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(2) img[src="img/courtyard.jpg"]', sizes: '(max-width: 760px) 50vw, 28vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(3) img[src="img/recep1.jpg"]', sizes: '(max-width: 760px) 50vw, 28vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(4) img[src="img/s4.jpg"]', sizes: '(max-width: 760px) 50vw, 28vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(5) img[src="img/big9.jpg"]', sizes: '(max-width: 760px) 50vw, 28vw' },
  { route: '/gallery.php', selector: '.gallery-hero img[src="img/v2/rooftop-dusk.jpg"]', sizes: '100vw' },
  { route: '/direction.php', selector: '.page-hero img[src="img/v2/courtyard.jpg"]', sizes: '100vw' },
  { route: '/jaisalmer-fort-guide.php', selector: '.article-image img[src="img/j1.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/jaisalmer-havelis-guide.php', selector: '.article-image img[src="img/j4.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/jaisalmer-beyond-the-fort-guide.php', selector: '.article-image img[src="img/j8.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/jaisalmer-markets-and-crafts-guide.php', selector: '.article-image img[src="img/j9.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/full-day-jaisalmer-tour.php', selector: '.experience-hero-media img[src="img/j2.jpg"]', sizes: '100vw' },
  { route: '/full-day-jaisalmer-tour.php', selector: '.experience-photo-gallery .photo-carousel-slide img[src="img/j10.jpg"]', sizes: EXPERIENCE_CAROUSEL_SIZES },
  { route: '/room.php', selector: '.room-card .photo-carousel-slide img[src="img/maharaniroom.jpg"]', sizes: ROOM_CAROUSEL_SIZES },
  { route: '/pack.php', selector: '.package-hero img[src="img/desert11.jpg"]', sizes: '100vw' },
  { route: '/desert-safari.php', selector: '.experience-hero-media img[src="img/desert3.jpg"]', sizes: '100vw' },
  { route: '/desert-safari.php', selector: '.experience-card-grid img[src="img/desert7.jpg"]', sizes: CARD_SIZES },
  { route: '/guided-jaisalmer-tour.php', selector: '.experience-hero-media img[src="img/j5.jpg"]', sizes: '100vw' },
  { route: '/guided-jaisalmer-tour.php', selector: '.experience-card-grid--two img[src="img/j2.jpg"]', sizes: '(max-width: 760px) 100vw, 50vw' },
  { route: '/restaurant.php', selector: '.restaurant-hero-media img[src="img/rest2.jpg"]', sizes: '(max-width: 760px) 100vw, 45vw' },
  { route: '/roof.php', selector: '.page-hero-media img[src="img/roofslide.webp"]', sizes: '100vw' },
  { route: '/roof.php', selector: '.roof-banner img[src="img/rest3.jpg"]', sizes: '(max-width: 1000px) 100vw, 1000px' },
  { route: '/', selector: '.home-experiences img[src="img/v2/swiss-tent.webp"]', sizes: NARROW_CARD_SIZES },
];

const galleryResponsiveSources = [
  'img/v2/facade.jpg',
  'img/v2/courtyard.jpg',
  'img/v2/sitting-room.jpg',
  'img/v2/fort-from-roof.jpg',
  'img/recep1.jpg',
  'img/s1.jpg',
  'img/s2.jpg',
  'img/s3.jpg',
  'img/s5.jpg',
  'img/maharaniroom.jpg',
  'img/redmaha1.jpg',
  'img/opium1.jpg',
  'img/purple-room.jpg',
  'img/risingroom.jpg',
  'img/scarlet.jpg',
  'img/green-room.jpg',
  'img/rr3.jpg',
  'img/Desert_Camp/camp_from_above.jpeg',
  'img/Desert_Camp/camp_evening.jpeg',
  'img/Desert_Camp/tent_deluxe.jpeg',
  'img/Desert_Camp/Bathroom.jpeg',
  'img/Desert_Camp/Prog.jpeg',
  'img/Desert_Camp/Tent.jpeg',
  'img/Desert_Camp/Tentout.jpeg',
];

test('half-day tour card uses a full-resolution responsive source', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Responsive source-fit contract runs once.');
  await page.goto(BASE_URL + '/guided-jaisalmer-tour.php', { waitUntil: 'domcontentloaded' });
  const cardImage = page.locator('.experience-card-grid--two img[src="img/j5.jpg"]');
  await expect(cardImage).toHaveCount(1);
  await expect(cardImage).toHaveAttribute('width', '1366');
  await expect(cardImage).toHaveAttribute('height', '768');
  await expect(cardImage).toHaveAttribute('sizes', '(max-width: 760px) 100vw, 50vw');
  const srcset = await cardImage.getAttribute('srcset');
  expect(srcset || '').toContain('/img/responsive/j5-480.webp');
  expect(srcset || '').toContain('/img/responsive/j5-800.webp');
  expect(srcset || '').toContain('/img/responsive/j5-1200.webp');
  expect(srcset || '').not.toContain('img/j6.jpg');
});

test('responsive image candidates receive slot-accurate sizes hints', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Slot contract is asserted once; browser source selection is tested separately');
  test.setTimeout(90_000);

  for (const slot of responsiveSlots) {
    const response = await page.goto(BASE_URL + slot.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
    expect(response, slot.route + ' should return source markup').not.toBeNull();
    const sourceMarkup = await response.text();
    const mediaTags = sourceMarkup.match(/<(?:img|source)\b[^>]*>/gi) || [];
    for (const tag of mediaTags) {
      expect((tag.match(/\bsrcset\s*=/gi) || []).length, slot.route + ' must not emit duplicate srcset attributes').toBeLessThanOrEqual(1);
      expect((tag.match(/\bsizes\s*=/gi) || []).length, slot.route + ' must not emit duplicate sizes attributes').toBeLessThanOrEqual(1);
    }
    const image = page.locator(slot.selector);
    await expect(image, slot.route + ' should render ' + slot.selector).toHaveCount(1);
    await expect(image, slot.route + ' sizes hint should match its layout').toHaveAttribute('sizes', slot.sizes);
    await expect(image, slot.route + ' should expose an approved responsive candidate').toHaveAttribute('srcset', /(?:^|\/)img\/responsive\//);
  }

  await page.goto(BASE_URL + '/gallery.php', { waitUntil: 'domcontentloaded', timeout: 20_000 });
  for (const source of galleryResponsiveSources) {
    const exact = page.locator('.gallery-item img[src="' + source + '"]');
    await expect(exact, 'Gallery should render responsive source ' + source).toHaveCount(1);
    await expect(exact, 'Gallery thumbnail should use slot-aware sizes for ' + source).toHaveAttribute('sizes', GALLERY_THUMB_SIZES);
    await expect(exact, 'Gallery thumbnail should expose generated responsive candidates for ' + source).toHaveAttribute('srcset', /(?:^|\/)img\/responsive\//);
  }
});

test('room and experience carousel source candidates cover their rendered image slots', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run controlled mobile and desktop source-selection contexts once.');
  test.setTimeout(60_000);

  const cases = [
    { route: '/room.php', selector: '.room-card .photo-carousel-slide img[src="img/maharaniroom.jpg"]', sizes: ROOM_CAROUSEL_SIZES },
    { route: '/full-day-jaisalmer-tour.php', selector: '.experience-photo-gallery .photo-carousel-slide img[src="img/j10.jpg"]', sizes: EXPERIENCE_CAROUSEL_SIZES },
    { route: '/swiss-tent-safari.php', selector: '.experience-photo-gallery .photo-carousel-slide img[src="img/Desert_Camp/Tent.jpeg"]', sizes: EXPERIENCE_CAROUSEL_SIZES },
    { route: '/swiss-tent-safari.php', selector: '.experience-photo-gallery .photo-carousel-slide img[src="img/Desert_Camp/CampingInJsm.jpeg"]', sizes: EXPERIENCE_CAROUSEL_SIZES },
    { route: '/swiss-tent-safari.php', selector: '.experience-photo-gallery .photo-carousel-slide img[src="img/Desert_Camp/tent_deluxe.jpeg"]', sizes: EXPERIENCE_CAROUSEL_SIZES },
  ];

  for (const width of [390, 1280]) {
    const context = await browser.newContext({
      baseURL: BASE_URL,
      viewport: { width, height: width < 768 ? 844 : 800 },
      deviceScaleFactor: 1,
      isMobile: width <= 390,
      hasTouch: width <= 390,
    });
    try {
      const page = await context.newPage();
      for (const item of cases) {
        const response = await page.goto(item.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
        expect(response, item.route + ' must respond for carousel source selection').not.toBeNull();
        expect(response.status(), item.route + ' must return a successful response').toBeLessThan(400);
        const image = page.locator(item.selector);
        await expect(image, item.route + ' must render its representative carousel image').toHaveCount(1);
        await image.scrollIntoViewIfNeeded();
        await expect.poll(
          () => image.evaluate(img => img.complete && img.naturalWidth > 0),
          { timeout: 15_000 }
        ).toBe(true);
        const state = await image.evaluate(img => {
          const currentPath = new URL(img.currentSrc || img.src, document.baseURI).pathname;
          const sourceSet = img.getAttribute('srcset') || '';
          const selectedCandidate = sourceSet.split(',').map(candidate => {
            const parts = candidate.trim().split(/\s+/);
            try {
              return new URL(parts[0], document.baseURI).pathname === currentPath
                ? { descriptor: parts[1] || '' }
                : null;
            } catch {
              return null;
            }
          }).find(Boolean);
          const widthDescriptor = (selectedCandidate?.descriptor || '').match(/^([1-9][0-9]*)w$/);
          const rect = img.getBoundingClientRect();
          return {
            sizes: img.getAttribute('sizes') || '',
            selectedSourceWidth: widthDescriptor ? Number(widthDescriptor[1]) : 0,
            selectedSourceDescriptor: selectedCandidate?.descriptor || '',
            renderedWidth: rect.width,
          };
        });
        expect(state.sizes, item.route + ' must advertise the shared carousel slot size').toBe(item.sizes);
        expect(state.selectedSourceDescriptor, item.route + ' must select a width-descriptor responsive candidate').toMatch(/^[1-9][0-9]*w$/);
        expect(
          state.selectedSourceWidth / state.renderedWidth,
          item.route + ' selected source pixels must cover at least 90% of the rendered slot'
        ).toBeGreaterThanOrEqual(0.9);
      }
    } finally {
      await context.close();
    }
  }
});


test('wide rooftop and city-tour hero slots select source-appropriate desktop candidates', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run large source-selection checks once at a controlled desktop viewport.');
  test.setTimeout(60_000);

  const cases = [
    {
      route: '/roof.php',
      selector: '.page-hero-media img[src="img/roofslide.webp"]',
      expectedPath: '/img/responsive/roofslide-1280.webp',
      expectedWidth: 1280,
    },
    {
      route: '/roof.php',
      selector: '.roof-banner img[src="img/rest3.jpg"]',
      expectedPath: '/img/rest3.jpg',
      expectedWidth: 900,
    },
    {
      route: '/car-tours.php',
      selector: '.package-hero-media img[src="img/jcity.jpg"]',
      expectedPath: '/img/jcity.jpg',
      expectedNativeWidth: true,
    },
  ];

  for (const item of cases) {
    const response = await page.goto(BASE_URL + item.route, { waitUntil: 'domcontentloaded' });
    expect(response, item.route + ' should return the desktop image markup').not.toBeNull();
    expect(response.status()).toBe(200);

    const image = page.locator(item.selector);
    await expect(image).toHaveCount(1);
    await expect(image).toHaveAttribute('srcset', /[1-9][0-9]*w/);
    await expect.poll(() => image.evaluate(img => img.complete && img.naturalWidth > 0), { timeout: 15_000 }).toBe(true);

    const selected = await image.evaluate(img => {
      const currentPath = new URL(img.currentSrc || img.src, document.baseURI).pathname;
      const sourcePath = new URL(img.getAttribute('src') || img.src, document.baseURI).pathname;
      const sourceWidth = Number(img.getAttribute('width')) || img.naturalWidth;
      const candidates = (img.getAttribute('srcset') || '').split(',').map(candidate => {
        const parts = candidate.trim().split(/\s+/);
        const width = Number((parts[1] || '').replace(/w$/, ''));
        try {
          return parts.length > 1 && width > 0
            ? { path: new URL(parts[0], document.baseURI).pathname, width }
            : null;
        } catch {
          return null;
        }
      }).filter(Boolean);
      const descriptor = candidates.find(candidate => candidate.path === currentPath);
      return {
        path: currentPath,
        sourcePath,
        sourceWidth,
        candidates,
        selectedWidth: descriptor?.width || 0,
        renderedWidth: img.getBoundingClientRect().width,
      };
    });

    if (item.expectedNativeWidth) {
      const generated = selected.candidates
        .filter(candidate => candidate.path !== selected.sourcePath)
        .sort((a, b) => a.width - b.width);
      const largestGenerated = generated[generated.length - 1];
      const needsNativeFallback = !largestGenerated || largestGenerated.width / selected.sourceWidth < 0.9;
      const expectedPath = needsNativeFallback ? selected.sourcePath : largestGenerated.path;
      const expectedWidth = needsNativeFallback ? selected.sourceWidth : largestGenerated.width;
      expect(selected.path, item.route + ' should select the largest valid derivative or native source fallback').toBe(expectedPath);
      expect(selected.selectedWidth, item.route + ' should expose the truthful selected width descriptor').toBe(expectedWidth);
    } else {
      expect(selected.path, item.route + ' should select the reviewed source candidate').toBe(item.expectedPath);
      expect(selected.selectedWidth, item.route + ' should expose the truthful width descriptor').toBe(item.expectedWidth);
    }
    expect(
      selected.selectedWidth / selected.renderedWidth,
      item.route + ' should deliver at least 90% of the rendered slot in source pixels'
    ).toBeGreaterThanOrEqual(0.9);
  }
});
