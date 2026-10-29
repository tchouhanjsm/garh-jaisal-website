const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';
const GALLERY_THUMB_SIZES = '(max-width: 500px) 100vw, (max-width: 760px) 50vw, (max-width: 1000px) 33vw, 25vw';
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
  { route: '/car-tours.php', selector: '.package-hero-media img[src="img/jcity.jpg"]', sizes: '100vw' },
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
  { route: '/pack.php', selector: '.package-hero img[src="img/desert11.jpg"]', sizes: '100vw' },
  { route: '/desert-safari.php', selector: '.experience-hero-media img[src="img/desert3.jpg"]', sizes: '100vw' },
  { route: '/desert-safari.php', selector: '.experience-card-grid img[src="img/desert7.jpg"]', sizes: CARD_SIZES },
  { route: '/guided-jaisalmer-tour.php', selector: '.experience-hero-media img[src="img/j5.jpg"]', sizes: '100vw' },
  { route: '/guided-jaisalmer-tour.php', selector: '.experience-card-grid--two img[src="img/j2.jpg"]', sizes: '(max-width: 760px) 100vw, 50vw' },
  { route: '/restaurant.php', selector: '.restaurant-hero-media img[src="img/rest2.jpg"]', sizes: '(max-width: 760px) 100vw, 45vw' },
  { route: '/roof.php', selector: '.page-hero-media img[src="img/rest5.jpg"]', sizes: '100vw' },
  { route: '/roof.php', selector: '.roof-banner img[src="img/rest3.jpg"]', sizes: '(max-width: 760px) 100vw, 1180px' },
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

test('small tour source does not create unsafe upscaled responsive candidates', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Intrinsic-source fallback contract runs once.');
  await page.goto(BASE_URL + '/guided-jaisalmer-tour.php', { waitUntil: 'domcontentloaded' });
  const smallSource = page.locator('.experience-card-grid--two img[src="img/j6.jpg"]');
  await expect(smallSource).toHaveCount(1);
  await expect(smallSource).toHaveAttribute('width', '370');
  await expect(smallSource).toHaveAttribute('height', '539');
  await expect(smallSource).not.toHaveAttribute('sizes', /.+/);
  const srcset = await smallSource.getAttribute('srcset');
  expect(srcset || '').not.toMatch(/\/img\/responsive\//);
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
    await expect(image, slot.route + ' should expose an approved responsive candidate').toHaveAttribute('srcset', /\/img\/responsive\//);
  }

  await page.goto(BASE_URL + '/gallery.php', { waitUntil: 'domcontentloaded', timeout: 20_000 });
  for (const source of galleryResponsiveSources) {
    const exact = page.locator('.gallery-item img[src="' + source + '"]');
    await expect(exact, 'Gallery should render responsive source ' + source).toHaveCount(1);
    await expect(exact, 'Gallery thumbnail should use slot-aware sizes for ' + source).toHaveAttribute('sizes', GALLERY_THUMB_SIZES);
    await expect(exact, 'Gallery thumbnail should expose generated responsive candidates for ' + source).toHaveAttribute('srcset', /\/img\/responsive\//);
  }
});
