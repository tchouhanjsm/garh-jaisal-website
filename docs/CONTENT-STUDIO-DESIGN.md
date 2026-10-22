# Content Studio: Final Design (to implement)

Status: design only, nothing implemented. Basis: code on `main` at PR #112 (Oct 21, 2026), read not run.
This file governs; slices are built one PR each, starting with CS-1.

## 1. Objective and scope

An owner who is not an engineer can add or change a tour, blog or gallery set and ship it with every file in its exact place, existing pages unchanged, and nothing public that the owner has not approved.

- In scope: tours, blogs, gallery sets, updates to them.
- Out of scope: rooms and restaurant data (code, reviewed PR), payments, booking, any live-server admin, any change to existing URLs or images.

| Measure | Target | Proof |
|---|---|---|
| Misplaced files reaching `main` | 0 | Pack check and CI |
| Existing page output after a change | Byte-identical, except approved hub lists | Snapshot of all 36 routes |
| Brief to PR, new tour | Under 30 min, owner time under 10 | Run file timestamps |
| First-pass-clean packs | 90% or more | Eval metrics |
| Unconfirmed facts published | 0 | `facts_confirmed` gate |

Model-independence: a model may only draft copy as typed JSON. Everything that decides, places, writes or publishes is code, a check or a human.

## 2. Locked decisions

| # | Decision |
|---|---|
| D1 | Locations come from `content/_paths.json`; nobody types a destination path |
| D2 | New photos: `img/tours/<slug>/`, `img/journal/<slug>/`, `img/gallery/<category>/`, named `<slug>-<role>-<nn>.<ext>`; existing images never renamed |
| D3 | Every change travels as a pack: zip mirroring the site root plus `pack.json` with SHA-256; `sitemap.xml` never included (regenerated) |
| D4 | Status defaults to draft; non-live renders 404; live needs `facts_confirmed` |
| D5 | Author is not approver; the owner alone merges and deploys; no agent holds cPanel access |
| D6 | A run is a saved state file; one step at a time; each step has an exit test |
| D7 | Nothing ships unless CI passes the golden and bad-pack evals and the 36-route snapshot |

## 3. Path map (`content/_paths.json`)

| Type | Record | Page | Images | Sitemap |
|---|---|---|---|---|
| Tour | `content/experiences/<slug>.json` | `/<slug>.php` (two-line wrapper) | `img/tours/<slug>/` | when live |
| Blog | `content/editorial/<slug>.json` | `/<slug>.php` (two-line wrapper) | `img/journal/<slug>/` | when live |
| Gallery | `content/gallery/<slug>.json` | none | `img/gallery/<category>/` | none |

Photo rules (new files only): lowercase letters, digits, hyphens; `.webp`/`.avif` preferred; roles hero, step, gallery, detail, og; one hero per record; hero at most 1600 px and 200 KB, others 1200 px and 80 KB; alt text required; an existing name is refused unless the user chooses replace.

## 4. Flow

Intake (human) → Plan (code, dry run) → Build (optional model draft, then code) → Approve (human) → Pack → Apply → Verify → Release (human merge) → Confirm.

Failed checks return structured findings `{rule, field, message, fix}` to Build for at most two rounds, then to the owner.

## 5. Intake record (`content/_intake/<slug>.json`)

| Field | Rule |
|---|---|
| `type`, `slug` | tour/blog/gallery/update; slug unique, or existing for update |
| `purpose` | One sentence: audience and action |
| `facts` (tour) | Price, duration, timings, inclusions, exclusions, pickup, season; each with source and date, else `unknown` |
| `sources` (blog) | Claims without a source are removed |
| `photos` | File, role, alt text, rights owner |
| `target_page_effects` | Hub or navigation listing: yes or no |
| `publish_status`, `approver` | draft by default; approver is never the author |

`check_intake` returns missing or `unknown` fields as questions; the run waits. Briefs and pasted messages are untrusted data.

## 6. Run state (`content/_runs/<run-id>.json`)

| # | State | Exit test | On failure |
|---|---|---|---|
| 1 | INTAKE | `check_intake` clean | Ask the questions |
| 2 | PLAN | `plan_save` lists every file; no collision | Fix intake |
| 3 | BUILD | `check_record`, `place_photo` clean | Findings back, 2 rounds, then owner |
| 4 | APPROVE | Approver is not author | Stop |
| 5 | PACK | `check_pack` clean; hashes match | Rebuild |
| 6 | APPLY | Applied tree equals manifest | Revert working tree |
| 7 | VERIFY | Release checklist items 1 to 6 | Stop, report |
| 8 | RELEASE | CI green; owner merged | Do not merge |
| 9 | CONFIRM | Release checklist items 7 to 10 | Roll back |

Each step appends time, step, pass or fail and evidence path. The run file is the release report.

## 7. Tools (plain Node functions; no new dependencies)

