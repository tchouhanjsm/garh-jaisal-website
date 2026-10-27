# SPRINT PLAN (fast lane)

Read `docs/BRAIN.md` first. Status lives in `docs/PROJECT-LOG.md`. Questions for the owner live in `docs/OWNER-FACTS.md`.

Goal: execute the owner-approved 12-week dual-track plan. Ship a meaningful visible and commercial vertical slice every two weeks, keep each PR to one coherent slice (typically 5–20 files), and leave merge/deployment authority with the owner.

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
| 6 | Direct-stay guide discoverability | ✓ merged in PR #56; guide linked from Home and Rooms with exact-head Browser E2E and screenshot evidence |
| 7 | Experience publication boundaries | ✓ merged in PR #57; hidden/draft sitemap and navigation gates plus sitemap-wide browser regression passed on exact PR head |
| 8 | Pre-booking FAQ coverage | ✓ merged in PR #58; FAQPage schema matches visible FAQs and exact-head Browser E2E passed |
| 9 | Car & City tour discovery and selected-page QA | ✓ merged in PR #59; exact-main Site Checks, package preflight, Staging QA and Browser E2E/screenshot sheet passed |
| 10 | Room booking journey clarity | ✓ merged in PR #60; exact-head Site Checks, package preflight, Staging QA and Browser E2E/screenshot sheet passed |
| 11 | Arrival policy accuracy | ✓ merged in PR #61; unconfirmed arrival policy promises removed |
| 12 | Arrival enquiry completion | ✓ folded into PR #61; arrival-to-Contact handoff covered by Browser E2E |
| 13 | Dining availability accuracy | ✓ merged in PR #62; unconfirmed meal hours and time constraints removed |
| 14 | Dining enquiry completion | ✓ merged in PR #62; restaurant/rooftop handoffs covered by Browser E2E |
| 15 | Site-wide arrival claim accuracy | ✓ merged in PR #63; stale reception/assistance claims removed from Home and two guides |
| 16 | Contextual arrival enquiry consistency | ✓ merged in PR #63; Directions and Contact arrival routes retained across all three pages |
| 17 | Booking and enquiry intent measurement | ✓ PR #64 merged; intent events routed to both approved GA4 IDs; live-property validation still owner-side |
| 18 | Licensed Desert Gallery and Swiss Tent Safari conversion | ✓ PR #66 merged; post-merge checks passed; visual photo/caption review remains owner-side |
| 19 | Restaurant conversion: menu booklet, maps QR and social follow | ✓ PR #67 merged; post-merge Site Checks, package preflight, staging dry run and Browser E2E passed |
| 20 | Fort walk, car services and Directions map | ✓ PR #68 merged; exact PR-head Site Checks, package preflight, staging dry run and Browser E2E passed; real staging remains owner-gated |
| 21 | SEO content cluster and Packages conversion | ✓ PRs #69–#78 delivered the source-backed itinerary/honeymoon guides, reciprocal links and sitemap coverage |
| 22 | Guest policies and trust pages | Blocked on owner-approved privacy, terms and cancellation/refund wording; keep unpublished/noindex until confirmed |
| 23 | Unified catalogue, comparison ladders and session-only Trip tray | ✓ PR #72 merged; exact PR-head PHP/site checks, package preflight, Staging QA dry run and Browser E2E passed; live staging remains owner-side |
| 24 | Thar Sunset visual storytelling across high-intent journeys | ✓ PR #73 merged; post-merge Site Checks, package preflight, Staging QA dry run and Browser E2E passed; live staging remains owner-side |
| 25 | Source-backed Jaisalmer travel-guide cluster | ✓ PR #74 merged; all four post-merge CI workflows passed on `241cddea9d02e4d29acb82941937797320f767ba` |
| 26 | Shared editorial renderer consolidation | ✓ PR #75 merged; PR-head Site Checks, package, Staging QA and Browser E2E passed |
| 27 | Contextual editorial enquiry handoff | ✓ PR #76 merged at `24c9d68d7b834d780d20e3fe83a2926afa1b239a`; post-merge Site Checks, package preflight, Staging QA dry run and Browser E2E passed |
| 28 | Four source-backed Jaisalmer Journal guides | ✓ PR #77 merged at `471636e1f074b8983bb46cd4f614ed335147f7fd`; Site Checks, package preflight and Staging QA passed; post-merge Browser E2E still running at this snapshot |
| 29 | Structured-data fidelity for Rooms, Contact and Directions | ◐ Combined into PR #78 with exact frozen-baseline exception and registry-backed HotelRoom parity |
| 30 | Shared breadcrumb/schema parity across high-intent hubs and Journal guides | ✓ merged in PR #78; breadcrumb/JSON-LD parity regression coverage passed |
| 31 | Desert experience photo galleries | ✓ merged in PR #80; exact-head Site Checks, package preflight, Staging QA dry run, Browser E2E and responsive page sheet passed; owner visual review remains |
| 32 | High-intent package discovery and guest-choice clarity | ✓ PR #81 merged; discovery hubs feed the existing session-only Trip shortlist and user-reviewed WhatsApp draft |
| 33 | Brand foundation, contrast and shared component consistency | ✓ PR #82 merged; brand constitution, token contrast contract, bastion motif and shared 6px geometry |
| 34 | Restaurant enquiry completion and conversion measurement | ✓ PR #83 merged: accessible server validation, dining CTA/form intent events and direct-contact fallback; real mail delivery remains owner-gated |
| 35 | Home and high-intent photography/performance pass | ✓ PR #84 merged the responsive art-directed hero and screenshot evidence; resized candidates remain review-only and production performance measurement is still owner-side |
| 36 | Experience comparison and Trip shortlist visual polish | ✓ PR #85 merged at `b9b15e1db0ff8a440b7a6546775fc4d2f1f8b61f`; registry-backed overnight comparison and Escape/focus recovery; exact-head CI passed |
| 37 | Restaurant identity and six first-party marketing templates | ✓ PR #86 merged at `113e75ab879057b780bde24ce9ec633999892dc6`; six guarded source masters, Canva workflow and restrained restaurant identity; operational facts remain owner-gated |
| 38 | Restaurant menu navigation, dietary filter feedback and keyboard-accessible controls | ✓ PR #87 merged at `4b135980fb9e617313d9939d7601649637b2caaa`; exact-head checks, package, staging dry run and browser E2E passed; owner assistive-tech review remains |
| 39 | Gallery curation, responsive image composition and progressive photo pagination | ✓ PR #88 merged at `5a4a060ab6f74fbddb0fe3143f5929c7d155cab3`; 36-photo selection, progressive reveal, responsive gallery evidence; owner photo/caption review remains |
| 40 | Shared verified quick-facts component for Home, Rooms and Trip Planner | ✓ PR #89 merged: escaped semantic partial, registry-backed room count, clear shortlist/enquiry/booking distinction and responsive regression tests |
| 41 | Critical-path loading and editorial featured content | ✓ PR #90 merged at `a256f22fe31b27a375876c28f3b433ccf009b36d`; exact-head Site Checks, cPanel package preflight, Staging QA and Browser E2E passed; production Lighthouse recheck remains owner-side |
| 42 | Site-wide typography, spacing, container and image-ratio normalization | ✓ PR #91 merged; shared normalization and 320/390/768/1280/1440px regression coverage |
| 43 | Responsive image delivery and generated-asset package integrity | ✓ PR #93 merged; responsive variants/preloads and manifest-derived package gate; merge-head Browser E2E cancelled during oversized evidence upload |
| 44 | Browser E2E evidence budget and release-gate reliability | ✓ PR #94 merged; compact screenshot/audit evidence and failure-only diagnostics are in CI; current artifact links are recorded in the Project Log |
| 45 | Rooms hero mobile height and quick-facts clearance | ✓ PR #95 merged at `a20640d8e0e4c3a83f34ff21373828b1ec4e6c0e`; exact-main Site Checks, package preflight, Staging QA dry run and Browser E2E passed |
| 46 | Owner visual-evidence review and cPanel staging readiness | ◐ Review approved screenshot artifacts and execute external staging only with owner-supplied staging URL and deployed SHA; production parity remains unresolved |
| 47 | Arrival, transfer and dining claim accuracy | ✓ merged through PR #97; regression coverage guards unsupported transfer and dining claims. |
| 48 | Restaurant menu publication guard and source reconciliation | ✓ merged through PR #98; unconfirmed dish names/descriptions/prices stay unpublished, and restaurant validation tolerates hosts without `mbstring`. |
| 49 | Exact-head browser evidence and repository reproducibility | ✓ PRs #100 and #101 merged; all nine checks passed on current main `dc1d7653eecc61fec72489015340d61ae8e52386`. Responsive image delivery and committed Playwright lockfile remain follow-up work; see the post-merge disposition in the full audit. |
| 50 | Responsive image delivery and reproducible browser dependencies | ✓ PRs #102–#104 merged; candidate delivery, Gallery lightbox, breadcrumb fix, pinned Playwright lockfile and layout-aware `sizes` landed. Exact-main PHP/site checks, Browser E2E, package preflight/build and responsive-image review passed on `f810d79547a04c8fe3d5b84612eafdedf88545c0` after PR #105 merged. |
| 51 | Directions arrival-journey UX refresh | ◐ Improve the hero with an arrival-summary card, add accessible in-page section navigation, style the parking-to-door steps as an ordered route, and add responsive Browser E2E coverage. Preserve `direction.php`, existing map/contact destinations and owner-confirmed copy; verify exact-head CI and screenshot evidence before review approval. |
| 55 | Structured room/experience/topic enquiry handoff | ✓ PR #129 merged at `15585db334be9683b0dc3cc54643ca4a9e317e97`; private lead/email context and retry preservation shipped. Its Browser E2E run was cancelled, so do not treat that run as a pass. |
| 60 | Trip enquiry completion with optional travel context | ✓ PR #134 merged at `686abd3272bdc37031b38855728bd1bd20a89e76`; optional date/count values appear only in the reviewed WhatsApp draft and are not stored by the site. |
| 61 | Shared photo storytelling for tours, rooms and dining | ✓ PR #135 merged at `c3326fb320e7b78bd20f70e8829c9e6a54266829`; Site Checks, responsive-image contracts, non-deploying staging QA and package preflight passed. Merge-head Browser E2E was cancelled. |
| 62 | Decision-first comparison on Desert Safari and Guided Jaisalmer hubs | ✓ PR #136 merged at `32c59b579ae7e528afe6644e90988892b35b5237`; reusable registry facts and city-tour shortlist links shipped. |
| 63 | Direct-booking path and room-first discovery across stay and experience journeys | ✓ PR #137 merged at `0facd0fbc2e2b491015c3c67d25fe7a7c50bc35f`; stay guide discoverability and room-first crosslinks shipped. |
| 64 | Car & City route-first enquiry journey | ✓ PR #138 merged at `412a34d5a42177d16c449636e572402ca19c1754`; route-first choices and proposed-itinerary disclosures shipped. Browser E2E was cancelled, so screenshot evidence remains pending. |
| 65 | Contact-channel consistency across Contact, Directions and Rooftop | ✓ PR #140 merged to `main` at `df812a33675a5d31d4ce388629038cf5a7960105`; existing visible phone/email channels are config-backed. Merge-head Browser E2E was cancelled. |
| 66 | Structured-data contact parity | ✓ PR #140 merged to `main` at `df812a33675a5d31d4ce388629038cf5a7960105`; shared Hotel/ContactPage/Restaurant contact values remain config-backed. Merge-head Browser E2E was cancelled. |
| 67 | Open-ended custom Jaisalmer itinerary discovery | ◐ PR #141 includes the custom planner and open-ended Car & City route; Site Checks, package preflight and non-deploying QA passed on head `be153e41dfbdaf956fce709f1271f8933a3ae07b`. Its Browser E2E was cancelled before producing a successful final gate; a new run is active on head `e45a8d770b273ad2c4760c0302f70c957a5cb1be` with a 40-minute job timeout. |
| 68 | Accessible navigation on experience detail pages | ◐ Draft PR #143: contextual in-page navigation is implemented in the shared detail template with source/browser contracts and tablet screenshot coverage; exact-head CI awaits PR #141 merge and retarget to `main`. |

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
- Historical blocker: Browser E2E failed because the closed Gallery lightbox contained an image without a source or intrinsic dimensions. The placeholder was replaced with an existing image and the dimension helper; subsequent merged CI runs supersede that failure.


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


