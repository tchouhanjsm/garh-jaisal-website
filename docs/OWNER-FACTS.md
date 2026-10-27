# Owner facts — Garh Jaisal Haveli

Last reviewed: 27 October 2026. This is the single source of owner-confirmed facts and unresolved business questions. Do not put passwords, CAPTCHA secrets, private lead data, or server-local configuration in this public repository.

## Confirmed facts

- **Property:** Garh Jaisal Haveli is inside Jaisalmer Fort, Rajasthan. Vehicles cannot enter the Fort, so the final approach is on foot through the Fort lanes.
- **Website stack:** Plain PHP on cPanel/shared hosting. The owner is the only person who approves merges and production releases.
- **Analytics:** Retain both approved GA4 IDs: `G-0C646S1FQ0` and `G-6S5PCEVXH4`.
- **Guest contact source of truth:** `include/site-config.php` holds public phone, email, WhatsApp, booking destination and analytics configuration. Preserve current destinations unless the owner explicitly approves a change. Server-only values belong in ignored `include/site-config.local.php` or environment variables.
- **Guide languages:** English, Hindi, Bengali, Italian, French, Spanish and German. Confirm availability for the requested date; do not promise additional languages without approval.
- **Vehicle arrangements:** SUV and sedan arrangements may be requested for Rajasthan and Delhi. Confirm route, vehicle, pickup, driver and availability per booking.
- **Swiss tent facilities:** King-size bed, attached bathroom with hot/cold shower, tea/coffee maker, toiletries, study table, full-length mirror and towels. Confirm any specific property's current setup before promising exceptions.
- **Fort walk:** The proposed focus is Jain temples inside Jaisalmer Fort, the Palace Museum and a heritage street walk. Price and guide-language availability are confirmed through WhatsApp for the requested date.
- **Official social profile:** `https://www.instagram.com/garhjaisal/`.
- **Restaurant directions:** Use the owner-supplied Garh Jaisal's Kitchen Maps destination recorded in restaurant configuration; do not substitute a guessed location.
- **Desert Camp photos:** Owner-provided assets are stored in `img/Desert_Camp/`. Review each photo and caption mapping; do not restore removed or unlicensed imagery.

## Never publish as confirmed without owner approval

- Room rates, live availability, cancellation terms, check-in/check-out times, early arrival, luggage storage, fixed reception hours, late-arrival assistance or meet-and-assist.
- Restaurant menu prices, operating hours, closed days, last seating, same-day cut-off or table availability.
- Tour prices, precise timings, route/stop lists, permissions, wildlife sightings, guide availability, vehicle availability or inclusions.
- Guest reviews, star ratings, guest counts, founder/family history, and any claim that cannot be traced to a verified source.
- Proposed itinerary themes are conversation starters, not bookable products. A custom itinerary is an enquiry until the hotel confirms feasibility, visitor rules, transport/guide, timings, fees and final price.

## Open owner decisions

1. **Unconfirmed tours:** For Longewala and Rajasthan-wide trips, confirm routes, stops, duration, vehicle/capacity, guide language, fees, meals and pickup. Keep pages draft/noindex until approved.
2. **Distinct experiences:** Confirm whether the legacy “explore the desert” route is a separate product or should redirect to a surviving approved offer.
3. **Rates and policies:** Confirm whether “from” room prices may be displayed, exact cancellation/booking policy wording, and privacy/data-controller/retention wording. Policy pages remain noindex/off-navigation until approved.
4. **Arrival:** Confirm actual check-in/check-out and reception hours, early-arrival/luggage options, and any late-arrival process. Do not publish fixed times or promises before approval.
5. **Dining:** Confirm the live menu and prices, opening hours, closed days, last seating, same-day cut-off and party-size constraints before enabling any reservation-like promise.
6. **Reviews and media:** Supply publishable review text with source/date/permission, and exact public Instagram post/reel URLs if they should be embedded.
7. **Hosting:** Confirm the production cPanel PHP version and Apache behavior before release. CI compatibility is not proof of the production runtime.
8. **Transfer estimates:** Confirm current airport/station/rickshaw routes, providers, prices and travel-time estimates before publishing any exact numbers.

## Safe handling

When a fact is blank or uncertain, ask the owner or say it must be confirmed for the guest's date. Never infer business promises from old copy or unverified third-party text. Keep private leads and server secrets out of Git; use staging for mail/CAPTCHA tests, and never send test enquiries to live guest addresses.
