# Site Architecture and Design Blueprint: Garh Jaisal Haveli

Status: **approved by the owner on 2026-10-12** (menu, Dining and Gallery placement, duplicate/orphan handling). This document changes no code. Build it in the PR batches in section 9, one batch per PR.
Basis: the V2 files (`include/header-v2.php`, `experience-data.php`, `experience-page.php`, `rooms-data.php`, `pack.php`, `garh-v2.css`, all page outlines). Read together with `docs/LAYOUT-REVIEW.md`.

---

## 0. Owner decisions (2026-10-12)

1. **Menu approved** as in section 3, including Dining as a top-level item and Gallery under About.
2. **Duplicate pages are merged properly.** A merge keeps one surviving page and gives the retired URL an owner-reviewed 301 (existing convention in `docs/PROJECT-LOG.md`). No page is deleted without its redirect.
3. **Irrelevant pages are temporarily hidden, not deleted**, and reviewed at the end. *Hidden* means exactly this: the URL still returns 200, the page carries `noindex,follow`, and it is removed from the menu, footer, hub lists and `sitemap.xml`. In the data file this is `status: hidden`. The final review decides: restore, merge or 301.
4. **Tour facts come after the pages are set up.** New tour pages (Car & City hub, Longewala, Rajasthan) are built as `status: draft`: not linked, not in the sitemap, `noindex`. Every unknown fact is marked `NEEDS OWNER INPUT`. They go live only when the owner supplies facts and approves.
5. **Merge authority stays with the owner.** Each batch is one PR; nothing is merged or deployed without the owner.

---

## 1. Decisions in one screen

1. **Navigation:** About ▾ · Stay ▾ · Dining · Packages ▾ (two groups: Desert Tours, Car & City Tours) · Directions · Blog · Contact · **Book Now**. Gallery moves under About and the footer.
2. **Every dropdown parent is also a real page and a real link.** Packages goes to the packages hub, Desert Tours to its hub, and so on. Hubs are crawlable and you don't lose a page when the menu collapses on mobile.
3. **Keep every existing URL.** The site is live and indexed. We add pages and 301-redirect only where two pages are merged.
4. **One system for all tours:** a tour is one entry in `include/experience-data.php`, plus a 3-line page file. The menu, hubs, breadcrumbs, related links and sitemap are all generated from that data, so adding "Longewala tour" is 10 minutes of work, not a new template.
5. **Swiss Tent** is a desert overnight tour, not a haveli room. It gets one canonical page (`swiss-tent-safari.php`) that is listed under both Stay and Desert Tours as a link, never as a second page.
6. **New pages you asked for** (Car Tours hub, Longewala tour, Rajasthan tours) need facts only you have (section 8). Until you supply them they stay as drafts and are not linked or added to the sitemap.
7. **Design stays heritage-warm** (your sand, brown and cream palette) but gets a real type scale, spacing scale, button hierarchy and component set (section 7).

---

## 2. Proposed site map

Status: ✅ exists · 🔧 exists, change needed · 🆕 new page · ↪ redirect

