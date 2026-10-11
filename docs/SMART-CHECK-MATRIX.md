# Smart Check Matrix

This matrix routes changes to the appropriate deterministic checks. It is a selection guide, not a claim that every check is currently implemented.

| Check type | Triggering change surface | Minimum checks | Broader regression / evidence |
|---|---|---|---|
| PHP / backend | `*.php`, `include/**`, request handling, routing | PHP lint on supported CI versions; `php scripts/check-site.php --strict-images` | Route smoke tests; relevant form or page behavior |
| Frontend / responsive | CSS, navigation, shared header/footer, layout, room cards | Playwright smoke; no horizontal overflow | 320×700, 390×844, 1280×800, 1440×900 projects; inspect screenshot artifacts |
| Technical SEO | titles, descriptions, canonicals, H1, sitemap, JSON-LD, redirects | Rendered SEO E2E checks over sitemap routes | Validate actual HTTP response and rendered HTML; do not rely on source heuristics alone |
| Content / local SEO | landing pages, area guides, headings, internal links, location intent | Fact-check against approved project content; heading/link/metadata checks | Confirm existing URLs and canonical targets; never fabricate hotel facts or location claims |
| Accessibility | forms, controls, icons, images, keyboard behavior, semantic HTML | Static audit plus rendered checks where available | Keyboard/focus checks, labels/alt text, mobile layouts; manual review for contrast and screen-reader behavior |
| Security / privacy | forms, mail, redirects, escaping, configuration, scripts | PHP lint; inspect validation/escaping and secret boundaries | Safe negative tests; no real emails, live enquiries, or secret output |
| Booking / conversion | booking URLs, call/WhatsApp links, thank-you page, analytics | Assert expected destination and event wiring without sending a live enquiry | Owner-confirmed GA4 property/event plan; test consent/privacy behavior if applicable |
| Images / assets | image paths, formats, dimensions, mapping | Strict image-reference check; verify file exists | Visual room-to-image matching; compare file size/dimensions and responsive rendering |
| Performance | CSS/JS/image payload, third-party scripts, font loading | Static payload and image-size inventory where available | Lighthouse/Core Web Vitals on a controlled environment; distinguish lab estimates from field data |
| Release / cPanel | deployment config, PHP compatibility, secrets, routing | Full CI and full browser regression on exact PR head | Staging smoke, config checklist, owner-approved release; never deploy from this skill |

## Status vocabulary

- **PASS** — output or CI evidence confirms the check succeeded on the stated revision.
- **FAIL** — the check ran and reported a failure that requires diagnosis.
- **BLOCKED** — the check could not run because of a concrete environment or access limitation.
- **NOT RUN** — the check was not selected or was deliberately deferred; give the reason.

## Current automated coverage

- PHP syntax lint and strict image-reference checks run in CI on PHP 7.4 and 8.2. PHP 7.4 is a compatibility signal, not proof of the production host version.
- Playwright route smoke, responsive overflow checks and selected screenshot evidence run through the browser workflow.
- Rendered technical SEO regression checks cover sitemap routes in the desktop-1280 project.
- Accessibility diagnostics include source-level warnings; confirm warnings against rendered output before changing templates.
- Manual review is still needed for visual correctness, physical-device coverage, contrast/screen-reader quality, current hotel facts, and cPanel-specific configuration.
