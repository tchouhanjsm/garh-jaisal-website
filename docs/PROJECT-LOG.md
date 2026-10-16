# Garh Jaisal Haveli — Project Log

> Repository state is authoritative for tracked code. Update this file inside every PR with verified main/CI state. Owner retains merge and deployment authority.

## Snapshot

| Item | Value |
|---|---|
| Last updated | 2026-10-16 |
| Current phase | Sprint 35 — home hero art direction, asset budget and screenshot evidence (PR #84 in review) |
| Base branch | `main` |
| Baseline commit | `44827e6575cd13c78316dc245d04a8e452b5ec4a` (PR #83 merge commit) |
| Last merged PR | [#83 — restaurant enquiry and visual consistency](https://github.com/tchouhanjsm/garh-jaisal-website/pull/83) |
| PR #83 main push Site Checks | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38842194108) |
| PR #83 main push package preflight | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38842194073) |
| PR #83 main push Staging QA dry run | [PASS](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38842194083) |
| PR #83 main push Browser E2E/page sheet | In progress at PR #84 start: [run](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38842194060) |
| Production URL | https://www.hotelgarhjaisal.com |
| Live-host evidence | No external cPanel staging or production deployment occurred. |
| Owner review | Inspect PR #84's homepage hero at 320/390/768/1280/1440px; compare `/roof.php`, `/catalogue.php` and `/room.php` in its screenshot artifacts. |
| Next action | Review PR #84 exact-head CI, hero screenshots and 390/768/1280 page sheet; decide on merge only after visual review. No live deployment. |

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

## Sprint 35 — responsive home hero and review evidence (PR #84 in review)

- Based on main SHA `44827e6575cd13c78316dc245d04a8e452b5ec4a`. PR #83's local-Chromium inventory recorded about 2.20 MB of same-origin homepage transfer; this is not production, CDN or field Core Web Vitals evidence.
- Use the existing portrait facade derivative below 768px and existing wide rooftop/fort panorama derivative from 768px upward. Exact tracked asset bytes are 591,152 and 388,404 respectively: a 202,748-byte (~34.3%) smaller desktop hero image; the tests enforce 600 KiB and 400 KiB per-image budgets.
- The art-directed hero keeps explicit dimensions for the selected source, high fetch priority, existing headline/booking CTA, and existing metadata/URL architecture. Only checked-in, same-dimension derivatives are referenced; the 48 resized review candidates remain off runtime.
- Browser tests verify five viewport selections, loading, dimensions, byte budgets, CTA visibility and no horizontal overflow. The page sheet also waits for local images to decode and adds a 768px review sample on high-intent routes.
- Acceptance remains exact-head PHP 7.4/8.2, strict images, sitemap, security/publication guards, package preflight, non-deploying Staging QA and Browser E2E. No local full-suite run or live cPanel verification is claimed; owner visual review and merge/deploy authority remain unchanged.
