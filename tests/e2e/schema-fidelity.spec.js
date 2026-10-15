const { test, expect } = require('@playwright/test');

async function readGraph(page) {
  const source = await page.locator('script[type="application/ld+json"]').first().textContent();
  const parsed = JSON.parse(source || '{}');
  return Array.isArray(parsed['@graph']) ? parsed['@graph'] : [];
}

test.describe('Sprint 29 structured-data fidelity', () => {
  test('Contact and Directions breadcrumb markup matches visible navigation', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Breadcrumb parity runs once.');

    const routes = [
      { path: '/contact.php', current: 'Contact', pageType: 'ContactPage' },
      { path: '/direction.php', current: 'Directions', pageType: 'WebPage' },
    ];

    for (const route of routes) {
      const response = await page.goto(route.path, { waitUntil: 'domcontentloaded' });
      expect(response, route.path + ': expected HTTP response').not.toBeNull();
      expect(response.status(), route.path + ': route should be public').toBeLessThan(400);

      const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
      await expect(breadcrumb, route.path + ': visible breadcrumb').toBeVisible();
      const visibleItems = await breadcrumb.evaluate(nav =>
        Array.from(nav.children)
          .filter(element => !element.hasAttribute('aria-hidden'))
          .map(element => ({
            name: (element.textContent || '').trim(),
            href: element.tagName === 'A' ? new URL(element.href).pathname : null,
            current: element.getAttribute('aria-current') === 'page',
          }))
      );
      expect(visibleItems.map(item => item.name)).toEqual(['Home', route.current]);
      expect(visibleItems[0].href).toBe('/');
      expect(visibleItems[1].current).toBe(true);

      const graph = await readGraph(page);
      const breadcrumbNodes = graph.filter(node => node['@type'] === 'BreadcrumbList');
      expect(breadcrumbNodes, route.path + ': exactly one BreadcrumbList').toHaveLength(1);
      const list = breadcrumbNodes[0];
      expect(list['@id']).toBe('https://www.hotelgarhjaisal.com' + route.path + '#breadcrumb');
      expect(list.itemListElement.map(item => item.name)).toEqual(visibleItems.map(item => item.name));
      expect(list.itemListElement.map(item => new URL(item.item).pathname))
        .toEqual(['/', route.path]);
      expect(list.itemListElement.map(item => item.position)).toEqual([1, 2]);

      const pageNode = graph.find(node => node['@type'] === route.pageType && node['@id'] === 'https://www.hotelgarhjaisal.com' + route.path + '#webpage');
      expect(pageNode, route.path + ': page entity references the breadcrumb').toBeTruthy();
      expect(pageNode.breadcrumb['@id']).toBe(list['@id']);
    }
  });

  test('room schema is generated only from visible room registry cards, with no invented offers', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Room entity parity runs once.');
    const response = await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    expect(response, 'Rooms page should be public').not.toBeNull();
    expect(response.status()).toBeLessThan(400);

    const cards = await page.locator('.room-card').evaluateAll(elements => elements.map(card => ({
      id: card.id,
      name: (card.querySelector('h3')?.textContent || '').trim(),
      description: (card.querySelector('.room-description')?.textContent || '').trim(),
      image: card.querySelector('img')?.getAttribute('src') || '',
      features: Array.from(card.querySelectorAll('.room-features li')).map(item => (item.textContent || '').trim()),
    })));

    const graph = await readGraph(page);
    const roomNodes = graph.filter(node => node['@type'] === 'HotelRoom');
    expect(roomNodes, 'Every visible room has one HotelRoom entity').toHaveLength(cards.length);
    expect(new Set(roomNodes.map(node => node['@id'])).size).toBe(roomNodes.length);

    const pageNode = graph.find(node => node['@type'] === 'WebPage' && node['@id'] === 'https://www.hotelgarhjaisal.com/room.php#webpage');
    expect(pageNode, 'Rooms WebPage entity exists').toBeTruthy();
    expect(pageNode.mainEntity.map(item => item['@id']).sort()).toEqual(roomNodes.map(node => node['@id']).sort());

    for (const card of cards) {
      const node = roomNodes.find(item => item['@id'] === 'https://www.hotelgarhjaisal.com/room.php#' + card.id);
      expect(node, card.name + ': schema entity exists').toBeTruthy();
      expect(node.name).toBe(card.name);
      expect(node.description).toBe(card.description);
      expect(new URL(node.image).pathname).toBe('/' + card.image.replace(/^\/+/, ''));
      expect(node.containedInPlace['@id']).toBe('https://www.hotelgarhjaisal.com/#hotel');
      expect((node.amenityFeature || []).map(item => item.name)).toEqual(card.features);
      for (const feature of node.amenityFeature || []) {
        expect(feature['@type']).toBe('LocationFeatureSpecification');
        expect(feature.value).toBe(true);
      }

      // Rates and availability are live, date-specific values; the page must
      // not imply offers, ratings, reviews, or availability that are not shown.
      expect(node.offers).toBeUndefined();
      expect(node.aggregateRating).toBeUndefined();
      expect(node.review).toBeUndefined();
    }
  });
});
