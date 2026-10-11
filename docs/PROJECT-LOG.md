# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-11 |
| Current phase | Phase 0 — repository hygiene and safety net |
| Base branch | `main` |
| Baseline commit | `b2c34cdb21a41a78c8b44332c02bbe481bd2d8c1` |
| Last merged PR | [#1 — Establish PHP CI and repository checks](https://github.com/tchouhanjsm/garh-jaisal-website/pull/1) |
| CI on main | [Actions run #4](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38115322127) previously reported successful |
| Production URL | https://www.hotelgarhjaisal.com |
| Active batch | `feature/phase-0b-hygiene` — open for owner review only after CI and final diff verification |
| Next action | Complete Phase 0 residual warnings and verify the owner-supplied WebP asset inventory before Phase 1 |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and a safe CI baseline | ◐ in progress |
| 1 | Site-wide audit with a prioritised backlog | ☐ |
| 2 | Booking conversion and mobile UX | ☐ |
| 3 | Technical SEO foundation | ☐ |
| 4 | Local SEO and content depth | ☐ |
| 5 | Performance and Core Web Vitals | ☐ |
| 6 | Accessibility | ☐ |
| 7 | Form, email and security hardening | ☐ |
| 8 | Analytics measurement | ☐ |
| 9 | Staging QA and cPanel handoff | ☐ |
| 10 | Monthly improvement loop | ☐ |

## Phase 0 — status and remaining work

- PR #1 is merged. The PHP/site-check workflow is present on `main`; the previous snapshot incorrectly described PR #1 as open.
- The repository's site checker has strict image-reference checks and source-level metadata heuristics. Warnings about metadata must be checked against rendered output; source-text warnings alone are not proof of a broken page.
- The checker previously reported 22 warnings in a local run. This number is historical and must be re-established from the current branch; it is not a claim about the current CI result.
- The sitemap currently has no `<lastmod>` values. Do not add dates unless the actual last-modified dates are supported by Git history.
- Legacy includes, backup files, local OS metadata and loader artifacts must be checked for tracked status and references before removal. File names alone are not proof that a file is safe to delete.
- The owner has a local `img/toWEBP/` directory with 68 new WebP images. Those local files are not available through the GitHub repository API until the owner supplies an inventory or commits/uploads them. Do not claim the images have been reviewed or committed yet.
- The seven room-image mappings must be preserved until replacement images have been visually compared and mapped to the correct room. Do not infer room identity from a filename alone.

## Current batch — `feature/phase-0b-hygiene`

- **Goal:** Reconcile the project state with merged PR #1 and continue the Phase 0 baseline safely.
- **Scope:** Project status accuracy and evidence-led cleanup/asset verification.
- **Not changed:** Public URLs, production configuration, hotel facts, room image mappings, CSS presentation or deployment settings.
- **Validation:** GitHub connector can read repository files and create branch/commits/PRs, but cannot run PHP or a real browser session. CI must be checked on the exact PR head; local PHP and 390px/1280px browser smoke tests remain owner-side verification unless their output is supplied.
- **Owner action:** Review the PR diff and actual CI status. Merge only when checks and local verification are acceptable.

## Findings and backlog

| ID | Priority | Finding | Next step |
|---|---|---|---|
| B-001 | P0 | CI workflow was absent from the initial baseline | Resolved by merged PR #1; verify CI on each new PR |
| B-002 | P1 | Source heuristics can flag metadata provided through shared includes | Verify rendered HTML; improve checker only when a deterministic false positive is demonstrated |
| B-003 | P1 | Sitemap contains no `<lastmod>` values | Use real Git history dates if adding lastmod; do not fabricate |
| B-004 | P1 | Two GA4 IDs are configured | Owner confirms whether both are intentional before changing analytics |
| B-005 | P1 | Enquiry recipients must remain private/server-configured | Verify configuration privately on staging/production |
| B-006 | P2 | Tracked OS metadata and loader artifacts were reported in earlier checks | Confirm exact tracked paths and references before removal |
| B-007 | P2 | Legacy header/footer and backup header remain candidates for cleanup | Prove unused across the repository before removal |
| B-008 | P1 | Experience content and prices are operationally sensitive | Confirm current inclusions, prices, timings and languages before public changes |
| B-009 | P1 | New WebP assets need inventory, visual matching and performance comparison | Review filenames/dimensions/size and create a verified mapping before referencing them |

## Non-negotiable conventions

- Branches use `feature/<short-name>`; one theme per branch.
- Conventional commits: `feat:`, `fix:`, `test:`, `chore:`, `docs:`, `content:`.
- Keep existing public URLs. URL retirement requires an owner-reviewed 301.
- Never commit `include/site-config.local.php`, mail credentials, server-only keys or passwords.
- Do not invent hotel facts, prices, inclusions, distances, schedules, testimonials, ratings or awards.
- Images are owner-supplied assets; do not rename or delete them without explicit need and proof.
- The owner is the only merge authority. Never claim a branch is merged or deployed until the owner does so.
- Do not claim tests passed unless output or CI evidence confirms the result. Verify checks against the exact final PR head SHA.
- If a check fails, diagnose the root cause and use a minimal fix commit; rerun the relevant checks until green or clearly report the remaining blocker.

## Owner decisions still needed

- Confirm whether both GA4 measurement IDs are intentional and which property should receive conversions.
- Confirm actual enquiry-recipient email addresses privately on the server.
- Confirm the cPanel PHP version before treating CI's PHP version as a compatibility guarantee.
- Confirm current experience inclusions, prices and operational descriptions before public release.
