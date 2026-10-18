# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file inside every PR with verified main/CI state. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-18 |
| Current phase | Sprint 50 — final responsive image delivery and reproducible browser dependencies |
| Base branch | `main` |
| Verified main SHA | `dc1d7653eecc61fec72489015340d61ae8e52386` (PR #101 merge commit, 2026-10-18) |
| Latest merged PR | [#101 — audit repository backlog and reconcile delivery status](https://github.com/tchouhanjsm/garh-jaisal-website/pull/101) |
| PR #100 image reuse remediation | Merged 2026-10-18 at `b51cbaed3a25af55f5294a5a1a7fe6e67657d6ba`; included in the green PR #101 merge-head run |
| Current-main full Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583892) on `dc1d7653eecc61fec72489015340d61ae8e52386` |
| Current-main Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583828) on `dc1d7653eecc61fec72489015340d61ae8e52386` |
| Current-main package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39112583836) on `dc1d7653eecc61fec72489015340d61ae8e52386` |
| PR #98 final PR head (historical) | `33f452253d0820370a3fd2d9d5d2c1907cfffb81` (separate from merge SHA) |
| PR #99 merge-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179680) on verified main SHA |
| PR #99 merge-head Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179657) on verified main SHA |
| PR #99 merge-head package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179660) on verified main SHA |
| PR #99 merge-head Staging QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39097179737), local/non-deploying dry run only |
| PR #93 PR-head Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39012551591) — exact head `cbf11bd2f84017561f1640c6acac40c04430e75d` |
| PR #93 merge-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39014642234) |
| PR #93 merge-head cPanel package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39014642162) |
| PR #93 merge-head Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39014642158) |
| PR #93 merge-head Browser E2E | [CANCELLED during evidence upload](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39014642303) — artifact upload reached about 1.14 GB and exhausted the 20-minute job window; PR-head Browser E2E passed. |
| PR #90 merged | Merged 2026-10-16 at `a256f22fe31b27a375876c28f3b433ccf009b36d`; all four PR-head workflows passed before merge |
| PR #89 merged | Merged 2026-10-16 at `1d8b02b6d0188a041bc824e30cbcfe565b14686f`; verified starting point for Sprint 41 |
| PR #88 merged | [Merged 2026-10-16](https://github.com/tchouhanjsm/garh-jaisal-website/pull/88) at `5a4a060ab6f74fbddb0fe3143f5929c7d155cab3`; review head `dfbaa2c556bea8bfaabfcdfdf6b2ae51b76a7204` |
| PR #88 Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401576) |
| PR #88 package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401608) |
| PR #88 Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401517) — [evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401517/artifacts/12031996540) |
| PR #88 Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401672) — [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401672/artifacts/12031774700); [evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38867401672/artifacts/12032530309) |
| PR #87 exact-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894128) — head `21bc78c7a3650c646567ca7efd8e4522105945b1` |
| PR #87 exact-head package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894116) |
| PR #87 exact-head Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894207) |
| PR #87 exact-head Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894115) — [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894115/artifacts/12028694758); [evidence bundle](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894115/artifacts/12030090186) |
| PR #86 PR-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127095) |
| PR #86 PR-head package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127092) |
| PR #86 PR-head Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127080) |
| PR #86 merge-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546303) |
| PR #86 merge-head cPanel package workflow (no deployment) | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546333) |
| PR #86 merge-head Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546323) |
| PR #86 merge-head Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341) — [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341/artifacts/12027655875); [evidence bundle](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341/artifacts/12027139666) |
| PR #97 merge-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805415) — `1a385f3c2975c7308849335d412b31f295ea4fd0` |
| PR #97 merge-head cPanel package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805478) — ZIP generated for review only; no upload |
| PR #97 merge-head Staging QA | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805519) — local/non-deploying dry run, not an external host |
| PR #97 merge-head Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805433) — [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805433/artifacts/12107765818); [curated evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39049805433/artifacts/12106848479) |
| Production parity | NOT VERIFIED / observed mismatch: public Home and Directions still show the legacy site; Directions includes transfer fares and arrival-service promises removed from repo main. External staging must be proven before production release. |
| Production URL | https://www.hotelgarhjaisal.com |
| Live-host evidence | No external cPanel staging or production deployment occurred. |
| Owner review | Review the generated responsive-image sheet and homepage-hero screenshots at 320px, 390px, 768px, 1280px and 1440px. Visual sign-off remains separate from CI. |
| PR #95 exact-main Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163687) — `a20640d8e0e4c3a83f34ff21373828b1ec4e6c0e` |
| PR #95 exact-main cPanel package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163492) — package built for review only; no upload |
| PR #95 exact-main Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163561) — local/non-deploying QA, not an external staging host |
| PR #95 exact-main Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163625) — [page sheet artifact](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163625/artifacts/12101465112) (31,501,727 bytes); [curated evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39032163625/artifacts/12101400416) (11,103,398 bytes) |
| Open pull requests at Sprint 48 baseline | None on 2026-10-18 after PR #97 merged |
| PR #96 merge-head Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39036260519) — exact main `7dd4a8bf4c70ea1e8f6cd9a0d5f6cb23daca0472` |
| PR #96 merge-head cPanel package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39036260531) — package generated for review, not uploaded |
| PR #96 merge-head Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39036260546) — local/non-deploying, not an external staging host |
| PR #96 merge-head Browser E2E | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39036260528) — [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39036260528/artifacts/12102915342) (31.5 MB); [curated evidence](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/39036260528/artifacts/12101769369) (11.1 MB) |
| Open PRs at audit baseline | Historical snapshot taken before PR #100 and PR #101 merged; both are now merged. |
| Next action | PR #102 now combines responsive slot sizing, expanded candidate coverage, the full-resolution Gallery lightbox fix, and a committed npm-generated Playwright lockfile (`npm ci` + `npm audit --audit-level=high`). Re-run and fix all checks on the combined latest PR head; image/caption visual approval and owner-only facts remain outstanding. |
## 2026-10-17 — Backend audit remediation in existing PR #91

