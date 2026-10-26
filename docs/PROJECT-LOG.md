# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 60 — Trip enquiry completion with optional travel context |
| Verified main SHA | 76a1bbe268f6a277d1043265013a16ec8a54ace4 (PR #133 merge commit) |
| Latest merged PR | [#133 — experience conversion checkpoint](https://github.com/tchouhanjsm/garh-jaisal-website/pull/133) |
| PR #133 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40285020749); PHP 7.4/8.2, source contracts and responsive-image review |
| PR #133 Browser E2E | [CANCELLED](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40285020651); page-sheet [artifact](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40285020651/artifacts/12634706477) and curated evidence [artifact](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40285020651/artifacts/12635110260) uploaded, but full test completion was not verified |
| PR #133 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40285020597); artifact/check only, no upload |
| PR #133 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40285020714); no external-host deployment |
| PR #133 exact-merge Browser E2E | [IN PROGRESS at snapshot](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40287558759); do not count as passed until GitHub reports success |
| Recent merged work | PR #131 room discovery; #132 experience filters; #133 mid-page experience decision checkpoint |
| Active phase branch | `feature/trip-enquiry-details-20261026`; PR #134 is the Sprint 60 review target |


## Current phase

**Sprint 60 goal:** reduce friction when a guest turns saved room/experience choices into a useful first enquiry.

- Add optional travel dates and traveller count to the Trip tray and include valid values in the WhatsApp draft.
- Keep those values out of sessionStorage, site/server requests and analytics; only the existing product shortlist is persisted for the current browser tab.
- Validate date order and traveller count without implying a confirmed booking or live availability.
- Preserve the hotel booking-engine URL/token, current experience/room Contact routes, selection cap, publication boundaries and existing analytics contract.
- No cPanel upload, external staging deployment or production deployment is in scope.


## Latest implementation entry

- [2026-10-26] Sprint 59 / [PR #133 — experience conversion checkpoint](https://github.com/tchouhanjsm/garh-jaisal-website/pull/133) merged at `76a1bbe268f6a277d1043265013a16ec8a54ace4`.
- PHP 7.4/8.2 Site Checks, non-deploying QA and package preflight passed on PR #133; its Browser E2E run was cancelled after producing the page-sheet and curated evidence artifacts. The exact-merge Browser E2E run was still in progress at this snapshot.
- Sprint 60 is [PR #134 — Trip enquiry completion with optional travel context](https://github.com/tchouhanjsm/garh-jaisal-website/pull/134); travel context is kept out of server persistence and analytics, and this work does not deploy.


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
