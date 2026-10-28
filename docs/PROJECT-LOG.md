# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Latest state — 28 October 2026

| Item | Value |
|---|---|
| Verified main SHA | `0f0ebe35c20e3a82d6c01d1ab6807cf3802395d8` — PR #153 merged |
| Active implementation | Sprints 75–77 — room comparison decision support |
| Scope | Add room photos, align listed features row by row and offer a difference-only filter; preserve booking and enquiry destinations |
| Release boundary | Owner retains merge and release approval; no cPanel upload or staging/production deployment |
| Exact-main CI | [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540394006) · [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540393565) · [Package](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540393646) · [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40540394143) — all passed on that SHA |

## Recent guest-journey milestones

- **PR #145 — homepage and arrival hierarchy:** reduced repeated promotion, improved page rhythm and restored detailed arrival guidance.
- **PR #146 — room comparison:** two-to-three-room comparison dialog and preservation of existing room/rate destinations.
- **PR #147 — room shortlist to Contact:** registry-validated multi-room enquiry with safe retry continuity.
- **PR #148 — room comparison to Trip:** grouped room options enter the shared tab-only Trip tray while separate choices remain selectable.
- **PR #149 — Trip to Contact + experience details:** mixed shortlist Contact fallback, room-option server resolution and full experience-detail links from desert/guided-tour hubs.
- **PR #150 — dining to shared Trip:** same validated `rooftop-dining` selection across restaurant, rooftop and catalogue pages, through to Contact. The corrected head passed all four required workflows.

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
- Add E2E tests for two- and three-room matrix states, filter on/off, recalculation when a selected room is removed, row visibility and the existing booking/Contact/Trip flows.