## Sprint 9 — Car & City tour discovery and selected-page QA

**Branch:** `feature/car-city-tour-discovery`  
**Vertical slice:** Car & City Tours + prefilled enquiry destinations + selected-page visual regression.

- Fix the repeated breadcrumb on `car-tours.php` and wrap the three existing card images in consistent 16:10 media containers, preserving image mappings.
- Add six research-backed sightseeing themes as enquiry-only ideas. These are not confirmed products; operational details require owner approval.
- Add allowlisted Contact topic keys and prefilled enquiry messages for the six ideas. No form submission is automated.
- Add Browser E2E for media ratio, one breadcrumb landmark, responsive overflow, all six contact-prefill topics, and Rooms/Rooftop Dining FAQ/CTA paths.
- Update research/owner-fact docs, layout findings, project log and sitemap lastmod for changed PHP routes.
- Done when PHP 7.4/8.2 Site Checks, cPanel preflight, Staging QA dry run, full Browser E2E and the 390px/1280px screenshot sheet pass on one exact head SHA.


## Sprint 10 — room booking journey clarity

**Branch:** `feature/sprint-10-room-booking-clarity`  
**Outcome:** protect the two existing choices on each room card without changing how guests book or enquire.

- Keep the current external booking URL, all room names/descriptions/features, every image mapping, existing Contact room-prefill format, all public URLs/canonicals and both GA4 IDs unchanged.
- Added `tests/e2e/room-booking-clarity.spec.js`: it reads the room registry, visits all current room cards, verifies each primary availability link and secondary room-specific enquiry link, and checks rendered Contact copy matches the selected room.
- Check primary-before-secondary action order, keyboard reachability, mobile and desktop overflow; retain the existing FAQ/schema suite and all other Browser E2E coverage.
- Run the standard CI matrix: PHP lint and strict source checks on PHP 7.4/8.2, sitemap/publication checks, package preflight, staging dry run, full Browser E2E and full page screenshot sheet.
- Do not add room rates, policy promises, a new form, new dependency, analytics behavior or production changes. External booking provider and real staging behavior remain unverified by CI.


## Sprint 11 — arrival policy accuracy

**Branch:** `feature/arrival-policy-accuracy`  
**Outcome:** prevent guest confusion caused by arrival times and service promises that the owner-facts record does not confirm.

- Remove unconfirmed check-in, early-arrival, reception-hour, luggage and late-arrival guarantees throughout Directions; retain practical route information and direct guests to confirm current arrangements.
- Keep public URLs, canonicals, navigation, booking destinations and existing contact paths. Do not add replacement service promises, policy wording or prices.
- Keep `docs/OWNER-FACTS.md` as the authority for unresolved check-in/check-out, reception, storage and late-arrival details.
- Exact-head browser coverage checks the rendered wording, contact destinations, unsupported promises and no horizontal overflow at 390px/1280px; full-site checks and screenshot sheet remain required.
- Acceptance: PHP 7.4/8.2 Site Checks, strict site checks, sitemap freshness, package preflight, Staging QA, Browser E2E and screenshot sheet pass on the exact PR head; owner visual review and live staging checks remain required.

## Sprint 12 — arrival enquiry completion (folded into PR #61)

**Outcome:** turn the Directions arrival CTA into a clear, contextual enquiry without implying that any assistance is already booked.

- The Directions CTA opens the allowlisted `contact.php?topic=arrival` journey and prepopulates check-in date, expected arrival time and questions for the hotel to confirm.
- Arrival-specific form guidance tells guests to wait for hotel confirmation before relying on meet-and-assist or luggage help.
- Browser E2E follows the CTA into the form, checks the contextual copy and message, and deliberately never submits the form.
- No new route, dependency, analytics behavior, service guarantee or mail-delivery claim is introduced.


## Sprint 13 — dining availability accuracy (folded into PR #62)

**Outcome:** stop publishing meal times that are not confirmed in `docs/OWNER-FACTS.md`.

- Replace the hard-coded breakfast/lunch/dinner schedule with clear instructions to confirm current service hours, menu and table availability.
- Remove unapproved min/max time limits from the optional reservation form while retaining valid time-format validation; never enable online requests through this PR.
- Clarify in the dining FAQ that a request is not confirmed until the hotel replies.
- Keep all current public URLs, menu entries/prices, images, schema IDs, booking links and both GA4 IDs unchanged.
- Acceptance: PHP 7.4/8.2 checks, sitemap freshness, package preflight, Staging QA, full Browser E2E and responsive page sheet all pass on the exact PR head.

## Sprint 14 — dining enquiry completion (folded into PR #62)

**Outcome:** carry useful context into the dining enquiry without implying that a table is booked.

- Enrich the existing WhatsApp and `contact.php?topic=dining` draft with preferred date, time and party size; ask the hotel to confirm hours, current menu/prices and dietary options.
- Add a Contact-page context message that states the dining enquiry is not a reservation confirmation.
- Add a direct dining enquiry action to the existing unavailable-online-request fallback; keep Call and WhatsApp available.
- Add Browser E2E for rooftop and restaurant entry points, the prefilled journey, no real submission, no unconfirmed meal times, viewport overflow and accessible tap targets.
- Online mail delivery, live staging, owner-approved service hours and dining operations remain owner-gated; no analytics events or new dependencies are added.


## Sprint 15 — site-wide arrival claim accuracy (folded into PR #63)

**Outcome:** ensure Home and planning guides do not republish reception or assistance promises withdrawn until the owner confirms them.

- Replace the previous fixed reception schedule with guidance to confirm current hours and any late-arrival arrangements directly with the hotel.
- Replace unconditional meet-and-assist/luggage guarantees with clear wording to ask whether help can be arranged and wait for confirmation.
- Preserve verified route facts: vehicles cannot enter fort lanes; guests continue on foot after Fort Parking. Public URLs and canonicals remain unchanged.
- Refresh article `dateModified` and sitemap `lastmod` for changed public routes.
- Acceptance: exact-head PHP 7.4/8.2 Site Checks, sitemap/publication guards, package preflight, Staging QA, full Browser E2E and screenshot sheet pass.

## Sprint 16 — contextual arrival enquiry consistency (folded into PR #63)

**Outcome:** give guests a consistent way to ask about arrival without implying assistance is booked.

- Retain each page's link to `direction.php` and include `contact.php?topic=arrival` from Home, the Stay Guide and the Fort Guide.
- Add rendered Browser E2E to assert these pages do not show withdrawn fixed hours or unconditional luggage/meet-and-assist claims.
- Keep check-in/out times, reception hours, storage and late-arrival availability as owner-input items; do not restore any unconfirmed values or policies.
- No new dependencies, routes, analytics events, booking destination changes, mail configuration or production changes.
- Manual visual review at 390px/1280px and live staging/form-delivery checks remain owner-gated.


## Sprint 17 — booking and enquiry intent measurement

**Outcome:** make visitor intent visible without conflating outbound clicks or an enquiry-form start with a completed booking or sent message.

- Use `include/site-config.php` as the single source of truth for the two approved GA4 destinations and the existing booking URL; preserve both IDs and all existing booking/contact destinations.
- Track `booking_outbound_click`, `click_to_call`, and `click_whatsapp` when those links are clicked. Track `enquiry_start` once when a visitor first focuses a field in the existing Contact form.
- Include `page_path`, a low-cardinality `page_context`, a static `cta_label` where relevant, and `enquiry_context` limited to `room`, `dining`, `arrival`, or `general`. Do not send query strings, field values, messages, email addresses, or phone values.
- Route the events to both configured GA4 IDs using `send_to`; use beacon transport for click-intent events. Do not create a purchase/booking-complete event.
- Add Browser E2E that asserts event names, payloads, one-time form-start behavior and both destination IDs while preventing real external navigation. No live booking, email or enquiry submission is part of the tests.
- Update the Smart Check matrix and Project Log within this PR. Actual GA4 receipt, Realtime/DebugView and property-level custom-dimension setup remain manual checks; no production deployment is authorized.


## PR #65 — licensed image reference replacement (2026-10-14)

- Replace reported/unlicensed image paths with surviving hotel assets and newly supplied Desert Camp photographs; do not restore deleted images.
- Keep the Gallery and experience data mappings, responsive-image review candidates, optimization regression manifest, and `sitemap.xml` aligned to the remaining files.
- Required exact-head gates: PHP 7.4/8.2 Site Checks, sitemap/publication checks, responsive-image review, full Browser E2E and screenshot sheet.
- Restaurant QR integration is a separate slice and must use the exact owner-verified Google Business Profile/Maps share URL.


