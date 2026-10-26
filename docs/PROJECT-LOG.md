# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 66 — structured-data contact parity |
| Verified main SHA | 412a34d5a42177d16c449636e572402ca19c1754 (PR #138 merge commit) |
| Latest merged PR | [#138 — Car & City enquiry discovery](https://github.com/tchouhanjsm/garh-jaisal-website/pull/138) |
| Recent merged work | PRs #136 tour comparison, #137 direct-booking discovery, #138 Car & City enquiry discovery |
| Active phase branch | `feature/structured-data-contact-parity-20261026` — Sprint 66 stacked review target |

## Current phase

**Sprint 66 goal:** remove the remaining hard-coded Hotel telephone values in structured data while keeping the emitted JSON-LD values identical to the approved current output.

- Pass the existing phone configuration through the shared schema helper and derive ContactPage telephone/email metadata from the current configured display values.
- Add source/browser checks for JSON-LD contact parity on Home, Contact, Directions, Rooftop and Restaurant.
- PR #139 (Sprint 65) is still in review. Site Checks and non-deploying Staging QA passed; Browser E2E/screenshots remain pending.

## Latest implementation entry

- [2026-10-26] Sprint 64 / [PR #138](https://github.com/tchouhanjsm/garh-jaisal-website/pull/138) merged at `412a34d5a42177d16c449636e572402ca19c1754`. Site Checks and non-deploying QA passed; Browser E2E was cancelled and must not be treated as a pass.
- Sprint 65 / PR #139 centralizes Contact/Directions/Rooftop channel links and adds source/browser contracts. Site Checks and non-deploying QA passed; Browser E2E/screenshots are still running.
- Sprint 66 / `feature/structured-data-contact-parity-20261026` sources JSON-LD contact details from public configuration while preserving current output values; exact-head CI is pending.

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
