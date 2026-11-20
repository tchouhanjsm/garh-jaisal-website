# Design system — Garh Jaisal Haveli

## Fort at Dusk

**A living fort, a family home.** The interface should feel like a real, welcoming heritage stay: sandstone, architectural detail, warm light and night-sky contrast. Use quiet confidence and local warmth, not generic luxury clichés, heavy ornament, fake scarcity, or animation for its own sake.

## Tokens

The canonical token source is `css/tokens.css`. Preserve existing variable names unless a planned migration updates every consumer.

| Role | Token | Value |
|---|---|---|
| Fort Night | `--night` | `#152636` |
| Deep Night | `--night-2` | `#20374A` |
| Brass on dark | `--gold` | `#EED29D` |
| Deep gold on light | `--gold-ink` | `#745624` |
| Primary action on paper | `--action-blue` | `#29485E` |
| Secondary ink | `--garnet` | `#2C4356` |
| Stone Parchment | `--parchment` | `#FBF4E5` |
| Sand | `--sand` | `#EEE6D6` |
| Body ink | `--ink` | `#212832` |
| Functional teal | `--peacock` | `#1D7472` |

- Use Palatino for display text and Trebuchet for body copy. Both have system fallbacks.
- Use slate blue for primary actions on cream and brass with dark ink for primary actions on midnight.
- Standard buttons and cards use a restrained 6px radius.
- Use the shared content rail and type/spacing variables. Avoid hard-coded near-duplicate scales in page CSS.
- Keep forms, policies, prices, notes and dense information on light surfaces for clarity.
- One primary action should dominate each viewport. Secondary actions remain visibly secondary.
- Use real owner-provided property photography; never imply that external design reference images are current hotel photos.

## First-screen rules

The homepage should reveal, without scrolling, the property, its distinct location inside Jaisalmer Fort, a short value proposition and a clear room/rate action. The architectural photo is the focal point; keep text contrast sufficient without hiding the photo in a heavy overlay. Stay/dine/explore facts must be short and factual.

## Component rules

- Reuse shared header/footer, hero, breadcrumb, facts, CTA, FAQ, image, form and trip components.
- Cards use consistent photo proportions, aligned titles, concise descriptions and obvious next actions. Avoid stacking multiple card grids that repeat the same routes.
- Booking emphasizes live room/rate checking. Enquiries and WhatsApp drafts remain enquiries until confirmed.
- Fixed mobile actions must not cover hero actions, inputs, FAQ content or the terminal CTA.
- Hover effects are restrained and removed/reduced under `prefers-reduced-motion`.
- Interactive controls have keyboard-visible focus. Aim for 44px minimum touch targets and 48px primary actions.
- Image crops are art-directed and reviewed at mobile and desktop widths.

## Responsive review matrix

Inspect 320, 390, 768, 1280 and 1440px. Check heading wraps, navigation/dropdowns, sticky/fixed controls, card rhythm, hero focal point, forms, content gutters and horizontal overflow. No new visual system is approved by a single desktop screenshot.

## Change discipline

Update the existing canonical stylesheet/component owner before creating another CSS layer. Search usages before removing selectors. Preserve public routes, the external booking engine destination, schema and verified facts. Passing automated tests does not replace screenshot review.


## Automated contrast check coverage

The repository checker enforces minimum contrast for documented representative pairs only; it checks representative pairs, not every possible rendered CSS combination across every page. Actual page contrast, overlays and text placement still require screenshot and accessibility review.

## Premium visual system — implementation contract (1 November 2026)

The canonical token source remains `css/tokens.css`. The first implementation batch adds semantic surface, text, line, action and motion roles while retaining every existing token name for backwards compatibility.

### Semantic usage

