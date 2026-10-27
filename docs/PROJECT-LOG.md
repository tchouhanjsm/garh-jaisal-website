# Garh Jaisal Haveli — Project Log

> Tracked source and exact-commit CI are authoritative. Owner retains production release approval.

## Latest state — 27 October 2026

| Item | Value |
|---|---|
| Baseline after PR #149 | `fb1dc5a2d39e3fd12fe06b1d7681a3929386a36e` |
| Active implementation | Sprint 71 — Dining → shared Trip shortlist → Contact |
| Scope | Canonical dining option across restaurant/menu, rooftop and catalogue entry points |
| Release boundary | No reservation submitted, cPanel upload, staging deployment or production deployment |

## Recent guest-journey milestones

- **PR #145 — homepage and arrival hierarchy:** reduced repeated promotion, improved page rhythm and restored detailed arrival guidance.
- **PR #146 — room comparison:** two-to-three-room comparison dialog and preservation of existing room/rate destinations.
- **PR #147 — room shortlist to Contact:** registry-validated multi-room enquiry with safe retry continuity.
- **PR #148 — room comparison to Trip:** grouped room options enter the shared tab-only Trip tray while separate choices remain selectable.
- **PR #149 — Trip to Contact + experience details:** mixed shortlist Contact fallback, room-option server resolution and full experience-detail links from desert/guided-tour hubs. Site Checks, Browser E2E/screenshots, package preflight and non-deploying Staging QA passed on its exact head.

## Sprint 71 — Dining to shared Trip

The dedicated restaurant/menu page and rooftop experience page use the same validated Trip item as the catalogue: `rooftop-dining`, “Rooftop dining”, destination `roof.php`, group `dining`. The add action is in the first-screen controls, allowing guests to combine dining with a room or experience without hunting for the catalogue first.

The Trip route remains an enquiry aid, not a reservation. Contact receives only the selected option ID and the server supplies its label. Guests retain the dedicated table request form and WhatsApp draft, while unapproved menu items and prices stay hidden. The sitemap generator also tracks `include/catalogue-data.php` when deriving the catalogue page's lastmod and updates lastmod for the touched dining pages.

## Verification boundary

- Run and inspect exact-head PHP 7.4/8.2, Browser E2E/screenshots, package preflight and non-deploying Staging QA.
- No local PHP/Playwright suite was run during this connector-driven change.
- Green CI does not prove live cPanel, CAPTCHA/mail delivery or actual reservation availability.

## Remaining owner-controlled release blockers

| Priority | Item | Status |
|---|---|---|
| P1 | Host validation | HTTPS staging, deployed package SHA, PHP/Apache behavior, CAPTCHA and mail delivery require real-host verification. |
| P1 | Business-content approval | Menu/prices/hours, policies/privacy, check-in/reception, room rates and verified review quotations need owner approval. |
| P2 | Human design/accessibility review | Review exact-head artifacts at 320/390/768/1280/1440px, keyboard/focus, contrast, reduced motion and assistive technology. |
| P1 | Production release | No deployment performed; owner approval remains mandatory. |