## Sprint 18 — licensed Desert Gallery and Swiss Tent Safari (completed in PR #66, merged 2026-10-14)

**Outcome:** give overnight-desert guests real owner-provided imagery, a clear list of tent facilities, and an accurate understanding of package inclusions before they enquire.

- Replace the Desert Gallery's legacy desert images with five photos from `img/Desert_Camp/`; add an enquiry path to the Swiss Tent Safari page.
- Reuse the shared experience template to render an optional photo gallery and facilities section only when data exists. Change the Swiss-tent hero to the owner-provided tent image and update the head snapshot fixture.
- Use “High tea with snacks” in package inclusions; explicitly exclude alcoholic beverages, soft drinks and a separate jeep safari excursion (must be booked separately). Keep included jeep transport distinguishable from an optional jeep safari.
- Record owner-confirmed tent facilities, tour languages, car services, Instagram and Google Maps destination in `docs/OWNER-FACTS.md`. Privacy/cancellation/booking-policy copy remains blocked until owner supplies exact wording.
- Required gates: PHP 7.4/8.2 Site Checks, strict image references, sitemap freshness, package preflight, full Browser E2E and 390px/1280px screenshot sheet. No production deploy or real staging test is authorized by this PR.


## Sprint 19 — restaurant conversion: booklet, directions and social follow (completed in PR #67, merged 2026-10-14)

**Outcome:** let guests browse the restaurant menu without a long scroll, find the correct Maps destination quickly, and discover the restaurant's social profile without slowing the page with third-party feed scripts.

- Turn the existing category-based menu into a progressive-enhancement booklet: categories, previous/next, page status and print action. Without JavaScript, keep category anchor navigation and all content available.
- Add a responsive map embed and QR code encoding the owner-confirmed Google Maps share URL. Include a clear directions link.
- Add a compact Instagram follow rail beside the existing restaurant photo gallery. Do not fake a live feed or embed unverified posts; direct post/reel URLs may be added when supplied.
- Retain existing menu items/prices and their current-menu disclaimer. Owner must confirm the menu before staging; no service hours or table availability promises are added.
- Required gates: PHP 7.4/8.2 Site Checks, strict image references, sitemap freshness, package preflight, full Browser E2E and 390px/1280px screenshot sheet. Staging and production remain owner-controlled.


## Sprint 20 — Fort walk, car services and Directions map (PR #68 merged)

**Outcome:** make the Fort walk, guided city tours and vehicle arrangements distinct products with clear enquiry paths.

- PR #68 merged to `main` at `47ca61866482e8046b2ea230da0cc18eaacf779f`. Exact PR head `a1fbda68b01312f6b623ef2e6d8a29aef70a6e51` passed [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38607504032), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38607504001), [Staging QA dry run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38607503864), and [Browser E2E/screenshot sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38607503842). The staging run was a local dry run only.

- Expand `city-tour.php` to cover Jain temples inside Jaisalmer Fort, the Palace Museum and heritage street walk. Display the confirmed guide-language choices; obtain current price and guide availability via WhatsApp.
- Improve `car-rental.php` with owner-confirmed SUV/sedan arrangements, desert pickup/drop-off, intercity journeys across Rajasthan and Delhi, and Hindi/English driver communication. Quote routes via WhatsApp.
- Direct half-day/full-day city-tour price and language enquiries to WhatsApp. Do not display rates or imply that a guide in a requested language is guaranteed before availability is confirmed.
- Add a responsive Google Maps map section to `direction.php` and preserve the existing exact hotel Maps destination link. Reinforce that the last part is on foot because cars cannot enter the Fort lanes.
- Required gates: PHP 7.4/8.2 Site Checks, strict-image checks, sitemap freshness, full Browser E2E and 320/390/1280/1440 responsive checks. No real staging-host validation or production deployment in this PR.


## Sprint 21 — SEO content cluster and Packages conversion (Batch 1 in PR #69)

**Commercial goal:** capture more non-brand searches and turn guide readers into direct room, tour or dining enquiries.

**Batch 1 vertical slice:** the three target planning guides already exist, so do not create duplicate articles. Add a reciprocal discovery module to `pack.php` using the existing card system and current licensed/owner-provided image paths, plus a direct link to `car-rental.php`. Refresh only the existing `/pack.php` sitemap `lastmod` to match its Git-derived source date; do not change route inventory or priorities. Regression-test every destination, preserve page metadata and public URLs, and validate the exact PR head in CI before owner review. This improves discoverability; it does not claim an SEO ranking or booking lift.

- Review current `blog.php`, existing guide pages, sitemap and internal links before proposing new URLs. Map high-intent topic clusters to one best destination to avoid keyword cannibalisation.
- Improve `pack.php` hierarchy, concise experience descriptions, landscape/cropped media and clear links to tour and car-service enquiry paths; retain only licensed/owner-provided images.
- The Fort guide, desert-safari comparison, Fort-stay guide and 2-day itinerary already exist. Keep each on its best destination URL; upgrade and link those pages where intent overlaps instead of publishing a second article for the same query.
- Each page needs a distinct search intent, helpful original content, descriptive title/meta, one H1, appropriate FAQ schema only for visible Q&A, relevant internal links and a contextual direct-contact CTA. Do not invent reviews, rankings, prices, opening hours or service guarantees.
- Add E2E coverage for sitemap/navigation reachability, internal links, metadata uniqueness, CTA journeys and responsive image crops. Use Search Console/GA4 after release to prioritize future content by impressions, clicks and enquiry intent.

## Sprint 21 Batch 2 — editorial intent cluster (completed in PR #70, merged 2026-10-15)

**Commercial goal:** turn high-intent non-brand planning searches into informed room, dining and desert enquiries without fragmenting the current guide cluster.

- Publish `jaisalmer-3-day-itinerary.php` for duration-specific intent and retain `jaisalmer-2-day-itinerary.php` as the two-day destination. Explain Fort, heritage and desert choices without inventing departure times, prices, external stops or availability.
- Publish `jaisalmer-honeymoon-guide.php` as a couples-planning guide, not a fixed honeymoon package. Connect room booking, rooftop dining, guided Fort sightseeing and listed desert options; make date-dependent arrangements explicit.
- Add both pages to `blog.php`, the explicit `scripts/generate-sitemap.php` allowlist and `sitemap.xml`. Add contextual links from the existing Fort, desert, Fort-stay and two-day guides.
- Reuse editorial CSS, shared page-head/breadcrumb partials and approved images. Each page has one H1, unique title/description, matching HTTPS canonical, BlogPosting/WebPage/BreadcrumbList schema, descriptive image alt text and booking/contact paths.
- Add Browser E2E for archive discovery, topic-specific content, reciprocal links and CTA markup. Required gates: PHP 7.4/8.2 lint, strict site/image checks, sitemap freshness, package preflight, non-deploying staging dry run, full Browser E2E and responsive page sheet on the exact PR head.
- Do not claim ranking or traffic lift from publication alone. After owner review and eventual publication, use Search Console and both configured GA4 properties to measure impressions, clicks and enquiry intent. Real staging, GA4 receipt and production deployment remain owner-gated.

## Owner-blocked follow-up — guest policies and trust pages

- Keep the privacy policy, terms and the single combined cancellation/refund policy page in draft/noindex/off-navigation status until the owner provides the approved policy wording.
- Confirm the privacy/data-controller name and contact email, information collected, contact-form processing and retention language before publishing the privacy page.
- Keep guest FAQs as their own useful pre-booking page and avoid legal claims until the owner supplies the copy. Do not invent refund windows, deposits, cancellation charges, check-in/out times or exceptions.
- Once approved wording is supplied, implement the policy pages as one trust-focused slice, update internal links/sitemap only after publication approval, and add tests that draft/approved state and exact visible copy are consistent.


## Sprint 22 — Batch 1: Thar Sunset identity and conversion foundation (PR #71 merged)

**Commercial goal:** make the site more recognisable and the next booking/enquiry action easier without changing established public URLs or making new operational promises.

- Introduce the master-plan palette through shared design tokens, update core color aliases and typography tokens, reserve saffron for primary actions, raise core eyebrows to 13px and control targets to at least 44px.
- Keep sticky navigation and restore dropdown access across 1081–1366px using the compact navigation pattern; responsive checks must cover 320, 390, 768, 1280 and 1440px.
- Append only safe, allowlisted UTM fields to the existing booking-engine URL; preserve its token, preserve existing UTMs, and exclude arbitrary query parameters and personal data from outbound links/events.
- Prefill previously bare WhatsApp links with the current page topic while preserving every existing page-specific message. Track safe UTM context on existing intent events without reporting completed bookings or sent enquiries.
- Update conversion and responsive regression tests plus this log. No new runtime dependency, live URL retirement, GA4 ID change, deployment or merge is part of the slice.

### Next batches — dependencies first

1. **Restaurant R1 — table-request workflow (P0):** build the request form, 30-minute slot engine, CSRF/honeypot/rate limit, durable lead log, acknowledgement email and manual staff-confirmation states as one reviewable vertical slice. Do not ship the backend until the owner confirms restaurant opening days/hours, last seating, same-day cut-off, closed dates/capacity, notification recipient/handler, privacy/retention wording and approved contact flow. Requests must never be auto-confirmed.
2. **V2 — visual storytelling:** after the token/header foundation, apply the dusk hero, arch/cinematic crops and reduced-motion-safe reveal to the high-intent pages using only approved image assets. Vendor licensed WOFF2 subsets before switching font delivery away from the existing provider.
3. **V3/V4 — unified product-card anatomy and camp/transport ladders:** use owner-confirmed catalogue facts and one source of truth before displaying prices or availability.
4. **V5 — Trip tray and lead log:** start only with a privacy-approved retention and access model; each lead needs reference, source/UTM, status and owner.
5. **V6/V7 — trust proof, camp ladder and transport calculator:** review proof sources and licensed-operator/safety notes before anything is represented as a bookable offer.
6. **V8–V10 / R2–R4:** editorial house ads only after owner approvals; complete the restaurant’s distinct Night Kitchen visual/story/FAQ experience after R1 works reliably; performance and schema changes are budget- and source-verified.

Owner-blocked items remain explicit: no fabricated prices, hours, capacity, reviews, policy terms, licensed-partner details or font assets.


## Sprint 23 — Batch 1: unified catalogue, comparison ladders and Trip tray (PR #72 merged)