- `--surface`, `--surface-raised`, and `--surface-muted` define warm editorial surfaces. Do not put dense policy, price, form or FAQ content over busy photography.
- `--surface-dark` and `--surface-dark-raised` define immersive Fort Night storytelling layers. Keep body copy on dark surfaces at the documented muted-light role.
- `--line` and `--line-strong` provide consistent separators and boundaries.
- `--text-muted` is for secondary text on light surfaces; `--text-on-dark-muted` is for supporting text on dark surfaces.
- `--action-primary` and `--action-primary-hover` are reserved for direct booking and the single primary action in a viewport. Use deep gold ink for small text on parchment.
- `--focus-ring` remains a visible focus indicator. Do not remove it to achieve a visual treatment.
- Motion roles must honour the existing `css/accessibility.css` reduced-motion preference layer.

### Composition standards

1. **Editorial rhythm:** use generous section spacing, varied full-bleed, split and restrained card compositions. A page should not become a repeated grid of identical boxes.
2. **Photography:** use only the repository's approved property/experience photography and existing responsive-image helpers. Never add external image references as though they depict the property.
3. **Typography:** Use the Palatino and Trebuchet stacks throughout the site. Prefer short, balanced headings and readable body measures.
4. **Actions:** 48px primary actions on public guest journeys; secondary actions must remain visually subordinate. Existing IDs, destinations and booking instrumentation are invariants.
5. **Cards:** use the shared surface/line/shadow roles. Reserve card treatments for comparable items or decision units; let editorial storytelling sections breathe without enclosing every paragraph.
6. **Responsive:** test 320, 390, 768, 1280 and 1440px. Review hero content, section-head stacking, text measure, image crop, fixed mobile dock clearance, cards, forms and footer.
7. **Accessibility:** keyboard-visible focus, contrast review at rendered component level, reduced-motion support, meaningful link text, correct heading hierarchy and touch targets of at least 44px (48px for primary actions).

### CSS ownership rules

- `css/tokens.css` owns tokens and shared component-level normalization only.
- `css/garh-v2.css` remains the base and legacy compatibility stylesheet.
- `css/conversion-system.css` owns shared conversion, FAQ and planning components.
- `css/fort-at-dusk.css` applies the shared midnight treatments to navigation, heroes and featured sections.
- `css/thar-sunset-story.css` owns the story motif and scoped narrative treatments.
- Route CSS owns only genuinely route-specific composition.
- Do not duplicate a shared rule in multiple layers to win the cascade. Before consolidating historical overrides, search selector usage and compare computed styles and responsive screenshots.
- Keep canonical semantic tokens in one top-level `:root` block, with one declaration per token. Keep shared component contracts in one final block after retained compatibility rules; enforce uniqueness in `scripts/check-brand-contrast.php`.
- No selector or route CSS is removed in this batch. This is intentional: a safe consolidation needs rendered baseline evidence on the exact commit.

### Vendor review boundary

This design-system work is repository-first. It is not based on a crawl or screenshot of `hotelgarhjaisal.com`. That domain is outside the repository vendor engagement. The Git repository, its exact commit, its committed asset library and its tests are the only implementation sources of truth.

### Acceptance

- Existing semantic contracts, routes, booking/contact destinations, content facts, schemas, analytics boundaries and asset manifests remain unchanged.
- Run the repository's PHP/site checks, token/contrast checks, Playwright E2E and screenshot/page-sheet matrix on the exact PR head.
- Inspect generated screenshots at 320/390/768/1280/1440px; a green CI run is not a substitute for human visual approval.
- Do not upload a package, dispatch real-host QA, submit an enquiry, merge or deploy as part of the PR.

## Dining hero action hierarchy

- The Restaurant and Rooftop heroes prioritize one table-enquiry action and one secondary menu or direct-contact route.
- Save-to-Trip is a planning action, not a reservation action; place it in a distinct lower-page planning panel rather than beside the hero's primary CTA.
- Keep the existing `rooftop-dining` Trip ID consistent across Restaurant and Rooftop so a guest's selection survives navigation.
- Use explicit enquiry/request language until the hotel confirms table availability. A click to the request form is intent, not a submitted enquiry or reservation.
- Preserve visible focus, 44px minimum interaction targets, mobile stacking and reduced-motion behavior for the lower planning panel.
