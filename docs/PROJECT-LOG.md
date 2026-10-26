# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-26 |
| Current phase | Sprint 55 — structured room/experience/topic enquiry handoff |
| Verified main SHA | 451d34acfa2ad5c09e8d06ac577da480e65de916 (PR #128 merge commit) |
| Latest merged PR | [#128 — measure successful enquiries and contextual lead starts](https://github.com/tchouhanjsm/garh-jaisal-website/pull/128) |
| PR #128 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40266287508); PHP 7.4 and 8.2 |
| PR #128 Browser E2E | [CANCELLED](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40266287564); full suite result not verified |
| PR #128 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40266287605); package artifact only |
| PR #128 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/40266287555); not external-host validation |
| Recent merged work | PR #127 conversion-led website/mobile UX; PR #128 success-only enquiry conversion measurement |
| Active phase branch | feature/structured-enquiry-context-20261026; exact-head acceptance pending |

## Current phase

**Sprint 55 goal:** carry the guest's selected room, live experience or approved Contact topic from the public form into the hotel's private lead record and email notification.

- Extract Contact topic definitions into a single shared registry; resolve every context from trusted registries, never from posted labels.
- Issue an opaque, two-hour session-bound context token per form view, with a bounded token set so multiple open tabs retain their own intent.
- Persist the resolved type, slug and label in the existing private lead record and distinguish the item in the email subject/table.
- Preserve the known context on recoverable validation/CAPTCHA/rate-limit/storage errors; consume the token only after a lead is stored.
- Tests cover registry allowlists, unsafe/unknown fallbacks, retry paths and browser token issuance without submitting an actual enquiry.
- Exact-head PHP 7.4/8.2 checks, strict asset/sitemap checks, package preflight, non-deploying QA and Browser E2E must all pass before this PR is ready for owner review. Production staging/deployment remains owner-controlled.

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
