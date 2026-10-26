# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 63 — direct-booking path and room-first discovery |
| Verified main SHA | 32c59b579ae7e528afe6644e90988892b35b5237 (PR #136 merge commit; Sprint 63 base) |
| Latest merged PR | [#136 — Decision-first tour comparison cards](https://github.com/tchouhanjsm/garh-jaisal-website/pull/136) |
| Recent merged work | PR #133 conversion checkpoint; #134 Trip enquiry context; #135 photo storytelling; #136 tour comparison facts |
| Active phase branch | `feature/direct-booking-path-closure-20261026`; Sprint 63 review target (unmerged) |


## Current phase

**Sprint 63 goal:** make the existing room-booking journey discoverable at the point where a guest chooses to stay or explore experiences.

- Add the existing Fort stay guide to the Stay submenu and footer, and link it from Home and the Rooms hero.
- Give the guide above-the-fold room-availability and room-comparison actions plus a concise, three-step booking path.
- Add a reusable, restrained room-first note to Packages, Desert Safari, Guided Jaisalmer and Car & City hub pages.
- Keep the configured booking URL/token and contact routes unchanged; room prices, policies, hours, mail and production configuration remain owner-confirmation gates.
- No new offer facts, external scripts, deployment or merge authority changes.

## Latest implementation entry

- [2026-10-26] Sprint 62 / [PR #136](https://github.com/tchouhanjsm/garh-jaisal-website/pull/136) merged at `32c59b579ae7e528afe6644e90988892b35b5237`.
- Sprint 63 / `feature/direct-booking-path-closure-20261026` introduces the shared room-first note and completes the internal discovery path for the existing stay guide. Exact-head CI is the verification gate.

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
