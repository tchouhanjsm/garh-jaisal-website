'use strict';
// check_pack and apply_pack. A pack is a zip (or folder) that mirrors the site root plus pack.json.
// Usage: node scripts/check-content-pack.js <pack.zip|folder> [--apply] [--json]
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const L = require('./studio/lib');
const { checkRecord } = require('./studio/check-record');
const { readZip } = require('./studio/zip');

const META = new Set(['pack.json', 'README-PLACE-ME.txt']);

function loadPack(input) {
  const stat = fs.statSync(input);
  if (stat.isDirectory()) {
    const out = [];
    (function walk(dir) {
      for (const name of fs.readdirSync(dir)) {
        const abs = path.join(dir, name);
        const lst = fs.lstatSync(abs);
        if (lst.isDirectory()) walk(abs);
        else out.push({ name: path.relative(input, abs).split(path.sep).join('/'), data: lst.isSymbolicLink() ? Buffer.alloc(0) : fs.readFileSync(abs), symlink: lst.isSymbolicLink() });
      }
    })(input);
    return out;
  }
  return readZip(fs.readFileSync(input)).filter(e => !e.isDir);
}

function expectedPaths(entry, record) {
  const set = new Set([L.recordPath(entry.type, entry.slug)]);
  const page = L.pagePath(entry.type, entry.slug);
  if (page) set.add(page);
  return { set, imageDir: L.imageDir(entry.type, entry.slug, record && record.category) };
}

