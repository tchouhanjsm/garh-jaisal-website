# PROJECT LOG: Garh Jaisal Haveli website

> **Single source of truth.** ChatGPT chats are disposable; this file is not.
> Update it in EVERY PR (ChatGPT drafts the entry, you approve it).
> Starting a new ChatGPT chat? Paste the "RESUME PROMPT" at the bottom, then attach this file.

## 1. Snapshot (update at every merge)

| Item | Value |
|---|---|
| Last updated | YYYY-MM-DD |
| Current phase | Phase 0, Repo hygiene |
| Last merged PR | none yet (baseline commit only) |
| Open PR | none |
| Latest tag / release | none |
| Production URL | https://www.hotelgarhjaisal.com (untouched; staging is the working copy) |
| Staging URL | <fill in> |
| CI status on main | <green / red> |
| Next action | Phase 0 batch: clean up repo, make CI green |

## 2. Roadmap and progress

Status key: ☐ not started · ◐ in progress · ☑ merged

| Phase | Goal | Status | PRs |
|---|---|---|---|
| 0 | Repo hygiene, CI green, sitemap lastmod | ☐ | |
| 1 | Full audit → `docs/AUDIT-001.md` and prioritised backlog | ☐ | |
| 2 | Visual polish and booking conversion (look and feel, CTAs, mobile) | ☐ | |
| 3 | Technical SEO (head include, schema, .htaccess, 404) | ☐ | |
| 4 | Local SEO and content (page rewrites, blog posts) | ☐ | |
| 5 | Performance and Core Web Vitals | ☐ | |
| 6 | Accessibility | ☐ | |
| 7 | Forms, email and security | ☐ | |
| 8 | Analytics and measurement | ☐ | |
| 9 | Staging QA, cPanel handoff, tag v1.0.0 | ☐ | |
| 10 | Monthly improvement loop | ☐ | |

## 3. Approved PR register (newest first)

<!-- Copy the block below for each merged PR. -->

### PR #__: <title>  ·  merged YYYY-MM-DD  ·  branch `feature/...`  ·  Phase __
- **Why (owner language):**
- **What changed:** (pages / files)
- **Not touched:**
- **Evidence:** CI green · `check-site.php` 0 errors · tested at 390px and 1280px
- **Residual risks:**
- **Owner decisions made:**
- **Follow-ups created:** (link to backlog IDs)

## 4. Backlog (from audit; keep IDs stable)

| ID | Priority | Page / area | Issue | Phase | Status |
|---|---|---|---|---|---|
| B-001 | P0 | include/header.php | Legacy header has 4 dead links | 0 | ☐ |
| B-002 | P1 | sitemap.xml | No `<lastmod>` | 0 | ☐ |
| B-003 | P1 | 7 experience pages | Confirm title/description/canonical render via shared include | 0 | ☐ |
| B-004 | P1 | all pages | Two GA4 IDs loaded; decide the correct setup | 8 | ☐ |
| B-005 | P2 | repo | `.backup`, `.DS_Store`, `Thumbs.db` files | 0 | ☐ |

## 5. Owner decisions and confirmed facts (ChatGPT must not contradict these)

| Date | Decision / fact | Where it applies |
|---|---|---|
| | e.g. "Check-in 12:00, check-out 11:00" | rooms, schema |
| | e.g. "Fort Parking → hotel ≈ 300 m / 5–6 min on foot" | arrival, direction.php |

## 6. Open questions for the owner

- [ ] 
- [ ] 

## 7. Conventions

- Branches: `feature/<short-name>`, one theme per branch. Squash-merge to `main`.
- Commits: conventional (`feat:`, `fix:`, `chore:`, `docs:`, `content:`).
- Never commit `include/site-config.local.php` or any secret.
- Images are final and live in `img/`; ChatGPT never renames or deletes them.
- Only the owner merges.

---

## RESUME PROMPT (copy into a NEW ChatGPT chat, then attach this file)

```text
We are continuing work on the Hotel Garh Jaisal Haveli PHP website. Your standing
instructions (Master Prompt and Role Prompt) are in this Project. I have attached
docs/PROJECT-LOG.md, which is the single source of truth for where we are.

Do this, in order:
1. Read the log. In 6 lines or fewer tell me: current phase, last merged PR, open
   PR (if any), CI state, the next action, and the open questions waiting on me.
2. List which files you will need me to upload for the next action.
3. Do NOT start any work until I reply "go". If anything in the log looks
   inconsistent (for example a PR marked merged but its phase still open), flag it.
```

## END-OF-BATCH PROMPT (use when the PR is green and you are about to approve it)

The log update ships INSIDE the same PR, so `main` always matches the log.

```text
PR for branch feature/<name> is green and I am about to merge it. Produce the updated
docs/PROJECT-LOG.md changes as a diff against the current version: Snapshot table,
Roadmap row, a new Approved PR register entry (use "PR #__" and "merged YYYY-MM-DD"
as placeholders for me to fill), backlog status changes, and any new owner
decisions. Keep every older entry unchanged. Then state the next action.
```

Then: apply the diff on the branch, fill in the PR number and date, commit
`docs: update project log`, push, merge.
