# Project state and release gates

Last updated: 28 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

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