function checkPack(entries) {
  const f = [];
  const add = (rule, file, message, fix) => f.push(L.finding(rule, file, message, fix));
  const files = new Map();
  for (const e of entries) {
    if (e.symlink) { add('PK-03', e.name, 'Symbolic links are not allowed in a pack.', 'Replace the link with a real file.'); continue; }
    if (!L.safeRelative(e.name)) { add('PK-02', e.name, 'Unsafe path (absolute, "..", backslash or empty segment).', 'Rebuild the pack with export_pack.'); continue; }
    files.set(e.name, e.data);
  }
  if (!files.has('pack.json')) {
    const nested = [...files.keys()].find(n => n.endsWith('/pack.json'));
    add('PK-01', 'pack.json', nested ? 'pack.json found at "' + nested + '" instead of the pack root (unzipped one level too deep).' : 'pack.json is missing.',
      nested ? 'Move the contents of "' + nested.replace(/\/pack\.json$/, '') + '" up to the project root.' : 'Rebuild the pack with export_pack.');
    return { ok: false, findings: f, entries: [] };
  }
  let manifest;
  try { manifest = JSON.parse(files.get('pack.json').toString('utf8')); } catch (_) { manifest = null; }
  if (!manifest || manifest.version !== 1 || !/^[A-Za-z0-9_.-]{6,}$/.test(String(manifest.pack_id || '')) || !Array.isArray(manifest.entries) || manifest.entries.length === 0) {
    add('PK-01', 'pack.json', 'pack.json is not valid (needs version 1, pack_id and entries).', 'Rebuild the pack with export_pack.');
    return { ok: false, findings: f, entries: [] };
  }
  const declared = new Map();
  for (const entry of manifest.entries) for (const fl of (entry.files || [])) declared.set(fl.path, fl);
  // Integrity: declared vs present, hashes and sizes.
  for (const [p, fl] of declared) {
    if (!files.has(p)) { add('PK-06', p, 'Listed in pack.json but missing from the pack.', 'Re-export the pack.'); continue; }
    const data = files.get(p);
    if (data.length !== fl.bytes || L.sha256(data) !== fl.sha256) add('PK-05', p, 'File changed after export (size or SHA-256 does not match pack.json).', 'Re-export the pack; do not edit files inside it.');
  }
  for (const p of files.keys()) {
    if (META.has(p)) continue;
    if (p === 'sitemap.xml') { add('PK-07', p, 'sitemap.xml must never be in a pack.', 'Remove it; the sitemap is regenerated after apply.'); continue; }
    if (!declared.has(p)) add('PK-06', p, 'File is not listed in pack.json.', 'Remove it or re-export the pack.');
  }
  const allPaths = new Set([...declared.keys()].filter(p => files.has(p)));
  const seenSlugs = new Set();
  for (const entry of manifest.entries) {
    const label = String(entry.type) + ':' + String(entry.slug);
    if (!L.TYPES.includes(entry.type) || !L.SLUG_RE.test(String(entry.slug || '')) || !['new', 'update', 'sync'].includes(entry.mode)) {
      add('PK-04', label, 'Entry needs a known type, a valid slug and mode "new", "update" or "sync".', 'Rebuild the pack with export_pack.');
      continue;
    }
    if (seenSlugs.has(label)) { add('PK-12', label, 'Duplicate entry in one pack.', 'List each slug once.'); continue; }
    seenSlugs.add(label);
    const recFile = L.recordPath(entry.type, entry.slug);
    const exists = L.recordExists(entry.type, entry.slug);
    if (entry.mode === 'new' && exists) add('PK-12', recFile, 'A page or record with this slug already exists.', 'Choose a different slug, or export an update.');
    if (entry.mode === 'update' && !exists) add('PK-13', recFile, 'Update requested but the record does not exist.', 'Export it as a new entry.');
    let record = null;
    if (files.has(recFile)) {
      try { record = JSON.parse(files.get(recFile).toString('utf8')); } catch (_) { add('RC-00', recFile, 'Record is not valid JSON.', 'Re-export the pack.'); }
    } else add('PK-04', recFile, 'Record file is not in the pack at the expected path.', 'Expected ' + recFile + '.');
    const { set, imageDir } = expectedPaths(entry, record);
    const page = L.pagePath(entry.type, entry.slug);
    if (page && entry.mode !== 'update' && !files.has(page)) add('PK-04', page, 'Page wrapper is missing from the pack.', 'Expected ' + page + '.');
    if (page && files.has(page) && files.get(page).toString('utf8') !== L.wrapperText(entry.type, entry.slug)) add('PK-21', page, 'Page wrapper differs from the generated two-line wrapper.', 'Use the wrapper export_pack writes.');
    for (const fl of (entry.files || [])) {
      const p = fl.path;
      if (!files.has(p)) continue;
      const data = files.get(p);
      if (entry.mode === 'update') {
        const current = L.hashInRepo(p);
        if (current !== null && fl.prior_sha256 !== current) add('PK-18', p, 'The site file changed since this pack was exported (stale base).', 'Re-export the update from the current site.');
      } else if (entry.mode === 'new' && L.existsInRepo(p) && p.startsWith('img/')) add('PK-12', p, 'An image with this name already exists.', 'Use a different number or export an update with replace.');
      if (p.startsWith('img/')) {
        const dir = p.slice(0, p.lastIndexOf('/') + 1);
        if (dir !== imageDir) { add('PK-19', p, 'Photo is in the wrong folder. Expected ' + imageDir + ', found ' + dir + '.', 'Place photos with place_photo.'); continue; }
        const base = p.slice(dir.length);
        const ext = base.includes('.') ? base.split('.').pop() : '';
        if (!L.EXTS.includes(ext)) { add('PK-09', p, 'Unsupported image extension ".' + ext + '".', 'Use ' + L.EXTS.map(e => '.' + e).join(' or ') + '.'); continue; }
        const m = L.photoNameRe(entry.slug).exec(base);
        if (!m) { add('PK-08', p, 'Photo name breaks the rule <slug>-<role>-<nn>.<ext> (lowercase only).', 'Rename to ' + entry.slug + '-hero-01.webp style.'); continue; }
        if (fl.role && fl.role !== m[1]) add('PK-08', p, 'Role in pack.json ("' + fl.role + '") differs from the file name ("' + m[1] + '").', 'Use the same role.');
        const sniff = L.sniffImage(data);
        if (!sniff.kind || sniff.kind !== ext) { add('PK-10', p, 'File contents are not a real ' + ext + ' image.', 'Convert the photo with place_photo.'); continue; }
        const cap = m[1] === 'hero' ? L.PATHS.limits.hero : L.PATHS.limits.other;
        if (data.length > cap.max_bytes) add('PK-11', p, 'Photo is ' + data.length + ' bytes; the limit for "' + m[1] + '" is ' + cap.max_bytes + '.', 'Resize with place_photo.');
        if (sniff.width && sniff.width > cap.max_width) add('PK-11', p, 'Photo is ' + sniff.width + ' px wide; the limit for "' + m[1] + '" is ' + cap.max_width + '.', 'Resize with place_photo.');
      } else if (!set.has(p)) {
        const base = p.split('/').pop();
        const near = [...set].find(x => x.split('/').pop() === base);
        add('PK-04', p, 'Path is not in the path map for this entry.' + (near ? ' Expected ' + near + ', found ' + p + '.' : ''), 'Use the path from content/_paths.json.');
      }
    }
    if (record) {
      for (const x of checkRecord(record, { type: entry.type, slug: entry.slug, isNew: entry.mode === 'new', packFiles: allPaths, sync: entry.mode === 'sync' })) f.push(x);
      if (L.PATHS.types[entry.type].sitemap && record.status === 'live') entry._live = true;
    }
  }
  return { ok: f.every(x => x.severity === 'warn'), findings: f, manifest, files };
}

