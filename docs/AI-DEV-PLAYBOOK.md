# Garh Jaisal Haveli: ChatGPT Development Playbook

How to use this: you are the **hotel owner and merge authority**. ChatGPT is the **developer**. You paste prompts in order, and ChatGPT returns code and evidence. You run the checks in VS Code, push, and merge.

Files in this pack (drop them into the repo root, keeping paths):

| File | Purpose |
|---|---|
| `docs/AI-DEV-PLAYBOOK.md` | This document |
| `scripts/check-site.php` | Local and CI checks: SEO basics, sitemap, broken refs, secrets, junk files |
| `.github/workflows/ci.yml` | Runs PHP lint and `check-site.php` on every PR |
| `.github/pull_request_template.md` | The handoff-evidence PR body |

---

## 0. One-time setup (you, about 30 minutes)

1. Extract the zip. Copy your real `img/` back in so the repo contains the real images. The staging site and the developer then get identical assets.
2. In the repo root run:
   ```bash
   git init -b main
   printf '.DS_Store\nThumbs.db\n*.backup\ninclude/site-config.local.php\nerror_log\nsync-local.sh\n' >> .gitignore
   git add -A && git commit -m "chore: baseline import of V2 site"
   ```
3. Create a GitHub repo (private) and push `main`.
4. Add the 4 pack files above, then commit them on a branch (`feature/ci-and-playbook`). This is the first PR and it follows the process.
5. GitHub → Settings → Branches → protect `main`: require a PR, require the `checks` status, and block direct pushes.
6. Create a ChatGPT **Project** named "Garh Jaisal Website". Paste the Master Prompt (section 1) into the project instructions. Upload `docs/*.md`, `include/site-config.example.php` and `scripts/check-site.php` as project files. Never upload `site-config.local.php`.

Notes on the zip:

- `check-site.php` already runs against it. It flags 4 real dead links in the legacy `include/header.php` and a set of `.backup`, `.DS_Store` and `Thumbs.db` files. CI will be red until Phase 0 removes them. That is intended.
- Seven experience pages (`sunset-camel-safari.php`, `swiss-tent-safari.php` and others) get their title, description and canonical from a shared include. They need a one-time check that the include renders them.
- `sitemap.xml` has no `<lastmod>` yet.
- `sync-local.sh` has your Mac paths in it. Keep it out of the repo.

---

## 1. MASTER PROMPT (paste once as the Project instructions, or as the first message of a chat)

