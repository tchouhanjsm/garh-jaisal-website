# Project state and release gates

Last updated: 27 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline after PR #150

- `main` baseline: `7f8785b9860062d4041906f9be1fd3035f2a7488` (PR #150 merge).
- Guest-journey milestones include #141 custom itinerary enquiries, #143 accessible experience-detail navigation, #144 editorial experience headings, #145 homepage/arrival hierarchy, #146 room comparison, #147 room-shortlist-to-Contact, #148 comparison-to-Trip, #149 unified Trip-to-Contact plus experience-detail handoff, and #150 dining-to-Trip-to-Contact.
- PR #150's corrected head `fda85a55b73e2fc92c46ade045a4287ba453ce5d` passed Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA. [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40493368760) · [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40493369366) · [Package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40493368827) · [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40493368740).
- The Trip tray is session-only; selected IDs resolve server-side, while optional travel dates and traveller count remain outside the Contact URL.

## Active phase — Sprint 72: measured hero-image payload budgets

- The PR #150 browser artifact audited 37 sitemap routes and 149 distinct rendered image URLs in a local PHP/Chromium run (not production measurements).
- The largest measured responsive candidates were `img/responsive/v2/facade-1200.webp` at 416,592 bytes and `img/responsive/v2/courtyard-1200.webp` at 406,736 bytes.
- Keep the default responsive-image encoding quality at 82; use a source-scoped quality of 74 only for the façade and directions-courtyard source photos. Original photos and all other sources remain unchanged.
- Enforce a 350 KiB maximum for those two generated 1200px candidates in Browser E2E using exact filesystem bytes, and record the budget evidence alongside the sitewide image-payload audit.
- Retain the generated source-vs-variant review sheet and report for visual inspection. Automated byte budgets do not prove acceptable perceived image quality; owner review is required before merge/release.

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
