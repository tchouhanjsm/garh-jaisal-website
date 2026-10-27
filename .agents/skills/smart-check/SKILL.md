---
name: smart-check
description: Review any change to the Garh Jaisal Haveli PHP website through four specialist roles (Full-Stack Developer, SEO Content Writer, Debugger, UI/UX Lead). Every role looks at every change; roles the change does not touch return a green that still names what they checked. Use for PR review, CI failures, release readiness, SEO, responsive/UI, PHP/backend, forms, security, and analytics changes.
---

# Smart Check Skill

## Purpose

Review a change the way four specialists would. Each role decides whether the change matters to them, runs the checks they own, and reports a verdict. A role that is not affected still reports a green, but it must show what it looked at, so "not my area" is never an unchecked assumption.

This skill coordinates existing project checks and reviewer judgment. It does not replace CI or owner review.

## Required workflow

1. Read `docs/PROJECT-STATE.md`, `docs/TESTING.md`, `package.json`, and the CI workflows in `.github/workflows/` first.
2. Inspect the diff. Using the matrix, mark each role **primary** (change touches its surface) or **secondary** (it does not).
3. Review as each role in turn, using the role sections below. If subagents are available, run each role as an independent reviewer so one role's conclusion does not bias another. Otherwise write each role's verdict before reading the next role's checklist.
4. Run the checks the primary roles select: cheap deterministic checks first, then targeted tests, then the full relevant regression suite.
5. Take any failure to the Debugger. Fix the root cause. Never weaken a valid assertion to make CI green.
6. Re-run the failed check and its regression group after every fix. Before recommending merge, verify CI on the exact latest PR head SHA.
7. Combine the role verdicts into the final report.
8. Never merge or deploy. The repository owner retains those decisions.

## Role verdicts

Every role reports exactly one verdict:

| Verdict | Meaning |
|---|---|
| **GREEN** | The change touches this role's surface, and its checks passed with evidence. |
| **N/A-GREEN** | The change does not touch this role's surface. The role still did the double-check below and states what it looked at. |
| **YELLOW** | Not blocking, but the owner should know (risk, gap, follow-up). |
| **RED** | A real defect or unsupported claim. Do not recommend merge. |

### The double-check rule

A role may not answer N/A-GREEN from the file list alone. It must open the diff and name at least one concrete thing it checked, for example: "No template changes; skimmed the diff for CSS selectors that could affect the nav." If the skim finds a problem, the verdict becomes YELLOW or RED. "N/A" with no stated check is not accepted.

## Roles

### 1. Full-Stack Developer

**Owns:** `*.php`, `include/**`, `mail.php`, `js/**`, `scripts/**`, `package.json`, `playwright.config.js`, `.github/workflows/**`, configuration and dependencies.

**Does it matter?** Any change to PHP, includes, routing, request handling, forms, mail, scripts, config or CI.

**Checks**
- PHP lint on the CI PHP versions, and `php scripts/check-site.php --strict-images`.
- Output escaping, input validation, redirects, error handling, and secret boundaries (`include/site-config.local.php` stays local and ignored).
- New or changed external scripts, and dependencies.
- PHP 7.4 and 8.2 compatibility. CI's PHP version is a signal only; the cPanel host version is unconfirmed until the owner confirms it.
- Never send real enquiries or print secrets.

**Double-check when not primary:** Skim for stray PHP in changed templates, broken `include` paths, unescaped output, and newly added external script or link tags.

### 2. SEO Content Writer

**Owns:** page copy in `*.php`, titles, meta descriptions, headings, internal links, JSON-LD, `sitemap.xml`, `robots.txt`, blog and guide pages.

**Does it matter?** Any change to visible copy, metadata, headings, links, structured data, sitemap or URLs.

**Checks**
- Rendered SEO checks (`tests/e2e/seo.spec.js`): one description, one absolute HTTPS canonical matching the sitemap route, one `main h1`, valid JSON-LD.
- Quality, which no test covers: unique and accurate title and description per page, a clear search intent, sensible heading order, useful internal links.
- Facts: every hotel, price, timing, inclusion and location claim must come from approved project content. Never invent testimonials, ratings, awards or dates.
- Existing public URLs and canonical targets stay unchanged unless the owner approved a change.

**Double-check when not primary:** Confirm no title, H1, meta description, anchor text or canonical changed incidentally, and that any new public page is in `sitemap.xml`.

### 3. Debugger

**Owns:** CI results, failing tests, console and runtime errors, broken references, flaky behavior.

**Does it matter?** Always at least partly. Every change gets its CI run read, not just its final status.

**Checks**
- Open the CI runs for the **exact latest PR head SHA**. A pass on an older commit does not count.
- For each failure, classify it as a real defect, a test defect, or an environment limitation, and find the root cause.
- Read `WARN` lines from `check-site.php`, not just its exit code. Warnings are leads to confirm against rendered output.
- CI retries failed Playwright tests once, so a retried pass is a flaky signal. Flag it instead of calling it clean.
- Look for page errors, 404 assets and broken `href`/`src` references.

**Double-check when CI is all green:** Still confirm the SHA matches, scan the logs and report for warnings or retries, and state what was read.

### 4. UI/UX Lead

**Owns:** `css/**`, `restaurant.css`, `restaurant.js`, navigation, shared header and footer, room cards, images, forms as seen by users, booking and contact calls to action.

**Does it matter?** Any change to layout, styling, navigation, images, forms, booking links, or copy long enough to affect layout.

**Checks**
- Playwright smoke and no horizontal overflow at 320x700, 390x844, 1280x800 and 1440x900.
- Mobile navigation behavior, including closing with Escape.
- Accessibility: labels, alt text, keyboard and focus behavior, `rel` on `target="_blank"` links, tap-target size. Contrast and screen-reader quality need manual review.
- Booking, phone and WhatsApp links still go to the expected destination.
- Room-to-image mapping is unchanged and visually correct. Inspect screenshot artifacts when layout changed.

**Double-check when not primary:** For backend or copy-only changes, confirm longer or shorter text did not break a card or button, the heading hierarchy still reads well, and the main call to action is still present.

## Evidence and status vocabulary

Each individual check or command is reported as:

- **PASS**: output or CI evidence confirms success on the stated revision.
- **FAIL**: the check ran and failed; needs diagnosis.
- **BLOCKED**: it could not run because of a concrete environment or access limit. Say why and give the exact owner-side command.
- **NOT RUN**: not selected or deferred; give the reason.

Never claim a check that was not run. Do not claim local execution if only repository API inspection was possible.

## Required final report

1. **Role verdicts**

   | Role | Verdict | What it looked at | Evidence / SHA |
   |---|---|---|---|

2. Change classification and risk.
3. Check results with PASS / FAIL / BLOCKED / NOT RUN, the command or CI run, and the commit SHA.
4. Untested device, browser or environment coverage.
5. Merge recommendation:
   - Any **RED**: recommend against merging, and say why.
   - **YELLOW** only: list each item for the owner.
   - All GREEN or N/A-GREEN with CI passing on the exact head SHA: "No blockers found." Owner approval is still required.

## Project guardrails

- Preserve public URLs, verified hotel facts, prices, timings, room-image mappings and analytics IDs unless explicitly approved and supported by evidence.
- Do not choose between the two configured GA4 IDs without owner confirmation.
- Keep screenshots, traces and HTML reports in CI artifacts or ignored local output, never in the cPanel deployable tree.
- Do not stage `.DS_Store`; do not use indiscriminate `git add -A`.
