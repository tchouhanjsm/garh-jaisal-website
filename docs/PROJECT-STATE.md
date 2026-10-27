# Project state and release gates

Last updated: 27 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline after PR #151

- `main` baseline: `fb02f83f96698cd1948cdd09e1358c74c661bd56` (PR #151 merge).
- Guest-journey milestones include #141 custom itinerary enquiries, #143 accessible experience-detail navigation, #144 editorial experience headings, #145 homepage/arrival hierarchy, #146 room comparison, #147 room-shortlist-to-Contact, #148 comparison-to-Trip, #149 unified Trip-to-Contact plus experience-detail handoff, #150 dining-to-Trip-to-Contact, and #151 measured responsive-image quality profiles with byte-budget checks.
- PR #151's corrected head passed Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA. [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40497295876) · [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40497295673) · [Package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40497295768) · [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40497295778).

## Completed phase — Sprint 72: measured hero-image payload budgets

- The PR #150 browser artifact audited 37 sitemap routes and 149 distinct rendered image URLs in a local PHP/Chromium run (not production measurements).
- The largest measured responsive candidates were `img/responsive/v2/facade-1200.webp` at 416,592 bytes and `img/responsive/v2/courtyard-1200.webp` at 406,736 bytes.
- PR #151 added source-scoped quality 74 overrides for those two candidates, retained default quality 82 for other sources, and enforced exact-byte 350 KiB ceilings. Original photographs remain unchanged.
- Local Chromium payload data is a comparative engineering baseline, not production/CDN or Core Web Vitals evidence. Human visual review of full-size outputs remains required.

## Active phase — Sprint 73: responsive image slots across high-intent journeys

- Apply slot-accurate `sizes` hints where Home, Dining, Rooftop, Desert Safari and Guided Tours templates were falling back to the full-resolution/optimized 1x source.
- Use full viewport hints for full-bleed heroes, actual two-column/three-column estimates for option cards, and a contained image size for the rooftop banner.
- Verify each newly wired slot exposes responsive candidates through the shared helper. Browser tests cover the source and slot hint together so later template changes cannot quietly drop responsive delivery.
- Preserve image content, alt text, priority/loading semantics and existing routes. Validate layouts and intrinsic dimensions at 320/390/768/1280/1440px.

## Priority sequence

1. Complete coherent user-facing journeys and resolve high-impact performance findings using measured browser evidence.
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
