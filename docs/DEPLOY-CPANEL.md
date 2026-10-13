# cPanel deployment handoff

This site is plain PHP and has no frontend build step. This document is a release procedure, not permission to deploy. The repository owner decides when to upload or merge to production.

## Before staging

1. Confirm the cPanel host's PHP version, Apache version, and whether `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_expires`, and `mod_authz_core` are available and allowed by host policy.
2. Take a restorable backup of the site files and current `.htaccess`. Record the current canonical host/redirect behavior before replacing rules.
3. Confirm that `include/site-config.local.php` exists on the server, remains untracked, and contains the real enquiry-recipient addresses and reCAPTCHA secret for the host. Do not copy `include/site-config.example.php` over it.
4. Use a staging subdomain with a certificate covering that host. Confirm the configured `site_url` and reCAPTCHA key/allowed host match staging.
5. From a full Git checkout, run:

   ```bash
   php -v
   find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
   php scripts/check-site.php --strict-images
   php scripts/generate-sitemap.php --check
   php scripts/check-experience-publication.php
   php scripts/check-launch-hardening.php
   npm install --no-save --package-lock=false @playwright/test@1.55.0
   npx playwright install chromium
   npm run test:e2e
   ```

   `scripts/generate-sitemap.php` needs a full Git history to derive `lastmod`; run it in a clone with history and commit the regenerated sitemap. The deployed runtime does not need Git.

## Staging deployment

- Prefer cPanel Git Version Control only if the owner has confirmed it is configured for this repository and knows the target directory and deploy command. Otherwise upload the reviewed files with File Manager/SFTP.
- Preserve `include/site-config.local.php`, uploaded media, and any server-only configuration. Do not upload `.git`, `node_modules`, `artifacts/`, test output, or local environment files.
- Upload the reviewed `.htaccess` and code together, then verify the staging host immediately. If a request returns HTTP 500, restore the previous `.htaccess` first and inspect the host's error log.
- The .htaccess file documents its rollback path. It does not enable HSTS; only enable HSTS after the owner confirms certificate coverage and HTTPS behavior for all affected hostnames/subdomains.
- Do not treat a green GitHub run as proof that the production host supports Apache directives. CI runs the PHP app with PHP's built-in server, which does not execute `.htaccess`.

## Production release gate

Production changes require owner approval after the staging checklist is signed off. Merge the reviewed PR, verify the merged commit is green, deploy using the owner-approved process, and repeat critical staging checks against production without sending test enquiries to guests.

Create the `v1.0.0` release tag only after the owner has approved the merged commit and staging/release gate. Do not tag an unmerged PR head or deploy from this document.
