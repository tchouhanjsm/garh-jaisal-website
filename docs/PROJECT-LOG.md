# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-11 |
| Current phase | Phase 5 — performance and Core Web Vitals (baseline instrumentation) |
| Base branch | `main` |
| Baseline commit | `9ea206ab697ba3095421947ab2447126fb95131b` |
| Last merged PR | [#12 — actionable rendered SEO finding summary](https://github.com/tchouhanjsm/garh-jaisal-website/pull/12) |
| CI baseline | Verify current workflow runs against the exact PR head; historical runs are not evidence for new commits |
| Production URL | https://www.hotelgarhjaisal.com |
| Active batch | `feature/phase-5-performance-baseline` — collect a repeatable rendered route/resource/image baseline in CI |
| Next action | Review Phase 5 baseline artifact and retries on the exact PR head; triage confirmed SEO findings separately before public metadata/content changes |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and a safe CI baseline | ✓ CI baseline merged; local cleanup and WebP verification remain |
| 1 | Site-wide audit with a prioritised backlog | ◐ in progress |
| 2 | Booking conversion and mobile UX | ◐ responsive navigation and shared booking URL merged; conversion measurement remains open |
| 3 | Technical SEO foundation | ✓ rendered metadata and JSON-LD regression checks merged in PR #6 |
| 4 | Local SEO and content depth | ◐ inventory and candidate finding reports exist; candidate findings still require review and verified remediation |
| 5 | Performance and Core Web Vitals | ◐ baseline instrumentation in this batch; optimisation and controlled Lighthouse/field measurement remain |
| 6 | Accessibility | ☐ |
| 7 | Form, email and security hardening | ☐ |
| 8 | Analytics measurement | ☐ |
| 9 | Staging QA and cPanel handoff | ☐ |
| 10 | Monthly improvement loop | ☐ |

## Phase 5 batch — rendered performance baseline (active)

- Add `tests/e2e/performance-baseline.spec.js`, running once in the `desktop-1280` Playwright project across sitemap routes.
- Record browser-exposed navigation timings, document/resource transfer and encoded/decoded sizes where Chromium provides them, same-origin resources, and rendered image attributes including dimensions, alt presence, loading mode and initial viewport visibility.
- Emit review candidates for large document/resource transfers, images missing explicit dimensions or `alt`, and below-fold images not marked lazy. These are diagnostic leads, not automatic proof of a defect; image visibility, accessibility intent and browser cache effects need review.
- Attach `artifacts/performance-baseline/rendered-performance-baseline.json` and include the directory in the existing 14-day GitHub Actions evidence artifact. No reports are written into the deployable website tree.
- The 250 KiB resource and 500 KiB document thresholds are triage thresholds only, not quality budgets or build failures. No thresholds are enforced as release gates before a baseline is reviewed.
- This is a local PHP + Chromium lab inventory, not a production crawl, Lighthouse score, or field Core Web Vitals measurement. Do not report the browser navigation values as production user experience.
- No public templates, content, URLs, hotel facts, prices, room-image mappings, analytics IDs, secrets or cPanel settings are changed.

## Prior phase records and non-negotiable conventions

The detailed Phase 0 hygiene notes, Phase A–C browser/SEO foundation, findings backlog, Smart Check role definitions, and Phase 4 rendered internal-link/SEO inventory records remain below. Treat any statement marked historical as historical; confirm current behavior from repository state and exact-head CI.

- Branches use `feature/<short-name>`; batch related implementation, tests, reporting and documentation into one coherent PR rather than creating a PR for each small follow-up.
- Conventional commits: `feat:`, `fix:`, `test:`, `chore:`, `docs:`, `content:`.
- Keep existing public URLs. URL retirement requires an owner-reviewed 301.
- Never commit `include/site-config.local.php`, mail credentials, server-only keys or passwords.
- Do not invent hotel facts, prices, inclusions, distances, schedules, testimonials, ratings or awards.
- Images are owner-supplied assets; do not rename or delete them without explicit need and proof.
- The owner is the only merge authority. Never claim a branch is merged or deployed until the owner does so.
- Do not claim tests passed unless output or CI evidence confirms the result. Verify checks against the exact final PR head SHA.
- If a check fails, diagnose the root cause and use a minimal fix commit; rerun the relevant checks until green or clearly report the remaining blocker.
- Do not commit `.DS_Store`; keep generated browser reports/screenshots as CI artifacts, not cPanel deployment files.

## Owner decisions still needed

- Confirm whether both GA4 measurement IDs are intentional and which property should receive conversions.
- Confirm actual enquiry-recipient email addresses privately on the server.
- Confirm the cPanel PHP version before treating CI's PHP version as a compatibility guarantee.
- Confirm current experience inclusions, prices and operational descriptions before public release.

## Phase 0 — hygiene and CI baseline (historical details)

- CI and rendered browser checks provide regression evidence but do not replace local PHP/browser review or staging verification.
- The sitemap currently has no `<lastmod>` values. Do not add dates unless the actual last-modified dates are supported by Git history.
- The owner has previously reported local `img/toWEBP/` assets. Local files are not available through the GitHub repository API until supplied or committed; do not claim they have been reviewed.
- Preserve all seven room-image mappings until replacement images have been visually compared and mapped to the correct room.
- Legacy includes, backup files, local OS metadata and loader artifacts must be checked for tracked status and references before removal.

## Phase 4 — local SEO and content depth (audit first)

- Audit the current sitemap entries and their intent before rewriting copy. The sitemap inventory in the audit report lists 26 URLs.
- Rendered internal-link and SEO inventory tests were merged in PRs #10 and #11. PR #12 adds a candidate finding summary to the rendered SEO inventory log and JSON artifact.
- Candidate findings include missing title, missing/multiple description tags, non-single main H1 count, canonical mismatch, invalid JSON-LD, explicit noindex, duplicate titles and duplicate descriptions. They require human review; duplicates/noindex can be intentional.
- See `docs/PHASE-4-LOCAL-SEO-AUDIT.md`. No public content or metadata should be changed without verifying the rendered defect and any owner-sensitive hotel facts.

