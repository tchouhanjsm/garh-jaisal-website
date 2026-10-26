# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 64 — Car & City route-first enquiry journey |
| Verified main SHA | 0facd0fbc2e2b491015c3c67d25fe7a7c50bc35f (PR #137 merge commit) |
| Latest merged PR | [#137 — Direct-booking discovery path](https://github.com/tchouhanjsm/garh-jaisal-website/pull/137) |
| Recent merged work | PRs #134 trip enquiry context, #135 photo storytelling, #136 tour comparison facts, #137 direct-booking discovery |
| Active phase branch | `feature/city-tour-enquiry-journey-20261026` — Sprint 64 review target |

## Current phase

**Sprint 64 goal:** make the Car & City hub's next step clearer without blurring the line between existing options and proposed itinerary ideas.

- Add a route-first choice for guided tours, car arrangements or a proposed itinerary enquiry.
- Distinguish six unapproved itinerary ideas from existing tour pages; preserve their contextual enquiry topics and make the “not bookable” distinction explicit.
- Keep all current contact destinations, query topics, booking engine details, public URLs, and publication guards unchanged.

## Latest implementation entry

- [2026-10-26] Sprint 63 / [PR #137](https://github.com/tchouhanjsm/garh-jaisal-website/pull/137) merged at `0facd0fbc2e2b491015c3c67d25fe7a7c50bc35f`; direct booking guide discovery is now in `main`.
- Sprint 64 / `feature/city-tour-enquiry-journey-20261026` adds a route-first Car & City choice path, clearer proposal disclosure and targeted responsive regression tests. Exact-head CI and owner visual review are pending.

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
