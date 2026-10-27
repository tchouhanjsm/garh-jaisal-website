# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Latest state — 27 October 2026

| Item | Value |
|---|---|
| Baseline before Sprint 69 | `684b9462769d12ab1bb116c0e29daf567a7f4190` (PR #147 merge) |
| Active implementation | Sprint 69 — room comparison → shared Trip planner |
| Scope | One room-options summary in the existing tab-only Trip tray |
| Release boundary | No real enquiry, cPanel upload, external staging deployment or production deployment |

## Recent implementation milestones

- **PR #141 — custom itinerary discovery:** added an open-ended itinerary enquiry builder, a client-side reviewable WhatsApp draft, contextual Contact fallback, SEO/sitemap coverage and responsive/browser contracts. The older failing browser run was superseded; later exact-head browser/screenshot evidence passed before merge.
- **PR #143 — experience section navigation:** added accessible, conditional navigation anchors for content sections on the live experience detail pages.
- **PR #144 — editorial headings:** strengthened the existing experience-page display typography while preserving facts and routes.
- **PR #145 — homepage and arrival hierarchy:** reduced repeated homepage promotion, refined the page rhythm and restored the detailed arrival guide.
- **PR #146 — room comparison:** added two-to-three-room side-by-side comparison, responsive dialog treatment and preservation of existing room/rate destinations.
- **PR #147 — room shortlist to Contact:** validates room slugs against the room registry, builds a contextual enquiry, and preserves the room selection through safe form retry paths.

## Sprint 69 — room comparison to Trip

The next vertical slice lets a guest select two or three existing rooms and add that preference to the shared Trip draft as a single “Room options: …” stay entry. It replaces any older stay entry (the Trip tray is designed for one stay choice) and keeps other selected options such as a Swiss Tent experience. The existing comparison-to-Contact CTA remains available.

The new integration must reuse the current Trip tray validation and storage rules, keep travel dates/counts out of sessionStorage, make the WhatsApp draft explicit and reviewable, and never book, send or store a lead automatically.

## Current verification

- Exact-head checks are the source of truth. Do not use stale/superseded runs as current status.
- Site Checks, package preflight and non-deploying Staging QA passed for PR #147's head. Main Browser E2E on merge SHA `684b9462769d12ab1bb116c0e29daf567a7f4190` was in progress at the latest inspection.
- This connector session can inspect and update GitHub source but has not run the PHP/Playwright suite locally. Report CI results directly and do not claim a local pass.

## Remaining owner-controlled release blockers

| Priority | Item | Status |
|---|---|---|
| P1 | Host validation | Real HTTPS staging, deployed package SHA, PHP/Apache behavior, CAPTCHA and mail delivery are not verified here. |
| P1 | Business-content approval | Menu/prices/hours, policies/privacy, check-in/reception, room rates and verified reviews still require owner decisions. |
| P2 | Human design/accessibility review | Review screenshot artifacts at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology. |
| P1 | Production release | No deployment performed; owner approval remains mandatory. |
