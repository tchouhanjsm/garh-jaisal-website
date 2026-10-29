#!/usr/bin/env node
'use strict';
// Release checklist, automated where possible. Usage:
//   node scripts/studio/verify-release.js <slug> [--base https://www.hotelgarhjaisal.com] [--run <run-id>]
// Without --base it renders the page from this checkout with php -S (checklist items 4-6, 8).
// With --base it checks the live site (items 7-9). Browser overflow (item 4) is covered by the Playwright suite.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const https = require('node:https');
const { spawn } = require('node:child_process');
const L = require('./lib');

const args = process.argv.slice(2);
const slug = args.find(a => !a.startsWith('--'));
const opt = k => (args.includes('--' + k) ? args[args.indexOf('--' + k) + 1] : null);
if (!slug || !L.SLUG_RE.test(slug)) { console.error('Usage: node scripts/studio/verify-release.js <slug> [--base URL] [--run ID]'); process.exit(2); }

function fetchUrl(url) {
  return new Promise(resolve => {
    (url.startsWith('https') ? https : http).get(url, { headers: { 'User-Agent': 'content-studio-verify' } }, res => {
      const c = []; res.on('data', d => c.push(d)); res.on('end', () => resolve({ status: res.statusCode, body: Buffer.concat(c).toString('utf8') }));
    }).on('error', () => resolve({ status: 0, body: '' }));
  });
}
const meta = (html, re) => { const m = re.exec(html); return m ? m[1] : ''; };