**Commercial goal:** help a guest combine a Fort stay, desert experience, guided tour, car arrangement and rooftop dining into one practical enquiry while keeping current prices and date-specific details owner-confirmed.

- PR #72 merged to `main` at `c7adfa2b8e1d761e8a0e53867791a7935c1c06a7`. The new `catalogue.php` uses the reviewed central catalogue data to resolve current room and published-experience sources; shared product cards, open-sky/Swiss-tent and transport comparisons, and the tab-scoped Trip tray make options easier to assemble.
- Trip selection remains a shortlist only. WhatsApp opens an editable draft; the site does not log a server lead, capture contact details in Trip storage, send an enquiry, make a reservation or confirm availability.
- Exact PR-head PHP 7.4/8.2, strict site/sitemap, package preflight, non-deploying Staging QA and Browser E2E/screenshot gates passed. See the [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38698215503), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38698215516), [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38698215504) and [Browser E2E/page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38698215501). Staging QA was a local dry run; there is no deployment authorization.

## Sprint 24 — Batch 1: Thar Sunset visual storytelling (PR #73 merged)

**Commercial goal:** make high-intent guest journeys feel like one distinctive Thar Sunset experience while keeping trusted facts and booking actions prominent.

- PR #73 merged to `main` at `943cc325980b06b65ac7e629fb405eb9c612e4ea`. A shared token-aware stylesheet now covers Home, Rooms, Packages, Trip Planner, rooftop dining, Blog and the shared experience template.
- Existing approved images and current palette tokens are reused. Dusk-led hero overlays, arch framing and subtle viewport reveals retain content visibility without JavaScript/IntersectionObserver and honour reduced-motion preferences.
- Booking URLs/tokens, public routes, metadata/schema facts, copy, both GA4 IDs and operational claims stayed unchanged.
- Post-merge Site Checks, package preflight, non-deploying Staging QA and Browser E2E passed. The screenshot sheet is [available here](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38704069260/artifacts/11958400378); the Staging QA run was a local dry run, not live cPanel validation.

## Sprint 25 — Batch 1: source-backed Jaisalmer travel-guide cluster (completed in PR #74, merged 2026-10-15)

**Commercial goal:** expand useful non-brand discovery, answer real pre-trip questions and connect readers to the hotel’s existing direct enquiry pathways without inventing operational facts.

- Publish three distinct guides: **Best Time to Visit Jaisalmer**, **How to Reach Jaisalmer and Arrive Inside the Fort**, and **What to Pack for a Jaisalmer Desert Safari**.
- Use one shared editorial data file and PHP renderer instead of copying three article head blocks. The renderer emits the established BlogPosting, WebPage and BreadcrumbList schema, uses the shared page head, and escapes all copy with a narrowly allowlisted internal-link syntax.
- Reuse only existing approved assets. Add the three cards to Blog, add three routes to the explicit sitemap allowlist, exclude their new metadata from the frozen historical head fixture while retaining rendered SEO and content checks, and add the exact sitemap route-count/content assertions.
- Source seasonal, travel access and packing guidance from official Rajasthan Tourism/RTDC references. Schedule and weather statements remain broad/caveated; current timetables and date-specific arrangements must be confirmed directly.
- Acceptance: PHP 7.4/8.2 lint, strict site/image/publication gates, sitemap freshness, cPanel package preflight, non-deploying Staging QA, full Browser E2E across the sitemap, route/canonical/schema assertions and responsive page-sheet evidence.

### Remaining sequence after Sprint 28

1. **Restaurant R1 — real table-request workflow (P0, owner-dependent):** still blocked on approved operating days/hours, last seating, same-day cut-off, closures, capacity/max party size, notification recipient/handler, approved privacy/retention wording, mail configuration and acknowledgement/contact flow. Do not enable slot availability or live submission until the owner approves those facts. Requests must never auto-confirm.
2. **V6/V7 — trust proof and transport calculator:** use dated, sourced reviews and owner-approved operator/safety facts; estimates require confirmed routes, rate model and assumptions.
3. **R2–R4 — Night Kitchen:** after R1 works reliably and the owner approves the restaurant identity, current menu and prices; no invented hours or availability.
4. **V8–V10 / growth:** first-party editorial placements only with owner approval; improve local/AI search and performance/schema from measured evidence, not assumptions.

Owner-blocked items remain explicit: no fabricated prices, hours, capacity, reviews, policy terms, licensed-partner details or font assets.


## Sprint 26 — Batch 1: shared editorial renderer consolidation (completed in PR #75, merged 2026-10-15)

**Commercial and engineering goal:** make future Journal work faster and more consistent without discarding existing high-intent content or its search contract.

- Migrate the Jaisalmer Fort guide, Desert Safari guide, two-day itinerary, three-day itinerary and honeymoon guide to `include/editorial-guides-data.php` and `include/editorial-guide-page.php`. Keep public routes, titles/descriptions/canonicals, publication dates, approved hero assets and CTA destinations.
- Extend the renderer with ordered content blocks for paragraphs, subheadings, lists, key facts and comparison tables. Escape all values and retain the narrow allowlisted internal-link syntax.
- Keep article.css presentation and preserve related-content destinations, schema and CTA safety attributes.
- E2E checks route H1, canonical, BlogPosting schema, sources, related cards and CTA target/rel, plus Fort facts, desert comparison rows and itinerary subheadings.
- No new rates, timings, availability, reviews, policies or operational promises. Restaurant R1 and trust/calculator features remain owner-blocked; real staging and production remain owner-controlled.


## Sprint 27 — Batch 1: contextual editorial enquiry handoff (completed in PR #76, merged 2026-10-15)

**Commercial goal:** reduce steps between a guest reading a guide and sending a useful, correctly framed enquiry without creating new promises or a live booking system.

- The eight editorial guides carry a specific topic into the existing contact form. Seven curated prompts cover visit planning, desert packing, the Fort, desert safari, two-day planning, three-day planning and honeymoon planning; the How to Reach guide continues using the approved arrival path.
- Route-specific WhatsApp text covers these guides and existing live tour/experience pages that could otherwise fall back to a generic hotel enquiry.
- GA4 keeps its existing event names and destinations. Only an allowlisted topic yields the coarse `travel_planning` context; names, email, message text and arbitrary query parameters are never sent.
- Browser E2E verifies contact prefill, WhatsApp context, existing arrival behavior and exactly-once `enquiry_start` classification on the PR head.
- R1 remains owner-blocked. No live slots, auto-confirmation, unapproved hours, rates, reviews or package facts are introduced. Real cPanel staging and production remain owner-controlled.


### Sprint 28 acceptance gates (PR #77 merged 2026-10-15)

- Four new guide routes use the shared renderer and carry official tourism sources; no fabricated restaurant menu, fee, opening time, guide, transport price, availability or operator credential is published.
- Journal cards, canonical and BlogPosting output, three related links, contact handoffs and route-specific WhatsApp copy are verified by Browser E2E.
- The sitemap allowlist, generated sitemap and frozen head baseline exclusions are updated consistently. PHP/site checks, package preflight, non-deploying Staging QA and Browser E2E must all pass on the exact PR head, with a screenshot/page-sheet artifact available for owner review.
- Owner remains responsible for merge and any live staging/production deployment.


## Sprint 29 — Batch 1: structured-data fidelity

**Commercial and technical goal:** make existing on-page information easier for search engines to associate with the correct page and room without inventing offer or operational details.

- Use one shared helper to derive BreadcrumbList nodes from the exact visible breadcrumb items. Add visible breadcrumbs plus matching WebPage/ContactPage references on Contact and Directions.
- Add HotelRoom entities for the current room cards using `include/rooms-data.php`. Each node must match the card's visible name, description, image, and feature list, and be referenced from the Rooms WebPage's `mainEntity`.
- Preserve canonical URLs, both GA4 IDs, all live public URLs, and the frozen SEO baseline. A focused Playwright test validates the new structured data and its parity with visible content; refresh the Directions sitemap lastmod without changing sitemap membership.
- Acceptance: PHP 7.4/8.2 Site Checks, sitemap checks (unchanged route list), package preflight, non-deploying Staging QA and Browser E2E/page-sheet must all pass on the exact PR head. No external staging or production deployment in this PR.


## Sprint 30 — Batch 1: shared breadcrumb parity (combined into PR #78)

**Commercial and engineering goal:** keep the guest's navigation trail visible on high-intent landing pages and make its structured-data representation come from the same reviewed data as the UI.

- Reuse the shared gj_schema_breadcrumb_list() helper in Rooms, Packages, Blog and the shared Journal renderer; eliminate their hand-maintained duplicate BreadcrumbList arrays.
- Add visible Home → Rooms and Home → Packages breadcrumbs using the existing shared UI partial. Contact and Directions already use this same pattern.
- Normalize Blog's schema breadcrumb label to the visible label "Blog". The editorial renderer derives both visible and JSON-LD labels/URLs from a single $breadcrumbs array.
- Expand Playwright parity coverage across Contact, Directions, Rooms, Packages, Blog and a representative shared-renderer article.
- Do not add or change public routes, canonical URLs, booking destinations, analytics IDs, rates, availability, review claims or operational facts. Keep the pre-refactor fixture immutable and exclude only the exact newly introduced Sprint 29 node IDs already covered by dedicated assertions.
- Acceptance: PHP 7.4/8.2 and strict site checks, sitemap/package/Staging QA gates, browser E2E and 390px/1280px screenshot sheet all pass on the exact updated PR head. Staging QA remains non-deploying; owner retains merge/deploy authority.


## Sprint 31 — desert experience photo galleries (PR #80 merged)

**Commercial goal:** make the real visual difference between the open-air Sleep Under the Stars experience and the Swiss Deluxe Tent stay easier to understand before a guest enquires.

- PR #80 merged to main at `8a1fe8ab3be1392b9cda82657a580947072075ce`.
- Nine Sleep Under the Stars photos and eleven Swiss Tent Safari photos are integrated into the respective experience pages; curated assets also appear in the main Gallery.
- PHP and Playwright regression coverage protects file paths, captions, alt text, required imagery, rendered gallery order/count and mobile overflow.
- Exact-head Site Checks, package preflight, non-deploying Staging QA and Browser E2E/page sheet passed on PR head `03c20b1dbf5c5053fc1898336c612778257e9a33`. Page sheet: [390px/1280px artifact](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38753963491/artifacts/11982838456).
- No external cPanel staging or production deployment occurred. Owner visual review of subjects/captions remains required.

