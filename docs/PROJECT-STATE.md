# Project state and release gates

Last updated: 28 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline after PR #153

- `main` is at `0f0ebe35c20e3a82d6c01d1ab6807cf3802395d8`, the merge commit for PR #153.
- PR #153 simplified experience hero choices and aligned the Desert Safari and Guided Tours hero action contract without changing booking or enquiry destinations.
- The post-merge workflows passed on the exact main SHA: [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540394006) · [Browser E2E/screenshots](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540393565) · [Package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540393646) · [Non-deploying Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540394143).
- Screenshot evidence and the generated cPanel package are CI artifacts only; no package was uploaded, no staging deployment occurred, and no production release was made.

## Completed phase — Sprint 72: measured hero-image payload budgets

- The PR #150 local Chromium artifact audited 37 sitemap routes, 149 distinct rendered image URLs and 118 images with measured transfer size. These are local observations, not production/CDN or Core Web Vitals measurements.
- PR #151 reduced the two largest reviewed responsive candidates with narrowly scoped quality 74 overrides while preserving the manifest's default quality 82 and 350 KiB candidate size ceilings. Original photographs remain unchanged.

## Completed phase — Sprint 73: responsive image slots across high-intent journeys

- PR #152 applied slot-accurate responsive `sizes` hints to Home, Dining, Rooftop, Desert Safari and Guided Tours templates/cards; added existing rooftop sources to the manifest; and retained the generator's no-upscale behavior for undersized originals.
- The corrected PR head fixed the initial E2E fixture's missing `BASE_URL`; the corrected merge head subsequently passed Browser E2E and screenshots on `2cea0d7e64b904969b4668e9010666f35ed64632`. The earlier failure is resolved, not a current release blocker.
- The restaurant hero overline now uses the existing light-gold treatment with a WCAG AA contrast regression assertion. Original photos, crops, alt text, public routes and business copy remain unchanged.

## Completed phase — Sprint 74: focused experience-action hierarchy (PR #153 merged)

PR #153 merged at `0f0ebe35c20e3a82d6c01d1ab6807cf3802395d8`. It keeps experience detail heroes focused on contextual enquiry plus Add to Trip, while preserving lower-page shortlist routes and the Desert Safari/Guided Tours hub alternatives.

## Active phase — Sprints 75–80: room comparison decision support (PR #154)

- **Sprint 75 — visual and accessibility foundation:** show each selected room's curated photo in the comparison table and make the horizontally scrollable comparison area keyboard-focusable, named and explained for mobile users.
- **Sprint 76 — feature-by-feature matrix:** align each existing, registered room feature into its own row, showing “Listed” or “Not listed” for each selected room rather than leaving guests to compare separate unordered lists. “Not listed” is deliberately not a claim that a facility is absent.
- **Sprint 77 — differences-only view:** add a keyboard-accessible “Show differences only” checkbox that filters feature rows while keeping room photos, floor, description, booking links and the rest of the matrix in place. Browser coverage checks two-room and three-room matrices, filter recalculation after room removal, and the no-difference state.
- **Sprint 78 — filter-proof shortlist:** show selected room names outside the result grid so the shortlist can still be reviewed while current filters hide all selected cards.
- **Sprint 79 — focus continuity:** move focus to the next relevant remove control after comparison rows are rebuilt and restore focus to room search after clearing the shortlist.
- **Sprint 80 — directory recovery:** when active filters hide selected rooms, offer a deliberate action to reset those filters, reveal the selected cards and focus the first selected room without losing the shortlist.
- Preserve room IDs, the two-to-three-room limit, original photos/alt text, existing features and descriptions, booking-engine URL/token, room-specific Contact handoff, session-only Trip behavior, routes, metadata and schema.
- Acceptance: exact-head PHP 7.4/8.2 Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA. No cPanel upload or deployment.

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
- **Remediation after Browser E2E run 739:** the captured 320px screenshot confirmed the eyebrow was rendered at 11.52px because the final compact-phone override used `0.72rem`. Keep the existing minimum-size assertion and set the homepage hero eyebrow back to the established `0.8125rem` (13px at the root scale); scope the test to the visible primary hero eyebrow instead of the first arbitrary `.eyebrow` in the DOM. Verify the new exact head before marking the browser gate complete.

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
