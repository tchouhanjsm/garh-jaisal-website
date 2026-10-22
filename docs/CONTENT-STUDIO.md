# Content Studio runbook (tours, blogs, gallery)

Governing design: `docs/CONTENT-STUDIO-DESIGN.md`. This file is the how-to. Owner merges and deploys; nobody else.

## What it guarantees
- Every file lands at an exact path from `content/_paths.json`. Nobody types a destination.
- New content is `draft` first. Drafts render `noindex,follow` and stay out of the sitemap (same as today).
- Existing pages and images never change: new slugs only, new image names only, `sitemap.xml` is regenerated, never shipped.
- Every release is a pack with SHA-256 hashes, so it is reproducible and checkable.

## Where things go

| Type | Record | Page | Photos |
|---|---|---|---|
| tour | `content/experiences/<slug>.json` | `/<slug>.php` | `img/tours/<slug>/` |
| blog | `content/editorial/<slug>.json` | `/<slug>.php` | `img/journal/<slug>/` |
| gallery | `content/gallery/<id>.json` | none | `img/gallery/<category>/` |

Photo name: `<slug>-<role>-<nn>.webp` (roles: hero, step, gallery, detail, og). Hero up to 1600 px and 200 KB; others 1200 px and 80 KB; alt text on every image. `place_photo` converts and sizes for you.

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
- Non-live pages are `noindex,follow` (existing Studio preview relies on this), not 404. Revisit when tours and blogs get token previews like landing pages.
- Optional record fields `images`, `facts_confirmed`, `last_verified`, `pack_id` are validated and stored; templates do not render `images` yet, so old pages are byte-identical.
- Facts are checked for presence and a source, not truth. Only the owner confirms them.
- Browser overflow checks stay in the Playwright suite.

## Tests
`node tests/node/content-pack.test.js` (golden packs plus 21 bad packs, each with its own rule id) runs in CI.
