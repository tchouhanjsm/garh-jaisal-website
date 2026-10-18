# Full Repository Audit — Garh Jaisal Haveli

**Audit date:** 2026-10-18  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Audited baseline:** `main` at `1d08a3a4cae91f0b78f2d83957db22fd9d529550` (PR #99 merge)  
**Latest merged PR at baseline:** [PR #99 — reconcile repository status after PR #98](https://github.com/tchouhanjsm/garh-jaisal-website/pull/99)  
**Related in-flight implementation:** [PR #100 — diversify repeated website image assignments](https://github.com/tchouhanjsm/garh-jaisal-website/pull/100)  
**Scope:** All tracked Markdown documents; repository tree inventory; current CI workflows; relevant sitemap, image-registry, gallery-test, and release-readiness source contracts.

## Executive summary

The repository has a substantial automated safety net, and all nine check runs on the audited `main` SHA passed. That is not the same as saying the website is fully finished. The audit found outstanding reproducibility, documentation-truth, image-review, owner-fact, and manual UX/accessibility tasks. Some are code tasks; others cannot be safely completed without approved hotel information.

The image-reuse work is in PR #100, not in `main`. Its initial CI failures exposed two linked issues: `sitemap.xml` had not been regenerated after changing source files that drive `lastmod`, and the Swiss-tent gallery test still required a cultural-program image that the new curated gallery intentionally removed. The sitemap and test contract have been corrected on the PR branch; the latest-head CI must finish before that PR is considered ready.

## Verified baseline and CI

At `1d08a3a4cae91f0b78f2d83957db22fd9d529550`, the following checks completed successfully:

- [PHP 7.4 compatibility/site checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680)
- [PHP 8.2 site checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680)
- [Browser E2E and screenshots](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179657)
- [Deployment package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179660)
- [Non-deploying Staging QA workflow](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179737)
- Brand contrast/token, marketing-template and responsive-image review checks also passed on this SHA.

The staging QA job is a repository/local dry run; it does not prove external hosting behavior or real mail delivery.

## Prioritized findings

### P1 — Resolve before calling the source repository complete

| ID | Finding | Evidence | Required action |
|---|---|---|---|
| AUD-01 | Browser-test dependencies are not reproducible from the repository manifest. | `package.json` declares only a test script. There is no committed npm lockfile; CI installs `@playwright/test@1.55.0` with `--no-save --package-lock=false`. | In a network-enabled environment, generate and verify a real lockfile, declare the pinned test dependency, then switch CI to `npm ci`. Do not fabricate a lockfile or change the install command before validating it. |
| AUD-02 | Repository status documentation lags behind `main`. | The current `main` baseline is PR #99 at `1d08a3a...`, but the release-readiness report and project-log snapshot still cite PR #98 and SHA `e6f2b809...`. | Update the snapshot on each delivery PR and preserve earlier SHA/run details as clearly historical records. |
| AUD-03 | Image selection needs semantic/visual review, not only unique file paths. | PR #100 changes hero, card and comparison assignments. Automated checks validate paths, dimensions, markup and gallery contracts, but cannot prove every photograph matches the caption, product or crop. | Review page-sheet/contact-sheet evidence across the main page archetypes and viewports; correct mismatched subjects and alt text; keep source mappings and preload/social metadata aligned. |
| AUD-04 | Some customer-facing facts are intentionally owner-blocked. | `docs/OWNER-FACTS.md` records unresolved policy, menu, restaurant-hours, check-in/out, reception, price, review and tour details. `include/menu-data.php` remains unconfirmed. | Keep unapproved menu data and policy pages unpublished/noindex. Do not invent facts to make a checklist appear complete. Only activate content after the owner supplies the exact approved details. |
| AUD-05 | Private enquiry storage and outbound mail need operational decisions before activation. | The source contains private lead storage, rate controls and failure handling, but access, backup/retention wording, recipient confirmation and real mail/CAPTCHA delivery cannot be established by source checks alone. | Keep these as explicit owner/configuration gates; retain safe failure paths and never send test enquiries to guests. |

### P2 — Complete during the quality and UX pass

| ID | Finding | Evidence | Required action |
|---|---|---|---|
| AUD-06 | Responsive image optimization is partial. | Existing optimized WebP files and responsive-image tooling exist, but the image optimization docs explicitly keep resized 480/800/1200px candidates review-only. The older remediation report records large original assets and incomplete `srcset`/`sizes` coverage. | Audit real rendered source selection, transfer size, intrinsic dimensions, lazy/eager loading and preload alignment by route. Keep originals and room mappings; approve derivatives only after visual comparison. |
| AUD-07 | Historical layout/architecture findings need reconciliation against current implementation. | `docs/LAYOUT-REVIEW.md` contains a mix of resolved, owner-blocked and older structure findings; `docs/SITE-ARCHITECTURE.md`, the current route set and newer Sprint entries are the implementation truth. | Reconcile each finding as resolved with evidence, still open, owner-blocked, or superseded. Do not reopen already-fixed findings without new rendered evidence. |
| AUD-08 | Human visual and accessibility sign-off remains separate from automated pass status. | Smart Check and Brand docs explicitly reserve screenshot judgement, physical-device, screen-reader and assistive-technology checks for human review. | Review the responsive page sheet at 320, 390, 768, 1280 and 1440px; test keyboard/focus, contrast, reduced motion, sticky/floating CTA overlap and long-page interactions. |
| AUD-09 | Public content and SEO quality require semantic checks beyond structural CI. | The SEO inventory reports candidates; it is not a quality score or production crawl. Several operational claims require owner confirmation. | Review title/description uniqueness, H1 and schema parity, internal-link intent, route status and accurate image/caption semantics; preserve all established public URLs. |
| AUD-10 | Sitemap freshness is a recurring integration risk. | The image PR's first CI head failed `generate-sitemap.php --check` after source/registry changes. The generator derives `lastmod` from Git history and requires the committed XML to match. | Treat sitemap regeneration/check as part of every route or registry edit; never replace trustworthy Git-derived dates with guessed dates. |

### Owner-blocked decisions, not engineering guesses

The owner-facts document still asks for, among other things:

- Privacy/data-controller contact and lead-log access, backup and retention policy.
- Approved booking, cancellation and refund wording.
- Current menu items/prices and restaurant opening hours, closed days, last seating and same-day cut-off.
- Check-in/check-out, early arrival/luggage storage and reception/late-arrival details.
- Approved room-rate display policy and authentic guest reviews.
- Exact Longewala/Rajasthan tour routes, inclusions, durations and pricing, plus whether Explore the Desert is a distinct offer.
- Current transfer fares/timings and which Instagram posts/reels may be embedded.

Until answered, keep affected promises qualified and draft pages out of public navigation/sitemap where required.

## Image PR #100 — failure diagnosis and correction

The first PR #100 head (`a0347116...`) failed both PHP-version jobs at the sitemap freshness check. The next head with the sitemap refreshed moved past that step and failed `tests/php/experience-gallery-assets.php`: the Swiss-tent gallery was intentionally cleaned up to remove `img/Desert_Camp/Prog.jpeg`, while the test still required that photo inside the Swiss-tent accommodation gallery.

The PR branch now:
1. Refreshes `sitemap.xml` for the changed PHP pages and registry-driven editorial/experience routes.
2. Removes the outdated Swiss-tent gallery requirement from the experience-gallery contract while preserving the independent assertion that the main Gallery includes its selected `Prog.jpeg` photo.

**Status:** latest-head CI is the deciding evidence. Do not merge PR #100 until PHP 7.4, PHP 8.2, Browser E2E, package preflight, staging dry-run, brand/token, marketing and responsive-image checks are green on the same latest SHA.

## Delivery sequence

1. Finish and verify PR #100 at its exact current head; review screenshots and image semantics before merge.
2. Land this audit/documentation reconciliation through a reviewable PR; keep the project log aligned to the verified main SHA.
3. Make browser dependencies reproducible with an actual generated lockfile when registry access is available.
4. Complete the responsive image, visual consistency, accessibility and route/content audit in coherent implementation batches with regression tests.
5. Resolve owner-blocked content only from explicit approved facts. The user's current delivery instruction is source/ZIP focused; hosting and deployment actions are not part of this audit batch.

## Markdown inventory reviewed

The audit reviewed all tracked Markdown documents in the repository tree:

- `.agents/skills/smart-check/SKILL.md`
- `.claude/skills/smart-check/SKILL.md`
- `.github/pull_request_template.md`
- `docs/AI-DEV-PLAYBOOK.md`
- `docs/AI-ROLES-PROMPTS.md`
- `docs/BACKEND-DEBUGGING-REMEDIATION-2026-10-17.md`
- `docs/BRAIN.md`
- `docs/BRAND.md`
- `docs/DEPLOY-CPANEL.md`
- `docs/FINAL-RELEASE-READINESS-2026-10-18.md`
- `docs/FULL-REPOSITORY-DEBUGGING-REVIEW-2026-10-16.md`
- `docs/IMAGE-OPTIMIZATION.md`
- `docs/JAISALMER-TOUR-RESEARCH.md`
- `docs/LAYOUT-REVIEW.md`
- `docs/OPERATIONAL-CONTENT-TRUST-AUDIT.md`
- `docs/OWNER-FACTS.md`
- `docs/PHASE-3-SEO-CONTENT-NOTES.md`
- `docs/PHASE-4-LOCAL-SEO-AUDIT.md`
- `docs/PROJECT-LOG.md`
- `docs/RECOVERY-README.md`
- `docs/SETUP-GUIDE.md`
- `docs/SITE-ARCHITECTURE.md`
- `docs/SMART-CHECK-MATRIX.md`
- `docs/SPRINT-PLAN.md`
- `docs/STAGING-QA-CHECKLIST.md`
- `docs/V2-MERGE-CHANGELOG.md`
- `marketing/templates/README.md`

This inventory is a documentation and source-contract audit, not a claim that every production-host behavior or every image has been manually judged. Exact-head CI and screenshot evidence remain the proof for implementation changes.
