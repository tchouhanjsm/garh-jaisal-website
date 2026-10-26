# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 56 — three-second homepage first impression |
| Verified main SHA | 15585db334be9683b0dc3cc54643ca4a9e317e97 (PR #129 merge commit) |
| Latest merged PR | [#129 — preserve structured guest enquiry context](https://github.com/tchouhanjsm/garh-jaisal-website/pull/129) |
| PR #129 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40269420923); PHP 7.4 and 8.2 |
| PR #129 Browser E2E | [CANCELLED](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40269420890); full suite result not verified |
| PR #129 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40269420839); checks package only |
| PR #129 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40269420860); not an external-host deployment |
| Recent merged work | PR #127 conversion-led homepage and mobile UX; #128 success-only lead conversion tracking; #129 structured enquiry handoff |
| Active phase branch | feature/home-first-impression-20261026; exact-head acceptance pending |

## Current phase

**Sprint 56 goal:** make the homepage's first screen communicate property identity, location, offer and next action rapidly.

- Tighten the hero eyebrow and lead copy; maintain the existing art-directed facade and Fort-roof image sources.
- Keep the booking-engine link as the primary action, use an internal Rooms route for the secondary action, and surface three concise, verified facts.
- Review initial screen composition across 320, 390, 768, 1280 and 1440 CSS pixels. Preserve the shared responsive image budget and zero-overflow contract.
- Require exact-head PHP 7.4/8.2 Site Checks, Browser E2E/screenshot evidence and responsive-image review before considering the code review-ready.
- cPanel upload, external staging and production deployment are explicitly out of scope for this phase.

## Remaining blockers

| Priority | Item | Status |
|---|---|---|
| P1 | External staging/runtime validation | Owner-controlled: needs HTTPS staging origin and exact reviewed package SHA, plus Apache/PHP, MIME/cache, CAPTCHA and mail checks |
| P1 | Business-content approvals | Owner input still required for current menu/prices/hours, booking/cancellation, privacy/controller/retention, check-in/reception, room rates and approved reviews |
| P2 | Human visual/accessibility review | Manual review of screenshots at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology |
| P2 | Image/caption sign-off | Owner verification of source-to-subject/caption mapping and approval of responsive crop candidates |
| P1 | Production parity and release approval | Not verified; no cPanel upload, external staging deployment or production deployment performed by this phase |

## Safety boundary

Content Studio remains an operator-side local tool, not a hosted CMS. Its Live state means live in the local working tree, not public production. Workflow packages are review artifacts only. Never invent owner-only facts or infer external-host readiness from green CI.
