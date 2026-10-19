# Website Repository Readiness — Garh Jaisal Haveli

**Audit date:** 2026-10-19  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA at this batch start:** `f810d79547a04c8fe3d5b84612eafdedf88545c0`  
**Latest merged PR at this batch start:** [PR #105 — reconcile release status with PR #104 baseline](https://github.com/tchouhanjsm/garh-jaisal-website/pull/105)

## Exact-main baseline

The following required site/release workflows passed on `f810d79547a04c8fe3d5b84612eafdedf88545c0` after PR #105 merged:

| Check | Result | Evidence |
|---|---|---|
| PHP 7.4/8.2 lint, strict site/source contracts, publication boundaries, sitemap freshness | PASS | [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562365) |
| Browser E2E, form validation and screenshot/evidence upload | PASS | [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562397) |
| Responsive image manifest validation and review candidates | PASS | Included in [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562365) |
| Runtime package safety tests and ZIP build | PASS | [Package workflow](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562358); review artifact only, no deployment |
| Non-deploying staging QA dry run | PASS | [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562402); not evidence of behavior on an external host |

These results apply to that exact SHA. They establish a passing automated baseline, not full manual image/caption approval, real-device assistive-technology sign-off, external mail/CAPTCHA delivery or production-host compatibility.

## Merged source work

- **PR #100:** diversified image assignments and aligned hero/social imagery with page content.
- **PR #102:** broadened responsive image candidate coverage, aligned preload selection, fixed Gallery full-size lightbox behavior and corrected the Car & City Tours breadcrumb.
- **PR #103:** pinned `@playwright/test@1.64.1`, committed the npm-generated lockfile, and moved browser CI to `npm ci` plus `npm audit --audit-level=high`.
- **PR #104:** added layout-aware `sizes` values for existing responsive image slots.
- **PR #105:** reconciled the prior release report and project snapshot with the then-current main baseline. This report was refreshed again after the PR #105 merge advanced `main` to `f810d795...`.

## Current implementation batch — Directions

Feature branch: `feat/directions-arrival-experience-20261019`, based on main SHA `f810d79547a04c8fe3d5b84612eafdedf88545c0`.

The batch applies the useful UX structure from the supplied “Getting Here” concept to the existing `direction.php` route:

1. An arrival-summary card in the hero with drop-off guidance, the final on-foot route and the existing exact hotel Maps pin.
2. Accessible in-page section links for quick directions, travel options, walking/mobility, map and FAQs.
3. A semantic ordered list for the four parking-to-door steps, with responsive card and number-marker styling.
4. New Browser E2E coverage for link/anchor behavior, the route list and no horizontal overflow at 320, 390, 768, 1280 and 1440 CSS pixels.

The mockup's unverified prices, journey times, staff identity, check-in and reception promises, third-party stock photos, and nonexistent form endpoint are intentionally excluded. Existing public URL, page metadata, Google Maps/contact destinations and policy-accurate copy are preserved. The branch's own exact-head CI and screenshot review are the acceptance gate; do not infer success from the base branch checks.

## Release work still outstanding

### Code-verifiable
- All required site checks, responsive-image checks, package preflight and Browser E2E must pass on the exact feature head after the Directions update.
- Inspect the feature-head page-sheet and responsive screenshots. A structural test pass does not prove the page's visual quality or image/caption semantics.
- Keep `sitemap.xml` freshness checked for route/source changes and verify package asset coverage if runtime assets are changed.

### Human review and owner-dependent facts
- Review rendered Directions and related page imagery/caption/alt-text mapping at mobile, tablet and desktop widths.
- Manually assess keyboard/focus visibility, contrast, reduced motion, screen-reader behavior and CTA overlap.
- Keep unapproved menu/prices/hours, booking/cancellation terms, privacy/data-controller and lead-retention details, check-in/reception promises, room-rate display, guest reviews, tour inclusions/pricing, and transfer fares/timings qualified or unpublished until approved by the owner.
- Host-specific Apache rewrites, PHP version, CAPTCHA/mail delivery, analytics receipt and field performance require owner-controlled verification; repository CI cannot prove those behaviors.

## Packaging boundary

The package workflow produces a runtime-only PHP ZIP and integrity manifest as a review artifact. Before final delivery, ensure the tested archive matches the intended source commit, extracts cleanly, includes required PHP pages/includes/CSS/JS/images/content and excludes private configuration, credentials and development-only tooling. No hosting access, upload, merge or deployment is performed by this batch.
