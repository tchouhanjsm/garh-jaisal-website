#!/usr/bin/env node
'use strict';
// Route snapshot: hashes the rendered HTML of every public route so a content change can prove existing pages are unchanged.
// Usage: node scripts/studio/route-snapshot.js [--update] [--allow route1,route2]
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const http = require('node:http');
const { spawn } = require('node:child_process');

const ROOT = path.resolve(__dirname, '../..');
const FIXTURE = path.join(ROOT, 'tests/fixtures/route-snapshot.json');

function get(port, route) {
  return new Promise(resolve => {
    http.get({ host: '127.0.0.1', port, path: route, headers: { Host: '127.0.0.1:' + port } }, res => {
      const chunks = [];
      res.on('data', c => chunks.push(c));
      res.on('end', () => resolve({ status: res.statusCode, body: Buffer.concat(chunks).toString('utf8') }));
    }).on('error', () => resolve({ status: 0, body: '' }));
  });
}
const normalise = html => html.replace(/name="csrf[^"]*" value="[^"]*"/g, '').replace(/\b[a-f0-9]{32,64}\b/g, 'HASH').replace(/\?v=\d+/g, '');

function routes(root = ROOT) {
  const sitemap = fs.readFileSync(path.join(root, 'sitemap.xml'), 'utf8');
  const set = new Set(['/']);
  for (const m of sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)) { try { set.add(new URL(m[1]).pathname); } catch (_) {} }
  return [...set].sort();
}

async function snapshot(root = ROOT) {
  const port = 45000 + (process.pid % 10000);
  const php = spawn('php', ['-S', '127.0.0.1:' + port, '-t', root], { stdio: 'ignore' });
  try {
    for (let i = 0; i < 50; i++) { if ((await get(port, '/')).status) break; await new Promise(r => setTimeout(r, 100)); }
    const out = {};
    for (const route of routes(root)) {
      const r = await get(port, route);
      out[route] = { status: r.status, sha256: crypto.createHash('sha256').update(normalise(r.body)).digest('hex') };
    }
    return out;
  } finally { php.kill(); }
}

module.exports = { snapshot, routes };

if (require.main === module) {
  (async () => {
    const args = process.argv.slice(2);
    const current = await snapshot();
    const allow = new Set((args[args.indexOf('--allow') + 1] || '').split(',').filter(Boolean));
    if (args.includes('--against')) {
      // Compare this checkout with a git ref (for example origin/main): no stored fixture to go stale.
      const ref = args[args.indexOf('--against') + 1];
      const os = require('node:os');
      const { execSync } = require('node:child_process');
      const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'gj-base-'));
      try {
        execSync('git archive ' + JSON.stringify(ref) + ' | tar -x -C ' + JSON.stringify(dir), { cwd: ROOT, stdio: 'pipe', shell: '/bin/bash' });
        const base = await snapshot(dir);
        const problems = Object.keys(base).filter(r => (!current[r] || current[r].status !== base[r].status || current[r].sha256 !== base[r].sha256) && !allow.has(r));
        if (problems.length) { console.error('Existing routes changed versus ' + ref + ':\n  ' + problems.join('\n  ')); process.exit(1); }
        const added = Object.keys(current).filter(r => !base[r]);
        console.log('Existing ' + Object.keys(base).length + ' routes unchanged versus ' + ref + (added.length ? '; new routes: ' + added.join(', ') : '') + '.');
      } finally { fs.rmSync(dir, { recursive: true, force: true }); }
      return;
    }
    if (args.includes('--update')) { fs.writeFileSync(FIXTURE, JSON.stringify(current, null, 2) + '\n'); console.log('Snapshot written for ' + Object.keys(current).length + ' routes.'); return; }
    const base = JSON.parse(fs.readFileSync(FIXTURE, 'utf8'));
    const problems = [];
    for (const [route, v] of Object.entries(base)) {
      const now = current[route];
      if (!now) problems.push('missing route ' + route);
      else if ((now.status !== v.status || now.sha256 !== v.sha256) && !allow.has(route)) problems.push('changed ' + route + ' (' + v.status + ' -> ' + now.status + ')');
    }
    if (problems.length) { console.error('Route snapshot differs:\n  ' + problems.join('\n  ')); process.exit(1); }
    const added = Object.keys(current).filter(r => !base[r]);
    console.log('Route snapshot matches for ' + Object.keys(base).length + ' routes' + (added.length ? '; new routes: ' + added.join(', ') : '') + '.');
  })();
}
