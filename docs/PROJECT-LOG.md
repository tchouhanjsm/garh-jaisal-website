# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-13 |
| Current phase | Staging isolation and automated QA; owner-controlled staging validation remains the release gate |
| Base branch | `main` |
| Baseline commit | `ff34f6bb4debb9129c2456a918c502b84adad53c` (PR #54 merge commit) |
| Last merged PR | [#54 — safe cPanel deployment package workflow](https://github.com/tchouhanjsm/garh-jaisal-website/pull/54) |
| Exact-main CI evidence | [Site Checks PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38432500907); [cPanel package PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38432500854); [Browser E2E/screenshot sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38432500866) |
| Screenshot evidence | [PR #54 page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38431409628/artifacts/11831127680); main post-merge screenshot workflow evidence is tracked above |
| Image review evidence | [Responsive WebP candidates](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526/artifacts/11828260774) — review-only, not enabled for runtime |
| Production URL | https://www.hotelgarhjaisal.com |
| Open PRs at baseline verification | None after PR #54 merge |
| Next action | Owner stages the matching package, runs Staging QA against the real HTTPS staging host, then signs off mail/CAPTCHA, images and release gates |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and safe CI baseline | ✓ CI, legacy CSS/JS/include/font cleanup merged; runtime guards retained |
| 1 | Site-wide audit with prioritised backlog | ◐ ongoing evidence-led defect fixes |
| 2 | Booking conversion and mobile UX | ◐ responsive navigation and booking URL reuse merged; conversion event measurement remains open |
| 3 | Technical SEO foundation | ✓ shared rendered metadata and JSON-LD regression checks |
| 4 | Local SEO and content depth | ◐ inventory exists; copy and owner-fact findings require review |
| 5 | Performance and Core Web Vitals | ◐ image derivatives and review candidates exist; controlled Lighthouse/field measurement remains open |
| 6 | Accessibility | ◐ rendered image alt audit passes; manual contrast, keyboard and screen-reader review remains |
| 7 | Form, email and security hardening | ◐ PR #51 tests pass; staging mail/CAPTCHA and cPanel behavior remain unverified |
| 8 | Analytics measurement | ◐ both owner-approved GA4 IDs retained; conversion event/property semantics need owner decision |
| 9 | Staging QA and cPanel handoff | ◐ guides committed; owner-run staging verification pending |
| 10 | Monthly improvement loop | ☐ not started |

## Sprint 3A/3B — launch hardening and release preparation (PR #51 merged)

- PR #51 merged to `main` at `86108fb77c3c9aec58c82f2e9e7f42c1a9727af6`; main-push Site Checks and Browser E2E jobs both passed on that exact SHA. See links in Snapshot.
- Hardening includes guarded Apache rules, form/mail failure recovery, session-cookie defaults, a noindex custom 404, Git-derived sitemap dates, and a rendered image accessibility inventory. Existing URLs, photo mapping, booking/contact destinations and both GA4 IDs are preserved.
- Responsive variants from the image review are not live references. Existing original images are retained; the owner must approve candidates at full size before authorizing any mapping or runtime change.
- Staging and server behavior remain unverified: cPanel PHP version, Apache modules/overrides, canonical redirects, response headers, reCAPTCHA hostname/secret configuration, recipients and mail success/failure paths. Follow `docs/STAGING-QA-CHECKLIST.md` and `docs/DEPLOY-CPANEL.md` on a staging host only.
- No release tag or deployment has been made. Tag `v1.0.0` only after owner review of merged main, staging evidence and responsive image candidates.

## Next safe engineering action — retained font inventory

- A focused reference scan of retained CSS files and key PHP page/head files found no explicit references to the tracked legacy Font Awesome, Glyphicons or Icofont font files. The shared head uses Google Fonts and main V2 CSS uses Source Serif/Inter with system fallbacks. Finish a complete reference scan across retained text, scripts, styles and tests before deletion.
- Keep any asset needed by code, HTML, CSS, tests or a documented fallback. Do not remove legacy HTML/SVG resources based only on filename. If the complete scan confirms no references, remove only verified-unused files in a focused reversible PR with strict site-check, local-asset/link and full Browser E2E gates.
- This cleanup does not authorize changes to hosted Google Fonts, icon markup, public routes, content, image mappings or production configuration.

## Owner decisions still needed

- Confirm actual cPanel PHP version and support for the `.htaccess` directives/overrides.
- Complete staging checks for redirects, error page, security headers, CAPTCHA, configured mail recipients and successful/failing enquiry paths.
- Inspect the responsive WebP artifact at full size before any candidate goes live.
- Confirm the conversion event plan and intended use of both GA4 properties.
- Supply approved privacy and booking/cancellation policy wording before draft pages can be linked or indexed.


## State at PR #52 creation (historical — 2026-10-13)

This note supersedes stale entries above that still label PRs #48–#51 as open/in progress.

- `main` is at merge commit `86108fb77c3c9aec58c82f2e9e7f42c1a9727af6`; PR #51 is merged and no open PRs were found at the start of this work.
- Exact-main Site Checks passed: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526
- Exact-main Browser E2E and screenshot generation passed: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551
- Screenshot sheet: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551/artifacts/11828016923
- Responsive image candidates remain review-only: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526/artifacts/11828260774
- Owner-side staging, cPanel/Apache behavior, form delivery and full-size image approval remain mandatory before release/tagging. No deployment or release tag was made.
- Next engineering action: complete retained-font reference verification before any deletion; then use a separate reversible cleanup PR only for assets proven unused.

## Completed phase — legacy icon-font cleanup (PR #53 merged)

- PR #53 removed 16 unused local Font Awesome, Glyphicons and Icofont files (8,217,618 tracked bytes) behind the source-reference guard and a sitemap-wide browser request regression. [Exact-head Site Checks passed](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38428599901) and [Browser E2E passed](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38428599781).
- The follow-on PR adds no changes to Google Fonts, public routes, CSS declarations, image mappings, booking destinations, form behavior or analytics IDs.

## Next sprint — repeatable cPanel deployment package (2026-10-13)

- Baseline after PR #53: main at `80b40250a4b95ba37aacf73c683892a2678e40f6`; exact-main [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38430084277) and [Browser E2E/screenshots](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38430084161) passed.
- The next slice adds a standard-library package builder, unit tests for path selection and sitemap coverage, and a GitHub Actions workflow. Pull requests run checks but do not publish a ZIP. Only pushes or manual dispatches from main create the downloadable package, sidecar manifest and SHA-256 checksum.
- The package contains tracked runtime files and excludes documentation, development tooling, CI, tests and server-local configuration. It does not deploy; it is only a candidate for owner-controlled staging.
- Release blockers remain: cPanel/PHP/Apache compatibility, redirect/header behavior, staging CAPTCHA/mail behavior, owner approval of resized image candidates, and approved privacy/booking policy text.


## Sprint 5 — staging isolation and automated QA (implementation PR in review)

- Baseline: PR #54 merged at `ff34f6bb4debb9129c2456a918c502b84adad53c`; main Site Checks and deployment-package workflow passed. The cPanel ZIP, JSON manifest and SHA-256 file are published for this exact commit; no upload/deployment has occurred.
- Correct the staging blocker where a blanket non-`www` redirect would send a staging subdomain to production. Canonical redirects are scoped to production apex/`www` aliases; the shared page head forces noindex outside the exact production origin.
- Add non-production robots regression tests, an exact-main target validator, and a manual staging-browser workflow covering sitemap routes, metadata, JSON-LD, image accessibility/dimensions, overflow, server protection headers, blocked config/hidden files, branded 404 and screenshot evidence.
- Staging remains unverified until the owner provides an HTTPS staging URL, applies the matching local configuration, runs the workflow against the exact staged package commit and completes manual mail/CAPTCHA/Apache checks. CI does not deploy or create a release tag.
