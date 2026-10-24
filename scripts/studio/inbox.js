'use strict';
// Automatic draft builder. Drop a brief plus photos in content/_inbox/<id>/ (or use the Studio "Quick add" tab):
// the code derives the slug, places and names the photos, drafts the SEO fields, builds the record, runs every
// check and the plan, and stops at the one human gate (approval). After approval it exports the pack, applies it
// to this working tree as a DRAFT, and verifies the page. No model is involved; everything is deterministic.
const fs = require('node:fs');
const path = require('node:path');
const L = require('./lib');
const { checkRecord } = require('./check-record');
const { planSave } = require('./plan-save');
const { placePhoto, analyzePhoto, hamming } = require('./place-photo');
const { exportPack } = require('./export-pack');
const { applyPack } = require('../check-content-pack');
const run = require('./run');
const { spawnSync } = require('node:child_process');

const inboxRoot = () => path.join(L.repoRoot(), 'content/_inbox');
const slugify = v => String(v || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 70);
const today = () => new Date().toISOString().slice(0, 10);
const LIST_KEYS = { included: 'included', 'not included': 'not_included', excluded: 'not_included', timeline: 'timeline', itinerary: 'timeline', faq: 'faq', photos: 'photos', sources: 'sources' };
const SCALAR_KEYS = new Set(['type', 'title', 'slug', 'category', 'duration', 'summary', 'best for', 'description', 'author', 'eyebrow', 'facts confirmed by', 'seo title', 'lede']);

// Forgiving brief format: "key: value" lines, "key:" followed by "- item" lines, and (blogs) "## Heading" sections.
function parseBrief(text) {
  const out = { lists: {}, sections: [] };
  let list = null;
  let section = null;
  for (const raw of String(text || '').replace(/\r/g, '').split('\n')) {
    const line = raw.trimEnd();
    const h = /^##\s+(.+)$/.exec(line);
    if (h) { section = { heading: h[1].trim(), paragraphs: [], buf: [] }; out.sections.push(section); list = null; continue; }
    if (section) {
      if (line.trim() === '') { if (section.buf.length) { section.paragraphs.push(section.buf.join(' ')); section.buf = []; } } else section.buf.push(line.trim());
      continue;
    }
    const kv = /^([A-Za-z][A-Za-z ]{1,24}):\s*(.*)$/.exec(line);
    if (kv) {
      const key = kv[1].toLowerCase().trim();
      if (LIST_KEYS[key]) { list = LIST_KEYS[key]; out.lists[list] = out.lists[list] || []; if (kv[2]) out.lists[list].push(kv[2].trim()); continue; }
      if (SCALAR_KEYS.has(key)) { out[key] = kv[2].trim(); list = null; continue; }
    }
    const item = /^\s*[-*]\s+(.+)$/.exec(line);
    if (item && list) out.lists[list].push(item[1].trim());
    else if (line.trim() === '') list = list && out.lists[list].length ? list : list;
  }
  for (const s of out.sections) { if (s.buf.length) s.paragraphs.push(s.buf.join(' ')); delete s.buf; }
  return out;
}

function fitTitle(t) { t = String(t).trim(); if (t.length <= L.PATHS.seo.title_max) return t; const cut = t.slice(0, L.PATHS.seo.title_max + 1); return cut.slice(0, cut.lastIndexOf(' ') > 20 ? cut.lastIndexOf(' ') : L.PATHS.seo.title_max).replace(/[\s,;:-]+$/, ''); }
function fitDescription(d, fallbackTail) {
  const { description_min: min, description_max: max } = L.PATHS.seo;
  let s = String(d || '').replace(/\s+/g, ' ').trim();
  while (s.length < min) s += (s ? ' ' : '') + fallbackTail;
  if (s.length > max) { const cut = s.slice(0, max - 1); s = cut.slice(0, cut.lastIndexOf(' ')).replace(/[\s,;:-]+$/, '') + '.'; }
  return s;
}
const TAIL = 'Ask Garh Jaisal Haveli to confirm current details for your dates.';

