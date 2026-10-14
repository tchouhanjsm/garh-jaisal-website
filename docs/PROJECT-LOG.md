# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-14 |
| Current phase | Sprint 17 — booking and enquiry intent measurement |
| Base branch | `main` |
| Baseline commit | `f441272b17fdbf520e5a06a84612fd5b84bfb8be` (PR #63 merge commit) |
| Last merged PR | [#63 — site-wide arrival claim accuracy](https://github.com/tchouhanjsm/garh-jaisal-website/pull/63) |
| PR #63 merge-commit checks | [Site Checks PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913587); [cPanel package preflight PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913723); [Staging QA PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913752); [Browser E2E + screenshot sheet PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913569) |
| Screenshot evidence | [PR #63 390px/1280px page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913569/artifacts/11878533834); [PR #63 Browser evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913569/artifacts/11878259357) |
| Image review evidence | [Responsive WebP candidates](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38423513526/artifacts/11828260774) — review-only, not enabled for runtime |
| Production URL | https://www.hotelgarhjaisal.com |
| Open PRs at baseline verification | None at Sprint 17 branch creation |
| Next action | Review PR #65 exact-head CI and licensed image replacements; owner alone merges |

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
| 8 | Analytics measurement | ◐ Sprint 17 event contract approved; implementation and live-property validation pending |
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
- Validate event receipt in both GA4 properties using Realtime/DebugView and decide whether to register custom dimensions for the new event parameters.
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


## Sprint 6 — direct-stay guide discoverability (PR #56 merged)

- Baseline: PR #55 merged at `09b60a45adf607052475e69aad4c379e9fd66445`; PR #56 merged at `68f1a931b656e5b0d4df8e89f676eeec6bf060ea`.
- Added the existing `book-rooms-in-jaisalmer.php` guide links from Home and Rooms and a Browser E2E navigation regression; no public URL, booking destination, photo mapping or GA4 ID changed.
- Exact-head [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38437562231), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38437562240), [Staging QA dry run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38437562309) and [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38437562317) passed. Evidence artifacts: [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38437562317/artifacts/11833009704) and [staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38437562309/artifacts/11833078639).
- Owner review first: Home, Rooms and the guide at 390px/1280px. Real staging, CAPTCHA/mail, image approval and release remain owner-gated.

## Sprint 7 — experience publication boundaries (PR #57 merged)

- Baseline: PR #56 merged at `68f1a931b656e5b0d4df8e89f676eeec6bf060ea`; PR #57 merged at `5415e67b422b2318dfb576e7d84531bacaa5e271`.
- Removed live Desert Tours planner copy that promoted the hidden `explore-the-desert` page, without retiring its URL or changing its noindex state.
- Added source checks for valid experience statuses, route-file availability, and live/non-live sitemap and navigation boundaries; browser coverage checks every sitemap route.
- Exact PR-head [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38439677652), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38439677679), [Staging QA dry run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38439677493) and [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38439677684) passed. [Page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38439677684/artifacts/11834807257).
- Real staging, CAPTCHA/mail, final image approval and release remain owner-gated.

## Sprint 8 — pre-booking FAQ coverage (PR #58 merged)

- Baseline: PR #57 merged at `5415e67b422b2318dfb576e7d84531bacaa5e271`; PR #58 merged to main at `33516d42e7e06b055c782da714cc8836f528574e`.
- Added visible FAQs and matching FAQPage schema on Rooms, rooftop dining, menu, Packages, Desert Tours, Car & City Tours, guided tours, Fort walk, car arrangements and camping.
- Exact PR-head [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38500923692), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38500923724), [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38500923694) and [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38500923706) passed. [390px/1280px page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38500923706/artifacts/11865921955).
- Real staging, CAPTCHA/mail, final image approval and release remain owner-gated.

## Sprint 9 — Car & City tour discovery and selected-page QA (PR #59 merged)

- Baseline: main at `33516d42e7e06b055c782da714cc8836f528574e`; main-push Site Checks, package preflight, Staging QA and Browser E2E passed on this SHA.
- Remove the duplicate category breadcrumb and give the three existing Car & City cards fixed 16:10 media frames; protect Rooms and Rooftop Dining with selected-page Browser E2E.
- Add six researched sightseeing themes as enquiry-only ideas, not confirmed hotel packages; each opens a safe prefilled Contact enquiry. Access, route, transport, duration, fees and price remain unconfirmed until the owner approves.
- Research references and operational questions are in `docs/JAISALMER-TOUR-RESEARCH.md` and `docs/OWNER-FACTS.md`.
- No new public routes, prices, booking claims, image assets/mappings, analytics IDs, third-party libraries, deployment or release tag. Real staging and operator-specific tour details remain owner-gated.


