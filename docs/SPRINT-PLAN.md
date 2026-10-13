# SPRINT PLAN (fast lane)

Read `docs/BRAIN.md` first. Status lives in `docs/PROJECT-LOG.md`. Questions for the owner live in `docs/OWNER-FACTS.md`.

Goal: finish the redesign in about a week by making every page cheap to build, then redesigning the remaining pages in three large slices.

| Sprint | Slice | Status |
|---|---|---|
| 1 | Foundation: page shell, UI partials, dead-asset removal, screenshot sheet | ✓ merged through PR #48; legacy icon-font assets are removed in a separate reference-guarded cleanup; hosted Google Fonts remain |
| 2A | Dining (`roof.php`, `restaurant.php`), Contact, Directions | ✓ page group and reciprocal Dining/responsive fixes merged through PRs #49–#50 |
| 2B | Gallery, About, Blog and guides | ✓ merged in PR #50; visual review artifact available |
| 2C | Tour detail template, `privacy.php`, `booking-policy.php` | ◐ responsive detail protections and draft shells merged in PR #50; policy pages remain noindex/off-nav until owner supplies terms |
| 3A | Launch hardening: `.htaccess`, form/email resilience, custom 404 | ✓ PR #51 merged; exact-main Site Checks and Browser E2E passed |
| 3B | Image compression, alt text, sitemap dates, cPanel handoff, release tag | ◐ PR #51 and cleanup PRs #52–#53 merged; image variants remain review-only, owner staging/image approval pending, no tag/deployment |
| 4 | Repeatable cPanel staging package and safe release preflight | ✓ merged in PR #54; package/checksum artifact generated on main, owner staging sign-off remains required |
| 5 | Staging isolation, non-production noindex and repeatable staging QA | ✓ merged in PR #55; local QA dry run passes, real staging/cPanel and CAPTCHA/mail checks remain owner-gated |
| 6 | Direct-stay guide discoverability | ◐ add the existing Jaisalmer Fort stay guide from Home and Rooms; verify both entry points in Browser E2E |

---

## SPRINT 1: foundation rollout, branch `feature/sprint-1-page-head-migration`

