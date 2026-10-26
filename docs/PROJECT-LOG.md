# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 58 — experience discovery and comparison UX |
| Verified main SHA | 9c0c15cf93f83cfba296e6b062e87dcf53f1773b (PR #131 merge commit) |
| Latest merged PR | [#131 — searchable room discovery and filters](https://github.com/tchouhanjsm/garh-jaisal-website/pull/131) |
| PR #131 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40279711938); PHP 7.4/8.2 and responsive-image review |
| PR #131 Browser E2E | [IN PROGRESS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40279711945); merge-head screenshot evidence not confirmed yet |
| PR #131 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40279711931); artifact/check only, no upload |
| PR #131 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40279711920); not an external-host deployment |
| Recent merged work | PR #129 structured enquiry handoff; #130 homepage first impression; #131 searchable room discovery |
| Active phase branch | feature/experience-finder-20261026; PR #132 open for review; exact-head acceptance pending |

## Current phase

**Sprint 58 goal:** make the Packages route easier to scan and narrow without duplicating or exposing unapproved experience data.

- Add search and registry-backed category/duration filters for published, non-hub desert and city options.
- Show each published card’s configured duration type; keep exact times, prices and inclusions subject to existing approved source content.
- Preserve live itinerary links, trip shortlist actions, SEO/schema and a full no-JavaScript fallback.
- Require exact-head PHP 7.4/8.2, responsive-image review, package/static checks, non-deploying QA, full Browser E2E and screenshot/page-sheet evidence.
- cPanel upload, external staging and production deployment are out of scope.

## Latest implementation entry

- [2026-10-26] Sprint 58 / [PR #132 — experience discovery](https://github.com/tchouhanjsm/garh-jaisal-website/pulls/132) adds search and category/duration filters to published Packages cards.
- Filter choices and duration labels come from the existing experience registry; draft and hidden records remain excluded.
- Playwright covers combined filters, zero-result recovery, reset, responsive targets and no-JavaScript card availability.
- PR #132 exact-head CI is pending; local PHP/Playwright was not run in this connector session.
- PR #131 merge-head Browser E2E remains in progress at last check; no cPanel or production action occurred.

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
