## Purpose
<!-- State the concrete user/business outcome. This should be a meaningful vertical slice, not a one-line cosmetic tweak. -->

## User-visible scope
- 
- 

## Explicitly not changed
- Existing public URLs/canonicals and booking engine destination/token remain unchanged unless the PR lists approved redirects.
- No invented rates, availability, reviews, operational promises or guest-facing business facts.
- No production deployment or server-local configuration changes.

## Review pass 1 — product behaviour and UI/UX
- [ ] Review mobile at 320px and 390px
- [ ] Review tablet at 768px
- [ ] Review desktop at 1280px and 1440px
- [ ] Inspect screenshot/page-sheet artifacts at full size
- [ ] Verify booking, Call, WhatsApp, room and contextual enquiry paths
- [ ] No fixed/sticky action obscures content or controls

## Review pass 2 — accessibility, SEO and recovery
- [ ] Keyboard and visible-focus review; reduced-motion behavior
- [ ] Heading hierarchy, meaningful alt text, contrast and tap targets
- [ ] Internal links/local assets, metadata, canonicals, schema and sitemap checked
- [ ] Error, empty, validation and no-JavaScript recovery paths considered
- [ ] No secrets, guest data or local configuration included

## Evidence
- [ ] PHP lint and `php scripts/check-site.php --strict-images`
- [ ] `php scripts/generate-sitemap.php --check`
- [ ] Required exact-head CI complete (Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA)
- [ ] Name the routes and viewport widths visually inspected
- [ ] State all checks not run; do not treat cancelled workflows as passes

## Owner decisions / facts to confirm
- None / list explicitly

## Residual risks
- 

## Owner decision
- [ ] Ready for owner review
- [ ] Changes needed