## Sprint 32 — high-intent package discovery and guest-choice clarity (completed in PR #81, merged 2026-10-15)

**Commercial goal:** help guests move between package categories, the useful detail pages and a clear enquiry next step without inventing prices/availability or creating a booking promise.

- Build on `include/experience-data.php`, `include/catalogue-data.php`, `include/rooms-data.php`, `include/ui/product-card.php` and the shared experience renderer rather than maintaining duplicate product details.
- Improve the catalogue and package discovery paths, with concise comparison cues from registry-backed details and contextual links between the catalogue and relevant tour hubs. Preserve current public URLs, canonical metadata, both GA4 IDs and the session-only Trip tray.
- The Trip tray stores only selected product IDs/titles/URLs in tab-scoped session storage and opens a user-editable WhatsApp draft; do not add server lead storage, contact details, form submission or availability confirmation.
- Add/update Playwright plus PHP/source regression guards for card counts and links, current price-on-request messaging, no unsupported commercial claims, safe Trip data boundaries and responsive overflow.
- Acceptance: PHP 7.4/8.2 Site Checks (including `php scripts/check-site.php --strict-images` and `php scripts/generate-sitemap.php --check`), publication/SEO checks, package preflight, non-deploying Staging QA and Browser E2E with the 390px/1280px page sheet all pass on the exact PR head.
- Do not start Restaurant R1, a lead log, transport-price calculator or review proof until their owner-fact, privacy, rate-model and sourcing dependencies are approved.

## Next unblocked direction

After Sprint 32, review owner-approved content and data quality across high-intent room/desert/tour choices, then prioritise an evidence-led performance or accessibility slice based on measured CI inventories. Restaurant R1, privacy/cancellation publication, paid transport estimates and third-party proof remain explicitly blocked on owner input. Do not bundle these independent blocked projects into Sprint 32.

## Sprints 34–36 — restaurant enquiry, event measurement and visual consistency (PR #83)

This bulk slice makes the existing restaurant request path more accessible and measurable without changing whether online mail requests are enabled. Field-level server errors are programmatically linked to inputs; focus returns to the first invalid field; tests run against a separate local PHP process with `GJ_RESTAURANT_MAIL_ENABLED=1` and invalid/synthetic submissions only.

Analytics distinguish `restaurant_enquiry_start` and `restaurant_request_attempt`; neither means the hotel received a request or confirmed a table. Only approved campaign fields go to both configured GA4 destinations, and personal form fields are explicitly excluded in tests. The disabled fallback continues to offer Call, WhatsApp and Contact.

The visual pass extends the approved bastion motif and architectural photo crop to restaurant, catalogue and experience media. Do not promote responsive image variants into runtime until the owner reviews the review-sheet artifact. Do not enable server mail, add guest leads, publish new offers/prices/hours, or alter public routes/canonicals without confirmed facts and owner approval.

## Sprint 35 — home hero art direction and screenshot evidence (PR #84 merged)

**Commercial goal:** make the first home-page impression more representative of the rooftop/fort setting while reducing the selected desktop hero asset, with responsive visual evidence the owner can review.

- Use two existing, already approved photo assets as art-directed sources: the portrait facade below 768px and the wide rooftop/fort panorama at 768px and above. The image is decorative behind the visible H1; the meaningful promise and Book your stay action remain text/links.
- Reuse only checked-in same-dimension WebP derivatives, preserving original image files and intrinsic source dimensions. Repository bytes: facade derivative 591,152 bytes; panorama derivative 388,404 bytes. These file sizes are deterministic repository budgets, not production transfer or a promise of LCP/CWV improvement.
- Add a browser regression for source selection at 320, 390, 768, 1280 and 1440px, image decode, dimensions, tracked-byte ceilings, CTA visibility, fixed-dock clearance and horizontal overflow. At tablet widths, reserve the dock height so the hero booking and arrival actions are not covered by the fixed mobile action bar. Preserve both GA4 IDs, all existing public URLs/canonicals, booking destinations, Trip shortlist boundaries and PHP 7.4/8.2 compatibility.
- Improve page-sheet reliability by waiting for same-origin images to load/decode before capturing; include a 768px sample for Home, Rooms, Catalogue, Car & City Tours, Rooftop Dining and Restaurant, while retaining 390/1280 coverage on every sitemap route.
- Do not promote any generated 480/800/1200px review-only variant, regrade/crop source files, add fonts/libraries/video, or make performance claims about production. Owner must inspect 320/390/768/1280/1440 hero evidence and key pages before merge.
- Exact-head acceptance: PHP lint 7.4/8.2, `php scripts/check-site.php --strict-images`, `php scripts/generate-sitemap.php --check`, existing security/SEO/publication guards, package preflight, non-deploying Staging QA, Browser E2E and screenshot/evidence artifacts. No external staging upload or production deployment by this PR owner/assistant.


## Sprint 36 — experience comparison and session-only Trip tray polish (PR #85 merged)

**Commercial goal:** make the two overnight choices easier to compare without inventing a benefit, while making the session-only shortlist easier to dismiss with a keyboard.

- Store overnight card labels and comparison facts in `include/experience-data.php`; the catalogue renders duration and comparison rows from the shared registry rather than branching on a product slug.
- Use a compact definition-list layout for duration, sleep setup and bathroom arrangements so the material difference between open-air sleeping and a Swiss Deluxe Tent is scannable on mobile and desktop.
- Allow Escape to close the visible Trip tray and return keyboard focus to its trigger. Preserve the existing explicit Close button, remove controls, status announcements and editable WhatsApp draft.
- Keep selection data tab-scoped in `sessionStorage`; add no guest details, server-side leads, prices, availability promises, booking confirmation, new dependencies or routes.
- Exact-head acceptance: PHP 7.4/8.2 lint, strict image checks, sitemap/publication/security checks, package preflight, non-deploying Staging QA, Browser E2E and screenshot artifacts. Owner visual review and live-host testing remain separate.


## Sprint 37 — restaurant identity and first-party marketing kit (PR #86 merged)

**Commercial goal:** create a repeatable route from approved copy and photography to first-party artwork, while making dining more recognisable without reducing information readability.

- PR #86 merged to `main` at `113e75ab879057b780bde24ce9ec633999892dc6`.
- Six SVG composition masters and Canva workflow instructions are now in `marketing/templates/`; visible placeholders remain until the owner replaces and approves them.
- Restaurant styling uses the approved Garnet/Night hero and restrained Garnet/Gold accents while keeping menu, information and enquiry surfaces light.
- PR-head Site Checks [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127095), package preflight [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127092) and non-deploying Staging QA [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127080). Merge-head Browser E2E [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341) — [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341/artifacts/12027655875) and [evidence bundle](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341/artifacts/12027139666). No external cPanel staging or production deployment occurred.
- Menu prices and contents, service hours, reservation/mail activation, public routes, canonicals, schema facts, GA4 IDs and production configuration were not changed. No live cPanel staging or production deployment occurred.

## Sprint 38 — restaurant menu and dietary-filter accessibility (PR #87 merged)

**Commercial goal:** make the existing menu easier to navigate with touch, keyboard and assistive technology without changing menu facts or reservation behaviour.

- PR #87 merged to `main` at `4b135980fb9e617313d9939d7601649637b2caaa`; exact PR-head `21bc78c7a3650c646567ca7efd8e4522105945b1`.
- The vegetarian filter and category links use 44px targets, shared 3px focus, `aria-current="location"` and a polite full-menu result count. The status logic does not change which booklet page is active.
- Exact-head Site Checks, package preflight, non-deploying Staging QA and Browser E2E passed. Visual contrast review and real assistive-technology/live-host review remain owner-side.
- No menu, price, hours, reservation, route, canonical, schema or GA4 changes; no cPanel deployment.

## Sprint 39 — curated Gallery and progressive photo pagination (PR #88 merged)

**Commercial goal:** help guests scan a stronger, more intentional visual introduction to the haveli, rooms, desert experiences and dining instead of facing the full legacy photo wall at once.

- Extract the current gallery data into `include/gallery-data.php` and curate 36 existing, repository-backed photos: 9 hotel, 12 rooms, 10 desert and 5 restaurant.
- Apply the approved art-direction language as CSS crops only—architectural arch, 3:4 portrait and 21:9 landscape. Do not re-grade, resize, rename or delete originals; the dedicated overnight experience galleries keep their full curated source sets.
- Progressive enhancement shows up to six photos per section with a clear “Show more” action; without JavaScript, all 36 remain available. Keep the existing category filters and native lightbox, and make the filter/pagination state keyboard-friendly.
- Add a PHP inventory/source contract and Playwright coverage for server-rendered completeness, page-size counts, filtering, more-photo state, lightbox next/previous, and no horizontal overflow at each configured viewport.
- Acceptance: PHP 7.4/8.2 lint, strict image and sitemap checks, publication/security/discovery guards, curated-gallery PHP guard, package preflight, non-deploying Staging QA and Browser E2E/page sheet on the exact PR head. No price, availability, booking, analytics, route or production behavior changes.



## Sprint 40 — shared verified quick facts (completed in PR #89, merged 2026-10-16)

**Commercial goal:** make the most important pre-booking context scannable without adding unsupported facts or distracting guests from the next useful action.

- Add `include/ui/at-a-glance.php` as a reusable, escaped semantic definition-list component that ignores invalid and empty rows. Guard this contract in PHP 7.4/8.2 Site Checks.
- Home shows verified location, registry-backed room count, rooftop dining and currently listed experience categories. Rooms reuses its existing registry and floor grouping for the count and floor summary; facilities are explicitly said to vary.
- Trip Planner surfaces the available product categories, tab-scoped shortlist, user-reviewed WhatsApp draft and a plain statement that a shortlist is not a confirmed booking.
- Reuse `.at-a-glance` tokens, keep information on a light surface, and assert exact rendered labels/values plus no horizontal overflow at 320, 390, 768, 1280 and 1440px.
- Preserve all public URLs, canonicals, schemas, menu data, prices, booking URLs, enquiry behavior, both GA4 IDs and the no-server-side-lead boundary. Owner retains merge and deployment.


## Sprint 41 — critical-path loading improvements (PR #90 merged)

**Commercial goal:** show high-intent page content quickly without changing guest-facing facts or replacing approved photography.