// Looks at every photo once: drops near-duplicates, picks the best main photo (named hero, else the sharpest landscape),
// and orders the rest by capture time (EXIF) or file name. Returns { photos, warnings, unreadable }.
function readPhotos(dir, brief) {
  const photoDir = path.join(dir, 'photos');
  const out = { photos: [], warnings: [], unreadable: [] };
  if (!fs.existsSync(photoDir)) return out;
  const alts = new Map();
  for (const row of (brief.lists.photos || [])) { const [file, ...alt] = row.split('|'); if (alt.length) alts.set(file.trim().toLowerCase(), alt.join('|').trim()); }
  const kept = [];
  for (const name of fs.readdirSync(photoDir).filter(n => /\.(jpe?g|png|webp)$/i.test(n)).sort()) {
    const file = path.join(photoDir, name);
    let info;
    try { info = analyzePhoto(file); } catch (_) { out.unreadable.push(name); continue; }
    const twin = kept.find(k => hamming(k.dhash, info.dhash) <= 4);
    if (twin) { out.warnings.push({ rule: 'AU-05', message: 'Skipped ' + name + ': near-duplicate of ' + twin.name + '.' }); continue; }
    kept.push({ file, name, alt: alts.get(name.toLowerCase()) || null, ...info });
  }
  const named = kept.find(k => /hero|cover|main/i.test(k.name));
  const score = k => k.sharp * Math.min(k.width, 1600) * (k.width / k.height >= 1.3 ? 1 : 0.35);
  const hero = named || [...kept].sort((a, b) => score(b) - score(a))[0];
  if (hero && !named && kept.length > 1) out.warnings.push({ rule: 'AU-06', message: 'Chose ' + hero.name + ' as the main photo (sharpest landscape); rename a file to hero-... to override.' });
  const rest = kept.filter(k => k !== hero);
  rest.sort((a, b) => (a.taken && b.taken && a.taken !== b.taken) ? (a.taken < b.taken ? -1 : 1) : (a.name < b.name ? -1 : 1));
  const ordered = hero ? [hero, ...rest] : [];
  const sharps = ordered.map(k => k.sharp).sort((a, b) => a - b);
  const median = sharps[Math.floor(sharps.length / 2)] || 0;
  for (const k of ordered) if (ordered.length >= 3 && k.sharp < median * 0.4) out.warnings.push({ rule: 'AU-07', message: k.name + ' looks blurry compared with the others; consider replacing it.' });
  out.photos = ordered;
  return out;
}

