# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 59 — mid-page experience decision checkpoint |
| Verified main SHA | 838b0e2c685a11b47a00ffdda2e431661a745bd4 (PR #132 merge commit) |
| Latest merged PR | [#132 — published experience discovery filters](https://github.com/tchouhanjsm/garh-jaisal-website/pull/132) |
| PR #132 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40284067930); PHP 7.4/8.2 and responsive-image review |
| PR #132 Browser E2E | [IN PROGRESS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40284067903); merge-head screenshot evidence pending |
| PR #132 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40284067910); artifact/check only, no upload |
| PR #132 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40284067866); no external-host deployment |
| Recent merged work | PR #130 homepage first impression; #131 room finder; #132 published experience finder |
| Active phase branch | feature/experience-conversion-checkpoint-20261026; PR #133 open for review; exact-head acceptance pending |

## Current phase

**Sprint 59 goal:** make the next action available just after guests review the itinerary and included/not-included details on an individual experience page.

- Share one conversion checkpoint across the live experience template; reuse the existing context-aware contact/WhatsApp destinations and Add to Trip/catalogue behavior.
- Preserve public URLs, registry publication boundaries, current rate/timing qualifications, images and structured data.
- Include the shared renderer in generated sitemap `lastmod` so template changes refresh all affected live experience URLs.
- Require exact-head PHP 7.4/8.2, responsive-image, sitemap, package/static, non-deploying QA, full Browser E2E and screenshot/page-sheet evidence.
- cPanel upload, external staging and production deployment are out of scope.

## Latest implementation entry

- [2026-10-26] Sprint 59 / [PR #133 — experience conversion checkpoint](https://github.com/tchouhanjsm/garh-jaisal-website/pulls/133) adds a post-detail CTA partial, responsive styling, route-level Playwright coverage and sitemap dependency tracking.
- Contextual contact links and the existing trip tray stay unchanged; duplicate Add to Trip controls are tested for synchronization.
- Exact-head CI is pending; local PHP/Playwright was not run in this connector session.
- PR #132 merged-main Browser E2E remains in progress at last check; no cPanel or production action occurred.

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