```text
ROLE
You are the lead web developer and SEO engineer for Hotel Garh Jaisal Haveli, a
heritage hotel inside Jaisalmer Fort, Rajasthan, India. I am the hotel owner and
the product owner. I decide what ships and I am the only person who merges.
Think and decide like an owner who is building this site himself: every change
must help a real guest find us, trust us, and book directly (Book Now, WhatsApp,
Call) instead of through an OTA.

PROJECT FACTS
- Stack: plain PHP (no framework), shared includes in /include, CSS in /css,
  deployed by a developer to cPanel shared hosting from the `main` branch.
- Key files: include/site-config.php (single source for phone, WhatsApp, email,
  booking URL, GA IDs), include/rooms-data.php, include/experience-data.php,
  include/header-v2.php, include/footer-v2.php, css/garh-v2.css, sitemap.xml,
  robots.txt, scripts/check-site.php.
- Production domain: https://www.hotelgarhjaisal.com. Existing URLs must keep
  working. Never rename or delete a public URL without adding a 301 redirect and
  telling me.
- Images are stored in the repo and are final. Never invent, rename or delete image
  files. Reference only files that exist. If you need a new image, tell me the
  exact filename, size and alt text you want and I will supply it.
- Secrets (reCAPTCHA secret, mail credentials) live only in
  include/site-config.local.php (gitignored) or environment variables. Never
  write a secret into any committed file or into this chat.

NON-NEGOTIABLE RULES
1. Do not invent facts. Prices, distances, timings, inclusions, awards, review
   counts and policies must come from the repo or from me. If something is
   unknown, write "NEEDS OWNER INPUT: <question>" and leave a neutral
   placeholder. Never add fake reviews, ratings or AggregateRating schema.
2. Keep each batch small and coherent: one theme, at most about 10 files, one
   branch, a few commits. If a task is bigger, split it into batches and tell me
   the order.
3. Preserve what works. Do not restyle or restructure pages that are outside
   the batch's scope.
4. Every public page needs: one H1, a unique <title> (under about 60 characters),
   a unique meta description (about 140 to 160 characters), a canonical URL, OG/Twitter
   tags, descriptive alt text, a breadcrumb where relevant, and internal links.
5. Mobile first: check at 390px wide and at 1280px. Tap targets are at least 44px.
   No horizontal scroll. Call, WhatsApp and Book Now stay reachable.
6. Code must pass `php -l`, run on PHP 8.x and also on 7.4 where possible, work
   on cPanel shared hosting (no Composer, no build step, no Node at runtime),
   and escape all output (htmlspecialchars). Forms need CSRF, honeypot and
   server-side validation.
7. Performance: width/height on images, lazy-load below the fold, WebP where it
   exists, no render-blocking @import, no new heavy libraries.
8. Be honest about risk. End every batch with residual risks and anything you
   could not verify. Do not claim something was tested if you only read the code.

MY WORKFLOW (you must follow it every time)
main (confirm base) -> feature/<short-name> -> two-pass code review ->
local check -> push -> PR with handoff evidence -> CI -> final diff review ->
OWNER MERGES.
- You never say "merged" or "deployed". You say "ready for owner review".
- Two-pass review: PASS 1 = product behaviour and UI/UX. PASS 2 = SEO, errors and
  recovery (failure states, 404s, form failures, rollback).

HOW TO ANSWER EACH TASK
Give me, in this order:
1. PLAN: the batch scope, the files you will touch, and what you will not touch.
2. BRANCH NAME and the exact git commands to run.
3. CHANGES: every changed file in FULL (or a unified diff if the file is large).
   Never use "... rest unchanged" inside a file I have to copy.
4. REVIEW PASS 1 and REVIEW PASS 2: bullet findings, and what you fixed because
   of them.
5. LOCAL CHECK: the exact commands for me to run
   (`php scripts/check-site.php --strict-images`, `php -l` on changed files, a
   local `php -S localhost:8000` smoke test) and the exact URLs to open and what I
   should see.
6. COMMIT MESSAGES (conventional commits) and the PR title and body, filled in
   using .github/pull_request_template.md.
7. RESIDUAL RISKS and OWNER DECISIONS NEEDED.

If I paste CI output or a diff back to you, treat it as evidence, diagnose the
root cause, and give a minimal fix commit. Do not rewrite unrelated code.

Acknowledge with "READY" and wait for my first task.
```

---

## 2. THE LOOP: one prompt you reuse for every batch

Paste this and fill in the two blanks. It maps one-to-one onto your 9 steps.

```text
NEW BATCH
Goal (owner language): <e.g. "make the rooms page convert better on mobile">
Phase: <number from the roadmap>   Pages in scope: <list or "you decide, max 10 files">

Run the full workflow:
STEP 1 base: tell me the git commands to confirm I am on a clean, up-to-date main.
STEP 2 branch: give me the branch name.
Then produce the PLAN, CHANGES, REVIEW PASS 1, REVIEW PASS 2, LOCAL CHECK, COMMITS
and PR text exactly as specified in your instructions.
Stop after the PR text. I will run the checks, push, and paste back the CI result
and the `git diff --stat main...HEAD`.
```

After the PR is open, paste this:

```text
CI RESULT and DIFF (below). Do STEP 8: final review. Check the diff for scope creep,
files that should not be there, commit count, secrets, changed URLs, and anything
SEO-negative (removed content, lost canonicals, broken internal links). Give a clear
verdict: READY FOR OWNER MERGE or CHANGES NEEDED, with the minimal fixes.

<paste CI output and git diff --stat>
```

You then (steps 5 to 9):

```bash
php scripts/check-site.php --strict-images
find . -name '*.php' -not -path './.git/*' -exec php -l {} \; | grep -v '^No syntax'
php -S localhost:8000          # open the URLs ChatGPT listed, at phone and desktop width
git push -u origin feature/<name>
gh pr create --fill            # or open it on github.com and paste the PR text
```

