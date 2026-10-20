# Website Repository Readiness — Garh Jaisal Haveli

**Snapshot refreshed:** 2026-10-20  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA:** f753107e98947f29ee30b849c7aa2bd91330ea5f  
**Latest merged PR:** [#108 — local Content Studio for site content](https://github.com/tchouhanjsm/garh-jaisal-website/pull/108)

## Exact-main automated baseline

The following workflows passed on the verified PR #108 merge SHA:

| Check | Result | Evidence |
|---|---|---|
| PHP 7.4/8.2 lint, strict site/source contracts, publication boundaries and sitemap freshness | PASS | [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817921) |
| Runtime package selection, safety gates and package build | PASS | [cPanel package workflow](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817936); artifact only, no upload |
| Non-deploying staging dry run | PASS | [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817926); not external-host evidence |
| Full Chromium Browser E2E and screenshot/evidence artifacts | PASS | [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39376817949) |

Passing CI is not proof of live Apache/.htaccess compatibility, actual cPanel PHP version, real CAPTCHA/mail delivery, GA4 receipt, guest-policy approval or production performance.

## Recent merged implementation

- **PR #106:** Directions arrival journey, route guidance and responsive layout.
- **PR #107:** photo-led entry journeys and mobile actions.
- **PR #108:** local Content Studio for tours, travel guides and Gallery images; JSON registry overlays; local upload validation; draft/live boundaries; responsive-image manifest integration; dynamic Blog archive; package inclusion of runtime content records. Browser E2E caught the Fort-guide lead-card regression; the feature was fixed and the merge-head suite passed.
- **Current next phase:** Sprint 52, one-click local Content Studio startup and PHP preview. It improves operator workflow only; it does not turn the app into a public CMS or publish changes by itself.

## Release work still outstanding

### Code-verifiable
- Required PHP/site checks, strict image and sitemap checks, deployment-package preflight and full Browser E2E must pass on each feature PR's exact head.
- Review generated package manifest/ZIP and ensure it matches the exact approved source commit. Preserve server-side config and exclude local authoring tools, credentials, tests and development-only scripts.
- Reconcile project snapshot and phase checklist after each merge; do not infer success from older SHAs or cancelled runs.

### Human review and owner-dependent facts
- Review page-sheet/screenshots and photo subject/caption/alt text at 320, 390, 768, 1280 and 1440px. Human review remains separate from automated structural checks.
- Confirm keyboard/focus visibility, contrast, reduced-motion behavior, CTA overlap, screen-reader behavior and real-device experience.
- Do not publish unapproved menu/prices/hours, booking/cancellation terms, privacy/data-controller and lead-retention details, check-in/reception promises, room-rate display policy, guest reviews, tour inclusions/prices, transfer fares/timings or unsupported service claims.
- Verify real host compatibility, HTTPS certificate/redirect behavior, Apache directives, configured PHP version, CAPTCHA/mail delivery, analytics receipt, cache/MIME behavior and runtime package extraction on owner-controlled staging.

## Packaging and release boundary

The deployment-package workflow creates an archive and integrity manifest for owner review. It does not upload to cPanel. Use an owner-supplied HTTPS staging origin and exact package SHA for the external staging dispatch, review the staged result, and obtain explicit release approval before production deployment or release tagging. No cPanel access, staging upload or production deployment was performed by PR #108.