- Kept changes in PR #91; no new PR, merge or deployment was performed.
- Corrected the Rooms responsive title regression: the newly added shared layout block had an unterminated CSS comment, so the browser ignored the intended layout rules. The comment is closed and the 761–1000px H1 minimum now clears the 33px contract.
- Made the outer site header sticky and reset nested navigation positioning; added browser coverage for Home, Rooms, Restaurant and Contact at 390px and 1280px.
- Implemented in this PR: Rooms hero token/comment regression fix; sticky outer header; always-visible restaurant enquiry form with 30-minute preference, note, consent and unconfirmed status; private JSONL lead records with reference codes and per-IP/phone daily limits; contact lead persistence before mail; owner-gated menu prices; cPanel include-directory denial, index.php redirect, robots/cache updates; exclusions for five verified stale/documentation-only package files; optimized WebP Rooms hero; and first-pass metadata corrections. Exact-head CI is still the gate.
- Last observed workflows before this follow-up: Site Checks, package preflight and non-deploying Staging QA passed at f216be5; Browser E2E failed the Rooms H1 floor. Exact-head checks for current changes are the merge gate.

## Sprint 43 — Responsive image delivery and package integrity (PR #93 merged)

- PR #93 merged on 2026-10-17 at `3fe9073648f0b26f7384fdffd2dc12745f5e2da5`; responsive WebP generation, selected hero/card `srcset`/preloads, and manifest-derived cPanel package integrity are now on `main`.
- The exact PR-head Browser E2E passed at `cbf11bd2f84017561f1640c6acac40c04430e75d`; Site Checks, cPanel package preflight and non-deploying Staging QA also passed on the merge head.
- The additional `main` Browser E2E run was cancelled during artifact upload after the evidence bundle reached about 1.14 GB and exhausted the 20-minute job window. This is an evidence/CI runtime issue, not a test assertion failure; Sprint 44 reduces duplicate uploads while retaining the dedicated full-site page sheet, curated responsive spotchecks and failure-only trace/error artifacts.
- Generated asset review and live cPanel MIME/cache/preload verification remain owner release gates; no cPanel upload or production deployment was performed.


## Phase roadmap

