# Project state and release gates

Last updated: 30 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The active homepage batch combines premium art direction, a complete heritage-to-city storytelling sequence and direct-booking conversion improvements. It uses first-party photography, transparent arrival/booking information, existing room/experience registries and privacy-safe CTA attribution through the shared analytics router and Trip-to-Contact journey. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline after PR #155

- main is at merge commit a6089e80cf1a1895f7280504d9ba377924c991f1, merging PR #155 on 28 October 2026.
- PR #154 delivered room-comparison decision support; PR #155 delivered the editorial publication contract, five-topic taxonomy, registry-driven related-guide ranking and browser regression coverage.
- PR #155 final head 7f69a5a94376c7a985c316f167d56b1c1418aff5 passed Site Checks (PHP 7.4/8.2), Browser E2E/screenshots, non-deploying Staging QA and deployment-package preflight. The owner-reviewed staging package build was intentionally skipped.
- CI checks prove repository-local contracts only. No cPanel upload, staging deployment or production release was performed as part of that verification.

## Completed phase — Sprint 72: measured hero-image payload budgets

- The PR #150 local Chromium artifact audited 37 sitemap routes, 149 distinct rendered image URLs and 118 images with measured transfer size. These are local observations, not production/CDN or Core Web Vitals measurements.
- PR #151 reduced the two largest reviewed responsive candidates with narrowly scoped quality 74 overrides while preserving the manifest's default quality 82 and 350 KiB candidate size ceilings. Original photographs remain unchanged.

## Completed phase — Sprint 73: responsive image slots across high-intent journeys

- PR #152 applied slot-accurate responsive `sizes` hints to Home, Dining, Rooftop, Desert Safari and Guided Tours templates/cards; added existing rooftop sources to the manifest; and retained the generator's no-upscale behavior for undersized originals.
- The corrected PR head fixed the initial E2E fixture's missing `BASE_URL`; the corrected merge head subsequently passed Browser E2E and screenshots on `2cea0d7e64b904969b4668e9010666f35ed64632`. The earlier failure is resolved, not a current release blocker.
- The restaurant hero overline now uses the existing light-gold treatment with a WCAG AA contrast regression assertion. Original photos, crops, alt text, public routes and business copy remain unchanged.

## Completed phase — Sprint 74: focused experience-action hierarchy (PR #153 merged)

PR #153 merged at `0f0ebe35c20e3a82d6c01d1ab6807cf3802395d8`. It keeps experience detail heroes focused on contextual enquiry plus Add to Trip, while preserving lower-page shortlist routes and the Desert Safari/Guided Tours hub alternatives.

## Completed milestone — PRs #154 and #155

- **PR #154:** room comparison decision support with a feature matrix, differences-only filter, shortlist recovery and focus continuity. Existing room IDs, photos, booking destinations and enquiry routes were retained.
- **PR #155:** editorial publication/indexability checks, committed sitemap parity, responsive reader UX, five shared topic groups, topic-ranked related-guide discovery and route/schema/browser contracts.
- **Release authority:** all merges and staging/production release decisions remain with the owner.

## Active phase — Sprints 91–120: SEO readiness, Journal discovery and first-impression quality (PR #156)

