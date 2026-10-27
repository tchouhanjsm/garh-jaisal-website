# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Latest state — 27 October 2026

| Item | Value |
|---|---|
| Baseline after PR #151 | `fb02f83f96698cd1948cdd09e1358c74c661bd56` |
| Active implementation | Sprint 73 — responsive image slots across high-intent journeys |
| Scope | Wire responsive candidates across Home, Dining, Rooftop, Desert Safari and Guided Tours |
| Release boundary | Originals untouched; no cPanel upload, staging deployment or production deployment |

## Recent guest-journey milestones

- **PR #145 — homepage and arrival hierarchy:** reduced repeated promotion, improved page rhythm and restored detailed arrival guidance.
- **PR #146 — room comparison:** two-to-three-room comparison dialog and preservation of existing room/rate destinations.
- **PR #147 — room shortlist to Contact:** registry-validated multi-room enquiry with safe retry continuity.
- **PR #148 — room comparison to Trip:** grouped room options enter the shared tab-only Trip tray while separate choices remain selectable.
- **PR #149 — Trip to Contact + experience details:** mixed shortlist Contact fallback, room-option server resolution and full experience-detail links from desert/guided-tour hubs.
- **PR #150 — dining to shared Trip:** same validated `rooftop-dining` selection across restaurant, rooftop and catalogue pages, through to Contact. The corrected head passed all four required workflows.

## Completed Sprint 72 — measured responsive hero-image budgets

The PR #150 Browser E2E artifact measured the local PHP site across 37 sitemap routes, with 149 unique rendered image URLs and 118 images reporting measurable Resource Timing size. These are local browser observations—not production/CDN or Core Web Vitals measurements.

The two largest responsive candidates were `img/responsive/v2/facade-1200.webp` at 416,592 bytes and `img/responsive/v2/courtyard-1200.webp` at 406,736 bytes. PR #151 added narrowly scoped WebP quality overrides of 74 for those two sources while leaving the manifest default quality at 82. Original photographs stay unchanged. The 350 KiB exact-file-size ceilings passed the final Browser E2E run.

## Sprint 73 — responsive image slots across high-intent journeys

The payload audit also identified high-intent templates that used the shared image helper without a responsive `sizes` hint. Sprint 73 adds slot-accurate hints to the Home tent card, restaurant hero, rooftop hero/banner, desert hub hero/cards and guided-tour hero/cards. This enables the browser to select a candidate matching its rendered slot instead of falling back to a full-size 1x image.
- Browser artifact at head `06e132b77593564170ccbe91a8d0c82c381afe75`: 37 local routes, 145 distinct image URLs and 94 measured image resources with non-zero transfer sizes. The largest reviewed candidates were facade-1200 at 299,358 transferred bytes and courtyard-1200 at 282,592 transferred bytes. These are local browser transfer observations, not production/CDN measurements.
- Browser E2E run 40507151185 failed one slot assertion because `img/j6.jpg` is 370×539 and was correctly skipped as `skipped-no-upscale` for widths 480/800/1200. The regression contract preserves that safe fallback and asserts responsive delivery against full-resolution `j2.jpg` instead.
- Browser E2E run 40512057026 on head `e61286152184003165ee5805824d5ed7225490e7` then recorded 590 passed, 605 skipped and one failure: the newly added rooftop-slot test referenced `BASE_URL` without defining it. This was a test-fixture `ReferenceError`; the follow-up adds the explicit local PHP test base URL used by the existing responsive-slot tests. The rerun on the new head remains the acceptance gate.

The source photos, crop, alt text, public routes and business copy remain unchanged. The existing browser slot test now checks both the rendered source and its `sizes`/responsive `srcset` contract. Screenshot review also caught a low-contrast restaurant hero overline on the dark background; the page stylesheet now uses the established light-gold hero color, with a WCAG AA contrast regression assertion. The follow-up exact-head Browser E2E run remains the acceptance gate; no pass is claimed until it completes.

## Verification boundary

- Run and inspect exact-head PHP 7.4/8.2, Browser E2E/screenshots, package preflight and non-deploying Staging QA.
- Image payload and timing data from local Chromium is a comparative engineering baseline, not evidence of production transfer behaviour or Core Web Vitals.
- No deployment is authorized by CI.

## Remaining owner-controlled release blockers

| Priority | Item | Status |
|---|---|---|
| P1 | Host validation | HTTPS staging, deployed package SHA, PHP/Apache behavior, CAPTCHA and mail delivery require real-host verification. |
| P1 | Business-content approval | Menu/prices/hours, policies/privacy, check-in/reception, room rates and verified review quotations need owner approval. |
| P2 | Human design/accessibility review | Review exact-head artifacts at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology. |
| P1 | Production release | No deployment performed; owner approval remains mandatory. |
