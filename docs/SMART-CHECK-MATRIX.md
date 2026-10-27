# Smart Check Matrix

This matrix tells the canonical Smart Check skill (`.agents/skills/smart-check/SKILL.md`) and its Claude Code entrypoint (`.claude/skills/smart-check/SKILL.md`) which role is **primary** for a change and which checks that role runs. It is a selection guide, not a claim that every matrix item is fully automated.

All four roles (Full-Stack Developer, SEO Content Writer, Debugger, UI/UX Lead) review every change. A role that is not primary still reports **N/A-GREEN** and names what it checked. See the skill for the double-check rule.

## Role ownership

| Change surface | Primary role | Also double-checked by | Minimum checks | Broader regression / evidence |
|---|---|---|---|---|
| `*.php`, `include/**`, request handling, routing | Full-Stack Developer | Debugger, UI/UX Lead | PHP lint on CI versions; `php scripts/check-site.php --strict-images` | Route smoke tests; relevant form or page behavior |
| Forms, `mail.php`, redirects, escaping, config, scripts | Full-Stack Developer | Debugger | Inspect validation, escaping and secret boundaries | Safe negative tests; no real emails, live enquiries or secret output |
| CSS, navigation, shared header/footer, layout, room cards | UI/UX Lead | Full-Stack Developer, Debugger | Playwright smoke; no horizontal overflow | 320×700, 390×844, 1280×800, 1440×900 projects; inspect screenshot artifacts |
| Forms, controls, icons, images, keyboard behavior | UI/UX Lead | SEO Content Writer | Static audit plus rendered checks where available | Keyboard and focus checks, labels and alt text; manual review for contrast and screen readers |
| Image paths, formats, dimensions, mapping | UI/UX Lead | Full-Stack Developer | Strict image-reference check; verify file exists | Visual room-to-image matching; file size and dimensions; responsive rendering |
| Booking URLs, call/WhatsApp links, thank-you page, analytics | UI/UX Lead | Full-Stack Developer | Assert existing destinations and Browser E2E event payload/routing without sending a live enquiry | Live GA4 receipt, Realtime/DebugView, consent and property-level custom dimensions remain owner-side checks |
| Titles, descriptions, canonicals, H1, sitemap, JSON-LD, redirects | SEO Content Writer | Debugger, Full-Stack Developer | Rendered SEO E2E checks over sitemap routes | Actual HTTP response and rendered HTML; do not rely on source heuristics alone |
| Landing pages, area guides, headings, internal links, location intent | SEO Content Writer | UI/UX Lead | Fact-check against approved project content; heading, link and metadata checks | Existing URLs and canonical targets confirmed; never fabricate hotel facts or location claims |
| CSS/JS/image payload, third-party scripts, font loading | UI/UX Lead | Full-Stack Developer | Rendered performance/resource inventory on sitemap routes | Review largest resources, document timings, image dimensions, alt attributes and lazy-loading candidates; controlled Lighthouse/Core Web Vitals measurement remains separate |
| CI failures, flaky tests, console or runtime errors | Debugger | All roles | Read CI on the exact PR head SHA; classify each failure | Re-run the failed check and its regression group after the fix |
| Deployment config, PHP compatibility, secrets, routing | Full-Stack Developer | Debugger | Full CI and full browser regression on exact PR head | Staging smoke, config checklist, owner-approved release; never deploy from this skill |

## Verdict vocabulary

Role verdicts: **GREEN**, **N/A-GREEN**, **YELLOW**, **RED**. Definitions are in the skill.

Check status, used for each command or CI run:

- **PASS**: output or CI evidence confirms the check succeeded on the stated revision.
- **FAIL**: the check ran and reported a failure that requires diagnosis.
- **BLOCKED**: the check could not run because of a concrete environment or access limitation.
- **NOT RUN**: the check was not selected or was deliberately deferred; give the reason.

## Current automated coverage

- PHP syntax lint and strict image-reference checks run in CI on PHP 7.4 and 8.2. PHP 7.4 is a compatibility signal, not proof of the production host version.
- Playwright route smoke, responsive overflow checks and the mobile navigation test run through the browser workflow on four viewport projects. Failures keep screenshots and traces as CI artifacts.
- Rendered technical SEO regression checks cover sitemap routes in the desktop-1280 project. The rendered SEO inventory records metadata, robots meta, internal/external link destinations and JSON-LD types and reports candidate findings in CI logs and JSON; candidates require human review.
- The Phase 5 performance baseline records local-PHP/Chromium route timing estimates, document/resource transfer-size data when exposed by the browser, and rendered image attributes for sitemap routes. The priority-image regression requires intrinsic width/height. The deduplicated image payload audit maps observed image resources to every sitemap route and records format, natural dimensions, loading/fetch priority, viewport visibility, and available transfer sizes. The page-layout inventory records rendered heading hierarchy, section/layout signals, image usage, forms, navigation and CTA destinations across sitemap routes. These are structural/local lab observations, not a visual-quality verdict, production crawl, field Core Web Vitals data, or enforced byte budgets.
- Accessibility diagnostics are source-level warnings from `scripts/check-site.php`; confirm them against rendered output before changing templates.
- CI retries failed Playwright tests once, so a retried pass can hide a flaky test. The Debugger checks for this.

## Not automated yet

These rely on role judgment or separate controlled/manual checks:

- SEO copy quality: uniqueness, intent and usefulness of titles and descriptions.
- Full end-to-end mail/CAPTCHA delivery and target-host security behavior remain unautomated. CI does cover form validation, CSRF/consent/honeypot checks, lead storage/rate limits, configuration diagnostics and non-delivering analytics semantics.
- Live GA4 receipt, Realtime/DebugView validation, and custom-dimension setup (event wiring and data-layer routing are covered by Browser E2E).
- Production performance, Lighthouse scoring and field Core Web Vitals (LCP, INP and CLS).
- Visual correctness, physical-device coverage, contrast and screen-reader quality.
- Current hotel facts, and cPanel-specific configuration and PHP version.