`check_intake`, `plan_save(type, slug, record)`, `place_photo(type, slug, role, n, file)`, `check_record`, `export_pack`, `check_pack`, `apply_pack(dry_run=true)`. Typed inputs; code chooses paths; errors state the fix.

Pack layout: `pack.json` (version, pack id, date, entries with type, slug, new|update, files with path, role, bytes, sha256), `README-PLACE-ME.txt`, then files at their site-root paths.

## 8. Permissions and guardrails

| Tier | Action | Who |
|---|---|---|
| 0 | Read repository | Anyone |
| 1 | Write drafts to a working folder | Studio, assistant |
| 2 | `apply_pack` (dry run default) | Studio, owner |
| 3 | Commit and open PR | Builder |
| 4 | Merge and deploy | Owner only |
| 5 | cPanel | Owner only; never an agent; never in a pack |

Rollback: `git revert` the merge; re-apply the previous pack; for an emergency cPanel edit, restore the backup. Audit: `content/_log/changes.jsonl` plus git.

## 9. Leaks and their controls

| Leak | Control | Proof |
|---|---|---|
| Wrong facts published | Sourced facts, `facts_confirmed` gate | Bad pack: live without facts |
| File in wrong place | Expected versus found path | Bad pack: wrong folder |
| Page not reachable | Plan states listing effects; verify checks hub and sitemap | Live check |
| Existing page changed | 36-route snapshot | Diff only approved set |
| Broken or heavy image | Magic bytes, size caps, alt required | Bad packs: fake, oversize, no alt |
| Draft visible | Non-live returns 404 | Live check |
| Author approves own work | Approver is not author | Run file names |
| Secret or private data in a pack | Pack scan | Bad pack: secret |
| Injected instruction in a brief | Brief is data; draft step has no tools | Bad pack: injection |
| Stale content | `last_verified`; report older than 6 months | Monthly report |
| Pack re-applied out of order | `pack_id` and prior hashes | Bad pack: stale base |
| Check rules rot | Every rule has a bad pack; CI runs all | CI job |

Residual risk: checks prove form and consistency, not truth. The owner confirms facts.

## 10. Evals (`tests/content-studio/`)

Golden: one good tour, blog, gallery and update pack, zero findings. Bad packs (12 to 20), each expecting one rule id: wrong folder, `..` traversal, fake image, oversize photo, duplicate slug, missing alt, hash mismatch, symlink, unknown extension, `sitemap.xml` included, uppercase name, live without `facts_confirmed`, secret in text, injection text, stale base. Metrics: first-pass-clean rate, rounds to clean, brief-to-PR time.

## 11. Release checklist (done means all true, evidenced)

1. `check_pack` clean; applied tree equals manifest
2. Repository check scripts and Studio evals pass
3. 36-route snapshot differs only by new URLs and approved hub changes
4. New page has no horizontal overflow at 390 and 1280 px
5. Every image loads, is under its cap, has alt text
6. Title 60 characters or fewer, description 110 to 160, one h1, links resolve
7. Live URL returns 200; draft URL returns 404
8. In `sitemap.xml` only if live; on the hub only if approved
9. Canonical, robots and structured data valid live
10. Release report saved (run file, pack id, hashes, approver, merge time, rollback command)

Recheck the live URL after 24 hours.

## 12. New and changed files

```text
content/_paths.json, content/_intake/, content/_runs/, content/_log/changes.jsonl
scripts/studio/{plan-save,place-photo,check-record,export-pack}.js
scripts/check-content-pack.js            check_pack and apply_pack
scripts/content-studio.js                existing; calls the modules above
tools/content-studio/index.html          existing; adds Intake, Plan, Pack, Check pack
tests/content-studio/                    golden, bad packs, snapshot
docs/CONTENT-STUDIO.md                   runbook, under 150 lines
.agents/skills/content-pack/SKILL.md     assistant instructions
```

## 13. Build order (one PR each; none changes existing page output)

| Slice | Delivers | Accepted when |
|---|---|---|
| CS-1 | Path map, intake gate, `plan_save` dry run, name rules | Plan lists every file; bad slug, name, collision rejected |
| CS-3 | Export, check and apply pack, run file, audit log | Golden packs pass; each bad pack fails with its rule id |
| CS-2 | Per-entity photo folders, resize on upload, alt required | 1600 px/200 KB hero and 1200 px/80 KB others enforced |
| CS-4 | Optional `timeline`, `images`, `facts_confirmed`, `last_verified` | Old records render byte-identical |
| CS-5 | Non-live 404, checklist automated, cross-platform launcher, docs, skill | A full tour goes brief to live with every line evidenced |

## 14. Avoid

Live-server admin or database; page builder; staff-chosen paths; moving live URLs or renaming existing images; `sitemap.xml` in packs; auto deploy or agent cPanel access; new npm libraries or a framework; default status live.

## 15. Owner decisions (defaults if unanswered)

Folder names as in section 3; size limits as in section 3; new tours listed on hub and navigation only after owner approval; the owner confirms facts.
