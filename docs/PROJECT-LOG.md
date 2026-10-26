# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 62 — decision-first comparison for desert and city tours |
| Verified main SHA | c3326fb320e7b78bd20f70e8829c9e6a54266829 (PR #135 merge commit; Sprint 62 base) |
| Latest merged PR | [#135 — Multi-photo storytelling across tours, rooms and dining](https://github.com/tchouhanjsm/garh-jaisal-website/pull/135) |
| PR #135 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40307181583); PHP 7.4/8.2, marketing template, contrast and responsive image contracts |
| PR #135 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40307181582) |
| PR #135 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40307181660); artifact/check only, no upload |
| PR #135 merge-head Browser E2E | [CANCELLED](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40307181617); do not treat as a pass |
| Recent merged work | PR #132 experience filters; #133 conversion checkpoint; #134 Trip enquiry context; #135 multi-photo storytelling |
| Active phase branch | `feature/tour-decision-cards-20261026`; Sprint 62 review target (unmerged) |


## Current phase

**Sprint 62 goal:** help guests compare the existing live desert and city tour offers before opening a detail page.

- Render up to three facts from each experience's existing `at_a_glance` registry on the Desert Safari and Guided Jaisalmer hub cards.
- Use a reusable, escaped partial with explicit field priorities; do not add prices, clock times, routes or inclusions that are not already in the source.
- Add the existing Trip shortlist action to the city-tour cards while preserving the detail links and contact destinations.
- Add browser coverage for fact completeness, preserved actions and mobile overflow.
- No tour data pack, new photo, cPanel upload or deployment is in scope.


## Latest implementation entry

- [2026-10-26] Sprint 61 / [PR #135](https://github.com/tchouhanjsm/garh-jaisal-website/pull/135) merged at `c3326fb320e7b78bd20f70e8829c9e6a54266829`. Site Checks, non-deploying QA and package preflight passed; merge-head Browser E2E was cancelled.
- Sprint 62 is prepared on `feature/tour-decision-cards-20261026`; it adds source-backed comparison facts and city-tour shortlist actions, with exact-head CI required before review.

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