- **Sprint 91 — registry integrity:** validate editorial slugs, statuses, topic assignments and live route ownership across the complete merged registry.
- **Sprint 92 — crawl policy:** enforce the production sitemap directive, safe robots rules, canonical-origin consistency and valid sitemap lastmod entries.
- **Sprint 93 — metadata/canonical parity:** check one canonical, one useful title and description, correct canonical route and live-only indexability on every published guide.
- **Sprint 94 — social metadata:** verify Open Graph and Twitter title, description, URL, card and image fields are present and consistent with the rendered page.
- **Sprint 95 — structured data fidelity:** check BlogPosting dates and canonical references, visible-content alignment and breadcrumb schema integrity.
- **Sprint 96 — topic graph integrity:** prevent self-links, duplicate related destinations, invalid taxonomy IDs and references to non-live editorial records.
- **Sprint 97 — readable anchors and citations:** validate TOC targets, section IDs, descriptive related-link labels, official-source links and safe external-link attributes.
- **Sprint 98 — internal route health:** verify referenced local PHP routes resolve and the sitemap contains every live editorial route without duplicate destinations.
- **Sprint 99 — reader UX regression:** exercise Journal/article discovery, keyboard reachability and horizontal-overflow checks at 320, 390, 768, 1280 and 1440px.
- **Sprint 100 — evidence and release handoff:** produce a rendered route-level JSON audit artifact, run the new PHP contract in both supported CI PHP versions, and document exact-head checks and release boundaries.
- Acceptance: Site Checks, sitemap parity, the editorial graph/readiness contracts, Browser E2E and screenshots, package preflight and non-deploying Staging QA must be evaluated on the exact final PR head. Never merge or deploy automatically.
- **Sprint 101 — searchable Journal:** add in-page search across visible guide-card copy while keeping every public link in server-rendered HTML.
- **Sprint 102 — topic filtering:** combine text search with the shared five-topic taxonomy from the same editorial registry.
- **Sprint 103 — accessible result status:** announce result counts through a polite live region and make the no-match state clear.
- **Sprint 104 — filter recovery:** provide a clear action that resets query/topic controls and restores focus to the search input.
- **Sprint 105 — keyboard and touch usability:** use explicit labels, visible focus and touch-sized controls without a JavaScript dependency.
- **Sprint 106 — Fort-guide prominence:** keep the Fort guide first in the unfiltered archive and preserve its established fragment and URL.
- **Sprint 107 — stable SEO identity:** keep archive canonical, metadata, fragments, card destinations and sitemap unchanged by local filtering.
- **Sprint 108 — predictable matching:** normalize Unicode compatibility forms, case and repeated whitespace before matching visible card copy.
- **Sprint 109 — filter/navigation interoperability:** reset filters before following a topic fragment so its actual card target remains visible.
- **Sprint 110 — progressive-enhancement evidence:** add PHP source contracts and Playwright coverage for search/topic intersection, no-match recovery, no-JavaScript fallback and mobile overflow.
- **Sprint 111 — cross-page first impression:** verify the home, rooms, packages, dining, directions, contact, Journal, gallery and about routes render successfully with a meaningful document title, visible H1 and canonical URL.
- **Sprint 112 — first-viewport action clarity:** verify the homepage exposes a visible primary booking action and that the hero image loads successfully in Chromium.
- **Sprint 113 — mobile layout integrity:** check high-intent routes at 320px and 390px for document-level horizontal overflow.
- **Sprint 114 — responsive route consistency:** confirm desktop routes at 1280px maintain the shared page identity contract and no broken canonical destinations.
- **Sprint 115 — image delivery evidence:** inspect visible image elements for load failures and record the actual currentSrc rather than inferring delivery from filename extensions.
- **Sprint 116 — metadata and heading baseline:** catch blank titles, missing canonical links and pages that render without a visible primary heading.
- **Sprint 117 — primary action resilience:** assert the homepage booking CTA is present and points to the configured booking destination without triggering a booking.
- **Sprint 118 — navigation route smoke test:** visit representative high-intent page families independently so one template failure is isolated to a route.
- **Sprint 119 — performance measurement discipline:** capture navigation timing entries for investigation; do not claim a sub-three-second real-user result from local CI timing.
- **Sprint 120 — first-impression release gate:** run the cross-page Playwright checks on each PR head and review screenshot artifacts before visual approval.

## Active phase — Sprints 121–130: Visual asset and typography evidence (PR #157)

- **Sprint 121 — route image inventory:** inspect first-party and external image sources on high-intent pages.
- **Sprint 122 — rendered image integrity:** record visible image load state, intrinsic dimensions and the selected responsive `currentSrc`.
- **Sprint 123 — alternative-text contract:** detect missing `alt` attributes while allowing explicitly decorative empty alternatives.
- **Sprint 124 — image geometry:** capture rendered box and intrinsic aspect ratio to identify suspicious distortion/cropping candidates.
- **Sprint 125 — responsive source evidence:** record `src`, `srcset`, `sizes` and `currentSrc` rather than assuming a candidate is used.
- **Sprint 126 — font declaration audit:** record computed display/body font stacks and font readiness per route.
- **Sprint 127 — transfer diagnostics:** match resource timing entries to image/font requests when the browser exposes timing data.
- **Sprint 128 — route coverage:** audit Home, Rooms, Packages, Dining, Directions, Contact, Gallery and Journal.
- **Sprint 129 — machine-readable evidence:** write JSON under `artifacts/image-accessibility/` for the existing CI artifact uploader.
- **Sprint 130 — evidence-led optimization:** use the resulting report and screenshot review to choose specific asset, crop or font changes; do not replace photos based on filename guesses.