- Keep Google Fonts non-render-blocking with a no-JavaScript fallback.
- Use viewport-aware preloads on the Home hero and retain explicit priority for the actual above-the-fold image.
- Preserve intrinsic image dimensions and real source photography.
- This work was merged in PR #90. Subsequent verification found one Trip Planner preload/source mismatch; the correction and browser regression guard are included in open PR #91.
- Preload success alone is not a performance measurement; do not claim Core Web Vitals or Lighthouse gains without comparable measured runs.

## Sprint 42 — site-wide layout normalization and guest-journey hardening (PR #91 merged)

**Commercial goal:** make every key page easier to scan, visually consistent and usable on mobile, while keeping booking/contact paths and operational statements truthful.

- Normalize typography, spacing, content rails, card geometry and photo-hero sizing through the existing token stylesheet, loaded after page-specific CSS.
- Retain deliberate page-specific art direction for the Gallery, dining, rooms and architectural imagery; do not replace genuine photographs with generated imagery.
- Keep the Trip Planner shortlist clearly separate from a confirmed booking. At 320px, the hero compacts without removing quick facts or reducing the 44px button minimum.
- Extend browser evidence: eleven page archetypes at 320, 390, 768, 1280 and 1440px; the primary screenshot sheet remains 390px/1280px, with extra viewport captures at 320px/768px/1440px for Home, Rooms, Packages, Trip Planner, Dining and Gallery.
- Browser E2E checks the Trip Planner's rendered hero path against its image preload to catch duplicate image downloads. Exact-head Site Checks, Browser E2E, deployment-package preflight and non-deploying Staging QA are required before the owner reviews the phase.
- PR #91 is now merged into main. External cPanel staging, real mail/CAPTCHA delivery, production PHP/Apache compatibility, and owner approval for menu/schedule/policy and enquiry-log privacy/retention remain release gates.

### Sprint 43 — responsive image delivery and package integrity (PR #93 merged)

- PR #93 merged on 2026-10-17 at `3fe9073648f0b26f7384fdffd2dc12745f5e2da5`; its exact PR-head Site Checks, package preflight, non-deploying Staging QA and Browser E2E passed.
- The PR adds generated responsive WebP candidates from the checked manifest, responsive `srcset`/`sizes` on selected high-intent images, matching hero preload candidates, and package gates that reject unlisted generated files/symlinks.
- The merge-head Browser E2E run was cancelled while uploading its roughly 1.14 GB evidence bundle; reduce artifact duplication in Sprint 44. No cPanel upload or production deployment occurred.

## Sprint 44 — Browser E2E evidence budget and release-gate reliability

**Engineering goal:** finish the complete responsive browser suite and preserve review-quality evidence without spending the job time budget uploading duplicate screenshots and traces.

- Keep the standalone `page-sheet` artifact for the all-route 390px/1280px screenshot sheet and selected 768px samples, plus expanded Gallery review captures.
- On every run, upload only five-width homepage hero screenshots, the six-page responsive spotcheck set at 320px/768px/1440px, and JSON audit inventories. Do not upload the entire screenshots directory alongside duplicate copies from the HTML report and test results.
- Upload Playwright traces, failed-test screenshots, videos and error context only when a check fails. Do not upload the full HTML report or all test results on successful runs.
- Keep public routes, runtime image choices, test coverage and page behaviour unchanged; no tests are skipped to shorten the workflow.
- Acceptance: exact-head Browser E2E and restaurant-form validation finish within the job timeout; the compact evidence and page-sheet artifact upload successfully; Site Checks, package preflight and non-deploying Staging QA pass. Verify main-push CI after merge.
- PR #94 merged on 2026-10-17 at `33b178fdfc40c9c498a857a92d04f5e49e71a3b3`, capping successful-run evidence uploads. The last full pre-PR-94 browser run reported 494 passed and one failure: Rooms hero height was 735.08px against the 720px ceiling; Sprint 45 addresses that release gate.
- No cPanel upload or production deployment by this implementation.


## Sprint 45 — Rooms hero mobile height regression (original scope; completed in PR #95)

**Commercial goal:** keep Rooms discovery compact on common phone widths without removing context or shrinking the booking actions.

- The last full browser run measured the Rooms photo hero at 735.08px on the 390px viewport, exceeding the existing 720px visual contract.
- The prior flex-based action sizing still wrapped both CTAs vertically at 390px. Use a two-column grid for 381–760px while retaining the intentional stacked layout at 320px.
- Keep both actions at least 44px high, keep all four quick facts visible, and preserve the existing hero photography.
- At 320×700, ensure the full Rooms fact card clears the fixed mobile contact dock. Use shorter copy and hide only the redundant location/floor note at widths up to 380px; retain the eyebrow and all four facts.
- Extend the existing sitewide Playwright contract to assert the two actions share one row, remain inside the content rail and preserve touch-target size; the existing five-viewport hero-height assertion remains authoritative.
- Acceptance: exact-head Site Checks, cPanel package preflight, non-deploying Staging QA, full Browser E2E and screenshot artifacts pass. No URL, metadata, menu facts, booking destination, analytics IDs or production deployment changes.


## Sprint 45 — Rooms hero regression (PR #95 merged)

- The 390px Rooms hero now lays out the two booking actions in a two-column grid; 320px retains the stacked actions while removing only the redundant location/floor note to keep all four facts above the mobile dock.
- The exact-main run on `a20640d8e0e4c3a83f34ff21373828b1ec4e6c0e` passed Site Checks, package preflight, non-deploying Staging QA and Browser E2E. Review the [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163625/artifacts/12101465112) and [responsive audit evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163625/artifacts/12101400416) before visual sign-off.
- Workflow success is not an external cPanel staging result. No staging upload or production deployment occurred.

## Sprint 46 — visual evidence review and staging gate readiness

- Reconcile the project snapshot to PR #95 and the verified `main` SHA; do not restart Sprint 45 or describe earlier intermediate-head cancellations as the final result.
- Owner review should start with Rooms at 320px and 390px, then compare Home hero sources at 320/390/768/1280/1440px, and review Gallery/Desert Camp subject-to-caption matching before any new image is promoted.
- Keep resized 480/800/1200px candidates out of runtime paths until a person approves full-size crop/sharpness and source mapping. The existing browser audit records selected `currentSrc`, natural/intrinsic dimensions, loading priority and visible/eager state; resource timing byte counts may be unavailable or zero.
- The separate staging dispatch requires an owner-supplied HTTPS staging origin and exact deployed package SHA. Only that dispatch can test a real external host; confirm Apache/PHP behavior, responsive WebP MIME types, cache headers, preloads, CAPTCHA configuration and mail delivery without recording secret values.
- Owner-blocked: staging URL/access, cPanel PHP version, real CAPTCHA/mail delivery, restaurant operating schedule/menu prices, approved privacy/controller/retention wording and booking cancellation terms.


## Sprint 47 — arrival, transfer and dining claim accuracy

**Commercial goal:** make the arrival decision trustworthy without implying that an unconfirmed transfer, fare, schedule, breakfast service or drink offering is guaranteed.

- Directions keeps the established Fort Parking / fort-entrance walking facts and the existing contact/map/book paths, but directs guests to confirm current transfer route, vehicle, pickup point, fare and timing for their date.
- Home and Rooftop Dining describe the setting without promising breakfast, an unverified drinks offering, or sunrise/all-day meal service; guests are told to confirm current dining service times and menu options.
- Playwright checks rendered Directions copy for unapproved transfer fares/timing and verifies that both Home and Rooftop Dining keep service-time language qualified.
- No new transport estimator, new public price, service-hour promise, booking behavior, URL, schema fact, analytics ID or production change is introduced.
- PR #97 merged on 2026-10-17 at `1a385f3c2975c7308849335d412b31f295ea4fd0`. Exact merge-head Site Checks, Browser E2E, cPanel package preflight and non-deploying Staging QA all passed.
- Release gate remains external staging on the owner-supplied HTTPS origin and exact deployed SHA; confirm web server behavior, images, CAPTCHA and mail without exposing secrets.


## Sprint 48 — final release-readiness and launch blocker closure (2026-10-18)

**Commercial goal:** prevent unverified commercial content from reaching guests, reconcile the actual roadmap with merged code, and turn the final release process into explicit, testable decisions.

- Gate the restaurant dish list, descriptions, categories and prices as one publication unit. `include/menu-data.php` remains owner-unconfirmed; while that flag is false (or the menu is empty), render a helpful menu enquiry CTA instead of the sample data.
- Keep the existing booklet and accessibility behavior under the owner-confirmed branch; Playwright tests the pending state now and continues to exercise the booklet when the registry is approved.
- Avoid a fatal restaurant-form validation path when `mbstring` is unavailable on the actual PHP host.
- Reconcile the Full Repository Review, Backend Remediation, Smart Check Matrix, Sprint Plan and Project Log with the merged PR #91/#95/#97 state. Historical findings remain in the log but no longer masquerade as current failures.
- Exact-main baseline `1a385f3c2975c7308849335d412b31f295ea4fd0`: Site Checks, Browser E2E, package preflight and non-deploying Staging QA passed. The next PR must pass all four workflows again on its exact head and supply screenshot/evidence artifacts.
- Release blockers that code cannot truthfully resolve: owner approval for current menu/prices/service hours, privacy controller/contact/data/retention wording and booking/cancellation terms; cPanel PHP and Apache compatibility; real CAPTCHA/mail delivery; external staging URL/access and staged-package SHA; image/crop and key-page screenshot sign-off; GA4 receipt and production performance evidence.
- Current public production is still serving legacy Home/Directions markup, including transfer prices and meet-and-assist/reception promises removed from repo source. Treat production parity as unverified until an owner-managed staging deployment proves the reviewed package and the live host is explicitly released.
- This sprint changes repository source/docs only; no cPanel upload, production deployment, owner-fact invention or public policy approval is implied.


## Sprint 51 — Directions arrival-journey UX refresh (2026-10-19)

**Goal:** apply the strongest parts of the getting-here reference concept to the existing PHP Directions page without importing unsupported operations, rates or policies.

- Add a compact arrival-essentials card in the photo hero, featuring the Fort Parking drop-off, the on-foot route, and a Maps link sourced from the current configured hotel pin.
- Add a horizontally scrollable, keyboard-accessible section navigation for quick directions, transfer options, walking/mobility, map and FAQs.
- Upgrade the parking-to-door route from generic tiles into an ordered list with responsive number markers, visible text and strong focus styles.
- Add Playwright assertions for the arrival card, link destinations, anchors, route order and no horizontal overflow at 320, 390, 768, 1280 and 1440 CSS pixels.
- Preserve all public URLs, metadata/schema, site-config phone/WhatsApp/Maps destinations and the current qualification for arrival assistance. Do not add fake route photography, named staff, made-up transfer fares, unsupported check-in/reception claims or an unimplemented form endpoint.
- Acceptance gate: PHP 7.4/8.2 Site Checks, responsive asset/image review, package preflight and complete Browser E2E pass on the exact feature head. Owner visual/accessibility review remains separate. No merge or deployment is implied.


