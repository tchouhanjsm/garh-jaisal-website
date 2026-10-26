# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 66 — combined public contact and schema parity |
| Verified main SHA | 412a34d5a42177d16c449636e572402ca19c1754 (PR #138 merge commit) |
| Latest merged PR | [#138 — Car & City enquiry discovery](https://github.com/tchouhanjsm/garh-jaisal-website/pull/138) |
| Recent merged work | PRs #136 tour comparison, #137 direct-booking discovery, #138 Car & City enquiry discovery |
| Active phase branch | `feature/structured-data-contact-parity-20261026` — PR #140 combined review target against `main` |

## Current phase

**Sprint 65–66 goal:** make all public Contact/Directions/Rooftop telephone and email links plus shared Hotel/ContactPage/Restaurant schema details configuration-backed while preserving current destinations and JSON-LD output.

- Preserve the existing primary/alternate phone and email destinations in Contact, Directions and Rooftop; derive the shared Hotel and ContactPage/Restaurant schema fields from the same configuration.
- Add PHP source contracts and browser regressions for visible contact links, JSON-LD parity, and responsive overflow.
- PR #140 is the combined 15-file candidate targeting `main`. Its exact-head workflows must all finish successfully, including Browser E2E and screenshots, before merge.

## Latest implementation entry

- [2026-10-26] Sprint 64 / [PR #138](https://github.com/tchouhanjsm/garh-jaisal-website/pull/138) merged at `412a34d5a42177d16c449636e572402ca19c1754`. Site Checks and non-deploying QA passed; Browser E2E was cancelled and must not be treated as a pass.
- Sprint 65 / PR #139 added the configuration-backed public Contact/Directions/Rooftop links and source/browser contracts. Site Checks, non-deploying Staging QA and package preflight passed; its Browser E2E job remains in progress.
- Sprint 66 / PR #140 combines the visible-link and JSON-LD parity work into one reviewable 15-file change and targets `main` to start a fresh full exact-head test run.

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
