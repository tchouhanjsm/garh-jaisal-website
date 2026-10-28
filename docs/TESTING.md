# Testing and engineering review

## Required checks

From the repository root:

```bash
php scripts/check-site.php --strict-images
php scripts/generate-sitemap.php --check
php tests/php/editorial-release-readiness.php
php scripts/check-experience-publication.php
php scripts/check-launch-hardening.php
php scripts/check-runtime-config.php
php tests/php/at-a-glance-contract.php
php tests/php/contact-channel-contract.php
php tests/php/conversion-copy-contract.php
php tests/php/custom-itinerary-contract.php
php tests/php/enquiry-context-contract.php
php tests/php/experience-detail-navigation-contract.php
php tests/php/experience-discovery.php
php tests/php/experience-gallery-assets.php
php tests/php/gallery-curation.php
php tests/php/image-dimension-cache-contract.php
php tests/php/lead-conversion-contract.php
php tests/php/lead-storage-validation.php
php tests/php/preload-contract.php
php tests/php/runtime-config-validation.php
php tests/php/staging-noindex.php
npm install
npm run test:e2e
node tests/node/content-pack.test.js
```

CI also runs PHP lint, responsive-image validation/review, Content Studio integration checks, deployment package preflight and non-deploying Staging QA. Follow current `.github/workflows/*.yml` if a command is added or moved.

## Browser and visual evidence

- Playwright uses local routes at `http://127.0.0.1:8080`; follow `.github/workflows/browser-e2e.yml` for server setup.
- Required visual widths: 320, 390, 768, 1280 and 1440px.
- Inspect screenshot/page-sheet artifacts at full size.
- Review Home, Rooms, Packages, a representative experience detail, Dining, Directions and Contact.
- Check hero focal point, visible primary action, fixed docks, heading wraps, forms/FAQs, image loading and horizontal overflow.
- Measure selected hero/image bytes and currentSrc; WebP extension alone is not a performance measure.
- Use exact PR-head CI evidence. Cancelled or stale runs are not passes.
- State anything not run. Automated tests do not validate real cPanel, mail or CAPTCHA delivery.

- Journal discovery checks cover client-side text search and topic filtering, accessible result announcements, no-match recovery, Fort-guide first-card stability, no-JavaScript visibility and mobile control overflow. These filters do not change the canonical URL or public route inventory.
- First-impression checks cover representative high-intent routes, title/H1/canonical presence, home booking CTA, hero image load state, responsive overflow and navigation timing evidence. Local timing is diagnostic only; sub-three-second performance must be verified with field data or a production-like environment and Core Web Vitals.

## Visual asset and typography evidence

- Browser E2E writes `artifacts/image-accessibility/visual-asset-audit-<project>.json` for the eight key page families. The report includes image source/currentSrc, intrinsic and rendered dimensions, alt-attribute presence, visible load failures, font stacks/readiness and resource-timing transfer data when available.
- Review image subject, composition, color, authenticity and crop manually against screenshot artifacts before changing source photography. An empty alt is valid for a deliberately decorative image; a missing alt attribute is not.
- Browser timing is diagnostic. Do not claim field Core Web Vitals or a real-user three-second result from CI.

## Four-perspective review

1. **Full-stack:** PHP compatibility, escaping, safe inputs/output, secret boundaries, error handling, routes and dependencies.
2. **SEO/content:** title/description, heading hierarchy, URL/canonical, internal links, JSON-LD and sitemap parity; ground claims in `docs/OWNER-FACTS.md`.
3. **Debugger:** root-cause analysis, regression coverage and no weakening valid assertions to make CI pass.
4. **UI/UX/accessibility:** hierarchy, mobile/desktop layout, keyboard/focus, contrast, alt text, reduced motion, touch targets, recovery states and image crops.

A role that is not directly affected still records a concrete double-check rather than assuming N/A.

## Merge/release gate

- [ ] The PR is a coherent user-visible slice, not an isolated cosmetic tweak.
- [ ] Public URLs, booking/contact destinations and business facts are preserved.
- [ ] Required exact-head checks and screenshots are complete.
- [ ] Owner decisions and staging risks are explicit.
- [ ] Owner approves the merge; production release requires separate owner approval.

## Compact-phone homepage first-screen contract

- At widths up to 360px, the homepage story stylesheet owns the compact hero rules; generic selectors in earlier stylesheets must not silently lose to the later, more specific theme rules.
- The 320px facts strip uses two columns, with Explore spanning the second row. Keep Stay, Dine and Explore above the fixed mobile action dock.
- Keep first-screen assertions strict. On failure, report fact-bottom, dock-top and hero-height values rather than weakening the expectation.
- Review screenshots at 320px, 390px, 768px, 1280px and 1440px. Human visual review remains required for final crop/composition approval.

## Responsive hero and smallest-phone regressions

