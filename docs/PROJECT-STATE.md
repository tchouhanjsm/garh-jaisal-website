# Project state and release gates

Last updated: 27 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline after PR #149

- `main` baseline: `fb1dc5a2d39e3fd12fe06b1d7681a3929386a36e` (PR #149 merge).
- Guest-journey milestones include #141 custom itinerary enquiries, #143 accessible experience-detail navigation, #144 editorial experience headings, #145 homepage/arrival hierarchy, #146 room comparison, #147 room-shortlist-to-Contact, #148 comparison-to-Trip, and #149 unified Trip-to-Contact plus experience-detail handoff.
- PR #149's Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA passed on its exact head.
- The Trip tray is session-only; selected IDs resolve server-side, while optional travel dates and traveller count remain outside the Contact URL.

## Active phase — Sprint 71: Dining entry points → shared Trip

- Branch: `feature/dining-trip-shortlist-20261027`.
- Make rooftop dining discoverable on both `restaurant.php` (menu and table request page) and `roof.php` (rooftop experience page), with the add-to-Trip action in the first-screen controls.
- Use one stable item across the dedicated pages and catalogue: ID `rooftop-dining`, label “Rooftop dining”, destination `roof.php`, and group `dining`. Re-selecting the option from another dining page must not duplicate it.
- Support the journey from either entry point into the session-only Trip tray and then Contact. Contact shows the server-controlled catalogue label and makes clear that it is an enquiry, not a confirmed table booking.
- Preserve the dedicated table request form, WhatsApp draft, current-menu warning and owner-confirmation guard. Do not expose the unapproved menu or prices.
- Add PHP contract and Playwright coverage for cross-page state, trusted Contact context and responsive layout at 320/390/768/1280/1440px.
- No automatic reservation, form submission, server-side Trip persistence or deployment.

## Priority sequence

1. Finish coherent guest-facing slices across Dining, Arrival and Contact; avoid isolated cosmetic patches.
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
