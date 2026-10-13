# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-13 |
| Current phase | Sprint 1 — shared page-head/schema foundation; groups 1–3 and experience renderer are being completed; UI partials/dead-asset removal remain |
| Base branch | `main` |
| Baseline commit | `438946452872dcc8b87fb593eff6869858f9d75d` (main after PR #44) |
| Last merged PR | [#44 — migrate packages and tour pages to shared head](https://github.com/tchouhanjsm/garh-jaisal-website/pull/44) |
| CI evidence | PR #44 exact head `8e43f40bfb180c2c32c02dd11e7a6b80dd2aa23c`: [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38359596089) and [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38359595950) passed; [page-sheet artifact](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38359595950/artifacts/11793244750) available |
| Production URL | https://www.hotelgarhjaisal.com |
| Active batch | `feature/sprint-1-page-head-completion` — migrate Dining, Blog, guides and the shared experience renderer while preserving frozen head output and both GA4 IDs |
| Next action | Review PR #45 on its exact head, including `page-sheet`; after owner-merge, build reusable UI partials, then remove reference-verified dead assets (fonts in a separate revertible commit) |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and a safe CI baseline | ✓ CI baseline merged; local cleanup and WebP verification remain |
| 1 | Site-wide audit with a prioritised backlog | ◐ in progress |
| 2 | Booking conversion and mobile UX | ◐ responsive navigation and shared booking URL merged; conversion measurement remains open |
| 3 | Technical SEO foundation | ✓ rendered metadata and JSON-LD regression checks merged in PR #6 |
| 4 | Local SEO and content depth | ◐ inventory and candidate finding reports exist; candidate findings still require review and verified remediation |
| 5 | Performance and Core Web Vitals | ◐ baseline captured; intrinsic geometry merged in PR #38; PR #39 merged 11 same-dimension WebP derivatives (2,909,109 B saved across the accepted subset); controlled Lighthouse/field measurement remains open |
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


## Phase 5 follow-up — reserve image geometry for priority hero assets

- The PR #13 rendered baseline artifact (run [38194646100](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38194646100), artifact ID 11722384880) checked 26 sitemap routes. It recorded 117 missing-explicit-dimension candidates and 48 large-resource route occurrences; these are local Chromium observations, not production performance metrics.
- Review of the route inventory showed the highest-priority shared experience hero images lacked width/height attributes, while the homepage facade attributes described the opposite aspect ratio from the observed intrinsic image (1333×2000).
- Add observed intrinsic dimensions to the shared experience hero data and correct the homepage facade dimensions. This reserves the correct geometry before image load and reduces layout-shift risk without changing the selected images, public content, URLs or room mappings.
- Add a browser regression check for high-priority images on sitemap routes. Do not treat this as image compression: the large image payloads remain a separate task requiring an approved/visually verified optimized asset set.
- Baseline run artifact is CI evidence and stays out of cPanel. Production field Core Web Vitals and Lighthouse remain unmeasured.


### CI follow-up — priority-image coverage gap

- Browser E2E on PR #14 initially failed because the new regression test found three remaining high-priority images without explicit dimensions: the desert-safari hub hero, guided-Jaisalmer-tour hub hero, and two-day-itinerary hero (the facade asset appears on two routes). This is a real coverage gap in the implementation, not a reason to weaken the assertion.
- Added the intrinsic 1366×768 dimensions to the desert image and 1333×2000 dimensions to facade images on those routes. Re-run PHP 7.4/8.2 and full Browser E2E on the updated exact head.
- The run reviewed so far reports the image-dimension assertion failure; it does not expose a row-selector cleanup failure or a formatter check. If those are present in a newer run, inspect that run's job logs before declaring readiness.


## Phase 5 follow-up — deduplicated image payload audit (PR in progress)

- PR #14 was merged by the owner. The high-priority image dimension regression check now covers the missing hero dimensions identified in CI; image geometry is addressed separately from file-size optimization.
- Add a sitemap-wide Playwright inventory that deduplicates image URLs and links each asset to every route and rendered usage, including observed format, natural dimensions, width/height attributes, loading mode, fetch priority, viewport visibility, and available Chromium transfer/encoded/decoded sizes.
- Sort the report by observed transfer size and flag resources above 250 KiB as review candidates. This is triage evidence, not an enforced performance budget; browser resource timing can return zero/unavailable sizes and must not be mistaken for actual file bytes.
- Save `artifacts/image-payload-audit/image-payload-audit.json` in the existing 14-day GitHub Actions evidence artifact. No report is written into the deployable website tree.
- Do not replace or recompress room, facade, courtyard, or experience imagery without visually comparing the proposed asset against the current image and confirming the exact page/room mapping. The previously noted local `img/toWEBP/` collection is not available through the repository unless uploaded/committed by the owner.
- This batch improves prioritization and evidence quality; it does not claim image payloads have already been reduced or production Core Web Vitals measured.


## Phase 6 — rendered page layout and information architecture inventory (PR in progress)

- Add a rendered inventory for all 26 sitemap routes at 1280×800, capturing title/headings, main structure, section counts, hero/card/gallery/form/table/accordion/breadcrumb/CTA signals, image attributes, navigation links and visible CTA destinations.
- Store JSON under `artifacts/page-layout-inventory/page-layout-inventory.json` in the existing 14-day CI artifact. This is a structural inventory, not an automated judgement of visual quality or a substitute for reviewing screenshots at mobile/tablet/desktop widths.
- No public pages, content, URLs, SEO metadata, assets, booking links, prices, analytics IDs, secrets or production settings change in this batch.
- Use the inventory with existing responsive screenshots and SEO inventory to create a page-by-page review list. Discuss proposed new pages before implementation: search intent, audience, unique value, required verified facts, links to existing pages, CTA and URL/canonical plan. Do not add new public pages or sitemap URLs without that discussion and owner approval.
- PR #15 was merged by the owner; the image payload audit provides a separate route-aware asset report. Actual image optimization remains gated on verified file sizes and visual mapping.


## Site architecture blueprint (approved by the owner 2026-10-12)

Plan documents: [`docs/SITE-ARCHITECTURE.md`](SITE-ARCHITECTURE.md) and [`docs/LAYOUT-REVIEW.md`](LAYOUT-REVIEW.md).

**Owner decisions**
- Menu approved: About, Stay, Dining, Packages (Desert Tours and City Tours for now), Directions, Blog, Contact, Book Now. Gallery sits under About.
- Existing public URLs stay unchanged unless a specific duplicate is owner-approved for 301 redirect.
- Hidden pages must remain HTTP 200 with `noindex,follow`, and be absent from navigation, hubs and sitemap. Draft tour pages remain unlinked, absent from sitemap and noindex until facts and publication are approved.
- New tour facts and privacy/cancellation wording must come from the owner. Do not invent operational facts.
- Owner retains merge/deploy authority. One focused PR at a time, based on current main; read both CI workflows at the exact head SHA and fix failures before review.

**Batch status (reconciled after PR #18 merged)**

| Batch | Scope | Status |
|---|---|---|
| Docs | Approved blueprint and layout review | Superseding documentation PR in progress; this log reflects the merged navigation state |
| A | Data-driven navigation, mobile dropdowns, footer links, active page states | Merged in PR #18 |
| B | Design tokens, shared visual components, contrast and responsive review | Merged in PR #20 |
| C | Experience data model, hub rendering, breadcrumbs and structured data | In progress in PR #21 |
| D | Duplicate/orphan decisions, reviewed redirects, status-aware sitemap | In progress in `feature/status-aware-sitemap-orphan-audit` |
| E | Car & City hub and new tour pages, draft-only until facts are confirmed | Blocked on owner facts before publishing |
| F | Home, Rooms and Packages redesign | Planned |
| G | Tour pages, Directions, Dining, Gallery and Contact redesign | Planned |
| H | Owner-approved policies, sitemap generator and freshness checks | Planned |

**PR #18 implementation notes**
- Navigation source: `include/nav-data.php`; the header and the relevant footer links render from this data.
- The Packages panel currently lists existing destinations only. The future Car & City hub is not yet implemented; do not link to it until the page exists.
- PR #18 preserved existing URLs, metadata, sitemap, analytics IDs, booking URL, hotel facts, prices, timings and server configuration.
- PR #18 reports passing site-check and Browser E2E workflows on head `6d5e49aecfe96a9a575d9a66e675c2ecdfd83e9c`. Keyboard/screen-reader and contrast still need explicit accessibility review.

**Next implementation phase**
- Batch B: shared design tokens/components and responsive/contrast checks. Preserve current rendered content and real image mappings; no invented copy or hotel facts.
- Keep CI artifacts out of the deployable website. Do not claim production performance or local execution from CI-only evidence.

## Batch B — shared design system and responsive guardrails (PR in progress)

- Add the approved type, spacing, radius, shadow and accent tokens while retaining the existing heritage palette and Inter / Source Serif pairing.
- Add opt-in shared primitives for cards, at-a-glance facts and CTA bands; existing templates are only progressively migrated in later page batches to reduce visual regression risk.
- Standardise visible keyboard focus, solid-brown primary buttons, reduced-motion behavior and small-screen section rhythm. No global markup rewrite in this batch.
- Preserve page copy, public URLs, canonical/SEO metadata, booking destinations, real image selections and room-image mappings.
- Validation gate: PHP 7.4/8.2 lint and strict site checks; full sitemap Playwright E2E across 320, 390, 1280 and 1440 widths; rendered contrast checks for the core text/button palette; verify screenshot artifacts for home, rooms, contact and directions.
- Connector limitation: local PHP/browser screenshot review is not available in this session; use CI evidence and keep any visual judgment not supported by screenshots marked NOT RUN. Review must be on the exact final PR head SHA.


## Batch C — data-driven experiences, breadcrumbs and structured data (PR in progress)

- Added category, parent, menu label, duration type, order and publication status to current experience entries without changing existing operational copy, prices, public URLs or image mappings.
- Replaced hard-coded package and tour-card slug arrays with filters over shared experience data, ordered by explicit data order.
- Extended the shared experience renderer to build hierarchical breadcrumbs from each experience parent, matching BreadcrumbList structured data.
- Related experience cards omit non-live entries; the shared renderer emits `noindex,follow` for non-live entries.
- Sitemap generation/status-aware membership and duplicate/orphan decisions remain Batch D; no sitemap URLs or redirects changed in Batch C.
- Validation gate: PHP 7.4/8.2 site checks, browser E2E across 320/390/1280/1440, rendered breadcrumb/schema checks, no broken internal links, and exact-head CI evidence before owner review.


## Batch D — publication-aware sitemap and orphan/overlap hardening (PR in progress)

- Added `scripts/generate-sitemap.php` with a reviewed explicit allowlist for non-experience pages and experience URLs sourced from `include/experience-data.php`.
- Sitemap membership now derives from `status === live`; `draft` and `hidden` experiences are excluded automatically. `--check` compares the tracked sitemap byte-for-byte with generated output so CI can detect stale membership.
- Added the sitemap consistency check to the PHP CI workflow and extended rendered SEO regression coverage to fail when a sitemap-listed route explicitly emits `noindex`.
- Kept the existing sitemap priorities and all current URLs. No `lastmod` values were added because reliable per-page modification dates have not been established; dates must not be fabricated.
- Duplicate/orphan review is still evidence-led: `explore-the-desert.php` claims a full-day experience while `sunset-camel-safari.php` is presented as a half-day/evening product, so a 301 must not be applied until the operational distinction is confirmed. `camping.php`, `city-tour.php` and `car-rental.php` remain on their existing URLs and are not deleted in this batch. The car-rental page needs the Car & City hub planned for Batch E.
- No verified hotel facts, rates, timings, image mappings, booking targets, analytics IDs, or server settings changed.
- Validation gate: PHP 7.4/8.2 lint and strict site checks, sitemap generator `--check`, rendered SEO/internal-link checks, responsive Browser E2E and exact-final-head CI evidence. Connector limitations mean local PHP/browser execution is not claimed.


## Batch E — Car & City Tours hub and draft-only tour scaffolding (PR in progress)

- Adds the live `car-tours.php` hub as the Packages > Car & City Tours destination and includes the existing guided-tour hub, Fort walk (`city-tour.php`) and car-arrangement page (`car-rental.php`) so those existing URLs have an intentional discovery path.
- Adds Longewala and Rajasthan tour entries to `include/experience-data.php` with `status: draft`, unique draft titles/descriptions, explicit NEEDS OWNER INPUT text and minimal placeholder content. Draft wrappers use the shared renderer, which emits `noindex,follow`; they are not linked from public navigation/hubs and are excluded from sitemap generation.
- Adds `car-tours.php` to the reviewed static sitemap allowlist and tracked sitemap; updates SEO/browser checks to verify the hub and draft publication rules.
- No prices, routes, distances, durations, vehicle capacities, pickup details, languages, fees, inclusions or availability are invented. Existing page URLs, analytics IDs, booking destination and image mappings are preserved.
- Existing `camping.php` and the overlap between `explore-the-desert.php` and `sunset-camel-safari.php` remain unresolved; no speculative redirects or page deletions are included in this batch.
- Validation gate: PHP 7.4/8.2 lint, strict site checks, deterministic sitemap check, Browser E2E, internal links, SEO metadata, draft noindex/sitemap exclusion and screenshot artifacts on exact final head. Connector cannot execute local PHP/Chromium, so local tests are not claimed.


## Batch F1 — car/city discovery and legacy content audit (PR in progress)

- Start the Phase F redesign path with an evidence-led discovery/UX hardening batch following PR #23. Keep existing public URLs and their canonical targets; do not merge or redirect potentially overlapping content without owner confirmation.
- Add regression coverage that confirms the Car & City hub links to the existing guided-tour hub, Fort walk and car-arrangement pages, and that all three existing destination pages provide a clear return path to the Car & City hub.
- Strengthen site checks for the experience publication boundary: live experience pages should remain sitemap-aligned, while draft/hidden experiences must not be referenced by public navigation/footer data or included in the sitemap. Keep exceptions explicit where hubs themselves are not individual tour cards.
- Audit `camping.php` and the `explore-the-desert.php` / `sunset-camel-safari.php` distinction; record facts needed from owner before any content consolidation/redirect decision. Do not invent operating facts or change GA4, booking, images or current public URLs.
- Next after this batch: Phase F page redesign, starting with the Packages hub as its information architecture now has both Desert Tours and Car & City Tours. Home and Rooms follow after the first redesigned page has responsive evidence.
- Validation gate: PHP 7.4/8.2 lint and strict site checks, deterministic sitemap check, full Browser E2E at 320/390/1280/1440, internal-link checks and rendered SEO checks on exact final PR head. Local PHP/browser is not available through this connector; use CI evidence and do not claim local execution.


## Batch F2 — Packages hub category-first redesign (added to PR #24)

- Replace the tree-style experience map with two clear category cards: Desert Tours and Car & City Tours. Each links to the existing live hub and uses existing approved experience descriptions only.
- Add a hero action that jumps directly to the category chooser; preserve the existing availability/contact CTA.
- Add responsive card layout, visible keyboard focus, reduced-motion behavior, and section scroll offset for the shared header.
- Add browser coverage for category count, category destinations, hero jump link, and scroll offset. Existing site-wide viewport smoke tests continue to cover horizontal overflow at 320/390/1280/1440.
- Fix the discovery regression assertion to compare normalized URL pathnames rather than requiring a leading slash in a relative HTML `href`. This keeps destination and visible-label assertions meaningful without coupling the test to relative-vs-absolute markup.
- Preserve existing package content sections, public URLs, canonical metadata, JSON-LD, analytics, image mappings, contact/booking paths, and operating facts.
- Validation gate: PHP 7.4/8.2 site checks and Browser E2E on the exact updated PR head; do not treat a successful PHP-only run as complete.


## Batch F3 — Home and Rooms discovery redesign (PR in progress)

- Add a compact, three-path discovery strip immediately below the home hero, linking to the existing Rooms, Packages and Rooftop pages. Preserve the current hero booking and arrival CTAs.
- Add a generated floor-jump navigation on the Rooms page based on the existing room data registry. Each link targets a stable, sanitized ID generated from the floor label; room records, names, descriptions, images and booking destinations remain unchanged.
- Keep a persistent check-availability action in the floor navigation and offset anchor scrolling for the shared header. Use responsive layouts for narrow screens, visible keyboard focus, and reduced-motion support.
- Add rendered Browser E2E coverage for the three home discovery paths and room-floor anchor integrity/booking target attributes. Existing viewport projects continue to check 320, 390, 1280 and 1440 widths and horizontal overflow.
- Do not invent room facts or change canonical URLs, metadata, JSON-LD, GA4 IDs, images, booking configuration or deployment settings.
- Validation gate: both Site Checks (PHP 7.4/8.2, strict checks, sitemap and publication boundary) and Browser E2E must pass on the exact final PR head. CI screenshot artifacts are the visual review evidence; no local execution is claimed by this connector.


## Batch F4 — Packages hub redesign (PR #26 in progress)

- Continue Phase F from the merged F3 discovery work. Make the Packages hub a clearer route from the hero to the two category hubs, then to live experience cards and planning guidance.
- Remove the repeated introductory category-card section that duplicated the primary category chooser and lengthened the page without adding a distinct decision.
- Tighten the category chooser heading/copy, retain the existing Desert Tours and Car & City Tours destinations, and add stable section IDs for desert options, city options and time-based planning.
- Add Browser E2E assertions for the concise section sequence, category destinations, and presence of live desert/city cards. Existing SEO, sitemap, publication-boundary and four-viewport checks remain unchanged.
- Preserve existing public URLs, canonical/OG/Twitter metadata, JSON-LD, GA4 IDs, approved experience descriptions, image mappings, contact/arrival paths and operating facts. Do not publish draft tours or add unverified prices/timings.
- Validation gate: PHP 7.4/8.2 lint and strict site checks, sitemap freshness/publication checks, and Browser E2E with screenshots on the exact final PR head. GitHub connector cannot run local PHP/Chromium, so local execution is not claimed.
- Next milestone after F4: redesign the Home page beyond the discovery strip, then the Rooms page, using exact-head responsive screenshot evidence before proceeding to the wider Phase G pages.


## Batch F5 — Homepage conversion and arrival UX (PR #27 merged)

- Continue Phase F after PR #26. Improve the homepage's practical decision path by surfacing the fort access constraint immediately after the existing Rooms/Packages/Rooftop discovery links.
- Add a compact, responsive "Before you arrive" notice explaining that vehicles cannot enter Jaisalmer Fort, the final stretch is on foot, and reception hours are 7:00 AM–10:30 PM. Link directly to the existing arrival guide.
- Add stable, accessible section IDs and heading associations for the homepage Rooms, Rooftop, Desert Experiences, full Arrival Guide and Gallery sections.
- Add rendered Browser E2E checks for the arrival notice, destination/section anchors, and booking CTA target/rel safety. Existing sitemap, metadata, internal-link and four-viewport checks remain required.
- Preserve the existing booking URL/configuration, public routes, canonical/OG/Twitter metadata, JSON-LD, GA4 IDs, approved images and room/tour details. Do not invent facilities, pricing, transport arrangements or availability.
- Validation gate: PHP 7.4/8.2 lint, strict site checks, sitemap/publication boundaries and Browser E2E/screenshots on the exact final PR head. The connector cannot execute local PHP/Chromium; no local run is claimed.
- Next milestone: Phase F6 — full Rooms page redesign, including room comparison, booking paths and mobile usability.


## Batch F6 — Full Rooms page UX redesign (PR #28 in progress)

- Redesign the Rooms page as a clear booking journey: room-led hero, data-derived at-a-glance summary, concise directory intro, floor navigation with room counts, comparison-friendly cards and clear availability actions.
- Improve the room cards' visual hierarchy and scanability while keeping the existing registry as the source of room names, descriptions, images, alt text and listed features. Each room receives a stable, sanitized in-page anchor ID.
- Keep the current floor grouping and provide count labels derived from the same registry; room totals are not hard-coded in the rendered page.
- Add Browser E2E assertions for the seven registered rooms, floor counts, image alt text, listed features, booking URL consistency and new-tab safety. Check the room card layout at all configured viewports; existing site-wide SEO, internal link, page error and screenshot checks remain required.
- Preserve the public `room.php` route, canonical/social metadata, JSON-LD breadcrumb, analytics IDs, existing image mappings, booking URL/configuration and existing room data. Do not invent prices, occupancy, availability or facilities.
- Validation gate: PHP 7.4/8.2 lint and strict site checks; sitemap/publication boundary checks; Browser E2E with screenshots at 320/390/1280/1440 on the exact final PR head. The GitHub connector cannot run local PHP/Chromium, so no local run is claimed.
- Owner retains review/merge and production deployment authority.
- Next milestone: Phase F6 — full Rooms page UX redesign after this homepage flow has passed responsive review.


## Batch D2 — resolve desert overlaps (owner decision, PR in progress)

Owner decision (2026-10-12): merge duplicates and orphans properly; if a page is not relevant, hide it temporarily and review at the end. No redirects or deletions yet.

- `explore-the-desert.php` → `status: hidden`. It repeats the sunset-safari route steps and was not confirmed as a distinct product. It keeps its URL (returns 200, `noindex,follow`), is removed from the Desert Tours hub, the sitemap (26 URLs) and related links. Reversible by setting `status` back to `live` and re-running `php scripts/generate-sitemap.php`.
- `camping.php` kept live and linked. It targets the "camping" search intent and routes to Sleep Under the Stars and Swiss Tent; it was an orphan, now linked from the Desert Tours hub.
- Desert hub heading no longer hard-codes a count ("Five ways" → "Choose how you want to experience the desert.").
- New regression test: the hidden page stays out of the sitemap and out of the hub, packages and sunset-safari links, while keeping a non-error response with `noindex,follow`. Existing camping and hub link tests extended.
- No hotel facts, prices, timings, images, booking links, GA4 IDs or public URLs changed.

### Hidden-page review register (revisit at the end)

| Page | State | Decision needed |
|---|---|---|
| `explore-the-desert.php` | hidden (noindex, not in sitemap) | Distinct full-day product (rewrite and restore) or 301 to `sunset-camel-safari.php`? A 301 needs a root `.htaccess`, which the repo does not have yet. |
| `longewala-tour.php`, `rajasthan-tours.php` | draft | Owner facts needed before publishing. |
## Hygiene — ignore node_modules, remove saved 404 file (PR in progress)

- `.gitignore` now ignores `node_modules/`, so a local `npm install` for the browser tests can never be committed or end up in the cPanel deployable tree.
- Removed `img/loader.html`: a saved "404 Not Found" HTML page, not an image. Its only reference is `css/style.css`, a legacy stylesheet that no PHP page or include loads. `css/style.css` itself is left for the end-of-project legacy CSS review.
- No public URL, page content, image mapping, booking link, GA4 ID or secret changed.



## Batch F7 — photo heroes and dark-surface accessibility polish (PR in progress)

- Reviewed commit [a2ea296](https://github.com/tchouhanjsm/garh-jaisal-website/commit/a2ea2962ef4be6bed0d8302035896531089a9fa2); retained its four existing-photo WebP hero variants and targeted dark-surface secondary-button styles.
- Added the shared photo hero variant to Rooms, Directions, Dining and Gallery. The four WebP files in the linked commit were not available on the main-based PR branch and could not be transferred through the connector's text-only binary interface. To keep the PR buildable, the hero references now point to existing checked-in photos (`img/v2/purple-room.jpg`, `img/v2/courtyard.jpg`, `img/v2/fort-from-roof.jpg`, `img/v2/rooftop-dusk.jpg`). Confirm the visual crop/semantic match from CI screenshots before merging.
- Added browser regression coverage for the dark-surface button style contract. Strict site checks verify local image references exist; responsive Browser E2E/screenshots are required to review hero crops and image/gradient contrast.
- The supplied contrast test compared text against an ancestor's computed background, which does not prove effective contrast over transparent backgrounds and CSS/image gradients. The replacement assertion explicitly checks the affected components' intended foreground and outline style. True image/gradient contrast still requires screenshot/visual inspection; this suite does not claim to compute pixel-level contrast.
- Preserved room/experience data, booking links, SEO metadata and tracking. No production action or merge.
- Browser E2E initially found the Directions hero declared as 1600×1066 while its checked-in courtyard image is 1333×2000. Corrected the HTML width/height attributes to the image's intrinsic dimensions so the browser reserves the right aspect ratio; the hero's CSS still uses `object-fit: cover`. Re-run the full browser suite on the exact final PR head.
- Validation gate: PHP 7.4/8.2 lint, strict site checks, sitemap/publication rules, Browser E2E at 320/390/1280/1440, and screenshot evidence against the exact final PR head. Connector cannot run local PHP/Chromium; local runs are not claimed.
- Next milestone: Phase F8 — accessibility hardening from rendered browser evidence: audit focus order, visible focus, keyboard access, heading/landmark structure, and accessible names at mobile and desktop widths; fix only confirmed issues and preserve operational content.


## Phase F8 — accessibility hardening (PR in progress)

- Add a visible-on-focus “Skip to main content” link in the shared header and give the rendered main landmark a stable, focusable destination. This is wired in the shared navigation script because the header include is emitted before page-specific main markup.
- Correct Escape behavior: closing the mobile navigation returns keyboard focus to its trigger; closing a desktop submenu returns focus to that submenu's toggle instead of leaving focus in a hidden panel.
- Add responsive browser regression assertions across sitemap routes for the skip-link target and single main landmark, plus keyboard tests for skip-link activation and Escape focus recovery on mobile and desktop.
- Preserve page copy, public routes, canonical/SEO metadata, booking destinations, analytics IDs, room/experience data and image mappings.
- Validation gate: PHP 7.4/8.2 Site Checks and full Browser E2E/screenshots at 320/390/1280/1440 on the exact final PR head. The connector cannot run local PHP/Chromium; local execution is not claimed.
- Follow-up within Phase F8: review the CI screenshot/artifact evidence and expand checks only where rendered evidence confirms an accessibility issue; do not infer visual contrast compliance from CSS token values alone.


### Phase F8 follow-up — Browser E2E root-cause fix

- Browser run [#82](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38237888014) exposed a real template inconsistency: `restaurant.php` shared the header/skip link but omitted `js/garh-v2.js`, so the main landmark was never dynamically assigned the skip-link target. The same assertion failed at all four viewport projects; the earlier PR #31 main-branch browser run is green.
- Make the skip-link target resilient without JavaScript by rendering `id="main-content"` and `tabindex="-1"` directly on the single main landmark throughout sitemap routes, including the shared experience renderer. Add the missing shared navigation script to `restaurant.php` so its mobile navigation uses the same behavior as other v2 pages.
- Keep the browser assertion strict; do not skip the restaurant route or weaken the test. Re-run both Site Checks and Browser E2E on the exact updated PR head.


## Phase F9 — founder-led conversion and cross-page consistency (PR #33 merged)

- Replace the three hand-written homepage room teaser cards with the first three entries from `include/rooms-data.php`; titles, copy, image paths and alt text now use the same reviewed room registry as the full Rooms page. Each teaser links to its exact room-card anchor, with scroll offset for the sticky navigation.
- Add a shared “Check availability” CTA to the footer wherever `footer-v2.php` is used. It reuses the configured booking URL, opens safely in a new tab with `rel="noopener"`, and receives rendered regression coverage on every sitemap route. It does not introduce a second booking provider or change booking configuration.
- Reconcile the airport sedan transfer copy on the Directions page with the owner's previously updated ₹1100 amount, replacing stale ₹1300 text in both the transport summary and the FAQ. Other pickup costs, reception hours, check-in guidance and access constraints remain unchanged.
- Add direct booking and arrival paths above the Contact enquiry form; retain the existing form and labels. Change the Rooftop “View the menu” action to target `restaurant.php#menu` so it opens the relevant section instead of restarting at the top of the restaurant page.
- Add Browser E2E assertions for footer CTA destination/new-tab safety across sitemap routes, homepage room-preview image text/detail-anchor integrity, Contact booking/arrival actions and Rooftop-to-menu navigation.
- Business rationale: turn site-wide footer traffic into a consistent booking action, show real room inventory instead of generic duplicated teasers, and make each teaser lead to the matching detailed room card. The primary guest journey remains Home → Room detail → configured availability engine, with no new hotel claims or booking flow.
- Validation gate: PHP 7.4/8.2 Site Checks, sitemap/publication boundaries, Browser E2E at 320/390/1280/1440, and artifact evidence on the exact PR head. Public website deployment is not part of this PR; production/repository parity and live operational details remain a release-gate review.
- Next milestone: Phase F10 — page-by-page conversion QA for Packages/experience and the full restaurant enquiry flow: confirm primary action destinations, review rendered heading/accessible-name and mobile CTA behavior, and separate test-backed findings from facts requiring owner confirmation.


### Phase F9 follow-up — third-party load-event reliability

- The post-merge Browser E2E run on `main` (`e62b7480a24b1d0a4740b391974cf138d038fd08`) showed three sitemap inventory tests timing out on `contact.php` while waiting for the full browser `load` event. That route includes external reCAPTCHA and a Google Maps embed; their network readiness is not a valid prerequisite for inspecting the local rendered document.
- Change image-payload and performance inventories to wait for `domcontentloaded` and decode eager/initially-visible images before sampling. Record an unfinished browser load event as unavailable (`null`), not a fabricated zero-ms result.
- Change the priority-image geometry audit to wait for the priority image decode specifically and report a failed priority-image load as a real finding. Keep its intrinsic ratio assertion; do not weaken or skip the image check.
- This is a CI reliability fix grounded in rendered evidence. It separates local page readiness and image geometry from external third-party embed/script availability, while keeping the full sitemap route coverage.

- Follow-up flake review: the added Contact conversion E2E used Playwright's default full-load wait and timed out on its first attempt before passing on retry. It now uses `domcontentloaded`, matching the other route smoke assertions, because form destination assertions need the rendered document rather than third-party reCAPTCHA/map completion.


## Phase F10 — contextual experience enquiries and page semantics (PR #35 merged)

- Carry the selected live, individual experience slug from the shared experience-page “Ask about availability” and lower “Contact us” CTAs into `contact.php?experience=<slug>`. Experience hubs continue to use a general enquiry link.
- In Contact, validate the query against `include/experience-data.php` and accept only records explicitly marked `live` that are not category hubs. Prefill the editable message with a request to confirm current price, availability, timings, arrangements and inclusions. Escape all rendered registry values; hidden, draft and unknown slugs do not reveal their titles or prefill a public enquiry.
- Clarify the global footer booking action as “Check room availability” so tour guests can distinguish room reservation from experience enquiries.
- Correct the Packages hub's experience-card headings from peer-level H2s to nested H3s under each section H2; carry the existing visual style over to H3 without rewriting experience copy or facts.
- Add Playwright checks for both contextual enquiry paths, rejection of hidden/draft/unknown experience slugs, footer booking-action clarity and Packages heading hierarchy.
- Business rationale: keep guest intent intact between the chosen tour and the enquiry, reduce typing and misrouted messages, help staff receive the experience name and the guest's request for current details, and avoid implying that the global room-booking action confirms tour availability. No prices are published or inferred.
- Preserve public URLs, canonicals, schema, analytics IDs, booking configuration, experience status boundaries, existing room and experience content, and production settings.
- Validation gate: PHP 7.4/8.2 Site Checks, sitemap/publication boundaries, Browser E2E at 320/390/1280/1440 and artifact review on the exact final PR head. PR #35 was validated after the Browser E2E reliability fix in PR #34; both are merged on main.
- Next milestone: Phase F11 — page-by-page guest-journey QA across Room → booking, Packages → experience → contextual enquiry, Rooftop → menu, and Directions → arrival/contact, followed by owner review of any operational facts or public pricing needing confirmation.


## Phase F11 — page-by-page guest-journey QA (PR #36 merged)

- Room detail: preserve the existing external booking action and configured booking URL, while adding a secondary “Ask about this room” path to the contact form. The room slug and name derive from the existing room registry and stable detail-card IDs; no rate, occupancy or availability is invented.
- Contact: accept room context only when the slug resolves against `include/rooms-data.php`; prefill an editable request for current availability, rate and room features. Existing live-experience context remains higher priority, then room context, then the finite `arrival`/`dining` topic allowlist. Unknown room/topic values are ignored.
- Directions: direct contact links preserve arrival intent; the guest-facing FAQ links to the contextual arrival enquiry.
- Rooftop / Restaurant: dining-related contact links preserve dining intent, Rooftop directs visitors to the existing menu anchor, and the restaurant menu caveat is rewritten as guest-facing guidance rather than an internal publication note. Listed dishes/prices are not changed.
- Browser E2E: cover all seven room cards, selected-room context and safe booking-link attributes; arrival and dining CTAs; contextual message prefill; unknown query rejection and deterministic context precedence. Existing Packages → experience → enquiry, Rooftop → menu, SEO, sitemap, publication-boundary, responsiveness and accessibility checks remain in force.
- Preserve public page URLs, canonical/social metadata, JSON-LD, analytics IDs, the configured booking destination, current room/experience records and image mappings. No live form submission, production deployment or merge.
- Validation gate: Site Checks on PHP 7.4 and 8.2; strict image/reference and sitemap/publication checks; full Browser E2E at 320/390/1280/1440; review exact-head CI evidence and screenshot artifacts. The connector cannot execute local PHP/Chromium, so local test runs are not claimed.
- Next milestone: Phase F12 — continue page-by-page visual and content QA on the remaining high-intent routes, based on rendered screenshot evidence and owner-confirmed operational facts; then review menu/transfer rates and content currently displayed publicly before any factual revisions.


## Phase F12 — restaurant reservation readiness (PR #37 merged)

- **Confirmed defect:** `restaurant.php` renders the online table-request form even when `GJ_RESTAURANT_MAIL_ENABLED` is not set to `1`. The POST handler then rejects the request and tells the guest that online table requests are not enabled. This makes guests enter details before discovering the form cannot send.
- **Implementation:** when the mail gate is disabled, replace the non-submitting form with a clear, visible message and direct Call / WhatsApp actions. Keep the server-side mail gate and validation intact; do not enable mail or change server configuration.
- **Regression coverage:** exercise the disabled/default route at 320, 390, 1280 and 1440 viewports; prove the unavailable form is absent, the reason is visible, and both contact alternatives have safe destinations/attributes. Attach rendered reservation-section screenshots to the Browser E2E evidence.
- **Guardrails:** do not send real form submissions, change configured recipients, expose secrets, or change menu items, menu prices, dining hours, room rates, or transfer facts.
- **Owner confirmation still needed:** verify the publicly displayed menu/prices and breakfast/lunch/dinner hours against the current restaurant operation before making factual copy changes. Preserve these current public claims until confirmed.
- **Validation gate:** PHP 7.4/8.2 Site Checks and full Browser E2E must pass on the exact final PR head. Inspect the reservation fallback screenshots and uploaded artifacts. Connector execution is remote CI; no local PHP/Chromium run is claimed.
- **Next milestone:** continue Phase F12 against the remaining high-intent pages, using rendered evidence to select the next conversion/accessibility fix. Record unresolved operational facts as owner questions, not guessed updates.


## Phase F12.2 — intrinsic image dimensions and layout stability (PR #38 merged)

- **Evidence baseline (PR #37 Browser E2E, run #98):** the local PHP-rendered test site checked 26 sitemap routes and discovered 68 unique image URLs. Resource timing measured 60 images; 15 exceeded the 250 KiB review threshold. The performance inventory reported 98 occurrences where rendered images lacked explicit `width`/`height` attributes. This is CI-local evidence, not a production or field measurement.
- **Implementation:** add a shared cached helper in `include/header-v2.php` that reads intrinsic dimensions from trusted local files under `img/`. Use it for the shared experience detail/related cards, Packages grids, Desert Tours and Guided Jaisalmer Tours grids, homepage image cards, gallery images, room registry cards and Blog cards. Existing manually dimensioned priority heroes stay untouched.
- **Rationale:** provide the browser with intrinsic image geometry during HTML parsing to reduce avoidable layout shifts, without guessing ratios, changing authentic photos, or swapping unverified WebP files.
- **Regression:** add a Playwright check across every sitemap route that asserts every rendered image has positive explicit width and height attributes, with a declared-to-intrinsic aspect-ratio check for images the browser has loaded. Keep existing priority-image checks and image-payload audit; do not claim payload compression.
- **Payload follow-up:** all 15 candidates above 250 KiB still require byte-level conversion/quality evaluation and visual equivalence checks. Their binaries have not been recompressed in this PR; preserve current public image mappings until matching derivatives are verified.
- **Guardrails:** no filenames renamed or deleted; no WebP substitution based only on extension; no changes to public copy, canonical/metadata, structured data, booking configuration, operational facts or production hosting.
- **Validation on PR head `09456e694643e8022bd6851164ad737891f62ea6`:** Site Checks passed on PHP 7.4 and 8.2 ([run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38290149988)); Browser E2E passed with 201 tests in 2.2 minutes ([run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38290150112)). The new image-layout inventory scanned all 26 sitemap routes and reported `imagesMissingPositiveDimensions: 0`. Evidence artifact: [browser-e2e-evidence-102](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38290150112/artifacts/11765451290). Remote CI only; no local PHP/Chromium execution claimed.
- **Next milestones after this batch:**
  1. **F12.3 — image payload reduction:** inspect source bytes and available derivatives; produce visually verified WebP/AVIF/resized assets with byte comparisons, preserve authentic appearance, and map only where meaningful savings are proved.
  2. **F13 — operational content trust audit:** owner-confirm menu prices, dining hours, transfer costs, reception/arrival wording and experience inclusions; update only confirmed facts.
  3. **F14 — conversion measurement:** establish one confirmed GA4 property and verify booking/enquiry measurement without triggering live enquiries or real reservations.
  4. **F15 — policy and release readiness:** obtain owner-approved privacy/cancellation terms, privately review form/mail configuration on staging, confirm cPanel PHP version, and complete production-parity checks. No production deployment without owner permission.


## Fast-lane reset (docs only)

- Added `docs/BRAIN.md` (operating rules and decisions for any AI), `docs/SPRINT-PLAN.md` (Sprint 1 build spec, Sprint 2 and 3 slices, ChatGPT kickoff prompt) and `docs/OWNER-FACTS.md` (single place for owner answers).
- Decisions: keep **both** GA4 IDs (single source `analytics_ids`); one PR per vertical slice; build over audit; screenshot sheet for visual review.
- Evidence: repository review on 2026-10-13: 38 PRs merged in 3 days, 16 of them audits and tests; per-page head blocks duplicated across 22 pages; 12 unreferenced JS files and 12 unreferenced stylesheets; `img/tent1.jpg` is about 6 MB.
- No page, CSS, JS, URL, image, booking link or GA4 ID changed in this PR.

## Phase F12.3 — measured image payload reduction (PR #39 merged)

- **Baseline:** PR #38 merged as `077a50b912220b3f3241de38d1504e12433a0202`; the sitemap-wide Chromium inventory measured 15 image candidates above 256,000 bytes. Browser Resource Timing was used to find candidates, while this phase uses exact repository file sizes for source/derivative comparison.
- **Generated derivatives:** 11 of the 15 candidates passed the 10% minimum byte-saving and 35 dB PSNR screening floors. The accepted sources total 5,901,701 bytes; same-dimension WebP derivatives total 2,992,592 bytes; the measured saving is 2,909,109 bytes (49.293% weighted) across that accepted subset.
- **Per-file evidence:**

| Source | Source bytes | WebP bytes | Saved |
|---|---:|---:|---:|
| `img/v2/facade.jpg` | 872,297 | 591,152 | 32.2% |
| `img/v2/courtyard.jpg` | 871,363 | 578,756 | 33.6% |
| `img/v2/fort-from-roof.jpg` | 692,713 | 388,404 | 43.9% |
| `img/v2/rooftop-dusk.jpg` | 615,060 | 342,528 | 44.3% |
| `img/v2/purple-room.jpg` | 537,837 | 275,778 | 48.7% |
| `img/v2/sitting-room.jpg` | 519,598 | 307,548 | 40.8% |
| `img/desert1.jpg` | 454,492 | 119,540 | 73.7% |
| `img/desert7.jpg` | 386,498 | 34,328 | 91.1% |
| `img/desert3.jpg` | 336,447 | 255,618 | 24.0% |
| `img/s2.jpg` | 308,254 | 47,426 | 84.6% |
| `img/s1.jpg` | 307,142 | 51,514 | 83.2% |

- **Conservative skips:** `img/big6.jpg` and `img/5.webp` did not meet the minimum 10% saving threshold. `img/s5.jpg` (PSNR 34.839 dB) and `img/desert4.jpg` (PSNR 33.702 dB) were rejected by the 35 dB screening floor. No derivatives for those four sources are used.
- **Runtime behaviour:** the shared helper adds a WebP `srcset` only when a derivative is a valid WebP, has the same intrinsic dimensions, and is smaller than the original. The original `src` remains the fallback. The original image files are never overwritten.
- **Visual review:** the generated side-by-side contact sheet shows broadly consistent scene framing, room appearance and photo identity at thumbnail scale. That is only a preliminary check; owner review of the full-size source/derivative image quality remains a merge gate.
- **Validation:** final PR head `bcaf2eb7c30df7819eeae091e26452010776040d` passed [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38345599188) and [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38345599125) (210 passed, 254 skipped). Post-merge `main` passed [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38346248251) and [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38346248287) on `93f27ad806a1b2de153b5f613bc82cc0a7125561`. Repository byte sizes are not production field Core Web Vitals.


## Phase F13 — contact and operational trust readiness (included in merged PR #39)

- Add rendered browser coverage for the contact and arrival pages: shared booking destination, safe new-tab attributes, direct phone/email links, accessible form labels, required fields, non-empty CSRF token, CAPTCHA site key, honeypot semantics, and arrival-to-contact/phone/WhatsApp paths.
- Browser tests confirm wiring and accessibility structure only. They do not submit an enquiry, send mail, click a live booking link, or verify whether any contact detail is externally current.
- Add docs/OPERATIONAL-CONTENT-TRUST-AUDIT.md as an owner-review queue for non-rate operational statements. No customer-facing factual text is changed in this scope.
- **Rates/prices are excluded:** the owner will add or update them later. No rate values are added, verified, asserted in tests, or modified by this batch.
- Browser wiring checks passed on PR #39; they do not verify externally current contact details or submit a live enquiry. Rates/prices remain owner-controlled and were not changed. Production deployment was not part of PR #39.


## Sprint 1 foundation — baseline and screenshot sheet (PR #41)

- Verified base: main `93f27ad806a1b2de153b5f613bc82cc0a7125561`; PR #40 is latest merged and both main workflows pass on this exact head.
- Added a rendered head snapshot for all sitemap routes plus title/robots baselines for the three draft/hidden routes before any shared-head migration.
- Added a full-page screenshot sheet for every sitemap route at 390px and 1280px; scrolls through the full document before capture and uploads artifact `page-sheet`.
- No page-head refactor, page copy, public URL, analytics ID, image mapping, rate, or price is changed in this foundation slice.
- Next: after owner merge, implement the shared page-head and migrate the first page group with the snapshot fixture unchanged; no owner facts are needed for this slice.


## Sprint 1 fixture follow-up — commit the pre-refactor baseline (PR #42)

- Base re-verified: main `f17523727effe40855d3ef2bbeca8e576d7a42d7`; PR #41 was the latest merged PR at the start of this follow-up.
- Captured and validated the pre-refactor baseline from the successful CI run: 26 sitemap routes and three draft/hidden routes. It freezes titles, descriptions, canonicals, robots, OG/Twitter tags, JSON-LD type inventory, and both approved GA4 config IDs.
- The first PR #42 attempt passed 212 Browser E2E tests, including the full page sheet, but its temporary CI push step failed because GitHub Actions uses a synthetic PR merge checkout; pushing that detached merge commit to the head branch is not a fast-forward. The generated fixture is now committed by the normal feature-branch change instead.
- Final workflow restores `contents: read` and removes both temporary bootstrap steps. The test fails if the fixture is missing or any frozen output changes; it does not weaken assertions.
- No public PHP page, URL, content, schema values, GA IDs, image mapping, owner fact, rate, or price changed. Production and cPanel were not touched.
- Next: after this PR is owner-merged and exact-head checks pass, proceed with the shared page-head foundation and the first page group.


## Sprint 1 — remaining shared page heads (PR #45)

- Verified base: main \`438946452872dcc8b87fb593eff6869858f9d75d\`; PR #44 is latest merged and no implementation PR was open before this batch.
- Migrated \`roof.php\`, \`restaurant.php\`, \`blog.php\`, three guides and \`include/experience-page.php\` to the shared head; public URLs and page body sections stay unchanged.
- Preserved title, description, canonical, robots policy, social metadata, page-specific JSON-LD and both configured GA4 IDs; added shared \`og:image:alt\` support and retained the frozen fixture.
- Validation gate: exact-head PHP 7.4/8.2 Site Checks, Browser E2E and the 390px/1280px page-sheet artifact; local PHP/Chromium were not run by the connector.
- Owner questions: none for this technical refactor; existing operational facts remain open in \`docs/OWNER-FACTS.md\`. Next: reusable UI partials, then verified dead assets with fonts as a separate revertible commit.
