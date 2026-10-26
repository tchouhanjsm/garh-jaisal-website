# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 61 — shared photo storytelling for tours, rooms and dining |
| Verified main SHA | 686abd3272bd37031b38855728bd1bd20a89e76 (PR #134 merge commit) |
| Latest merged PR | [#134 — Trip enquiry completion with optional travel context](https://github.com/tchouhanjsm/garh-jaisal-website/pull/134) |
| PR #134 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40291811227); PHP 7.4/8.2 and shared source contracts |
| PR #134 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40291811223) |
| PR #134 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40291811219); artifact/check only, no upload |
| PR #134 merge-head Browser E2E | [CANCELLED](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40291811182); do not treat as a pass |
| Recent merged work | PR #131 room discovery; #132 published experience filters; #133 experience conversion checkpoint; #134 optional Trip enquiry context |
| Active phase branch | `feature/guest-visual-storytelling-20261026`; PR #135 is the Sprint 61 review target |


## Current phase

**Sprint 61 goal:** make the existing room, dining and tour offers easier to understand visually through a consistent multi-photo experience, rather than shipping isolated page tweaks.

- Reuse a single accessible carousel for the live experience renderer, both tour hubs, every room card and the restaurant photo section.
- Use the existing published Gallery/experience photo library and preserve the registry's own photo first where the page is a room card.
- Keep static images visible when JavaScript is unavailable; no autoplay, no new client-side libraries, and keyboard/touch controls with a live photo counter.
- Keep booking and enquiry actions, trip shortlist behavior, public routes, SEO/schema, menu publication guard and owner-confirmation boundaries unchanged.
- No cPanel upload, external staging deployment or production deployment is in scope.


## Latest implementation entry

- [2026-10-26] Sprint 60 / [PR #134 — Trip enquiry completion with optional travel context](https://github.com/tchouhanjsm/garh-jaisal-website/pull/134) merged at `686abd3272bd37031b38855728bd1bd20a89e76`. Site Checks, non-deploying QA and package preflight passed on the exact merge SHA; merge-head Browser E2E was cancelled.
- Sprint 61 / PR #135 introduces `include/ui/photo-carousel.php`, existing-photo collections for tour pages, a secondary photo for each room, a restaurant gallery carousel and route-level Playwright coverage.
- This work uses images already listed in the public Gallery/experience registry and does not change the restaurant menu's unconfirmed-price guard. No cPanel or production action occurred.


## Remaining blockers

| Priority | Item | Status |
|---|---|---|
| P1 | External staging/runtime validation | Owner-controlled: needs HTTPS staging origin and exact reviewed package SHA, plus Apache/PHP, MIME/cache, CAPTCHA and mail checks |
| P1 | Business-content approvals | Owner input remains needed for menu/prices/hours, booking/cancellation, privacy/controller/retention, check-in/reception, room rates and approved reviews |
| P2 | Human visual/accessibility review | Manual review at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology |
| P2 | Image/caption sign-off | Owner verification of photo subject/caption mapping and approval of responsive crop candidates |
| P1 | Production parity and release approval | Not verified; no cPanel upload, external staging deployment or production deployment performed by this work |

## Safety boundary

Content Studio remains an operator-side local tool, not a hosted CMS. Its Live state means live in the local working tree, not public production. Workflow packages are review artifacts only. Never invent owner-only facts or infer external-host readiness from green CI.
