# Setup Guide: from existing folder to a working Git + ChatGPT workflow

Goal: your existing site folder becomes a GitHub repo. ChatGPT proposes improvements (look and feel, SEO, content) in small batches. You review and merge, and `docs/PROJECT-LOG.md` always says where you are.

Time: about 45 minutes, once. Commands are for macOS/Linux Terminal. On Windows use Git Bash.

---

## Step 1: Install the tools (once)

```bash
git --version        # if missing: macOS → xcode-select --install
php -v               # PHP 8.x; macOS: brew install php
gh --version         # GitHub CLI; macOS: brew install gh
gh auth login        # choose GitHub.com → HTTPS → login with browser
git config --global user.name  "Your Name"
git config --global user.email "aratan506@gmail.com"
```
`gh` is optional; without it you create the repo and PRs on github.com instead.

## Step 2: Prepare the project folder

1. Make a **backup copy** of the folder (zip it somewhere safe).
2. Open the real working folder in VS Code (`gj-web` with the real `img/`).
3. Delete files that must not be in the repo (Phase 0 will tidy the rest):
   `.DS_Store`, `Thumbs.db`, `sync-local.sh` (it has your Mac paths). Keep everything else.
4. Unzip `gj-workflow-pack.zip` into the folder root. You should now have `docs/`, `scripts/`, `.github/` next to `index.php`.
5. Check `docs/` contains: `AI-DEV-PLAYBOOK.md`, `AI-ROLES-PROMPTS.md`, `PROJECT-LOG.md`, `SETUP-GUIDE.md` (this file), plus your existing `V2-STATUS.md` notes.

## Step 3: Protect secrets before the first commit

