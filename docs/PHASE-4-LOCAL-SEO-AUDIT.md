# Phase 4 — Local SEO and Content Relevance Audit

## Purpose

Inventory the 26 current sitemap routes and identify evidence-backed improvements. This is an audit report, not permission to publish unverified claims or alter public URLs.

## Method and limitations

- Source reviewed through GitHub repository files on the Phase 4 branch.
- This session does not have a local PHP server/browser runtime; rendered output and live SERP rankings were not measured here.
- Do not infer that template source text equals final rendered HTML. Shared includes and PHP expressions can supply metadata dynamically.
- Findings below are candidates for rendered validation and owner review, not a completed live-site crawl.

## Initial high-confidence findings

| Priority | URL / component | Observation from source | Why it matters | Next action |
|---|---|---|---|---|
| P1 | `restaurant.php` | Page title begins with a dynamic expression pattern in source review; source excerpt yielded no literal title, and description expression was dynamic. | Could be perfectly valid rendered metadata, or could be malformed if variables are unset. Static inspection alone cannot decide. | Run the existing SEO browser test and inspect rendered title/description for this route. Fix source only if the rendered output is defective. |
| P1 | `blog.php` | Uses variables for title, description and canonical; the homepage of the journal links to the four guide pages. | Dynamic metadata should be checked as rendered output. | Preserve metadata values if valid; verify all four articles remain discoverable and cross-linked. |
| P1 | Individual experience routes | Seven public route files delegate to `include/experience-page.php`; metadata comes from `include/experience-data.php`. | Shared templates reduce duplication but make the data source the authoritative review point. | Validate every sitemap route in the desktop SEO project; avoid editing the thin route wrapper files unless routing is broken. |
| P1 | Guides and itinerary | `jaisalmer-fort-guide.php`, `book-rooms-in-jaisalmer.php`, `desert-safari-guide.php`, and `jaisalmer-2-day-itinerary.php` link to key transactional pages and each other. | This supports user journeys; indiscriminate rewrites risk losing those pathways. | Maintain links to `room.php`, `direction.php`, `roof.php`, `pack.php`, `guided-jaisalmer-tour.php`, and contact/booking CTAs. |
| P1 | Sitemap | Current sitemap contains 26 URLs. Experience detail pages and guide pages coexist alongside hubs. | Potential overlap should be assessed by actual intent, not by URL similarity alone. | Keep URL inventory unchanged; compare title/H1 and rendered copy per page before proposing consolidation. |
| P2 | `robots.txt` / sitemap | Sitemap declares canonical public host `https://www.hotelgarhjaisal.com`; robots disallows mail and thank-you routes plus other utility paths. | Indexing and canonical policy should stay coordinated. | No change proposed; confirm against rendered canonicals and preserve robots intent. |
| P2 | Analytics across page templates | Homepage and some older templates configure both GA4 IDs directly, while newer guide templates iterate configured `analytics_ids`. | Measurement inconsistency may make future conversion analysis difficult, but the intended GA4 property is unknown. | Do not change IDs or analytics wiring without owner confirmation and a test plan. |

## Current sitemap route inventory (26)

1. `/`
2. `/room.php`
3. `/pack.php`
4. `/desert-safari.php`
5. `/guided-jaisalmer-tour.php`
6. `/half-day-jaisalmer-tour.php`
7. `/full-day-jaisalmer-tour.php`
8. `/sunset-camel-safari.php`
9. `/sleep-under-the-stars.php`
10. `/cultural-safari.php`
11. `/swiss-tent-safari.php`
12. `/explore-the-desert.php`
13. `/direction.php`
14. `/roof.php`
15. `/restaurant.php`
16. `/gallery.php`
17. `/about.php`
18. `/contact.php`
19. `/blog.php`
20. `/book-rooms-in-jaisalmer.php`
21. `/jaisalmer-fort-guide.php`
22. `/desert-safari-guide.php`
23. `/jaisalmer-2-day-itinerary.php`
24. `/camping.php`
25. `/car-rental.php`
26. `/city-tour.php`

## Recommended sequence

1. Run CI's rendered SEO check over all sitemap routes and inspect exact logs/warnings/retries.
2. Build a per-route rendered inventory of title, meta description, canonical, H1, indexability and internal-link destinations.
3. Resolve only verified issues. Prioritize genuine missing/duplicate metadata, broken internal links, unsupported content claims, and route mismatches.
4. After owner confirms facts, improve one focused group at a time (hotel/rooms, dining, arrival, desert experiences, guides).
5. Run the Smart Check roles and full relevant regression after each batch.

## Explicitly not changed by this audit

- No public copy or metadata values.
- No public URLs, redirects, sitemap membership or canonicals.
- No room-image mappings or assets.
- No prices, amenities, package inclusions, timings or local factual claims.
- No analytics IDs, form destinations, secrets, or cPanel settings.
