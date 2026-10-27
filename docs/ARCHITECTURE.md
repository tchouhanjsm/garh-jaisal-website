# Application architecture and public-route rules

## Stack and deployment boundary

Plain PHP on cPanel/shared hosting. There is no frontend build step. JavaScript and CSS are first-party/vanilla. Do not introduce a framework or dependency without a measured reason.

## Core sources

- `include/site-config.php`: public site URL, room-booking destination, phone/email/WhatsApp and analytics IDs. Private values belong in ignored `include/site-config.local.php` or environment variables.
- `include/page-head.php` and `include/schema.php`: title, description, canonical, robots policy, social metadata, shared Hotel/WebSite JSON-LD and page schema.
- `include/header-v2.php`, `include/footer-v2.php`, `include/nav-data.php`: shared site chrome and one navigation source.
- `include/rooms-data.php`, `include/experience-data.php`, `include/catalogue-data.php`, `include/menu-data.php`: room, experience, trip catalogue and menu registries.
- `include/ui/*`: reusable hero, breadcrumbs, quick facts, CTAs, FAQs, product/experience cards, carousels, enquiry checkpoints and trip-tray components.
- `css/garh-v2.css`: broad base styles. `css/tokens.css`: shared type, spacing, content rail and component normalization loaded after route CSS. `css/conversion-system.css`: conversion/FAQ/decision components. `css/thar-sunset-story.css`: scoped brand-story treatment. Avoid another stylesheet without assigning ownership or consolidating first.
- `js/garh-v2.js`: shared interactions; other JS files are feature-specific.
- `scripts/generate-sitemap.php`: sitemap source of truth/checker. `scripts/check-site.php`: static/source and asset checks.

## Routes and content state

- Existing `*.php` routes are public surfaces; sitemap and publication state determine indexability.
- Draft/hidden Content Studio records must not appear as published pages, menu links or sitemap entries. A draft is not a bookable public offer.
- Keep metadata, canonical, robots, structured data, navigation, sitemap and visible page state consistent.
- Never delete or rename an indexed URL without an owner-approved 301 mapping.
- The custom itinerary builder prepares an enquiry message in the visitor's browser; it does not reserve a tour, create a server lead or store the draft.
- Room booking stays on the configured external booking engine. A tour/contact enquiry is not a room reservation.

## Implementation rules

- Keep facts in one registry/config instead of duplicating them across templates.
- Escape dynamic text and attributes for HTML output.
- Reuse shared UI partials rather than copying markup.
- CSS load order: base `css/garh-v2.css`, optional route stylesheet(s), shared `css/tokens.css`, then `css/conversion-system.css`.
- Responsive-image candidates are managed by shared helpers/manifests. Check actual selected `currentSrc`; do not assume a WebP extension proves fast delivery.
- Preserve analytics privacy boundaries; never send names, emails, phone numbers or free-text enquiry content to GA4.

## Safety and release authority

Never include server-local secrets, passwords, guest PII, workstation paths or cPanel credentials in Git. Tests must not submit a real enquiry or trigger production mail. GitHub workflows validate and produce artifacts; they do not upload to cPanel. The owner approves merge, real staging actions and production releases.
