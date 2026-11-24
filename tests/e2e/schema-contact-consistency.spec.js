const { test, expect } = require('@playwright/test');

const schemaRoutes = ['/', '/contact.php', '/direction.php', '/roof.php', '/restaurant.php'];
const SHARED_HOTEL_TELEPHONE = '+91-7727047567';
const CONTACT_PHONES = ['+917727047567', '+912992253836'];
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

    expect(hotels[0].email, route + ': same public hotel email').toBe('info@hotelgarhjaisal.com');
    expect(hotels[0].hasMap, route + ': same verified hotel map pin').toBe('https://maps.app.goo.gl/nAbRiZhv78FSbju48');
    for (const url of hotels[0].sameAs) {
      await expect(page.locator('.site-footer a[href="' + url + '"]')).toHaveCount(1);
    }
    if (route === '/contact.php' || route === '/direction.php') {
      const guide = data.nodes.find(node => node['@id'] === data.base.slice(0, -1) + route + '#webpage');
      expect(guide.mainEntity).toEqual({ '@id': hotels[0]['@id'] });
      expect(data.nodes.filter(node => node['@type'] === 'Hotel')).toHaveLength(1);
      for (const phone of CONTACT_PHONES) {
        expect(await page.locator('main a[href="tel:' + phone + '"]').count()).toBeGreaterThan(0);
      }
      for (const email of CONTACT_EMAILS) {
        expect(await page.locator('main a[href="mailto:' + email + '"]').count()).toBeGreaterThan(0);
      }
    }
    if (route === '/restaurant.php') {
      const restaurant = data.nodes.find(node => node['@type'] === 'Restaurant'
        && node['@id'] === data.base + 'restaurant.php#restaurant');
      expect(restaurant, 'Restaurant schema entity remains available').toBeTruthy();
      expect(restaurant.telephone).toBe('+917727047567');
      expect(restaurant.address).toEqual(hotels[0].address);
      expect(restaurant.parentOrganization).toEqual({ '@id': hotels[0]['@id'] });
      if (await page.locator('[data-menu-booklet]').count()) {
        expect(restaurant.hasMenu).toBe(data.base + 'restaurant.php#menu');
        await expect(page.locator('#menu .restaurant-dish').first()).toBeAttached();
      }
      expect(restaurant.hasMap).toBe('https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8');
    }
  }
});
