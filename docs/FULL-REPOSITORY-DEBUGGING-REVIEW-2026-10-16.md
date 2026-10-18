# Full Repository Debugging Review — Garh Jaisal Haveli Website

**Review date:** 2026-10-16  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Reviewed baseline:** `main` at `a256f22fe31b27a375876c28f3b433ccf009b36d`  
**Open change under review:** [PR #91 — normalize site-wide typography and layout rhythm](https://github.com/tchouhanjsm/garh-jaisal-website/pull/91), head `e040786b0326b881d471a3a101b2031a685fa148`  
**Scope:** Two-pass review, from the Product Manager and Senior Engineer perspectives.

> **Review boundary:** This review combines repository inventory/source inspection with live GitHub Actions evidence. The repository's full recursive tree was retrieved (367 tracked files, not truncated), but this was not a local checkout and not every line of every asset/file could be executed or visually inspected in this session. Items below are marked **Verified**, **Risk / needs validation**, or **Not tested** to avoid overstating coverage. No production deployment or live cPanel test is claimed.

---

## Executive Summary

### Release posture

**PR #91 should not merge while Browser E2E is red.** Its current CI run reports **4 failed browser cases, 484 passed, and 468 skipped**. Site Checks, the cPanel deployment-package check, and the non-deploying Staging QA job passed on the PR head. This is a focused, actionable blocker—not a reason to discard the overall CSS normalization effort.

| Area | Evidence-based status | Consequence |
|---|---|---|
| PHP/site checks | **PASS** on PR #91 head | Static contracts, lint and repository checks reported green |
| cPanel package preflight | **PASS** on PR #91 head | Package validation passed; this does not deploy to cPanel |
| Staging QA | **PASS** on PR #91 head | Non-deploying CI/staging-target checks passed; no external host QA is implied |
| Browser E2E | **FAIL** on PR #91 head | Four failing cases block a clean acceptance gate |
| Production smoke / Lighthouse | **NOT VERIFIED** | No live deployment or production measurements were performed as part of this review |
| Full page-by-page visual QA | **PARTIAL** | CI has five viewport contracts on eleven archetypes; an owner screenshot review remains necessary |

### Highest-priority findings

1. **P1 — Rooms hero exceeds its own visual contract.** The PR #91 Browser E2E log records `/room.php` hero height **1005.25px**, against a test ceiling of **720px**. This directly contradicts the shared-layer goal and is visible layout regression evidence.
2. **P1 — Restaurant keyboard-focus contract fails on token case.** Three viewport cases fail because the test expects `#1d4ed8`, but the CSS custom property reads `#1D4ED8`. The actual CSS focus outline color matches expected RGB, so this appears to be a brittle case-sensitive token-string assertion, not proof that the rendered focus color is wrong.
3. **P1 — Shared styling is broad and needs archetype-specific boundaries.** The PR adds a final cascade layer that targets heroes, section heads, card titles, image frames, buttons and form fields across the site. The current Rooms hero failure demonstrates that selectors interact with legacy/specialized layouts in ways static token contracts alone cannot catch.
4. **P2 — Image weight remains a performance risk.** The inventory contains `img/tent1.jpg` at about **6.14 MB** and multiple large originals/derivatives (several hundred KB). The optimization workflow exists, but the presence of large originals means that actual rendered requests, source selection and image dimensions must be checked by route, not inferred from file presence.
5. **P2 — The server config example contains obvious placeholders.** `include/site-config.example.php` uses placeholder values for the reCAPTCHA secret and recipient. They are correctly described as placeholders; the release process must verify real server-only configuration exists before a contact form is considered operational.
6. **P2 — CI version strategy is a compatibility signal, not production parity.** Site checks run PHP 7.4 and 8.2, while browser/staging jobs use 8.2 and Node 22; CI itself comments that production PHP must be confirmed with the cPanel host. Record the deployed PHP/Apache/module versions before release.

---

## Review Pass 1 — Product Manager / Product Quality

This pass assesses whether the site makes it easy for guests to understand the offer, trust operational details, and complete a direct booking or enquiry.

### PM-01 — Repair the current visual acceptance blockers
**Priority:** P1 · **Status:** Verified

**Evidence:** PR #91 claims a shared typography/layout normalization with hero bounds. The current Browser E2E job fails on Rooms hero height (1005.25px vs. maximum 720px). Restaurant focus-token assertions fail at 320px, 390px and 1280px.

**Guest impact:** Oversized photo-led headers delay access to useful content and booking controls. Failing CI makes it difficult to distinguish a deliberate art-direction choice from an accidental regression.

**Acceptance criteria:**
- All Browser E2E checks pass on the exact PR head, with no test weakened merely to accommodate a layout regression.
- Rooms hero stays within the documented bound at the five contracted widths, or an intentional exception is explicitly documented and justified by the visual evidence.
- Restaurant keyboard focus remains visibly discernible, and token assertions normalize color casing or validate semantic/computed color.
- A refreshed screenshot sheet is reviewed at 390px and 1280px first, then 320px, 768px and 1440px.

### PM-02 — Keep the direct-booking path obvious and truthful
**Priority:** P1 · **Status:** Partially verified in source

The homepage and Rooms page provide direct booking-engine CTAs. Pages also surface Call/WhatsApp and arrival guidance. This is a solid conversion intent; the remaining risk is whether the sticky header, mobile CTA bar, floating dock, and page CTAs compete for visual attention on narrow screens.

**Acceptance criteria:**
- On Home and Rooms, a guest can identify the primary booking action without competing CTAs appearing equal in weight.
- Booking links open correctly and retain safe `target="_blank"` relationships where used.
- No booking CTA is hidden under the sticky header or mobile fixed controls.
- The pre-arrival “vehicles cannot enter the fort” message remains prominent before a guest commits to logistics.
- Direct booking funnel measurement is verified in a real browser; configured analytics IDs alone do not prove conversion events are sent correctly.

### PM-03 — Treat room facts, room count and facilities as data, not duplicated copy
**Priority:** P1 · **Status:** Source contract observed; operational accuracy needs owner confirmation

`include/rooms-data.php` is the registry for seven named rooms and listed facilities. `room.php` derives the room count/floor summary from it and states that facilities vary by room—good protection against hand-maintained counters. Descriptions and facilities still represent owner-facing commercial facts and need periodic sign-off.

**Acceptance criteria:**
- All advertised rooms are present and have working anchors, valid local image paths, useful alt text and current facilities.
- Home, Rooms, metadata/schema and all quick-fact surfaces reflect the same registry.
- No rate/availability promise is made unless it is confirmed by the booking system.
- Any owner-approved room name or facility change updates the registry first, then tests all dependent outputs.

### PM-04 — Separate rooftop dining discovery from menu/reservation intent
**Priority:** P2 · **Status:** Architecture exists; live usability not verified

The repository has separate `roof.php` and `restaurant.php` pages; shared navigation data includes a Dining entry. The menu has category navigation and a vegetarian filter, and the E2E suite covers its expected behavior.

**Acceptance criteria:**
- Each page has a distinct purpose: rooftop experience and atmosphere on one page; menu details and enquiry/reservation next steps on the other.
- Opening times, menu prices, availability and reservation wording are owner-verified before release.
- The vegetarian filter announces updated results and keeps active category state accurate.
- Category tabs have >=44px target dimensions and keyboard-visible focus; current automated checks cover these but are presently failing on the token assertion.

### PM-05 — Close the policy/trust gap with owner-approved wording
**Priority:** P1/P2 · **Status:** Needs owner decision / current policy content review

An earlier `docs/LAYOUT-REVIEW.md` records the need for privacy and booking/cancellation terms, while current tree includes `privacy.php` and `booking-policy.php`. Existence alone does not establish that current consent, data retention, cancellation/refund, no-show, payment and third-party booking terms are complete or legally adequate.

**Acceptance criteria:**
- The privacy policy accurately describes contact-form data, analytics and reCAPTCHA processing.
- Booking/cancellation terms match the actual booking engine and operating practice.
- Footer links are visible on desktop/mobile and accessible from contact/booking-related journeys.
- Owner/legal reviewer approves exact text; developers must not invent policy facts.

### PM-06 — Make travel guides support a coherent journey, not create duplicate SEO paths
**Priority:** P2 · **Status:** Broad content structure exists; consolidation needs ongoing review

The tree includes multiple destination guides, itinerary pages, desert/fort topics and experience hubs. The prior architecture docs explicitly call for stable URLs, deliberate hidden/draft publication status, and avoiding duplicate pages. These rules should continue to govern new content.

**Acceptance criteria:**
- Each indexable route has a unique search intent and canonical URL.
- Every published guide links to one relevant onward action (room, dining, tour, arrival or direct enquiry) without inserting unsupported facts.
- Hidden/draft experiences remain out of navigation/sitemap and are noindex, per the registry contract.
- Sitemap generation and inbound-link checks remain green when content is added or moved.

### PM-07 — Do not equate a green staging dry-run with a deployed staging review
**Priority:** P1 · **Status:** Verified process boundary

The workflow is named “Staging QA” and runs a local dry smoke unless an owner dispatches an external HTTPS staging target and deployed SHA. Repository documentation also states that no external cPanel staging or production deployment occurred in the reviewed phases.

**Acceptance criteria:**
- Before production, deploy the reviewed package to a staging host and run the external staging workflow using the exact deployed SHA.
- Verify mail, CAPTCHA, redirects, headers, images, booking link and representative routes on the actual host.
- Do not report production success based only on local PHP server tests.

---

## Review Pass 2 — Senior Engineer / Implementation Quality

### ENG-01 — Fix the PR #91 Rooms hero regression at the source of the cascade
**Priority:** P1 · **Status:** Verified failing assertion

**Evidence:** Browser E2E log: `tests/e2e/sitewide-visual-system.spec.js:97`; `/room.php` measured height 1005.25px when the allowed height was <=720px. The CSS layer sets photo-hero minimum heights through broad selectors, but min-height alone does not cap actual height when child/layout content expands. The failure is therefore a real geometry issue; changing only a `min-height` value is not guaranteed to solve it.

**Recommended debugging sequence:**
1. Inspect computed styles and bounding boxes for `main .rooms-hero`, `.rooms-hero-grid`, `.rooms-hero-copy`, `.rooms-hero-summary`, and `.page-hero-media` at 1280x800 and on the other four widths.
2. Identify which child is forcing the 1005px height (content, grid stretch, vertical padding, min-height, image wrapper or conflicting legacy selector).
3. Apply a scoped fix for `main.thar-sunset-story--rooms .rooms-hero` / room hero internals rather than raising the shared global cap.
4. Ensure copy and summary can wrap/stack without clipping; do not impose a fixed height that hides content.
5. Re-run the full responsive visual contract and inspect screenshots.

### ENG-02 — Make CSS custom-property tests semantically robust
**Priority:** P1 · **Status:** Verified failing assertions

**Evidence:** `tests/e2e/restaurant-accessibility.spec.js:76` compares the root custom property string to lowercase `#1d4ed8`, while the declaration is uppercase `#1D4ED8`. All three selected viewport cases report this mismatch. The computed outline color reports `rgb(29, 78, 216)`, which equals the expected color.

**Recommendation:** Normalize the token string with `.toLowerCase()` before comparison, or assert a canonical normalized/computed color. Keep the assertions on 3px outline width, matching focus color and keyboard flow. Do not remove keyboard focus tests or relax them to a generic “not empty” check.

### ENG-03 — Scope the last-loaded stylesheet deliberately
**Priority:** P1 · **Status:** Risk demonstrated by current failure

The PR expands `css/tokens.css` from design tokens into a broad global rule layer. Its selectors touch H1 sizing, all listed hero archetypes, section headers, card headings/radii/shadows, repeated media, buttons, inputs and site-shell geometry. Loading a common layer last can be effective, but it raises regression risk for nonstandard templates.

**Recommendations:**
- Keep token definitions separate conceptually from component overrides (even if the deployment constraints favor one file); document what is a token versus a normalized component rule.
- Prefer stable shared component classes and explicit variants to long global selector lists.
- List known exceptions beside the relevant selector and add tests that protect them.
- Avoid setting `max-width:100%` on the global shell if any page depends on a different intentional width; test the header and footer too, not only the page's first `.site-shell`.
- Audit CSS specificity and duplicate/conflicting declarations before introducing `!important`.

### ENG-04 — Verify image delivery per rendered route and viewport
**Priority:** P2 · **Status:** Verified large assets; actual transfer sizes not measured here

The recursive tree lists 367 tracked files, including 19 WebP images and an optimization directory, but also high-payload JPEGs: `img/tent1.jpg` ≈6.14MB, `img/v2/facade.jpg` ≈872KB, `img/v2/courtyard.jpg` ≈871KB, `img/v2/green-room.jpg` ≈800KB, and more. Optimized derivatives exist, but that does not guarantee the relevant `<img>` or `<picture>` selects them in every page.

**Recommendations:**
- Use the existing image payload audit to fail on regressions in actual page requests, not only file existence.
- Confirm hero source/preload match (same-origin and correct viewport source); avoid preloading a JPEG while the rendered image fetches a WebP or vice versa.
- Check intrinsic width/height, `srcset` candidate selection, lazy loading below the fold and eager/high priority only for the actual LCP image.
- Keep original source assets if needed for editorial quality, but do not make the browser transfer full originals unnecessarily.
- Re-measure production Core Web Vitals after staging; CI is not a substitute for field data.

### ENG-05 — Verify runtime config and contact delivery as release gates
**Priority:** P1 · **Status:** Code inspected; production configuration not testable from repository

`include/site-config.php` keeps the reCAPTCHA secret and recipient overrides in `site-config.local.php` or an environment variable. `mail.php` checks POST, CSRF token, honeypot, input length/format, a session throttle, server-side reCAPTCHA response/hostname and recipients. These are positive safeguards. `include/site-config.example.php` intentionally contains placeholders and must not be copied unchanged to production.

**Recommendations:**
- Add a deployment/preflight check that verifies non-placeholder recipient(s) and secret are configured on staging without printing secret values.
- Verify actual mail transport, PHP mail/cURL configuration, outbound HTTPS from cPanel, reCAPTCHA hostname and email deliverability on staging.
- Add error-path tests for missing config, reCAPTCHA timeout/non-JSON response, invalid hostname, duplicate submits and mail failure.
- Ensure logs never include CAPTCHA secrets, CSRF values or full guest messages.
- Verify the private local config is excluded from deploy packages; current code comments and .gitignore indicate this intent, but actual host config still requires checking.

### ENG-06 — Verify redirect and security-header behavior on the target Apache configuration
**Priority:** P2 · **Status:** Static contract exists; target host unverified

`.htaccess` disables directory listing, limits canonical redirects to production host aliases, denies hidden files and selected backups/logs, sets basic browser security headers, compression and cache rules, and maps 404s. `scripts/check-launch-hardening.php` tests the intended file content. It explicitly acknowledges that it does not verify Apache modules or production behavior.

**Recommendations:**
- On staging, test HTTP-to-HTTPS and non-www-to-www paths, query strings, URL encoding and non-production host isolation.
- Confirm required Apache modules/directives are supported by the GoDaddy/cPanel environment to avoid HTTP 500.
- Verify 404 response status and noindex behavior, private config denial, directory-listing denial, response headers and caching on actual routes/assets.
- Keep HSTS deferred until the owner confirms certificate and all required subdomain coverage.

### ENG-07 — Improve delivery reproducibility and workflow cost visibility
**Priority:** P2 · **Status:** Verified workflow design; improvement opportunity

`package.json` only defines `test:e2e` and does not declare a locked Playwright dependency; workflows install `@playwright/test@1.55.0` with `--no-save --package-lock=false`. This is version-pinned in CI commands, which helps, but dependency reproducibility/security review would be clearer with a committed lockfile and standard install command. The current E2E job takes about **11.2 minutes** and its page-sheet test alone is reported at **7.5 minutes**.

**Recommendations:**
- Prefer committed dev dependency + lockfile with `npm ci` where compatible with the current repo policy.
- Preserve the five-width contract, but review whether screenshot generation can be split from core fast E2E or run in a dedicated job; do not reduce coverage without evidence.
- Review action runtime warnings about deprecated Node 20 action runtimes and upgrade actions to supported versions after confirming compatibility.
- Continue collecting screenshots, traces and HTML reports as artifacts when a workflow fails.

### ENG-08 — Extend audit coverage to dynamic/shared template boundaries
**Priority:** P2 · **Status:** Partial coverage exists

The PHP checker validates sitemap targets, registry publication status, shared page-head use and literal local paths for a scope of files. The browser suite covers many routes and behaviors. Static string scanning cannot fully validate PHP-generated paths, redirects, remote booking URLs, runtime config or all CSS computed states.

**Recommendations:**
- Audit the complete tree by categories: public PHP route, include/partial, configuration, script, CSS, JS, image, workflow, test and documentation.
- Validate each live route with response code, canonical, robots, exactly one visible H1, schema parseability, internal links, image status and console errors.
- Test PHP-generated link/image paths through rendered pages, not just literal source matches.
- Keep data registries as the source of truth for route/nav/sitemap/structured data wherever the current architecture supports them.
- Make the review report an evolving triage document, then update each row only when there is repeatable test evidence.

### ENG-09 — Keep secrets, generated output and development tooling outside the deployment payload
**Priority:** P1 · **Status:** Safeguards visible; package test exists

The repository has `.gitignore` rules for local site config, Node dependencies and generated test artifacts, plus a cPanel deployment package workflow and unit tests checking that docs/scripts/tests/tooling are excluded and private config cannot be packaged.

**Acceptance criteria:**
- Run package build and inspect contents for each release SHA.
- Confirm `include/site-config.local.php`, credentials, `node_modules`, test traces/reports and development-only files never enter the deployment archive.
- Do not rely on `.gitignore` alone; validate the actual deployment artifact and server document root.
- Rotate credentials if any server-only secret has ever been committed or exposed.

---

## Current Open PR #91 — Focused Review Notes

**PR:** [#91 — feat: normalize site-wide typography and layout rhythm](https://github.com/tchouhanjsm/garh-jaisal-website/pull/91)  
**Head:** `e040786b0326b881d471a3a101b2031a685fa148`  
**CI:** [Browser E2E run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38927330738) · [Site Checks run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38927330962) · [Package run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38927330778) · [Staging QA run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38927330746)

| Gate | Result |
|---|---|
| Site Checks | PASS |
| cPanel deployment package | PASS |
| Staging QA | PASS |
| Browser E2E | FAIL — 4 failing cases; 484 passed; 468 skipped |

### Required before merge

- [ ] Correct the Rooms hero geometry without weakening the 720px acceptance limit unless a product-approved exception is documented and visually reviewed.
- [ ] Normalize the focus token assertion casing while preserving computed-color and keyboard-focus assertions.
- [ ] Re-run Browser E2E on the exact updated head.
- [ ] Confirm all four workflow jobs are green for that exact head.
- [ ] Inspect screenshot sheet at 390px and 1280px; spot-check 320px, 768px and 1440px.
- [ ] Owner retains merge and production deployment authority.

---

## File/Area Inventory and Review Coverage

The GitHub recursive tree for `main` was retrieved successfully and reported **367 tracked files** with no tree truncation. Inventory counts:

| File class | Count |
|---|---:|
| PHP | 82 |
| JavaScript | 57 |
| CSS | 16 |
| Markdown | 25 |
| YAML | 4 |
| HTML | 7 |
| JSON | 3 |
| Python | 4 |
| XML | 1 |
| Images (WebP/JPEG/JPG/PNG/SVG/AVIF) | 164 |
| Other | 4 |

Files/components inspected in source or repository metadata include:
- Core routes: `index.php`, `room.php`, `contact.php`, `restaurant.php`, `mail.php`, and shared experience/guide/template files.
- Shared layout/data: `include/header-v2.php`, `include/page-head.php`, `include/schema.php`, `include/session.php`, `include/site-config.php`, `include/site-config.example.php`, `include/rooms-data.php`, `include/ui/hero.php`.
- CSS/design system: `css/garh-v2.css`, `css/tokens.css`, `css/packages.css`, `css/thar-sunset-story.css`, `restaurant.css`.
- Runtime behavior: `restaurant.js`.
- Safety/build checks: `.htaccess`, `scripts/check-site.php`, `scripts/check-launch-hardening.php`, `scripts/check-brand-contrast.php`, `scripts/build-responsive-image-review.py`, `tests/test_deploy_package.py`.
- Workflows: `.github/workflows/ci.yml`, `.github/workflows/browser-e2e.yml`, `.github/workflows/staging-qa.yml`.
- Regression coverage: `tests/e2e/restaurant-accessibility.spec.js`, `tests/e2e/sitewide-visual-system.spec.js`, plus the suite listed in the tree.
- Decision docs: `docs/LAYOUT-REVIEW.md`, `docs/SITE-ARCHITECTURE.md`, `docs/OWNER-FACTS.md`, `docs/OPERATIONAL-CONTENT-TRUST-AUDIT.md`.

**Coverage limitation:** The list above identifies inspected representatives and reviewed CI evidence. It is not a claim of an exhaustive semantic, visual, security or execution review of every byte of all 367 files. The attached/repository report should be expanded as individual routes and assets are systematically verified.

---

## Prioritized Backlog

| ID | Priority | Work item | Done when |
|---|---|---|---|
| R-01 | P1 | Fix PR #91 Rooms hero overflow | Five-width layout test passes; screenshot confirms readable copy/controls |
| R-02 | P1 | Fix focus token test casing | Restaurant keyboard/focus tests pass without reducing semantics |
| R-03 | P1 | Run exact-head full CI | All required workflows green on the same commit |
| R-04 | P1 | Verify live cPanel configuration and contact delivery | Staging can submit valid and invalid form cases; private config verified without revealing secrets |
| R-05 | P1 | External staging acceptance | Correct deployed SHA, redirects/headers, route smoke, booking path and forms confirmed |
| R-06 | P2 | Route-level image payload audit | High-priority routes use the intended source/candidate and meet documented payload budgets |
| R-07 | P2 | CSS cascade/archetype boundaries | Shared rules remain consistent while page-specific designs stay deliberate |
| R-08 | P2 | Reproducible Node test dependencies | Lockfile and pinned installation path reviewed; workflow stays green |
| R-09 | P2 | Policy and commercial fact owner review | Privacy, booking/cancellation, room facilities, menu and service facts approved |
| R-10 | P2 | Continue registry/SEO validation | Live/draft/hidden, canonicals, schema, sitemap and navigation stay in sync |

---

## Verification Commands / Release Checklist

Run from the repository root, on the exact commit being considered:

```bash
# PHP syntax
find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l

# Source, sitemap, publication and launch checks
php scripts/check-site.php --strict-images
php scripts/generate-sitemap.php --check
php scripts/check-experience-publication.php
php tests/php/experience-gallery-assets.php
php tests/php/gallery-curation.php
php tests/php/at-a-glance-contract.php
php tests/php/preload-contract.php
php tests/php/experience-discovery.php
php scripts/check-launch-hardening.php
php scripts/check-brand-contrast.php
php tests/php/staging-noindex.php

# Python deployment package tests
python -m unittest tests/test_deploy_package.py

# Browser suite (install the version required by project/workflow first)
npm run test:e2e
```

Before the release is called complete, also:
- build and inspect the deployment package;
- verify external staging against the exact deployed SHA;
- test real contact mail and CAPTCHA behavior on staging;
- inspect responsive screenshots and browser console/network failures;
- record production PHP/Apache versions;
- deploy only after the owner accepts the evidence.

## Final Recommendation

**Treat the current PR #91 as “changes required,” not merge-ready.** The broader direction—a shared visual system, registry-driven content, image optimization, PHP/site contracts and broad browser coverage—is sound in principle. The immediate gate is concrete: fix the Rooms hero geometry and case-sensitive focus-token assertion, then obtain an all-green exact-head run and perform the owner screenshot review. Keep production/cPanel claims separate from local CI evidence.


---

## Revalidation and disposition — 2026-10-18

The PR #91 section above is a historical snapshot from 2026-10-16, not the current open-PR state. PR #91 merged on 2026-10-17; PR #95 fixed the Rooms hero regression and PR #97 hardened arrival/transfer/dining claims plus the cached responsive-image helper. Current `main` is `1a385f3c2975c7308849335d412b31f295ea4fd0`; no open PR existed at the start of Sprint 48.

| Backlog item | Current disposition | Evidence / remaining work |
|---|---|---|
| R-01 Rooms hero geometry | **Resolved in repository CI** | PR #95 merged; current merge-head Browser E2E passed. Owner still needs to inspect full-size responsive screenshots rather than infer crop quality from pass status. |
| R-02 focus token assertion | **Resolved in repository CI** | Current merge-head Browser E2E passed with focus-color and keyboard tests enabled. |
| R-03 exact-head full CI | **PASS at current main** | [Site Checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805415), [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805433), [package preflight](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805478), [Staging QA dry run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805519). A new PR still needs its own exact-head pass. |
| R-04 runtime configuration / contact delivery | **BLOCKED on external staging** | CI checks structure and failure handling; it cannot prove live host configuration, CAPTCHA hostname, SMTP/mail route, recipient receipt, filesystem permissions or backup/retention. |
| R-05 external staging acceptance | **BLOCKED** | No owner-supplied HTTPS staging origin or deployed package SHA is available in this review. |
| R-06 route-level image payload | **PARTIAL** | Responsive source selection, intrinsic sizes and image payload inventories exist in CI; production transfer sizes and source/preload/cache/MIME alignment remain unverified. Image/caption mapping still needs owner visual approval. |
| R-07 CSS cascade and page archetypes | **PARTIAL** | Main Browser E2E passes responsive structural contracts. Physical devices, full screenshot judgement, assistive technology and contrast review remain human checks. |
| R-08 reproducible browser dependencies | **OPEN, non-blocking for source merge** | Workflows pin Playwright `1.55.0` but `package.json` still has no declared dev dependency/lockfile. Continue as a separate tooling task; do not confuse a green run with lockfile reproducibility. |
| R-09 policies and commercial facts | **OWNER BLOCKED** | Privacy/data-controller/retention language, cancellation/payment terms, actual menu contents/prices, restaurant hours and transport quotes must be supplied and approved; no facts are to be invented. |
| R-10 registry/SEO parity | **PASS for repository checks; live parity NOT VERIFIED** | Publication/sitemap/metadata checks pass in CI. The public production Home and Directions pages still show legacy content, so an actual staging release must prove route/redirect/schema/content parity. |

### New finding — unconfirmed menu content

The source menu registry is explicitly marked `owner_confirmed: false`, but the page previously rendered its dish names and descriptions and replaced only numeric prices with “Price on request”. Those names/descriptions were therefore reaching the public HTML without owner approval. Sprint 48 gates the whole menu publication unit and asserts the fallback CTA and absence of fixture dish names in rendered HTML.

### Release posture

**Repository CI is green; the website is not yet release-approved.** The production host was retrieved on 2026-10-18 and still served legacy Home/Directions copy, including fixed transfer fares and arrival-service promises no longer present in repository source. The next release step is an owner-managed staging deployment of the exact reviewed package, followed by manual mail/CAPTCHA, Apache/PHP, image/cache, booking/contact and screenshot checks. No cPanel upload, merge or production deploy is done by this review.
