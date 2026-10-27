# Garh Jaisal Haveli website

A direct-booking and guest-planning website for Garh Jaisal Haveli, inside Jaisalmer Fort, Rajasthan.

## Project at a glance

- **Stack:** Plain PHP, vanilla CSS/JavaScript, JSON/PHP data registries, cPanel deployment.
- **Production URL:** `https://www.hotelgarhjaisal.com`.
- **Primary business goal:** make the property, rooms, dining, experiences and arrival process easy to understand, then help visitors book rooms or send a contextual enquiry directly.
- **Authority:** the owner approves merges and all staging/production releases. A green CI run does not authorize deployment.
- **No secrets in Git:** server-specific configuration is `include/site-config.local.php` (ignored) or approved environment variables.

## Start here

1. `docs/PROJECT-STATE.md` — current phase, known blockers and release state.
2. `docs/DESIGN-SYSTEM.md` — visual direction, tokens and responsive UI rules.
3. `docs/ARCHITECTURE.md` — PHP structure, routes, templates and data sources.
4. `docs/OWNER-FACTS.md` — verified business facts and questions that must not be guessed.
5. `docs/DEPLOYMENT.md` — cPanel package, staging, rollback and owner approval.
6. `docs/TESTING.md` — local/CI checks and required evidence.
7. `docs/CONTENT-STUDIO.md` — local tour/blog/gallery authoring workflow.

## Core guest journeys

- Home → Rooms → external booking engine for live rates/availability.
- Experiences → itinerary/inclusions → contextual enquiry or reviewed WhatsApp draft.
- Rooftop/restaurant → current details and enquiry; not an assumed confirmed reservation.
- Directions → Fort parking/walking approach → arrival enquiry if needed.

## Local development

Use Node.js 22 for repository Playwright/Content Studio checks and a PHP version compatible with the host (CI checks PHP 7.4 and 8.2; production PHP still needs owner/host confirmation).

```bash
npm install
php scripts/check-site.php --strict-images
php scripts/generate-sitemap.php --check
npm run test:e2e
node tests/node/content-pack.test.js
```

Browser E2E expects the local PHP site on `http://127.0.0.1:8080`; follow `.github/workflows/browser-e2e.yml` if local server setup differs. Don't submit a live contact enquiry as a test.

Optional local Content Studio commands include `npm run studio:start`, `npm run studio:inbox <id>`, `npm run studio:approve <id> -- --by "Name"`, and `npm run studio:ship <id>`. Content Studio remains operator-side; its local “Live” state is not proof of public production publication.

## Engineering rules

- Prefer complete user-visible vertical slices over isolated polish PRs.
- Preserve public routes, canonicals, booking destination/token, contact channels and approved schema facts.
- Reuse shared includes and data registries instead of duplicating markup/data.
- Never invent room facilities, prices, tour details, policies, timings, reviews or availability.
- Never delete a public URL without an owner-approved 301 mapping.
- Review screenshots and exact-head CI evidence; do not equate test success with visual approval or production readiness.
