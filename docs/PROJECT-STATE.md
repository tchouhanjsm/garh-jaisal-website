# Project state and release gates

Last updated: 27 October 2026.

## Current objective

Deliver a coherent, premium first impression and more direct-booking clarity without replacing the plain-PHP/cPanel stack. The working direction is editorial heritage hospitality: real property photography, sandstone and night-sky contrast, warm light, readable typography, restraint, and local warmth.

## Verified baseline after PR #145

- `main` is at `68c49cb6bd9ed0277a1412a74f759183b201dee8` (PR #145 merged on 27 October 2026).
- PR #145 consolidated the homepage planning path, refined the shared visual rhythm and restored the detailed arrival guide and existing documentation.
- The next vertical slice is room selection: compare the existing seven room records using only their current descriptions and listed features.

## Verified baseline after PR #146

- Latest merged PR: #146 — room comparison dialog, selected-room state, responsive table and comparison actions, merged on 27 October 2026.
- The next phase closes the gap from comparison to enquiry: selected rooms travel into Contact as an allowlisted shortlist and remain intact across safe CAPTCHA/form retry paths.
- No enquiry is created just by comparing rooms. When a guest explicitly selects “Ask about selected rooms,” the room slugs reach Contact and are validated against the existing room registry; only a submitted enquiry is recorded by the existing contact flow.

## Active phase — Room shortlist enquiry handoff

- Branch: `feature/room-shortlist-enquiry-bulk-20261027`.
- Add a two-to-three-room enquiry CTA inside the comparison dialog.
- Validate each incoming slug against the room registry; reject duplicates, unknown rooms and lists outside the supported range.
- Carry trusted shortlist context into the Contact form, prefill a room-specific message, and preserve context on recoverable errors.
- Required gates: PHP 7.4/8.2 Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA. No deployment is included.

## Provisional audit scores

These are engineering judgments, not user-research results or a website valuation:
- Engineering/product foundation: 6.5/10.
- Visual design maturity: 5.5/10 pending live browser/staging screenshots.
- Direct-booking journey: 6/10 pending end-to-end and owner UAT.

## Priority sequence

1. Tighten repeated homepage storytelling and consolidate arrival information into one canonical anchor.
2. Enforce one visual rhythm for typography, spacing, component surfaces, hover/focus and imagery.
3. Inspect Home, Rooms, Packages/Experiences, Dining, Directions and Contact at 320/390/768/1280/1440px.
4. Measure real hero image transfer sizes, LCP/CLS, font loading and cache behavior instead of assuming WebP means fast.
5. Run exact-head CI and inspect screenshots; validate external booking, call, WhatsApp, contact form, CAPTCHA/mail and cPanel behavior on staging.
6. Keep documentation minimal and current; do not use historical sprint narrative as the operating state.

## Release blockers that require owner action

- Confirm the production cPanel PHP version and Apache/.htaccess behavior.
- Approve menu/prices, policies/privacy wording, reception/check-in, room rates and review quotations before publishing them.
- Confirm staging package SHA, server configuration, real CAPTCHA/mail tests and visual review.
- Owner explicitly approves production release.

## Working agreement

A PR should represent a complete, visible vertical slice. Each relevant PR must include exact-head CI evidence, screenshot/page-sheet evidence, public-route/SEO preservation checks, and an honest list of anything not run. Nobody merges or deploys on the owner's behalf.