| Phase | Goal | Status |
|---|---|---|
| 0 | Repository hygiene and safe CI baseline | ✓ merged; runtime guards retained |
| 1 | Site-wide audit with prioritised backlog | ◐ continue evidence-led defect fixes |
| 2 | Booking conversion and mobile UX | ◐ shared navigation, booking links and intent events exist; validate real conversion owner-side |
| 3 | Technical SEO foundation | ✓ shared rendered metadata/JSON-LD regression coverage |
| 4 | Local SEO and content depth | ◐ content cluster exists; continue only source/intent-led improvements |
| 5 | Performance and Core Web Vitals | ◐ image inventory exists; controlled Lighthouse/field measurement remains open |
| 6 | Accessibility | ◐ automated image-alt/layout checks exist; manual contrast, keyboard and screen-reader review remains |
| 7 | Form, email and security hardening | ◐ staging mail/CAPTCHA/cPanel behavior remains unverified |
| 8 | Analytics measurement | ◐ intent event wiring merged; live receipt remains owner-side |
| 9 | Staging QA and cPanel handoff | ◐ workflows/guides exist; real owner-run staging verification pending |
| 10 | Monthly improvement loop | ☐ begin after reliable baseline measurement is available |

## Sprint 31 — desert experience photo galleries (PR #80 merged)

- PR #80 merged to `main` at `8a1fe8ab3be1392b9cda82657a580947072075ce` on 2026-10-15 14:58:13 UTC; it integrates nine Sleep Under the Stars photos, expands the Swiss Tent Safari gallery to eleven photos, and adds the curated desert photos to the main Gallery.
- Exact PR-head Site Checks, cPanel package preflight, non-deploying Staging QA and Browser E2E/page sheet passed on `03c20b1dbf5c5053fc1898336c612778257e9a33`; artifacts are linked in the Snapshot. PR #80's live merge state was re-verified before this follow-on.
- No cPanel staging upload or production deployment occurred; the owner still needs to review subjects/captions at 390px and 1280px.

## Sprint 32 — high-intent package discovery and guest-choice clarity (PR #81)

- Baseline is verified main SHA `f9bed798efc99d7aec88098a9116e4e8a0e787c6`, the PR #81 merge commit.
- Improve the catalogue/package guest journey using the current experience/room registries and shared templates: clearer, source-backed choices, consistent contextual links and no false implication of availability or confirmation. The session-only Trip tray remains a shortlist and WhatsApp stays a user-reviewed draft.
- Add meaningful PHP/source and browser regression coverage for responsive catalogue decisions and safe handoff; preserve all public URLs, canonicals, both GA4 IDs, booking destinations and the no-personal-data storage boundary.
- Required exact-head CI: PHP 7.4/8.2 Site Checks including strict images and sitemap, publication checks, cPanel package preflight, non-deploying Staging QA, Browser E2E and 390px/1280px page sheet. Owner visual review and live staging remain separate.
- No server-side lead log, form submission, new prices, availability, policies or live booking behavior.

## Owner-blocked dependencies

- Restaurant R1 remains blocked until opening days/hours, last seating, same-day cut-off, closures/capacity, notification owner/recipient, approved privacy/retention wording, mail configuration and acknowledgement/contact flow are confirmed in `docs/OWNER-FACTS.md`.
- Privacy and booking/cancellation terms remain draft/noindex until owner-approved wording is supplied. Do not invent refunds, deposits, cancellation windows or exceptions.
- Transport quotes/calculators remain blocked on route/rate assumptions and a reviewed model; no estimate should be implied by visual comparisons.
- Review proof, licence/partner/safety claims, font licensing and production performance claims require verified source evidence and owner approval.

## Sprint 33 — brand foundation and accessible UI (PR #82 merged)
- Build on the existing Thar Sunset tokens, shared head, image safeguards and four-viewport E2E; add `docs/BRAND.md`, documented 6px controls/cards, decorative bastion SVG and arch crop for room photography.
- Contrast CI checks representative approved token pairs; Peacock Teal was darkened to `#1D7472` because its original `#1F7A78` measured 4.47:1 on parchment.
- Sprints 34–35 continue in this bulk PR only where they can be safely verified; restaurant mail activation, photography spend and live performance claims remain owner-gated.
- Site Checks, cPanel package preflight and non-deploying Staging QA passed for PR #81's merge SHA. Browser E2E was still running at the start of this batch; refresh the result and new PR #82 exact-head artifacts before approval.
- No merge, staging upload or production deployment has been performed by this implementation.

