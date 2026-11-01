# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Latest state — 31 October 2026

| Item | Value |
|---|---|
| Verified main SHA | e858fa794a71bd68d1940bb49f6b382e2aec3dfd — PR #161 merged 31 October 2026 |
| Active implementation | Release provenance and cPanel staging delivery assurance (PR #162, successor to retained PR #160 after PR #161 merged) |
| Working branch | `feature/staging-release-provenance-delivery-20261030` (PR #162; base main `e858fa794a71bd68d1940bb49f6b382e2aec3dfd`) |
| Scope | Exact-SHA package marker, reproducible ZIP/manifest/checksum, 11-route guest-journey staging smoke, and read-only real-host responsive-image/cache diagnostics |
| Release boundary | Owner retains staging upload, merge and production approval; PR checks do not upload or submit enquiries |
| Exact-main CI | [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40853667258) · [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40853667379) · [Package](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40853667299) · [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40853667390) — all passed on 15afdf46506bdaf3799bde97f47e3296210bb898 |

## Recent guest-journey milestones

- **PR #145 — homepage and arrival hierarchy:** reduced repeated promotion, improved page rhythm and restored detailed arrival guidance.
- **PR #146 — room comparison:** two-to-three-room comparison dialog and preservation of existing room/rate destinations.
- **PR #147 — room shortlist to Contact:** registry-validated multi-room enquiry with safe retry continuity.
- **PR #148 — room comparison to Trip:** grouped room options enter the shared tab-only Trip tray while separate choices remain selectable.
- **PR #149 — Trip to Contact + experience details:** mixed shortlist Contact fallback, room-option server resolution and full experience-detail links from desert/guided-tour hubs.
- **PR #150 — dining to shared Trip:** same validated `rooftop-dining` selection across restaurant, rooftop and catalogue pages, through to Contact. The corrected head passed all four required workflows.
- **PR #158 — editorial media readiness:** safe local image metadata, responsive delivery contracts, shared ImageObject schema, visible captions and caption-gated Content Studio publishing. Site Checks, Browser E2E, package preflight and non-deploying Staging QA passed on the merged main SHA.

## Completed Sprint 72 — measured responsive hero-image budgets

The PR #150 Browser E2E artifact measured the local PHP site across 37 sitemap routes, with 149 unique rendered image URLs and 118 images reporting measurable Resource Timing size. These are local browser observations—not production/CDN or Core Web Vitals measurements.

The two largest responsive candidates were `img/responsive/v2/facade-1200.webp` at 416,592 bytes and `img/responsive/v2/courtyard-1200.webp` at 406,736 bytes. PR #151 added narrowly scoped WebP quality overrides of 74 for those two sources while leaving the manifest default quality at 82. Original photographs stay unchanged. The 350 KiB exact-file-size ceilings passed the final Browser E2E run.

## Sprint 73 — responsive image slots across high-intent journeys (completed)

PR #152 was merged as `2cea0d7e64b904969b4668e9010666f35ed64632`. It applies slot-accurate `sizes` hints across Home, Dining, Rooftop, Desert Safari and Guided Tours, extends responsive source delivery to the rooftop feature and related experience cards, and corrects the restaurant hero overline contrast. Original photos and public routes remain unchanged.

- The responsive generator correctly avoids upscaling undersized originals; Guided Tours' `img/j6.jpg` remains on its safe native fallback while a full-resolution source exercises responsive candidate delivery.
- Browser E2E previously caught two test-contract defects (an undersized image expected to have generated candidates, then an undefined `BASE_URL`). Both were corrected before merge. Do not treat those historical failed runs as current failures.
- The final merge-head Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA all passed on SHA `2cea0d7e64b904969b4668e9010666f35ed64632`.

## Sprint 74 — focused experience-action hierarchy (completed, PR #153 merged)

The post-merge page sheet revealed competing hero actions on individual experience pages (contextual enquiry, Add to Trip and a separate catalogue link) and the Guided Jaisalmer Tours hub (plan, compare and catalogue). This slice keeps a clear primary enquiry path, retains the session-only Add to Trip action for individual experiences, and preserves the catalogue route elsewhere in the site's navigation and lower-page discovery paths. On the Guided Jaisalmer Tours hub, the hero focuses on enquiry plus the in-page comparison anchor; the tour cards continue to offer details and Trip selection.

Regression coverage verifies action count and destination parity on the live experience routes, Trip-toggle synchronization at desktop and mobile sizes, and the guided-tour hub's primary and compare paths. No rates, operational claims, booking URLs, contact destinations or public routes are changed.

## Verification boundary

- Run and inspect exact-head PHP 7.4/8.2, Browser E2E/screenshots, package preflight and non-deploying Staging QA.
- Image payload and timing data from local Chromium is a comparative engineering baseline, not evidence of production transfer behaviour or Core Web Vitals.
- No deployment is authorized by CI.

## Remaining owner-controlled release blockers

| Priority | Item | Status |
|---|---|---|
| P1 | Host validation | HTTPS staging, deployed package SHA, PHP/Apache behavior, CAPTCHA and mail delivery require real-host verification. |
| P1 | Business-content approval | Menu/prices/hours, policies/privacy, check-in/reception, room rates and verified review quotations need owner approval. |
| P2 | Human design/accessibility review | Review exact-head artifacts at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology. |
| P1 | Production release | No deployment performed; owner approval remains mandatory. |


## Sprint 75 — photo-led, keyboard-accessible room comparison (PR #154 in review)

**Commercial goal:** help guests identify the exact room they are comparing without switching back and forth between card photos and a text-only comparison table.

- Add a photo row sourced from the first already-curated room carousel slide for each selected room. Reuse responsive image candidates and size thumbnails for the comparison column.
- Make scrollable comparison content a named, keyboard-focusable region and tell mobile guests how to reach columns beyond the viewport.
- Preserve floor, description, listed-feature rows, two-to-three-room limits, room IDs, session-only Trip behavior, Contact shortlist context, and the exact existing booking-engine destination.
- Add E2E coverage for photo/source/alt parity, region semantics and mobile horizontal scrolling.
- No new room/facility claims, rates, availability, routes, schema changes or deployment; owner retains merge and release approval.


### Sprint 76 — feature-by-feature room comparison (included in PR #154)

- Replace separate per-room feature lists inside the dialog with an aligned matrix: one row per unique listed feature, each room column labelled “Listed” or “Not listed”.
- Derive entries solely from the existing room cards/registry; do not imply an unlisted facility is absent.
- Keep photo, floor, description, availability, remove, Contact and Trip actions intact.

### Sprint 77 — show differences only (included in PR #154)

- Add an accessible checkbox that filters only feature rows whose listed status differs across the selected rooms.
- Keep non-feature rows and all existing actions in view; the live status announces the number of differing feature rows and clearly handles the no-difference case.
- Add E2E tests for two- and three-room matrix states, filter on/off, recalculation when a selected room is removed, the no-differences message, row visibility and the existing booking/Contact/Trip flows.

### Sprint 78 — filter-proof shortlist and reliable comparison entry (included in PR #154)

- Keep selected room names and remove controls in a compact shortlist above results, independent of whether directory filters currently hide the selected cards.
- Provide a single review action that remains usable from the shortlist, plus a clear-shortlist action. Do not reset or weaken the user's active search/filter state.
- Browser coverage selects two rooms, filters every card out, and verifies the shortlist can still reopen the same comparison and clear itself.

### Sprint 79 — focus continuity during comparison edits (included in PR #154)

- Restore keyboard focus to the next sensible room-removal control after a row-driven removal rebuilds the matrix.
- Keep focus usable when the last in-dialog removal clears the feature table; after clearing the directory shortlist, return focus to room search.
- Add E2E assertions for focus restoration and the filter-hidden shortlist journey.

### Sprint 80 — restore selected rooms to the directory (included in PR #154)

- Show a contextual “Show selected rooms” action only while selected cards are hidden by active directory filters.
- Clear search, floor and feature filters only when the guest activates that action, reveal the selected cards and focus the first selected room's compare control.
- Re-evaluate recovery-action visibility after each filter update as well as each shortlist edit.
- Verify this differs from clearing the shortlist: selected room choices remain intact.


## Sprint 82 — shared experience hero preload and viewport audit (PR #159, completed)

- Shared one verified responsive-candidate resolver between rendered image `srcset` attributes and responsive preload descriptors.
- Added high-priority responsive preloads to shared experience detail pages without changing routes, contact destinations or fallback behavior.
- Expanded image auditing across 37 sitemap routes at mobile and desktop sizes, with transfer-byte accounting keyed to viewport identity and bounded image-decode waits.
- Passed Site Checks (PHP 7.4/8.2), Browser E2E/screenshots, package preflight and non-deploying Staging QA on `732ff6a498cc43f7b346f74a4af3a21acc526941`.

## Sprint 83 — experience hero source-fit and mandatory responsive candidates (PR #159, active)

- Replace the 370px-wide half-day tour thumbnail used as a full-bleed hero/card image with the existing 1366px-wide Jaisalmer haveli photograph.
- Replace the under-resolved sleep-under-the-stars hero with the existing 1280px-wide dusk-camp image and update its alt text/caption to describe the selected asset accurately.
- Declare both sources as required heroes in the responsive image manifest, require all configured 480/800/1200 candidates in the generator, and add 1200px byte budgets.
- Add browser assertions for responsive candidate selection and at least 90% source-pixel coverage of the rendered hero slot at 1x density.
- Add PHP/Python contracts tying experience records to the required source allowlist, minimum intrinsic width and generated variant paths.
- Validate the exact new head through Site Checks, Browser E2E, package preflight and non-deploying Staging QA. No merge or deployment.


## Sprint 84 — offscreen image priority and responsive payload budgets (PR #159, active)

- Latest completed Browser E2E evidence identified the Journal card image `j7` as below the initial viewport at both 390px mobile and 1280px desktop, while the markup still preloaded it and assigned high fetch priority. Sprint 84 moves it to lazy loading and adds a regression assertion.
- The same 37-route/two-viewport audit measured `facade-1200.webp` at 278,254 bytes and `courtyard-1200.webp` at 269,602 bytes. Introduce a bounded highest-quality-that-fits encoder and reduce both manifest caps to 250,000 bytes; the route-wide audit must then report zero resources above 250 KiB.
- Re-run exact-head Site Checks (PHP 7.4/8.2), Browser E2E and screenshots, package preflight and non-deploying Staging QA before considering Sprint 84 validated.
- Release boundary unchanged: owner retains merge approval; no cPanel upload, staging deployment or production deployment.

 
## Sprint 84 — offscreen image priority and responsive payload budgets (completed in PR #159)

- Removed the below-fold Journal lead-card preload/high-priority policy while keeping responsive loading available on scroll.
- Added a quality-ladder encoder for the five manifest-owned hard byte limits. The final artifact reports all five budgets passing, with 89,304 bytes of combined headroom.
- The final image audit checked 37 sitemap routes at 390px and 1280px, recording 272 unique image/viewport URLs and 397 measurable image-resource observations. No selected resource exceeded the 256,000-byte review threshold.
- Exact-head Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA all passed on `42e9922c52aad0ea95043d05d52d0d1536c9e034`. Browser E2E recorded 622 passed tests; measurements remain synthetic local Chromium evidence, not production field Core Web Vitals.

## Sprint 85 — source-size-accurate carousel delivery and truthful image auditing (active in PR #159)

- The Sprint 84 image report showed the room carousel's 33vw hint underestimating a 563px desktop slot, causing a 480px derivative to be selected where the available 800px source should be used.
- Correct shared variant-specific `sizes` values for room, experience/editorial and rooftop carousels.
- Derive source-pixel coverage from the selected responsive width descriptor, not density-corrected `naturalWidth`; keep the latter as a separate diagnostic.
- Classify initial-viewport image visibility using both horizontal and vertical intersection in the image audit and performance baseline.
- Revalidate all image, performance, PHP, package and staging contracts on the new exact head before the next sprint.


## Homepage premium storytelling — Milestones 1 and 2 (PR #161)

- Existing homepage foundations were retained: the configured booking engine, registered rooms, rooftop dining, desert-experience cards, arrival guide, gallery, FAQs, footer CTA and session-only Trip/Contact journeys.
- The new homepage city-discovery section links to the existing guided Jaisalmer and car-tour destinations; it does not create new tour products or imply availability.
- The new planning-confidence layer explains the verified Fort vehicle restriction, room-specific differences and date-dependent tour confirmation. It is not a substitute for verified guest reviews; review proof remains owner-data gated.
- The existing Trip planning panel is repositioned after the core discovery sequence.
- PR [#161](https://github.com/tchouhanjsm/garh-jaisal-website/pull/161) is open for review. Exact-head Site Checks, Browser E2E/screenshots, sitemap parity, package preflight and non-deploying Staging QA must pass before merge. Human screenshot review remains required; local browser timings are not field Core Web Vitals.
- No cPanel upload, staging deployment or production release is included.


## Homepage Phase 3 — direct-booking conversion (PR #161)

- Added one explicit room-section action that opens the configured booking engine; it does not claim to preselect a specific room or guarantee a rate.
- The hero, room-section and closing booking CTAs now have stable CTA IDs/locations in the existing outbound-booking event.
- Regression coverage verifies both approved GA4 destinations, first-touch allowlisted campaign attribution and exclusion of private query values (email/topic).
- Fixed the 320px compact hero collision with the fixed mobile action dock by restoring the previously tuned compact type scale and spacing.
- Updated the shared CTA test to reflect the approved closing headline.
- No new booking integration, offer, review, pricing promise, public route or deployment is introduced.


## Phase 4 — Premium Directions and arrival experience (PR #161)

**Guest outcome:** visitors can understand the Fort's last-mile arrival before travelling, give a driver the right drop-off point and continue to the existing direct-booking path.

- Refresh the full Directions page as one coherent visual system: premium arrival hero/quick facts, in-page navigation, parking-to-door sequence, transfer choices, rickshaw restriction notice, walking/mobility clarity, map panel, contact details, FAQs and closing conversion band.
- Add an on-page driver note using only verified arrival details: Fort Kotari Para address, vehicle restriction inside the Fort, Fort Parking drop-off, approximately 300 metres / 5–6 minutes on foot, uneven cobbles and short stair sections, and the exact existing Google Maps hotel pin.
- Add an accessible copy action with clipboard support and a legacy fallback. The address and arrival instructions remain readable when JavaScript is unavailable.
- Instrument the existing arrival-page booking button with stable CTA metadata and extend the outbound-intent test. Keep attribution allowlisted; do not forward email, topic or other private query values, and do not emit a reservation-success event.
- Add a PHP source contract, expanded multi-viewport E2E, progressive-enhancement coverage and the Directions sitemap freshness update.
- Preserve the public route, canonical/schema, map pin/embed, phone/WhatsApp/enquiry destinations, business facts and original photographs. No cPanel upload, staging deployment or production release is included.
- Exact-head CI must pass after the batch commit; owner visual review remains a release gate.


## PR #161 — completed homepage and arrival milestones

- PR #161 merged on 31 October 2026 at e858fa794a71bd68d1940bb49f6b382e2aec3dfd.
- Delivered premium homepage storytelling, the three measured homepage booking CTAs, privacy-safe campaign attribution and the complete Directions visual/arrival experience with a driver-ready copy action.
- The merge is repository integration only. It is not evidence of a cPanel upload, live staging verification or production deployment.

## PR #162 — release provenance and cPanel staging delivery assurance (successor to PR #160)

- PR #160 was closed without merging after PR #161 merged. PR #162 carries the retained Sprint 86 release-provenance work as the only open implementation PR on the existing branch.
- Preserve the generated exact-SHA release marker, package manifest/checksum, no-store/noindex marker rules and external staging read-only image/cache diagnostics.
- Sprint 87: widen the local staging smoke matrix to 11 guest-facing routes and assert the merged homepage booking paths plus the Directions driver note, exact map pin and arrival booking CTA at mobile/desktop widths.
- Sprint 88: exercise the actual package builder in a temporary clean Git checkout; extract the archive marker, verify marker/manifest/archive/sidecar hashes, assert exclusion of the server-only config, and check byte-identical ZIP output for repeat builds of one commit.
- Sprint 89: exact-head validation of PHP/site contracts, deployment-package unit/preflight, local staging route smoke, and Browser E2E/screenshots. Real-host image HEAD/cache verification remains owner-dispatched and read-only.
- No cPanel upload, external staging deployment, enquiry submission or production release is part of this PR.


### Staging QA RCA — PR #162 initial visibility audit

The first expanded non-deploying staging run exposed an existing Playwright callback-boundary bug: the image metadata callback executed in the browser context but referenced the Node-side `initialImageVisibility` array as an undeclared closure variable. Pass the captured array explicitly as the second `locator.evaluateAll` argument and read its zero-based index inside the page context. This preserves the measured first-viewport state for high-priority image and real-host cache-probe decisions; it does not change page rendering or guest-facing behavior.


### Responsive image RCA — Rooftop and Car & City Tours desktop slots

The expanded staging route matrix exposed three large desktop image slots whose srcsets stopped at 800w. Rooftop's banner source is 1180px wide, so a 1200px derivative would be an invalid upscale; the shared PHP image resolver adds the verified original source as the native-width fallback when the best generated derivative covers less than 90% of the source width. The Rooftop hero rest5.jpg and Car & City Tours jcity.jpg sources are no wider than 1200px, so requiring 1200w derivatives for either was incorrect; their originals are the truthful native-width candidates. Smaller 480/800w derivatives remain generated where they can be made without upscaling. The browser contract verifies the actually selected candidates and at least 90% slot coverage at 1280px; the staging smoke retains the sitewide threshold. Original image files, aspect ratios, crop intent, text and public URLs are unchanged.


## Sprint 90 — source-aware responsive requirements and native-width delivery (PR #162)

The deployment, browser and staging workflows were blocked by two false manifest requirements: img/responsive/jcity-1200.webp and img/responsive/rest5-1200.webp had hard byte budgets even though neither original is wider than 1200px. The encoder correctly returned skipped-no-upscale; the manifest then treated those safe skips as fatal budget failures.

- Changed required-hero coverage to require only targets strictly below each decoded original width.
- Added manifest validation that rejects hard byte budgets on any width target that cannot be generated without upscaling.
- Removed both impossible jcity-1200 and rest5-1200 budgets while retaining their generated smaller derivatives and the PHP resolver's native original-source fallbacks.
- Updated desktop Browser E2E to assert the original rest5.jpg and jcity.jpg URLs and their true intrinsic width descriptors, with the same 90% minimum source-pixel coverage.
- Added PHP parity coverage proving the Rooftop and Car & City Tours responsive srcsets and preloads both include the same native-source candidates.
- The original photograph, crop, page copy, routes and booking destinations are unchanged. This is delivery-pipeline correctness, not a request to upscale or replace the photo.


## Phase 3 — High-resolution responsive delivery and exact preload parity (PR #162)

**Finding:** the 37-route/two-viewport image audit showed that multiple hero sources stopped at 1200w. Chromium therefore selected 1366–2000px originals for wide desktop slots, with several selected resources above the 250 KiB review threshold. Homepage and Gallery had independently assembled preload lists, creating a risk of preload/rendered-srcset divergence.

- Add source-specific 1280w candidates for 1366px photos and 1280/1440/1800w candidates for selected 1600–2000px hero masters. Do not increase the global derivative count for all image sources.
- Enforce a 250,000-byte maximum on each additional high-resolution WebP with the existing adaptive quality ladder. Preserve original assets, aspect ratios, color profiles and the no-upscale rule.
- Extend the shared PHP candidate resolver to approved responsive widths and use its candidate list for Homepage, Gallery and Rooftop preload descriptors as well as rendered image srcset.
- Use the reviewed 1600×575 roofslide.webp panorama for the full-bleed Rooftop hero; keep rest3.jpg and jcity.jpg in maximum-1000px layouts so the 900px originals are not stretched.
- Add source-specific generator validation, per-source coverage reporting, browser candidate/preload parity and PHP contracts.
- Local Chromium measurements are engineering evidence only, not field Core Web Vitals. No cPanel upload, external staging deployment, enquiry submission or production release is included.


### Phase 3 RCA — source-width-aware facade target

The first image-generator run correctly rejected 1440w for `img/v2/facade.jpg`. Its decoded dimensions are 1333×2000, so 1280w is the largest valid added candidate. Removed 1440w/1800w target entries and budgets for this one source; all 2000px masters retain 1280/1440/1800 and other 1366px masters retain only 1280. The generator's validation is unchanged: no upscale candidate or byte budget is accepted.


### Phase 3 payload-budget calibration

The first generated 1280w facade candidate missed the 250,000-byte limit by 7,328 bytes at the pre-existing quality floor of 58. Lower the adaptive floor to 56 and cap this single candidate at 256,000 bytes (250 KiB); all other additional high-resolution candidates remain at 250,000 bytes. The source-pixel target and source photo are unchanged.


## Phase 4 — Source-aware image delivery and preload parity (PR #162)

**RCA:** the PR #162 browser artifact measured three oversized Swiss Tent gallery downloads: `Tent.jpeg` (327,101 B), `CampingInJsm.jpeg` (307,943 B) and `tent_deluxe.jpeg` (287,688 B). Each source is 1600px wide. The page advertises a 95vw desktop slot; at 1280px that is approximately 1216px, slightly wider than the existing 1200w derivative. Chromium therefore chose the 1600w JPEG originals. The remediation adds budgeted 1280w and 1440w derivatives for those three sources and retains the 250,000-byte per-candidate cap. It does not weaken the route-wide 256,000-byte (250 KiB) audit threshold.

- Corrected `target_widths_for_source()` and `required_hero_variant_paths()` so the explicit width list is honoured when a caller supplies a minimal manifest fragment without `target_widths`.
- Added source-specific 1280w/1440w candidates and hard budgets for the three Swiss Tent gallery photos. Existing JPEG originals and editorial ordering/alt text/captions remain untouched.
- Removed duplicated Room and Gallery preload assembly. Both pages now use the same validated PHP candidate resolver as their actual hero `img` markup. The Room preload uses the optimized source actually rendered by the page; Gallery uses its rooftop-dusk source. This guarantees identical candidate lists and sizes hints instead of independently maintained three-width lists.
- Strengthened LCP preload parity and the Gallery 1280w source-selection assertion.
- Updated editorial media readiness to permit only an exact same-origin native source as the final fallback, and only when its width descriptor matches the verified intrinsic source width. All other srcset paths remain restricted to validated responsive derivatives.
- Expanded the Swiss Tent mobile/desktop carousel source matrix. The general image payload audit remains strict and must report zero selected resources above 250 KiB and zero image slots below 90% source-pixel coverage.
- Validation required on the exact final PR head: PHP 7.4/8.2, responsive image generator/budget tests, Browser E2E/screenshots, package preflight/reproducibility and 11-route non-deploying Staging QA. No live-host deployment or production release is part of this phase.


### Phase 4 sitemap freshness RCA

- The exact-head PHP 7.4/8.2 sitemap contract correctly identified that `room.php` had changed on 31 October while its committed `lastmod` remained 29 October. Updated only the Room entry to 31 October 2026; no bulk sitemap date refresh or unrelated URL date change was applied.


### Package inventory RCA — source-specific image candidates

- The first package preflight after adding Swiss Tent 1280w/1440w derivatives correctly rejected them because `get_generated_responsive_paths()` still built its allowed-path set from global target widths only. It now validates `source_target_widths` against the source allowlist and unions those widths for that source only. Unit coverage verifies the 1280w/1440w budgeted assets are selected without permitting arbitrary output files.
- Browser E2E's first run also caught a malformed escape in the added LCP preload-candidate normalizer. Replaced the slash regex with explicit leading-slash normalization; exact candidate-list equality remains enforced.


## Phase 5 — Interaction-aware carousel image delivery and accessibility (PR #162)

**Root cause from Browser E2E run 857:** the source-selection test located one of the later Swiss Tent carousel images after the carousel's DOMContentLoaded enhancement had intentionally hidden inactive slides. It then called `scrollIntoViewIfNeeded()` on a hidden slide and timed out. The image resolver, responsive candidate list and 90% coverage threshold were not the cause. The test did not reproduce the guest interaction required to reveal the target photo.

- Rework the mobile/desktop source-selection matrix to locate the target slide index and advance through the same Next control guests use. Only after the target is visible does the test measure currentSrc, width descriptor, sizes and actual rendered width.
- Enhance the shared carousel so an explicitly selected slide is decoded after a click or arrow-key action. Keep initial loading lazy, do not preload hidden/adjacent images on page load, and do not auto-advance.
- Extend browser coverage for active/inactive hidden and aria-hidden state, exactly one visible slide, loaded selected images, keyboard navigation, wraparound and the live Photo N of M status.
- Add a PHP source contract for the shared renderer's meaningful alt text, path validation, intrinsic dimension helper, lazy loading, asynchronous decoding and hidden-until-enhanced controls; run it in PHP 7.4 and 8.2 CI.
- Preserve the no-JavaScript behavior: all curated slides remain visible and controls remain hidden when the script does not execute.
- Preserve source photographs, order, captions, routes, responsive candidate budgets, preload parity, booking destinations and site content. This phase changes carousel interaction/loading behavior only after explicit guest input.
- Fresh exact-head Site Checks, Browser E2E/screenshots, package integrity/reproducibility and non-deploying Staging QA are required. No cPanel upload or production deployment is authorized.

## Premium visual foundation — repository-first vendor batch (1 November 2026)

- Captured the exact source baseline at `d15af654b411266a4380a3b839c698959e8412bd`, after PR #162. At capture time there were no open PRs.
- Scope is strictly `tchouhanjsm/garh-jaisal-website`. The separately hosted `hotelgarhjaisal.com` domain is explicitly outside this vendor engagement; no live domain browsing, crawling, probing or screenshots were performed.
- Added a repository baseline record and extended the canonical visual tokens with semantic surface, text, border, action, spacing, type and motion roles while preserving pre-existing token names.
- Refined shared story composition, editorial section rhythm, cards and shared navigation/footer chrome. Existing PHP route templates, verified business content, booking destinations, public metadata and media manifests remain unchanged.
- Added a browser contract for the new semantic token roles and extended the brand contrast contract to verify semantic aliases and presence of shared roles.
- This is not a selector-deletion cleanup. Existing route CSS and historical responsive fixes have been preserved because safe consolidation requires selector usage analysis, computed-style comparison and exact viewport screenshot parity first.
- Required exact-head verification: brand/token PHP contract, full PHP Site Checks (7.4/8.2), sitemap/publication checks, Browser E2E/page sheets and responsive screenshots at 320/390/768/1280/1440px, responsive-image generator/review, package preflight/reproducibility and non-deploying local Staging QA.
- No real-host QA, package upload, staging deployment, form submission, merge or production deployment is included. Owner visual review and release approval remain required.


## Canonical visual foundation consolidation — continuation (1 November 2026)

- Root cause: the initial premium-token implementation was repeatedly appended during repair attempts. css/tokens.css contained three top-level :root declarations and three copies of the shared component contracts; repeated declarations created avoidable cascade ambiguity and made further edits unsafe.
- Consolidated the original palette and semantic roles into one top-level token block. Existing token names remain compatible; legacy responsive :root overrides under media queries remain in place.
- Retained one final shared component-contract layer after legacy responsive overrides so its intended precedence is preserved. Removed only repeated copies, retaining the image fallback color and compact-section spacing contract once.
- Extended the brand/token check to reject duplicate semantic-token declarations, duplicate top-level root blocks and repeated shared selectors. The one base button rule plus one mobile touch-target override are explicitly permitted.
- Preserved route CSS, page templates, photography, booking/contact destinations, SEO metadata, analytics behavior and release/package configuration.
- Exact-head PHP/Site Checks and Browser E2E/screenshots at 320/390/768/1280/1440px remain required CI and human-review gates. No live-host inspection, package upload, staging QA dispatch, merge or deployment is authorized.