```
HOME  index.php ✅

ABOUT ▾
  Our story ................ about.php ✅
  Gallery .................. gallery.php ✅
  Contact is a separate top-level item

STAY ▾
  Heritage rooms ........... room.php ✅   (7 rooms on one page, linked by anchor: Sweet Purple, Rising Sun,
                                            Scarlet, Opium, Red Maharani, Green, BlueSky)
  Swiss tent desert stay ... swiss-tent-safari.php ✅  (link; canonical lives under Desert Tours)
  Book direct .............. book-rooms-in-jaisalmer.php 🔧 (keep as the "why book direct" landing page)

DINING  roof.php 🔧  (rooftop kitchen, the dining hub)
  └─ Menu ................... restaurant.php 🔧  (child page, breadcrumb: Dining › Menu)

PACKAGES ▾  pack.php 🔧  (hub: choose by Desert Tours or Car & City Tours)
  ├─ DESERT TOURS ........... desert-safari.php ✅  (hub)
  │    ├─ Sunset camel safari (half day) ........ sunset-camel-safari.php ✅
  │    ├─ Cultural evening, folk dance (half day) cultural-safari.php ✅
  │    ├─ Sleep under the stars (overnight) ..... sleep-under-the-stars.php ✅
  │    ├─ Swiss tent safari (overnight) ......... swiss-tent-safari.php ✅
  │    ├─ Desert camping ......................... camping.php 🔧 (orphan today; link it, or merge it)
  │    └─ Explore the desert ..................... explore-the-desert.php 🔧 (near-duplicate, see 5)
  └─ CAR & CITY TOURS ....... car-tours.php 🆕  (hub)
       ├─ Jaisalmer city tours .... guided-jaisalmer-tour.php ✅ (sub-hub)
       │     ├─ Half-day tour ........ half-day-jaisalmer-tour.php ✅
       │     ├─ Full-day tour ........ full-day-jaisalmer-tour.php ✅
       │     └─ Fort walk ............ city-tour.php 🔧 (orphan today; link it)
       ├─ Longewala tour ........... longewala-tour.php 🆕 (needs owner facts)
       ├─ Rajasthan tours .......... rajasthan-tours.php 🆕 (one page first; split by route later)
       └─ Car for arrival or a desert day ... car-rental.php 🔧 (orphan today; link it)

DIRECTIONS  direction.php ✅

BLOG  blog.php 🔧  (SEO hub; rename label consistently, see section 6)
  ├─ jaisalmer-fort-guide.php ✅
  ├─ desert-safari-guide.php ✅
  ├─ jaisalmer-2-day-itinerary.php ✅
  └─ (new guides per Phase 4 topic plan)

CONTACT  contact.php 🔧
FOOTER-ONLY: privacy.php 🆕 · booking-policy.php 🆕 (cancellation and terms; need your wording)
NOT INDEXED: mail.php, thank-you.php, tariff.php
```

### What you asked for vs what is proposed

| You asked | Proposed | Why it differs |
|---|---|---|
| About | About ▾ (Our story, Gallery) | Gallery was a top-level item; it fits under About and frees a menu slot. |
| Stay → Rooms, Swiss Tent | Stay ▾ → Heritage rooms, Swiss tent desert stay | Same. The Swiss tent page stays single (no duplicate). |
| Packages → Desert Tour, Car Tours | Packages ▾ with exactly those two groups | Same, plus hub pages so each group is a page. |
| Desert: sleeping under stars, half-day sunset, cultural folk dance | Same three pages, already built | They exist and are data-driven. |
| Car: city tour, Longewala, Rajasthan | City tours exist; Longewala and Rajasthan are new | Need your facts first. |
| Direction, Blog, Contact | Unchanged | Added **Dining** as a top-level item. It is a main reason people choose the hotel, and today it is hidden in the footer. |

---

## 3. Menu specification

**Desktop (≥ 992px):**
```
[logo]   About ▾   Stay ▾   Dining   Packages ▾   Directions   Blog   Contact      [Book Now]
```
- "Packages ▾" opens a **two-column panel**: left column Desert Tours (6 links), right column Car & City Tours (5 links). Each column heading is a link to its hub. A footer row says "All packages →" and "Not sure? WhatsApp us".
- "About ▾" and "Stay ▾" open small lists (2 to 3 links).
- Open on hover **and** keyboard focus; close on Esc, outside click, or tab-out. Parent label is a link; the chevron is a separate `<button aria-expanded aria-controls>`.
- Active state: parent highlights when any child page is active.

**Mobile (< 992px):**
- Hamburger opens a full-height panel with grouped accordions (Stay · Dining · Packages · …). Parent row: link on the left, chevron button on the right.
- Bottom bar (Call · WhatsApp · Book Now) stays on every page.
- Tap targets at least 44px; body scroll locked while open.

