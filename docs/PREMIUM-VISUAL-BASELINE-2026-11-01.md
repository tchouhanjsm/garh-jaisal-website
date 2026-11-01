# Premium visual foundation — repository baseline

Captured: 1 November 2026
Repository: https://github.com/tchouhanjsm/garh-jaisal-website
Scope: Git repository only. The live domain hotelgarhjaisal.com is a different system and is explicitly outside this vendor engagement.

## Exact source baseline

- Default branch: main
- Baseline commit: d15af654b411266a4380a3b839c698959e8412bd
- Parent release merge: PR #162
- Open PRs at baseline capture: none
- New implementation branch: feature/premium-visual-foundation-batch-20261101
- Baseline working tree: GitHub repository source at the exact commit above; no production or staging host inspection was performed.

## Existing foundations retained

- Plain PHP, vanilla CSS and JavaScript, JSON/PHP registries and cPanel-compatible packaging.
- Canonical shared tokens in css/tokens.css.
- Existing shared visual layers: css/garh-v2.css, css/thar-sunset-story.css, css/conversion-system.css, css/accessibility.css and route stylesheets.
- Shared PHP navigation, page-head, footer and UI partials.
- Existing room/experience registries, booking-engine configuration, CTA attribution and contextual enquiry journeys.
- Existing responsive-image generator, source-aware candidates, preload parity and image budgets.
- Existing PHP contracts, Playwright browser tests, sitemap/schema/SEO contracts, package integrity and staging QA safeguards.
- Existing responsive visual review requirements: 320, 390, 768, 1280 and 1440px.

## Repository-only audit observations

1. The design-system token stylesheet contains foundational normalization plus accumulated route-specific responsive overrides. This increases cascade ownership complexity and should be consolidated only with computed-style and screenshot parity evidence.
2. css/garh-v2.css remains the base/legacy compatibility layer, while shared tokens repeat some layout and card normalization. The layering is intentional in places but requires selector-level ownership.
3. Route-specific styles are numerous. The directory is not itself evidence of duplication; each selector and route dependency must be checked before consolidation.
4. Existing source documentation identifies potential page-intent overlaps (for example Explore the Desert vs Sunset Camel Safari, and several overnight desert experiences). These are candidates for editorial review, not confirmed duplicate content without route-level comparison.
5. The current brand-contrast workflow checks documented representative token pairs; the existing design-system documentation correctly states it does not measure every rendered foreground/background pairing.
6. The existing Playwright suite already includes page sheets, first-impression checks, sitewide visual-system checks, responsive image audits and key guest journeys. It should be extended rather than replaced.
7. The latest merged baseline included source-aware image work and carousel accessibility. This batch must not change image sources, image manifests, preload behavior or asset budgets.

## Vendor engagement boundary

- This engagement is against the Git repository and its pull-request workflow.
- Do not inspect, fetch, crawl, screenshot, probe or claim validation against hotelgarhjaisal.com.
- Do not upload packages, dispatch staging host probes, submit forms/enquiries, or deploy to staging/production.
- Do not modify public routes, canonical paths, sitemap ownership, booking destinations, business facts, contact channels or schema semantics as part of the visual-system batch.

## Baseline capture limitations

This is a source and Git metadata baseline, not a browser-rendered screenshot baseline. No browser was run in this execution environment. Existing repository screenshot/test infrastructure is retained and the new PR must run it on its exact head in GitHub Actions. Human visual review of the generated 320/390/768/1280/1440px artifacts remains a required gate.