## Sprint 10 — room booking journey clarity (PR #60 merged)

- PR #60 merged to main at `2c587f98d3f926367c5cfaee2f81ef240dd59296`; no open PRs remained at the next-batch baseline.
- Added `tests/e2e/room-booking-clarity.spec.js` to protect the existing availability destination and selected-room Contact journey for all seven rooms.
- Exact PR-head PHP/site checks, package preflight, staging dry run, Browser E2E and screenshot sheet passed; real booking-provider checkout and external staging remain owner-gated.

## Sprint 11–12 — arrival policy accuracy + enquiry completion (PR #61 merged)

- PR #61 merged to `main` at `c1834eca165ef67f53cca18153be7e08ab657112`; exact-head Site Checks, package preflight, Staging QA and Browser E2E/screenshot sheet passed.
- Removed unconfirmed reception, early-arrival, dining-time and meet-and-assist promises; updated only approved Directions description values in the frozen metadata fixture.
- Added an arrival-specific Contact prefill for check-in date/ETA and regression coverage for Directions → Contact without submitting a live enquiry.
- No public routes, booking destinations, image mappings, GA4 IDs, dependencies, confirmed prices or policy terms changed.
- Owner confirmation of arrival/dining hours and live staging/mail delivery remains required.


## Sprint 13–14 — dining availability accuracy + enquiry completion (PR #62 merged)

- PR #62 merged to `main` at `29afa115f5e6b1b0f64b45ac1846bac03052634d`; all four merge-commit CI workflows passed.
- Removed unconfirmed meal hours and reservation-time limits; clarified the dining request is not a confirmed table and improved WhatsApp/Contact prefills.
- Added a direct dining enquiry fallback and end-to-end coverage; aligned the shared site-smoke assertion with the structured prefill.
- Contact/restaurant sitemap dates updated; online table-request mail was not enabled.
- Owner confirmation of dining hours and live staging/mail delivery remain required.


## Sprint 15–16 — site-wide arrival accuracy + contextual enquiry consistency (PR #63 merged)

- PR #63 merged to `main` at `f441272b17fdbf520e5a06a84612fd5b84bfb8be`.
- Removed stale reception-hour and unconditional meet-and-assist/luggage promises from Home, the Stay Guide and the Fort Guide; linked each page to Directions and the contextual arrival enquiry.
- Added rendered Browser E2E to guard all three public pages against withdrawn promises and verify both arrival destinations remain available.
- Updated article `dateModified` values, sitemap dates and project/sprint snapshots. Corrected an intermediate stale homepage assertion without weakening the test.
- Exact merge-SHA Site Checks, cPanel package preflight, Staging QA dry run, and Browser E2E/screenshot generation all passed: [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913587), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913723), [Staging QA](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913752), [Browser E2E and screenshot sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38531913569).
- Official check-in/out, current reception hours and assistance availability remain owner-input items. CI did not verify the live cPanel host or submit a real enquiry.


## Sprint 17 — booking and enquiry intent measurement (PR #64 in review)

- Baseline `main`: `f441272b17fdbf520e5a06a84612fd5b84bfb8be`; branch: `feature/sprint-17-conversion-measurement`. Owner approved the event contract on 2026-10-14.
- Add `booking_outbound_click`, `click_to_call`, `click_whatsapp` and `enquiry_start`; route each custom event to both configured GA4 IDs using the site configuration as the single source of truth.
- Enquiry context is limited to `room`, `dining`, `arrival` or `general`. Send only route/context and static CTA metadata; never send field contents, messages, email addresses, or query strings. These events represent intent, not confirmed bookings or sent enquiries.
- Browser E2E checks event payloads and routing in the data layer, and focuses the form without submitting it. Actual GA4 receipt, Realtime/DebugView validation, and optional custom-dimension registration remain owner-side checks.


## PR #65 — licensed image reference replacement (2026-10-14)

- Replaced removed image references in the homepage, Desert Gallery and shared experience data without restoring any image reported for copyright infringement.
- Kept the deleted image files deleted; aligned the responsive-image review inventory and WebP regression manifest with the surviving/new licensed asset set.
- Regenerated the sitemap timestamp for the changed Gallery route; PR-head CI and screenshot evidence must pass before owner merge.
- The QR artwork `img/restaurant_QR.png` is not part of this PR’s changed files. Its Google Business Profile destination and restaurant-page integration are a separate follow-up, pending the exact profile share URL.
