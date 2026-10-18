# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file inside every PR with verified main/CI state. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-18 |
| Current phase | Sprint 51 — exact-main verification, final package validation and owner release gates |
| Base branch | `main` |
| Verified main SHA | `b7334ec9b0dea4aa3ba527e38725dd96131712a6` (PR #104 merge commit, 2026-10-18) |
| Latest merged PR | [#104 — size existing responsive images by layout slot](https://github.com/tchouhanjsm/garh-jaisal-website/pull/104) |
| PR #102 | Merged before #103/#104; extended responsive candidates, preload alignment, Gallery full-size lightbox, Car & City Tours breadcrumb, plus source and browser regression contracts |
| PR #103 | Merged; pins `@playwright/test@1.64.1`, commits the npm-generated `package-lock.json`, switches CI to `npm ci` and checks high-severity advisories |
| PR #104 | Merged; sets CSS-layout-aware `sizes` on existing responsive image slots; originals, selected photography and public URLs remain unchanged |
| Main-head Browser E2E | [Run 39128969413](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39128969413), exact SHA `b7334ec9b0dea4aa3ba527e38725dd96131712a6`; **IN PROGRESS at snapshot time**, do not treat as PASS |
| Main-head setup steps | Pillow/image generation, PHP/Node setup, `npm ci`, `npm audit --audit-level=high` and Chromium installation were complete; the full responsive Playwright suite was running |
| Main-head classic commit statuses | No entries reported by the combined-status endpoint; use exact-head Actions run evidence instead |
| Open pull requests | No open PRs reported when checked after PR #104 merged |
| Latest release readiness report | `docs/FINAL-RELEASE-READINESS-2026-10-18.md` (reconciled in this PR branch) |

## Current release backlog

| Priority | Item | Acceptance criteria | Status |
|---|---|---|---|
| P1 | Exact-main Browser E2E | Run `39128969413` completes on the recorded SHA; inspect failures and screenshots/page sheet; record conclusion and artifact links | In progress at snapshot time |
| P1 | PHP/source/site checks | PHP 7.4/8.2 lint, strict image checks, source contracts, publication guards and sitemap check pass on the final SHA | Reverify for final candidate |
| P1 | Runtime ZIP integrity | Package preflight passes; build review ZIP and manifest; extract cleanly and check all sitemap routes/local assets/runtime files | Pending final test gate |
| P1 | Owner-only content facts | Approved details entered into `docs/OWNER-FACTS.md`; gates updated only with those facts | Blocked on owner input |
| P2 | Image semantic review | Human confirms photo subject/caption/alt/crop alignment across representative page and viewport evidence | Manual review pending |
| P2 | Responsive accessibility/UX | Review 320, 390, 768, 1280 and 1440 widths; keyboard/focus, contrast, reduced motion, sticky/floating CTAs and assistive technology | Manual review pending |
| P2 | Operational integrations | Owner verifies mail/CAPTCHA, lead access/retention, analytics receipt, host PHP and production performance in the owner's environment | External owner verification pending |

## Owner-blocked dependencies

Do not infer or publish unconfirmed restaurant menu/prices/hours, booking and cancellation terms, privacy/data-controller and lead-retention details, check-in/out or reception promises, room-rate display policy, review quotations, tour inclusions/pricing, transfer fares/timings or Instagram post/reel embeds. Continue to keep the restaurant menu gated and table requests explicitly unconfirmed until the owner approves those facts.

## Release boundary

No merge, hosting change, cPanel access or deployment is authorized by this log. Package ZIPs produced by CI are review artifacts only. Final release requires successful exact-SHA checks, package extraction/runtime verification, human visual/accessibility review and explicit owner resolution of the business-data blockers.
