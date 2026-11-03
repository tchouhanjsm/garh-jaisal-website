# Deployment and staging runbook

Production target: `https://www.hotelgarhjaisal.com`, hosted on cPanel. Repository workflows create and validate artifacts; they do not deploy them.

## Prepare a reviewed package

1. Require every applicable exact-head CI job: Site Checks (PHP 7.4 and 8.2), Browser E2E/screenshots, package preflight, responsive-image review and non-deploying Staging QA.
2. Open the deployment-package workflow for the merged `main` SHA and download the ZIP, manifest and `.sha256` checksum.
3. Verify `sha256sum -c <package-name>.sha256`; confirm the manifest commit matches the approved SHA.
4. Inspect the manifest. The package contains tracked runtime PHP, the Sveltia `/admin/` dashboard and its non-secret repository configuration, `.htaccess`, sitemap/robots, CSS/JS, approved images and shared includes. It excludes Git history, docs, tests, scripts, workflows and server-only `include/site-config.local.php`.
5. Preserve the existing server-side `include/site-config.local.php`. Never replace it with an example file or copy secrets into Git.

## Staging workflow (manual owner action)

1. Take a restorable backup before extracting anything.
2. Configure staging-only `site-config.local.php` with its HTTPS origin and correct CAPTCHA settings. Shared page-head should force `noindex,follow` off production.
3. Upload/extract only the reviewed package into the staging document root. Do not use production credentials in automation.
4. Run the GitHub Staging QA workflow against the exact staging origin and 40-character SHA represented by the package. Download its route report and screenshots.
5. Complete the manual checks below. Automated QA does not prove mail/CAPTCHA delivery, owner-approved business facts or visual quality.

## Manual acceptance

- [ ] Verify canonical host/HTTPS redirects, no redirect loops or query loss.
- [ ] Verify missing paths return a real 404, directory listing is disabled and config/hidden files are not exposed.
- [ ] Check security headers, compression and asset cache headers.
- [ ] Open published sitemap routes at 390px and 1280px; spot-check 320px, 768px and 1440px.
- [ ] Inspect image crops, alt text, layout, keyboard/focus, sticky controls and browser console.
- [ ] Compare Book Now/room rates/Call/WhatsApp links with approved configuration.
- [ ] Test contact validation, CSRF/honeypot, CAPTCHA error, config/mail failure with owner-controlled staging recipients. Send only an approved staging test enquiry.
- [ ] Confirm metadata/canonicals/schema/sitemap and draft/policy publication state.
- [ ] Confirm production PHP version and Apache/.htaccess behavior with the host.

## Rollback and release approval

Keep the pre-release backup and identify the previous package SHA. If a release fails, restore that backup/package and verify routing, assets, contact channels and booking link. A Git revert may be appropriate for code regression; it does not restore server-side configuration by itself.

The owner reviews staging evidence and explicitly authorizes production deployment. A merged PR or green workflow is not deployment approval.


## Sveltia Gallery publishing

The Sveltia dashboard at `/admin/` writes Gallery JSON and newly uploaded/replacement media to `cms-media`. The 36 original Gallery image URLs remain unchanged after migration. Responsive-image candidates are discovered from `content/gallery/*.json`, including Draft and Hidden records so their assets are packageable if later published. After a CMS review PR is merged to `main`, the Sveltia branch-sync workflow merges the new main baseline into `cms-media`. Neither workflow uploads to the server; continue using the owner-reviewed cPanel package and manual staging/release process.


The owner media report at /admin/media-library.php is a read-only runtime route included with the reviewed admin/ and include/ directories. It does not modify media or content. It is marked noindex/no-store, but it is not an authentication gate. No new deployment action is triggered by opening the report.


## Owner Media Library access and page-media approval

Before enabling the Media Library report on cPanel, add media_admin_auth to the existing untracked include/site-config.local.php. Generate the password hash on a trusted machine with PHP password_hash() and use HTTPS only. Test both /admin/media-library.php and /admin/media-library.php?format=json in a private browser window: both must deny anonymous access, then return the report only after valid owner credentials. If the local credentials are absent, the route must return 503 without running the inventory.

For page image slots, configure the server-only media_approvers allowlist and a random media_approval_hmac_key. The same key must be available to the trusted local signing process and deployed PHP runtime. New or changed page-media entries remain on their reviewed PHP fallback until a trusted reviewer supplies evidence and runs scripts/sign-page-media-approval.php. Never deploy the HMAC key, password hash or local configuration file in the package. Verify that changing an image, path, alt text or caption invalidates its approval signature.

These protections are implemented in application code, but actual cPanel authentication and environment/local-config setup still require owner verification on staging. Do not claim the production host is protected until both endpoints have been tested there.