Append to `.gitignore` (keep what's there):
```bash
cat >> .gitignore <<'EOT'
Thumbs.db
*.backup
sync-local.sh
include/site-config.local.php
error_log
EOT
```
Confirm `include/site-config.local.php` (if you have it) is NOT tracked later with `git ls-files | grep local`. It must print nothing.

## Step 4: Run the checks locally once

```bash
php scripts/check-site.php --strict-images
```
Expect some errors (legacy `include/header.php` dead links, `.backup` warnings). That is your Phase 0 job. Just confirm the script runs.

## Step 5: Create the repo and the baseline commit

```bash
cd /path/to/your/site
git init -b main
git add -A
git status                      # read the list: no secrets, no junk
git commit -m "chore: baseline import of V2 site"
```

Create the GitHub repo (private):
```bash
gh repo create garh-jaisal-website --private --source=. --remote=origin --push
```
(Without `gh`: create an empty private repo on github.com, then `git remote add origin <url> && git push -u origin main`.)

## Step 6: Protect `main` and turn on CI

1. GitHub → repo → **Settings → Branches → Add rule** for `main`:
   - Require a pull request before merging
   - Require status checks to pass → select `checks` (appears after the first CI run)
   - Do not allow bypassing
2. **Settings → General → Pull Requests:** enable *Allow squash merging* only.
3. CI runs on every PR via `.github/workflows/ci.yml`. In that file set `php-version` to the PHP version your cPanel host uses (ask your developer).

## Step 7: Set up the ChatGPT Project

1. ChatGPT → **Projects → New project**: "Garh Jaisal Website".
2. **Instructions:** paste the *Master Prompt* from `AI-DEV-PLAYBOOK.md` (section 1), then the *Role Prompt* from `AI-ROLES-PROMPTS.md` (section 1).
3. **Files:** upload `docs/PROJECT-LOG.md`, `docs/V2-STATUS.md`, `include/site-config.example.php`, `scripts/check-site.php`. Never upload `site-config.local.php` or passwords.
4. For each task, upload only the page files it needs (the intake prompt tells ChatGPT what else to ask for).
5. If you want ChatGPT to see how pages look, give it screenshots at phone and desktop width. It can critique design from images.

## Step 8: Your first PR (the pack itself)

```bash
git checkout main && git pull
git checkout -b feature/ci-and-docs
# (files are already in place from Step 2; if you committed them in the baseline, skip to Phase 0)
git add -A && git commit -m "chore: add CI, check script and project docs"
git push -u origin feature/ci-and-docs
gh pr create --fill
```
Watch Actions. It will be **red** at first because of the known legacy issues. Merge anyway only if the failures are exactly the expected ones. Otherwise start Phase 0 on this branch.

## Step 9: Start the improvement loop

Open a new chat in the Project and paste, in order:

1. **File intake** (from `AI-ROLES-PROMPTS.md`, section 2) with the uploaded files.
2. **Phase 0 brief** (from the playbook). Run the loop: PLAN → branch → changes → two-pass review → local check → push → PR → CI → final review → **you merge**.
3. **End-of-batch prompt** (from `PROJECT-LOG.md`) *before* merging, so the log update is inside the PR.

### Making it "look better": the Phase 2 visual brief

Use this after Phase 0 and 1. Upload screenshots (390px and 1280px) of `index.php`, `room.php`, `pack.php`, plus `css/garh-v2.css` and `include/header-v2.php`.

```text
[FRONTEND] Goal: make the existing site look more premium and heritage-appropriate
WITHOUT changing URLs, copy facts or image files. Do a design review of the
screenshots and CSS, then propose ONE batch:
1. Design diagnosis: spacing, type scale, colour palette, image treatment,
   hierarchy, card styles, buttons, header/hero. List what feels dated or
   inconsistent and why.
2. A small design-token plan (CSS variables for colour, font sizes, spacing,
   radius, shadow) added to css/garh-v2.css, reusing existing class names.
3. Concrete changes for the homepage hero, room cards and the sticky mobile
   Call/WhatsApp/Book bar, with full code.
4. What stays exactly as it is.
Keep it to max 10 files. Give a before/after checklist I can verify by eye.
```
Repeat per page group: homepage → rooms → packages → arrival/directions → gallery → blog/guides → contact.

## Step 10: Per-batch routine (every time)

```bash
git checkout main && git pull
git checkout -b feature/<name>
# apply ChatGPT's files in VS Code
php scripts/check-site.php --strict-images
find . -name '*.php' -not -path './.git/*' -exec php -l {} \; | grep -v '^No syntax'
php -S localhost:8000                      # check pages at phone + desktop width
git add -A && git commit -m "<type>: <message from ChatGPT>"
git push -u origin feature/<name>
gh pr create --fill                        # paste ChatGPT's PR body
# wait for green CI → paste CI + `git diff --stat main...HEAD` to ChatGPT for final review
# run the End-of-batch prompt → update docs/PROJECT-LOG.md → commit → push
# you merge (squash) on GitHub
git checkout main && git pull && git branch -d feature/<name>
```

## Step 11: When a ChatGPT chat gets full

1. Open a **new chat in the same Project**.
2. Paste the **RESUME PROMPT** from the bottom of `docs/PROJECT-LOG.md`.
3. Attach the current `docs/PROJECT-LOG.md` from `main` (always the latest merged copy).
4. Wait for its 6-line status. If it matches reality, reply "go".

## Step 12: Hand-off to your cPanel developer (Phase 9)

Give them: the repo (read access), the `v1.0.0` tag, and `docs/DEPLOY-CPANEL.md` (ChatGPT writes it in Phase 9). They create `include/site-config.local.php` on the server themselves. You never send them secrets over chat.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| CI red on `broken local ref img/...` | An image referenced in code isn't in `img/`. Add the file or fix the reference. |
| CI red on `possible hard-coded secret` | Remove the secret from the file, rotate it, move it to `site-config.local.php`. |
| ChatGPT describes code that isn't in the file | You didn't re-upload the latest copy. Re-upload and ask it to re-read. |
| Merge conflict in `PROJECT-LOG.md` | Two PRs edited it. Merge `main` into your branch, keep both register entries, newest first. |
| Accidentally committed a secret | Rotate the secret immediately. Removing it from a later commit does not erase history. |
