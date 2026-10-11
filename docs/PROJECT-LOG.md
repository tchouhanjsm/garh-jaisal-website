# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file in each PR after the owner confirms the merged state.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-11 |
| Current phase | Phase 0 — repository CI and hygiene |
| Base branch | `main` |
| Baseline commit | `f03dbfde09144a23614b4f96c3a2c58f94d1f553` |
| Last merged PR | None yet; PR #1 is open and not merged |
| Open PR | [#1 — Establish PHP CI and repository checks](https://github.com/tchouhanjsm/garh-jaisal-website/pull/1) |
| CI on main | No workflow on `main` yet; branch CI passed on run [#3](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38115235778) |
| Production URL | https://www.hotelgarhjaisal.com |
| Next action | Review branch `feature/repo-ci-baseline`; run local PHP lint and site checks; push and open PR |

## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and a safe CI baseline | ◐ in progress |
| 1 | Site-wide audit with a prioritised backlog | ☐ |
| 2 | Booking conversion and mobile UX | ☐ |
| 3 | Technical SEO foundation | ☐ |
| 4 | Local SEO and content depth | ☐ |
| 5 | Performance and Core Web Vitals | ☐ |
| 6 | Accessibility | ☐ |
| 7 | Form, email and security hardening | ☐ |
| 8 | Analytics measurement | ☐ |
| 9 | Staging QA and cPanel handoff | ☐ |
| 10 | Monthly improvement loop | ☐ |

## Current batch — `feature/repo-ci-baseline`

- **Goal:** Introduce pull-request PHP/site validation and make local review standards explicit.
- **Changes authored on branch:** GitHub Actions workflow, pull-request template, stricter site checker diagnostics, ignore rules for local and backup artifacts, and clearer server-side enquiry recipient configuration.
- **Not changed:** Public page copy, page URLs, imagery, CSS presentation, production hosting, and DNS.
- **Validation:** GitHub Actions run #3 passed PHP lint and `php scripts/check-site.php --strict-images`. Local run and browser smoke tests at 390px and 1280px remain pending.
- **Important limitation:** GitHub API write access can create/update tracked text files, but cannot run PHP or a browser in this environment. No test has been claimed as passed.
- **Owner action:** Review the branch diff and workflow PHP version. Do not merge until local checks and CI are green.

## Findings and backlog

| ID | Priority | Finding | Next step |
|---|---|---|---|
| B-001 | P0 | CI workflow was not present on initial `main` tree | Workflow added on PR #1; CI green on branch; merge after owner review |
| B-002 | P1 | Site checker relied on source heuristics for metadata | Improve diagnostics; preserve manual review for dynamic pages |
| B-003 | P1 | Sitemap contains no `<lastmod>` values | Add dates only when justified by Git history |
| B-004 | P1 | Two GA4 IDs are loaded in page scripts | Confirm property ownership/use before selecting one |
| B-005 | P1 | Enquiry recipients must be configured outside public source | Owner/developer confirms actual recipients privately on server |
| B-006 | P2 | Tracked `.DS_Store` and `img/loader.html` appear in repo | Confirm candidates and remove via a dedicated hygiene diff |
| B-007 | P2 | Legacy header/footer and a backup header remain | Prove no pages use them before removing them |
| B-008 | P1 | Experience content and prices are operationally sensitive | Confirm prices, timings, inclusions and language availability before publishing |

## Non-negotiable conventions

- Branches use `feature/<short-name>`; one theme per branch.
- Conventional commits: `feat:`, `fix:`, `test:`, `chore:`, `docs:`, `content:`.
- Keep existing public URLs. URL retirement requires an owner-reviewed 301.
- Never commit `include/site-config.local.php`, mail credentials, server-only keys or passwords.
- Do not invent hotel facts, prices, inclusions, distances, schedules, testimonials, ratings or awards.
- Images are owner-supplied assets; do not rename or delete them without explicit need and proof.
- The owner is the merge authority. Do not call a branch merged or deployed until the owner does so.

## Owner decisions still needed

- Confirm whether both GA4 measurement IDs are intentional and which property should receive conversions.
- Confirm actual enquiry-recipient email addresses for private staging/production configuration.
- Confirm the cPanel PHP version before treating CI's selected version as a compatibility guarantee.
- Confirm current experience inclusions, prices and operational descriptions before public release.
