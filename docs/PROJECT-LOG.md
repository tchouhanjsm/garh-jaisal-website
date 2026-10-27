# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-27 |
| Current phase | Sprint 67–68 parent delivery gate; Sprint 69 editorial typography prepared |
| Verified main SHA | df812a33675a5d31d4ce388629038cf5a7960105 (PR #140 merge commit) |
| Latest merged PR | [#140 — unified public contact channels and structured data](https://github.com/tchouhanjsm/garh-jaisal-website/pull/140) |
| Recent merged work | PRs #138 Car & City enquiry discovery, #139 contact-channel source of truth, #140 contact/schema parity |
| Active phase branch | `feature/experience-editorial-heading-20261027` — Sprint 69 branch stacked on PR #141; PR opens after the parent browser gate clears |

## Current phase

**Parent delivery gate:** PR #141 combines Sprint 67 (open-ended custom itinerary builder) and Sprint 68 (accessible section navigation from PR #143, merged into the parent feature branch). These changes are not on `main` yet.

- Current PR #141 head: `7d61e78eb6402ace04fb4c866d27c78a151902bd`.
- The Browser E2E run on `b8457cc8ad8a8dddc62415e2a7c88b8d2cecea27` exposed six regressions. The first consolidated fix reduced that to two remaining failures; the latest correction addresses the WhatsApp redirect and the 320px facts-card cascade.
- Exact-head checks are rerunning on `7d61e78`: [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40464240577), [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40464240589), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40464240652) and [non-deploying Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40464240778). These results are pending at this log revision.
- No merge or deployment until Browser E2E succeeds and page-sheet/screenshot evidence is available.

**Next phase:** Sprint 69's shared editorial hero heading treatment is prepared on `feature/experience-editorial-heading-20261027`, stacked on the parent head. Its PR will be opened after the parent Browser E2E gate completes to avoid overlapping full browser runs.

## Latest implementation entry

- [2026-10-27] PR #140 merged at `df812a33675a5d31d4ce388629038cf5a7960105`. Sprint 65 public contact channels and Sprint 66 structured-data parity are in `main`; Site Checks, package preflight and non-deploying Staging QA passed. Merge-head Browser E2E was cancelled and must not be treated as a pass.
- Sprint 65–66 / PRs #139–#140 merged the visible contact and structured-data parity slice. Browser screenshot evidence remains absent because the merge-head Browser E2E run was cancelled.
- Sprint 67 / PR #141 contains the custom-itinerary page, browser-only draft composer, contextual contact fallback, open-ended Car & City links, sitemap and regression contracts. Latest Site Checks, package preflight and non-deploying QA pass; Browser E2E/screenshot evidence is still in progress.
- Sprint 68 / PR #143 was merged into `feature/custom-itinerary-builder-20261027`, so its navigation code is included in PR #141. It is not a standalone merge to `main`; the combined parent PR must pass exact-head checks before these pages ship.
- Sprint 69 / editorial hero typography is prepared on `feature/experience-editorial-heading-20261027`; its pull request is intentionally waiting for the parent Browser E2E gate before being opened.

## Remaining blockers

| Priority | Item | Status |
|---|---|---|
| P1 | External staging/runtime validation | Owner-controlled: HTTPS staging URL and reviewed package SHA, Apache/PHP and MIME/cache behavior, CAPTCHA/mail delivery |
| P1 | Business-content approvals | Owner input remains needed for menu/prices/hours, booking/cancellation terms, privacy controller/retention, check-in/reception, room rates, and approved reviews |
| P2 | Human visual/accessibility review | Manual review at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology |
| P2 | Image/caption sign-off | Owner verification of photo subject/caption mapping and responsive crop candidates |
| P1 | Production parity and release approval | Not verified; no cPanel upload or staging/production deployment performed by this work |

## Safety boundary

Content Studio remains an operator-side local tool, not a hosted CMS. Local “Live” state is not proof of production publication. Never infer external-host readiness from green CI.
