# Project state and release gates

Last updated: 27 October 2026.

## Current objective

Deliver a coherent, premium first impression and a lower-friction direct-booking journey on the existing plain-PHP/cPanel stack. The visual direction remains editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint and local warmth.

## Verified baseline after PR #148

- `main` baseline: `95e43decf5833115c632adebfeee78ee1dd3bbae` (PR #148 merge: room comparison → shared Trip planner).
- Merged guest-journey work includes #141 custom itinerary enquiries, #143 accessible experience-detail navigation, #144 editorial experience headings, #145 homepage/arrival hierarchy, #146 room comparison, #147 room-shortlist-to-Contact handoff and #148 room comparison → Trip.
- PR #148 introduced a grouped `room-options-…` Trip item for a two- or three-room comparison. PR #149 must retain that choice in its Contact fallback by resolving the ID and label against the server-side room registry.

## Active phase — Sprint 70: Trip Contact + experience-detail handoff

- Branch: `feature/unified-trip-contact-handoff-20261027` (PR #149).
- Add Contact as a secondary path from the session-only Trip tray; keep WhatsApp the primary, reviewable draft.
- Carry selected IDs only into Contact. Do not put optional travel dates or traveller count in the Contact URL.
- Resolve every label against server-side room, live experience and reviewed catalogue/transport records. Reject unknown, malformed, duplicate and over-limit selections; never trust browser-supplied titles.
- Preserve grouped room comparison choices through Contact and safe form retries. Treat the grouped selection as one stay option.
- On the desert and guided-tour hubs, give each live experience card a clear “More details” route to its full detail page. Keep the hub list registry-driven so additional published experiences appear without hard-coded source-count limits. The detail page retains its itinerary, inclusions, FAQs and its own enquiry actions.
- No automatic enquiry submission, booking, server-side Trip storage, dependency changes or deployment.

## Priority sequence

1. Complete coherent guest-facing slices across Room, Experience, Dining, Arrival and Contact; avoid isolated cosmetic patches.
2. Review Home, Rooms, Packages/Experiences, Dining, Directions and Contact screenshots at 320/390/768/1280/1440px.
3. Measure actual image transfer sizes and page-loading metrics rather than assuming the file extension guarantees performance.
4. Verify keyboard/focus, contrast, reduced motion, form error recovery, responsive controls, canonical/schema/sitemap and route consistency.
5. Inspect exact-head CI artifacts. Passing CI does not replace human visual review or live-host verification.

## Release blockers requiring owner action

- Confirm production cPanel PHP version and Apache/.htaccess behavior.
- Approve menu/prices, booking/cancellation and privacy wording, check-in/reception, room rates and review quotations before publishing.
- Review staging for HTTPS, booking redirects, CAPTCHA/mail delivery and host-specific behavior.
- Approve visual/accessibility review and explicitly approve production release.

## Working agreement

Every PR must deliver a coherent visible slice with exact-head Site Checks, Browser E2E/screenshot evidence, package preflight and non-deploying Staging QA. Do not infer a pass from a cancelled or stale run. Nobody merges or deploys on the owner's behalf.