## Active phase — Sprints 131–140: Responsive hero delivery and mobile first impression (PR #157)

- **Sprint 131 — image-slot truth:** compare the declared sizes slot to the actual rendered hero geometry.
- **Sprint 132 — Gallery hero correction:** align the full-bleed hero sizing hint with its 100vw rendered slot so desktop does not select a smaller source.
- **Sprint 133 — responsive hero preload:** preload the same srcset and sizes candidate set used by the Gallery hero, with high fetch priority.
- **Sprint 134 — preload consistency:** assert that preload candidates and rendered image candidates match to avoid duplicate or mismatched requests.
- **Sprint 135 — 320px facts hierarchy:** place Home quick facts in two balanced columns with the final item spanning the width on small phones.
- **Sprint 136 — compact-phone readability:** verify minimum item width, touch-safe layout and no horizontal overflow at 320px.
- **Sprint 137 — first-viewport asset contract:** fail browser testing if Gallery hero source selection regresses to the 45vw candidate or drops below expected width.
- **Sprint 138 — image/accessibility regression:** retain alt-attribute, visible-image integrity and font readiness coverage across eight routes.
- **Sprint 139 — evidence-led performance:** distinguish candidate review thresholds from hard budgets; do not degrade photo quality to win bytes blindly.
- **Sprint 140 — release verification:** require exact-head PHP 7.4/8.2, Browser E2E, package preflight and non-deploying Staging QA. Human screenshot review remains required for final visual approval.

## Active phase — Sprints 141–150: Compact-phone first-screen completeness (PR #157)

- **Sprint 141 — cascade integrity:** move the 320px hero rules into the homepage story stylesheet that owns the stronger selectors.
- **Sprint 142 — fact hierarchy:** render Stay and Dine in two readable columns; let Explore span the full second row.
- **Sprint 143 — viewport geometry:** constrain the smallest-phone hero to the usable first screen instead of inheriting the 620px desktop minimum.
- **Sprint 144 — action-dock clearance:** keep hero proof points above the fixed mobile CTA dock without hiding booking/contact actions.
- **Sprint 145 — compact typography:** reduce the smallest-phone heading/body scale and vertical gaps while preserving contrast and booking intent.
- **Sprint 146 — layout semantics:** retain the three verified facts and meaningful DOM order.
- **Sprint 147 — browser diagnostics:** report fact-bottom, dock-top and hero-height values on failures.
- **Sprint 148 — regression guard:** keep strict two-column and first-screen clearance assertions.
- **Sprint 149 — exact-head validation:** rerun Site Checks, package preflight, non-deploying Staging QA and Browser E2E on the updated SHA.
- **Sprint 150 — visual review:** retain 320/390/768/1280/1440 screenshot review; CI timing is not proof of field three-second performance.

## Active phase — Sprints 151–155: Reduced-motion and first-screen accessibility (PR #157)

- **Sprint 151 — reduced-motion parity:** remove homepage discovery-card transforms and CSS transitions when `prefers-reduced-motion: reduce` is active; retain non-motion hover affordances.
- **Sprint 152 — actual preference regression:** emulate the browser reduced-motion preference in Playwright and assert computed transitions and hover transforms instead of merely checking that a media query exists.
- **Sprint 153 — focused selector contract:** keep the first-screen typography assertion scoped to the visible homepage hero, so a later section label cannot accidentally satisfy it.
- **Sprint 154 — responsive no-overflow gate:** retain document-width assertions across the configured mobile/tablet/desktop browser projects.
- **Sprint 155 — release evidence integrity:** use exact-head workflow results only, and keep local timing reports separate from field performance claims.
- **Remediation after Browser E2E run 739:** the captured 320px screenshot confirmed the eyebrow was rendered at 11.52px because the final compact-phone override used `0.72rem`. Keep the existing minimum-size assertion and set the homepage hero eyebrow back to the established `0.8125rem` (13px at the root scale); scope the test to the visible primary hero eyebrow instead of the first arbitrary `.eyebrow` in the DOM. Verify the new exact head before marking the browser gate complete.

## Active phase — Sprints 156–170: Gallery image UX and sitewide motion preferences (PR #157)

