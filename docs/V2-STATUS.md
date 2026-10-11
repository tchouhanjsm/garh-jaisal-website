# Garh Jaisal Website V2

## Current state

This is the merged V2 working copy. Production remains untouched.

### Implemented
- Shared V2 header, footer, stylesheet and menu script.
- Production navigation: Home, About Us, Rooms, Packages, Directions, Gallery, Contact Us, Blog, Book Now.
- Mobile Call / WhatsApp / Book bar and desktop contact dock.
- Homepage, rooms, rooftop dining, desert packages, individual desert experiences, arrival, gallery, about, contact, blog and Jaisalmer guide.
- Rooms rendered from `include/rooms-data.php`.
- Page-specific CSS for packages, directions, gallery, rooftop, contact, guide and desert detail pages.
- Canonical production URLs and unique metadata retained across public pages.
- Package, gallery, guide and room structured-data improvements.
- Shared site configuration without committing the reCAPTCHA secret.
- Large images reduced while preserving existing filenames and paths.

### Existing URLs retained
- `index.php`
- `about.php`
- `room.php`
- `pack.php`
- `direction.php`
- `gallery.php`
- `contact.php`
- `book-rooms-in-jaisalmer.php`

### Added/expanded support URLs
- `roof.php`
- `blog.php`
- `explore-the-desert.php`
- `sunset-camel-safari.php`
- `sleep-under-the-stars.php`
- `cultural-safari.php`
- `swiss-tent-safari.php`
- `camping.php`
- `car-rental.php`
- `city-tour.php`

## Open before staging
1. Add the production/staging reCAPTCHA secret to `include/site-config.local.php` or the server environment.
2. Confirm current package inclusions/prices and arrival wording.
3. Decide whether both GA4 measurement IDs are required.
4. Run PHP lint, link/image checks and 390px/desktop visual QA.
5. Test one contact enquiry on staging without changing live mail credentials.

## Phase 3 implemented
- Added three editorial SEO pages: Jaisalmer Fort Guide, Desert Safari Guide, and Jaisalmer 2-Day Itinerary.
- Expanded `blog.php` into a real internal content hub.
- Added `css/article.css` for editorial/article layouts.
- Added contextual links between guides, arrival, rooms, guided tours and desert experiences.
- Added BlogPosting + WebPage + BreadcrumbList markup to the new guides.
- Removed the Google Fonts CSS `@import`; pages now load the font stylesheet in the document head.
- Booking URL is now sourced from `include/site-config.php` on the main V2 pages that previously hardcoded it.
- Footer contact details are now sourced from `include/site-config.php`.
- Hardened the contact form with CSRF, honeypot, optional phone, recipient validation and 30-second session rate limiting.
- Normalized arrival distances to approximately 300 m / 5–6 minutes from Fort Parking and approximately 200 m / 3–4 minutes from the fort entrance.

## SEO direction
The content layer is intentionally compact and site-specific. The goal is to build topical depth around Jaisalmer Fort, Jaisalmer tours and desert experiences without creating large sets of near-duplicate keyword pages.
