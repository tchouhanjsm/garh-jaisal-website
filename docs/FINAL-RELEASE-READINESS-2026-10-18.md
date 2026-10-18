# Website Repository Readiness — Garh Jaisal Haveli

**Audit date:** 2026-10-18  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA:** `b7334ec9b0dea4aa3ba527e38725dd96131712a6`  
**Latest merged PR:** [PR #104 — size existing responsive images by layout slot](https://github.com/tchouhanjsm/garh-jaisal-website/pull/104)  
**Main-head Browser E2E:** [run 39128969413](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39128969413), triggered for the exact SHA above; it was still in progress at the time of this update.

## Baseline and recent implementation

PR #104 is merged to `main`. Recent merged work relevant to release readiness:

- **PR #100** diversified image assignments across editorial, room, package, tour and dining pages, while aligning hero/social image metadata.
- **PR #102** added more manifest-driven responsive candidates, corrected the Gallery lightbox to use the selected full-resolution image, corrected the Car & City Tours breadcrumb, aligned responsive preloads and slot `sizes`, and added regression coverage.
- **PR #103** committed the pinned `@playwright/test@1.64.1` dependency and npm-generated lockfile; CI now uses `npm ci` and `npm audit --audit-level=high`.
- **PR #104** refined `sizes` hints on existing responsive image slots without changing original photography, crops, public URLs or room/photo mappings.

The exact-main Browser E2E workflow run `39128969413` checked out `b7334ec9b0dea4aa3ba527e38725dd96131712a6`. The following setup steps had succeeded at last inspection: pinned Pillow install, responsive asset generation, PHP setup, Node setup, `npm ci`, `npm audit --audit-level=high`, and Chromium installation. The full responsive Playwright run was in progress; the job had not yet produced a final conclusion or screenshot artifacts. No PASS claim is made for that incomplete run.

## Remaining release work (prioritized)

### P1 — Final verification on the exact source
1. Wait for exact-main Browser E2E run `39128969413` to finish; inspect its result, screenshot/page-sheet artifact and curated visual evidence. If it fails, diagnose root cause and open a review PR; do not weaken test expectations to manufacture a pass.
2. Confirm Site Checks, package preflight and responsive image review on the final source revision. Record each result against its exact SHA. The main-head Classic Commit Status endpoint currently reports no entries; use the GitHub Actions workflow runs and their head SHAs as the evidence.
3. Build the PHP runtime ZIP only after preflight passes, then extract it to a clean directory and verify package contents, integrity manifest and sitemap-route asset coverage. The workflow package is a review artifact only—not a deployment.

### P2 — Human judgement not supplied by automated tests
4. Review actual image subjects, captions, alt text, crops and source selection on page-sheet/contact-sheet evidence, including Home, Gallery, rooms, desert/tent, dining and editorial pages. Automated existence/shape checks do not prove semantic correctness.
5. Conduct responsive review at 320, 390, 768, 1280 and 1440 CSS pixels; manually check keyboard/focus, sticky/floating CTA overlap, reduced motion, contrast and assistive-technology behavior. Automated CI is not a substitute for physical-device or screen-reader review.
6. Review SEO copy intent, metadata uniqueness, schema parity, canonical routes, sitemap freshness, redirects and internal-link destinations after any source change. Keep the established public URLs intact.

### P1 — Explicit owner/configuration blockers
7. Keep the restaurant menu gated until the owner confirms the current menu and prices. An online dining request remains an enquiry, not a confirmed reservation.
8. Obtain approved booking/cancellation wording; privacy/data-controller contact and lead-log access/backup/retention policy; check-in/check-out and reception/late-arrival facts; room-rate display policy; authentic review permissions; exact tour inclusions/routes/prices; and current transfer prices/timings. Keep affected claims qualified or unpublished; do not infer missing facts.
9. Verify production mail/CAPTCHA delivery, lead-storage access and retention, analytics receipt, host PHP compatibility and production performance only through the owner's separate operational process. Repository CI cannot prove those external behaviors.

## Packaging and delivery boundary

The package workflow runs a manifest-based package-safety test and `scripts/build_deploy_package.py --check`. The main-branch workflow can build and publish a ZIP plus integrity manifest as a GitHub Actions artifact after preflight passes. That archive is meant for review; it is not a live deployment. Before final delivery, extract the archive and confirm the PHP source, runtime includes, CSS/JS, images, content data and required runtime files are present, while test tooling, private local config, credentials and development-only artifacts are excluded.

This readiness report records source/CI state only. No merge, cPanel access, hosting mutation or external deployment is authorized or performed here.
