# Staging QA checklist — Garh Jaisal Haveli

Run this checklist on staging after deploying a reviewed merge. Record the URL, commit SHA, tester, date, results and screenshots before production approval.

## Routing and server behavior

- [ ] Canonical origin is `https://www.hotelgarhjaisal.com` in production; staging uses its own approved host and configuration.
- [ ] Test HTTP, non-`www` and `www` entry points on staging; confirm redirects reach the configured canonical origin without loops or query-string loss.
- [ ] Check a non-existent path returns HTTP 404, renders the branded `404.php`, and emits `noindex,follow`.
- [ ] Confirm `.well-known/acme-challenge/` is not blocked or redirected away from the host's certificate challenge handler.
- [ ] Inspect response headers for `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` and `Permissions-Policy`; confirm gzip and asset cache headers behave as expected.
- [ ] Check directory listing is disabled. Verify a request for `include/site-config.local.php` and hidden files is denied; do not expose error logs or backup files.

## Page, SEO and assets

- [ ] Open all 26 sitemap routes on mobile and desktop; record non-2xx responses, console errors, overflow and missing images.
- [ ] Confirm every rendered image has an `alt` attribute. Empty alt is reserved for decorative imagery or icons whose parent link has an accessible name.
- [ ] Inspect `sitemap.xml`: every published URL has a real `lastmod` from Git history, the public set matches publication status, and policy drafts are not listed.
- [ ] Check titles, meta descriptions, canonicals, H1, JSON-LD and both approved GA4 IDs remain intact.
- [ ] Review Gallery, Blog, Jaisalmer Fort Guide, Contact, Directions and the new 404 at 390px and 1280px; test 320px, 1440px and keyboard navigation as well.
- [ ] Inspect the responsive image review artifact at full size before authorizing any resized variant to replace a current source. Original photo files and room mappings must remain intact.

## Booking and enquiry recovery

- [ ] Compare Book Now, room availability, Call and WhatsApp URLs against the approved site configuration.
- [ ] Test Contact form validation, CSRF rejection, honeypot rejection, CAPTCHA failure, configuration failure and mail failure using a staging recipient controlled by the owner.
- [ ] Submit exactly one owner-approved staging test enquiry only after staging mail/CAPTCHA are configured; confirm it reaches the intended test mailbox and thank-you confirmation appears only on successful mail.
- [ ] Confirm failure states visibly preserve the phone and WhatsApp alternatives.
- [ ] Do not send tests to a live guest address or submit a fake live enquiry in production.

## Approval and rollback

- [ ] Save the before/after `.htaccess`, backup location and rollback instructions.
- [ ] Confirm the cPanel PHP version and Apache module/override behavior with the host; GitHub's PHP built-in-server tests do not validate Apache rules.
- [ ] Confirm policy pages remain noindex/off-nav until the owner approves their wording.
- [ ] Owner signs off staging evidence, then approves production deployment and release tagging.
