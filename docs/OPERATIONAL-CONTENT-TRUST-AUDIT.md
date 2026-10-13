# F13 — Operational content trust and contact readiness

## Purpose

Create a safe baseline for reviewing customer-facing operational information and the actions a guest can take to ask a question or plan an arrival. This is an audit/readiness milestone, not a rewrite of hotel facts.

## Scope in PR #39

- Add a sitemap-independent browser regression for the contact and arrival journeys.
- Check that the contact page's booking CTA uses the same destination as the shared navigation, opens safely in a new tab, and has the expected arrival route.
- Check that direct phone and email paths are present.
- Check the enquiry form's labels, required fields, CSRF token, CAPTCHA site key, and honeypot semantics without submitting it.
- Check that the arrival page offers a route back to contact plus phone and WhatsApp paths.
- Preserve public URLs, text, phone/email values, the live recipient configuration, and any business facts. This test proves wiring and accessible structure, not the external accuracy of the supplied details.

## Explicitly excluded

**Rates and prices are out of scope for this batch.** The owner will add/update them in a later change. This PR does not add, update, verify, or assert any rate, tariff, menu price, transfer cost, package price, or other numeric pricing claim.

## Owner review backlog (non-rate claims only)

Before changing customer-facing facts in a later content PR, confirm these directly with the operator:

- [ ] Arrival flow, vehicle-access limitation, and route/wayfinding wording.
- [ ] Reception and meet-and-assist availability windows.
- [ ] Accessibility and walking-surface descriptions; do not overstate step-free access.
- [ ] Current contact channels and the intended recipient configuration on the server.
- [ ] Restaurant availability and the distinction between an enquiry and a confirmed table reservation.
- [ ] Current tour/experience inclusions and exclusions, and which activities are offered on request.
- [ ] Address, map target, and directions across contact, arrival and core landing pages.
- [ ] Room and experience page claims agree with current operations, including cancellation/availability language where published.

Items above are an owner review queue, not assertions that all current statements have been verified. Do not rewrite operational copy to make the audit appear complete. Record the owner-confirmed decision and evidence in the relevant future change before publishing any corrections.

## Safety and validation

- Browser checks do not click the booking CTA, follow external contact actions, submit the form, send emails, or create real reservations.
- Run the full responsive Playwright suite and PHP 7.4/8.2 Site Checks on the final PR head.
- Rates/prices must remain unchanged in this batch.
