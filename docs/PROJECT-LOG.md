# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-13 |
| Current phase | Post-PR #51 release readiness and retained-font verification |
| Base branch | `main` |
| Baseline commit | `86108fb77c3c9aec58c82f2e9e7f42c1a9727af6` (PR #51 merge commit) |
| Last merged PR | [#51 — harden launch and prepare release QA](https://github.com/tchouhanjsm/garh-jaisal-website/pull/51) |
| Exact-main CI evidence | [Site Checks PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526); [Browser E2E/screenshots PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551) |
| Screenshot evidence | [390px/1280px page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551/artifacts/11828016923); [browser traces/reports](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551/artifacts/11827971993) |
| Image review evidence | [Responsive WebP candidates](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526/artifacts/11828260774) — review-only, not enabled for runtime |
| Production URL | https://www.hotelgarhjaisal.com |
| Open PRs at baseline verification | None |
| Next action | Verify fonts against all retained asset references, then complete staging checklist before release/tag; never deploy or tag without owner approval |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and safe CI baseline | ✓ CI and asset cleanup merged; font-reference cleanup remains |
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


## Current verified state after PR #51 (2026-10-13)

This note supersedes stale entries above that still label PRs #48–#51 as open/in progress.

- `main` is at merge commit `86108fb77c3c9aec58c82f2e9e7f42c1a9727af6`; PR #51 is merged and no open PRs were found at the start of this work.
- Exact-main Site Checks passed: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526
- Exact-main Browser E2E and screenshot generation passed: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551
- Screenshot sheet: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513551/artifacts/11828016923
- Responsive image candidates remain review-only: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526/artifacts/11828260774
- Owner-side staging, cPanel/Apache behavior, form delivery and full-size image approval remain mandatory before release/tagging. No deployment or release tag was made.
- Next engineering action: complete retained-font reference verification before any deletion; then use a separate reversible cleanup PR only for assets proven unused.