**Rules:**
- The dropdown HTML is rendered by PHP on every page. Search engines must see the links without running any JS.
- No new libraries. Extend `js/garh-v2.js` and `css/garh-v2.css`. No jQuery, meanmenu or Bootstrap on V2 pages.
- Labels are short and plain: "Sunset camel safari", not "Sunset Camel Safari with Dinner in Jaisalmer Desert".

---

## 4. URL and redirect policy

- **Existing URLs stay exactly as they are.** New pages use the same style: lowercase, hyphenated, `.php`, flat (`longewala-tour.php`).
- **Do not introduce `/folders/` URLs now.** Moving to clean URLs is a separate, optional migration with a full redirect map, and it brings little ranking gain on a site this size. If you do it later, it is its own phase.
- **Merging two pages:** the retired URL becomes a 301 in `.htaccess` to the surviving page, is removed from the sitemap, and is logged in `PROJECT-LOG.md`.
- **Every page** has one canonical URL (`https://www.hotelgarhjaisal.com/<file>`), one H1, a breadcrumb that matches the menu tree, and at least one inbound link from its hub.

Breadcrumb examples:
`Home › Packages › Desert Tours › Sleep under the stars` · `Home › Dining › Menu` · `Home › Blog › Jaisalmer fort guide`

---

## 5. Overlaps to resolve before building (owner decisions)

| Pages | Problem | Recommendation |
|---|---|---|
| `explore-the-desert.php` vs `sunset-camel-safari.php` | The itineraries are the same steps (jeep → Khuldhara → pond and village → camel → sunset). That is duplicate content and the pages will compete in search. | If "Explore the desert" is not a different product, 301 it to the sunset safari. If it is different, rewrite so the difference is obvious (duration, route, what is included). |
| `camping.php` vs `sleep-under-the-stars.php` vs `swiss-tent-safari.php` | Three overnight options. Camping is orphaned. | Keep three only if they are three real products (open sky, standard camp, tent). Otherwise fold camping into the nearest one. |
| `city-tour.php` ("a walk inside the fort") vs `guided-jaisalmer-tour.php` / half-day | Similar promise. | Keep the walk as the lightest option inside "Jaisalmer city tours", and label it "Fort walk". |
| `roof.php` vs `restaurant.php` | Both dining. | `roof.php` is the dining hub (experience, timings, booking). `restaurant.php` is the menu child. Each links to the other near the top. |
| `pack.php` vs `desert-safari.php` | Both introduce desert options. | `pack.php` only chooses between the two groups. All desert detail lives on `desert-safari.php`. |

---

## 6. Engineering design (how to build it so it stays simple)

### 6.1 One source of truth for navigation: `include/nav-data.php`
The header menu, mobile menu, footer links, breadcrumbs and the sitemap generator all read this file. Sketch:

```php
<?php
return [
  ['key'=>'about', 'label'=>'About', 'url'=>'about.php', 'children'=>[
      ['label'=>'Our story', 'url'=>'about.php'],
      ['label'=>'Gallery',   'url'=>'gallery.php'],
  ]],
  ['key'=>'stay', 'label'=>'Stay', 'url'=>'room.php', 'children'=>[
      ['label'=>'Heritage rooms',         'url'=>'room.php'],
      ['label'=>'Swiss tent desert stay', 'url'=>'swiss-tent-safari.php'],
  ]],
  ['key'=>'dining', 'label'=>'Dining', 'url'=>'roof.php'],
  ['key'=>'packages', 'label'=>'Packages', 'url'=>'pack.php', 'groups'=>[
      ['label'=>'Desert Tours',    'url'=>'desert-safari.php', 'from'=>['category'=>'desert']],
      ['label'=>'Car & City Tours','url'=>'car-tours.php',     'from'=>['category'=>'car']],
  ]],
  ['key'=>'directions', 'label'=>'Directions', 'url'=>'direction.php'],
  ['key'=>'blog', 'label'=>'Blog', 'url'=>'blog.php'],
  ['key'=>'contact', 'label'=>'Contact', 'url'=>'contact.php'],
];
```
Group children with `from` are pulled automatically from `experience-data.php` by category, in a defined order. Result: add a tour to the data and it appears in the menu, hub, breadcrumb and sitemap with no edits elsewhere.

