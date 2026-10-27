# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-27 |
| Current phase | Sprint 67 — open-ended custom itinerary discovery |
| Verified main SHA | df812a33675a5d31d4ce388629038cf5a7960105 (PR #140 merge commit) |
| Latest merged PR | [#140 — unified public contact channels and structured data](https://github.com/tchouhanjsm/garh-jaisal-website/pull/140) |
| Recent merged work | PRs #138 Car & City enquiry discovery, #139 contact-channel source of truth, #140 contact/schema parity |
| Active phase branch | `feature/custom-itinerary-builder-20261027` — Sprint 67 draft PR #141 |

## Current phase

**Sprint 67 goal:** let guests shape a custom Jaisalmer enquiry beyond the six proposed themes, while keeping the options explicitly non-bookable until the hotel confirms details.

- Add a standalone custom-itinerary page with multi-select interests, travel context and guest-written stops/preferences.
- Keep all form data in the browser, render a WhatsApp draft for review, and open WhatsApp only after an explicit guest click.
- Treat the six themes as examples, never as the complete list of offerings. No invented routes, prices, schedules, guide availability or booking confirmation.

## Latest implementation entry

- [2026-10-27] PR #140 merged at `df812a33675a5d31d4ce388629038cf5a7960105`. Sprint 65 public contact channels and Sprint 66 structured-data parity are in `main`; Site Checks, package preflight and non-deploying Staging QA passed. Merge-head Browser E2E was cancelled and must not be treated as a pass.
- Sprint 65–66 / PRs #139–#140 merged the visible contact and structured-data parity slice. Browser screenshot evidence remains absent because the merge-head Browser E2E run was cancelled.
- Sprint 67 / PR #141 opens the custom itinerary builder scope first; implementation and exact-head CI are next.

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
