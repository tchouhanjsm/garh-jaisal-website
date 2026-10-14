# Layout and Information-Architecture Review: Garh Jaisal Haveli

Basis: the V2 files (header/footer includes, every page's section order, internal links, sitemap), re-verified against `main` on 2026-10-12. This is a **structure** review. Images were removed from the zip, so visual polish (colour, spacing, photography) must be reviewed from staging screenshots in the Phase 2 batches.

---

## 1. Current structure (as built)

```
Header (all pages): Home · About Us · Rooms · Packages · Directions · Gallery · Contact Us · Blog · [Book Now]
Mobile: hamburger + bottom bar (Call · WhatsApp · Book Now)     Desktop: floating Call/WhatsApp dock
Footer: brand blurb · Visit · Contact · Plan your stay (Rooms, Rooftop dining, Restaurant & menu, Desert experiences, Guided tours, Gallery, Travel guides)

Home ─ hero · intro · rooms · dining · desert · arrival · gallery · final CTA
About ─ hero · story · what the house looks after · rooftop · team · plan the rest · CTA
Rooms (room.php) ─ hero · intro · room cards by floor · things to know · CTA
Packages (pack.php, hub) ─ hero · category map · intro · Desert Safari block · Guided Tours block · evening choices · route planner · planning · CTA
   ├─ desert-safari.php ─ sunset-camel-safari · sleep-under-the-stars · swiss-tent-safari · cultural-safari · explore-the-desert
   ├─ guided-jaisalmer-tour.php ─ half-day · full-day
   └─ camping.php · car-rental.php · city-tour.php      ← NOT linked from anywhere
Dining ─ roof.php (rooftop) · restaurant.php (menu)      ← not in the header menu
Directions (direction.php) ─ hero · overview · parking to door · ways to reach · access · reception · FAQ · more · CTA
Gallery · Contact (no hero, H1 inside a section) · Blog hub → fort guide · desert safari guide · 2-day itinerary
Landing page: book-rooms-in-jaisalmer.php (not in header or footer)
```

## 2. Findings (verified in the files)

| # | Priority | Finding | Evidence | Fix |
|---|---|---|---|---|
| L1 | P0 | ~~Dining is missing from the header menu.~~ **Resolved before Phase F12.** | `include/nav-data.php` defines the top-level Dining link to `roof.php`; `roof.php` and `restaurant.php` both set `$activePage = 'dining'`. The Rooftop page links to `restaurant.php#menu`. | No additional navigation change; validate the rendered layout in Browser E2E screenshots. |
| L2 | P0 | ~~3 pages are orphans.~~ **Resolved in current source; recheck whenever hubs change.** | `camping.php` is linked from `desert-safari.php`; `car-rental.php` and `city-tour.php` are linked from the Car & City Tours group in `include/nav-data.php`. | Keep inbound-link regression checks; no URL deletion or redirects. |
| L3 | P0 | **No policy pages.** There is no privacy policy and no cancellation or booking-terms page, yet the site has a contact form, GA4 tracking and a third-party booking engine. | No `privacy`, `terms` or `policy` file; no matching text in any page. | Add privacy and booking/cancellation pages, linked in the footer. Needs your real policies. |
| L4 | P1 | ~~Menu is flat and crowded.~~ **Navigation is now grouped in the shared data registry.** | `include/nav-data.php` defines About and Stay submenus, Dining, and a Packages mega-menu with Desert Tours and Car & City Tours groups; the shared header renders the registry. | Reassess fit, tap targets and dropdown visibility using current four-viewport screenshots rather than source-only claims. |
| L5 | P1 | **Overlapping dining pages.** `roof.php` (rooftop) and `restaurant.php` (menu) have near-identical purposes, and `roof.php` has only one H2. | Section outlines. | One clear role each, or merge; cross-link both ways. |
| L6 | P1 | **Overlapping experience pages:** `pack.php`, `desert-safari.php`, `explore-the-desert.php` and `cultural-safari.php` all introduce desert options. Several have only one inbound link. | Inbound-link count: explore-the-desert 1, half-day 1, full-day 1. | Define one hub per topic; make the others clearly distinct detail pages. |
| L7 | P1 | **7 different hero patterns:** `page-hero`, `package-hero`, `experience-hero`, `restaurant-hero`, `article-hero`, `about-hero`, `gallery-hero`. | Section class names. | One hero component with variants. |
| L8 | P1 | **Contact page breaks the pattern:** no hero, H1 sits inside a plain section. | `contact.php` outline. | Use the standard hero. |
| L9 | P2 | **Label mismatch:** menu says "Blog", footer says "Jaisalmer travel guides", the page H1 says "Practical Jaisalmer travel guides". | Header, footer, `blog.php` | Pick one name ("Journal" or "Guides") and use it everywhere. |
| L10 | P2 | ~~Dining active state is inconsistent.~~ **Resolved.** | Both `restaurant.php` and `roof.php` set `$activePage = 'dining'`, matching `include/nav-data.php`. | No change required unless rendered-browser evidence finds a regression. |
| L11 | P2 | **`book-rooms-in-jaisalmer.php` is a landing page with no navigation entry**, and its H1 is dynamic or empty in source. | Outline | Decide: keep as a campaign page (linked from home and rooms) or fold it into rooms. Check the H1 renders. |
| L12 | P2 | **Responsive CSS is thin:** `garh-v2.css` is 1,562 lines with only 4 `@media` rules, and many legacy CSS files remain. | `grep @media` | Audit at 390, 768 and 1280px in Phase 2 and Phase 5. |
| L13 | P2 | **No visible rates or "from" price** anywhere in the structure; `tariff.php` is blocked in robots. | `robots.txt`, outlines | Owner decision: show "from ₹X" on room cards? |
| L14 | P2 | ~~No FAQ on Rooms, Dining or Experiences pages.~~ **Resolved in Sprint 8.** | Shared visible FAQ component is used on Rooms, rooftop dining, menu, Packages, Desert Tours, Car & City Tours, guided tours, Fort walk, car arrangements and camping. | Keep Browser E2E parity checks between each visible Q&A and its FAQPage JSON-LD. |

## 3. Proposed structure

**Header (desktop):** logo (home) · Rooms · Dining · Experiences ▾ · Directions · Gallery · About · Journal · **Book Now**
Utility row or right-side links: Contact · Call.

```
Experiences ▾
  Desert Safari         → desert-safari.php   (sunset camel, sleep under the stars, swiss tent, cultural, camping)
  Guided Jaisalmer Tours→ guided-jaisalmer-tour.php  (half-day, full-day, city walk)
  All experiences       → pack.php
  Car for arrival/desert→ car-rental.php
```
**Mobile:** hamburger opens a grouped full-screen menu (Stay · Eat · Explore · Plan · About); the bottom bar of Call / WhatsApp / Book stays.
**Footer:** add Privacy, Booking and cancellation policy, and Contact; keep the NAP block.

**Rules:** every page is within 2 clicks of the home page; every page has a breadcrumb (except home); no orphan pages; one hero component; one primary CTA per screen (Book Now), with WhatsApp or Call as secondary.

### Page templates (section order)

**Home:** hero (H1 + Book Now + WhatsApp) → quick-facts strip (inside the fort · rooms · rooftop · desert arranged here) → rooms → dining → experiences → how to arrive → gallery → real guest reviews (only if you supply them) → final CTA.

**Rooms:** hero → short intro → room cards (photo · capacity · bed · 3 key amenities · "Check availability") → what's included → good to know → FAQ → CTA.

**Dining (roof.php, restaurant.php):** hero → rooftop story and photos → featured dishes → menu → timings and booking → FAQ → CTA. Link the two pages to each other at the top.

**Experiences hub (pack.php):** hero → pick by time or interest → cards (duration · what is included · "Ask us") → how to book → FAQ → CTA.

**Experience detail:** hero → at a glance (duration, group, what's included) → the experience, step by step → photos → who it suits → related experiences → FAQ → CTA.

**Directions:** hero → quick answer ("300 m from Fort Parking, about 5–6 min on foot") → map → by car, train, air → the last stretch (cobbles, stairs) → reception and late arrival → FAQ → CTA.

**Gallery:** hero → category tabs (rooms, rooftop, fort, desert) → grid → CTA.

**About:** hero → story → what we look after → team → CTA.

**Journal (blog hub):** hero → featured guide → grid of guides → CTA. **Article:** breadcrumb → H1 → intro → table of contents → body → FAQ → related stays and tours → CTA.

**Contact:** hero → phone, WhatsApp, email, address → form → map → reception hours.

### Shared components to standardise (one include or one CSS block each)
Hero (variants) · Breadcrumb · Quick-facts strip · Card (room / experience / article) · CTA band · FAQ accordion · Related-links block.

## 4. Backlog mapping

These findings are delivered through the batches in `docs/SITE-ARCHITECTURE.md` section 9. They are tracked there and in `docs/PROJECT-LOG.md`; this table maps findings to batches.

| Finding | Batch |
|---|---|
| L1 Dining missing from the menu, L4 crowded menu, L9 label mismatch, L10 active state | A |
| L7 hero patterns, L8 contact page pattern, L12 thin responsive CSS | B, F, G |
| L5 dining overlap, L6 experience overlap, L2 orphans, L11 landing page | D |
| L3 policy pages | H (needs owner wording) |
| L13 "from" prices, L14 FAQs | owner decision, then F or G |

## 5. Prompts for ChatGPT (use with the roles in `AI-ROLES-PROMPTS.md`)

**Layout review of the real site (upload screenshots at 390px and 1280px, plus the files):**
```text
[FRONTEND] Page layout review. I uploaded: header-v2.php, footer-v2.php, garh-v2.css,
<page>.php and screenshots of the page at 390px and 1280px. Also read
docs/LAYOUT-REVIEW.md (my structure review) and treat its findings as known.
Return: (1) what the screenshots show that the structure review cannot (spacing,
type scale, image treatment, contrast, alignment, density), (2) a findings table
(P0/P1/P2 | Section | Problem | Why it matters for bookings | Fix), (3) proposed
section order for this page, matching the template in LAYOUT-REVIEW.md section 3
unless you can justify a change. Do not write code yet.
```

**Batch 1: navigation and footer (blueprint Batch A):**
```text
[FRONTEND] Implement the new navigation from docs/LAYOUT-REVIEW.md section 3: Rooms,
Dining, Experiences dropdown (keyboard and touch accessible), Directions, Gallery,
About, Journal, Contact as a utility link, and Book Now. Home is reached through the logo.
Grouped mobile menu. Footer gets Contact, Privacy and Booking policy links (use
placeholders that point to pages I will add; do not invent policy text). Set a
correct $activePage on every page. Keep the existing Call/WhatsApp/Book bar. Max 10 files.
```

**Batch 2: orphan and overlap resolution (blueprint Batch D):**
```text
[SEO] Decide, with reasons, what to do with camping.php, car-rental.php, city-tour.php,
explore-the-desert.php, cultural-safari.php, roof.php and restaurant.php: keep and link,
merge, or redirect (301). Read each file I upload first. For each: target search intent,
which hub links to it, the exact anchor text, and what content is unique. Then give
the file changes, the sitemap changes and the redirects needed.
```

**Batch 3: hero and page templates (blueprint Batches F and G, one page group per PR):** use the visual brief in `docs/SETUP-GUIDE.md`, adding: "Follow the section order for this page type in LAYOUT-REVIEW.md section 3."


## Revalidation after PR #36 — 2026-10-12

The original findings above were written before the recent navigation and conversion work. L1, L2, L4 and L10 have been reconciled against current `main` source as marked in the table. They should not be reopened without new rendered/browser evidence. Remaining content/policy questions—especially live menu items, prices and dining hours—are not authorization to publish guesses; verify with the owner before changing public facts.
