# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-12 |
| Current phase | Phase F5 — Homepage conversion and arrival UX |
| Base branch | `main` |
| Baseline commit | `1cf8b884ffed8dcdbd9a9e9f3734a7b8b7db4494` (main after PR #26) |
| Last merged PR | [#26 — streamline Packages hub discovery](https://github.com/tchouhanjsm/garh-jaisal-website/pull/26) |
| CI on main | [Actions run #7](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38153905747) passed on merge commit |
| Production URL | https://www.hotelgarhjaisal.com |
| Active batch | `feature/phase-f5-homepage-conversion-ux` — make arrival constraints and primary homepage destinations clearer |
| Next action | Verify homepage arrival guidance, stable discovery anchors, PHP/site checks and responsive/browser evidence on the exact PR head; owner retains merge authority |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and a safe CI baseline | ✓ CI baseline merged; local cleanup and WebP verification remain |
| 1 | Site-wide audit with a prioritised backlog | ◐ in progress |
| 2 | Booking conversion and mobile UX | ◐ responsive navigation and shared booking URL merged; conversion measurement remains open |
| 3 | Technical SEO foundation | ✓ rendered metadata and JSON-LD regression checks merged in PR #6 |
| 4 | Local SEO and content depth | ◐ inventory and candidate finding reports exist; candidate findings still require review and verified remediation |
| 5 | Performance and Core Web Vitals | ◐ baseline captured; priority hero dimensions in remediation; image payload optimisation and controlled Lighthouse/field measurement remain |
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
