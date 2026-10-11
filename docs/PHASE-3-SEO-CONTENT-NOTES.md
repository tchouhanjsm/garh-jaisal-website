# Phase 3: SEO + content architecture

Implemented in this phase:

- Expanded the Blog/Journal hub with three new editorial pages.
- Added `jaisalmer-fort-guide.php`.
- Added `desert-safari-guide.php`.
- Added `jaisalmer-2-day-itinerary.php`.
- Added `css/article.css` for editorial layouts and comparison tables.
- Added `BlogPosting`, `WebPage`, and `BreadcrumbList` JSON-LD to the new articles.
- Added contextual internal links from articles to rooms, arrival, guided tours and desert experiences.
- Kept exact desert clock times out unless operationally confirmed.
- Removed Google Fonts `@import` from `css/garh-v2.css` and moved font loading into page heads to avoid CSS-import blocking.
- Made the V2 footer use `site-config.php` for phone, WhatsApp and email values.
- Hardened the contact form with CSRF, honeypot, optional phone, rate limiting and config-driven recipients.
- Normalized key arrival distances to approximately 300 m / 5–6 minutes from Fort Parking and approximately 200 m / 3–4 minutes from the fort entrance.

## Search strategy

The article pages are intentionally first-hand and practical rather than written around a target word count. Google Search guidance emphasizes helpful, original, people-first content and warns against mass-producing content only to attract search traffic.

## Before production

Confirm all time-sensitive facts: desert route inclusions, guide language, current entry-fee treatment, hotel contact recipients, reception/late-arrival handling, and current booking-engine URL.
