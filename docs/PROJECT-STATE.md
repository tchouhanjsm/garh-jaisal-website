# Project state and release gates

Last updated: 27 October 2026.

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

## Active phase — Sprint 75: photo-led, keyboard-accessible room comparison

- Reduce choice overload in the first viewport: detail-page heroes keep the contextual enquiry as primary and the session-only “Add to Trip” control as the only secondary action. Remove the redundant catalogue link from individual experiences; global navigation and lower-page discovery paths remain available.
- The Guided Jaisalmer Tours hub keeps “Plan my guided tour” and “Compare tour options”; card-level Add to Trip and detail-page routes remain intact. Keep the Desert Safari hub's catalogue action because its hero does not add a single experience to the Trip tray.
- Add exact browser contracts for two purposeful hero actions, matching hero/checkpoint contact destinations, and synchronized Trip toggles at mobile and desktop sizes.
- Preserve room-booking destinations, contact routing, all public URLs, factual copy, and the rule that an enquiry does not confirm a booking.

Sprint 75 makes the existing selected-room table easier to scan by reusing each selected room's curated first photo, exposing the horizontally scrollable comparison region to keyboard users, and keeping the table responsive without changing room facts or the booking-engine destination.

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
