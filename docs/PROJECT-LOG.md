# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-22 |
| Current phase | Sprint 54 — visual photo library for local Content Studio |
| Verified main SHA | 2a1797b40716a71ff9ab4b1c3d6e5c6e1d2773dc (PR #115 merge commit) |
| Latest merged PR | [#115 — keep unapproved landing pages private](https://github.com/tchouhanjsm/garh-jaisal-website/pull/115) |
| Main Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39733975303) |
| PR #115 Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39732186406); exact feature head |
| PR #115 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39732186351); artifact only |
| PR #115 non-deploying QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39732186373); not external-host validation |
| Recent merged work | PR #114 Landing Page Builder; #115 public Draft route guard |
| Active phase branch | `feat/content-studio-visual-photo-library-20261022`; exact-head CI pending |

## Current phase

**Sprint 54 goal:** let hotel staff visually find and reuse existing source photos without needing to know or type repository file paths.

- Add a local-only `GET /api/assets` inventory with supported raster types, normalized `img/...` paths and file sizes.
- Exclude generated responsive derivatives, hidden files and symbolic-link paths; keep the local-only loopback boundary and safe image CSP.
- Reuse one accessible photo picker for tour/blog/landing-page/Gallery hero fields and dynamic landing-page image/text sections.
- Keep alternative text independent and required; picking a photo never publishes, changes metadata, alters source photos or deploys.
- Acceptance is pending until PHP 7.4/8.2 Site Checks, strict image/sitemap checks, package preflight, non-deploying QA and complete Browser E2E pass on the exact PR head.

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