## Sprints 34–36 — restaurant enquiry, event measurement and visual consistency (PR #83)
- Add accessible relationships for server validation errors, focus recovery, an isolated local PHP form test, and dining intent/attempt events that exclude form values.
- Extend bastion/arch styling to restaurant photos, catalogue product media and experience cards; no runtime image variants are promoted without owner visual review.
- No live mail, personal-data lead store, booking-confirmation event, new price/policy or operations claim is introduced.
- PR #82 main Site Checks, package preflight and non-deploying Staging QA passed at `682e3364491fe5beefba712e2ad053afe9de2a64`; its Browser E2E/page sheet remained in progress at branch start.
- PR #83 exact-head Site Checks, Browser E2E (including form validation), package preflight and non-deploying Staging QA are required before owner review. No merge or deployment by the implementation.
- The first Browser E2E run isolated a focus-recovery race on rejected restaurant requests: the form returns to `#reserve`, and fragment restoration could override focus applied during `DOMContentLoaded`. Recovery is now scheduled after document loading; the exact-head browser workflow must confirm the fix before owner review.

## Sprint 35 — responsive home hero and review evidence (PR #84 merged)

- Based on main SHA `44827e6575cd13c78316dc245d04a8e452b5ec4a`. PR #83's local-Chromium inventory recorded about 2.20 MB of same-origin homepage transfer; this is not production, CDN or field Core Web Vitals evidence.
- Use the existing portrait facade derivative below 768px and existing wide rooftop/fort panorama derivative from 768px upward. Exact tracked asset bytes are 591,152 and 388,404 respectively: a 202,748-byte (~34.3%) smaller desktop hero image; the tests enforce 600 KiB and 400 KiB per-image budgets.
- The art-directed hero keeps explicit dimensions for the selected source, high fetch priority, existing headline/booking CTA, and existing metadata/URL architecture. Only checked-in, same-dimension derivatives are referenced; the 48 resized review candidates remain off runtime.
- Browser tests verify five viewport selections, loading, dimensions, byte budgets, CTA visibility, clearance above the fixed mobile action dock and no horizontal overflow. Visual inspection caught a tablet-width dock overlap that simple visibility assertions did not; the responsive rule and explicit bounding-box regression now keep the action group above the dock. The page sheet waits for local images to decode and adds a 768px review sample on high-intent routes.
- Acceptance remains exact-head PHP 7.4/8.2, strict images, sitemap, security/publication guards, package preflight, non-deploying Staging QA and Browser E2E. No local full-suite run or live cPanel verification is claimed; owner visual review and merge/deploy authority remain unchanged.


## Sprint 36 — overnight comparison and Trip tray accessibility (PR #85 merged)

- Base: verified `main` SHA `fcb1604531a4fe7dfe74838013e5a252a149c8b2`, the PR #84 merge commit; there were no open pull requests at branch creation.
- The overnight comparison now reads its labels and sleep/bathroom facts from the shared experience registry. A source-level regression prevents returning to slug-specific comparison copy.
- Escape closes the open Trip tray and returns focus to the visible trigger. Browser coverage checks this alongside the existing room-plus-desert shortlist and privacy-safe WhatsApp draft.
- PR #84's exact-head Site Checks, package preflight, non-deploying Staging QA and Browser E2E passed; its [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38845914013/artifacts/12023791438) remains available for owner comparison. PR #84 main-push Browser E2E passed: https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38847403964
- PR #85 final-head Site Checks [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38848897965), package preflight [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38848897972), non-deploying Staging QA [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38848898064), and Browser E2E [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38848898092). The [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38848898092/artifacts/12023864764) and [evidence bundle](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38848898092/artifacts/12023879611) are available. No live cPanel staging or production deployment occurred.


## Sprint 37 — restaurant identity and first-party marketing masters (completed in PR #86)

- Base: verified main SHA `b9b15e1db0ff8a440b7a6546775fc4d2f1f8b61f` after PR #85 merged; no open PRs were present when this batch started.
- Add six fixed-size SVG composition masters plus a Canva-oriented workflow note; mark replacement photography and campaign-copy fields, and enforce master count/dimensions/palette/accessibility/source-safety in CI.
- Apply restrained Garnet/Night emphasis to the restaurant hero and 6px geometry to controls/cards; preserve light menu, information and form surfaces and all current contact/booking fallbacks.
- No changes to menu data, prices, hours, reservation/mail activation, public URLs, canonicals, schema facts, GA4 IDs or live host. Owner dependencies in `docs/OWNER-FACTS.md` remain unresolved.
- Exact-head Site Checks, package preflight, non-deploying Staging QA, Browser E2E and screenshot evidence for this new PR are pending. Automated evidence is not owner visual approval.