- The Gallery photo hero is full-bleed. Its image sizes attribute must describe 100vw, and its high-priority preload must use the same imagesrcset and imagesizes values as the rendered image.
- At desktop width, the Gallery hero test asserts that the largest supported responsive candidate is selected and loaded. This guards against a successful page request with an undersized source stretched across a full-width hero.
- At 320px, the Home quick-facts strip uses two columns, with the third fact spanning both columns. Browser checks verify readable item widths and no document overflow.
- The primary Home hero eyebrow has a 13px minimum at all configured viewport widths; the strict assertion targets `.hero-content > .eyebrow` so later section labels cannot satisfy it accidentally.
- Homepage discovery cards must respect `prefers-reduced-motion: reduce`: Playwright emulates the setting and checks both computed transition duration and hover transform.
- Preserve the original approved photographs. Change resolution/candidate selection only when browser evidence shows the declared slot and selected resource disagree.


## Gallery lightbox keyboard and image readiness

- Open a photo with the keyboard, move with Left/Right arrows, verify “Photo N of M” is announced, and confirm Previous/Next buttons still work.
- Confirm arrow navigation wraps at the ends, the selected full-size source resolves, Escape closes the native dialog, and focus returns to the opening thumbnail. If a filter hides the trigger, focus should return to the selected filter.
- Neighboring full-size images may be prewarmed only after a visitor opens the lightbox; do not front-load every original image.
- Verify the curated Gallery remains usable without JavaScript and preserves the image-to-source mapping.

## Shared reduced-motion preference

- css/accessibility.css is loaded after page-specific CSS from include/page-head.php so a visitor preference reliably overrides route-level transitions.
- With Playwright reduced motion set to reduce, verify shared controls have no CSS transition and the Gallery image does not scale on hover. Smooth scrolling and decorative animations must also be disabled.
- Keep visible keyboard focus and the existing selected/hover color contrast; motion reduction must not hide content or alter booking/contact destinations.
- The PHP source contract runs under both CI PHP versions; browser behavior is separately tested in Chromium on the exact PR head.


## Browser E2E follow-up — stylesheet contract and image decoding

- `sitewide-visual-system.spec.js` treats `css/accessibility.css` as the last preference layer, not as a route stylesheet expected to load before `tokens.css`. It still asserts that every real route stylesheet precedes shared tokens and that the preference layer follows all other CSS.
- `image-decoding-policy.spec.js` checks core public page families for lazy-image `decoding="async"`, explicit `alt` attributes and absence of high-priority lazy images. The JSON report is browser evidence from local PHP/Chromium.
- Browser E2E run 742 failed because the existing test grouped the newly added final preference layer with route styles. Do not reorder production styles or remove the accessibility layer to make the old assertion pass; update the classification and retain explicit final-order coverage.
- CI image decode hints are implementation conventions, not a claim of a particular LCP/Core Web Vitals gain. Review selected sources and screenshots for visual fidelity.


## Accessible social preview metadata

- Every route in the committed sitemap is covered by the Browser E2E social-image contract. A newly published route automatically joins this coverage.
- The shared default facade image has a reviewed description; an unrelated custom image must supply a source-specific description instead of inheriting that fallback.
- When Open Graph and Twitter/X use the same image, their descriptions must match. Distinct images require their own explicit text.
- Published Content Studio experience, editorial and landing-page records must provide non-empty hero descriptions before the shared renderer accepts them.
- Run `php tests/php/social-image-alt-contract.php` and `npx playwright test tests/e2e/social-image-metadata.spec.js --project=desktop-1280`.
- The frozen legacy head snapshot intentionally excludes only the two newly contracted image-alt tags; the sitemap-derived social metadata test validates those fields on every live public route.


## Search snippet quality guard

- The sitemap-wide SEO browser test uses an internal editorial target of 30–60 characters for page titles and 120–160 characters for meta descriptions. These are project QA bands, not guaranteed search-engine display limits or ranking factors.
- The test checks rendered values from every sitemap route, alongside canonical-path and indexability checks. Keep titles and descriptions accurate, distinct and aligned with actual page content.
- The rendered audit previously identified overlong descriptions on Home, Car & City Tours, the Fort guided walk, Desert Safari, guided city tours, the restaurant, and the honeymoon guide, plus a long Desert Safari title. This batch trims those and pages already at the upper edge of the description target.
- The frozen head snapshot fixture is updated with the approved copy so further unintended title/description changes remain detectable.


## Contextual planning-link regression

- `tests/e2e/seo.spec.js` checks the contextual planning section on all 11 configured high-intent routes.
- The contract expects one visible section, one heading, three distinct links and same-site PHP destinations; the shared renderer also validates and escapes the page-provided labels, descriptions and paths.
- Review Browser E2E screenshots and failure diagnostics on the exact PR head. Passing local-route checks does not establish live Google indexing, ranking changes or production performance.