- **Sprint 156 — keyboard-first image browsing:** support Left/Right arrow keys in the native Gallery dialog while keeping the existing Previous/Next buttons and direct-image link fallback.
- **Sprint 157 — position feedback:** announce the active item as “Photo N of M” through a polite, atomic status region so keyboard and screen-reader users can track movement.
- **Sprint 158 — predictable focus recovery:** return focus to the image that opened the dialog after Close/Escape; if filtering makes that trigger hidden, recover focus to the active filter instead.
- **Sprint 159 — neighbor readiness:** after a visitor explicitly opens a photo, prewarm only its adjacent full-size images and decode them where supported. Do not preload the complete gallery during initial page load.
- **Sprint 160 — source fidelity:** continue using the exact curated original linked to each thumbnail for the full-size viewer; do not remap rooms, experiences or photos by filename.
- **Sprint 161 — shared motion preference layer:** load a small shared accessibility stylesheet after route/component styles on pages using the common page head.
- **Sprint 162 — reduced-motion scroll behavior:** replace smooth scrolling, CSS animations and transitions with non-animated behavior when the user requests reduced motion.
- **Sprint 163 — no-motion hover state:** remove nonessential card, navigation, CTA and Gallery hover transforms under reduced motion while preserving content, focus states and active/inactive colors.
- **Sprint 164 — route-family browser evidence:** assert the shared preference stylesheet loads last and suppresses transitions across Home, Rooms, Dining and Gallery.
- **Sprint 165 — rendered hover contract:** emulate reduced motion and assert the Gallery image does not scale on hover; do not rely on stylesheet-marker checks alone.
- **Sprint 166 — source contract:** test stylesheet existence, shared-head inclusion/order and reduced-motion declarations in PHP CI for PHP 7.4 and 8.2.
- **Sprint 167 — Gallery interaction regression:** test arrow-key wraparound, live position updates, full-size image resolution, Escape close and focus restoration in Chromium.
- **Sprint 168 — progressive enhancement:** preserve all curated images, native links and category navigation when JavaScript is disabled.
- **Sprint 169 — review evidence:** inspect latest exact-head screenshots at mobile and desktop widths and verify the lightbox remains inside the viewport.
- **Sprint 170 — release gate:** rerun Site Checks, Browser E2E/screenshots, non-deploying Staging QA and package preflight on the same latest PR head. CI evidence is not a production deployment or a field Core Web Vitals result.

## Active phase — Sprints 171–185: Responsive image decoding and cascade-contract recovery (PR #157)

- **Sprint 171 — correct the visual-system contract:** exclude the shared accessibility preference layer from the route-stylesheet group that must precede tokens; keep a separate assertion that the preference layer loads last.
- **Sprint 172 — keep the cascade strict:** verify the preference stylesheet follows route styles, shared tokens and conversion components rather than moving it earlier to satisfy the old assertion.
- **Sprint 173 — homepage imagery:** add explicit asynchronous decoding hints to image elements that previously omitted them while retaining high-priority hero hints.
- **Sprint 174 — package cards:** make asynchronous decoding explicit on lazy-loaded package images.
- **Sprint 175 — desert discovery:** apply the same decode policy to desert-hub hero and card images without replacing their source or changing slot sizes.
- **Sprint 176 — guided and car-tour cards:** make image decode behavior consistent across travel-discovery cards.
- **Sprint 177 — shared header assets:** use the same decode hint for the persistent brand and contact imagery.
- **Sprint 178 — experience template:** align the shared experience hero with existing high-priority/asynchronous image declarations; retain responsive srcset and sizes.
- **Sprint 179 — editorial template:** apply the decode hint to the shared guide hero without changing visible editorial content, canonical routes or structured data.
- **Sprint 180 — reusable hero component:** keep asynchronous decoding consistent for consumers of the shared hero partial.
- **Sprint 181 — route freshness:** update sitemap lastmod only for directly changed public page sources and live experience/editorial routes whose shared renderer changed.
- **Sprint 182 — lazy-image policy evidence:** add a Chromium contract across core page families for async decoding on lazy images, explicit alt attributes and no high-priority/lazy contradiction.
- **Sprint 183 — visual-system regression:** reproduce and fix the stale token-order assertion from Browser E2E run 742 while retaining strict checks for all real route styles.
- **Sprint 184 — reduced-motion hover parity:** suppress all hover transforms under prefers-reduced-motion without removing focus indicators or hover color contrast.
- **Sprint 185 — release gate:** validate the exact final PR head with Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA. Do not infer a field-performance result from CI timing.

## Priority sequence

