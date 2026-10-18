# Final Release Readiness — Garh Jaisal Haveli

**Audit date:** 2026-10-18  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA:** `1a385f3c2975c7308849335d412b31f295ea4fd0`  
**Latest merged change:** [PR #97 — harden arrival claims and responsive image cache](https://github.com/tchouhanjsm/garh-jaisal-website/pull/97)

## Decision

**Repository checks are green; production release is blocked.** The source repository has progressed well past the historical PR #91 review. The live host is not demonstrably running this source: the public Home page still shows the older site, and the public Directions page still publishes transfer fares and meet-and-assist, reception-hour and travel-time claims removed from repository source. No live host change has been made.

## Verified repository state

| Gate on `1a385f3c2975c7308849335d412b31f295ea4fd0` | Result |
|---|---|
| Site Checks (PHP 7.4/8.2 and source contracts) | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805415) |
| Browser E2E and screenshots | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805433) |
| cPanel package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805478) — artifact only, not uploaded |
| Staging QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805519) — local/non-deploying dry run, not external-host evidence |

## Code issues closed in Sprint 48

- The restaurant menu fixture is marked `owner_confirmed: false`; its dish names and descriptions previously appeared even though prices were hidden. The entire item list is now withheld until the owner confirms it and the registry contains records. The fallback retains Contact and WhatsApp actions.
- Browser coverage asserts that the unconfirmed menu data is absent from rendered HTML and that the fallback remains responsive; the accessible booklet tests continue to run when the menu is approved.
- Restaurant name-length validation no longer fatally depends on the optional PHP `mbstring` extension.
- Project Log, Sprint Plan, full-repository review, backend remediation and Smart Check Matrix were reconciled with the actual merged baseline and current CI state.

## Launch blockers (must close before production)

| Priority | Blocker | Required proof / owner action |
|---|---|---|
| P0 | Production parity | Deploy the reviewed merged package to a separate HTTPS staging origin. Confirm exact staged SHA against package manifest/checksum; verify that current legacy pages have been replaced only after staging passes. |
| P0 | cPanel runtime | Confirm actual PHP version and required extensions plus Apache/module/override behavior. Verify redirects, query strings, 404, config denial, response headers and cache rules on cPanel. |
| P0 | Forms and notification | Configure server-only CAPTCHA secret and approved recipient(s). Test CAPTCHA success/failure/timeout, actual owner notification, guest acknowledgement, storage permissions and mail-failure states on staging, using owner-controlled test addresses only. Do not expose secrets in logs. |
| P0 | Privacy notice | Owner/legal reviewer supplies and approves data-controller identity, contact for privacy requests, enquiry data/purpose, external booking/analytics/reCAPTCHA disclosures, retention, access, rights and complaint handling. Keep draft noindex/off-navigation until complete. |
| P0 | Booking/cancellation terms | Owner supplies approved confirmation, payment/deposit, cancellation, modification, no-show, refund and late-arrival terms. Keep policy draft noindex/off-navigation until approved. |
| P0 | Restaurant accuracy | Confirm menu items/descriptions/prices, service hours, closed days, last seating, same-day cut-off and online party-size policy. Keep menu data unconfirmed and prices hidden until approved. |
| P1 | Visual release review | Human-review page sheet and responsive hero/image evidence at 320, 390, 768, 1280 and 1440px; verify rooms and desert image-to-caption mapping, crops/sharpness, sticky/fixed CTA clearance, and tablet/mobile reading order. CI evidence availability is not the same as visual sign-off. |
| P1 | Analytics and performance | Verify real receipt in both configured GA4 properties without sending personal fields; capture a comparable staging Lighthouse/performance baseline. Do not claim production Core Web Vitals gains from CI. |
| P2 | Dependency reproducibility | Current workflows pin Playwright `1.55.0` at install time, but `package.json` has no dev dependency/lockfile. Address separately with a reviewed lockfile/install-path change; this is not a substitute for release blockers above. |

## Release sequence

1. Review and merge the Sprint 48 PR only after exact-head Site Checks, Browser E2E, package preflight and Staging QA pass and the screenshot/evidence artifacts are available.
2. Generate/download the deployment artifact for the exact merged `main` SHA. Check the ZIP manifest and SHA-256; preserve server-only `include/site-config.local.php` outside the package.
3. Make a restorable backup and deploy the package to the owner-controlled HTTPS staging host only. Supply that exact host and package SHA to the workflow-dispatch external Staging QA; the automated pull-request dry run is not enough.
4. Complete the manual host, mail/CAPTCHA, form-failure, booking/contact, image MIME/cache, redirects/security-header and screenshot checks. Record tester/date/SHA/URLs/results.
5. Obtain owner sign-off on operational facts, policies, visuals and analytics. Only then may the owner authorize production deployment.
6. After production deployment, verify representative routes, forms without guest data, response headers, booking/contact links, assets and the exact deployed commit/package. Tag `v1.0.0` only after that approved release.

## Authority and limits

This repository changes source code and CI only. Green CI does not authorize production. No cPanel upload, external staging deployment, production deployment, policy approval, menu approval or fabricated operational fact is implied by this report. The repository owner retains merge and deployment authority.