## Sprint 37 — restaurant identity and marketing kit (PR #86 merged)

- PR #86 merged on 2026-10-16 to `main` at `113e75ab879057b780bde24ce9ec633999892dc6`; no open PRs were present at the Sprint 38 branch start.
- Six guarded SVG marketing masters, Canva workflow guidance, restrained restaurant styling, the template source contract, and Playwright regression checks are merged.
- PR-head Site Checks [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127095), package preflight [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127092), and non-deploying Staging QA [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38853127080). The merge-head Site Checks [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546303), cPanel package workflow [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546333), Staging QA dry run [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546323), and Browser E2E [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341). Merge-head evidence: [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341/artifacts/12027655875), [evidence bundle](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38855546341/artifacts/12027139666).
- No public URL, canonical, schema facts, menu data/prices, restaurant hours, reservation/mail activation or GA4 IDs changed. No external cPanel staging or production deployment occurred.

## Sprint 38 — restaurant menu accessibility and dietary-filter feedback (PR #87 merged)

- PR #87 merged on 2026-10-16; merge commit `4b135980fb9e617313d9939d7601649637b2caaa`, tested PR head `21bc78c7a3650c646567ca7efd8e4522105945b1`.
- The vegetarian filter and category links now provide 44px targets, the shared 3px focus token, correct `aria-current="location"` semantics, and full-menu filter status while preserving booklet pagination.
- Exact PR-head Site Checks, package preflight, non-deploying Staging QA and Browser E2E passed; [page sheet](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894115/artifacts/12028694758) and [evidence bundle](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38860894115/artifacts/12030090186) remain available.
- Menu facts, pricing, service hours, URLs, canonicals, schema facts and GA4 IDs were not changed. No external cPanel staging or production deployment occurred.

## Sprint 39 — curated Gallery and progressive photo pagination (PR #88 merged)

- Base: verified `main` SHA `4b135980fb9e617313d9939d7601649637b2caaa`; no open PRs existed before this batch.
- Curate the current Gallery into 36 existing photos across hotel (9), rooms (12), desert (10) and restaurant (5); move source/alt/crop metadata into `include/gallery-data.php`.
- Add six-at-a-time per-section reveal, responsive architectural/portrait/landscape treatments and stronger touch/focus behaviour while keeping the original full image files and dedicated experience galleries intact.
- Add PHP registry checks and Playwright coverage for no-JavaScript completeness, counts, filtering, pagination, lightbox navigation and overflow at configured responsive widths.
- No public route, canonical, schema fact, price, availability, booking flow, GA4 ID, image file or production dependency changes; implementation stops before merge or deployment.
- Follow-up review coverage: the Browser E2E page sheet now adds Gallery full-page captures at 320, 390, 768, 1280 and 1440px, with all ten Desert photos expanded; a source/alt contact sheet and JSON inventory are produced alongside the screenshots.
- Release evidence must distinguish automated responsive review from the owner’s final visual sign-off. Verify Desert Camp source subjects against the rendered alt captions before treating the visual-review item as fully owner-approved.
- PR #88 merged on 2026-10-16 at `5a4a060ab6f74fbddb0fe3143f5929c7d155cab3`. No cPanel deployment or production change occurred.


## Sprint 40 — shared verified quick facts for high-intent pages (completed in PR #89)

- Base: verified post-PR-88 main merge commit `5a4a060ab6f74fbddb0fe3143f5929c7d155cab3`.
- Add an escaped, semantic `include/ui/at-a-glance.php` renderer and cover it with a PHP contract test. Use existing room registry data for the Home and Rooms room counts; keep facilities qualified as varying by room.
- Apply the shared component to Home, Rooms and Trip Planner. Catalogue facts explain the browser-tab shortlist and reviewed WhatsApp draft without suggesting that an enquiry or shortlist is a confirmed booking.
- Reuse existing tokens and CSS, keep the information card light, and run responsive E2E checks at 320, 390, 768, 1280 and 1440px.
- No route, canonical, schema, booking destination, prices, availability, policies, analytics ID, server-side lead storage, or production dependency changes. Owner controls merge and deployment.