Wait for green CI, read the diff yourself, then merge (squash). Then `git checkout main && git pull`.

---

## 3. ROADMAP: paste one phase brief per batch into the loop

Each phase is one or more batches. Do them in order. Don't start the next phase until the previous PR is merged.

### Phase 0: Repo hygiene and safety net (1 batch)
```text
Phase 0 brief. Clean the repo and make CI green. Known issues from my own check:
- include/header.php (legacy) links to deluxe-room.php, classic-triple-room.php,
  attractions.php, attraction.php, which do not exist. Confirm nothing includes
  header.php / footer.php (legacy) and header-v2.backup.php, then remove the unused
  legacy includes. If something does include them, migrate it to the v2 versions.
- Remove contact.php.backup, mail.php.backup, .DS_Store, Thumbs.db, img/loader.html
  and the .html "ui-icons" files in css/images if unreferenced. List each file and
  the proof it is unused before deleting.
- Confirm all 7 experience pages render title, meta description and canonical
  through include/experience-page.php. Report how.
- Add sitemap <lastmod> using the real last-change dates from git.
- Make sure `php scripts/check-site.php --strict-images` exits 0.
No visual changes in this batch.
```

### Phase 1: Full audit (1 batch, no code changes except docs)
```text
Phase 1 brief. Audit only. Produce docs/AUDIT-001.md with a prioritised backlog
(P0 / P1 / P2), each item having: page, problem, why it costs us bookings or
rankings, the fix, effort (S/M/L). Cover: (1) conversion path and CTAs on every page,
(2) technical SEO (titles, descriptions, canonicals, headings, schema, sitemap,
robots, redirects, duplicate or thin pages, internal linking), (3) mobile UX,
(4) Core Web Vitals risks from the markup, (5) accessibility, (6) security of the
contact/mail flow, (7) analytics (note: two GA4 IDs are loaded on every page, so
tell me if that double-counts and what to do), (8) content gaps against what a
guest researching "stay inside Jaisalmer Fort" needs. Then propose the batch order
for Phases 2 to 9. Do not change code.
```

### Phase 2: Booking conversion and mobile UX (2 to 3 batches)
```text
Phase 2 brief. Goal: more direct bookings. Review header, hero, room cards, the sticky
mobile Call/WhatsApp/Book bar and the booking CTA on every page. Improve: clarity of the
primary action, "why book direct" block (only use benefits I confirm), room cards
showing capacity, bed type, key amenities and a "Check availability" CTA, a clear
arrival/"how to find us inside the fort" section near the CTA, and trust signals
that already exist in the repo. Add GA4 events for click on Book Now, WhatsApp, Call
and form submit via one shared script, not inline on every page. Ask me for any
missing facts instead of guessing.
```

### Phase 3: Technical SEO foundation (2 batches)
```text
Phase 3 brief. Standardise head metadata through one include so no page can ship
without title, description, canonical, OG and Twitter tags. Fix schema: Hotel (with
real address, geo, telephone, checkin/checkout only if I confirm), room-level
HotelRoom/Offer only with prices I confirm, BreadcrumbList on inner pages, FAQPage
only where the visible page has the same Q&A, and no AggregateRating. Add .htaccess
rules for: force https and one canonical host (www), 301 from any old URLs, custom
404 page with navigation, caching and gzip headers, and security headers. Warn me
about any rule that could take the site down on cPanel and give a rollback line.
Update sitemap.xml and robots.txt.
```

### Phase 4: Local SEO and content depth (3 to 5 batches, repeat)
```text
Phase 4 brief. Build topical authority for "hotel inside Jaisalmer Fort", "Jaisalmer
fort stay", "desert safari from Jaisalmer", "heritage haveli Jaisalmer". Rules:
people-first, first-hand, specific, no keyword stuffing and no near-duplicate pages.
For each new or upgraded page give me: target search intent, the title and
description, an outline, internal links in and out, and a list of facts I must confirm.
Strengthen the existing guides (fort, desert safari, 2-day itinerary) before adding
new ones. Add a FAQ to rooms, arrival and packages pages. Make the NAP (name,
address, phone) identical everywhere and identical to my Google Business Profile.
Also give me a short checklist for the Google Business Profile that I do by hand
(categories, photos, posts, Q&A, reviews).
```

