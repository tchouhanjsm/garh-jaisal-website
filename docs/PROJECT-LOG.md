# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-27 |
| Current phase | Sprint 68 — accessible experience detail navigation (stacked on Sprint 67) |
| Verified main SHA | df812a33675a5d31d4ce388629038cf5a7960105 (PR #140 merge commit) |
| Latest merged PR | [#140 — unified public contact channels and structured data](https://github.com/tchouhanjsm/garh-jaisal-website/pull/140) |
| Recent merged work | PRs #138 Car & City enquiry discovery, #139 contact-channel source of truth, #140 contact/schema parity |
| Active phase branch | `feature/experience-detail-navigation-20261027` — Sprint 68 draft PR #143, based on PR #141 |

## Current phase

**Sprint 68 goal:** let guests jump directly to the itinerary, inclusions and FAQ on existing live experience detail pages while preserving all current content and enquiry safeguards.

- Update the shared experience detail template with contextual section links, conditionally rendered for each live page.
- Add focused source/browser tests for section-target parity and responsive layout.
- PR #141 is awaiting a successful final browser/screenshot gate. Its prior browser run was cancelled; Site Checks, package preflight and non-deploying QA passed on `be153e41dfbdaf956fce709f1271f8933a3ae07b`. The new exact-head run on `e45a8d770b273ad2c4760c0302f70c957a5cb1be` increases the browser job timeout to 40 minutes. No merge or deployment until the gate completes.

## Latest implementation entry

- [2026-10-27] PR #140 merged at `df812a33675a5d31d4ce388629038cf5a7960105`. Sprint 65 public contact channels and Sprint 66 structured-data parity are in `main`; Site Checks, package preflight and non-deploying Staging QA passed. Merge-head Browser E2E was cancelled and must not be treated as a pass.
- Sprint 65–66 / PRs #139–#140 merged the visible contact and structured-data parity slice. Browser screenshot evidence remains absent because the merge-head Browser E2E run was cancelled.
- Sprint 67 / PR #141 contains the custom-itinerary page, browser-only draft composer, contextual contact fallback, open-ended Car & City links, sitemap and regression contracts. Latest Site Checks, package preflight and non-deploying QA pass; Browser E2E/screenshot evidence is still in progress.
- Sprint 68 / PR #143 is a draft stacked on #141, for section navigation on the six live experience detail pages. It must be retargeted to `main` after #141 merges and checked against its exact head.

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

## 2026-10-27 — PR #146 and next bulk phase

- PR #146 merged the side-by-side room comparison interaction.
- Active follow-on: room shortlist → Contact enquiry continuity. This is a complete guest handoff, not a standalone style tweak.
- Required validation covers allowlisted room IDs, multi-room copy, safe retry URLs, the contact context token, unchanged booking links and responsive dialog behavior.
- No production deployment or live enquiry submission is performed.