function runSitemap() {
  const r = spawnSync('php', [path.join(L.ROOT, 'scripts/generate-sitemap.php')], { cwd: L.ROOT, encoding: 'utf8' });
  return r.status === 0;
}

// apply_pack: dry run by default. Writes only after a clean check; rolls back on any failure.
function applyPack(input, opts = {}) {
  const dryRun = opts.dryRun !== false;
  const result = checkPack(loadPack(input));
  if (!result.ok) return { ok: false, dryRun, findings: result.findings, operations: [] };
  const root = L.repoRoot();
  const operations = [];
  for (const entry of result.manifest.entries) for (const fl of entry.files) {
    const prior = L.hashInRepo(fl.path);
    operations.push({ op: prior === null ? 'create' : (prior === fl.sha256 ? 'unchanged' : 'change'), path: fl.path, bytes: fl.bytes, sha256: fl.sha256, prior_sha256: prior });
  }
  if (dryRun) return { ok: true, dryRun, pack_id: result.manifest.pack_id, findings: [], operations };
  const backups = [];
  try {
    for (const op of operations) {
      if (op.op === 'unchanged') continue;
      const abs = path.join(root, op.path);
      backups.push({ abs, before: fs.existsSync(abs) ? fs.readFileSync(abs) : null });
      L.atomicWrite(abs, result.files.get(op.path));
    }
    const needsSitemap = result.manifest.entries.some(e => L.PATHS.types[e.type].sitemap);
    if (needsSitemap && opts.sitemap !== false && !runSitemap()) throw new Error('sitemap regeneration failed');
    const line = JSON.stringify({ time: new Date().toISOString(), pack_id: result.manifest.pack_id, entries: result.manifest.entries.map(e => e.type + ':' + e.slug + ':' + e.mode), files: operations.map(o => ({ path: o.path, op: o.op, sha256: o.sha256, prior_sha256: o.prior_sha256 })) }) + '\n';
    const logFile = path.join(root, 'content/_log/changes.jsonl');
    fs.mkdirSync(path.dirname(logFile), { recursive: true });
    fs.appendFileSync(logFile, line);
  } catch (error) {
    for (const b of backups.reverse()) { try { if (b.before === null) fs.unlinkSync(b.abs); else fs.writeFileSync(b.abs, b.before); } catch (_) {} }
    return { ok: false, dryRun, findings: [L.finding('AP-01', '', 'Apply failed and was rolled back: ' + error.message, 'Fix the cause and apply again.')], operations };
  }
  return { ok: true, dryRun, pack_id: result.manifest.pack_id, findings: [], operations };
}

module.exports = { loadPack, checkPack, applyPack };

if (require.main === module) {
  const args = process.argv.slice(2);
  const input = args.find(a => !a.startsWith('--'));
  if (!input) { console.error('Usage: node scripts/check-content-pack.js <pack.zip|folder> [--apply] [--json]'); process.exit(2); }
  let out;
  try { out = args.includes('--apply') ? applyPack(input, { dryRun: false }) : applyPack(input, { dryRun: true }); }
  catch (error) { out = { ok: false, findings: [L.finding('PK-01', input, 'Cannot read the pack: ' + error.message, 'Check the file path and that it is a zip.')], operations: [] }; }
  if (args.includes('--json')) console.log(JSON.stringify(out, null, 2));
  else {
    console.log(out.ok ? (out.dryRun ? 'PACK OK (dry run). Files that would be written:' : 'PACK APPLIED. Files written:') : 'PACK REJECTED:');
    for (const x of out.findings) console.log('  [' + x.rule + '] ' + x.file + ': ' + x.message + (x.fix ? ' Fix: ' + x.fix : ''));
    for (const o of out.operations || []) if (out.ok) console.log('  ' + o.op.padEnd(9) + o.path + ' (' + o.bytes + ' bytes)');
  }
  process.exit(out.ok ? 0 : 1);
}
