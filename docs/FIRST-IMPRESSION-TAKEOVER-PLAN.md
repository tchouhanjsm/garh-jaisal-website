# First-Impression Takeover Plan — Garh Jaisal Haveli

**Status:** Audit findings and approved next steps, 2026-10-27  
**Repository:** https://github.com/tchouhanjsm/garh-jaisal-website  
**Production model:** Plain PHP on cPanel; owner retains merge and release authority.

## Executive decision

The site has a meaningful engineering foundation: shared PHP UI includes, design tokens, SEO/schema utilities, image optimization scripts, booking and contextual enquiry paths, PHP source contracts, and Playwright coverage. It is not yet possible to certify the user-visible result as premium solely from source inspection. The priority is not more features or isolated CSS patches; it is a cohesive art-directed site with a measurable three-second value proposition and an efficient direct-booking path.

Provisional internal assessment (not user research or a commercial valuation):
- Engineering/product foundation: 6.5/10
- Visual design maturity: 5.5/10, pending live screenshot review
- Direct-booking experience: 6/10, pending journey/UAT verification

## Grounded repository findings

- The homepage hero already uses “Wake up inside Jaisalmer Fort,” “Check rooms & rates,” an internal room-discovery action, and stay/dine/explore facts.
- The homepage contains multiple discovery, story, arrival, rooms, rooftop, desert, gallery, and conversion sections. Review hierarchy and duplicate prominence without throwing away useful facts or SEO content.
- `include/page-head.php` loads `css/garh-v2.css`, optional page CSS, `css/tokens.css`, and `css/conversion-system.css`. The large shared stylesheet has 3,222 lines (~62 KB); `tokens.css` has 859 lines (~21 KB); `thar-sunset-story.css` is ~14.8 KB; `js/garh-v2.js` is ~46.6 KB. This is not proof of a runtime defect, but requires a cascade/ownership map and loaded-CSS measurement.
- The image inventory has source photos and optimized/responsive derivative tooling. Some WebP files are still several hundred KB. Confirm which bytes are downloaded at each viewport; extension alone does not prove optimized delivery.
- Navigation includes Book Now, contact dock (Call and WhatsApp), and a mobile Call/WhatsApp/Book Now bar. Validate overlaps, safe-area clearance, and whether persistent controls compete with content.
- Recent merged work (PRs #127–#140) has improved conversion journeys, room and experience discovery, photos, enquiry context, and contact consistency. Feature completeness should not be mistaken for a finished visual system.
- PR #141 is open at audit time. Its code has a useful custom-itinerary enquiry builder; its own description calls for exact-head Browser E2E and screenshot evidence. Do not merge on diff quality alone. Site Checks and package preflight have passed; do not infer Browser E2E pass from these.
- `docs/` contains 29 files totaling ~337 KB. `docs/SPRINT-PLAN.md` alone is ~112 KB and `docs/FULL-REPOSITORY-DEBUGGING-REVIEW-2026-10-16.md` is ~33 KB. Documentation needs a controlled consolidation, not blind deletion.

## Design direction

Brand idea: “A living fort, a family home.” Retain real property photography, sandstone and night-sky contrast, warm light, quiet confidence, readable editorial type, and local warmth. Avoid generic hotel templates, royal clichés, excessive ornament, false luxury cues, and animation for animation’s sake.

### Homepage first viewport

1. Strong real architectural photo as the focal point; deliberate crop and tested contrast.
2. Short eyebrow: “Heritage stay · Inside Jaisalmer Fort.”
3. Headline: “Wake up inside Jaisalmer Fort.”
4. One concise sentence about the haveli, rooftop dining, and city/desert experiences.
5. Primary action “Check rooms & rates”; secondary action to explore rooms/the haveli.
6. Three concise factual proof points: Stay, Dine, Explore.
7. Do not make the visitor read several cards or a long paragraph before understanding property + location + action.

The first screen must be tested at narrow mobile and desktop sizes. Do not claim these changes are implemented until screenshot evidence exists.

## Phased implementation

### Phase 1 — Visual baseline and current-state evidence
- Generate screenshot/page sheets for Home, Rooms, Packages, a representative experience detail, Dining, Arrival/Directions, and Contact at 320, 390, 768, 1280, and 1440px where feasible.
- Review all sticky/fixed controls, heading wraps, image crops, menu/dropdown states, forms, and terminal CTA areas.
- Record visible defects and measured image/network payloads.
- Confirm exact-head CI status for PR #141; require its Browser E2E and screenshot artifact before merge.

### Phase 2 — Design-system ownership
- Map CSS ownership and load order.
- Set canonical typography, colour tokens, spacing scale, content widths, cards, button variants, form controls, focus states, breakpoints, image aspect ratios, and reduced-motion rules.
- Identify duplicate/overridden selectors; remove only after reference search and screenshot regression.
- Retain vanilla CSS/PHP unless a measured need justifies a dependency.

### Phase 3 — Coherent customer-facing vertical slices
- Home: tighten section hierarchy and avoid repeated promotional emphasis; keep arrival facts discoverable and accessible.
- Rooms: consistent room photo ratios, factual facilities, easy comparison, booking action and room-specific enquiry.
- Experiences: consistent photo/itinerary/inclusions/enquiry order; clearly distinguish published offers from unconfirmed enquiry ideas.
- Dining: atmosphere, verified menu/content, and clear location/enquiry path.
- Directions/Contact: explain Fort vehicle access and final walk; keep contact destinations consistent.
- Each PR must change a meaningful user-visible slice (template + styling + copy + tests as needed), not a one-line cosmetic tweak.

### Phase 4 — Performance, accessibility, and SEO verification
- Measure mobile/desktop Lighthouse and field data if available; document hero-image transfer size, LCP, CLS, blocking CSS/font behaviour.
- Verify responsive srcset/source selection and cache headers on staging.
- Check keyboard, focus, contrast, semantic headings, alt text, reduced motion, touch targets, form error recovery.
- Preserve public URLs, canonicals, schema facts, sitemap inclusion, and existing booking URL/token unless the owner explicitly approves change.

### Phase 5 — Release-quality review
- Exact-head CI passes (PHP versions, strict image/sitemap, package preflight, Staging QA, Browser E2E).
- Inspect screenshot artifacts, not just pass/fail badges.
- Owner or human reviewer signs off at mobile and desktop.
- Perform real staging checks for booking outbound links, telephone/WhatsApp, contact forms, CAPTCHA/mail and cPanel behavior.
- Do not claim production readiness until staging and owner release approval are recorded.

## Acceptance criteria
- First 390px and 1280px viewport immediately reveals property identity, Fort location and primary booking action.
- No horizontal overflow at 320, 390, 768, 1280, and 1440px.
- No critical booking/contact dead ends or misleading booking promises.
- Design tokens and components behave consistently across all live route families.
- Hero-image payload and real loading metrics are measured and recorded.
- Keyboard, focus, contrast, reduced motion, form and alt-text checks reviewed.
- Metadata, canonical, schema, sitemap and public routes remain consistent.
- Exact-head CI, screenshots, and staging smoke test evidence available.

## Documentation cleanup policy

Do not delete the current docs folder all at once. First consolidate information. Preserve only these active files in `docs/`:

1. `README.md` — short project overview, stack, start-here links.
2. `PROJECT-STATE.md` — current main SHA, active PR, current phase, blockers and owner decisions (keep current).
3. `DESIGN-SYSTEM.md` — brand idea, tokens, component rules and responsive standards (merge useful parts of BRAND.md and relevant layout findings).
4. `OWNER-FACTS.md` — only verified business facts and open questions (retain current, do not store secrets).
5. `ARCHITECTURE.md` — PHP include/template model, data registries, SEO/schema, routes, stylesheet ownership (consolidate approved architecture).
6. `DEPLOYMENT.md` — cPanel package creation/checksum, staging checklist, rollback and release approval.
7. `TESTING.md` — local commands and required CI/evidence.

Archive to a tagged Git release or a separate history folder before deletion, if historical incident details might matter. Remove superseded audit snapshots, repeated prompts, duplicated setup instructions, long sprint narratives, stale status docs, and old phase notes only after extracting unique facts into the active files. Never remove executable code, tests, workflows, live route files, or source assets as part of documentation cleanup.

## Explicit non-goals
- No framework migration merely for fashion.
- No redesign by changing only colours/buttons.
- No invented reviews, ratings, rates, availability, features, timings, owner story or unverified claims.
- No URL deletion without owner-approved 301 mapping.
- No cPanel uploads or production deployment from this audit plan.
- No merging PR #141 without all required checks and screenshot review.

## Review decision
Proceed with Phase 1 evidence and design-system ownership before another major cosmetic layer. Then deliver a substantial, cross-page design batch that visibly improves the experience and is supported by tests. Owner retains merge and deployment approval.