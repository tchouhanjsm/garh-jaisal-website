'use strict';
// export_pack: stage folder -> zip + pack.json. Refuses any entry that fails the checks.
// Stage folder layout: record.json plus img/... placed by place_photo.
const fs = require('node:fs');
const path = require('node:path');
const L = require('./lib');
const { writeZip } = require('./zip');
const { checkPack } = require('../check-content-pack');

function listFiles(dir, base = dir) {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const name of fs.readdirSync(dir)) {
    const abs = path.join(dir, name);
    const st = fs.lstatSync(abs);
    if (st.isDirectory()) out.push(...listFiles(abs, base));
    else if (st.isFile()) out.push(path.relative(base, abs).split(path.sep).join('/'));
  }
  return out;
}

function exportPack({ type, slug, stage, outDir, mode, version = 1, date }) {
  const record = JSON.parse(fs.readFileSync(path.join(stage, 'record.json'), 'utf8'));
  const exists = L.recordExists(type, slug);
  const effective = mode || (exists ? 'update' : 'new');
  const files = new Map();
  const roles = new Map();
  files.set(L.recordPath(type, slug), Buffer.from(JSON.stringify(record, null, 2) + '\n'));
  if (L.pagePath(type, slug) && effective === 'new') files.set(L.pagePath(type, slug), Buffer.from(L.wrapperText(type, slug)));
  for (const rel of listFiles(path.join(stage, 'img'), stage)) {
    const full = path.join(stage, rel);
    files.set(rel, fs.readFileSync(full));
    const m = L.photoNameRe(slug).exec(rel.split('/').pop());
    if (m) roles.set(rel, m[1]);
  }
  const manifest = {
    version: 1,
    pack_id: 'gj-pack_' + slug + '_' + (date || new Date().toISOString().slice(0, 10)) + '_v' + version,
    created: (date || new Date().toISOString().slice(0, 10)),
    entries: [{
      type, slug, mode: effective, status: record.status,
      files: [...files.keys()].sort().map(p => ({ path: p, role: roles.get(p) || (p.endsWith('.json') ? 'record' : 'page'), bytes: files.get(p).length, sha256: L.sha256(files.get(p)), prior_sha256: L.hashInRepo(p) })),
    }],
  };
  const readme = 'Garh Jaisal content pack ' + manifest.pack_id + '\n\nUnzip into the project root so that the folders content/ and img/ line up with the site.\nThen run: node scripts/check-content-pack.js <this zip>   (add --apply to write the files)\nsitemap.xml is regenerated after apply. Do not edit files inside the pack.\n';
  const all = new Map(files);
  all.set('pack.json', Buffer.from(JSON.stringify(manifest, null, 2) + '\n'));
  all.set('README-PLACE-ME.txt', Buffer.from(readme));
  const check = checkPack([...all].map(([name, data]) => ({ name, data, symlink: false })));
  if (!check.ok) return { ok: false, findings: check.findings };
  fs.mkdirSync(outDir, { recursive: true });
  const zipPath = path.join(outDir, manifest.pack_id + '.zip');
  fs.writeFileSync(zipPath, writeZip([...all]));
  return { ok: true, zip: zipPath, pack_id: manifest.pack_id, files: manifest.entries[0].files.map(f => f.path) };
}

// Export a record that already lives in this working tree (saved by the Studio form) as a "sync" pack:
// applying it makes another checkout (or the cPanel copy) match this record. Images in the entity's own
// folder travel in the pack; shared site images are assumed to exist already. Legacy flat uploads are refused.
function exportFromTree({ type, slug, outDir, date, version = 1 }) {
  const root = L.repoRoot();
  const recFile = L.recordPath(type, slug);
  if (!fs.existsSync(path.join(root, recFile))) return { ok: false, findings: [L.finding('EX-01', recFile, 'No saved record at this path.', 'Save the content in the Studio first.')] };
  const record = JSON.parse(fs.readFileSync(path.join(root, recFile), 'utf8'));
  const dir = L.imageDir(type, slug, record.category);
  const refs = new Set();
  if (type === 'gallery' && record.image && record.image.src) refs.add(record.image.src);
  if (record.hero_image) refs.add(record.hero_image);
  for (const im of (Array.isArray(record.images) ? record.images : [])) if (im && im.path) refs.add(im.path);
  const findings = [];
  const files = new Map([[recFile, Buffer.from(JSON.stringify(record, null, 2) + '\n')]]);
  if (L.pagePath(type, slug)) files.set(L.pagePath(type, slug), Buffer.from(L.wrapperText(type, slug)));
  const roles = new Map();
  for (const ref of refs) {
    if (!L.safeRelative(ref)) { findings.push(L.finding('EX-02', ref, 'Unsafe image path in the record.', 'Choose the photo again in the Studio.')); continue; }
    if (ref.startsWith('img/content-studio/')) { findings.push(L.finding('EX-03', ref, 'This photo was uploaded the old way (flat folder) and cannot travel in a pack.', 'Upload it again from the Studio form so it is stored in ' + dir + '.')); continue; }
    if (!ref.startsWith(dir)) continue; // shared site photo: expected to exist on the target already
    const abs = path.join(root, ref);
    if (!fs.existsSync(abs)) { findings.push(L.finding('EX-04', ref, 'Photo file is missing from this working tree.', 'Upload it again.')); continue; }
    files.set(ref, fs.readFileSync(abs));
    const m = L.photoNameRe(slug).exec(ref.split('/').pop());
    if (m) roles.set(ref, m[1]);
  }
  if (findings.length) return { ok: false, findings };
  const day = date || new Date().toISOString().slice(0, 10);
  const manifest = {
    version: 1, pack_id: 'gj-pack_' + slug + '_' + day + '_v' + version + '_sync', created: day,
    entries: [{ type, slug, mode: 'sync', status: record.status, files: [...files.keys()].sort().map(p => ({ path: p, role: roles.get(p) || (p.endsWith('.json') ? 'record' : 'page'), bytes: files.get(p).length, sha256: L.sha256(files.get(p)) })) }],
  };
  const readme = 'Garh Jaisal content pack ' + manifest.pack_id + '\n\nUnzip into the project root of the other checkout so content/ and img/ line up, then run:\n  node scripts/check-content-pack.js <this zip> --apply\nsitemap.xml is regenerated after apply. Do not edit files inside the pack.\n';
  const all = new Map(files);
  all.set('pack.json', Buffer.from(JSON.stringify(manifest, null, 2) + '\n'));
  all.set('README-PLACE-ME.txt', Buffer.from(readme));
  const check = checkPack([...all].map(([name, data]) => ({ name, data, symlink: false })));
  if (!check.ok) return { ok: false, findings: check.findings.filter(x => x.severity !== 'warn') };
  fs.mkdirSync(outDir, { recursive: true });
  const zipPath = path.join(outDir, manifest.pack_id + '.zip');
  fs.writeFileSync(zipPath, writeZip([...all]));
  return { ok: true, zip: zipPath, pack_id: manifest.pack_id, files: manifest.entries[0].files.map(f => f.path), warnings: check.findings.filter(x => x.severity === 'warn') };
}

module.exports = { exportPack, exportFromTree };
