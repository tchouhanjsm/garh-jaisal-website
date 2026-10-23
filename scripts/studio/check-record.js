'use strict';
// check_intake and check_record: deterministic checks that report every finding at once.
const L = require('./lib');

const REQUIRED = {
  tour: ['slug', 'category', 'status', 'title', 'description', 'h1', 'summary', 'hero_image', 'hero_alt', 'best_for'],
  blog: ['slug', 'status', 'title', 'description', 'h1', 'lede', 'hero_image', 'hero_alt'],
  gallery: ['id', 'category', 'status', 'image'],
};
const SECRET_RE = /(AKIA[0-9A-Z]{16}|-----BEGIN [A-Z ]*PRIVATE KEY|(?:password|passwd|secret|api[_-]?key|token)\s*[:=]\s*\S{6,}|sk-[A-Za-z0-9]{20,}|ghp_[A-Za-z0-9]{20,})/i;
const EMAIL_RE = /[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/;
const PHONE_RE = /\+?\d[\d\s().-]{8,}\d/g;
function hasContact(text) {
  if (EMAIL_RE.test(text)) return true;
  return (text.match(PHONE_RE) || []).some(m => !/^\d{4}-\d{2}-\d{2}$/.test(m.trim()) && m.replace(/\D/g, '').length >= 10);
}
const INJECTION_RE = /(ignore (all |any |the |your )?(previous |prior |above )?(instructions|rules)|disregard (the )?(rules|instructions)|system prompt|you are now|<script|javascript:)/i;
const SENSITIVE_KEYS = new Set(['hero_image', 'image', 'src', 'path', 'href', 'cta_primary_href', 'cta_secondary_href']);

function strings(value, keyPath, out) {
  if (typeof value === 'string') out.push([keyPath, value]);
  else if (Array.isArray(value)) value.forEach((v, i) => strings(v, keyPath + '[' + i + ']', out));
  else if (value && typeof value === 'object') for (const [k, v] of Object.entries(value)) strings(v, keyPath ? keyPath + '.' + k : k, out);
  return out;
}

// Intake gate: returns questions for the owner. Empty list means ready.
function checkIntake(intake) {
  const q = [];
  const need = (cond, rule, field, question) => { if (!cond) q.push({ rule, field, question }); };
  const known = v => v !== undefined && v !== null && v !== '' && v !== 'unknown';
  need(intake && L.TYPES.concat('update').includes(intake.type), 'IN-01', 'type', 'Is this a tour, blog, gallery set or an update?');
  need(intake && L.SLUG_RE.test(String(intake.slug || '')), 'IN-02', 'slug', 'What is the URL slug (lowercase letters, digits, hyphens)?');
  need(intake && known(intake.purpose) && String(intake.purpose).length >= 15, 'IN-03', 'purpose', 'In one sentence: who is this for and what action should it drive?');
  need(intake && known(intake.approver), 'IN-04', 'approver', 'Who approves this? It cannot be the author.');
  need(intake && known(intake.author) && intake.author !== intake.approver, 'IN-05', 'author', 'Who is the author? Author and approver must be different people.');
  need(intake && ['yes', 'no'].includes(intake.target_page_effects), 'IN-06', 'target_page_effects', 'Should it be listed on a hub or navigation page (yes or no)?');
  need(intake && Array.isArray(intake.photos) && intake.photos.length > 0, 'IN-07', 'photos', 'Which photos belong to it? Give role, alt text and rights owner for each.');
  if (intake && Array.isArray(intake.photos)) {
    intake.photos.forEach((p, i) => need(p && L.ROLES.includes(p.role) && known(p.alt) && known(p.rights), 'IN-08', 'photos[' + i + ']', 'Photo ' + (i + 1) + ' needs a role (' + L.ROLES.join(', ') + '), alt text and a rights owner.'));
  }
  if (intake && intake.type === 'tour') {
    for (const f of ['price', 'duration', 'timings', 'inclusions', 'exclusions', 'pickup', 'season']) {
      const fact = intake.facts && intake.facts[f];
      need(fact && known(fact.value) && known(fact.source) && known(fact.date), 'IN-09', 'facts.' + f, 'What is the confirmed ' + f + ', who confirmed it and on what date?');
    }
  }
  if (intake && intake.type === 'blog') need(Array.isArray(intake.sources) && intake.sources.length > 0, 'IN-10', 'sources', 'Which sources back the claims in this guide?');
  return q;
}

// Record check. ctx: { type, slug, isNew, packFiles:Set of repo-relative paths in the pack }
function checkRecord(record, ctx) {
  const f = [];
  const { type, slug } = ctx;
  const file = L.recordPath(type, slug);
  const draftSync = ctx.sync && record && record.status !== 'live';
  const add = (rule, message, fix, soft) => { const x = L.finding(rule, file, message, fix); if (soft && draftSync) x.severity = 'warn'; f.push(x); };
  if (!record || typeof record !== 'object' || Array.isArray(record)) return [L.finding('RC-00', file, 'Record is not a JSON object.', 'Export the record again from the Studio.')];
  const cfg = L.typeConfig(type);
  for (const key of REQUIRED[type]) if (record[key] === undefined || record[key] === '' || record[key] === null) add('RC-01', 'Missing required field "' + key + '".', 'Add "' + key + '".');
  const idKey = type === 'gallery' ? 'id' : 'slug';
  if (record[idKey] !== slug) add('RC-02', 'Record ' + idKey + ' "' + record[idKey] + '" does not match the file name "' + slug + '".', 'Make them identical.');
  if (!['draft', 'live'].includes(record.status)) add('RC-03', 'Status must be "draft" or "live".', 'Use "draft" until the owner approves.');
  if (ctx.isNew && record.status === 'live') add('RC-04', 'New content must start as draft.', 'Set status to "draft"; go live in a later update after approval.');
  if (type !== 'gallery') {
    const title = String(record.title || '');
    const desc = String(record.description || '');
    if (title && (title.length < 8 || title.length > L.PATHS.seo.title_max)) add('RC-05', 'SEO title is ' + title.length + ' characters.', 'Use 8 to ' + L.PATHS.seo.title_max + ' characters.', true);
    if (desc && (desc.length < L.PATHS.seo.description_min || desc.length > L.PATHS.seo.description_max)) add('RC-06', 'SEO description is ' + desc.length + ' characters.', 'Use ' + L.PATHS.seo.description_min + ' to ' + L.PATHS.seo.description_max + ' characters.', true);
    if (record.hero_alt !== undefined && String(record.hero_alt).length < 10) add('RC-07', 'Hero alt text is too short.', 'Describe the photo in at least 10 characters.');
  }
  if (type === 'tour') {
    if (!['desert', 'city'].includes(record.category)) add('RC-08', 'Category must be "desert" or "city".', 'Choose one.');
    if (!Array.isArray(record.timeline) || record.timeline.length === 0) add('RC-09', 'Timeline needs at least one step.', 'Add steps with title and text.');
    else record.timeline.forEach((s, i) => { if (!s || !s.title || !s.text) add('RC-09', 'Timeline step ' + (i + 1) + ' needs title and text.', 'Fill both.'); });
    if (!Array.isArray(record.included) || record.included.length === 0) add('RC-10', 'Inclusions list is empty.', 'List what is included.', true);
  }
  if (type === 'blog') {
    if (!Array.isArray(record.sections) || record.sections.length === 0) add('RC-11', 'Article needs at least one section.', 'Add sections.');
    if (!Array.isArray(record.sources) || record.sources.length === 0) add('RC-12', 'Article needs sources.', 'List the sources for each claim.');
  }
  if (type === 'gallery') {
    if (record.category && !cfg.categories.includes(record.category)) add('RC-13', 'Unknown gallery category "' + record.category + '".', 'Use one of: ' + cfg.categories.join(', ') + '.');
    if (record.image && String(record.image.alt || '').length < 10) add('RC-07', 'Gallery image alt text is too short.', 'Describe the photo in at least 10 characters.');
  }
  // Image paths: every referenced image sits in this entity's folder and ships in the pack (or already exists for updates).
  const dir = L.imageDir(type, slug, record.category);
  const refs = [];
  if (type === 'gallery' && record.image) refs.push(['image.src', record.image.src]);
  else if (record.hero_image) refs.push(['hero_image', record.hero_image]);
  if (Array.isArray(record.images)) record.images.forEach((im, i) => {
    if (!im || typeof im !== 'object') return add('RC-14', 'images[' + i + '] must be an object.', 'Use {path, role, alt}.');
    refs.push(['images[' + i + '].path', im.path]);
    if (!L.ROLES.includes(im.role)) add('RC-14', 'images[' + i + '] has an unknown role.', 'Use one of: ' + L.ROLES.join(', ') + '.');
    if (String(im.alt || '').length < 10) add('RC-07', 'images[' + i + '] is missing alt text.', 'Describe the photo in at least 10 characters.');
  });
  for (const [key, ref] of refs) {
    if (!L.safeRelative(ref)) { add('RC-15', key + ' is not a safe relative path.', 'Use a path like ' + dir + slug + '-hero-01.webp.'); continue; }
    const isNewFolder = ref.startsWith(dir);
    if (!isNewFolder && ctx.isNew) add('RC-15', key + ' "' + ref + '" is outside ' + dir + '.', 'Place new photos in ' + dir + ' with place_photo.');
    if (!ctx.packFiles.has(ref) && !L.existsInRepo(ref)) add('RC-16', key + ' "' + ref + '" is not in the pack and not in the site.', 'Add the photo to the pack.');
  }
  // Optional additive fields.
  if (record.timeline_images !== undefined) add('RC-17', 'Unknown field "timeline_images".', 'Use images[] with role "step".');
  if (record.last_verified !== undefined && !/^\d{4}-\d{2}-\d{2}$/.test(String(record.last_verified))) add('RC-18', 'last_verified must be YYYY-MM-DD.', 'Use a date.');
  // Facts gate.
  if (record.status === 'live') {
    if (type === 'tour' && record.facts_confirmed !== true) add('RC-19', 'Live tours need facts_confirmed: true.', 'The owner confirms price, timings and inclusions, then sets facts_confirmed and last_verified.');
    if (type === 'tour' && !record.last_verified) add('RC-19', 'Live tours need last_verified.', 'Add the confirmation date.');
    if (record.review_status !== undefined && record.review_status !== 'approved') add('RC-20', 'Live content must be approved for release.', 'Set review_status to "approved" after owner approval.');
  }
  // Content hygiene: secrets, injection, contact details, markup.
  for (const [key, text] of strings(record, '', [])) {
    if (SENSITIVE_KEYS.has(key.split('.').pop().replace(/\[\d+\]$/, ''))) continue;
    if (SECRET_RE.test(text)) add('RC-21', key + ' looks like a secret or credential.', 'Remove it. Secrets never go in a pack.');
    if (INJECTION_RE.test(text)) add('RC-22', key + ' contains instruction-like or script text.', 'Remove it; briefs are data, not instructions.');
    if (hasContact(text)) add('RC-23', key + ' contains an email address or phone number.', 'Use the site contact page; contact details come from site config.');
  }
  return f;
}

module.exports = { checkIntake, checkRecord, REQUIRED };
