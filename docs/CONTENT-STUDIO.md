# Content Studio runbook (tours, blogs, gallery)

Governing design: `docs/CONTENT-STUDIO-DESIGN.md`. This file is the how-to. Owner merges and deploys; nobody else.

## What it guarantees
- Every file lands at an exact path from `content/_paths.json`. Nobody types a destination.
- New content is `draft` first. Public draft routes return 404 and stay out of the sitemap; only a token-bound, expiring loopback preview can render a draft.
- Existing pages and images never change: new slugs only, new image names only, `sitemap.xml` is regenerated, never shipped.
- Every release is a pack with SHA-256 hashes, so it is reproducible and checkable.

## Where things go

| Type | Record | Page | Photos |
|---|---|---|---|
| tour | `content/experiences/<slug>.json` | `/<slug>.php` | `img/tours/<slug>/` |
| blog | `content/editorial/<slug>.json` | `/<slug>.php` | `img/journal/<slug>/` |
| gallery | `content/gallery/<id>.json` | none | `img/gallery/<category>/` |

Photo name: `<slug>-<role>-<nn>.webp` (roles: hero, step, gallery, detail, og). Hero up to 1600 px and 200 KB; others 1200 px and 80 KB; alt text on every image. `place_photo` converts and sizes for you.

## Quick add (automatic, the default tab)
You supply only what a person must: **title**, **page name** (optional) and **photos**. Everything else is automatic:
- **Photos:** each is read once; wrong rotation is fixed, near-duplicates are skipped, the sharpest landscape (or a file named `hero-...`) becomes the main photo, the rest are ordered by capture time, too-small photos are refused, blurry ones are flagged. All are converted to WebP, stripped of metadata (including GPS), sized to the caps and stored at `img/tours/<slug>/` (or `img/journal/<slug>/`) with the `<slug>-<role>-<nn>` name.
- **Text:** SEO title and description are fitted to the limits; summary, best-for, timeline and inclusions are optional. If you leave them out, neutral placeholders are used and listed ("To fix before Live"); nothing is invented.
- **Page name:** made from the title; if taken, the next free name is used (an explicitly typed name that is taken is refused).
- **Checks and plan:** every check runs, the exact files are listed, and the run stops at the one human gate.
- **After Approve (a named person other than the author):** the pack is built, the draft is saved in this working tree, the page is rendered and checked (drafts are private, so they must return 404 and stay out of the sitemap), then **Open pull request** puts exactly those files on a new branch `content/<slug>-<date>`, pushes it and opens a PR (via `gh` if installed). It never merges; merging and deploying stay with the owner.
- Optional notes format: `key: value` lines (`summary`, `best for`, `duration`, `category`, `seo title`, `description`, `facts confirmed by: Name 2026-10-24`), lists with `- item` lines (`included`, `not included`, `timeline` as `- Step | text`, `faq` as `- Question | answer`, `photos` as `- file.jpg | alt text`, `sources`), and for blogs `## Heading` sections.
- No browser: put `brief.md` (a `title:` line is enough) and a `photos/` folder in `content/_inbox/<id>/`, then `npm run studio:inbox <id>`, `npm run studio:approve <id> -- --by "Name"`, `npm run studio:ship <id>`. `npm run studio:watch` builds any new folder automatically.
- Tours cannot go Live until facts are confirmed; alt text is drafted from the title when not given, and the result says so.

## Using the detailed forms (press "Advanced forms" for edits and everything Quick add does not cover)
1. Add a tour or blog. Enter the title or URL slug **before** uploading the hero photo: it is converted to WebP and stored as `img/tours/<slug>/<slug>-hero-01.webp` (or `img/journal/<slug>/...`) automatically.
2. For tours, set **Owner confirmed the facts** to Yes only after the owner has confirmed price, timings and inclusions; a tour cannot go Live without it.
3. In **Manage saved content**, press **Export pack** on a tour or blog. It writes `content/_stage/packs/gj-pack_<slug>_<date>_v1_sync.zip`. Unzip into the other checkout (or run `node scripts/check-content-pack.js <zip> --apply`).
4. Photos uploaded the old way (`img/content-studio/`) cannot be packed; upload them again from the form.

## The run (one step at a time)
1. **Intake** `node scripts/studio/cli.js intake-check intake.json` returns questions until complete (facts need a source and date; author is not approver).
2. **Plan** `node scripts/studio/cli.js plan <stage> --type tour --slug <slug>` lists every file. Nothing is written.
3. **Build** put `record.json` in the stage folder. `node scripts/studio/cli.js photo <file> --type tour --slug <slug> --role hero --n 1 --stage <stage>`.
4. **Approve** the approver signs the plan (`run-step <run-id> --by <approver>`).
5. **Pack** `node scripts/studio/cli.js export <stage> --type tour --slug <slug>` writes `gj-pack_<slug>_<date>_v1.zip` (refused if any check fails).
6. **Apply** unzip into the project root, or `npm run pack:check <zip>` (dry run) then `node scripts/check-content-pack.js <zip> --apply`.
7. **Verify** `npm run studio:verify <slug>` and `npm run studio:snapshot -- --against origin/main`.
8. **Release** commit, open a PR, wait for CI, owner merges.
9. **Confirm** `npm run studio:verify <slug> -- --base https://www.hotelgarhjaisal.com`; recheck after 24 hours.

`npm run studio:start` opens the Studio and preview on any OS; its **Pack check** tab plans a record without writing anything.

## Common mistakes the pack check catches
| Mistake | Rule |
|---|---|
| Unzipped one level too deep | PK-01 |
| Photo in the wrong folder | PK-19 |
| Uppercase, spaces, wrong role or number | PK-08 |
| Not a real webp/avif, too big, wrong extension | PK-10, PK-11, PK-09 |
| File edited after export | PK-05 |
| Same slug as an existing page | PK-12 |
| Update based on an out-of-date site | PK-18 |
| `sitemap.xml` inside the pack | PK-07 |
| Live without confirmed facts, secrets, instructions or contact details in text | RC-19, RC-21, RC-22, RC-23 |

## Emergency route (cPanel File Manager)
Upload and extract the pack at the site root, keep the record `draft`, run `php scripts/generate-sitemap.php`, then copy the same files into git the same day. Never put hosting credentials in a pack or give them to an assistant.

## Rollback
`git revert` the merge; or re-apply the previous pack; or restore the cPanel backup. `content/_log/changes.jsonl` records the prior hash of every file an apply changed.

## Known limits (honest)
- Tour, blog and landing-page drafts return 404 on public routes. The local Studio preview uses a loopback-only, token-bound record with an expiry check before opting into Draft rendering.
- Optional record fields `images`, `facts_confirmed`, `last_verified`, `pack_id` are validated and stored; templates do not render `images` yet, so old pages are byte-identical.
- Facts are checked for presence and a source, not truth. Only the owner confirms them.
- Browser overflow checks stay in the Playwright suite.

## Tests
`node tests/node/content-pack.test.js` (golden packs, 21 bad packs each with its own rule id, sync export and Quick add) runs in CI.