## Backend audit remediation — delivered code scope (PR #91)

- Historical source of findings: the backend debugging report audited main after PR #90. Its remediation landed in PR #91, merged on 2026-10-17; follow-up changes are recorded in the dated sections below.
- The new `docs/BACKEND-DEBUGGING-REMEDIATION-2026-10-17.md` tracks implemented, partial and owner-blocked findings separately.
- Known limitations: no external cPanel/production deployment, no real mailbox delivery proof, no owner-approved restaurant schedule/menu/policy facts, and no broad responsive image derivative generation or deletion of heuristic orphan candidates.
- Exact-head workflow results must be refreshed after the final commit; earlier success from a prior head is not evidence for current changes.

## 2026-10-17 — Metadata/schema follow-up in PR #91

- Completed the remaining metadata length warnings called out in the attached debugging report, including the Blog title, restaurant description, guided-tour description, shared experience records and shared editorial guide records.
- Changed `book-rooms-in-jaisalmer.php` from `Article` to `WebPage` and added a visible breadcrumb plus matching `BreadcrumbList` schema.
- Updated sitemap generation so `lastmod` incorporates the shared experience/editorial data file when a URL's content is driven by that registry, preventing stale timestamps after shared-data edits.
- Broader image derivatives and `srcset`/`sizes`, external cPanel proof, SMTP delivery, owner-approved restaurant schedule/menu and privacy retention remain explicit release gates.


## 2026-10-17 — Sprint 42 responsive follow-up in PR #91

- The 320px browser contract identified an oversized Trip Planner hero. The existing responsive CSS was tightened only at widths up to 380px; button targets remain at least 44px and the four quick facts remain present.
- Shortened the Trip Planner lead/fact copy to reduce mobile wrapping without relying on very small fact text.
- Aligned the Trip Planner hero image with the existing optimized WebP already declared in its image preload. Browser E2E now checks that the preload path matches the browser-selected hero image at each configured viewport.
- Added browser screenshot artifacts for Home, Rooms, Packages, Trip Planner, Dining and Gallery at 320px, 768px and 1440px. The established page sheet remains at 390px and 1280px.
- The current-head CI result must be reviewed before merge. These are repository/browser checks only; they do not verify live cPanel behaviour or production delivery.
- Owner release gates remain: external cPanel staging, actual CAPTCHA/mail delivery, approved menu/schedule/policy facts, and the access, backup, retention and privacy wording for private enquiry storage.


## 2026-10-17 — Sprint 45 Rooms hero regression

- Base: PR #94 merge commit `33b178fdfc40c9c498a857a92d04f5e49e71a3b3`.
- Last full Browser E2E measured the Rooms hero at 735.08px on 390px; the contract ceiling is 720px.
- Replaced the wrapping flex sizing with a two-column action grid for 381–760px; 320px remains stacked.
- Added a regression assertion for same-row actions, 44px minimum targets and content-rail bounds.
- Follow-up visual review found that, at a 320×700 viewport, the fixed mobile contact dock covered the bottom row of the Rooms quick-facts card. The small-screen Rooms hero now removes only the duplicated location/floor note and uses a shorter lead; browser coverage asserts that the full fact card clears the dock.


## Sprint 45 — Rooms hero mobile regression (PR #95 merged)

- PR #95 merged on 2026-10-17 at `a20640d8e0e4c3a83f34ff21373828b1ec4e6c0e`; its 390px two-column CTA layout and 320px quick-facts clearance guard are present on `main`.
- Exact-main Site Checks, deployment-package preflight, non-deploying Staging QA dry run, and Browser E2E all passed on this SHA. The run produced a 31.5 MB page-sheet artifact and an 11.1 MB curated evidence artifact.
- The earlier PR-head browser run on an intermediate commit was cancelled; the successful exact-main Browser E2E above is the authoritative post-merge check.
- These CI/staging-dry-run workflows use the local PHP server and do not prove live cPanel Apache/PHP, MIME/cache, CAPTCHA or mail delivery.
- No cPanel upload or production deployment occurred. New resized image candidates remain review-only until the owner approves visual quality and image-to-page mapping.


