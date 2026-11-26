# Guest journey backlog

## Current baseline and priorities (26 November 2026)

PR #177 merged at `97d794201b42989d687d3905c15057d3880f33c7`; final head `6c207d89cf0492d701f1b1711d3bf3909b57a2c8` passed all four workflows. The repository sitemap now contains 42 URLs. Production release SHA and live host behavior are not established.

| Priority | Outstanding work | Acceptance evidence |
| --- | --- | --- |
| P0 | Extend existing route audit to legacy/non-sitemap routes, redirects and asset failures | Route inventory with expected statuses, redirect targets and affected source pages; no accidental public drafts |
| P0 | Strengthen existing SEO route tests | Direct HTTP 200, exact production canonical and matching social URL for every sitemap route; new PR CI pending |
| P0 | Validate real-host CAPTCHA, email delivery and authorised lead recovery | Owner-approved staging tests, private data protection, recovery evidence; no live test enquiries |
| P1 | Review mobile guest journeys and reduce duplicate content | Screenshot and touch interaction review across stay, dining, safaris, car tours and Rajasthan journeys; preserve existing room comparison and menu search |
| P1 | Audit query-dependent anchors and external links | GET-only audit of safe public destinations; distinguish transient third-party failures from reproducible defects |
| P2 | Validate tour variations, car circuits, Rajasthan routes, packages and trade enquiries | Existing registry and itinerary builder reused; publish only owner-supported facts, no duplicate product pages |
| P3 | Measure commercial search intent and conversion events | Search Console and both GA4 properties when accessible; no guest PII; distinguish handoff from confirmed booking |
| P4 | Verify production performance, accessibility and release reliability | Exact-SHA staging package, visual review, field data where available, regression checks and rollback evidence |

Do not mark a task complete solely because a commit exists. Separate repository implementation, exact-head CI, staging validation and production release. No automatic merge or deployment.

## Post-177 development batch (PR #178; exact-head checks pending)

- P0: Reject array-shaped contact POST values before casting or external CAPTCHA verification. Restore the missing Car & City Tours hero contact topic. Extend the existing route audit to query-dependent anchors on published pages and persist a redacted external destination inventory in CI.
- P1: Custom itinerary drafts now respect browser validity, including positive traveller counts and India-local travel dates. The Rajasthan enquiry exposes a clear service-choice field.
- P2: Car-only and car-with-accommodation requests use the existing enquiry builder. No new tour operating facts, routes, prices or confirmed bookings are implied.
- P3: Distinct stable CTA IDs for the two itinerary WhatsApp handoffs reuse existing privacy-safe intent tracking and both configured properties. Actual GA4 event receipt and commercial demand remain unverified.
- P4: Stream the lead log instead of loading the complete file into memory; reject symlink/hard-link log targets and attempt rollback on partial append failure. Tests cover both request/event aliases and normal storage, malformed contact POSTs, itinerary validation, service choice and CTA metadata.

The log scan remains linear in record count; measure real retention and workload before introducing an index or database. Host permissions, storage fault recovery, actual mailbox delivery, CAPTCHA, staff lead retrieval, mobile visual review and production performance still require validation. External destinations are inventoried, not checked for uptime. No production deployment.

## Previous release evidence

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

## Follow-up hardening (merged in PR #177)

- Shared error canonical and page-head path now agree; non-public experience routes remain noindex.
- Strict date syntax before PHP parsing; synthetic malformed-date and fractional-guest regression checks.
- Identical validated restaurant enquiries are deduplicated under the private storage lock for five minutes, including fresh sessions. No second email is attempted for a duplicate; the receipt advises contact when notification is unconfirmed. Distinct or later requests remain subject to existing rate limits.
- Menu search includes published descriptions and exact dietary labels; category navigation and print remain available.

## Tour discovery and conversion (merged in PR #177)

- Restored three legacy URLs as enquiry-led pages using the existing renderer, nav, catalogue and sitemap; no new fixed service promises. Custom desert avoids a duplicate fixed safari, Longewala remains feasibility-led, Rajasthan uses the approved car/car-with-hotels model.
- Five travel shortcuts on Packages; optional signature holiday and themed walk/day-trip ideas use the existing allowlisted custom itinerary form. No new booking or trip system.
- Pending: verify all new and existing routes against the exact PR head; confirm actual tour operating details, photos, prices, hours, production mail/CAPTCHA, accessibility, mobile layout, and real analytics. Expand approved offerings only after operational validation.

## Regression and guest-copy follow-up

- Update the source contract to verify the corrected shared canonical path, keep metadata within the existing SEO bounds, and shorten repetitive commercial copy without removing the main guest journeys.
- Add isolated storage idempotency checks, filtered print checks and sitemap-derived same-site URL and anchor audit. PR #177 exact-head CI passed; this is not a live crawl.

## Follow-up: scope and regression alignment

- Restored the open-ended itinerary copy contract after Site checks failed on PHP 7.4 and 8.2. The six examples are explicitly not an exhaustive list.
- Guided tour comparison remains limited to the actual guided city tours; private day trips and Rajasthan travel remain separate enquiry journeys. Custom desert options have explicit enquiry wording.
- Updated live/unknown/hub contact tests and expanded the existing editorial media audit to include all three restored pages. Exact-head CI passed for PR #177; the same acceptance gate applies to subsequent changes.