(async () => {
  const checks = [];
  const add = (id, name, pass, evidence) => checks.push({ id, name, pass: !!pass, evidence });
  const type = L.TYPES.find(t => t !== 'gallery' && L.existsInRepo(L.recordPath(t, slug)));
  const record = type ? JSON.parse(fs.readFileSync(path.join(L.repoRoot(), L.recordPath(type, slug)), 'utf8')) : null;
  const status = record ? record.status : 'live'; // pages not created by the Studio are already live
  let php = null;
  let base = opt('base');
  if (!base) { const port = 46000 + (process.pid % 10000); php = spawn('php', ['-S', '127.0.0.1:' + port, '-t', L.repoRoot()], { stdio: 'ignore' }); base = 'http://127.0.0.1:' + port; for (let i = 0; i < 50; i++) { if ((await fetchUrl(base + '/')).status) break; await new Promise(r => setTimeout(r, 100)); } }
  const live = !!opt('base');
  try {
    const page = await fetchUrl(base.replace(/\/$/, '') + '/' + slug + '.php');
    add(7, live ? 'Live URL responds as expected' : 'Page renders', status === 'live' ? page.status === 200 : page.status === 404, 'HTTP ' + page.status + ', status ' + status);
    if (status !== 'live' && page.status === 404) {
      add(6, 'Draft is private (404 for the public), so page content checks are skipped until it is Live', true, 'draft returns 404');
      add(8, 'In sitemap only if live', !(await fetchUrl(base.replace(/\/$/, '') + '/sitemap.xml')).body.includes('/' + slug + '.php</loc>'), 'draft not in sitemap');
      return finish();
    }
    const html = page.body;
    const h1s = (html.match(/<h1[\s>]/g) || []).length;
    const title = meta(html, /<title>([^<]*)<\/title>/); const desc = meta(html, /<meta name="description" content="([^"]*)"/);
    add(6, 'One h1, title and description in range', h1s === 1 && title.length > 0 && title.length <= 70 && desc.length >= 110 && desc.length <= 160, 'h1=' + h1s + ' title=' + title.length + ' desc=' + desc.length);
    if (record && ['tour', 'blog', 'page'].includes(type)) {
      const caption = typeof record.hero_caption === 'string' ? record.hero_caption.trim() : '';
      const captionId = type === 'tour' ? 'experience-hero-caption' : type === 'blog' ? 'article-hero-caption' : 'studio-page-hero-caption';
      const visibleCaption = html.includes('id="' + captionId + '"');
      const linkedCaption = html.includes('aria-describedby="' + captionId + '"') && visibleCaption;
      const heroTag = (html.match(/<img\\b[^>]*aria-describedby="' + captionId + '"[^>]*>/) || [])[0] || '';
      const hasIntrinsicDimensions = /\\bwidth="[1-9][0-9]*"/.test(heroTag) && /\\bheight="[1-9][0-9]*"/.test(heroTag);
      const usesAsyncDecoding = /\\bdecoding="async"/.test(heroTag);
      add(4.1, 'Hero caption is reviewed and visibly rendered', caption.length >= 20 && caption.length <= 180 && visibleCaption, 'stored=' + caption.length + ' visible=' + visibleCaption);
      add(4.2, 'Hero image is programmatically associated with its visible caption', linkedCaption, 'captionId=' + captionId + ' linked=' + linkedCaption);
      add(4.3, 'Hero image declares intrinsic dimensions to reduce layout shift', hasIntrinsicDimensions, 'widthAndHeight=' + hasIntrinsicDimensions);
      add(4.4, 'Hero image uses asynchronous decoding', usesAsyncDecoding, 'decodingAsync=' + usesAsyncDecoding);
    }
    const robots = meta(html, /<meta name="robots" content="([^"]*)"/);
    add(9, 'Canonical present; robots matches status', /rel="canonical"/.test(html) && (status === 'live' ? !/noindex/.test(robots) : page.status === 404), 'robots=' + robots);
    const imgs = [...html.matchAll(/<img[^>]+src="([^"]+)"[^>]*>/g)].map(m => ({ src: m[1], tag: m[0] }));
    const bad = [];
    for (const im of imgs) {
      if (/^https?:|^data:/.test(im.src)) continue;
      const rel = im.src.replace(/^\//, '').split('?')[0];
      const abs = path.join(L.repoRoot(), rel);
      if (!fs.existsSync(abs)) bad.push('missing ' + rel); else if (rel.startsWith('img/tours/') || rel.startsWith('img/journal/') || rel.startsWith('img/gallery/')) { const cap = /-hero-/.test(rel) ? L.PATHS.limits.hero.max_bytes : L.PATHS.limits.other.max_bytes; if (fs.statSync(abs).size > cap) bad.push('over cap ' + rel); }
      const studioImage = /^img\/(tours|journal|gallery)\//.test(rel);
      if (studioImage ? !/\balt="[^"]{10,}"/.test(im.tag) : !/\balt="/.test(im.tag)) bad.push('no alt ' + rel);
    }
    add(5, 'Every image exists, within caps, has alt text', bad.length === 0, bad.join('; ') || imgs.length + ' images');
    const links = [...html.matchAll(/<a[^>]+href="([^"#?:]+\.php)"/g)].map(m => m[1].replace(/^\//, ''));
    const broken = [...new Set(links)].filter(l => !L.existsInRepo(l));
    add(6.1, 'Internal links resolve', broken.length === 0, broken.join(', ') || links.length + ' links');
    const sitemap = await fetchUrl(base.replace(/\/$/, '') + '/sitemap.xml');
    const inMap = sitemap.body.includes('/' + slug + '.php</loc>');
    add(8, 'In sitemap only if live', status === 'live' ? inMap : !inMap, 'in sitemap: ' + inMap);
  } finally { if (php) php.kill(); }
  finish();
  async function finish() {
  if (php) php.kill();
  for (const c of checks) console.log((c.pass ? 'PASS ' : 'FAIL ') + c.id + ' ' + c.name + ' - ' + c.evidence);
  const ok = checks.every(c => c.pass);
  if (opt('run')) { try { require('./run').advance(opt('run'), { pass: ok, evidence: 'verify-release ' + (live ? 'live' : 'local') + ' ' + checks.filter(c => !c.pass).map(c => c.id).join(',') }); } catch (e) { console.error(e.message); } }
  process.exit(ok ? 0 : 1);
  }
})();