### 6.2 Extend `include/experience-data.php`
Add these fields to every experience entry (existing fields stay; image width/height fields added in Phase 5 are untouched):

| Field | Example | Used for |
|---|---|---|
| `category` | `desert` · `car` · `city` | menu group, hub, breadcrumb |
| `parent` | `desert-safari` | breadcrumb and "back to hub" |
| `menu_label` | `Sunset camel safari` | short menu text |
| `duration_type` | `half-day` · `overnight` · `full-day` · `multi-day` | hub filter chips and card badges |
| `order` | `10` | order in menus and hubs |
| `status` | `live` · `draft` · `hidden` | drafts and hidden pages are never linked, never in the sitemap, and carry `noindex` (section 0) |
| `lastmod` | `2026-10-12` | sitemap |

### 6.3 Pages become 3-line files (already true for most tours)
```php
<?php
$experienceSlug = 'longewala-tour';
require __DIR__ . '/include/experience-page.php';
```
`experience-page.php` renders hero, at-a-glance, itinerary, inclusions, FAQ, related tours and CTA, and should also render the breadcrumb and JSON-LD from the data.

### 6.4 Hubs render from data, not hard-coded lists
`pack.php` has hard-coded `$desertItems` and `$guideItems` arrays. Replace them with a filter over `experience-data.php` by `category` and `duration_type`. `desert-safari.php`, `car-tours.php` and `guided-jaisalmer-tour.php` use the same card component.

### 6.5 Shared includes
- `include/head.php`: title, description, canonical, OG and Twitter tags, fonts, CSS, GA. One place, so no page can ship without a canonical.
- `include/breadcrumb.php`: builds from `nav-data.php` and experience `parent`.
- `include/cta-band.php`, `include/faq.php`, `include/related.php`: one markup each.

### 6.6 Sitemap and checks
- `scripts/generate-sitemap.php` builds `sitemap.xml` from the page list plus live experiences, with `lastmod`. Run it before each PR; CI fails if `sitemap.xml` is stale.
- Extend the repository's `scripts/check-site.php` (and the sitemap-driven Playwright specs under `tests/e2e/`): every URL in `nav-data.php` exists; no live page is an orphan; no `draft` or `hidden` page appears in the menu, footer, hubs or sitemap. Do not weaken any existing assertion.

### 6.7 cPanel safe
Plain PHP, no Composer, no build step. `.htaccess` carries only the 301s for merged pages. Test PHP 7.4 and 8.x.

---

## 7. Design direction (improving the existing look)

**Keep:** the warm heritage identity already in `garh-v2.css` (`--sand #d9b27a`, `--sand-light #f2e8d8`, `--cream #fbf8f1`, `--brown #6b442d`, `--ink #29231e`) and the Source Serif and Inter pairing. **Fix:** inconsistency, not identity.

### 7.1 Design tokens to add to `:root`
```css
:root{
  /* type scale (fluid) */
  --fs-hero:clamp(2.2rem,5vw,3.6rem);  --fs-h2:clamp(1.6rem,3vw,2.3rem);
  --fs-h3:1.25rem;  --fs-body:1rem;  --fs-small:.875rem;
  /* spacing scale */
  --sp-1:.5rem; --sp-2:1rem; --sp-3:1.5rem; --sp-4:2.5rem; --sp-5:4rem; --sp-6:6rem;
  /* shape and depth */
  --radius:14px; --radius-sm:8px; --shadow-sm:0 6px 18px rgba(42,28,18,.08);
  /* accents */
  --accent:var(--brown);   --accent-hover:#58361f;   --focus:#1d4ed8;
}
```
Contrast check is part of Batch B: body text on cream and sand-light, white on brown, and the Book Now button must meet WCAG AA.