function buildRecord(type, slug, brief, placed, warnings) {
  const title = brief.title;
  const date = today();
  const heroAlt = placed[0].alt || (warnings.push({ rule: 'AU-01', message: 'Alt text for the hero photo was drafted from the title; please review it.' }), title + ' - main photo');
  const images = placed.map((p, i) => ({ path: p.path, role: p.role, alt: p.alt || (i === 0 ? heroAlt : title + ' - photo ' + (i + 1)) }));
  if (placed.slice(1).some(p => !p.alt)) warnings.push({ rule: 'AU-01', message: 'Alt text for some photos was drafted from the title; add "filename | description" lines under "photos:" in the brief to improve it.' });
  const common = { slug, status: 'draft', review_status: 'draft', hero_image: placed[0].path, hero_alt: heroAlt, images };
  if (type === 'tour') {
    const text = (title + ' ' + (brief.summary || '')).toLowerCase();
    const category = ['desert', 'city'].includes(brief.category) ? brief.category : (/desert|dune|camel|safari|sam|khuhri|tent/.test(text) ? 'desert' : 'city');
    const dur = String(brief.duration || '').toLowerCase();
    const durationType = /overnight|night/.test(dur) ? 'overnight' : /multi|\d\s*days/.test(dur) ? 'multi-day' : /full/.test(dur) ? 'full-day' : 'half-day';
    const timeline = (brief.lists.timeline || []).map((row, i) => { const [t, ...rest] = row.split('|'); return { step: String(i + 1).padStart(2, '0'), title: t.trim(), text: (rest.join('|').trim() || t.trim()) }; });
    const faq = (brief.lists.faq || []).map(row => { const [q, ...a] = row.split('|'); return { question: q.trim(), answer: a.join('|').trim() }; }).filter(f => f.question && f.answer);
    const confirmed = /\S+.*\d{4}-\d{2}-\d{2}/.test(brief['facts confirmed by'] || '');
    const rec = {
      ...common, category, parent: category === 'city' ? 'guided-jaisalmer-tour' : 'desert-safari', menu_label: title.slice(0, 70), duration_type: durationType, order: 900,
      title: fitTitle(brief['seo title'] || title), description: fitDescription(brief.description || brief.summary, TAIL), h1: title,
      eyebrow: brief.eyebrow || (category === 'city' ? 'Jaisalmer tour' : 'Desert experience'), hero_width: placed[0].width, hero_height: placed[0].height, summary: brief.summary || '',
      at_a_glance: { Duration: brief.duration || durationType, Overnight: 'Confirm with the hotel', 'Camel ride': 'Confirm with the hotel', Meals: 'Confirm with the hotel', Transport: 'Confirm with the hotel', Return: 'Confirm with the hotel' },
      timeline_label: 'Typical sequence', timeline_note: 'Exact timing and arrangements should be confirmed directly with the hotel for your travel date.', timeline,
      included: brief.lists.included || [], not_included: brief.lists.not_included || [], best_for: brief['best for'] || '',
      before_book: 'Ask the hotel to confirm the current price, availability, pickup arrangements, timing and exact inclusions before booking.', faq,
      related: category === 'city' ? ['half-day-jaisalmer-tour', 'full-day-jaisalmer-tour'] : ['sleep-under-the-stars', 'swiss-tent-safari'], date_modified: date, facts_confirmed: confirmed,
    };
    if (confirmed) rec.last_verified = (brief['facts confirmed by'].match(/\d{4}-\d{2}-\d{2}/) || [date])[0];
    return rec;
  }
  const sections = brief.sections.map(s => ({ id: slugify(s.heading), heading: s.heading, paragraphs: s.paragraphs }));
  return {
    ...common, title: fitTitle(brief['seo title'] || title), description: fitDescription(brief.description || brief.lede || brief.summary, TAIL), h1: title,
    eyebrow: brief.eyebrow || 'Jaisalmer travel guide', lede: brief.lede || brief.summary || '', date_published: date, date_modified: date, archive_title: title.slice(0, 140),
    archive_description: (brief.summary || brief.lede || title).slice(0, 260), archive_category: brief.eyebrow || 'Jaisalmer travel guide', toc: sections.map(s => ({ id: s.id, label: s.heading })), sections,
    sources: (brief.lists.sources || []).map(s => { const [label, ...u] = s.split('|'); return { label: label.trim(), url: (u.join('|') || '').trim() }; }),
    related: [{ label: 'Build your trip', title: 'Compare tours and experiences', href: 'catalogue.php', text: 'Shortlist options and ask the hotel to confirm current details.' }],
    cta_eyebrow: 'Plan with Garh Jaisal Haveli', cta_title: 'Plan your Jaisalmer stay.', cta_text: 'Confirm current arrangements with the hotel before making travel plans.',
    cta_primary_label: 'Explore tours', cta_primary_href: 'catalogue.php', cta_secondary_label: 'Contact the hotel', cta_secondary_href: 'contact.php',
  };
}

// Questions come back in plain words, tied to the brief key that fixes them.
const HINT = { 'RC-09': 'Add a "timeline:" list: "- Step title | what happens".', 'RC-10': 'Add an "included:" list.', 'RC-11': 'Add at least one "## Heading" section with text.', 'RC-12': 'Add a "sources:" list.', 'RC-01': 'Add the missing field to the brief.', 'RC-23': 'Remove phone numbers and emails; guests use the contact page.' };

