# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file inside every PR with verified main/CI state. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-19 |
| Current phase | Sprint 51 — Directions arrival-journey UX and responsive layout |
| Base branch | `main` |
| Verified main SHA for this batch | `f810d79547a04c8fe3d5b84612eafdedf88545c0` (PR #105 merge commit) |
| Latest merged PR at batch start | [#105 — reconcile release status with PR #104 baseline](https://github.com/tchouhanjsm/garh-jaisal-website/pull/105) |
| Main Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562365) on `f810d79547a04c8fe3d5b84612eafdedf88545c0` |
| Main Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562397) on the same SHA, with the current screenshot/evidence upload workflow |
| Main package preflight/build | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562358) on the same SHA; review ZIP only, not deployment |
| Main non-deploying Staging QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39132562402); local dry run, not external-host validation |
| PR #102–#104 | Merged: responsive candidate coverage, full-size Gallery lightbox, breadcrumb correction, pinned Playwright lockfile/CI install, and CSS-aware responsive `sizes` |
| PR #105 | Merged; reconciled the release-readiness report and project log but left the baseline snapshot at its pre-merge SHA, corrected in this batch |
| Feature branch | `feat/directions-arrival-experience-20261019`, created from the verified main SHA above |
| Feature validation | Site and Browser E2E checks must pass on the exact feature PR head; pending until the current branch is opened for review |

## Current feature batch

**Scope:** refine the PHP Directions journey using the user's getting-here reference concept while keeping the established site architecture and existing `direction.php` URL.

- Add an arrival summary card in the hero with three steps, an exact hotel Maps pin, and truthful “ask to confirm” language for any assistance.
- Add keyboard-accessible in-page jump links to directions, travel options, walking/mobility, map and FAQ sections.
- Convert the four parking-to-door steps into an ordered list with distinct responsive route-step styling.
- Add browser regression coverage for the new summary, Maps link, section anchors and 320/390/768/1280/1440px overflow.
- Do not copy the reference mockup’s unsupported prices, timings, named staff, check-in promises, unverified service claims, fake photo URLs, or nonexistent form action.
- No new server endpoint, runtime dependency, public URL, analytics identifier, business policy, hosting configuration or deployment behavior is introduced.
- Refresh `sitemap.xml` only for `/direction.php` to its latest Git-derived `lastmod` date (`2026-10-19`); `php scripts/generate-sitemap.php --check` remains the regression gate.

**Acceptance criteria:** PHP 7.4/8.2 site checks, strict local-image/sitemap/publication contracts, package preflight, and the complete Playwright suite pass on the exact feature head. Review screenshots at mobile, tablet and desktop widths. No merge without the owner's explicit approval.

## Current release backlog

| Priority | Item | Acceptance criteria | Status |
|---|---|---|---|
| P1 | Directions feature validation | Exact feature-head PHP/site checks, package preflight and Browser E2E all pass; inspect screenshot/page-sheet artifacts | Pending feature PR CI |
| P1 | Public route and source integrity | Preserve `direction.php`, canonical metadata, configured contact links, Google Maps pin/embed and current arrival-policy safeguards | Protected by existing + new regression tests |
| P2 | Visual and accessibility judgement | Review 320, 390, 768, 1280 and 1440 widths; keyboard/focus, contrast, reduced motion, CTA collision and screen-reader behavior | Manual owner review remains |
| P2 | Owner-only business facts | Approved details entered in `docs/OWNER-FACTS.md`; gates updated only from those facts | Blocked on owner input |
| P2 | Host/runtime behavior | Apache rewrites, actual host PHP version, CAPTCHA/mail, lead retention and analytics receipt verified in owner-controlled environment | External owner verification pending |

## Owner-blocked dependencies

Do not infer or publish unconfirmed restaurant menu/prices/hours, booking and cancellation terms, privacy/data-controller and lead-retention details, check-in/out or reception promises, room-rate display policy, review quotations, tour inclusions/pricing, transfer fares/timings or Instagram post/reel embeds. Continue to keep the restaurant menu gated and table requests explicitly unconfirmed until the owner approves those facts.

## Release boundary

No merge, hosting change, cPanel access or deployment is authorized by this log. Package ZIPs produced by CI are review artifacts only. Final release requires successful exact-SHA checks, package extraction/runtime verification, human visual/accessibility review and explicit owner resolution of the business-data blockers.
