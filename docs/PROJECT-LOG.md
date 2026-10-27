# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Latest state — 27 October 2026

| Item | Value |
|---|---|
| Baseline after PR #148 | `95e43decf5833115c632adebfeee78ee1dd3bbae` |
| Active implementation | Sprint 70 — Trip Contact fallback + experience-detail handoff |
| Scope | Mixed Trip options, grouped room comparisons and registry-driven experience details |
| Release boundary | No live enquiry, cPanel upload, external staging deployment or production deployment |

## Completed guest-journey milestones

- **PR #141 — custom itinerary discovery:** open-ended itinerary enquiry builder, reviewable WhatsApp draft, contextual Contact fallback, SEO/sitemap coverage and responsive/browser contracts.
- **PR #143 — experience section navigation:** accessible, conditional links to sections on live experience detail pages.
- **PR #144 — editorial headings:** stronger experience-page display typography while preserving routes and facts.
- **PR #145 — homepage and arrival hierarchy:** reduced repeated promotion, improved page rhythm and restored detailed arrival guidance.
- **PR #146 — room comparison:** two-to-three-room comparison dialog and preservation of existing room/rate destinations.
- **PR #147 — room shortlist to Contact:** registry-validated multi-room enquiry with safe retry continuity.
- **PR #148 — room comparison to Trip:** grouped room options enter the shared tab-only Trip tray while separate experience, transport and dining choices remain selectable.

## Sprint 70 — Unified Trip Contact + experience detail handoff

The Trip tray offers Contact as a secondary route while keeping WhatsApp as the primary, reviewable draft. Contact carries selected IDs only; server-side registries provide the labels. Optional travel dates and traveller count remain local to the WhatsApp draft.

The Contact resolver also recognises the grouped `room-options-…` ID introduced in PR #148 and rebuilds its label from the room registry. The selected room-comparison choice therefore remains intact as one stay item during the Contact handoff and recoverable form errors.

Desert and guided-tour hubs expose an explicit “More details” route to each experience's full page. Both hubs stay registry-driven; no new tours, prices, timings, inclusions or availability claims are invented.

## Verification boundary

- Exact-head workflow results are authoritative. Re-check CI on the resolved commit; stale or cancelled runs are not passes.
- This connector session can inspect and update GitHub source but has not run the PHP or Playwright suites locally.
- Green CI is not proof that the live cPanel host, CAPTCHA/mail delivery or production routes have been verified.

## Remaining owner-controlled release blockers

| Priority | Item | Status |
|---|---|---|
| P1 | Host validation | HTTPS staging, deployed package SHA, PHP/Apache behavior, CAPTCHA and mail delivery require real-host verification. |
| P1 | Business-content approval | Menu/prices/hours, policies/privacy, check-in/reception, room rates and verified review quotations need owner approval. |
| P2 | Human design/accessibility review | Review exact-head artifacts at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology. |
| P1 | Production release | No deployment performed; owner approval remains mandatory. |
