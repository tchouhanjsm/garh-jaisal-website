# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 57 — searchable room discovery and shortlist UX |
| Verified main SHA | e1ca0725ed3b3b7aa8ed42135abd9435f08357dd (PR #130 merge commit) |
| Latest merged PR | [#130 — sharpen three-second homepage first impression](https://github.com/tchouhanjsm/garh-jaisal-website/pull/130) |
| PR #130 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40276229242); PHP 7.4/8.2 and responsive-image review |
| PR #130 Browser E2E | [IN PROGRESS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40276229330); merge-head screenshot evidence not confirmed yet |
| PR #130 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40276229348); artifact/check only, no upload |
| PR #130 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40276229356); not an external-host deployment |
| Recent merged work | PR #128 success-only lead conversion tracking; #129 structured enquiry handoff; #130 homepage first impression |
| Active phase branch | feature/room-discovery-20261026; PR #131 open for review; exact-head acceptance pending |

## Current phase

**Sprint 57 goal:** make the Rooms page a useful selection tool, not just a long grid of cards.

- Search the existing room names, descriptions and listed features; combine the search with floor and one feature filter.
- Generate feature options and room counts from the authoritative room registry; do not create parallel facts or imply current room availability.
- Keep booking and room-specific enquiry actions prominent, retain all original card content and public routes, and preserve a full-card browsing fallback without JavaScript.
- Require exact-head PHP 7.4/8.2 checks, responsive-image review, full Browser E2E and screenshot/page-sheet evidence before visual review.
- cPanel upload, external staging and production deployment are explicitly out of scope.

## Latest implementation entry

- [2026-10-26] Sprint 57 / [PR #131 — searchable room discovery](https://github.com/tchouhanjsm/garh-jaisal-website/pulls/131) adds progressive search and filters sourced from `include/rooms-data.php`.
- The batch includes live matching counts, empty-state recovery, responsive controls and floor-nav synchronization.
- Playwright now checks search, combined filters, reset recovery and preserves the existing room-specific enquiry and booking destinations.
- The exact-head checks for PR #131 are pending; local PHP/Playwright checks were not run in this connector session.
- PR #130's merged-main Browser E2E remains in progress at the last verified check; no cPanel or production action occurred.

## Remaining blockers

| Priority | Item | Status |
|---|---|---|
| P1 | External staging/runtime validation | Owner-controlled: needs HTTPS staging origin and exact reviewed package SHA, plus Apache/PHP, MIME/cache, CAPTCHA and mail checks |
| P1 | Business-content approvals | Owner input still required for current menu/prices/hours, booking/cancellation, privacy/controller/retention, check-in/reception, room rates and approved reviews |
| P2 | Human visual/accessibility review | Manual review of screenshots at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology |
| P2 | Image/caption sign-off | Owner verification of source-to-subject/caption mapping and approval of responsive crop candidates |
| P1 | Production parity and release approval | Not verified; no cPanel upload, external staging deployment or production deployment performed by this phase |

## Safety boundary

Content Studio remains an operator-side local tool, not a hosted CMS. Its Live state means live in the local working tree, not public production. Workflow packages are review artifacts only. Never invent owner-only facts or infer external-host readiness from green CI.
