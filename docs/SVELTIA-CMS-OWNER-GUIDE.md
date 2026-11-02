# Sveltia CMS — Owner guide

## What the media manager does

Open `/admin/` on the deployed website. Sveltia CMS is the owner-facing dashboard for the Gallery page. It edits repository content and media; it does not use a separate database and it does not publish to the live server by itself.

The four collections are **Hotel & fort**, **Rooms**, **Desert experiences** and **Restaurant & rooftop**. The original 36 curated gallery photographs are now individual JSON entries in `content/gallery/`.

**The migration preserves existing image URLs.** The 36 entries still reference their existing `img/...` files, so the initial migration does not change the Gallery page's image paths or overwrite photos that may be reused on other pages. Existing local Content Studio Gallery records in the same folder remain supported; records created before the Sveltia order field are placed after explicitly ordered photos. When you replace one of those photos, the new upload is stored under `img/gallery/`; the old original file remains in the repository.

## Sign-in and access

Sveltia's GitHub backend requires a GitHub account with Write, Maintain or Admin access to this repository.

Before first sign-in, verify in GitHub repository settings that `main` requires pull requests and the required checks. The `cms-media` branch setting routes CMS writes but is not an access-control boundary: a repository-scoped Contents-write token can write to any branch GitHub permits. Keep `main` protected and `cms-media` writable.

The initial setup is owner-only:

1. Wait until this integration is merged and the **Sveltia CMS branch sync** workflow has completed successfully.
2. Open `/admin/` on the deployed site.
3. Choose **Sign in with Token**.
4. Use a GitHub fine-grained personal access token restricted to this repository.
5. Grant **Contents: Read and write**. Metadata read is added automatically.
6. Paste the token only into Sveltia's sign-in prompt in the owner's browser.

Sveltia stores the token in that browser's local storage. Use a trusted, owner-controlled browser profile. Never put the token in this repository, screenshots, shared documents or support chats. Sign out and revoke the token if the browser/device is lost or the token is exposed.

The sign-in screen is publicly reachable; repository writes still require an authorised GitHub token. The admin route is marked noindex, but noindex is not authentication.

## Update an existing photo

1. Open **Gallery photos** and choose the entry you want to change.
2. The Image file field shows the current photo. The migrated entries initially reference the same existing `img/...` path as the live site.
3. To replace it, upload or choose a new image in the Image file field. New/replacement uploads are stored under `img/gallery/`, not over the old shared file.
4. Review the accessible description (alt text) and Display shape.
5. Keep the status as **Draft** while preparing the change.
6. Save.

The previous source file is intentionally retained. Do not delete it from the repository: another page may still use it.

## Add a photo

1. In **Gallery photos**, create a new entry.
2. Give it a unique ID using lowercase letters, numbers and hyphens, for example `hotel-rooftop-evening-01`. Do not change an ID after creation.
3. Choose the category.
4. Set **Display order** to the next unused positive number in that category. Lower numbers appear first. The migrated values currently run from 1–9 for Hotel, 1–12 for Rooms, 1–10 for Desert and 1–5 for Restaurant. The default value 100 places a new item after the original set.
5. Upload/select the photograph in Image file. New uploads are saved under `img/gallery/`.
6. Enter meaningful alt text and choose the display shape.
7. Leave the status as **Draft**, then save.

New entries are not visible on the public Gallery until their status is changed to **Live**, the change is reviewed and merged, and the reviewed site package is deployed. The Sveltia collection disables entry deletion; use **Hidden** to remove a photo from public display while retaining its record.

## Reorder and organise

Change **Display order** to a unique positive number within the selected category. Smaller numbers appear first. This collection uses a numeric order field rather than drag-to-reorder.

The Asset Library manages media under `img/gallery/`. The 36 migrated original files remain at their old paths outside this managed folder; their image previews are available from their Gallery entries. New and replacement uploads appear in the Asset Library.

**Do not rename or delete an asset that is referenced by a gallery entry.** Replace it through the Image file field instead. Keep an entry Hidden rather than deleting its media.

## Image handling

Sveltia's current configuration provides browser-side raster processing:

- Maximum source upload: 15 MiB.
- New/replacement raster images are converted to WebP where browser encoding is available.
- Maximum dimensions: 1600 × 1600 pixels, without upscaling.
- Quality target: 84.
- Raster EXIF metadata, including GPS metadata, is removed.

Use JPG/JPEG, PNG or WebP photographs. Avoid animated images and SVGs for this Gallery workflow. Inspect the result at full size before submitting, especially fine sandstone details and low-light room photographs.

The repository's responsive-image pipeline automatically discovers image paths from all Gallery JSON records. Responsive derivatives and original fallbacks are validated by the existing image and deployment-package checks.

## Review and publish — no direct production edits

Sveltia writes to the dedicated `cms-media` branch, not `main`.

1. Save your changes in Sveltia. The CMS commits them to `cms-media`.
2. Open the [CMS changes comparison](https://github.com/tchouhanjsm/garh-jaisal-website/compare/main...cms-media?expand=1).
3. Create a pull request from `cms-media` into `main`.
4. Review every changed JSON record and uploaded image. Check category, order, status, image path, alt text, file size and the complete file diff.
5. Wait for all repository checks to pass: PHP Site Checks, gallery contracts, responsive-image validation, Browser E2E, staging QA and cPanel package preflight. Fix failures before merging.
6. Merge only after owner approval. Do not merge directly from the CMS branch without pull-request checks.
7. After the merge, the **Sveltia CMS branch sync** workflow merges the new `main` baseline into `cms-media`. Wait for that workflow to pass before the next CMS editing session. It never force-pushes; a conflict fails safely and must be resolved before further editing.
8. Use the existing cPanel deployment-package and staging procedure for a production release.

A CMS save, GitHub merge or branch-sync run does **not** upload files to cPanel. Production deployment remains a manual owner-controlled release.

## Rollback and recovery

For a bad published change, revert the relevant pull request or restore the previous JSON/image from Git history in a new pull request. Do not edit production files directly in cPanel as the normal rollback path.

If branch synchronisation fails, do not continue editing on a stale branch. Open the workflow run, resolve any merge conflict on `cms-media` with the repository's latest `main` baseline, and rerun the sync. Never force-push the CMS branch over owner changes.

## Current boundaries

- This collection manages the public Gallery page. It does not yet manage Home hero, individual room-page, dining-page or experience-page image slots.
- The 36 migrated entries preserve their original source paths until each owner replaces the image.
- Staff OAuth onboarding and automatic pull-request creation are not included. The owner creates the review PR using the compare link.
- Crop changes the Gallery display treatment; it does not edit image pixels.
- No CMS workflow deploys to production.
