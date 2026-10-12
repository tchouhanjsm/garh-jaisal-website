# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-11 |
| Current phase | Phase 5 — performance and Core Web Vitals baseline instrumentation |
| Base branch | `main` |
| Baseline commit | `9ea206ab697ba3095421947ab2447126fb95131b` |
| Last merged PR | [#12 — actionable rendered SEO finding summary](https://github.com/tchouhanjsm/garh-jaisal-website/pull/12) |
| CI on main | [Actions run #7](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38153905747) passed on merge commit |
| Production URL | https://www.hotelgarhjaisal.com |
| Active batch | `feature/phase-5-performance-baseline` — collect a repeatable rendered route/resource/image baseline in CI |
| Next action | Review the Phase 5 baseline artifact and retries on the exact PR head; triage confirmed SEO findings separately before public metadata/content changes |

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


## Phase A — browser automation foundation (merged in PR #4)

- Added Playwright browser smoke tests driven by the routes in `sitemap.xml`.
- Configured four Chromium viewport projects: 320×700, 390×844, 1280×800 and 1440×900.
- Each route is checked for a successful HTTP response, a document language, a non-empty title and uncaught browser page errors.
- Full-page screenshots are captured per route and viewport and attached to test results; CI uploads screenshots, traces, and the HTML report as a 14-day artifact.
- CI starts the plain-PHP site with PHP's built-in server. It does not contact production or submit live enquiries.
- Playwright is currently pinned to 1.55.0 in the workflow installation command; no lockfile is introduced in this initial foundation batch.
- Browser coverage and screenshot output passed in CI on the exact PR head before merge. This automation does not yet assert visual baselines or prove every physical device is supported.

## Phase 1 — accessibility and external-link audit (merged in PR #3)

- Added static diagnostics for image tags without a literal `alt` attribute and links using `target="_blank"` without a literal `rel` attribute.
- These are warnings, not build failures, because PHP templates can construct attributes dynamically. Confirm findings against rendered HTML before editing page templates.
- No user-facing page, content, asset, URL, analytics or production configuration changed in this batch.
- CI and local browser testing must be checked before merge; the repository connector cannot execute PHP or a browser locally.

## Phase B — mobile booking UX and efficient browser evidence (merged in PR #5)

- Move the responsive navigation breakpoint to 1080px so the eight-link desktop navigation does not compete for space at tablet widths.
- Keep sitemap-wide browser smoke checks, but capture full-page visual evidence only for home, rooms, contact and directions at each viewport. This keeps screenshots in GitHub Actions artifacts and out of the deployable site tree while reducing artifact volume.
- Assert that tested routes do not create horizontal document overflow at the configured viewports.
- Read the room-page booking URL from the shared public site configuration rather than duplicating it in the template.
- No public URLs, room-image mappings, hotel facts, prices, analytics IDs, secrets or production deployment settings changed.

## Phase C — technical SEO regression coverage (merged in PR #6)

- Add rendered-browser checks for every route listed in `sitemap.xml`, using one desktop viewport to avoid multiplying metadata tests across all responsive projects.
- Verify exactly one non-empty meta description and canonical link, an absolute HTTPS canonical whose path matches the sitemap route, one primary `main h1`, and parseable JSON-LD blocks where present.
- Keep this as regression detection: the test must expose real rendered-page issues rather than infer them from PHP source strings. Any failure must be investigated and fixed at the source, not weakened just to get green.
- No public URLs, content, image mappings, analytics IDs, secrets, or production deployment settings are intentionally changed in this batch.

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


## Smart Check skill (merged in PR #7)

- Added a reusable agent skill at `.agents/skills/smart-check/SKILL.md` to select checks based on changed paths and risk, use two-pass review, classify results as PASS/FAIL/BLOCKED/NOT RUN, and require exact-head CI evidence before a merge recommendation.
- Added `docs/SMART-CHECK-MATRIX.md` to map PHP/backend, frontend/responsive, technical SEO, content/local SEO, accessibility, security, booking/conversion, images, performance, and release checks to triggers and minimum regression coverage.
- This skill coordinates existing checks; it does not claim every matrix item is automated. Manual reviews and unavailable local/browser/cPanel execution must remain explicit.

## Smart Check roles (merged in PR #8)

- Reworked the skill so four reviewer roles (Full-Stack Developer, SEO Content Writer, Debugger, UI/UX Lead) each review every change. A role the change does not touch reports **N/A-GREEN**, and must still name what it looked at (the double-check rule).
- Role verdicts are GREEN, N/A-GREEN, YELLOW or RED. PASS/FAIL/BLOCKED/NOT RUN stays as the vocabulary for individual checks and CI runs.
- Rewrote `docs/SMART-CHECK-MATRIX.md` as a role-ownership table and documented current unautomated areas: SEO copy quality, forms/mail/security tests, booking and analytics wiring, and performance.
- Stop tracking the three committed `.DS_Store` files (already listed in `.gitignore`).
- Phase 4 is active in PR #9: audit existing pages and rendered metadata before changing public copy. Do not invent operational facts or alter existing public URLs.


## Phase 4 — local SEO and content depth (audit first)

- Audit current sitemap entries and their intent before rewriting copy. The present sitemap includes 26 URLs spanning core hotel pages, room booking, experiences, and guides.
- Keep the audit evidence-led: record each URL's purpose, existing title/H1/description, internal-link role, overlap risks, and whether any fact needs owner confirmation before suggesting content edits.
- Do not change public URLs, canonical targets, sitemap membership, prices, operational timings or room/image mappings as part of an audit-only step.
- This phase's initial PR also adds a Claude Code-discoverable entrypoint at `.claude/skills/smart-check/SKILL.md`, pointing to the canonical `.agents/skills/smart-check/SKILL.md`. This addresses the path compatibility noted in PR #8.
- No production site or live enquiries are touched. Browser/PHP checks are to be validated by CI at the exact PR head; public content remains unchanged in this initial batch.


## Phase 4 follow-up — rendered internal-link regression (PR #10 merged)

- Added `tests/e2e/internal-links.spec.js` to inspect same-site anchor links from each of the 26 sitemap routes using the local PHP test server.
- Runs only in the `desktop-1280` Playwright project to avoid duplicating the same link crawl at every viewport.
- Skips fragments, non-navigation protocols, downloads and external origins; checks each unique same-origin target without following redirects.
- This test is intended to identify broken local destinations. Any failure must be inspected for legitimate endpoint behavior or URL normalization before changing production links.
- No public content, URLs, assets, analytics IDs, hotel facts, prices, secrets or production configuration changed.
- PR #10 was merged by the owner. The test and local-PHP limitation remain documented; review its CI run for retries/warnings before treating the audit as fully clean.


## Phase 4 follow-up — rendered SEO inventory (PR #11 merged)

- Added `tests/e2e/seo-inventory.spec.js` to render each sitemap route at 1280px and record HTTP status, title, meta descriptions, canonical URLs/path alignment, primary H1 values, robots meta, unique internal/external link destinations and JSON-LD validity/types.
- The inventory is evidence for review, not an assertion that the production site was crawled or that metadata is high quality. The existing SEO and internal-link regression tests remain the enforcement gates.
- The browser workflow uploads `artifacts/seo-inventory/rendered-seo-inventory.json` with existing screenshot/trace/report evidence, retaining artifacts for 14 days.
- No public templates, metadata values, routes, hotel facts, room-image mappings, prices, analytics IDs, secrets or production configuration changed.
- PR #11 was merged by the owner. Browser E2E passed on PR head `8957d4ce36778d9f3901e689a0afbb4db6012e58` with 163 passed and 161 skipped tests; the skipped cases are project/viewport-specific by design. The JSON inventory was included in the 14-day artifact. The log showed Node deprecation warnings from the runner/actions; they did not fail this run.


## Phase 4 follow-up — actionable SEO finding summary (PR #12 merged)

- PR #12 extends the rendered inventory to emit candidate findings in both the JSON artifact and Playwright log: missing title, missing/multiple description tags, H1 count not equal to one, canonical mismatch, invalid JSON-LD, explicit noindex, duplicate titles and duplicate meta descriptions.
- These are review candidates, not automatic SEO quality judgments. Duplicate metadata or noindex directives must be checked against page intent before changing content.
- Existing strict SEO checks and route smoke tests remain unchanged. No public content or production files were changed in this batch.

## Phase 5 batch — rendered performance baseline (active)

- Add `tests/e2e/performance-baseline.spec.js`, running once in the `desktop-1280` Playwright project across sitemap routes.
- Record browser-exposed navigation timings, document/resource transfer and encoded/decoded sizes where Chromium provides them, same-origin resources, and rendered image attributes including dimensions, alt presence, loading mode and initial viewport visibility.
- Emit review candidates for large document/resource transfers, images missing explicit dimensions or `alt`, and below-fold images not marked lazy. These are diagnostic leads, not automatic proof of a defect; image visibility, accessibility intent and browser cache effects need review.
- Attach `artifacts/performance-baseline/rendered-performance-baseline.json` and include the directory in the existing 14-day GitHub Actions evidence artifact. No reports are written into the deployable website tree.
- The 250 KiB resource and 500 KiB document thresholds are triage thresholds only, not quality budgets or build failures. No thresholds are enforced as release gates before a baseline is reviewed.
- This is a local PHP + Chromium lab inventory, not a production crawl, Lighthouse score, or field Core Web Vitals measurement. Do not report the browser navigation values as production user experience.
- No public templates, content, URLs, hotel facts, prices, room-image mappings, analytics IDs, secrets or cPanel settings are changed.
