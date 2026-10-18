const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://127.0.0.1:8080';
const GALLERY_THUMB_SIZES = '(max-width: 500px) 100vw, (max-width: 760px) 50vw, (max-width: 1000px) 33vw, 25vw';
const CARD_SIZES = '(max-width: 760px) 100vw, (max-width: 900px) 50vw, 33vw';

const responsiveSlots = [
  { route: '/about.php', selector: '.intro-grid img[src="img/v2/courtyard.jpg"]', sizes: '(max-width: 1080px) 100vw, 50vw' },
  { route: '/about.php', selector: '.wide-feature .media img[src="img/big6.jpg"]', sizes: '(max-width: 1080px) 100vw, 50vw' },
  { route: '/blog.php', selector: '.blog-card--featured img[src="img/j7.jpg"]', sizes: '(max-width: 900px) 100vw, 60vw' },
  { route: '/blog.php', selector: '.blog-card img[src="img/desert3.jpg"]', sizes: CARD_SIZES },
  { route: '/blog.php', selector: '.blog-card img[src="img/s4.jpg"]', sizes: CARD_SIZES },
  { route: '/blog.php', selector: '.blog-card img[src="img/big6.jpg"]', sizes: CARD_SIZES },
  { route: '/blog.php', selector: '.blog-card img[src="img/jcity.jpg"]', sizes: CARD_SIZES },
  { route: '/car-tours.php', selector: '.package-hero-media img[src="img/jcity.jpg"]', sizes: '100vw' },
  { route: '/car-tours.php', selector: '.experience-card-grid img[src="img/j10.jpg"]', sizes: CARD_SIZES },
  { route: '/', selector: '.home-experiences img[src="img/tent.jpg"]', sizes: '(max-width: 1080px) 100vw, 33vw' },
  { route: '/', selector: '.gallery-strip figure:first-child img[src="img/jcity.jpg"]', sizes: '(max-width: 1080px) 100vw, 48vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(2) img[src="img/courtyard.jpg"]', sizes: '(max-width: 1080px) 50vw, 26vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(3) img[src="img/recep1.jpg"]', sizes: '(max-width: 1080px) 50vw, 26vw' },
  { route: '/', selector: '.gallery-strip figure:nth-child(4) img[src="img/s4.jpg"]', sizes: '(max-width: 1080px) 50vw, 26vw' },
  { route: '/gallery.php', selector: '.gallery-hero img[src="img/v2/rooftop-dusk.jpg"]', sizes: '100vw' },
  { route: '/direction.php', selector: '.page-hero img[src="img/v2/courtyard.jpg"]', sizes: '100vw' },
  { route: '/jaisalmer-fort-guide.php', selector: '.article-image img[src="img/j1.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/jaisalmer-havelis-guide.php', selector: '.article-image img[src="img/j4.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/jaisalmer-beyond-the-fort-guide.php', selector: '.article-image img[src="img/j8.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/jaisalmer-markets-and-crafts-guide.php', selector: '.article-image img[src="img/j9.jpg"]', sizes: '(max-width: 768px) 100vw, 60vw' },
  { route: '/full-day-jaisalmer-tour.php', selector: '.experience-hero-media img[src="img/j2.jpg"]', sizes: '100vw' },
  { route: '/pack.php', selector: '.package-hero img[src="img/desert11.jpg"]', sizes: '100vw' },
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

test('responsive image candidates receive slot-accurate sizes hints', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Slot contract is asserted once; browser source selection is tested separately');
  test.setTimeout(90_000);

  for (const slot of responsiveSlots) {
    await page.goto(BASE_URL + slot.route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
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
