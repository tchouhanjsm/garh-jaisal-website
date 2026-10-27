---
name: smart-check
description: Review Garh Jaisal Haveli website changes through four specialist roles (Full-Stack Developer, SEO Content Writer, Debugger, UI/UX Lead). Each role reviews every change and records evidence for any N/A-GREEN verdict. Use for PR review, CI failures, release readiness, SEO, responsive/UI, PHP/backend, forms, security, and analytics changes.
---

# Smart Check

This Claude Code entrypoint mirrors the canonical project skill at `.agents/skills/smart-check/SKILL.md`.

Before reviewing, read the canonical skill and matrix:
- `.agents/skills/smart-check/SKILL.md`
- `docs/TESTING.md`
- `docs/PROJECT-STATE.md`
- Relevant workflows in `.github/workflows/`

Follow the canonical skill in full. Do not treat this file as an abbreviated alternative. If the two differ, the canonical `.agents/skills/smart-check/SKILL.md` is authoritative.

The four roles must review every change:
1. Full-Stack Developer
2. SEO Content Writer
3. Debugger
4. UI/UX Lead

Use role verdicts GREEN, N/A-GREEN, YELLOW, RED. For individual checks, use PASS, FAIL, BLOCKED, NOT RUN. For N/A-GREEN, name the concrete diff detail inspected. Verify CI for the exact latest PR head SHA and inspect warnings/retries, not only the overall success indicator.

Never weaken a valid assertion to make CI pass. Never merge or deploy. The owner retains those decisions. Preserve public URLs, verified hotel facts, prices, timings, room-image mappings, analytics IDs and secret boundaries unless explicitly approved and evidence supports the change.
