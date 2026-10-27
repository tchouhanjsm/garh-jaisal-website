# Design system — Garh Jaisal Haveli

## Brand idea

**A living fort, a family home.** The interface should feel like a real, welcoming heritage stay: sandstone, architectural detail, warm light and night-sky contrast. Use quiet confidence and local warmth, not generic luxury clichés, heavy ornament, fake scarcity, or animation for its own sake.

## Tokens

The canonical token source is `css/tokens.css`. Preserve existing variable names unless a planned migration updates every consumer.

| Role | Token | Value |
|---|---|---|
| Fort Night | `--night` | `#14122B` |
| Deep Night | `--night-2` | `#241B3F` |
| Golden Stone accent | `--gold` | `#D9A441` |
| Deep gold on light | `--gold-ink` | `#8A6212` |
| Primary action | `--saffron` | `#B94A0E` |
| Haveli Garnet | `--garnet` | `#5B1A2B` |
| Stone Parchment | `--parchment` | `#F7EFE0` |
| Sand | `--sand` | `#EADBC0` |
| Body ink | `--ink` | `#231A14` |
| Functional teal | `--peacock` | `#1D7472` |

- Display type: Source Serif 4. Body: Inter/system sans-serif stack.
- Saffron is the primary CTA; gold is a graphic accent, not small text or a primary fill on light backgrounds.
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