1. Deliver coherent guest journeys and resolve high-impact performance findings using measured browser evidence.
2. Review Home, Rooms, Packages/Experiences, Dining, Directions and Contact screenshots at 320/390/768/1280/1440px.
3. Measure actual image transfer sizes and page-loading metrics rather than assuming file extensions guarantee performance.
4. Verify keyboard/focus, contrast, reduced motion, form error recovery, responsive controls, canonical/schema/sitemap and route consistency.
5. Keep sitemap lastmod derived from every registry that affects rendered public pages; inspect exact-head CI artifacts. Passing CI does not replace human visual review or live-host verification.

## Release blockers requiring owner action

- Confirm production cPanel PHP version and Apache/.htaccess behavior.
- Approve menu/prices, booking/cancellation and privacy wording, check-in/reception, room rates and review quotations before publishing.
- Review staging for HTTPS, booking redirects, CAPTCHA/mail delivery and host-specific behavior.
- Approve visual/accessibility review and explicitly approve production release.

## Working agreement

Every PR must deliver a coherent visible slice with exact-head Site Checks, Browser E2E/screenshot evidence, package preflight and non-deploying Staging QA. Do not infer a pass from a cancelled or stale run. Nobody merges or deploys on the owner's behalf.


## Completed phase — Sprints 186–200: accessible social preview images

- Extended the shared page-head renderer to emit Twitter/X `image:alt` metadata without copying Open Graph alt text onto a different image source.
- Added source-grounded Open Graph image descriptions for the key public routes covered by this batch; existing approved image files and source mappings remain unchanged.
- Added PHP coverage for output escaping and same-image/distinct-image fallback rules, plus Browser E2E coverage across Home, Rooms, Packages, Trip Planner, Gallery, About, Directions, Contact, Journal, itinerary, safari and city-tour routes.
- Wired the PHP contract into the PHP 7.4/8.2 Site Checks workflow and documented the exact-head validation command.
- No page URLs, canonical paths, booking/contact destinations, business facts or production assets were changed. The batch requires fresh exact-head checks; no staging or production deployment was performed.


## Completed phase — Sprints 201–215: sitewide social image metadata completeness

- The shared head now supplies a reviewed description only when a page uses the shared default facade image; custom images cannot accidentally inherit the facade's description.
- Added source-specific Open Graph descriptions to the remaining static experience/dining pages. Experience and editorial renderers now explicitly send the same image description to both social-card formats and reject empty hero descriptions; Content Studio overlays enforce non-empty hero alt text.
- Expanded social-image Browser E2E from a fixed shortlist to every live route in the committed sitemap. The historical frozen head baseline ignores only the two image-alt fields now independently verified by that route-complete contract.
- Updated sitemap `lastmod` for changed static sources and the catalogue's data source. Sitemap generation now accounts for the shared Content Studio page renderer when deriving landing-page freshness.
- No public URLs, booking destinations, business facts or original photographs changed. Exact-head Site Checks, Browser E2E, package preflight and non-deploying Staging QA remain the release gate; no deployment was performed.


## Completed phase — Sprints 216–230: search-snippet quality and metadata alignment

- Trimmed the titles/descriptions identified by the 37-route rendered metadata audit, including Home, Car & City Tours, the Fort walk, Desert Safari, guided tours, Rooms, About, Contact, rooftop dining, restaurant and the honeymoon guide.
- Kept title and description copy aligned across the core page head and social-preview fields; shortened the Desert Safari title from 64 characters to the project's 30–60 character target.
- Added sitemap-wide Playwright checks for rendered title length (30–60 characters), meta-description length (120–160 characters), canonical route parity, and indexability. The length bands are internal quality gates, not claims about fixed Google snippet lengths.
- Updated the approved frozen head snapshot for the intentional copy changes. Further metadata drift remains a test failure instead of being silently normalized.
- Business details, URLs, booking destinations and image assets are unchanged.


## Sprints 231–245 — Contextual planning paths and route-wide E2E recovery

