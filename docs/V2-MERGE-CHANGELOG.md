# Garh Jaisal Haveli V2 merge changelog

This working copy combines the current V2 visual system with the strongest additions from the Cursor redesign.

## Merged
- Exact production navigation: Home, About Us, Rooms, Packages, Directions, Gallery, Contact Us, Blog, Book Now.
- Cursor room data model and reusable room rendering.
- Rooftop dining page and styling.
- Individual desert experience pages and supporting tour styling.
- Practical Jaisalmer guide and Blog index.
- Categorized gallery and page-specific styles.
- Shared contact dock and mobile Call / WhatsApp / Book bar.
- Reduced-motion, focus, Escape-to-close and outside-click navigation behavior.
- Room WebPage + BreadcrumbList JSON-LD.
- Shared server configuration for booking/contact/reCAPTCHA settings.
- reCAPTCHA secret removed from public source. Staging/production must provide it through `include/site-config.local.php` or `GJ_RECAPTCHA_SECRET`.
- Large retained legacy images were resized while keeping their filenames and paths.
- Package detail buttons now use neutral `View experience` wording.

## Deliberately not merged
- Cursor's alternate navigation labels such as Rooftop, Tours and Arrival.
- Cursor's mobile submenu implementation, which kept the submenu visible at mobile widths.
- Cursor's hard-coded reCAPTCHA secret.
- Legacy Bootstrap/jQuery components as dependencies of V2 pages.

## Production facts to confirm before deployment
- Current package pricing/inclusions.
- Current reception and arrival instructions.
- Final analytics property usage if both GA4 IDs are intentional.
- Server-side reCAPTCHA secret configuration.

## Phase 3: SEO/content + hardening
- Expanded the Journal into a useful travel-guide hub.
- Added Jaisalmer Fort Guide, Desert Safari Guide and Jaisalmer 2-Day Itinerary.
- Added article-specific CSS and BlogPosting schema.
- Removed the Google Fonts CSS import from `garh-v2.css` and moved font loading to page heads.
- Centralized booking URL use on the remaining main V2 pages.
- Centralized footer contact/WhatsApp values.
- Restored CSRF + honeypot + rate limiting on the contact form and made phone optional.
- Updated arrival distance wording for consistency.
- Added homepage WebPage entity to the existing Hotel/WebSite graph.
