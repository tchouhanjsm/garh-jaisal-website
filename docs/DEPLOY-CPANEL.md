# cPanel deployment handoff

This site is plain PHP and has no frontend build step. This document is a release procedure, not permission to deploy. The repository owner decides when to upload or merge to production.

## Repeatable deployment package

The **cPanel deployment package** workflow validates file selection on pull requests. It builds a ZIP artifact only from a successful push to `main` (or a manual workflow dispatch on `main`); it never uploads files to a server.

1. Open the successful [cPanel deployment package workflow](https://github.com/tchouhanjsm/garh-jaisal-website/actions/workflows/deployment-package.yml) for the exact merged `main` SHA.
2. Download the `cpanel-deployment-package-<commit SHA>` artifact. It contains the deployment ZIP, a JSON manifest and a `.sha256` checksum file. The ZIP includes a generated `release-info.txt` containing only `GARH_JAISAL_RELEASE_SHA=<full commit SHA>`; it is generated at package time and is not a tracked file or secret.
3. Confirm the manifest's commit matches the merged SHA approved for staging, then verify the archive checksum with `sha256sum -c <package-name>.sha256`.
4. Inspect the manifest's file list and published-route count. The ZIP contains tracked runtime PHP pages, `.htaccess`, sitemap, robots rules, CSS, JavaScript, images and runtime includes. It excludes repository metadata, docs, tests, scripts, workflow files, package/test configuration, `include/site-config.example.php` and the server-only `include/site-config.local.php`.
5. Preserve the existing server-only `include/site-config.local.php` and uploaded/server-managed files. Extract the reviewed archive into a **staging** document root only after making a restorable backup. Do not overwrite local server configuration with any package or example file.

The package builder selects Git-tracked runtime files plus manifest-approved WebP candidates generated under ignored `img/responsive/`. Install the pinned Pillow version and run `python scripts/build-responsive-image-assets.py` before package preflight/build; the ZIP includes generated responsive variants and its SHA-256 manifest enumerates them. The generator preserves originals, never upscales or crops, strips EXIF/XMP metadata from derivatives, preserves color profiles, and writes a human-review sheet/report under ignored `artifacts/`. The builder rejects a tracked `include/site-config.local.php`, validates sitemap-route coverage and refuses to create an archive from a dirty tracked worktree or non-main branch. This is packaging and validation—not a deployment action or approval to release.

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
   python -m pip install Pillow==11.3.0
   python scripts/build-responsive-image-assets.py
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

## Responsive image build inputs

Responsive WebP files in `img/responsive/` are intentionally ignored generated assets, not committed binaries. The browser workflow builds them before E2E; the cPanel package workflow builds the same deterministic variants before package validation and archive creation. If generating locally, use Python 3.12 with `Pillow==11.3.0`, inspect `artifacts/responsive-image-assets/responsive-image-delivery-review.jpg`, and keep original image masters unchanged. The deployment ZIP must contain the manifest-approved generated variants selected by the package builder—not the build script, review reports, or arbitrary files copied into the generated directory.

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

## Private lead storage and restaurant request configuration

Public contact requests and restaurant table enquiries are recorded in `guest-enquiries.jsonl` outside the web document root before outbound email is attempted. The default directory is `gj-private-data` beside the document root. If the PHP user cannot write there on cPanel, set `GJ_LEAD_STORAGE_DIR` to an absolute writable directory outside the document root. The code rejects a directory inside the document root.

The file uses JSON Lines, file locking, request references, restrictive permissions where supported and per-IP/per-phone daily limits. Raw IP addresses are not written into the request log; hashes are used for daily duplicate counting. The log does contain the submitted enquiry fields, so the owner must approve access, backups and a retention period and provide matching privacy-page wording.

Restaurant requests are always enquiries, not confirmed bookings. Preferred time choices use 30-minute increments. Closed days and same-day cut-off are enforced only when the owner supplies actual values in server-only `include/site-config.local.php`: `restaurant_closed_days` (weekday numbers 0=Sunday through 6=Saturday or weekday names) and `restaurant_same_day_cutoff_minutes` (non-negative integer). Empty defaults do not imply verified opening hours.

Restaurant menu records are in `include/menu-data.php`; prices remain hidden while `owner_confirmed` is false. The owner must compare dishes and prices with the live menu before enabling them.

CI adds `php tests/php/lead-storage-validation.php`. Before a release, also verify the log path/permissions, actual owner notification and guest acknowledgement delivery, `/index.php` 301, `/include/*.php` 403, robots/redirect behavior, and cache headers on a real cPanel staging host. The repository staging workflow is non-deploying and is not proof of external-host behavior.

The deployment package excludes `img/tent1.jpg`, root `restaurant.css`/`restaurant.js`, `thanx.png` and `css/owl.video.play.html`. These files remain in the repository; other image candidates are not deleted until references and owner visuals are reviewed.

## Sprint 43 — responsive image delivery handoff

The responsive variants in `img/responsive/` are generated during CI/package preparation and are intentionally absent from Git. Before uploading a locally assembled site, generate them with Python 3.12 and `Pillow==11.3.0`, then check the generated report and visual-review sheet. The cPanel package workflow performs this build and packages only manifest-approved outputs. The PHP runtime must be able to serve the WebP candidates from the generated directory; when a candidate is absent or fails the runtime type/dimension/aspect-ratio/byte checks, the helper falls back to the existing approved image. Do not delete the original source or optimized fallback assets during promotion.

CI dry runs do not verify that generated files are served correctly from an actual cPanel staging host. Verify the deployed ZIP manifest and image requests (HTTP status, content type, selected responsive candidate, and preload alignment) on staging before production. Keep owner review of the generated image sheet and screenshots as a release gate.


## Sprint 86 — staged package provenance and image delivery evidence

After extracting the reviewed archive into the owner-approved staging document root, run the owner-dispatched Staging QA workflow with the exact staging origin and the SHA in the package manifest. The workflow reads `/release-info.txt` from the host and rejects the run unless the served commit matches the exact `main` checkout SHA. The marker is sent with `Cache-Control: no-store` and `X-Robots-Tag: noindex, nofollow`, and is excluded from robots crawling.

External QA also records the selected image candidate, its rendered slot and source-pixel coverage, responsive preload parity, and read-only HTTP HEAD observations for first-viewport same-origin image URLs. It checks status, image content type and at least seven days of effective cache lifetime. These checks do not upload files, submit enquiries or authorize production deployment.


## Sprint 87–89 — merged guest journeys and package integrity

The current package candidate includes the premium homepage and Directions/arrival experience merged by PR #161. The non-deploying staging smoke matrix checks the principal guest routes at 390px and 1280px, including the three homepage booking CTAs and the Directions driver-ready note, exact map pin and arrival booking action. These assertions verify rendered markup and destinations; they do not click through to submit a booking or enquiry.

The deployment-package unit suite builds a temporary clean main Git checkout twice, verifies that the ZIP contains the release marker for its exact Git SHA, checks marker and archive hashes against the JSON manifest and .sha256 sidecar, confirms that the server-only configuration is excluded, and rejects a workflow SHA mismatch. The fixed ZIP timestamps make repeat builds of one commit comparable by SHA-256.

For external staging evidence, first obtain owner approval, upload the exact reviewed ZIP to the staging document root, and then dispatch Staging QA from the merged main SHA with the HTTPS staging origin and the full SHA from the ZIP manifest. This read-only verification checks the host-served marker and first-viewport image responses/cache headers. A green pull-request run alone does not exercise cPanel Apache rules. No automated workflow uploads to staging or production.
