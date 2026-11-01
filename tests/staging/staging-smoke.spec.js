const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');
const { cacheMaxAgeSeconds, normalizeImagePolicySnapshot, normalizedSrcset, parseReleaseMarker, selectedSrcsetWidth } = require('../../scripts/staging-delivery-diagnostics');

const ROOT = path.resolve(__dirname, '../..');
const REMOTE_TARGET = (process.env.STAGING_BASE_URL || '').trim();
const EXTERNAL_STAGING = Boolean(REMOTE_TARGET);
const BASE_URL = EXTERNAL_STAGING
  ? new URL(REMOTE_TARGET).origin
  : 'http://127.0.0.1:8081';
const STAGING_ORIGIN = new URL(BASE_URL).origin;
const EXPECTED_SHA = (process.env.STAGING_EXPECTED_COMMIT_SHA || '').toLowerCase();
const CHECKED_OUT_SHA = (process.env.CHECKED_OUT_COMMIT_SHA || '').toLowerCase();
const CORE_ROUTES = ['/', '/room.php', '/contact.php', '/direction.php', '/restaurant.php', '/roof.php', '/desert-safari.php', '/guided-jaisalmer-tour.php', '/car-tours.php', '/gallery.php', '/blog.php'];
const MIN_IMAGE_CACHE_AGE_SECONDS = 7 * 24 * 60 * 60;
const IMAGE_POLICY_SNAPSHOT_KEY = '__garhJaisalStagingImagePolicySnapshots';