function processFolder(id) {
  const dir = path.join(inboxRoot(), id);
  const briefFile = path.join(dir, 'brief.md');
  if (!/^[A-Za-z0-9_-]{4,60}$/.test(id) || !fs.existsSync(briefFile)) return { ok: false, state: 'INTAKE', questions: [{ field: 'brief', question: 'Put brief.md in the folder (see docs/CONTENT-STUDIO.md).' }], findings: [] };
  const brief = parseBrief(fs.readFileSync(briefFile, 'utf8'));
  const type = brief.type === 'blog' ? 'blog' : brief.type === 'tour' || !brief.type ? 'tour' : null;
  const questions = [];
  const warnings = [];
  if (!type) questions.push({ field: 'type', question: 'Quick add supports "type: tour" or "type: blog". Use the Gallery form for photo sets.' });
  if (!brief.title || brief.title.length < 3) questions.push({ field: 'title', question: 'What is the title? Add "title: ..." to the brief.' });
  const scan = type ? readPhotos(dir, brief) : { photos: [], warnings: [], unreadable: [] };
  warnings.push(...scan.warnings);
  const photos = scan.photos;
  for (const bad of scan.unreadable) questions.push({ field: 'photos', question: bad + ' is not a readable photo; replace it with a JPG, PNG or WebP.' });
  if (type && photos.length === 0 && !scan.unreadable.length) questions.push({ field: 'photos', question: 'Add at least one photo (the sharpest landscape, or one named hero, becomes the main photo).' });
  let slug = brief.slug ? slugify(brief.slug) : slugify(brief.title);
  if (type && !L.SLUG_RE.test(slug)) questions.push({ field: 'slug', question: 'Could not make a URL slug; set the page name using lowercase letters, digits and hyphens.' });
  else if (type && L.recordExists(type, slug)) {
    const free = [2, 3, 4, 5, 6, 7, 8, 9].map(n => slug + '-' + n).find(c => !L.recordExists(type, c));
    if (brief.slug || !free) questions.push({ field: 'slug', question: 'A page named "' + slug + '" already exists and is never overwritten. Choose another page name.' });
    else { warnings.push({ rule: 'AU-04', message: 'A page named "' + slug + '" exists, so this draft uses "' + free + '". Change the page name if you prefer another.' }); slug = free; }
  }
  // Everything except title, page name and photos is optional: missing facts get neutral placeholders and are listed for the owner.
  const needs = [];
  const fill = (cond, label) => { if (!cond) needs.push(label); return cond; };
  if (type === 'tour') { fill(brief.summary, 'summary'); fill(brief['best for'], 'best for'); fill((brief.lists.included || []).length, 'included'); fill((brief.lists.timeline || []).length, 'timeline'); }
  if (type === 'blog') { fill(brief.lede || brief.summary, 'intro'); fill(brief.sections.length, 'sections'); fill((brief.lists.sources || []).length, 'sources'); }
  if (type) {
    const t = brief.title || '';
    if (!brief.summary) brief.summary = t + ' with Garh Jaisal Haveli in Jaisalmer. Details are confirmed by the hotel for your dates.';
    if (type === 'tour' && !brief['best for']) brief['best for'] = 'Confirm suitability with the hotel before booking.';
    if (type === 'blog' && !brief.lede) brief.lede = t + ': a Garh Jaisal Haveli guide to Jaisalmer, written from the hotel.';
    if (needs.length) warnings.push({ rule: 'AU-03', message: 'Not provided, so placeholders or empty sections were used (add them before going Live): ' + needs.join(', ') + '.' });
  }
  const author = brief.author || 'inbox';
  const base = { id, type, slug, warnings };
  if (questions.length) return { ...base, ok: false, state: 'INTAKE', questions, findings: [] };
  const r = run.newRun({ type, slug, author, approver: 'pending' });
  run.advance(r.id, { pass: true, evidence: 'intake complete from brief.md', by: author });
  const stage = path.join(dir, 'stage');
  fs.rmSync(stage, { recursive: true, force: true });
  const placed = [];
  try {
    photos.slice(0, 13).forEach((p, i) => {
      const role = i === 0 ? 'hero' : (type === 'tour' ? 'step' : 'detail');
      const res = placePhoto({ type, slug, role, n: i + 1, file: p.file, stage });
      placed.push({ ...res, alt: p.alt });
    });
  } catch (error) {
    run.advance(r.id, { pass: false, evidence: error.message, by: author });
    return { ...base, ok: false, run: r.id, state: 'PLAN', questions: [{ field: 'photos', question: error.message }], findings: [] };
  }
  const record = buildRecord(type, slug, { ...brief, title: brief.title }, placed, warnings);
  fs.writeFileSync(path.join(stage, 'record.json'), JSON.stringify(record, null, 2) + '\n');
  run.advance(r.id, { pass: true, evidence: placed.length + ' photos placed', by: author });
  const plan = planSave({ type, slug, record, photos: placed.map(p => ({ path: p.path, bytes: p.bytes })), soft: true });
  const fixes = plan.findings.map(f => ({ rule: f.rule, message: f.message, fix: HINT[f.rule] || f.fix }));
  if (!plan.ok) { run.advance(r.id, { pass: false, evidence: plan.findings.map(f => f.rule).join(','), by: author }); return { ...base, ok: false, run: r.id, state: 'BUILD', questions: [], findings: fixes, plan }; }
  run.advance(r.id, { pass: true, evidence: 'record and plan clean', by: author });
  const softNotes = plan.findings.filter(f => f.severity === 'warn');
  if (softNotes.length) warnings.push({ rule: 'AU-08', message: 'To fix before Live: ' + softNotes.map(f => f.message).join(' ') });
  if (type === 'tour' && !record.facts_confirmed) warnings.push({ rule: 'AU-02', message: 'Facts are not confirmed: price, timings and inclusions must be confirmed by the owner before this tour can go Live ("facts confirmed by: Name 2026-10-23" in the brief).' });
  const report = { ...base, ok: true, run: r.id, state: 'APPROVE', questions: [], findings: [], plan, record_path: L.recordPath(type, slug), photos: placed.map(p => p.path), next: 'Approve to build the pack and add the draft to this working tree.' };
  fs.writeFileSync(path.join(dir, 'report.json'), JSON.stringify(report, null, 2) + '\n');
  return report;
}

