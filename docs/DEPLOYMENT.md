# Deployment and staging runbook

Production target: `https://www.hotelgarhjaisal.com`, hosted on cPanel. Repository workflows create and validate artifacts; they do not deploy them.

## Prepare a reviewed package

1. Require every applicable exact-head CI job: Site Checks (PHP 7.4 and 8.2), Browser E2E/screenshots, package preflight, responsive-image review and non-deploying Staging QA.
2. Open the deployment-package workflow for the merged `main` SHA and download the ZIP, manifest and `.sha256` checksum.
3. Verify `sha256sum -c <package-name>.sha256`; confirm the manifest commit matches the approved SHA.
4. Inspect the manifest. The package contains tracked runtime PHP, `.htaccess`, sitemap/robots, CSS/JS, approved images and shared includes. It excludes Git history, docs, tests, scripts, workflows and server-only `include/site-config.local.php`.
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