- Fixed the exact-head Browser E2E failure on `book-rooms-in-jaisalmer.php`: its custom rooftop social-card image now has explicit Open Graph and Twitter/X image descriptions instead of relying on the shared facade-image fallback.
- Added a reusable, output-escaped planning-path component to 11 high-intent routes: booking guide, Rooms, Packages, Directions, Rooftop Dining, Restaurant, About, Contact, Desert Safari, guided tours and the Fort walk.
- Each route supplies three distinct contextual next steps. The component validates same-site PHP destinations, uses a labelled navigation landmark, and supplies concise link descriptions. Responsive card styling follows the existing conversion design layer and respects reduced-motion preferences.
- Added a desktop Browser E2E contract for the 11 routes and refreshed the booking-guide sitemap lastmod to match its source revision.
- This is a repository-local publication and journey improvement, not evidence of ranking gains or field Core Web Vitals. No public URL or booking destination changed; no merge, cPanel upload, staging deployment or production deployment was performed.


## Sprints 246–260 — Editorial media readiness

- Added one safe local-image metadata helper shared by rendering, schema and responsive dimension attributes. It rejects path traversal and symlinked image files, and returns measured dimensions/MIME only when the local PHP runtime can decode the asset.
- Added accurate intrinsic dimension and MIME tags for inspectable local Open Graph images. Editorial BlogPosting/WebPage and experience WebPage nodes now use an ImageObject containing the same canonical media URL, descriptive alt text, reviewed caption and dimensions when available.
- Added a visible caption to each of the 12 editorial guide hero images and each shared experience hero. Captions remain separate from alt text and are used consistently in the structured data.
- Strengthened the source publication checks and added a rendered browser audit across 12 editorial routes and eight experience routes. The audit verifies successful image decoding, caption visibility, social-card/hero parity, and ImageObject dimension and text parity.
- This is a local release-readiness gate, not a claim of live ranking changes or field performance. Original photo files, route URLs, prices and booking destinations remain unchanged.


## Sprints 261–275 — Caption-gated Content Studio publishing and renderer recovery

- Fixed the 500 on published editorial guide routes by loading the shared schema helper before constructing the hero ImageObject.
- Extended Content Studio tour, guide and landing-page forms to capture a reviewed hero caption while keeping captionless Draft creation possible. Private Draft previews display an explicit reminder rather than failing to render.
- Enforced a 20–180 character caption at Live rendering/publication time for experience, editorial and Studio landing pages; sync-pack validation now raises `RC-24` for Live records without an acceptable caption.
- Added a release-verification check for stored and visibly rendered captions. Integration coverage now checks Live tour/guide caption rendering, captionless private-preview reminders, and the new authoring fields.
- Updated the editorial media browser audit to assert the caption length band across the 12 guides and eight experience pages, and added source-order/publication contracts so schema helper order and the caption gate cannot silently regress.
- These changes harden local publishing; they do not imply a hosting deployment or new live content. No route URLs, business claims, image assets, booking destinations or publication statuses were changed.


## Active implementation — Premium homepage storytelling (Milestones 1–2)

- **PR:** [#161](https://github.com/tchouhanjsm/garh-jaisal-website/pull/161) — open for review. **Branch:** `feature/home-premium-storytelling-20261030`, based on main `da806a67f7369d33eb15969d28c0386819cef7dc`.
- **Milestone 1:** refine the homepage hero art direction and responsive visual hierarchy using the existing Fort/facade photography, established brand tokens and the current responsive-image pipeline.
- **Milestone 2:** complete the guest-facing story with a dedicated Jaisalmer discovery section and transparent planning-confidence content; retain the existing room, rooftop, desert, arrival, gallery, FAQ and final conversion journeys.
- Move the existing Trip planning panel after accommodation and experience discovery so it acts as a considered next step rather than interrupting the story.
- Preserve the configured external booking destination, registered room data, public routes, Trip tray, WhatsApp/Contact handoff, verified facts and analytics privacy boundaries.
- **Release state:** implementation is prepared for PR review. Exact-head CI, browser screenshots, visual review and non-deploying package/Staging QA remain required. No cPanel upload or staging/production deployment is authorized by this batch.


## Active implementation — Homepage Milestones 1–2 + Phase 3 (PR #161)

- **Milestones 1–2:** premium first-screen art direction and a complete Fort-to-city homepage story.
- **Phase 3:** provide a clear room-discovery booking action that uses the already configured booking engine; instrument hero, room-section and final booking CTAs with stable IDs and locations.
- Keep attribution limited to the existing allowlisted UTM fields. Never forward email, topic, name, message or arbitrary query parameters to analytics.
- Preserve first-screen proof-point clearance at 320px, with the existing fixed mobile booking dock remaining unobstructed.
- PR #160's independent staging-release-provenance work is paused on its retained branch while this homepage PR is the sole active PR. Rebase and resume it only after #161 is merged.