Progress update (2026-10-13): PR [#42](https://github.com/tchouhanjsm/garh-jaisal-website/pull/42) merged the frozen pre-refactor head fixture and screenshot-sheet CI support. Do not regenerate the fixture during migration. The next page-head change intentionally adds the shared Hotel/WebSite nodes on migrated pages; the snapshot test excludes only those shared graph roots from the page-specific comparison and separately asserts their IDs, types and approved values.

Progress update (2026-10-13): PR [#43](https://github.com/tchouhanjsm/garh-jaisal-website/pull/43) migrated the first six pages to the shared head. PR [#44](https://github.com/tchouhanjsm/garh-jaisal-website/pull/44) then migrated the second planned group (`pack.php`, `desert-safari.php`, `car-tours.php`, `guided-jaisalmer-tour.php`, `camping.php`, `car-rental.php`, `city-tour.php`, `book-rooms-in-jaisalmer.php`), preserving the frozen fixture and both GA4 IDs.

Progress update (2026-10-13): PR #45 completes group 3 (`roof.php`, `restaurant.php`, `blog.php` and the three guides) and the shared experience renderer. The fixture remains unchanged; the shared-schema regression now covers all sitemap routes, and the source checker requires all public routes to use `page-head.php` directly or through the renderer.

Progress update (2026-10-13): PR #47 is merged. PR #48 combines the 12 reference-verified unused CSS files with evidence-led Contact fixes: the visible anti-spam honeypot is moved off-screen, fixed controls no longer cover the Contact facts/form, and the existing configured WhatsApp action remains available inline. The font files are reserved for a separate revertible commit after a final retained-CSS scan. The screenshot-sheet workflow remains provided by PR #41.

Make 5 commits in this order. Each commit must leave CI green. If the connector cannot run PHP or a browser, rely on CI after each push and keep commits small.

### Commit 1: head snapshot safety net (`test:`)
Before touching any page, record what every public page emits today.
- Add `tests/e2e/head-snapshot.spec.js`. For every sitemap route (desktop-1280 project only) read: `<title>`, meta description, canonical, robots, all `og:*` and `twitter:*` meta, the list of JSON-LD `@type` values, the `gtag.js` script count, and the `gtag('config', ...)` IDs.
- Store the result as `tests/fixtures/head-snapshot.json` (generate with `UPDATE_HEAD_SNAPSHOT=1 npx playwright test tests/e2e/head-snapshot.spec.js --project=desktop-1280`; the test compares when the variable is unset).
- Also snapshot the drafts and the hidden page (`longewala-tour.php`, `rajasthan-tours.php`, `explore-the-desert.php`) for robots and title.
- Rule: after Commit 2, this test must pass **without regenerating the fixture**. Any difference is a regression unless the PR explains it.

### Commit 2: shared head and analytics (`feat:`)
Create `include/page-head.php`. Contract (document it in a header comment):
```php
$page = [
  'title' => '...',            // required
  'description' => '...',      // required
  'path' => '/room.php',       // required, site-relative; canonical = site_url + path ('/' for home)
  'robots' => 'index,follow',  // optional; drafts and hidden pages pass noindex,follow
  'og_image' => '/img/v2/facade.jpg', // optional default
  'css' => ['css/packages.css'],      // extra stylesheets after css/garh-v2.css
  'schema' => [ /* extra JSON-LD nodes as PHP arrays */ ],
  'preload' => [ /* optional <link rel=preload> hero image: href, imagesrcset, imagesizes */ ],
];
require __DIR__ . '/include/page-head.php';   // emits everything from <meta charset> to the last <link>/<script> in <head>
```
It must render, with `htmlspecialchars` on every value:
- charset, viewport, title, description, robots, absolute HTTPS canonical, `og:*`, `twitter:*`, favicon, font preconnects and the existing Google Fonts stylesheet, `css/garh-v2.css`, then `$page['css']`.
- **Analytics: keep both GA4 IDs.** Read `analytics_ids` from `include/site-config.php`. Load `gtag.js` once using the first ID, call `gtag('config', id)` once per ID. No ID is hard-coded in any page after this commit.
- JSON-LD: shared Hotel and WebSite nodes from a helper (`include/schema.php`, functions `gj_schema_hotel()`, `gj_schema_website()`) merged with `$page['schema']`, encoded with `json_encode(..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT)` inside one `application/ld+json` script. Keep each page's existing node types and values exactly (copy them into `$page['schema']`).
Then migrate pages in groups, running the snapshot test after each group:
1. `index.php`, `about.php`, `room.php`, `contact.php`, `direction.php`, `gallery.php`
2. `pack.php`, `desert-safari.php`, `car-tours.php`, `guided-jaisalmer-tour.php`, `camping.php`, `car-rental.php`, `city-tour.php`, `book-rooms-in-jaisalmer.php`
3. `roof.php`, `restaurant.php`, `blog.php`, `jaisalmer-fort-guide.php`, `desert-safari-guide.php`, `jaisalmer-2-day-itinerary.php`
4. `include/experience-page.php` (drafts and hidden pages keep `noindex,follow`)
Do not change `thank-you.php`, `mail.php`, `tariff.php` heads except to keep them `noindex`.
Add a check to `scripts/check-site.php`: a public page must call `page-head.php` (warning first, error once migration is complete).

### Commit 3: UI partials (`feat:`)
Create `include/ui/hero.php`, `section-head.php`, `cta-band.php`, `faq.php`. Each takes one array, escapes all output, and emits the **same class names the CSS already uses** (read `css/garh-v2.css` first; do not rename classes).
- `cta-band`: eyebrow, title, text, primary button (label, href, `target`), secondary button.
- `faq`: list of question and answer pairs, using the existing `<details class="faq-item">` markup from `include/experience-page.php`.
- `hero`: variant (`home` | `page` | `photo`), eyebrow, h1, lead, actions, image (src, alt, width, height), breadcrumb items.
- `section-head`: eyebrow, h2, intro text.
Adopt them only where the rendered HTML is equivalent: the final call-to-action on Home, Rooms, Directions, Gallery, About and Blog, and the FAQ in `direction.php` and `include/experience-page.php`. Compare with the screenshot sheet (Commit 5). Do not redesign pages in this PR.

### Commit 4: remove dead assets (`chore:`)

Progress (2026-10-13): PR #47 removed the 12 unreferenced JavaScript bundles and three retired includes, and removed the stale checker exclusions. PR #48 removes the following 12 legacy CSS files after rechecking source references; this is bundled with the verified Contact layout/anti-spam fixes and viewport regression tests so the PR stays below the 25-file review limit.

- JS (removed in PR #47): `bootstrap.min.js countdown.js jquery-3.2.0.min.js jquery-ui.js jquery.counterup.min.js jquery.fancybox.min.js jquery.magnific-popup.min.js jquery.meanmenu.js jquery.scrollUp.js jquery.waypoints.min.js owl.carousel.min.js theme.js` (keep `garh-v2.js`, `restaurant.js`).
- CSS (this PR): `animate.css bootstrap.min.css directions.css font-awesome.min.css icofont.css jquery-ui.css jquery.fancybox.min.css magnific-popup.css meanmenu.css owl.carousel.min.css responsive.css style.css`.
- Includes (removed in PR #47): `include/header.php`, `include/footer.php`, `include/header-v2.backup.php` after reference verification.
- Fonts: delete only files no retained CSS references (`grep -rF fonts/ css`). Do the font deletion as its own commit so it can be reverted alone.
- Contact regressions included in the CSS cleanup slice: hide `.hp-field` off-screen without dropping it from the form, keep Call/WhatsApp/Book actions from overlaying Contact content, and test desktop/mobile facts/form alignment. Preserve the configured WhatsApp path inline.
- Gate: all Playwright suites pass; `check-site.php` shows no new broken references; page-sheet artifact on the exact head.

### Commit 5: page screenshot sheet (`test:` and `ci:`)
- Add `tests/e2e/page-sheet.spec.js` (runs once, desktop-1280 project, but opens its own contexts at **390x844** and **1280x800**). For every sitemap route: load, scroll the full height in 300px steps waiting about 120 ms per step, wait 800 ms (**lazy-loaded images stay blank in full-page screenshots otherwise**), then save a full-page JPEG (quality 60) to `artifacts/page-sheet/<route>-<width>.jpg`.
- Write `artifacts/page-sheet/index.html`: a simple grid, each route with its 390 and 1280 thumbnails linking to the full image.
- In `.github/workflows/browser-e2e.yml` upload `artifacts/page-sheet/` as artifact `page-sheet` (retention 7 days). `artifacts/` is already git-ignored.
- The PR description names the artifact and the 3 pages to open first.

### Sprint 1 acceptance (all must be true)
- `head-snapshot` passes against the fixture generated **before** Commit 2.
- Every migrated page shows both GA4 IDs exactly once and one `gtag.js` request.
- `php scripts/check-site.php --strict-images` 0 errors; `php scripts/generate-sitemap.php --check` pass; all existing Playwright suites pass on 320, 390, 1280 and 1440.
- Repo is smaller (dead JS, CSS, includes removed) and no page load requests a deleted file (404 asset check passes).
- Screenshot sheet artifact exists on the exact head SHA.
- `docs/PROJECT-LOG.md` snapshot updated; PR template filled in.

### Not in Sprint 1
No new copy, no new pages, no visual redesign, no `.htaccess`, no image compression, no redirects, no analytics changes beyond the single-source refactor.

---

## SPRINT 2: three large slices (one PR each, after Sprint 1 merges)
Every slice: use the partials, redesign to the templates in `docs/LAYOUT-REVIEW.md` section 3 and `docs/SITE-ARCHITECTURE.md` section 7, keep facts from `docs/OWNER-FACTS.md`, ship the screenshot sheet, name 3 pages to open.

**2A, Dining, Contact, Directions.**
- Dining: `roof.php` is the dining hub (photo hero, featured dishes with real photos, timings, how to reserve), `restaurant.php` is the menu child page with a breadcrumb. Cross-link at the top of each.
- Contact: standard hero, two big actions (Call, WhatsApp) before the form, map after.
- Directions: one-line answer first ("on foot from Fort Parking"), map, last-300 m step list, reception and late arrival, FAQ.

**2B, Gallery, About, Blog and guides.**
- Gallery: category filter (Rooms, Rooftop, Fort, Desert), lazy grid, lightbox without new libraries, CTA.
- About: story, what we look after, team, CTA.
- Blog: featured guide plus a card grid; guide pages get a table of contents, related stays and tours, FAQ. Use `[BLOG]` for copy; ask the owner first-hand questions before writing.

**2C, Tour detail template, `privacy.php`, `booking-policy.php`.**
- Tour template: hero, at-a-glance strip, itinerary timeline, included and not included, who it suits, FAQ, related tours, sticky WhatsApp on mobile. Applies to every live tour through `include/experience-page.php`.
- Policy pages: only with the owner's wording from `docs/OWNER-FACTS.md` (#5, #6); until then `status: draft`, noindex, linked nowhere.

## SPRINT 3: launch hardening (2 PRs)
- **3A:** root `.htaccess` (force HTTPS and one host, 301s only for owner-approved merges, custom 404 with navigation, cache and gzip headers, security headers, with a rollback line for each rule); form and email hardening in `contact.php` and `mail.php` (CSRF, honeypot, rate limit, safe failure with a WhatsApp and phone fallback, tracking only on real success); custom `404.php`.
- **3B:** image compression (WebP plus sensible sizes; `img/tent1.jpg` is about 6 MB; 16 images are over 400 KB; keep filenames, give the owner a command list if conversion cannot run in CI), alt-text warnings cleared, sitemap `lastmod` from git history, `docs/DEPLOY-CPANEL.md` for the developer, staging QA checklist, tag `v1.0.0`.

## Kickoff prompt for ChatGPT (paste to start Sprint 1)
```text
[BUILD] Read docs/BRAIN.md, docs/PROJECT-LOG.md and docs/SPRINT-PLAN.md in the repo.
Execute SPRINT 1 exactly as written: one branch feature/sprint-1-foundation, five commits in
the stated order, one PR. You cannot run PHP or a browser, so keep each commit small, push
after each one, and wait for CI on the exact head SHA before the next commit. If CI fails,
fix the root cause with a minimal commit; never weaken a test. Keep both GA4 IDs. Do not
add copy, pages or redesigns. When finished, fill .github/pull_request_template.md, link the
page-sheet artifact, list the 3 pages to open first, and list every check you could not run.
Stop there: the owner merges.
```


### Sprint 2A implementation batch (2026-10-13)

- Contact now follows the site's page-hero hierarchy, with direct booking, arrival and WhatsApp actions kept in the content section above the form.
- Dining hub links directly to the menu page. Directions surfaces the parking-to-haveli walk before detailed transport cards.
- Browser regression coverage checks primary Contact actions, the dining link, the Directions quick answer and horizontal overflow at 320px, 390px and 1280px.
- Exact-head Site Checks, Browser E2E and 390px/1280px screenshot sheet are required before owner merge. Font files are intentionally untouched until a final retained-CSS reference scan confirms candidates are unused.


### Sprint 2B bulk implementation (2026-10-13)

- Gallery: filterable Fort & Haveli, Rooms, Desert and Rooftop Dining sections; native accessible lightbox with previous/next and Escape support; original image URLs remain the no-JavaScript fallback.
- About and all three travel guides use the shared escaped breadcrumb include. Blog promotes the Jaisalmer Fort guide and follows the approved “Blog” navigation label.
- Editorial CSS aligns titles, card grids, TOCs, table containers and card spacing at mobile/desktop widths. Dining reciprocal cross-link and narrow-screen layout fixes close the remaining Sprint 2A gap without a separate tweak PR.
- Tests cover category filtering, modal behavior, breadcrumbs, Dining cross-links and overflow at 320, 390, 1280 and 1440px. Require exact-head PHP 7.4/8.2 checks, sitemap/publication checks, Browser E2E and the 390/1280 screenshot sheet.
- Owner review first: Gallery, Blog, Jaisalmer Fort Guide. No new facts, rates, policy text, URLs, canonicals or GA IDs added/changed.


### Sprint 2C scope folded into PR #50

- Shared experience-detail CSS receives mobile-first overflow and alignment protections; regression checks exercise six live experience routes at 320px, 390px and 1280px.
- `privacy.php` and `booking-policy.php` exist only as clearly labelled `noindex,follow` drafts, are not linked from navigation/footer, and are not added to the sitemap. They do not assert unapproved terms or privacy practices.
- Browser E2E previously failed because the closed Gallery lightbox contained an image without a source or intrinsic dimensions. The placeholder now uses an existing image and the dimension helper; rerun full CI on the updated final head.


### Sprint 3A launch hardening batch (2026-10-13)

- Canonical HTTPS/host rewrite and cPanel Apache headers/compression/cache rules are guarded by module checks. `.well-known` certificate challenge paths bypass redirects. HSTS is deliberately not enabled until certificate/subdomain coverage is owner-verified.
- Contact form changes preserve CSRF, honeypot, CAPTCHA, configured recipients and existing destinations; verification secrets move out of query strings, the session throttle runs before remote verification, and session cookies gain HttpOnly/SameSite plus HTTPS Secure.
- Custom `404.php` returns HTTP 404, stays noindex, and provides Home, Rooms, Contact, WhatsApp and booking recovery actions.
- Regression tests cover 404 behavior, GET rejection, invalid-CSRF rejection and honeypot rejection without sending a real enquiry. A static gate checks the Apache configuration; CI cannot prove cPanel module availability or production redirects.


### Sprint 3B continuation folded into PR #51 (2026-10-13)

- Sitemap `lastmod` dates come from Git commit history for the listed route PHP source files; the generator fails closed without a valid history date and CI uses a full-history checkout. It does not depend on repository timestamps or the current date.
- A rendered image-accessibility test checks every sitemap route; decorative hero images are explicitly hidden from assistive technology. Existing originals and existing image URLs stay intact.
- CI builds a responsive WebP review artifact at widths up to 480, 800 and 1200 pixels for the 16 original tracked image files over 400,000 bytes (including the 6,138,540-byte `img/tent1.jpg`). Review outputs are not public references yet.
- Added cPanel deployment and staging QA guides. The owner must approve image visuals, test Apache rules on staging, and approve release before creating `v1.0.0`.

## Next phase — legacy icon-font asset cleanup (2026-10-13)

- Remove the 16 tracked legacy Font Awesome, Glyphicons and Icofont files only with source-reference and published-route request guards in place. This is a reversible asset-only change; no CSS class, icon markup, page design or external Google Fonts behavior changes.
- `scripts/check-site.php --strict-images` must fail if a retired asset is present or a retained PHP/CSS/JS/HTML/XML source references a retired font path/family.
- Playwright checks all sitemap routes once on desktop-1280 and asserts that no local `/fonts/` request is made. Require exact-head PHP 7.4/8.2 lint/checks, sitemap/publication gates, full Browser E2E and the 390px/1280px screenshot sheet.
- This phase does not close the staging/release gate. Keep image variants review-only until the owner approves them and do not deploy or create `v1.0.0` before staging sign-off.

## Sprint 4 — repeatable cPanel staging package (2026-10-13)

- Add a standard-library Python package builder that packages only Git-tracked runtime files, verifies every published sitemap route is present, and fails if server-only local config is tracked.
- Add unit tests for runtime allowlisting, development-file exclusion, sitemap coverage, canonical-host validation and path traversal rejection.
- Add `.github/workflows/deployment-package.yml`: pull requests run package preflight/unit checks; only pushes or manual dispatches from main upload the ZIP, SHA-256 checksum and sidecar manifest. The workflow must never deploy to a host.
- Update the cPanel handoff with package commit/checksum verification and how to preserve server-only config during staging.
- Acceptance: PHP 7.4/8.2 Site Checks, sitemap/publication gates, Browser E2E/screenshot sheet, Python package unit tests, package preflight and main-only artifact generation all green on the exact PR/merge SHA. Owner validates cPanel/Apache behavior, form delivery and resized image candidates on staging. Only the owner approves deployment/release tagging.


## Sprint 5 — staging isolation and automated QA (2026-10-13)

- Restrict canonical host redirects to `hotelgarhjaisal.com` and `www.hotelgarhjaisal.com`; staging hosts must not be redirected to production.
- Force `noindex,follow` for every page head when the configured site origin is not exactly `https://www.hotelgarhjaisal.com`. Preserve existing production page indexing and explicit noindex policy/utility pages.
- Add PHP regression coverage for staging noindex and a dependency-free Node validator for HTTPS staging origin, production-host rejection, main-branch workflow, and exact staged-package commit matching.
- Add a manual, non-deploying Staging QA workflow. On PR/main push it runs a local smoke test; on owner-dispatched `main` runs it against the supplied staging URL, verifies all sitemap routes, canonical paths, robots, JSON-LD, images, mobile overflow, protected config/hidden files, headers and branded 404, and uploads a 390px/1280px visual artifact.
- Acceptance: PHP 7.4/8.2 Site Checks, package preflight, Browser E2E/page sheet, Staging QA local smoke, staging-target unit tests and PHP noindex regressions pass on the exact PR head. External staging behavior remains unverified until the owner provides a staging URL and runs the manual workflow. No deployment, form submission or release tag is authorized.
