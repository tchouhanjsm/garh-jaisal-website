# Sveltia CMS — Owner guide

## What this provides

The hosted media manager is available at `/admin/` after this pull request is merged and the reviewed cPanel package is deployed. It uses Sveltia CMS with the existing GitHub repository as its content and media store. There is no separate database or paid CMS subscription.

The first release supports **new owner-managed gallery items** in four categories: Hotel & fort, Rooms, Desert experiences, and Restaurant & rooftop. The existing PHP gallery renderer already reads valid `content/gallery/*.json` records and only displays records whose status is `live`.

**Important current limitation:** the original 36 curated photographs are still embedded in `include/gallery-data.php` and remain at their existing image paths. They are not yet individual Sveltia entries and are not yet inside the CMS Asset Library folder. This first slice does not yet let the owner replace those 36 legacy photographs. A separate migration milestone must copy/register those assets and move the curated metadata into the same managed model without breaking image references used elsewhere on the site.

## Sign-in and access

Sveltia's GitHub backend requires a GitHub account with Write, Maintain or Admin access to this repository.

For the owner-only initial setup:

1. Open `/admin/` on the deployed site.
2. Choose **Sign in with Token** in the Sveltia sign-in screen.
3. Use a GitHub fine-grained personal access token restricted to this repository only.
4. Grant **Contents: Read and write**. Metadata read is added automatically by GitHub. Pull-request permission is not needed for this direct-branch setup because the owner creates the review PR in GitHub.
5. Paste the token only into Sveltia's sign-in prompt in the owner's browser.

Sveltia stores the token in that browser's local storage. Use only a trusted, owner-controlled browser profile. Do not put the token in this repository, a screenshot, a shared document, a support chat or a website configuration file. Sign out and revoke the token in GitHub if the browser/device is lost or the token is exposed.

The CMS sign-in screen is publicly reachable, but repository writes require an authorised GitHub token. The admin route is marked noindex; this is not a substitute for GitHub authentication.

## Add a gallery photo

1. Open **Gallery photos** in Sveltia.
2. Create a new entry.
3. Give it a unique ID such as `hotel-rooftop-evening-01`. Use lowercase letters, numbers and hyphens only. The ID becomes the JSON filename and should not be changed after creation.
4. Choose one of the four gallery categories.
5. Keep **Publication status** as **Draft** while preparing the entry.
6. Upload the photograph in the **Image file** field or use the Asset Library. New uploads are stored beneath `img/gallery/`.
7. Add meaningful alt text. Describe the visible subject rather than repeating the hotel's name or adding SEO keywords.
8. Add a caption if it helps a guest understand the photograph.
9. Choose the display shape: Standard, Architectural arch, Landscape or Portrait. This is a layout treatment for the website; it is not an interactive crop editor.
10. Save the entry.

The entry is stored as `content/gallery/<photo-id>.json`. New records default to Draft and therefore do not appear in the public gallery.

## Image optimisation

The CMS is configured to process supported raster uploads in the browser before committing them to Git:

- Convert raster photos to WebP where the browser supports encoding.
- Limit the maximum dimension to 1600 × 1600 pixels without upscaling.
- Use quality 84 as the starting point.
- Strip raster EXIF metadata, including GPS data.
- Reject source files larger than 15 MiB before processing.

Large panoramas and exceptionally detailed architectural photographs should still be visually reviewed after conversion. WebP conversion is not a substitute for checking the image at full size. Animated images are not a supported gallery workflow. Sveltia's Asset Library does not provide a freehand crop editor in this configuration.

## Review and publish

The CMS is configured to write to the dedicated `cms-media` branch, **not** to `main`.

1. Save content and media changes in Sveltia. They are committed to `cms-media`.
2. Open GitHub and create a pull request from `cms-media` into `main`.
3. Review every JSON change and every new or replaced image. Check the category, alt text, caption, status, image path and file size.
4. Keep new entries as Draft until the owner has reviewed them. Set a record to Live only when it is ready to appear on the public site after merge.
5. Wait for the existing repository checks to pass. Resolve any failed checks before merging.
6. Merge the PR only after owner approval. Prefer a merge commit so the CMS branch remains an ancestor of main; do not merge this CMS branch directly to production without review.
7. Use the existing cPanel deployment-package and staging procedure for a production release. A GitHub merge or CMS save does not itself upload files to cPanel.

The CMS does not automatically deploy the website and does not send changes directly to the live server.

## Replace and remove media safely

The Asset Library can manage files stored under `img/gallery/`. Use it to upload new assets or replace an asset that is already stored in that managed folder.

Before replacing an asset, inspect all references and the public result. A file may be used by more than one page or content record. Do not delete a file merely because one gallery entry is hidden or removed. Prefer setting an entry to **Hidden**, which preserves the record and its asset while removing it from the public gallery after the reviewed change is merged.

The Asset Library can rename or delete assets; those operations can affect references. Treat rename/delete as a code change and review the complete Git diff before merging.

## Rollback

For a bad gallery update, revert the relevant merged pull request or restore the previous JSON/image file from Git history in a new pull request. Do not edit production files directly in cPanel as the normal rollback path. Preserve the current server backup and follow the existing cPanel deployment guide.

## What is not included yet

- Migration of the 36 hard-coded curated photographs into Sveltia.
- Owner-managed editing of Home hero images, individual room-page images, dining-page images or experience-page assets.
- Automatic pull-request creation from the CMS branch.
- OAuth sign-in for non-technical staff. The first setup is owner-only token authentication.
- A visual crop editor or automatic multiple responsive derivatives generated by Sveltia.

These are separate milestones. Keep the public PHP renderer, URL structure, booking journeys and business facts unchanged while this gallery workflow is proven.