function routePathsFromSitemap(content) {
  return [...new Set(
    [...content.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
      .map(([, value]) => new URL(value.trim()).pathname || '/')
  )].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

function safeName(route) {
  return route === '/' ? 'home' : route.replace(/^\//, '').replace(/[^a-zA-Z0-9._-]+/g, '-');
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[character]));
}

const REPOSITORY_SITEMAP = fs.readFileSync(path.join(ROOT, 'sitemap.xml'), 'utf8');
const PUBLISHED_ROUTES = routePathsFromSitemap(REPOSITORY_SITEMAP);
const ROUTES_TO_CHECK = EXTERNAL_STAGING ? PUBLISHED_ROUTES : CORE_ROUTES;

test('staging release gate: route inventory, SEO, images, responsive layout and server protections', async ({ page, request }, testInfo) => {
  test.setTimeout(20 * 60 * 1000);
  const failures = [];
  const routeResults = [];
  const screenshots = [];
  const selectedImageUrls = new Set();
  const imageCacheAudit = [];
  let releaseProvenance = { status: 'not-run', packageCommit: null };
  const screenshotsDir = path.join(process.cwd(), 'artifacts', 'staging-qa', 'screenshots');
  fs.mkdirSync(screenshotsDir, { recursive: true });
  let activePageErrors = [];
  page.on('pageerror', error => activePageErrors.push(error.message));

  const record = (condition, message, context = {}) => {
    if (!condition) failures.push({ message, ...context });
  };

  if (EXTERNAL_STAGING) {
    record(/^[0-9a-f]{40}$/i.test(EXPECTED_SHA), 'External run is missing a full staged-package SHA.');
    record(EXPECTED_SHA === CHECKED_OUT_SHA, 'Staged package SHA differs from the workflow checkout SHA.');

    try {
      const markerResponse = await request.get(new URL('/release-info.txt', BASE_URL).href, { maxRedirects: 0, timeout: 15_000 });
      const markerText = await markerResponse.text();
      const observedCommit = parseReleaseMarker(markerText);
      const markerHeaders = markerResponse.headers();
      releaseProvenance = {
        status: markerResponse.status(),
        packageCommit: observedCommit,
        expectedCommit: EXPECTED_SHA,
        contentType: markerHeaders['content-type'] || null,
        cacheControl: markerHeaders['cache-control'] || null,
        robotsHeader: markerHeaders['x-robots-tag'] || null,
      };
      record(markerResponse.status() === 200, 'Staging release marker must return HTTP 200.', { status: markerResponse.status() });
      record(Boolean(observedCommit), 'Staging release marker is missing or malformed.');
      record(observedCommit === EXPECTED_SHA, 'Staging is not serving the exact reviewed package commit.', { expectedCommit: EXPECTED_SHA, observedCommit });
      record((markerHeaders['content-type'] || '').toLowerCase().startsWith('text/plain'), 'Staging release marker must be text/plain.', { contentType: markerHeaders['content-type'] || null });
      record(/noindex/i.test(markerHeaders['x-robots-tag'] || ''), 'Staging release marker must be noindex.');
      record(/no-store/i.test(markerHeaders['cache-control'] || ''), 'Staging release marker must not be cached.', { cacheControl: markerHeaders['cache-control'] || null });
      record(markerText.length <= 128, 'Staging release marker exceeds its 128-character safety limit.', { characters: markerText.length });
    } catch (error) {
      releaseProvenance = { status: 'request-failed', packageCommit: null, detail: error.message };
      failures.push({ message: 'Unable to verify staged package release provenance.', detail: error.message });
    }
    try {
      const sitemapResponse = await request.get(new URL('/sitemap.xml', BASE_URL).href, { maxRedirects: 0, timeout: 20_000 });
      record(sitemapResponse.status() < 400, 'Staging sitemap did not return a successful response.', { status: sitemapResponse.status() });
      record(new URL(sitemapResponse.url()).origin === STAGING_ORIGIN, 'Staging sitemap redirected away from the supplied staging origin.');
      if (sitemapResponse.status() < 400) {
        const stagingSitemapPaths = routePathsFromSitemap(await sitemapResponse.text());
        record(
          JSON.stringify(stagingSitemapPaths) === JSON.stringify(PUBLISHED_ROUTES),
          'Staging sitemap route inventory differs from the repository sitemap.',
          { expectedRoutes: PUBLISHED_ROUTES, stagingRoutes: stagingSitemapPaths },
        );
      }
    } catch (error) {
      failures.push({ message: 'Unable to read the staging sitemap.', detail: error.message });
    }

    try {
      const rootResponse = await request.get(new URL('/', BASE_URL).href, { maxRedirects: 0, timeout: 20_000 });
      record(rootResponse.status() === 200, 'Staging root must return HTTP 200 without redirect.', { status: rootResponse.status() });
      record(new URL(rootResponse.url()).origin === STAGING_ORIGIN, 'Staging root did not remain on the staging origin.');
      const headers = rootResponse.headers();
      for (const [name, expected] of [
        ['x-content-type-options', 'nosniff'],
        ['x-frame-options', 'SAMEORIGIN'],
        ['referrer-policy', 'strict-origin-when-cross-origin'],
      ]) {
        record((headers[name] || '').toLowerCase() === expected.toLowerCase(), 'Staging security header is missing or incorrect: ' + name, { actual: headers[name] || null });
      }
      record(Boolean(headers['permissions-policy']), 'Staging Permissions-Policy header is missing.');
    } catch (error) {
      failures.push({ message: 'Unable to inspect staging root headers.', detail: error.message });
    }

    for (const protectedPath of ['/.htaccess', '/include/site-config.local.php', '/include/site-config.example.php']) {
      try {
        const response = await request.get(new URL(protectedPath, BASE_URL).href, { maxRedirects: 0, timeout: 15_000 });
        record(
          response.status() === 403 || response.status() === 404,
          'Protected or development path must not be publicly readable: ' + protectedPath,
          { status: response.status() },
        );
        record(new URL(response.url()).origin === STAGING_ORIGIN, 'Protected-path request left the staging origin.', { path: protectedPath });
      } catch (error) {
        failures.push({ message: 'Unable to verify protected path: ' + protectedPath, detail: error.message });
      }
    }

    try {
      const missingPath = '/staging-qa-not-found-' + EXPECTED_SHA.slice(0, 12) + '.php';
      const missingResponse = await page.goto(new URL(missingPath, BASE_URL).href, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      record(Boolean(missingResponse) && missingResponse.status() === 404, 'Unknown staging route must return HTTP 404.', { status: missingResponse?.status() ?? null });
      record(new URL(page.url()).origin === STAGING_ORIGIN, 'Unknown-route handling left the staging origin.');
      record((await page.locator('main h1').first().textContent().catch(() => '') || '').includes('We couldn’t find that page'), 'Unknown staging route did not render the branded 404 heading.');
      const robots = (await page.locator('head meta[name="robots"]').getAttribute('content').catch(() => '')) || '';
      record(/noindex/i.test(robots), 'Unknown staging route must remain noindex.', { robots });
    } catch (error) {
      failures.push({ message: 'Unable to verify staging 404 behavior.', detail: error.message });
    }
  }

  const viewports = [
    { width: 390, height: 844 },
    { width: 1280, height: 800 },
  ];

  for (const viewport of viewports) {
    await page.setViewportSize(viewport);
    for (const route of ROUTES_TO_CHECK) {
      activePageErrors = [];
      const result = {
        route,
        viewport: viewport.width,
        status: null,
        finalOrigin: null,
        title: '',
        canonical: '',
        robots: '',
        h1Count: 0,
        imageCount: 0,
        imageFailures: 0,
        imagePolicySummary: null,
        horizontalOverflow: null,
        imageSources: [],
        preloads: [],
        pageErrors: [],
        screenshot: null,
      };

      try {
        const response = await page.goto(new URL(route, BASE_URL).href, { waitUntil: 'domcontentloaded', timeout: 25_000 });
        result.status = response?.status() ?? null;
        result.finalOrigin = new URL(page.url()).origin;
        record(Boolean(response) && response.status() < 400, 'Published route returned an error or no response.', { route, viewport: viewport.width, status: result.status });
        record(result.finalOrigin === STAGING_ORIGIN, 'Route redirected to a different origin.', { route, viewport: viewport.width, finalOrigin: result.finalOrigin, expectedOrigin: STAGING_ORIGIN });

        result.title = await page.title();
        record(/\S/.test(result.title), 'Page title is missing.', { route, viewport: viewport.width });
        const descriptions = await page.locator('head meta[name="description"]').evaluateAll(nodes => nodes.map(node => node.getAttribute('content') || ''));
        record(descriptions.length === 1 && Boolean(descriptions[0].trim()), 'Page must have exactly one non-empty meta description.', { route, viewport: viewport.width, count: descriptions.length });
        const canonicals = await page.locator('head link[rel="canonical"]').evaluateAll(nodes => nodes.map(node => node.getAttribute('href') || ''));
        result.canonical = canonicals[0] || '';
        record(canonicals.length === 1, 'Page must have exactly one canonical link.', { route, viewport: viewport.width, count: canonicals.length });
        if (result.canonical) {
          try {
            const canonical = new URL(result.canonical);
            record(canonical.protocol === 'https:', 'Canonical URL must use HTTPS.', { route, canonical: result.canonical });
            record(canonical.pathname === route && canonical.search === '' && canonical.hash === '', 'Canonical URL path must match the current sitemap route.', { route, canonical: result.canonical });
            if (EXTERNAL_STAGING) record(canonical.origin === STAGING_ORIGIN, 'Staging canonical points away from the staging origin.', { route, canonical: result.canonical, expectedOrigin: STAGING_ORIGIN });
          } catch {
            failures.push({ message: 'Canonical URL is invalid.', route, viewport: viewport.width, canonical: result.canonical });
          }
        }

        result.robots = (await page.locator('head meta[name="robots"]').getAttribute('content').catch(() => '')) || '';
        record(Boolean(result.robots), 'Robots meta tag is missing.', { route, viewport: viewport.width });
        if (EXTERNAL_STAGING) record(/noindex/i.test(result.robots), 'Staging page must be noindex.', { route, viewport: viewport.width, robots: result.robots });

        result.h1Count = await page.locator('main h1').count();
        record(result.h1Count === 1, 'Page must have exactly one main H1.', { route, viewport: viewport.width, count: result.h1Count });
        const schemaBlocks = await page.locator('script[type="application/ld+json"]').allTextContents();
        for (let i = 0; i < schemaBlocks.length; i += 1) {
          try { JSON.parse(schemaBlocks[i]); }
          catch { failures.push({ message: 'Invalid JSON-LD block.', route, viewport: viewport.width, block: i + 1 }); }
        }
        record(schemaBlocks.length > 0, 'Page must emit structured data.', { route, viewport: viewport.width });

        // Validate the guest journeys introduced by the merged PR #161.
        if (route === '/') {
          const homeBookingCases = [
            ['a[data-cta-id="booking-home-hero"]', 'booking-home-hero', 'home-hero'],
            ['#rooms-preview a[data-cta-id="booking-home-rooms"]', 'booking-home-rooms', 'home-rooms-section'],
            ['.final-cta a[data-cta-id="booking-home-final"]', 'booking-home-final', 'home-final-cta'],
          ];
          for (const [selector, ctaId, location] of homeBookingCases) {
            const link = page.locator(selector);
            const count = await link.count();
            record(count === 1, 'Homepage booking journey is missing or duplicated: ' + ctaId, { route, viewport: viewport.width, count });
            if (count === 1) {
              const href = await link.getAttribute('href') || '';
              let host = '';
              try { host = new URL(href, BASE_URL).hostname; } catch { /* recorded below */ }
              record(host === 'asiatech.in', 'Homepage booking CTA must retain the configured booking engine.', { route, viewport: viewport.width, ctaId, host });
              record(await link.getAttribute('data-cta-location') === location, 'Homepage booking CTA location metadata changed.', { route, viewport: viewport.width, ctaId });
              record(await link.getAttribute('target') === '_blank', 'Homepage booking CTA must open the configured engine in a new tab.', { route, viewport: viewport.width, ctaId });
              record((await link.getAttribute('rel') || '').includes('noopener'), 'Homepage booking CTA must retain noopener.', { route, viewport: viewport.width, ctaId });
            }
          }
        }

        if (route === '/direction.php') {
          const driverCard = page.locator('.arrival-driver-card');
          const driverCount = await driverCard.count();
          record(driverCount === 1, 'Directions page must render one driver-ready arrival card.', { route, viewport: viewport.width, count: driverCount });
          if (driverCount === 1) {
            const driverCopy = await driverCard.innerText();
            for (const fact of [
              'Fort Kotari Para, Inside Jaisalmer Fort, Jaisalmer, Rajasthan 345001',
              'Vehicles cannot enter Jaisalmer Fort',
              'Fort Parking',
            ]) {
              record(driverCopy.includes(fact), 'Directions driver card is missing an approved arrival fact.', { route, viewport: viewport.width, fact });
            }
            const copyButton = driverCard.locator('button[data-arrival-copy]');
            record(await copyButton.isVisible().catch(() => false), 'Directions driver-copy action should be available when JavaScript is enabled.', { route, viewport: viewport.width });
            const copyPayload = await copyButton.getAttribute('data-arrival-copy').catch(() => '') || '';
            record(copyPayload.includes('300 metres (5–6 minutes)'), 'Driver-copy payload is missing the verified final-walk guidance.', { route, viewport: viewport.width });
            record(copyPayload.includes('https://maps.app.goo.gl/nAbRiZhv78FSbju48'), 'Driver-copy payload is missing the exact approved hotel pin.', { route, viewport: viewport.width });
          }

          const arrivalBooking = page.locator('.arrival-page .final-cta a[data-cta-id="booking-arrival-final"]');
          const arrivalBookingCount = await arrivalBooking.count();
          record(arrivalBookingCount === 1, 'Directions page must retain one measured direct-booking CTA.', { route, viewport: viewport.width, count: arrivalBookingCount });
          if (arrivalBookingCount === 1) {
            const href = await arrivalBooking.getAttribute('href') || '';
            let host = '';
            try { host = new URL(href, BASE_URL).hostname; } catch { /* recorded below */ }
            record(host === 'asiatech.in', 'Directions booking CTA must retain the configured booking engine.', { route, viewport: viewport.width, host });
            record(await arrivalBooking.getAttribute('data-cta-location') === 'arrival-final-cta', 'Directions booking CTA location metadata changed.', { route, viewport: viewport.width });
          }
        }


        await page.evaluate(() => window.scrollTo(0, 0));
        await page.waitForTimeout(50);
        // Snapshot each image's original request policy before scroll/decode diagnostics mutate it.
        await page.evaluate(snapshotKey => {
          const snapshots = new WeakMap();
          for (const img of document.images) {
            const rect = img.getBoundingClientRect();
            const style = getComputedStyle(img);
            const visibleAtInitialViewport = rect.width > 0 && rect.height > 0
              && rect.right > 0 && rect.left < window.innerWidth
              && rect.bottom > 0 && rect.top < window.innerHeight
              && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0;
            snapshots.set(img, {
              captured: true,
              visibleAtInitialViewport,
              loadingAttribute: img.getAttribute('loading'),
              loadingProperty: img.loading || 'eager',
              fetchPriorityAttribute: img.getAttribute('fetchpriority'),
              fetchPriorityProperty: img.fetchPriority || 'auto',
              decodingAttribute: img.getAttribute('decoding'),
              decodingProperty: img.decoding || 'auto',
            });
          }
          window[snapshotKey] = snapshots;
        }, IMAGE_POLICY_SNAPSHOT_KEY);
        const pageHeight = await page.evaluate(() => document.documentElement.scrollHeight);
        for (let top = 0; top < pageHeight; top += 700) {
          await page.evaluate(value => window.scrollTo(0, value), top);
          await page.waitForTimeout(30);
        }
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.waitForTimeout(100);

        // Include images added by scroll-triggered content before the audit promotes request priority.
        await page.evaluate(snapshotKey => {
          const snapshots = window[snapshotKey] instanceof WeakMap ? window[snapshotKey] : new WeakMap();
          for (const img of document.images) {
            if (snapshots.has(img)) continue;
            snapshots.set(img, {
              captured: true,
              visibleAtInitialViewport: false,
              loadingAttribute: img.getAttribute('loading'),
              loadingProperty: img.loading || 'eager',
              fetchPriorityAttribute: img.getAttribute('fetchpriority'),
              fetchPriorityProperty: img.fetchPriority || 'auto',
              decodingAttribute: img.getAttribute('decoding'),
              decodingProperty: img.decoding || 'auto',
            });
          }
          window[snapshotKey] = snapshots;
        }, IMAGE_POLICY_SNAPSHOT_KEY);

        // Promote lazy images to eager and wait for decoding before inspecting natural dimensions.
        await page.locator('img').evaluateAll(async nodes => {
          await Promise.all(nodes.map(async img => {
            img.loading = 'eager';
            try {
              await Promise.race([
                img.decode(),
                new Promise(resolve => setTimeout(resolve, 2500)),
              ]);
            } catch {
              // The audit below records an actual failed image without aborting the route inventory.
            }
          }));
        });

        result.horizontalOverflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1);
        record(result.horizontalOverflow === false, 'Horizontal overflow at responsive viewport.', { route, viewport: viewport.width });

        const images = await page.locator('img').evaluateAll((nodes, snapshotKey) => nodes.map((img, index) => {
          const style = getComputedStyle(img);
          const currentSrc = img.currentSrc || img.src || '';
          const pictureSources = Array.from(img.closest('picture')?.querySelectorAll('source[srcset]') || []);
          const activeSource = pictureSources.find(source => source.srcset.split(',').some(candidate => {
            const candidateUrl = candidate.trim().split(/\s+/)[0];
            try { return new URL(candidateUrl, document.baseURI).href === currentSrc; }
            catch { return false; }
          }));
          const rect = img.getBoundingClientRect();
          const policySnapshot = window[snapshotKey]?.get(img) || null;
          return {
            index: index + 1,
            policySnapshot,
            isRooftopHero: Boolean(img.closest('.page-hero-media')),
            isRooftopEditorial: Boolean(img.closest('figure.roof-banner')),
            src: img.getAttribute('src') || img.src || '',
            srcset: activeSource?.getAttribute('srcset') || img.getAttribute('srcset') || '',
            sizes: activeSource?.getAttribute('sizes') || img.getAttribute('sizes') || '',
            currentSrc,
            altPresent: img.hasAttribute('alt'),
            width: Number(img.getAttribute('width')),
            height: Number(img.getAttribute('height')),
            naturalWidth: img.naturalWidth,
            naturalHeight: img.naturalHeight,
            renderedWidth: Math.round(rect.width * 100) / 100,
            renderedHeight: Math.round(rect.height * 100) / 100,
            devicePixelRatio: window.devicePixelRatio || 1,
            // Loading/fetch/decode policy is normalized from the pre-mutation snapshot below.
            visibleAtInitialViewport: false,
            loaded: img.complete && img.naturalWidth > 0 && img.naturalHeight > 0,
            rendered: style.display !== 'none' && style.visibility !== 'hidden' && img.getClientRects().length > 0,
          };
        }), IMAGE_POLICY_SNAPSHOT_KEY);

        for (const img of images) {
          const policy = normalizeImagePolicySnapshot(img.policySnapshot);
          Object.assign(img, policy);
          img.placement = img.isRooftopHero ? 'rooftop-hero' : img.isRooftopEditorial ? 'rooftop-editorial' : null;
          delete img.policySnapshot;
          delete img.isRooftopHero;
          delete img.isRooftopEditorial;
          record(img.policyCapturedBeforeAuditMutation, 'Image request policy was not captured before staging-audit mutations.', {
            route, viewport: viewport.width, src: img.src,
          });
        }

        result.imagePolicySummary = {
          captureTiming: 'pre-audit-mutation',
          capturedImageCount: images.filter(img => img.policyCapturedBeforeAuditMutation).length,
          lazyImageCount: images.filter(img => img.loading === 'lazy').length,
          eagerImageCount: images.filter(img => img.loading === 'eager').length,
          highPriorityImageCount: images.filter(img => img.fetchPriority === 'high').length,
        };

        if (route === '/roof.php') {
          const rooftopHero = images.find(img => img.placement === 'rooftop-hero');
          const rooftopEditorial = images.find(img => img.placement === 'rooftop-editorial');
          record(Boolean(rooftopHero), 'Rooftop hero is missing from the staging image-policy audit.', { route, viewport: viewport.width });
          record(Boolean(rooftopHero && rooftopHero.loading === 'eager' && rooftopHero.fetchPriority === 'high'),
            'Rooftop hero must retain eager loading and high fetch priority.',
            { route, viewport: viewport.width, observed: rooftopHero || null });
          record(Boolean(rooftopEditorial && rooftopEditorial.loading === 'lazy' && rooftopEditorial.decoding === 'async'),
            'Rooftop editorial photography must retain lazy loading and async decoding.',
            { route, viewport: viewport.width, observed: rooftopEditorial || null });
        }

        result.imageCount = images.length;
        result.imageSources = images;
        result.preloads = await page.locator('link[rel="preload"][as="image"]').evaluateAll(nodes => nodes.map(link => ({
          href: link.href || '',
          imagesrcset: link.getAttribute('imagesrcset') || '',
          imagesizes: link.getAttribute('imagesizes') || '',
          media: link.getAttribute('media') || '',
          active: !link.media || window.matchMedia(link.media).matches,
          fetchpriority: link.getAttribute('fetchpriority') || '',
        })));
        for (const img of images) {
          const selectedWidth = selectedSrcsetWidth(img.srcset, img.currentSrc, BASE_URL);
          const targetPixelWidth = img.renderedWidth * img.devicePixelRatio;
          const sourcePixelCoverage = selectedWidth && targetPixelWidth > 0 ? selectedWidth / targetPixelWidth : null;
          img.selectedSrcsetWidth = selectedWidth;
          img.sourcePixelCoverage = sourcePixelCoverage;
          if (sourcePixelCoverage !== null) {
            record(sourcePixelCoverage >= 0.9, 'Responsive image candidate is under-resolved for its rendered slot.', {
              route, viewport: viewport.width, src: img.currentSrc, selectedWidth, targetPixelWidth,
              sourcePixelCoverage: Math.round(sourcePixelCoverage * 1000) / 1000,
            });
          }
          if (img.fetchPriority === 'high') {
            record(img.visibleAtInitialViewport, 'High-priority image is outside the initial viewport.', {
              route, viewport: viewport.width, src: img.currentSrc,
            });
          }
          if (EXTERNAL_STAGING && img.visibleAtInitialViewport && img.currentSrc) {
            try {
              if (new URL(img.currentSrc).origin === STAGING_ORIGIN) selectedImageUrls.add(img.currentSrc);
            } catch { /* Invalid image URLs are covered by the existing load assertion. */ }
          }
        }
        const activePreloads = result.preloads.filter(preload => preload.active);
        const preloadKeys = activePreloads.map(preload => [preload.href, preload.imagesrcset, preload.imagesizes].join('|'));
        record(new Set(preloadKeys).size === preloadKeys.length, 'Duplicate active image preloads were emitted.', { route, viewport: viewport.width, preloads: activePreloads });
        for (const preload of activePreloads) {
          const preloadSet = normalizedSrcset(preload.imagesrcset, BASE_URL);
          const matchingImages = images.filter(img => {
            if (preload.imagesrcset) {
              return Boolean(img.srcset)
                && preloadSet !== ''
                && preloadSet === normalizedSrcset(img.srcset, BASE_URL)
                && preload.imagesizes === img.sizes;
            }
            try {
              const preloadPath = new URL(preload.href, BASE_URL).pathname;
              return [img.currentSrc, img.src].some(source => Boolean(source) && new URL(source, BASE_URL).pathname === preloadPath);
            } catch {
              return false;
            }
          });
          record(matchingImages.length > 0, 'Image preload does not match a rendered candidate and sizes hint.', {
            route, viewport: viewport.width, preload,
          });
        }
        for (const img of images) {
          const problems = [];
          if (!img.altPresent) problems.push('missing-alt');
          if (!(img.width > 0 && img.height > 0)) problems.push('missing-dimensions');
          if (img.rendered && !img.loaded) problems.push('visible-image-failed-to-load');
          if (problems.length) {
            result.imageFailures += 1;
            record(false, 'Image QA failed: ' + problems.join(', '), { route, viewport: viewport.width, src: img.src, index: img.index });
          }
        }

        const screenshotName = safeName(route) + '-' + viewport.width + '.jpg';
        const screenshotPath = path.join(screenshotsDir, screenshotName);
        await page.screenshot({ path: screenshotPath, type: 'jpeg', quality: 60, fullPage: true, animations: 'disabled' });
        result.screenshot = 'screenshots/' + screenshotName;
        screenshots.push({ route, viewport: viewport.width, path: result.screenshot });
        result.pageErrors = [...activePageErrors];
        for (const message of result.pageErrors) record(false, 'Uncaught browser error: ' + message, { route, viewport: viewport.width });
      } catch (error) {
        failures.push({ message: 'Route QA could not complete.', route, viewport: viewport.width, detail: error.message });
        result.pageErrors = [...activePageErrors];
      }
      routeResults.push(result);
    }
  }

  if (EXTERNAL_STAGING) {
    const imageUrls = [...selectedImageUrls].sort();
    for (let offset = 0; offset < imageUrls.length; offset += 6) {
      const batch = imageUrls.slice(offset, offset + 6);
      const observations = await Promise.all(batch.map(async url => {
        try {
          const response = await request.head(url, { maxRedirects: 0, timeout: 15_000 });
          const headers = response.headers();
          const maxAgeSeconds = cacheMaxAgeSeconds(headers);
          const contentType = (headers['content-type'] || '').split(';')[0].trim().toLowerCase();
          const contentLength = Number(headers['content-length']);
          const observation = {
            url, status: response.status(), contentType: contentType || null,
            contentLengthBytes: Number.isFinite(contentLength) && contentLength > 0 ? contentLength : null,
            cacheControl: headers['cache-control'] || null,
            expires: headers.expires || null,
            effectiveMaxAgeSeconds: maxAgeSeconds,
          };
          record(response.status() === 200, 'Selected staging image did not return HTTP 200 to HEAD.', { url, status: response.status() });
          record(contentType.startsWith('image/'), 'Selected staging image has a non-image Content-Type.', { url, contentType });
          record(maxAgeSeconds !== null, 'Selected staging image has no verifiable cache lifetime.', { url, cacheControl: observation.cacheControl, expires: observation.expires });
          record(maxAgeSeconds !== null && maxAgeSeconds >= MIN_IMAGE_CACHE_AGE_SECONDS,
            'Selected staging image cache lifetime is below the seven-day minimum.',
            { url, effectiveMaxAgeSeconds: maxAgeSeconds, minimumSeconds: MIN_IMAGE_CACHE_AGE_SECONDS });
          return observation;
        } catch (error) {
          record(false, 'Unable to inspect selected staging image cache headers.', { url, detail: error.message });
          return { url, status: null, error: error.message };
        }
      }));
      imageCacheAudit.push(...observations);
    }
  }

  const report = {
    generatedAt: new Date().toISOString(),
    mode: EXTERNAL_STAGING ? 'external-staging' : 'local-dry-run',
    stagingOrigin: STAGING_ORIGIN,
    stagedPackageCommit: EXTERNAL_STAGING ? EXPECTED_SHA : null,
    workflowCheckoutCommit: EXTERNAL_STAGING ? CHECKED_OUT_SHA : null,
    deploymentPerformed: false,
    enquirySubmitted: false,
    releaseProvenance,
    imageDelivery: {
      firstViewportFirstPartyImageCount: selectedImageUrls.size,
      cacheProbeCount: imageCacheAudit.length,
      minimumCacheAgeSeconds: MIN_IMAGE_CACHE_AGE_SECONDS,
      cacheProbes: imageCacheAudit,
    },
    routeCount: ROUTES_TO_CHECK.length,
    viewportWidths: viewports.map(viewport => viewport.width),
    failures,
    results: routeResults,
  };
  const reportDir = path.join(process.cwd(), 'artifacts', 'staging-qa');
  fs.mkdirSync(reportDir, { recursive: true });
  fs.writeFileSync(path.join(reportDir, 'staging-qa-report.json'), JSON.stringify(report, null, 2) + '\n');
  const indexHtml = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Staging QA screenshot review</title><style>body{font-family:system-ui,sans-serif;margin:24px;line-height:1.5}main{max-width:1440px;margin:auto}h1{font-size:1.6rem}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}.item{border:1px solid #ccc;padding:12px}.item img{width:100%;height:auto;display:block}.meta{font-size:.9rem;overflow-wrap:anywhere}</style></head><body><main><h1>Staging QA screenshots</h1><p>Mode: ' + escapeHtml(report.mode) + '; routes: ' + ROUTES_TO_CHECK.length + '; viewports: 390px and 1280px; failures recorded: ' + failures.length + '.</p><p>Deployment performed: no. Enquiry submitted: no.</p><div class="grid">' + screenshots.map(item => '<div class="item"><p class="meta">' + escapeHtml(item.route) + ' · ' + item.viewport + 'px</p><a href="' + escapeHtml(item.path) + '"><img src="' + escapeHtml(item.path) + '" loading="lazy" alt="Full-page screenshot for ' + escapeHtml(item.route) + ' at ' + item.viewport + ' pixels"></a></div>').join('') + '</div></main></body></html>';
  fs.writeFileSync(path.join(reportDir, 'index.html'), indexHtml);

  await testInfo.attach('staging-qa-report', {
    path: path.join(reportDir, 'staging-qa-report.json'),
    contentType: 'application/json',
  });
  await testInfo.attach('staging-qa-screenshot-index', {
    path: path.join(reportDir, 'index.html'),
    contentType: 'text/html',
  });

  expect(failures, 'Staging QA found failures; inspect the attached JSON report and screenshot artifact.').toEqual([]);
});
