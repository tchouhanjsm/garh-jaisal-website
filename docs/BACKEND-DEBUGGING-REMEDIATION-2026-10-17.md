# Backend Debugging Remediation — 2026-10-17

Repository: `tchouhanjsm/garh-jaisal-website`

Delivery target: existing PR #91 only. No new PR was created; the PR has not been merged and no production deployment occurred.

## Implemented in code

| Audit finding | Remediation | State / release gate |
|---|---|---|
| Rooms H1 was 32px at 768px against the 33px visual contract; the appended Rooms rules were also disabled by an unterminated CSS comment | Closed the comment, activated the compact hero layout and raised the 761–1000px title floor | Corrected in source; exact-head Browser E2E must pass |
| Sticky header failed because `.nav-wrap` was sticky inside a static `.site-header` | Outer `header.site-header` is sticky; child nav uses normal relative positioning; new test covers Home, Rooms, Restaurant and Contact at 390px/1280px | Code/test added; exact-head E2E required |
| Restaurant table form was hidden and had no half-hour slots, note, log or consent | Form is visible; time uses 30-minute steps plus server validation; note (500 chars max), response-purpose consent, request reference and unconfirmed-booking copy added | Implemented; owner schedule still required |
| Valid enquiries could be lost when `mail()` failed; no server-side lead record or non-session abuse control | Added `include/lead-storage.php`: private JSONL records outside document root, request reference, file locking, restrictive permissions where supported, IP/phone hashes and per-source daily limits. Contact and restaurant flows store before mail notification | Implemented in source; private storage and mail require cPanel staging verification |
| Live menu prices were shown while owner confirmation remained pending | Separated menu to `include/menu-data.php`; public price output stays “Price on request” while `owner_confirmed` is false | Implemented; owner must confirm actual items/prices |
| Direct include access, duplicate homepage URL, robot directives and deploy bloat | Added `include/.htaccess` deny, `/index.php` 301, AVIF/font expiry rules, removed conflicting `Disallow` entries for `/tariff.php` and `/thank-you.php`, and excluded five known obsolete/documentation-only files from the deployment ZIP | Source and package tests added; verify redirects/403/cache on external cPanel staging |
| Large Rooms hero JPEG and first-pass metadata warnings | Rooms hero now uses its already-available optimized WebP; adjusted catalogue/desert-safari titles, Pack/Car Tours descriptions and Gallery description | Partial only; broad responsive image work and remaining metadata need follow-up |

## Files and tests added/updated

- `include/lead-storage.php`, `include/menu-data.php`, `include/.htaccess`
- `tests/php/lead-storage-validation.php`, `tests/test_deploy_package.py`, and `.github/workflows/ci.yml`
- `tests/e2e/sticky-header.spec.js`, `tests/e2e/restaurant-reservation-readiness.spec.js`, `tests/e2e/restaurant-form-validation.spec.js`
- `restaurant.php`, `mail.php`, `contact.php`, `thank-you.php`, `room.php`, `include/site-config.php`, `.htaccess`, `robots.txt`, and deployment package selection
- `docs/PROJECT-LOG.md`, `docs/DEPLOY-CPANEL.md`, `docs/OWNER-FACTS.md`

## Remaining items — explicitly not claimed done

1. Responsive image variants and `srcset`/`sizes` across Home, Gallery, Blog, Catalogue, About, Pack and Swiss tent remain open. Existing optimized files are unevenly sized; no generated binary derivative is claimed here.
2. The original audit found around 80 candidates by filename-only reference scanning. This is a heuristic, not deletion authority. Only five clear stale/documentation-only files are excluded from the deployment package; no photo masters were deleted.
3. Actual restaurant opening hours, closed days, last seating, same-day cut-off, maximum online party size and current menu prices require owner input. Defaults do not infer these facts.
4. Privacy wording/data controller, permitted staff access, backup and retention policy for the private lead log require owner approval.
5. Authenticated SMTP/SPF/DKIM, actual recipient confirmation, real owner email and guest acknowledgement receipt, and permission checks on cPanel require external staging.
6. `/index.php` redirect, `/include/*.php` 403, robots visibility and AVIF/font caching have source changes but need real Apache/cPanel verification. PHP's built-in CI server does not execute `.htaccess`.
7. Additional metadata/schema/content warnings from the audit are still open; do not invent opening hours, prices, ratings, policies, route inclusions or guide availability to close them.
8. Self-hosted fonts remain deliberately deferred pending license and byte-size review.
9. Refresh workflows on the final PR head before merge; previous-head pass results are not current-head evidence.

## Second metadata/schema pass

- Corrected the remaining audited title/description length warnings in shared experience/editorial registries and on Blog, guided-tour hub and restaurant pages.
- Retyped `book-rooms-in-jaisalmer.php` from `Article` to `WebPage`; added a visible breadcrumb and matching structured breadcrumb. Canonical/schema URLs use the site URL from configuration.
- The sitemap generator now considers shared experience/editorial registries when deriving `lastmod`, as well as the corresponding PHP route file. The committed sitemap reflects those source-file dates.
- This closes the report's listed metadata and guide-schema gaps; responsive image-weight remediation remains partial.
