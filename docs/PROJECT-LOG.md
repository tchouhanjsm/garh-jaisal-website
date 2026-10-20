# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Keep this file synchronized inside each implementation PR. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-20 |
| Current phase | Sprint 52 — one-click Content Studio startup and safe local preview |
| Base branch | main |
| Verified main SHA | f753107e98947f29ee30b849c7aa2bd91330ea5f (PR #108 merge commit) |
| Latest merged PR | [#108 — local Content Studio for tours, blogs and Gallery](https://github.com/tchouhanjsm/garh-jaisal-website/pull/108) |
| Main Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817921) on the verified merge SHA |
| Main cPanel package preflight/build | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817936); review artifact only, not deployment |
| Main non-deploying Staging QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817926); not evidence of external-host validation |
| Main Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817949), including blog lead-card regression and guided Content Studio authoring checks |
| Recent merged work | PR #106 Directions arrival journey; PR #107 photo-led entry/mobile actions; PR #108 local tour/blog/Gallery authoring |
| Current feature branch | feat/content-studio-one-click-launch-20261020 |
| Active PR | [#109 — one-click Content Studio startup](https://github.com/tchouhanjsm/garh-jaisal-website/pull/109); exact-head checks in progress |

## Current phase

**Sprint 52 goal:** remove the terminal-knowledge hurdle to starting the local Content Studio and its PHP preview, while preserving the local-only security boundary.

- Add an executable macOS .command launcher that starts the Node authoring server and PHP preview on loopback, opens the browser, gives readable dependency/port errors, writes local logs, and stops both services when the launcher is closed.
- Explain the one-click startup and stop workflow directly in the editor.
- Verify launcher shell syntax, executable mode, loopback-only behavior, cleanup behavior and exclusion from the cPanel runtime package.
- Preserve the existing tour/blog/Gallery form workflow, draft-first behavior, route/registry integration, sitemap checks and owner review boundary.
- Run the complete four-workflow acceptance suite on the exact feature head; this log is a plan until the phase PR is merged and its exact main SHA is checked.

## Current release backlog

| Priority | Item | Acceptance criteria | Status |
|---|---|---|---|
| P1 | Sprint 52 launch workflow | One-click local startup, safe loopback binding, server readiness/error messages, clean shutdown, automated tests | Open PR #109; exact-head Browser E2E pending |
| P1 | External staging/runtime validation | Owner-provided HTTPS staging URL and deployed package SHA; Apache/PHP and runtime headers validated on host | Owner-controlled and pending |
| P1 | Business-content approvals | Current menu/prices/hours, booking/cancellation, privacy/controller/retention, check-in/reception, room-rate policy and approved review copy | Blocked on owner facts |
| P2 | Human visual/accessibility review | Inspect page sheet and key pages at 320/390/768/1280/1440px; keyboard/focus, contrast, reduced motion and real assistive-technology review | Manual review pending |
| P2 | Image/caption sign-off | Owner confirms source-to-subject/caption mapping and approves responsive derivative crops before production promotion | Owner review pending |
| P2 | Production parity and release approval | Compare a reviewed staging package with existing production; confirm real CAPTCHA/mail and analytics receipt; explicit release approval | Not verified; no deployment |

## Owner-blocked dependencies

Do not infer or publish unconfirmed restaurant menu/prices/hours, booking and cancellation terms, privacy/data-controller and lead-retention details, check-in/check-out or reception promises, room-rate display policy, guest-review quotations, tour inclusions/pricing, transfer fares/timings, or specific Instagram post/reel embeds. Keep affected offers and policy pages qualified, draft or off-navigation until owner-provided facts are approved.

## Release boundary

Content Studio remains an operator-side local tool; it is not a hosted CMS or public editing endpoint. Draft/live values in the UI refer to the local working tree and must not be described as production publication. Packages are review artifacts only. This phase does not access cPanel, upload files, merge a PR or deploy to staging/production.
