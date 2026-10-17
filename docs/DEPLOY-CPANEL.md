# cPanel deployment handoff

This site is plain PHP and has no frontend build step. This document is a release procedure, not permission to deploy. The repository owner decides when to upload or merge to production.

## Repeatable deployment package

The **cPanel deployment package** workflow validates file selection on pull requests. It builds a ZIP artifact only from a successful push to `main` (or a manual workflow dispatch on `main`); it never uploads files to a server.

1. Open the successful [cPanel deployment package workflow](https://github.com/tchouhanjsm/garh-jaisal-website/actions/workflows/deployment-package.yml) for the exact merged `main` SHA.
2. Download the `cpanel-deployment-package-<commit SHA>` artifact. It contains the deployment ZIP, a JSON manifest and a `.sha256` checksum file.
3. Confirm the manifest's commit matches the merged SHA approved for staging, then verify the archive checksum with `sha256sum -c <package-name>.sha256`.
4. Inspect the manifest's file list and published-route count. The ZIP contains tracked runtime PHP pages, `.htaccess`, sitemap, robots rules, CSS, JavaScript, images and runtime includes. It excludes repository metadata, docs, tests, scripts, workflow files, package/test configuration, `include/site-config.example.php` and the server-only `include/site-config.local.php`.
5. Preserve the existing server-only `include/site-config.local.php` and uploaded/server-managed files. Extract the reviewed archive into a **staging** document root only after making a restorable backup. Do not overwrite local server configuration with any package or example file.

The package script uses only Python's standard library, selects Git-tracked runtime files, rejects a tracked `include/site-config.local.php`, validates sitemap-route coverage and emits a SHA-256 manifest. It refuses to create an archive from a dirty tracked worktree or non-main branch. This is packaging and validation—not a website build step, deployment action or approval to release.

## Before staging

1. Confirm the cPanel host's PHP version, Apache version, and whether `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_expires`, and `mod_authz_core` are available and allowed by host policy.
2. Take a restorable backup of the site files and current `.htaccess`. Record the current canonical host/redirect behavior before replacing rules.
3. Confirm that `include/site-config.local.php` exists on the server, remains untracked, and contains the real enquiry-recipient addresses and reCAPTCHA secret for the host. Do not copy `include/site-config.example.php` over it.
4. Use a staging subdomain with a certificate covering that host. Confirm the configured `site_url` and reCAPTCHA key/allowed host match staging.
5. On a full checkout that can read the server-only config, run `php scripts/check-runtime-config.php --strict`. This reports configuration readiness and the valid-recipient count without printing secret values or email addresses. If PHP CLI is unavailable on the host, run the equivalent preflight in a secure staging environment that loads that host's actual configuration; never paste secrets into CI logs or support tickets.
5. From a full Git checkout, run:

   ```bash
   php -v
   find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
   php scripts/check-site.php --strict-images
   php scripts/generate-sitemap.php --check
   php scripts/check-experience-publication.php
   php scripts/check-launch-hardening.php
   php scripts/check-runtime-config.php
   php tests/php/runtime-config-validation.php
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

## Backend audit remediation follow-up

PR #91 adds outer-header stickiness and a browser regression for Home, Rooms, Restaurant and Contact at mobile and desktop widths. Verify the new exact-head Browser E2E result and inspect screenshots before merge.

Private lead storage, restaurant request settings and package hygiene are also being addressed inside the same PR. Do not treat the non-deploying Staging QA workflow as cPanel evidence. Before release, verify redirect/403/cache behaviour, writable private storage outside the document root, approved operating hours/cutoff values, live menu approval, and a dedicated staging email test.
