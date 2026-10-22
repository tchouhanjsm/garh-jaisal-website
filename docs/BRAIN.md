# BRAIN: read this first

Short operating brief for any AI working on this repo (ChatGPT, Codex, Claude). If another doc conflicts with this one, **BRAIN.md wins**. Then read `docs/PROJECT-LOG.md` (state) and `docs/SPRINT-PLAN.md` (what to build now).

## Mission
Make hotelgarhjaisal.com a fast, trustworthy direct-booking site for a heritage haveli inside Jaisalmer Fort. Plain PHP on cPanel, no build step. Owner merges; nobody else.

## How we work (fast lane)
1. **Build, don't audit.** The test and audit net is complete (CI: PHP lint, site checks, Playwright smoke, SEO, links, layout). Do not add new audit PRs unless a bug needs a regression test.
2. **One PR = one vertical slice** (a page group or a foundation piece: template + CSS + copy + test). Typical size 5 to 20 files. No one-tweak PRs. If a PR would exceed ~25 files, split it.
3. **Reuse before writing.** New pages are config + partials (`include/page-head.php`, `include/ui/*`, `include/*-data.php`). If you are copying a head block or a card's markup, stop and make it a partial.
4. **Review by eye.** Every PR must produce the page screenshot sheet (390px and 1280px, all routes) as a CI artifact and name the 3 pages the owner should open first.
5. **Evidence in the PR:** CI green on the exact head SHA; `php scripts/check-site.php --strict-images`; `php scripts/generate-sitemap.php --check`; list anything not run (for example PHP 7.4 locally, real devices).
6. **Commits:** small, conventional (`feat:`, `fix:`, `chore:`, `docs:`, `test:`). Branch `feature/<slice>`. Never `git add -A`; never stage `.DS_Store` or `node_modules`.
7. **Update `docs/PROJECT-LOG.md` inside the PR** (snapshot, one entry, owner questions). Keep entries to 5 lines.

## Roles (tag the message: [BUILD] [SEO] [BLOG] [REVIEW])
- **[BUILD]** frontend and PHP. Output complete files or a unified diff, never "rest unchanged".
- **[SEO]** titles, descriptions, headings, internal links, schema. Facts only from `docs/OWNER-FACTS.md` or the repo.
- **[BLOG]** guides in the owner's voice; no invented anecdotes, prices, timings or reviews.
- **[REVIEW]** the Smart Check four-role review (`docs/SMART-CHECK-MATRIX.md`).

## Hard rules
- **Never invent** prices, timings, distances, inclusions, awards, ratings, reviews or policies. Missing fact: write `NEEDS OWNER INPUT: <question>`, add it to `docs/OWNER-FACTS.md`, and keep the page `status: draft` (noindex, out of nav and sitemap).
- **Keep every public URL.** Merging or removing a page needs an owner decision and a 301.
- **Images are final.** Never rename or delete image files that pages reference. Request new images by exact filename, size and alt text.
- **Secrets** only in `include/site-config.local.php` (git-ignored). Never in chat or commits.
- **No new JS or CSS libraries.** Vanilla JS in `js/garh-v2.js`; styles in `css/garh-v2.css` plus the existing page CSS. Do not add jQuery, Bootstrap or icon fonts.
- **Mobile first:** 320, 390, 1280, 1440 wide; no horizontal scroll; tap targets at least 44px; Book Now, WhatsApp and Call stay reachable.
- **Accessibility:** one H1, alt text, labels, visible focus, `rel="noopener"` on `_blank`.

## Decisions already made (do not reopen)
| Decision | Value |
|---|---|
| Google Analytics | **Keep both GA4 IDs** (`G-0C646S1FQ0`, `G-6S5PCEVXH4`). One source: `analytics_ids` in `include/site-config.php`. Load `gtag.js` once; `config` each ID once. |
| Menu | About ▾ · Stay ▾ · Dining · Packages ▾ (Desert Tours, Car & City Tours) · Directions · Blog · Contact · Book Now. Source: `include/nav-data.php`. |
| Hidden for now | `explore-the-desert.php` (hidden). Longewala and Rajasthan tours are drafts. Revisit at the end. |
| Kept live | `camping.php` (linked from the Desert Tours hub). |
| Repo visibility | Public. Do not put private business data in docs. |

## Where things live
`include/nav-data.php` menu · `include/experience-data.php` tours · `include/rooms-data.php` rooms · `include/site-config.php` contact, booking URL, GA IDs · `scripts/generate-sitemap.php` sitemap · `tests/e2e/` browser tests · `docs/SPRINT-PLAN.md` current work.

## Reply format for a build task
1. Plan in 5 lines: slice, files to touch, files not touched. 2. Branch name. 3. Files (complete). 4. Commit messages. 5. PR body from `.github/pull_request_template.md`. 6. Not-run checks and owner questions. No other prose.

## Resuming in a new chat
Read this file, `docs/PROJECT-LOG.md`, `docs/SPRINT-PLAN.md`. Reply in 5 lines: current sprint and step, last merged PR, open PR, blocker, next action. Wait for "go".

## Content Studio (tours, blogs, gallery)
Add or change tours, blogs and gallery sets only through the Content Studio pack workflow: `docs/CONTENT-STUDIO.md` (how-to), `docs/CONTENT-STUDIO-DESIGN.md` (design), `content/_paths.json` (exact paths), skill `.agents/skills/content-pack`. Never hand-place files or rename existing images.
