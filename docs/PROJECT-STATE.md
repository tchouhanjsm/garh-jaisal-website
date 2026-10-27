# Project state and release gates

Last updated: 27 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline

- `main` baseline before Sprint 69: `684b9462769d12ab1bb116c0e29daf567a7f4190`, after PR #147.
- Merged guest-journey work: #141 custom itinerary enquiry builder; #143 accessible experience-detail navigation; #144 editorial experience headings; #145 homepage/arrival hierarchy; #146 room comparison; #147 room-shortlist-to-Contact handoff.
- PR #141's earlier browser failure was superseded by a later exact-head Browser E2E/screenshot run that passed at `7d61e78eb6402ace04fb4c866d27c78a151902bd`; do not treat the old failure as current.
- On the PR #147 merge SHA, Site Checks, package preflight and Staging QA passed. Main Browser E2E was still in progress when this state was written and must be checked before claiming post-merge success.

## Active phase — Sprint 69: comparison to Trip planner

Turn a two- or three-room comparison into one named “Room options” entry in the existing session-only Trip tray. Treat that grouped choice as the single stay option so it replaces a previous stay selection but preserves any separately selected experiences, transport or dining choices.

- Keep the existing comparison → Contact enquiry route intact.
- Use the existing Trip tray, sessionStorage item schema, date/count validation and reviewed WhatsApp draft; do not add another storage format or submit anything automatically.
- Make the handoff explicit, accessible and responsive. The guest stays in control and can remove or replace the selection later.
- Regression-test a stay + experience → comparison → Trip path, preservation of non-stay choices, generated message content and the stored selection boundaries.

## Priority sequence

1. Complete coherent guest-facing vertical slices across Room, Experience, Dining, Arrival and Contact; avoid isolated cosmetic patches.
2. Review Home, Rooms, Packages/Experiences, Dining, Directions and Contact screenshots at 320/390/768/1280/1440px.
3. Measure real image transfer sizes and page-loading metrics instead of assuming the file extension guarantees performance.
4. Verify keyboard/focus, contrast, reduced motion, form error recovery, responsive controls, canonical/schema/sitemap and route consistency.
5. Inspect exact-head CI artifacts. Passing CI does not replace human visual review or live-host verification.

## Release blockers requiring owner action

- Confirm production cPanel PHP version and Apache/.htaccess behavior.
- Approve menu/prices, booking/cancellation and privacy wording, check-in/reception, room rates and review quotations before publishing.
- Review live staging for HTTPS, booking redirects, CAPTCHA/mail delivery and host-specific behavior.
- Approve visual/accessibility review and explicitly approve any production release.

## Working agreement

Each PR should deliver a coherent visible slice with exact-head Site Checks, Browser E2E/screenshot evidence, package preflight and non-deploying Staging QA. Do not infer a pass from a cancelled or stale run. No cPanel upload or production deployment is included here.