### Phase 5: Performance and Core Web Vitals (2 batches)
```text
Phase 5 brief. Using only what is in the repo: find render-blocking CSS/JS, unused
CSS (css/ has many legacy files), jQuery/Bootstrap that can be removed from V2
pages, missing width/height on images, missing lazy-loading, oversized images (list
file, current size, recommended size), font loading, and layout-shift risks. Fix what
is safe. For image conversion give me a command list (cwebp / ImageMagick) that I run
locally, and then reference the outputs. Target: LCP under 2.5s and CLS under 0.1 on a
mid-range phone.
```

### Phase 6: Accessibility (1 batch)
```text
Phase 6 brief. Landmarks, heading order, focus states, keyboard menu, aria on the mobile
menu, colour contrast against the V2 palette, alt text quality, form labels and error
messages, reduced-motion. Fix what you find and give me a short manual test script.
```

### Phase 7: Forms, email and security (1 to 2 batches)
```text
Phase 7 brief. Harden contact.php and mail.php: CSRF, honeypot, rate limit, server-side
validation, header-injection protection, reCAPTCHA verification failing safely, a
friendly failure message, a logged failure (no personal data in logs), and a fallback
that shows the WhatsApp/phone/email if the mail fails. Make sure the thank-you page
cannot be spam-indexed and that the tracking event fires only on a real success.
Describe how I test one enquiry on staging without touching live mail credentials.
```

### Phase 8: Analytics and measurement (1 batch)
```text
Phase 8 brief. Decide the one correct GA4 setup (I currently load two IDs) and give me the
change. Define key events: book_now_click, whatsapp_click, call_click, form_submit,
direction_click. Provide a Search Console and GA4 verification checklist that I do by
hand. Create docs/MEASUREMENT.md listing each event, where it fires and how to test it.
```

### Phase 9: Staging QA and cPanel handoff (1 to 2 batches)
```text
Phase 9 brief. Create docs/DEPLOY-CPANEL.md for the developer: required PHP version and
extensions, document root, how to create include/site-config.local.php with the
reCAPTCHA secret and mail settings (placeholders only), file permissions, how to
deploy from git (cPanel Git Version Control with .cpanel.yml, or a ZIP of main), the
smoke-test list, DNS/SSL checks, the rollback procedure (redeploy the previous tag),
and a post-launch checklist (sitemap submission in Search Console, 301 test list,
check robots.txt, confirm forms deliver). Also produce a full pre-launch QA
checklist: every URL on 390px and 1280px, every CTA, every form, 404 page, and
structured-data testing.
Tag the release as v1.0.0 and give me the git command.
```

### Phase 10: Continuous improvement (monthly loop)
```text
Monthly brief. I will paste this month's Search Console queries/pages, GA4 landing pages
and conversion events, and any guest feedback. Find the 3 highest-impact changes (pages
with impressions but low CTR, pages with traffic but no booking clicks, queries with no
matching page). Return ONE batch for the loop, ordered by impact and effort, plus facts
you need from me. Do not add content just for volume.
```

---

## 4. Rules for you (the owner)

- You are the only person who merges. ChatGPT has no repo access, so it cannot merge.
- Don't accept a batch without `check-site.php` and `php -l` output pasted into the PR.
- One branch, one theme. If ChatGPT drifts, paste: `Scope creep: revert everything outside <scope> and resubmit.`
- Facts: whenever ChatGPT writes "NEEDS OWNER INPUT", answer it in the next message before continuing.
- If ChatGPT's context gets long, start a new chat inside the same Project. Begin with: `Read docs/V2-STATUS.md and the last merged PR summary. Confirm you understand the current state, then wait.` Update `docs/V2-STATUS.md` in each PR so state lives in the repo, not in the chat.
- Handing off to the developer: give them the repo URL, a tagged release, and `docs/DEPLOY-CPANEL.md`. They should not need to change any code.

## 5. Cash-voucher project

The loop in section 2, the PR template and `check-site.php`-style CI work unchanged. Replace the Master Prompt's project facts and the SEO rules with the voucher rules (amounts, expiry, redemption, audit trail), and keep the "no invented facts" and "owner merges" rules.
