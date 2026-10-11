---
name: smart-check
description: Select and run the smallest sufficient set of website checks based on changed files and risk. Use for code review, CI failures, SEO, frontend/responsive, PHP/backend, security, forms, analytics, and release readiness in the Garh Jaisal Haveli PHP website.
---

# Smart Check Skill

## Purpose

Choose checks from the actual change surface, run checks in layers, and report evidence without claiming checks that were not run. This skill coordinates existing project checks; it does not replace CI or owner review.

## Required workflow

1. Read `docs/PROJECT-LOG.md`, `docs/SMART-CHECK-MATRIX.md`, `package.json`, and the relevant CI workflows before selecting checks.
2. Inspect the diff and classify every changed path into one or more check types in the matrix.
3. Start with low-cost deterministic checks, then run targeted tests, then the full relevant regression suite.
4. For failures, determine whether the failure is a real defect, a test defect, or an environment limitation. Fix the root cause; never weaken a valid assertion merely to make CI green.
5. Re-run the failed check and its relevant regression group after every fix. Before recommending merge, verify CI against the exact latest PR head SHA.
6. Report each check as **PASS**, **FAIL**, **BLOCKED**, or **NOT RUN**, with command or CI run, exact commit SHA when available, and a short evidence-based explanation.
7. Keep screenshots, traces, HTML reports, and other generated evidence in CI artifacts or ignored local output. Never add them to the cPanel deployable website tree.
8. Never merge or deploy. The repository owner retains those decisions.

## Check selection rules

- Always run the repository's PHP lint and `php scripts/check-site.php --strict-images` in CI for changes that affect PHP, includes, routing, assets, or site-wide configuration.
- Run Playwright route smoke and responsive overflow checks for changes to templates, shared includes, CSS, navigation, booking links, images, or layout.
- Run rendered technical SEO checks for metadata, canonical URLs, H1 structure, sitemap routes, and JSON-LD changes.
- Run accessibility checks for semantic structure, labels, keyboard operation, focus states, alt text, contrast, and target/rel behavior. Treat source-level heuristics as leads until rendered output confirms them.
- Run security checks for request handling, form processing, mail delivery, escaping, redirects, secrets/config, dependencies, and external script changes. Never print secrets or send real enquiries during CI.
- Run analytics/conversion checks when tags, booking actions, phone/WhatsApp links, thank-you flows, or conversion events change. Do not choose between the two configured GA4 IDs without owner confirmation.
- Run full route and multi-viewport browser regression before release, or whenever shared shell/layout/site-wide CSS changes.
- If a command cannot run in the available environment, mark it **BLOCKED**, explain why, and identify the exact owner-side command needed. Do not imply it passed.

## Two-pass review

**Pass 1 — correctness and risk**
- Check changed files against the intended scope and requirements.
- Look for broken paths, incorrect PHP behavior, unsafe output handling, missing error handling, secrets, accessibility regressions, SEO regressions, and unsupported hotel claims.

**Pass 2 — regression and operations**
- Confirm tests cover the changed behavior and relevant adjacent behavior.
- Check public URL and image mapping stability, cPanel/PHP compatibility assumptions, artifact handling, configuration requirements, and rollback implications.

## Required final report

Summarize:
- Change classification and risk.
- Checks selected and why.
- Results table with status, evidence/commit SHA, and failure/blocker details.
- Any untested device/browser/environment coverage.
- Merge recommendation only when evidence supports it; owner approval is still required.

## Project guardrails

- Preserve public URLs, verified hotel facts, prices, timings, room-image mappings, and analytics IDs unless explicitly approved and supported by evidence.
- Never invent testimonials, ratings, awards, operational details, package inclusions, or dates.
- Do not stage `.DS_Store`; do not use indiscriminate `git add -A`.
- Do not claim local execution if only repository API inspection was possible.