### 7.2 Components (one markup, one style each)
1. **Hero** with variants: `home` (full-bleed, H1, one primary and one secondary button), `page` (shorter, breadcrumb above H1), `tour` (adds the at-a-glance strip).
2. **Buttons:** primary = Book Now (solid brown); secondary = WhatsApp (outline); tertiary = text link with arrow. At most one primary per screen.
3. **Cards:** room card, tour card (duration badge, 3 facts, "Ask us" link), article card. Same radius, shadow and image ratio.
4. **At-a-glance strip** (duration, group size, includes, best for), used on tours, rooms and dining.
5. **Section rhythm:** alternate cream and sand-light bands; one section = one message.
6. **FAQ accordion, CTA band, related links:** one component each.

### 7.3 Per-page improvements
| Page | Improvement |
|---|---|
| Home | Hero with a single clear promise and Book Now + WhatsApp; add a quick-facts strip under the hero; compress to the 8 sections in the layout review; each section ends with one link, not three. |
| Rooms | Room cards in a consistent grid: photo, capacity, bed, three amenities, "Check availability"; floor grouping becomes a filter or anchor row; Swiss tent shown as a "Stay in the desert" card linking out. |
| Packages hub | Two big choice cards (Desert Tours, Car & City Tours) with a duration filter row beneath; remove the long intro block. |
| Tour pages | Hero, at-a-glance strip, itinerary as a vertical timeline with step numbers, what is included, who it suits, FAQ, related tours. Sticky "Ask on WhatsApp" on mobile. |
| Directions | Lead with the one-line answer and the map; the last 300 m (cobbles, stairs) as an illustrated step list; one call button for late arrival. |
| Dining | Hero photo of the roof, featured dishes with real photos, timings and "reserve a table" button above the fold; menu as a child page. |
| Gallery | Category filter (Rooms, Rooftop, Fort, Desert), lazy-loaded grid, lightbox, CTA after the grid. |
| Blog | Featured guide + card grid; article pages get a table of contents and related stays and tours. |
| Contact | Standard hero; two big actions (Call, WhatsApp) before the form; map after it. |

### 7.4 Imagery rules
Real photos only. A fixed aspect ratio per card type (rooms 4:3, tours 3:2, hero 16:9). Every image has width and height, alt text and lazy-loading (except the hero). Keep existing filenames. Ask the owner for any missing shot by exact name.

---

## 8. Owner inputs needed for the new pages (ChatGPT must not guess these)

**Car & City Tours hub + Longewala tour + Rajasthan tours**
- Exact tours you want to sell, and for each: route and stops, distance, duration, start time, vehicle type and capacity, driver or guide language, entry fees included or not, meals included, pickup point, who it suits.
- Rajasthan tours: which routes (for example Jaisalmer → Jodhpur, Udaipur, Jaipur?), single or multi-day, and whether prices are shown or "on request".
- Whether prices appear on the site, and in which format.

**Existing tours:** confirm "half-day sunset" and "cultural folk dance half-day" match `sunset-camel-safari` and `cultural-safari`, and whether `explore-the-desert` and `camping` are real separate products.

**Policy pages:** your privacy wording and cancellation and booking terms.

---

## 9. Build plan: PR batches (one theme per branch)

