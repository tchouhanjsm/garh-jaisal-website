# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 65 — shared public contact-channel consistency |
| Verified main SHA | 412a34d5a42177d16c449636e572402ca19c1754 (PR #138 merge commit) |
| Latest merged PR | [#138 — Car & City enquiry discovery](https://github.com/tchouhanjsm/garh-jaisal-website/pull/138) |
| Recent merged work | PRs #136 tour comparison, #137 direct-booking discovery, #138 Car & City enquiry discovery |
| Active phase branch | `feature/contact-channel-consistency-20261026` — Sprint 65 review target |

## Current phase

**Sprint 65 goal:** keep the existing telephone and email destinations consistent across Contact, Directions and Rooftop, with the public contact values defined in `include/site-config.php`.

- Preserve both existing phone numbers and both current email destinations.
- Eliminate hard-coded duplicate links on those pages and add targeted source/browser regression coverage.
- Refresh the last-modified dates for the affected public pages without changing sitemap inventory or priority.

## Latest implementation entry

- [2026-10-26] Sprint 64 / [PR #138](https://github.com/tchouhanjsm/garh-jaisal-website/pull/138) merged at `412a34d5a42177d16c449636e572402ca19c1754`. Site Checks and non-deploying QA passed; Browser E2E was cancelled and must not be treated as a pass.
- Sprint 65 / `feature/contact-channel-consistency-20261026` centralizes visible Contact/Directions/Rooftop channels, adds source and browser contracts, and refreshes sitemap lastmod dates. Exact-head CI and screenshot evidence are pending.

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
