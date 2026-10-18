# Website Repository Readiness — Garh Jaisal Haveli

**Audit date:** 2026-10-18  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA:** `1d08a3a4cae91f0b78f2d83957db22fd9d529550`  
**Latest merged PR:** [PR #99 — reconcile website repository status after PR #98](https://github.com/tchouhanjsm/garh-jaisal-website/pull/99)  
**Open image-remediation PR:** [PR #100](https://github.com/tchouhanjsm/garh-jaisal-website/pull/100)

## Source and CI status

All nine check runs on the verified `main` SHA completed successfully:

| Check | Result | Evidence |
|---|---|---|
| PHP 7.4 compatibility/site checks | PASS | [Site Checks run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680) |
| PHP 8.2 site checks | PASS | [Site Checks run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680) |
| Browser E2E and screenshots | PASS | [Browser E2E run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179657) |
| Deployment package preflight | PASS | [Package run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179660) |
| Non-deploying Staging QA | PASS | [QA run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179737) |
| Brand contrast and token contract | PASS | [Site checks run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680) |
| Marketing template source contract | PASS | [Site checks run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680) |
| Responsive image review | PASS | [Site checks run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680) |
| Owner-reviewed package build check | PASS | [Package run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179660) |

The Staging QA workflow is a local/non-deploying dry run. It does not prove behavior on an external host.

## Open repository work

1. **PR #100 — image reuse remediation:** initial CI exposed stale `sitemap.xml` dates and an outdated gallery-test requirement after a photo was removed from the Swiss-tent accommodation gallery. Both are corrected on the PR branch; wait for every required check to finish on the latest head and inspect the visual evidence before merge.
2. **Reproducible browser dependencies:** `package.json` currently has no declared Playwright dependency or committed lockfile. CI installs `@playwright/test@1.55.0` ad hoc. Generate a real npm lockfile in a network-enabled environment, verify it, and only then switch to `npm ci`.
3. **Image correctness:** path and layout contracts do not prove the subject/crop matches its caption, page intent or alt text. Review actual rendered screenshots/contact sheets and keep hero preloads/social images aligned.
4. **Responsive image optimization:** the reviewed 480/800/1200px derivative set remains review-only; audit source selection and transfer payload by route before approving replacements.
5. **Manual UX/accessibility:** screenshot judgement, keyboard/focus, reduced motion, physical devices, contrast and assistive-technology review remain distinct from automated passes.
6. **Owner-approved facts:** menu/prices/hours, booking/cancellation wording, privacy/data-controller/retention details, check-in/reception facts, guest reviews, room-rate display and certain tour/transfer details remain unresolved in `docs/OWNER-FACTS.md`. Keep affected claims qualified and draft pages unpublished until supplied.
7. **Documentation truth:** the current baseline and outstanding work are reconciled in `docs/PROJECT-LOG.md` and `docs/FULL-REPOSITORY-AUDIT-2026-10-18.md`.

## Content safety

The restaurant menu remains unpublished while `include/menu-data.php` is not owner-confirmed or the approved registry is empty. A dining form is an enquiry, not a confirmed reservation. Do not invent menu items, prices, hours, policies, awards, ratings, route inclusions or guide availability.

## Delivery boundary

This report describes repository source and automated evidence. It does not claim that every image has been manually approved or that every external-host behavior has been tested. The current user instruction is to complete and deliver the PHP website source/ZIP; this audit does not perform hosting changes, staging deployment, production deployment or release tagging.