| Batch | Branch | Contents | Done when |
|---|---|---|---|
| A | `feature/nav-data-and-menu` | `nav-data.php`; new header with dropdowns and mobile accordion; footer from nav data; `activePage` fixed on every page | Menu works by keyboard and touch; all links resolve; `check-site.php` passes |
| B | `feature/design-tokens-components` | Tokens, button, card, hero variants, at-a-glance, FAQ and CTA components in CSS; contrast pass | No visual regressions on the home page at 390 and 1280px |
| C | `feature/experience-data-v2` | New data fields; hubs render from data; breadcrumb and JSON-LD from data | `pack.php` and `desert-safari.php` identical content to before, from data |
| D | `feature/resolve-overlaps` | Decisions from section 5; 301s; orphans linked or merged | No orphan pages; sitemap matches |
| E | `feature/car-tours` | `car-tours.php` hub; Longewala and Rajasthan entries and pages (after section 8 answers) | Pages render from data; drafts hidden until approved |
| F | `feature/page-redesigns-1` | Home, Rooms, Packages hub redesigned to the templates | Reviewed at 390 and 1280px |
| G | `feature/page-redesigns-2` | Tour pages, Directions, Dining, Gallery, Contact | Same |
| H | `feature/policies-and-sitemap` | `privacy.php`, `booking-policy.php`; `generate-sitemap.php`; CI freshness check | CI green |

Order: A → B → C → D → E (draft pages only until facts arrive) → F → G → H.

**One PR at a time.** The CI workflows run only for pull requests that target `main`, and the batches share files (`css/garh-v2.css`, `include/header-v2.php`, `include/experience-data.php`). Stacked PRs would get no CI and would conflict. The docs PR and Batch A touch different files and can be open together; every later batch starts from `main` after the previous merge.

**Per-PR loop (repository convention):** branch `feature/<short-name>` from current `main` → build → smart-check four-role review (Full-Stack Developer, SEO Content Writer, Debugger, UI/UX Lead) → local PHP lint and `php scripts/check-site.php --strict-images` → push → PR using `.github/pull_request_template.md` → read both CI workflows on the exact head SHA → owner reviews and merges. Update `docs/PROJECT-LOG.md` inside each PR.

**Constraints from the existing tests:** the mobile navigation test looks for a button named *Open navigation* and a navigation landmark named *Primary navigation*; the internal-link spec crawls every anchor on every sitemap route, so the menu may only link to pages that exist; the SEO specs run over `sitemap.xml` routes only.

---

## 10. Prompts for ChatGPT

**Kick-off (tells ChatGPT to treat this as the plan):**
```text
[FRONTEND] Read docs/SITE-ARCHITECTURE.md and docs/LAYOUT-REVIEW.md. They are the approved
plan. Confirm in 8 lines: the menu structure, the data-driven design (nav-data.php and
experience-data.php fields), the URL policy, and the batch order. List the exact files
you need from me for Batch A. Do not write code yet.
```

**Batch A:**
```text
[FRONTEND] Batch A (docs/SITE-ARCHITECTURE.md sections 3 and 6.1). Build include/nav-data.php
and rewrite include/header-v2.php and the footer to render from it. Desktop dropdowns (Packages as
a two-column panel), mobile accordion, keyboard and touch accessible, parent items are links with a
separate chevron button, links rendered in PHP not JS. Extend js/garh-v2.js and css/garh-v2.css only.
Set $activePage correctly on every page. Do not touch page content. Max 10 files. Follow the
standing workflow and stop after the PR text.
```

**Batch C:**
```text
[FRONTEND] Batch C (sections 6.2 to 6.5). Add the new fields to include/experience-data.php for
every existing experience using the labels in section 2 of the architecture doc. Replace the hard-coded
$desertItems and $guideItems in pack.php with data filters. Add breadcrumb and JSON-LD rendering to
experience-page.php. Output must match the current pages. Show me a before and after list of
rendered cards.
```

**Batch E (only after I have answered section 8):**
```text
[SEO] Batch E. My answers for section 8 are below. Create the experience-data entries and 3-line
page files for car-tours (hub), longewala-tour and rajasthan-tours with unique titles, descriptions,
H1s, FAQs and internal links. Mark every fact I did not provide as NEEDS OWNER INPUT and set
status 'draft' until I approve. <paste answers>
```

---

## 11. Status of the batches

Track progress in `docs/PROJECT-LOG.md` (section "Site architecture blueprint"). This document describes the target; the log records what has actually merged.
