const { test, expect } = require('@playwright/test');

const schemaRoutes = ['/', '/contact.php', '/direction.php', '/roof.php', '/restaurant.php'];
const SHARED_HOTEL_TELEPHONE = '+91-7727047567';
const CONTACT_PHONES = ['+91 77270 47567', '+91 2992 253836'];
const CONTACT_EMAILS = ['mukeshkila@yahoo.com', 'info@hotelgarhjaisal.com'];

test('shared Hotel JSON-LD telephone remains configuration-backed with unchanged output', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Schema telephone parity runs once on desktop-1280.');

  for (const route of schemaRoutes) {
    const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
    expect(response, route + ': HTTP response').not.toBeNull();
    expect(response.status(), route + ': page remains available').toBeLessThan(400);

    const data = await page.evaluate(() => {
      const canonical = document.querySelector('link[rel="canonical"]')?.href || window.location.href;
      const base = new URL('/', canonical).href;
      const nodes = [];
      const visit = value => {
        if (Array.isArray(value)) {
          value.forEach(visit);
          return;
        }
        if (!value || typeof value !== 'object') return;
        if (typeof value['@type'] === 'string') nodes.push(value);
        Object.values(value).forEach(visit);
      };
      for (const script of document.querySelectorAll('script[type="application/ld+json"]')) {
        visit(JSON.parse(script.textContent || 'null'));
      }
      return { base, nodes };
    });

    const hotels = data.nodes.filter(node => node['@type'] === 'Hotel' && node['@id'] === data.base + '#hotel');
    expect(hotels, route + ': exactly one shared Hotel node').toHaveLength(1);
    expect(hotels[0].telephone, route + ': established schema phone format is preserved')
      .toBe(SHARED_HOTEL_TELEPHONE);

    if (route === '/contact.php') {
      const contactHotel = data.nodes.find(node => node['@type'] === 'Hotel'
        && !node['@id'] && Array.isArray(node.telephone) && Array.isArray(node.email));
      expect(contactHotel, 'ContactPage retains its detailed hotel contact entity').toBeTruthy();
      expect(contactHotel.telephone).toEqual(CONTACT_PHONES);
      expect(contactHotel.email).toEqual(CONTACT_EMAILS);
    }
    if (route === '/restaurant.php') {
      const restaurant = data.nodes.find(node => node['@type'] === 'Restaurant'
        && node['@id'] === data.base + 'restaurant.php#restaurant');
      expect(restaurant, 'Restaurant schema entity remains available').toBeTruthy();
      expect(restaurant.telephone).toBe('+917727047567');
    }
  }
});
