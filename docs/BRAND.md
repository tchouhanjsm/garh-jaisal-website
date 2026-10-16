# Garh Jaisal Haveli — Brand and UI Constitution

**Version:** V1 foundation  
**Owner:** Garh Jaisal Haveli  
**Applies to:** public website, guest-facing web UI and first-party marketing templates  
**Source:** Senior Graphic Designer Report, 15 October 2026; current repository implementation and owner-approved tokens

## Brand idea

**A living fort, a family home.** Visuals should hold together monumental sandstone, night sky and architectural scale with warm light, real people and the details of a family-run haveli. Aim for quiet confidence and local warmth, not royal clichés, wedding-card ornament or a generic travel template.

## Design decisions

- Reuse the existing Thar Sunset token system. “Golden City” can be used as the brand-story name, but existing CSS variable names and compatibility aliases remain stable.
- Keep the current logo while its source file, small-size legibility, one-colour reproduction and contrast are reviewed. Do not redraw or replace the mark in this phase.
- Use the existing Source Serif 4 display face and Inter/system sans stack. Do not add font files or weights until licensing, subset size and real rendering are checked.
- Use Fort Night `#14122B`, Stone Parchment `#F7EFE0`, Golden Stone `#D9A441`, Dusk Saffron `#B94A0E`, and Haveli Garnet `#5B1A2B` as the restrained identity palette. Charcoal Ink remains the default text on light backgrounds.
- Saffron is the primary action fill with white text. Gold is a graphic accent, not a button fill. Gold on light backgrounds is not an ordinary small-text colour.
- Peacock functional teal is `#1D7472`, slightly darker than the initial proposal so its contrast against parchment clears 4.5:1.
- Buttons and standard card containers use a 6px radius. A photographic arch is a deliberate image crop, not a replacement for card geometry.
- Keep forms, prices, policies, notices and other dense information on light surfaces—even when the surrounding section uses a dark emotional treatment.
- Keep one strong focal point per viewport and one primary saffron action. Decorative motif must not replace headings, links or meaningful content.
- Existing public routes, canonicals, booking URLs, navigation, both GA4 IDs and session-only Trip shortlist boundaries must remain intact.

## Existing system first

The repository already has `css/tokens.css`, `css/garh-v2.css`, `css/thar-sunset-story.css`, shared `include/page-head.php`, image dimension/optimized-WebP safeguards and responsive Browser E2E coverage. Extend these systems. Do not introduce a parallel stylesheet or an independent analytics setup.

The V1 motif is a small, decorative bastion line at relevant section eyebrows. The source SVG lives at `img/brand/bastion-line.svg`. It is intentionally lightweight and is not a new logo or a full illustration set.

## Accessibility and responsive rules

- Minimum 4.5:1 contrast for the approved normal-text token pairs exercised by `scripts/check-brand-contrast.php`. That script tests representative pairs, not every possible rendered CSS combination; manual contrast review is still required for new surfaces and component states.
- Primary actions and key navigation controls must retain at least 44px hit areas.
- Keep focus indicators visible and keep link meaning independent of colour.
- Honour `prefers-reduced-motion`; use opacity/transform for optional motion and avoid motion-dependent information.
- Review widths 320, 390, 768, 1280 and 1440px for layout/overflow. CI's full page sheet remains at 390 and 1280px.
- No upscaled or soft source image, invented guest review, unconsented guest portrait, unverified price, availability promise or operational policy.

## Photography and performance

Use the strongest real photographs already in the repository first. Any grade/crop change requires a same-image side-by-side visual review and must preserve natural skin tones and food colour. Keep originals and existing source-to-WebP safeguards. Do not introduce autoplay video as a launch requirement; use a poster first and measure the actual payload. Paid photography quantity, budget, usage rights and shoot dates remain owner-approved inputs.

## Release checklist

1. Review desktop/mobile page sheet and open the three highest-impact surfaces selected in the PR.
2. Check contrast contract, focus states, 44px actions, reduced motion and no horizontal overflow.
3. Confirm no public URL, canonical, schema fact, booking destination, GA4 ID or Trip shortlist storage boundary changed.
4. Pass PHP lint, strict image checks, sitemap/publication guards, Browser E2E, package preflight and non-deploying Staging QA on the exact head SHA.
5. Record what is automated versus still requiring visual, live-staging or owner confirmation.

## Deferred on purpose

Full logo redraw, additional logo lockups, font downloads, 60-photo shoot, video loop, fort map, large custom icon set, print/signage rollout and multiple new sub-brands do not block V1. Restaurant mail activation stays gated on confirmed recipients, privacy/retention wording, verified operating details and owner-approved acknowledgement behaviour.

## Restaurant and experience extension (Sprint 34)

- Use the bastion motif for key restaurant section eyebrows and keep table/contact decisions on a light parchment surface.
- Use the architectural crop selectively on the first restaurant gallery image, catalogue media and experience cards. Keep supporting photography rectangular where a crop would obscure useful context.
- Server validation messages must be tied to their field with `aria-describedby`, mark invalid controls, preserve focus recovery and remain readable at mobile width.
- Conversion events may record enquiry start and request attempt only; they must never include names, email addresses, phone numbers, preferred times, dates, number of guests or form/message content.
