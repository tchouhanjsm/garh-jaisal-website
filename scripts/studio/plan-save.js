'use strict';
// plan_save: the dry run. Lists every file that would be created or changed. Writes nothing.
const L = require('./lib');
const { checkRecord } = require('./check-record');

// photos: [{ path, bytes }] already placed in the stage folder.
function planSave({ type, slug, record, photos = [], mode, soft = false }) {
  const findings = [];
  if (!L.TYPES.includes(type)) return { ok: false, findings: [L.finding('PL-01', '', 'Unknown type "' + type + '".', 'Use one of: ' + L.TYPES.join(', ') + '.')], operations: [] };
  if (!L.SLUG_RE.test(String(slug || ''))) return { ok: false, findings: [L.finding('PL-02', '', 'Invalid slug.', 'Use lowercase letters, digits and hyphens.')], operations: [] };
  const exists = L.recordExists(type, slug);
  const effective = mode || (exists ? 'update' : 'new');
  if (effective === 'new' && exists) findings.push(L.finding('PL-03', L.recordPath(type, slug), 'Slug already exists; existing pages are never overwritten by a new entry.', 'Choose another slug or plan an update.'));
  if (effective === 'update' && !exists) findings.push(L.finding('PL-04', L.recordPath(type, slug), 'Update requested but nothing exists to update.', 'Plan it as new.'));
  const files = new Set([L.recordPath(type, slug), ...(L.pagePath(type, slug) ? [L.pagePath(type, slug)] : []), ...photos.map(p => p.path)]);
  findings.push(...checkRecord(record, { type, slug, isNew: effective === 'new', packFiles: files, sync: soft }));
  const operations = [];
  const add = (p, bytes) => operations.push({ op: L.existsInRepo(p) ? 'change' : 'create', path: p, bytes: bytes, prior_sha256: L.hashInRepo(p) });
  add(L.recordPath(type, slug), Buffer.byteLength(JSON.stringify(record, null, 2)));
  if (L.pagePath(type, slug) && effective === 'new') add(L.pagePath(type, slug), Buffer.byteLength(L.wrapperText(type, slug)));
  for (const p of photos) add(p.path, p.bytes);
  const effects = L.PATHS.types[type].sitemap && record && record.status === 'live' ? ['sitemap.xml regenerated after apply'] : [];
  return { ok: findings.every(f => f.severity === 'warn'), mode: effective, findings, operations, effects };
}

module.exports = { planSave };