## Sprint 47 — arrival, transfer and dining claim accuracy (implementation PR)

- Verified base is PR #96 merge `7dd4a8bf4c70ea1e8f6cd9a0d5f6cb23daca0472`; no open PRs existed at branch start.
- Directions now asks guests to confirm transfer route, vehicle, pickup point, current quote, timing and availability instead of publishing unverified fixed fares or pickup/journey-time estimates. The Fort Parking walk remains approximately 300 metres / 5–6 minutes; the fort entrance remains approximately 200 metres / 3–4 minutes.
- Home and Rooftop Dining copy describe the setting without promising breakfast, an unverified drinks offering or unconfirmed sunrise/all-day meal service; both direct guests to confirm current service times and menu options.
- Playwright guards Directions against removed transfer prices/timing and checks qualified Home and rooftop dining copy. Public URLs, booking destinations, map/contact links and analytics IDs are unchanged.
- Production still serves older content than repository `main` at the time of review. No cPanel upload or production deployment was attempted; release parity must be checked on owner-controlled staging first.


## 2026-10-18 — Final repository audit and release readiness (pre-PR #98 historical record)

- PR #97 merged into `main` at `1a385f3c2975c7308849335d412b31f295ea4fd0`. All four merge-head workflows passed on that previous SHA.
- The prior 2026-10-16 review's PR #91 failure notes are historical. PR #91 and follow-up PRs #95/#97 are merged; the current main CI result supersedes the earlier red runs.
- Sprint 48 prevents the repository's explicitly unconfirmed restaurant menu fixture from appearing in rendered HTML. The menu is shown only when the owner-controlled `owner_confirmed` flag is true and the registry contains items; until then guests can ask for current details through Contact or WhatsApp.
- Added a fallback length check in the restaurant form so server validation does not fatally depend on PHP's optional `mbstring` extension.
- The public production homepage and Directions page were checked on 2026-10-18 and remain on legacy content. This is a deployment-parity blocker; source changes merged to GitHub have not been released on the live host.
- Owner / host blockers remain: HTTPS staging URL/access and exact package SHA; cPanel PHP/extensions/Apache behavior; actual reCAPTCHA and mail delivery; approved privacy and booking/cancellation wording; live restaurant menu and operating schedule; image-source/caption visual sign-off; analytics receipt and production performance review.
- No merge, cPanel upload or production deployment was performed by this implementation.


## Sprint 49 — verified state and browser evidence (2026-10-18)

- Verified main is `e6f2b8094b845ef9a30fe7784c31bd142666cfa6`; PR #98's final PR head was `33f452253d0820370a3fd2d9d5d2c1907cfffb81`.
- Exact-main Site Checks and package preflight passed. Staging QA passed in local dry-smoke mode. Browser E2E remained in progress at the latest check, and no screenshot artifacts had been uploaded; visual QA is therefore not signed off.
- Verified that `include/menu-data.php` stays `owner_confirmed: false` and `restaurant.php` has a pending-menu branch that directs guests to ask about the current menu; no sample menu records should be rendered while the flag is false.
- The browser-test package still lacks a lockfile. Attempted to generate one using npm, but registry access from the current environment failed (`EAI_AGAIN`); no lockfile or unverified install-path change is being committed. Revisit this in a network-enabled environment and commit an actual npm-generated lockfile before switching workflows to `npm ci`.


## 2026-10-18 — Full repository audit follow-up

- Audited all tracked Markdown documents and cross-checked the current source registries, sitemap generator, CI workflows and image-gallery test contracts. Findings and priority order are recorded in `docs/FULL-REPOSITORY-AUDIT-2026-10-18.md`.
- Main baseline remains `1d08a3a4cae91f0b78f2d83957db22fd9d529550`; all nine current-main checks passed. This does not imply external host behavior or manual visual/accessibility sign-off.
- PR #100's first head exposed stale sitemap dates after source-registry edits and a gallery test that still required the Swiss-tent cultural-program image removed by the curation change. The PR branch has been updated to address both; latest-head checks must finish before review/merge.
- Browser dependency reproducibility remains open: no committed npm lockfile and no declared Playwright dev dependency. Generate the lockfile only in a network-enabled environment, then verify `npm ci` before changing CI installation behavior.
