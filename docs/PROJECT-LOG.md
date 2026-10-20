# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-20 |
| Current phase | Sprint 53 — saved Content Studio management |
| Verified main SHA | ed75f9856e9e84b6c483ce637b28fba0f409066c (PR #109 merge commit) |
| Latest merged PR | [#109 — one-click local Content Studio launcher](https://github.com/tchouhanjsm/garh-jaisal-website/pull/109) |
| Main Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437203) |
| Main package preflight/build | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437165); package artifact only |
| Main non-deploying Staging QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437166); not external-host validation |
| Main Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39393437265); exact merge SHA |
| Recent merged work | PR #107 photo-led entry/mobile actions; #108 Content Studio authoring; #109 one-click local startup |
| Active phase PR | [#110 — manage saved Content Studio content safely](https://github.com/tchouhanjsm/garh-jaisal-website/pull/110); exact-head CI pending |
| Feature branch | feat/content-studio-draft-manager-20261020 |

## Current phase

**Sprint 53 goal:** let non-developers reopen, edit, publish locally, unpublish and safely delete Content Studio records without editing JSON manually.

- The manager lists only Content Studio JSON overlays. Legacy PHP registry pages are deliberately read-only.
- Editing keeps existing public slugs, preserves blog publication dates and retains custom registry fields that the form does not expose.
- Publishing/unpublishing updates the record and sitemap consistently. Hidden records and externally modified route wrappers are protected; live records must be made Draft before deletion.
- Draft deletion removes only the overlay record and the generated wrapper, retaining source images. Image/content directory symlinks are rejected; failed saves roll back record, sitemap and image-manifest state.
- Acceptance is pending until Site Checks on PHP 7.4/8.2, package preflight, non-deploying Staging QA and full Browser E2E pass on PR #110's exact head.

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
