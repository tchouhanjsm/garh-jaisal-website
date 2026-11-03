# Sveltia CMS — Owner guide

## What the media manager does

Open `/admin/` on the deployed website. Sveltia CMS is the owner-facing dashboard for the Gallery page. It edits repository content and media; it does not use a separate database and it does not publish to the live server by itself.

The CMS provides **Gallery photos** plus **Page image slots** for Home, Rooms, Dining and Experiences. The original 36 curated gallery photographs are individual JSON entries in `content/gallery/`; fixed page image slots are grouped in `content/page-media/`.

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

The repository's responsive-image pipeline discovers image paths from both Gallery JSON records and the four fixed Page image slot files. New slot uploads are included in responsive WebP candidate generation and cPanel package fallback validation.

## Record source, rights and review evidence

Each Gallery photo has separate fields for publication status, editorial review, source/rights evidence, human visual review and technical validation. A record marked **Live** or **Approved** is not proof that its underlying evidence is complete, and saving in Sveltia never publishes to the production server.

1. Start with **Editorial review status: Draft** and **Rights evidence status: Unverified**.
2. Record the named creator or rights holder and a stable evidence reference. Licensed images also require the original HTTPS source URL and licence name/scope. Owner-confirmed or commissioned work requires the actual permission or commission reference. Do not infer ownership from a filename, folder, website or old CMS record.
3. Open the actual image at full size. Record whether the visible subject matches the intended description and crop. Set **Matched** only after inspection; record the reviewer, date and what was checked. Use **Mismatch** when the subject or context does not match.
4. Complete technical validation only after the image has been decoded and its dimensions/path checked. Link the actual validation evidence. Technical validation does not establish rights or visual relevance.
5. Keep publication status **Draft** while evidence is incomplete. The tourism-site audit also requires descriptive alt text and a separate review decision; a CMS field alone cannot satisfy all release gates.

The safe defaults for new entries are deliberately unresolved: rights **Unverified**, visual review **Not assessed**, technical validation **Pending**, and editorial review **Draft**. These fields do not backfill the 36 existing Gallery records or change their statuses. Review existing records individually; do not bulk-mark them approved or owner-confirmed.

The reviewer/date fields are editorial attestations, not cryptographic signatures or authenticated approval authority. Keep supporting evidence in the approved owner-controlled record system and submit final changes through the reviewed `cms-media` to `main` pull-request workflow. Do not paste private credentials, tokens, personal data or confidential contract details into a public repository record.

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

## Manage Home, Rooms, Dining and Experience images

Open **Page image slots** in Sveltia. The collection contains four fixed files—Home, Rooms, Dining and Experiences. Each file contains the named image slots used by the page templates; owners can replace images and update accessible descriptions, but cannot create or delete page slots. Home includes the responsive mobile/desktop hero, feature images and lower Gallery strip. Rooms includes the Rooms hero and the primary photo for each room. Dining includes the restaurant hero. Experiences includes the hero image for each registered experience, including draft entries.

Choose or upload the replacement in **Image file**. New uploads are stored under `img/gallery/` and do not overwrite the original page image. Keep alt text descriptive and accurate. Experience hero captions may also be updated in the Caption field. Save in the `cms-media` branch, compare it with `main`, and submit the change through the normal reviewed PR and deployment process. Missing or invalid slot data falls back to the existing page image. The responsive-image builder discovers all page-slot sources, and cPanel packaging requires those source images as fallbacks.

The PHP renderer validates each source as a decodable regular file beneath `img/`; malformed paths, missing images and symlinks are ignored in favour of the existing fallback.

## Rollback and recovery

For a bad published image, open **View Git history** on its Media Library card to inspect the file's commits on `main` and identify a known-good version. Restore the previous image from that revision or revert the publishing pull request, then submit the restoration through a new reviewed pull request. Do not edit production files directly in cPanel as the normal rollback path. The dashboard provides a history link only; it does not perform a restore or change repository files.

If branch synchronisation fails, do not continue editing on a stale branch. Open the workflow run, resolve any merge conflict on `cms-media` with the repository's latest `main` baseline, and rerun the sync. Never force-push the CMS branch over owner changes.

## Current boundaries

- Gallery photos and fixed Page image slots are managed separately; changing a page slot does not alter a Gallery entry that may reference the same original image.
- The 36 migrated Gallery entries preserve their original source paths until each owner replaces the image; page-slot images likewise retain their original sources as fallbacks.
- Staff OAuth onboarding and automatic pull-request creation are not included. The owner creates the review PR using the compare link.
- Crop changes the Gallery display treatment; it does not edit image pixels.
- No CMS workflow deploys to production.


## Media Library Intelligence — usage and duplicate review

Open /admin/media-library.php for the read-only Media Library Intelligence report. The Sveltia screen also has an Owner tools navigation link to this report and the public Gallery. It inventories image files under img/, reports file size and dimensions where available, lists literal references found in public PHP/HTML/CSS/JavaScript/JSON/XML/configuration source files, links Gallery records to their image assets, and groups byte-identical files using SHA-256.

Use the filters to narrow the inventory by Gallery category, filename/source path, usage state, exact duplicate content or optimisation-review signals. The summary reports total media storage and flags files larger than 512 KB or images whose largest dimension exceeds 2,560 px. These are conservative review thresholds—not automatic failures or compression instructions. Compare each original with its actual page display size and responsive derivatives before making changes. Open Sveltia CMS to edit a Gallery record or upload a replacement.

**This report is evidence, not a deletion tool.** “No literal reference found” means the scanner did not find a complete image path in the source files it inspected. It does not prove that a file is unused: PHP or JavaScript can assemble paths dynamically, and shared responsive-image code may select derivatives without embedding each generated path in a page template. Responsive derivatives are labelled separately and are never listed as unused candidates. Exact duplicate means the file bytes have the same SHA-256 digest; it does not mean the images are visually identical.

The report is strictly read-only. It does not upload, replace, hide, rename or delete files, and it does not change Gallery records. The storage total includes originals and responsive derivatives, so it is a repository inventory footprint rather than a measure of bytes transferred to visitors. Before removing any asset, inspect all source usage, responsive-image manifests, CMS records, route screenshots and the current release package. Retain a restorable copy and submit removal through a reviewed pull request only after proving that no page or fallback depends on it.

The report scans bounded source files and explicitly skips include/site-config.local.php, private local configuration, documentation, tests, tooling and dependency directories. It is marked noindex and served with no-store headers, but it is not an authentication boundary. It contains read-only media paths and usage metadata only; do not put secrets or private configuration into the repository or CMS content.

## Sign-in method

The hosted configuration now explicitly allows Sveltia's **token** sign-in method only. Use a fine-grained personal access token restricted to this repository with **Contents: Read and write**. Pull request permission is not required for the current manual compare-and-create workflow. Sveltia stores the token in the browser's local storage; use an owner-controlled browser profile, sign out on shared devices, and revoke the token immediately if the browser or token is exposed. The CMS's cms-media branch selection is not a substitute for protecting main.


### Understanding scan coverage

The Media Library report shows whether its source scan was complete. If it says **Source scan partial**, at least one eligible source file exceeded the per-file safety limit, could not be read, or the overall file/byte budget was reached. Treat all usage counts as incomplete until the skipped source files are reviewed. Responsive derivatives inherit the Gallery category of their original image for filtering, while their direct source-file usage remains attributed to the original so the report does not claim that a page directly references a generated derivative.
