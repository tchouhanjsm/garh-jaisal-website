#!/usr/bin/env node
'use strict';
// One command per tool, same code as the Studio form. Usage: node scripts/studio/cli.js <command> [options]
const fs = require('node:fs');
const path = require('node:path');
const L = require('./lib');
const { checkIntake, checkRecord } = require('./check-record');
const { planSave } = require('./plan-save');
const { placePhoto } = require('./place-photo');
const { exportPack } = require('./export-pack');
const { checkPack, applyPack, loadPack } = require('../check-content-pack');
const run = require('./run');

const [cmd, ...rest] = process.argv.slice(2);
const opt = {};
const pos = [];
for (let i = 0; i < rest.length; i++) { if (rest[i].startsWith('--')) { opt[rest[i].slice(2)] = rest[i + 1] && !rest[i + 1].startsWith('--') ? rest[++i] : true; } else pos.push(rest[i]); }
const readJson = f => JSON.parse(fs.readFileSync(f, 'utf8'));
const out = v => { console.log(JSON.stringify(v, null, 2)); };
const need = (k) => { if (!opt[k]) { console.error('Missing --' + k); process.exit(2); } return opt[k]; };

try {
  if (cmd === 'intake-check') { const q = checkIntake(readJson(pos[0])); out({ ok: q.length === 0, questions: q }); process.exit(q.length ? 1 : 0); }
  else if (cmd === 'record-check') {
    const type = need('type'); const slug = need('slug'); const rec = readJson(pos[0]);
    const f = checkRecord(rec, { type, slug, isNew: !L.recordExists(type, slug), packFiles: new Set() }); out({ ok: f.length === 0, findings: f }); process.exit(f.length ? 1 : 0);
  } else if (cmd === 'plan') {
    const stage = pos[0]; const type = need('type'); const slug = need('slug');
    const photos = [];
    const walk = d => { if (!fs.existsSync(d)) return; for (const n of fs.readdirSync(d)) { const a = path.join(d, n); if (fs.statSync(a).isDirectory()) walk(a); else photos.push({ path: path.relative(stage, a).split(path.sep).join('/'), bytes: fs.statSync(a).size }); } };
    walk(path.join(stage, 'img'));
    const r = planSave({ type, slug, record: readJson(path.join(stage, 'record.json')), photos }); out(r); process.exit(r.ok ? 0 : 1);
  } else if (cmd === 'photo') {
    out(placePhoto({ type: need('type'), slug: need('slug'), role: need('role'), n: Number(need('n')), file: pos[0], category: opt.category, stage: need('stage') }));
  } else if (cmd === 'export') {
    const r = exportPack({ type: need('type'), slug: need('slug'), stage: pos[0], outDir: opt.out || path.join(L.ROOT, 'content/_stage/packs') }); out(r); process.exit(r.ok ? 0 : 1);
  } else if (cmd === 'check') { const r = checkPack(loadPack(pos[0])); out({ ok: r.ok, findings: r.findings }); process.exit(r.ok ? 0 : 1); }
  else if (cmd === 'apply') { const r = applyPack(pos[0], { dryRun: !opt.write }); out(r); process.exit(r.ok ? 0 : 1); }
  else if (cmd === 'run-new') out(run.newRun({ type: need('type'), slug: need('slug'), author: need('author'), approver: need('approver') }));
  else if (cmd === 'run-step') out(run.advance(pos[0], { pass: opt.fail ? false : true, evidence: opt.evidence, by: opt.by }));
  else { console.error('Commands: intake-check, record-check, plan, photo, export, check, apply (--write), run-new, run-step'); process.exit(2); }
} catch (error) { console.error('Error: ' + error.message); process.exit(1); }
