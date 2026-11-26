# Guest journey backlog

## In review

- Checked the 39 public sitemap URLs for HTTP errors and canonical mismatches against cPanel release `863f47f`. All passed. The release candidate also passed an internal-link and anchor check across 106 destinations.
- Table enquiries keep a reference after refresh or a repeated submit. Notification failures leave the request recoverable in guest lead storage.
- Both dining pages share dish search, printable full menus and a full-menu fallback.
- Rajasthan quote requests support airport return, sample routes and car-only or car-with-hotels options. Travel trade enquiries use the existing contact form. Quote handoffs send a WhatsApp intent event without enquiry details. The mobile action bar stays clear of the active form.

## Next

- Verify CAPTCHA, mailbox delivery and authorised staff lead recovery against the release candidate in cPanel staging.
- Compare room, desert and day-tour journeys on mobile. Keep one Heritage Room booking category.
- Confirm pickup, operating times, inclusions, guide languages, access requirements and pricing with the hotel before publishing additional tours or combined packages.
- Confirm any agency rates, commissions or allotments with the hotel before publishing trade terms.
- Check real GA4 events on both properties. Compare booking-engine handoffs, contact enquiries and WhatsApp clicks by guest journey.
- Recheck redirect, indexing, performance and accessibility after the release, and automate any regression found.

## Follow-up hardening (pending CI)

- Shared error canonical and page-head path now agree; non-public experience routes remain noindex.
- Strict date syntax before PHP parsing; synthetic malformed-date and fractional-guest regression checks.
- Identical validated restaurant enquiries are deduplicated under the private storage lock for five minutes, including fresh sessions. No second email is attempted for a duplicate; the receipt advises contact when notification is unconfirmed. Distinct or later requests remain subject to existing rate limits.
- Menu search includes published descriptions and exact dietary labels; category navigation and print remain available.

## Tour discovery and conversion (pending CI)

- Restored three legacy URLs as enquiry-led pages using the existing renderer, nav, catalogue and sitemap; no new fixed service promises. Custom desert avoids a duplicate fixed safari, Longewala remains feasibility-led, Rajasthan uses the approved car/car-with-hotels model.
- Five travel shortcuts on Packages; optional signature holiday and themed walk/day-trip ideas use the existing allowlisted custom itinerary form. No new booking or trip system.
- Pending: verify all new and existing routes against the exact PR head; confirm actual tour operating details, photos, prices, hours, production mail/CAPTCHA, accessibility, mobile layout, and real analytics. Expand approved offerings only after operational validation.