## Sprint 52 — one-click Content Studio startup and safe local preview (2026-10-20)

**Goal:** let a non-web developer start the local tour/blog/Gallery authoring screen and a PHP page preview without manually opening two terminal sessions, while making the local-only and non-deploying boundaries unmistakable.

- Add an executable macOS .command launcher in tools/content-studio/. A double-click starts the local PHP preview and Content Studio, checks server readiness, opens the browser and gives readable dependency/port errors.
- Bind both services only to 127.0.0.1. Do not install software automatically, expose a public server, stop an unrelated process, add a CMS dependency, or deploy content.
- Keep logs in the user's Library Logs folder; closing the launcher terminal sends termination signals to the two child servers. Existing services on either selected port cause a clear error rather than being overwritten.
- Add CI for shell syntax, executable-bit/loopback assertions and cleanup expectations. Keep launcher files outside the cPanel runtime package.
- Update the Content Studio UI, project log and release-readiness snapshot so the entry point and true current main/CI state are visible.
- PR #109 merged on 2026-10-20 at ed75f9856e9e84b6c483ce637b28fba0f409066c. Site Checks [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437203), package [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437165), non-deploying Staging QA [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437166), Browser E2E [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437265) on the same merge SHA.
- The executable macOS launcher defaults to ports 4173/8088, binds both servers to 127.0.0.1 and is excluded from the cPanel runtime package.
- This phase improved operator-side local startup only. It did not create a hosted CMS or deployment mechanism. External staging sign-off and owner deployment approval remain required.

## Sprint 53 — saved Content Studio management (2026-10-20)

**Goal:** close the Draft-first workflow gap so a non-web developer can return to saved Content Studio records and manage their lifecycle without editing JSON manually.

- Add a Manage saved content screen with per-record Edit, Publish locally, Set to Draft and draft-only Delete actions for tours, blog guides and Gallery entries.
- Reuse the existing guided forms. Keep public slugs immutable, preserve blog publication date and operator-customized fields, and render saved record values into the form repeaters.
- Restrict management to JSON overlays created by Content Studio. Existing legacy PHP registry entries and Hidden records remain read-only.
- Before changing a page state or deleting it, verify that its route file is still the generated Content Studio wrapper. Do not replace an externally modified route.
- Publishing validates required structure and local image paths, updates the responsive-image manifest and sitemap atomically where applicable. Failed transitions roll back content, sitemap and manifest changes.
- Delete only records in Draft state; remove its generated wrapper and JSON record, but never delete the source image. Preserve draft noindex and sitemap exclusion.
- Add Node integration tests and Browser E2E coverage for list/read/edit/publish/unpublish/delete, legacy/hidden protections, route tampering and symlink safety.
- Exact-head acceptance: PHP 7.4/8.2 Site Checks, responsive-image review, package preflight, non-deploying Staging QA, full Browser E2E and screenshot/evidence artifacts. PR #110 is open; verification is pending until all four workflows pass on the exact head.
- No cPanel access, staging upload, merge or production deployment is part of this phase.



## Sprint 55 — structured enquiry handoff (PR #129)

**Commercial goal:** give the hotel enough trusted context to respond quickly when a guest asks about a particular room, live experience or visit-planning topic.

- Extract existing Contact topics into one shared registry and add a resolver that accepts only known room slugs, published experience details and explicit Contact topics. All display labels come from the server registries.
- Bind each rendered form to a short-lived opaque token held server-side in the session. Keep multi-tab behaviour bounded, expire tokens after two hours, and trust no posted label/type/slug.
- Persist the resolved type, slug and label with the existing private lead record. Add the same context to the owner-notification subject and escaped email table so room/experience enquiries are distinguishable without opening every message.
- On recoverable validation, CAPTCHA, rate-limit or storage errors, return to the original trusted enquiry context. Keep invalid-CSRF/non-POST paths generic and consume the context token only after private storage succeeds.
- Regression coverage walks the registry, unknown/unsafe slug fallbacks, context-preserving retry URLs, and token presence on the existing room and experience journeys. Browser tests never submit or send an enquiry.
- Preserve the external booking-engine URL and existing GA4 privacy boundary; no prices, availability, policies or unconfirmed operational facts are introduced.
- Acceptance is pending until PHP 7.4/8.2 Site Checks, strict image and sitemap checks, responsive-image review, package preflight, non-deploying Staging QA and complete Browser E2E/screenshot artifacts pass on the exact PR head. No cPanel upload or production deployment is part of the phase.


## Sprint 56 — three-second homepage first impression (PR #130)

**Goal:** let a first-time visitor understand the property's identity, location, offer and next action at a glance, without scrolling or decoding a long opening paragraph.

- Clarify the homepage eyebrow as a heritage stay inside Jaisalmer Fort; keep the strong existing H1 and reduce the lead to one concrete sentence.
- Make the booking action primary and replace the vague planning anchor with the direct, internal "Explore the rooms" route.
- Bring three registry-backed stay/dine/explore proof points into the hero, using the existing shared facts component and dynamic room count.
- Give the proof strip a refined, responsive visual treatment while retaining the existing art-directed photo sources, brand palette, no-library rule and reduced-motion/accessibility conventions.
- Extend the five-viewport hero browser review to verify visible first-screen hierarchy, correct destinations, facts visibility, mobile CTA-dock clearance and zero horizontal overflow.
- Acceptance: exact-head Site Checks including PHP 7.4/8.2, all browser E2E/screenshots, responsive-image review, and safe package/static checks pass. No cPanel action or production deployment is in scope.


## Sprint 57 — searchable room discovery and shortlist UX (PR #131 merged)

**Commercial goal:** help guests compare the existing rooms quickly, so the route from the homepage to a relevant room and then to booking or a room-specific enquiry takes fewer steps.

- Add a progressive-enhancement room finder to the existing Rooms directory: search by room name, description or listed feature; filter by floor; and filter by one registry-backed feature.
- Build feature choices and room counts directly from `include/rooms-data.php`. Do not duplicate room facts, infer facilities, add prices or imply live availability.
- Show a live result count, hide empty floor groups and their jump links while filtering, and offer a clear no-results/reset state. With JavaScript disabled, all existing room cards and actions remain available.
- Preserve every public URL and room anchor, existing photography/captions, room-specific Contact context, booking-engine destination/token, page metadata/schema, trip-shortlist controls and image budgets.
- Add Playwright coverage for search, combined floor/feature filters, zero-result recovery, reset behavior, keyboard-operable controls and the unchanged booking/enquiry journey. Keep the existing screenshot-sheet and responsive-viewport coverage.
- Exact-head acceptance: PHP 7.4/8.2 Site Checks, strict image and sitemap checks, responsive-image review, non-deploying QA, package preflight, full Browser E2E and screenshot evidence. Local PHP/Playwright execution must be reported honestly if not run in this connector session.
- No cPanel action, external staging upload or production deployment is in scope. Owner approval of business facts, visual sign-off and production parity remain separate release gates.


## Sprint 58 — experience discovery and comparison UX (PR #132 merged)

**Commercial goal:** help guests narrow the published desert and city experience list quickly, then open the relevant itinerary or add the option to their trip shortlist.

- Add a progressive-enhancement finder to the existing Packages route, with text search plus category and duration-type filters generated from currently published, non-hub experience registry entries.
- Add concise Half-day, Full-day and Overnight labels to the corresponding published cards using the same registry duration field; do not infer exact clock times or introduce new inclusions.
- Show live result counts, hide empty result sections, and provide a clear no-results/reset path. With JavaScript disabled, the complete existing published experience lists and their itinerary/trip actions remain available.
- Keep draft/hidden entries out of cards, filters and counts. Preserve all current public URLs, SEO/schema, booking/contact paths, guide content, Content Studio workflow, trip shortlist behavior, original photography and image budgets.
- Add Playwright coverage for combined search/category/duration filters, empty-state recovery, reset, 320/390/1280/1440px overflow/tap targets and the no-JavaScript fallback.
- Exact-head acceptance: PHP 7.4/8.2 Site Checks, strict image and sitemap checks, responsive-image review, non-deploying QA, package preflight, complete Browser E2E and screenshot/page-sheet evidence. Local PHP/Playwright execution must be stated honestly if not run in this connector session.
- No cPanel upload, external staging deployment, production deployment or new business fact is part of this phase.


## Sprint 59 — mid-page experience decision checkpoint (PR #133 merged)

**Commercial goal:** after guests review an experience's itinerary and the included/not-included details, present a clear next step without forcing them to scroll to the final footer CTA.

- Reuse the shared experience renderer and a small UI partial to place one responsive conversion checkpoint after the itinerary/detail sections on each live experience page.
- Keep the primary action context-aware: existing Contact topic for general desert/hub routes, trusted `?experience=` Contact context for non-hub experiences, and the current WhatsApp draft for half-day/full-day city tours.
- Offer the existing Add to Trip interaction for live non-hub offers and the existing catalogue shortlist route for hub pages. Duplicate Add to Trip controls must stay synchronized through the existing trip-tray implementation.
- Do not change route slugs, current pricing/availability language, package inclusions, contact destinations, analytics payloads, schema or photography.
- Update sitemap generation to account for the shared experience renderer in `lastmod`, then regenerate the XML for all live experience URLs.
- Add Playwright coverage across all eight live experience routes for CTA destinations, placement after itinerary/details, hub/non-hub action behavior, synchronized trip selection and responsive/no-overflow controls.
- Exact-head acceptance: PHP 7.4/8.2 Site Checks, strict image and sitemap checks, responsive-image review, non-deploying QA, package preflight, complete Browser E2E and screenshot/page-sheet evidence. Local PHP/Playwright execution must be reported honestly if not run in this connector session.
- No cPanel upload, external staging deployment, production deployment or new unapproved business fact is in scope.


## Sprint 60 — Trip enquiry completion with optional travel context (PR #134 merged)

**Commercial goal:** make the Trip shortlist draft easier for the hotel to respond to by letting a guest add a travel window and party size before opening WhatsApp, without pretending the site can confirm availability.

