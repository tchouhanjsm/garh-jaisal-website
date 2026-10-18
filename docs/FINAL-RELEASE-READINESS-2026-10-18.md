# Website Repository Readiness — Garh Jaisal Haveli

**Audit date:** 2026-10-18  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA:** `e6f2b8094b845ef9a30fe7784c31bd142666cfa6`  
**Latest merged PR:** [PR #98 — gate unapproved menu data and reconcile final release gates](https://github.com/tchouhanjsm/garh-jaisal-website/pull/98)  
**PR #98 final head (not merge SHA):** `33f452253d0820370a3fd2d9d5d2c1907cfffb81`

## Website-source status

PR #98 is merged. The restaurant page withholds unconfirmed menu categories, dish names, descriptions and prices while the owner-controlled menu flag is false or the approved registry is empty, and gives guests a direct-enquiry fallback. A dining form submission remains an enquiry, not a confirmed table reservation.

## Exact-main workflow evidence

| Workflow | Result | Evidence |
|---|---|---|
| Site Checks | PASS | [Run 39092305671](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39092305671) |
| Browser E2E and screenshots | In progress at audit time | [Run 39092305698](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39092305698); no screenshots available at the time checked |
| Deployment package preflight | PASS | [Run 39092305682](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39092305682) |
| Staging QA workflow | PASS | [Run 39092305685](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39092305685); local dry-smoke path |

## Findings

- **Confirmed website guard:** keep the restaurant menu unpublished until owner confirmation. Do not publish sample menu content or create guessed prices/hours/policies.
- **Browser evidence:** the main Browser E2E run was still executing the responsive test step; screenshot output had not yet been produced. Passing source checks is not visual approval.
- **Dependency reproducibility:** `package.json` has no declared Playwright dependency or lockfile; workflows currently install Playwright `1.55.0` ad hoc. An npm lockfile must be generated and verified in a network-enabled environment before changing workflows to `npm ci`.
- **Responsive baseline:** keep the existing 320px, 390px, 768px-selected, 1280px and 1440px checks. Fix genuine rendered defects rather than weakening assertions.

## Scope boundary

This is a repository and website-code report. It does not assert that screenshot evidence was visually approved, nor claim that a remote website has changed. No deployment work or guest-facing fact changes are part of this batch.

## Next website engineering action

Re-check [the exact-main Browser E2E run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39092305698), download and inspect its screenshot evidence when available, and generate a proper npm lockfile in a network-enabled environment. Then handle demonstrated UI defects with regression coverage.