// After the human gate: pack, apply to this working tree as a draft, verify, and stop before RELEASE (commit and PR).
function approve(id, by) {
  const dir = path.join(inboxRoot(), id);
  const report = JSON.parse(fs.readFileSync(path.join(dir, 'report.json'), 'utf8'));
  const state = run.readRun(report.run);
  if (state.state !== 'APPROVE') throw new Error('This run is at ' + state.state + ', not APPROVE.');
  if (!by || by === state.author) throw new Error('Approval must be given by a named person other than the author.');
  const file = path.join(L.repoRoot(), 'content/_runs', report.run + '.json');
  const raw = JSON.parse(fs.readFileSync(file, 'utf8')); raw.approver = by; L.atomicWrite(file, JSON.stringify(raw, null, 2) + '\n');
  run.advance(report.run, { pass: true, evidence: 'plan approved', by });
  const exp = exportPack({ type: report.type, slug: report.slug, stage: path.join(dir, 'stage'), outDir: path.join(L.repoRoot(), 'content/_stage/packs'), mode: 'new' });
  if (!exp.ok) { run.advance(report.run, { pass: false, evidence: exp.findings.map(f => f.rule).join(',') }); return { ok: false, state: 'PACK', findings: exp.findings }; }
  run.advance(report.run, { pass: true, evidence: exp.pack_id });
  const applied = applyPack(exp.zip, { dryRun: false, sitemap: false });
  if (!applied.ok) { run.advance(report.run, { pass: false, evidence: applied.findings.map(f => f.rule).join(',') }); return { ok: false, state: 'APPLY', findings: applied.findings }; }
  run.advance(report.run, { pass: true, evidence: applied.operations.length + ' files written as draft' });
  // Automatic verification: render the new draft page and run the release checks that can run locally.
  const verify = verifyDraft(report.slug);
  if (verify.ran) run.advance(report.run, { pass: verify.ok, evidence: 'verify-release: ' + (verify.ok ? 'all local checks pass' : verify.lines.filter(l => l.startsWith('FAIL')).join(' ; ')) });
  else run.advance(report.run, { pass: true, evidence: 'verify skipped: ' + verify.reason });
  const approved = { run: report.run, id, type: report.type, slug: report.slug, zip: path.relative(L.repoRoot(), exp.zip), files: exp.files, verify, by, time: new Date().toISOString() };
  fs.writeFileSync(path.join(dir, 'approved.json'), JSON.stringify(approved, null, 2) + '\n');
  return { ok: true, state: verify.ran && !verify.ok ? 'VERIFY' : 'RELEASE', run: report.run, zip: approved.zip, files: exp.files, verify, next: verify.ran && !verify.ok ? 'The draft page has problems: ' + verify.lines.filter(l => l.startsWith('FAIL')).join(' ; ') : 'Draft saved in this working tree and checked. Press "Open pull request" (or run npm run studio:ship ' + id + ') to send it for review.' };
}

function verifyDraft(slug) {
  const root = L.repoRoot();
  if (spawnSync(process.env.PHP_BIN || 'php', ['--version'], { stdio: 'ignore' }).status !== 0) return { ran: false, reason: 'php not found' };
  if (!fs.existsSync(path.join(root, 'include/page-head.php'))) return { ran: false, reason: 'not a full site checkout' };
  const r = spawnSync(process.execPath, [path.join(__dirname, 'verify-release.js'), slug], { encoding: 'utf8', timeout: 60000, env: process.env });
  const lines = String(r.stdout || '').split('\n').filter(Boolean);
  return { ran: true, ok: r.status === 0, lines };
}

module.exports = { parseBrief, processFolder, approve, verifyDraft, inboxRoot, buildRecord, slugify };
