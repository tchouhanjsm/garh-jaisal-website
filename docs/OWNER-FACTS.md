# Owner facts (single place to answer)

Fill in what you know. AI assistants read this file and must not guess anything that is blank. Update the date when you change an answer. Do not put passwords or secrets here (this repo is public).

## Decided
| Topic | Answer | Date |
|---|---|---|
| Google Analytics IDs | Keep both: G-0C646S1FQ0 and G-6S5PCEVXH4 | 2026-10-13 |
| Menu and page structure | Approved (see `docs/SITE-ARCHITECTURE.md`) | 2026-10-12 |
| Duplicate and orphan pages | Hide unclear ones temporarily; review at the end | 2026-10-12 |
| Swiss tent facilities | All Swiss tents have a king-size bed, attached bathroom with hot/cold shower, tea and coffee maker, toiletries, study table, full-length mirror and towels. | 2026-10-14 |
| Guide languages | English, Hindi, Bengali, Italian, French, Spanish and German; ask before promising any additional language. | 2026-10-14 |
| Car services | SUV and sedan arrangements across Rajasthan and Delhi; drivers can communicate in Hindi and English. Confirm route, vehicle and availability for each booking. | 2026-10-14 |
| Instagram profile | `https://www.instagram.com/garhjaisal/` | 2026-10-14 |
| Garh Jaisal's Kitchen Maps destination | `https://maps.app.goo.gl/28gGJ6tX8xoanQ9D8`; use this destination for the restaurant directions QR. Owner also supplied the Google Maps embed URL. | 2026-10-14 |
| Desert Camp imagery | Owner-provided image assets are in `img/Desert_Camp/`; visually review subject/caption mapping before release. Do not restore removed or unlicensed images. | 2026-10-14 |

## Needed to unblock pages
| # | Question | Answer | Unblocks |
|---|---|---|---|
| 1 | Longewala tour: route and stops, distance, duration, start time, vehicle and capacity, guide language, fees included?, meals?, pickup | | `longewala-tour.php` |
| 2 | Rajasthan tours: which routes, single or multi-day, price shown or "on request" | | `rajasthan-tours.php` |
| 3 | Is `explore-the-desert` a different full-day product from the sunset safari? If yes, how? | | restore or redirect |
| 4 | Show "from" prices on room cards? If yes, per room and the wording ("from ₹X per night, taxes extra"?) | | Rooms |
| 5 | Cancellation and booking policy (your words) | NEEDS OWNER INPUT: owner will supply approved policy wording. Keep policy pages noindex/off-navigation until the text is provided and approved. | `booking-policy.php` |
| 6 | Privacy: who is the data controller, which email handles requests | | `privacy.php` |
| 7 | Check-in and check-out times, early-arrival access and luggage storage | NEEDS OWNER INPUT: confirm official times and whether early check-in or luggage storage can be arranged. Do not publish a specific time or promise before approval. | Rooms, Directions, schema |
| 8 | Real guest reviews you may quote (text, first name, source, date) | | Home, Rooms |
| 9 | Rooftop kitchen: opening hours, is a table booking by phone/WhatsApp only? | NEEDS OWNER INPUT: confirm current service hours and whether online table requests are enabled or guests should contact the hotel directly. Do not publish meal times or imply a table is reserved until approved. | Dining |
| 10 | Reception hours and late-arrival process (the site previously showed 7:00 AM to 10:30 PM: confirm) | NEEDS OWNER INPUT: confirm current reception hours and whether meet-and-assist or luggage help can be arranged outside those hours. Do not state a fixed advance-notice rule unless approved. | Directions |
| 11 | Hosting: cPanel PHP version, can the repo be deployed through cPanel Git? | | Sprint 3 |
| 12 | Photos missing for any page (list pages where you want new images) | Owner-provided Desert Camp photos are available in `img/Desert_Camp/`; continue to request page-specific images where the current library is insufficient. | Design |
| 13 | Which researched Jaisalmer itinerary themes may become actual offerings? Approve each theme and confirm route/stops, duration, vehicle, guide, access/permissions, fees, inclusions and price | | Car & City Tours and future detail pages |
| 14 | Which exact Instagram posts/reels should be embedded on the restaurant page? | NEEDS OWNER INPUT: profile URL is confirmed, but specific public post/reel URLs have not been supplied. Keep a lightweight profile link until direct URLs are approved. | `restaurant.php` |
| 15 | Are the current restaurant menu items and prices still accurate? | NEEDS OWNER INPUT: the current `restaurant.php` menu data needs confirmation against the live menu before staging. | `restaurant.php` |


## Research-led tour ideas — not approved offers

These are candidates for review, not a promise that Garh Jaisal Haveli currently operates or can arrange them. Keep public copy framed as an enquiry until the owner approves each option and its operational details. See `docs/JAISALMER-TOUR-RESEARCH.md` for official destination sources and suggested validation.

- Havelis and architecture route.
- Jain temples and Fort architecture visit.
- Gadisar Lake / Bada Bagh / Vyas Chhatri photography route (may require separate outings).
- Kuldhara heritage and desert landscape visit.
- Akal Wood Fossil Park or Desert National Park nature route (separate options; wildlife is never guaranteed).
- Longewala War Memorial history excursion (visitor requirements and travel logistics must be verified).