- Add optional travel-start, travel-end and traveller-count fields to the existing Trip tray. Keep the current product shortlist, primary booking engine and all existing contact destinations unchanged.
- Build the WhatsApp draft from the selected options plus the optional context. When details are not provided, use clear "to be confirmed" wording instead of an instruction-like placeholder.
- Validate that the end date is not earlier than the start date and that any supplied traveller count is a positive whole number. Prevent an invalid draft from opening and focus the field that needs attention.
- Keep travel details in memory only: do not add them to sessionStorage, server requests, lead records or analytics events. The existing shortlisted product IDs/titles/URLs remain the only persisted Trip tray state; WhatsApp opens a draft that the guest reviews and sends manually.
- Clear optional details when the shortlist becomes empty. Do not add price, capacity, availability, policy or timing claims, and do not alter the external booking engine URL/token.
- Add Playwright coverage for the combined room-and-experience draft, valid date/count values, invalid range/count recovery, tab-only persistence boundaries, reload recovery, keyboard behaviour and 390px/mobile overflow.
- Exact-head acceptance remains PHP 7.4/8.2 Site Checks, responsive-image/static contracts, sitemap/package preflight, non-deploying QA, complete Browser E2E and screenshot/page-sheet evidence. Local PHP/Playwright was not run from the connector session; record CI results without treating cancelled runs as passes.
- No cPanel upload, external staging deployment, production deployment or owner-only business fact is part of this phase.


## Sprint 61 — shared photo storytelling for tours, rooms and dining (PR #135 in review)

**Commercial goal:** help guests make a visual choice before enquiring by letting them browse multiple relevant photos within the existing tour, room and restaurant journeys instead of relying on a single hero image.

- Add one reusable, accessible previous/next photo carousel with captions, meaningful alt text, visible status, 44px controls, keyboard arrow navigation, reduced-motion support and no automatic movement.
- Apply it to the live experience-detail renderer and the Desert Safari and Guided Jaisalmer Tours hubs. Use existing experience-gallery data where present and curated public Gallery assets for the sunset, cultural and city tour pages that lacked a set.
- Add a second photo to each of the seven room cards using already-curated room photographs; keep each room's registered hero first, and preserve search/filter behavior, room anchors, the booking-engine URL/token, trip selection and room-specific Contact handoff.
- Convert the restaurant's static photo row into a previous/next carousel using the existing restaurant Gallery assets. Keep the menu publication guard unchanged while the menu remains unconfirmed.
- Preserve all image paths, routes, metadata/schema, rates, timings, confirmed/uncertain inclusions and contact destinations. New city images can be supplied in a later Content Studio pack if the owner wants a different or more accurate set.
- Add Playwright coverage for next/previous behavior, single-slide active state, all live experience routes, seven room cards, restaurant menu-price guard, no-JavaScript visibility and 320/390/1280/1440px no-overflow.
- Exact-head acceptance: PHP 7.4/8.2 Site Checks, strict image/sitemap contracts, responsive-image review, package preflight, non-deploying QA, complete Browser E2E and screenshot/page-sheet artifact. Local browser/PHP execution was not run from this connector session; treat only exact-head CI as evidence.
- No cPanel upload, external staging deployment or production deployment is part of this phase.


## Sprint 64 — Car & City route-first enquiry journey (PR pending)

**Commercial goal:** help visitors choose a useful next step quickly while clearly distinguishing existing pages from itinerary ideas that have not been approved as offers.

- Add three high-level routes on the Car & City hub: explore existing guided options, enquire about car arrangements, or browse proposed themes.
- Separate the existing-options heading from six proposed itinerary ideas, with more explicit enquiry-only wording and topic-specific contact paths.
- Preserve every public URL, query topic, image path, booking destination/token, metadata/canonical/schema, analytics, trip-list actions, publication guards and contact configuration.
- Add Playwright checks for the three route choices, six proposal enquiry destinations, non-bookable disclosure, existing room-first booking path, image loading/alt text and 320/390/1280/1440px overflow.
- Exact-head acceptance: PHP 7.4/8.2 Site Checks, strict image and sitemap checks, package preflight, non-deploying staging QA, Browser E2E and page-sheet artifact. Report any unavailable local checks honestly.
- No new tour product, operational claim, price, timing, inclusion, policy, dependency, cPanel upload or deployment is introduced. Owner keeps merge and release authority.


## Sprint 65 — shared public contact-channel consistency (PR pending)

**Commercial goal:** keep the hotel's existing phone and email paths dependable and consistent wherever guests decide to contact the hotel.

- Use `include/site-config.php` as the source of truth for the existing primary and alternate phone numbers and both email destinations currently shown on Contact and Directions.
- Replace hard-coded Contact, Directions and Rooftop telephone/email links with escaped configuration-backed output. Preserve all existing public destinations and display order; do not change WhatsApp, booking URL/token, form recipients or private server configuration.
- Add a PHP source contract and browser regressions for rendered contact-channel parity, HTTP response, and 320/390/1280/1440px horizontal overflow on the three changed journeys.
- Refresh `sitemap.xml` lastmod values for the changed public routes, then rely on `php scripts/generate-sitemap.php --check` in the existing Site Checks workflow.
- No additional contact channel, promise, privacy statement, menu fact, booking/policy wording or dependency is introduced. Owner retains production release approval.
- Acceptance: PHP 7.4/8.2 Site Checks, strict images, sitemap check, package preflight, non-deploying QA, full Browser E2E and screenshot/page-sheet review on the exact PR head.


## Sprint 66 — structured-data contact parity (PR pending)

**Technical SEO goal:** keep phone details in structured data aligned with the same public configuration that renders the contact links, without changing the current JSON-LD output.

- Make the shared Hotel entity telephone derive from the configured public phone value, preserving the existing `+91-7727047567` schema representation.
- Source ContactPage hotel telephone/email arrays from the existing primary/alternate display values and remove remaining hard-coded schema phone strings from the Restaurant page's redundant Hotel node.
- Extend the PHP source contract and add a browser regression for shared Hotel, ContactPage and Restaurant JSON-LD values. Keep schema node count, IDs, addresses, URLs, visible phone/email destinations and currently emitted values unchanged.
- No new structured-data claims, prices, reservation guarantees, policies or business facts. No deployment or local server configuration changes.
- Acceptance: exact-head PHP 7.4/8.2 Site Checks, head snapshot and Browser E2E, package preflight, non-deploying staging QA and screenshot/page-sheet evidence.


## Combined release — PR #140 (merged 2026-10-27)

PR #140 merged to `main` at `df812a33675a5d31d4ce388629038cf5a7960105`, completing Sprints 65–66. Site Checks, package preflight and non-deploying Staging QA passed on the merge SHA. Browser E2E was cancelled on the merge head, so screenshot evidence is still missing and must not be represented as a pass.


## Sprint 67 — open-ended custom Jaisalmer itinerary discovery (PR #141)

**Goal:** turn the six research-led themes on Car & City Tours into inspiration, not a perceived limit on what guests may ask about. Give guests a clear detail/planning page where they can combine interests and describe other places before preparing a WhatsApp enquiry.

- Add a standalone custom-itinerary page with travel period, time available, group size, preferred travel style, multiple interests and free-text places/requests.
- Build a readable WhatsApp draft in the browser; require the guest to review it and click explicitly before opening WhatsApp. Do not submit or persist form data to the server.
- Link the Car & City route-first choice and the inspiration section to the new page. Keep the current six themes and their enquiry-only disclaimers.
- Add source/browser contracts for field handling, multi-selection, message encoding, explicit-click behavior, accessible labels and 320/390/768/1280/1440px layout.
- Add canonical/breadcrumb/FAQ metadata and the page to the existing sitemap without altering unrelated entries.
- Do not invent services, prices, timings, routes, guide availability, access permissions or vehicle availability. Every selection is an enquiry prompt; the hotel must confirm feasibility, visitor rules, transport, guide, timings, applicable fees and final price.
- Acceptance: exact-head PHP 7.4/8.2 Site Checks, full Browser E2E/screenshot evidence, package preflight and non-deploying Staging QA. Owner-controlled live staging and production approval remain separate.


## Sprint 68 — accessible section navigation on experience detail pages (stacked PR #143)

**Goal:** make the full details already present on experience pages easier to scan, especially on mobile, without weakening the enquiry-only/owner-confirmation wording.

- Add a normal-flow, keyboard-accessible section index to the shared detail template for At a glance, optional Photo gallery, Itinerary, What's included / Before you book, and optional Questions.
- Render only links for sections available on the current record; use unique IDs and section headings. Do not make the index sticky or add a new dependency.
- Style the index using the existing brand tokens, visible focus, wrapping on small screens, and reduced-motion-safe behavior.
- Add source-contract and Browser E2E coverage for all six live experience detail pages, link/target parity, visible sections and 320/390/768/1280/1440px no-overflow.
- Preserve all registry content, image/alt values, schema, canonical URLs, WhatsApp/Trip-shortlist actions and prices-on-request. Do not fabricate missing operational facts or publish draft/hidden experiences.
- PR #143's implementation was merged into PR #141's feature branch; it will ship with #141 and is subject to #141's exact-head PHP 7.4/8.2 checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA. The stacked-branch merge is not a merge to `main`.


## Sprint 69 — editorial hero typography for tours and experiences (PR #144)

**Goal:** give the first screen of the Experiences, Car & City Tours and individual tour pages a more distinctive heritage-editorial character without adding another font download or changing operational claims.

- Strengthen the existing Source Serif 4 headline treatment with a clear display weight, balanced line-height and a small saffron/gold rule beneath the primary heading.
- Apply the shared treatment only to the primary hero headings on the Packages/Experiences hub, Car & City Tours page, live experience detail pages and the Fort walk detail page.
- Preserve the current responsive type scale, paragraph measure, hero photography, navigation, CTA destinations, schema, metadata, route content, current-price wording and all booking/contact behavior.
- Add browser coverage for the four public entry points at 320, 390, 768, 1280 and 1440px, including the displayed typeface, title weight, decorative rule and no horizontal overflow.
- Reuse the already approved Source Serif 4/Inter stack; do not add a dependency, font file, new business fact, service promise or external asset.
- Acceptance: exact-head PHP 7.4/8.2 Site Checks, Browser E2E and page-sheet evidence, package preflight and non-deploying Staging QA. This PR remains stacked on PR #141 until the parent sprint is merged and accepted.
