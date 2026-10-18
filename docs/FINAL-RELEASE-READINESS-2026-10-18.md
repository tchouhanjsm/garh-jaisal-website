# Website Repository Readiness — Garh Jaisal Haveli

**Audit date:** 2026-10-18  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Verified main SHA:** `dc1d7653eecc61fec72489015340d61ae8e52386`  
**Latest merged PR:** [PR #101 — audit repository backlog and reconcile delivery status](https://github.com/tchouhanjsm/garh-jaisal-website/pull/101)  
**Image reuse PR:** [PR #100 — merged](https://github.com/tchouhanjsm/garh-jaisal-website/pull/100) at `b51cbaed3a25af55f5294a5a1a7fe6e67657d6ba`

## Verified source baseline

All nine checks passed on current `main` at `dc1d7653eecc61fec72489015340d61ae8e52386`:

| Check area | Result | Evidence |
|---|---|---|
| Browser E2E and page screenshots | PASS | [Browser E2E](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583892) |
| PHP 7.4 / PHP 8.2 site checks | PASS | [Site checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583828) |
| Brand contrast and token contract | PASS | [Site checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583828) |
| Marketing template source contract | PASS | [Site checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583828) |
| Responsive image review | PASS | [Site checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583828) |
| Package preflight | PASS | [Package checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583836) |
| Non-deploying repository QA | PASS | [QA checks](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583831) |

These results prove the checked-in source and local CI harness at this SHA. They do not mean every public image has human-approved composition, every screen-reader/device combination has been tested, or actual external mail/configuration has been exercised.

## Remaining source work

1. **Responsive image delivery:** PR #100 diversified image assignments, but several high-payload Gallery, Journal, About, Rooms and tour photos still had no resized candidate or no slot-specific `sizes`. The current follow-up adds more reviewed source candidates and aligns the Journal featured-image preload with its actual responsive source selection.
2. **Reproducible browser dependencies:** Playwright `@playwright/test@1.64.1` is pinned in `package.json`; this branch’s next CI run will generate the npm lockfile from the actual registry and publish it as a temporary artifact. The generated file must be committed and the temporary bootstrap step removed before this task is complete.
3. **Image correctness:** automated paths, alt and aspect-ratio contracts are useful, not a substitute for full-size image/caption judgement. Original sources remain unchanged.
4. **Manual UX/accessibility:** automated browser tests do not replace manual keyboard/focus, reduced-motion, assistive-technology and device review.
5. **Owner-confirmed facts:** current menu items/prices/hours, booking/cancellation wording, privacy controller and data-retention details, check-in/reception facts, room-rate display, authentic guest reviews and certain tour/transfer details remain unresolved in `docs/OWNER-FACTS.md`. Keep public promises qualified and draft/policy pages unpublished until the owner supplies approved wording.
6. **Lead/mail safety:** source contains validation, abuse controls and private lead storage, but production delivery/access/retention decisions cannot be inferred from CI. Do not write guest messages or secrets to public logs or test live guest mail.

## Publication integrity

The restaurant menu remains gated while `include/menu-data.php` is not owner-confirmed or the approved registry is empty. A dining form is an enquiry, not a confirmed reservation. Do not invent opening hours, menu prices, policy terms, tour inclusions, awards, ratings, guest reviews or transport fares.

## Delivery boundary

This is a source-repository readiness report. The target deliverable remains the complete PHP website source/ZIP. No merge, upload or external deployment is performed by this PR.
