'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawn } = require('node:child_process');
const vm = require('node:vm');

const ROOT = path.resolve(__dirname, '../..');
const STUDIO_PORT = 43000 + (process.pid % 10000);
const PHP_PORT = STUDIO_PORT + 1;
const STUDIO_URL = 'http://127.0.0.1:' + STUDIO_PORT;
const PHP_URL = 'http://127.0.0.1:' + PHP_PORT;
const slugs = [
  'content-studio-ci-tour',
  'content-studio-ci-draft-tour',
  'content-studio-ci-guide',
];
const routeFiles = slugs.map(slug => path.join(ROOT, slug + '.php'));
const recordFiles = [
  path.join(ROOT, 'content/experiences/' + slugs[0] + '.json'),
  path.join(ROOT, 'content/experiences/' + slugs[1] + '.json'),
  path.join(ROOT, 'content/editorial/' + slugs[2] + '.json'),
];
const galleryDir = path.join(ROOT, 'content/gallery');
const galleryPrefix = 'content-studio-ci-photo-';
const sitemapPath = path.join(ROOT, 'sitemap.xml');
const originalSitemap = fs.readFileSync(sitemapPath);
const contentDirs = ['content/experiences', 'content/editorial', 'content/gallery'].map(p => path.join(ROOT, p));
const existedDirectories = new Map(contentDirs.map(dir => [dir, fs.existsSync(dir)]));
let galleryRecordPath = null;
const ownedPaths = new Set();
let sitemapTouched = false;
let studio = null;
let phpServer = null;
let studioOutput = '';
let studioErrors = '';
let phpOutput = '';
let phpErrors = '';

function noExistingFixtures() {
  for (const file of [...routeFiles, ...recordFiles]) {
    let occupied = false;
    try {
      fs.lstatSync(file);
      occupied = true;
    } catch (error) {
      if (error.code !== 'ENOENT') throw error;
    }
    assert.ok(!occupied, 'Refusing to overwrite fixture path: ' + path.relative(ROOT, file));
  }
  if (fs.existsSync(galleryDir)) {
    for (const name of fs.readdirSync(galleryDir, { withFileTypes: true }).filter(x => x.name.startsWith(galleryPrefix))) {
      assert.fail('Refusing to overwrite a previous Gallery test fixture: ' + name.name);
    }
  }
}

function start(command, args, env, onOutput, onError) {
  const child = spawn(command, args, { cwd: ROOT, env: { ...process.env, ...env }, stdio: ['ignore', 'pipe', 'pipe'] });
  child.stdout.on('data', chunk => onOutput(chunk.toString()));
  child.stderr.on('data', chunk => onError(chunk.toString()));
  return child;
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function waitFor(url, child, label) {
  let lastError = 'no response';
  for (let i = 0; i < 100; i++) {
    if (child.exitCode !== null) {
      throw new Error(label + ' exited early (' + child.exitCode + ').\n' + (label === 'Content Studio' ? studioErrors : phpErrors));
    }
    try {
      const response = await fetch(url, { signal: AbortSignal.timeout(1000) });
      if (response.ok) return response;
      lastError = 'HTTP ' + response.status;
    } catch (error) {
      lastError = error.message;
    }
    await sleep(100);
  }
  throw new Error(label + ' did not become ready: ' + lastError + '\n' + (label === 'Content Studio' ? studioOutput + studioErrors : phpOutput + phpErrors));
}

async function requestJson(url, method, payload) {
  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: payload === undefined ? undefined : JSON.stringify(payload),
    signal: AbortSignal.timeout(45000),
  });
  let body;
  try { body = await response.json(); } catch (_) { body = { error: 'Response was not valid JSON.' }; }
  return { response, body };
}

function tourPayload(slug, status) {
  return {
    title: status === 'draft' ? 'Content Studio Draft Test Tour' : 'Content Studio Integration Test Tour',
    slug, category: 'city', durationType: 'half-day', status,
    description: 'A temporary test-only tour that validates the new content publishing workflow.',
    summary: 'Temporary tour used to validate the current content authoring pipeline.',
    heroImage: 'img/desert3.jpg',
    heroAlt: 'Camel riders travelling over dunes near Jaisalmer',
    bestFor: 'Automated verification of the local publishing workflow.',
    timeline: 'Meet the guide | Confirm the meeting point for the selected date.\nExplore the route | Describe the verified itinerary.\nReturn to town | Confirm the final arrangement for the selected date.',
  };
}

function verifyContentStudioAuthoringUi() {
  const file = path.join(ROOT, 'tools/content-studio/index.html');
  const html = fs.readFileSync(file, 'utf8');
  const scripts = [...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)];
  assert.ok(scripts.length > 0, 'Content Studio must include its browser interaction script');
  scripts.forEach((match, index) => {
    new vm.Script(match[1], { filename: 'tools/content-studio/index.html inline script ' + (index + 1) });
  });
  for (const id of ['blog-section-list', 'tour-step-list', 'tour-faq-list', 'blog-source-list']) {
    assert.ok(html.includes('id="' + id + '"'), 'guided authoring control should exist: ' + id);
  }
  assert.ok(html.includes('Four steps to add content'), 'the editor should explain the workflow in plain language');
  assert.ok(html.includes('Live in local files — still not public'), 'the UI must state that saving does not publish to production');
  process.stdout.write('PASS Content Studio UI script syntax and nontechnical guided authoring controls.\\n');
}

async function main() {
  verifyContentStudioAuthoringUi();
  noExistingFixtures();
  for (const file of [...routeFiles, ...recordFiles]) ownedPaths.add(file);
  sitemapTouched = true;
  studio = start(process.execPath, ['scripts/content-studio.js'], { CONTENT_STUDIO_PORT: String(STUDIO_PORT) }, s => { studioOutput += s; }, s => { studioErrors += s; });
  await waitFor(STUDIO_URL + '/api/status', studio, 'Content Studio');

  phpServer = start('php', ['-S', '127.0.0.1:' + PHP_PORT, '-t', ROOT], {}, s => { phpOutput += s; }, s => { phpErrors += s; });
  await waitFor(PHP_URL + '/blog.php', phpServer, 'PHP preview server');

  // Live tour: data record, route wrapper, sitemap membership, route rendering and Trip Planner discovery.
  let result = await requestJson(STUDIO_URL + '/api/tours', 'POST', tourPayload(slugs[0], 'live'));
  assert.equal(result.response.status, 201, 'live tour should be created: ' + JSON.stringify(result.body));
  assert.ok(fs.existsSync(routeFiles[0]), 'tour route wrapper should exist');
  assert.equal(JSON.parse(fs.readFileSync(recordFiles[0], 'utf8')).status, 'live');
  let sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(sitemap.includes('/' + slugs[0] + '.php</loc>'), 'live tour should appear in sitemap');
  let response = await fetch(PHP_URL + '/' + slugs[0] + '.php');
  assert.equal(response.status, 200, 'new tour should render through the shared PHP renderer');
  let html = await response.text();
  assert.ok(html.includes('Content Studio Integration Test Tour'), 'tour H1 should render');
  response = await fetch(PHP_URL + '/catalogue.php');
  assert.equal(response.status, 200);
  assert.ok((await response.text()).includes('Content Studio Integration Test Tour'), 'new tour should appear in Trip Planner');

  // Existing routes must never be overwritten.
  const routeBefore = fs.readFileSync(routeFiles[0], 'utf8');
  result = await requestJson(STUDIO_URL + '/api/tours', 'POST', { ...tourPayload(slugs[0], 'live'), title: 'Attempted overwrite title' });
  assert.equal(result.response.status, 409, 'duplicate slug should be rejected');
  assert.equal(fs.readFileSync(routeFiles[0], 'utf8'), routeBefore, 'existing route must remain unchanged');

  // Draft tour: usable for local review but not exposed through the public sitemap or catalogue.
  result = await requestJson(STUDIO_URL + '/api/tours', 'POST', tourPayload(slugs[1], 'draft'));
  assert.equal(result.response.status, 201, 'draft tour should be created');
  assert.ok(fs.existsSync(routeFiles[1]));
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(!sitemap.includes('/' + slugs[1] + '.php</loc>'), 'draft tour must be absent from sitemap');
  response = await fetch(PHP_URL + '/' + slugs[1] + '.php');
  assert.equal(response.status, 200);
  html = await response.text();
  assert.match(html, /noindex,follow/, 'draft route must remain noindex');

  // Live blog: route, structured article content, archive card and sitemap must agree.
  const blog = {
    h1: 'Content Studio Integration Test Guide', slug: slugs[2], status: 'live',
    seoTitle: 'Content Studio Integration Test Guide',
    description: 'A temporary guide that validates blog creation, archive discovery and sitemap publication.',
    lede: 'This is temporary test content used to exercise the new authoring flow end to end.',
    eyebrow: 'Integration test',
    heroImage: 'img/desert3.jpg',
    heroAlt: 'Camel riders travelling over dunes near Jaisalmer',
    cardTitle: 'Content Studio Test Guide',
    cardDescription: 'A temporary article card used for CI verification.',
    sections: '## Test section\nThis temporary article body verifies the shared editorial renderer.\n\n## What this checks\n- The archive card comes from the same content record.',
    sources: 'Official Rajasthan Tourism | https://www.tourism.rajasthan.gov.in/ | Temporary integration test source.',
  };
  result = await requestJson(STUDIO_URL + '/api/blogs', 'POST', blog);
  assert.equal(result.response.status, 201, 'live blog should be created: ' + JSON.stringify(result.body));
  assert.ok(fs.existsSync(routeFiles[2]));
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(sitemap.includes('/' + slugs[2] + '.php</loc>'), 'live blog should appear in sitemap');
  response = await fetch(PHP_URL + '/blog.php');
  assert.equal(response.status, 200);
  html = await response.text();
  assert.ok(html.includes('href="' + slugs[2] + '.php"'), 'new blog should appear in the archive');
  assert.ok(html.includes('Content Studio Test Guide'), 'new archive card should have its authored title');
  response = await fetch(PHP_URL + '/' + slugs[2] + '.php');
  assert.equal(response.status, 200);
  html = await response.text();
  assert.ok(html.includes('Content Studio Integration Test Guide'), 'article content should render');
  assert.ok(html.includes('BlogPosting'), 'article structured data should render');

  // A Gallery addition uses a local, previously uncurated source and renders on the existing Gallery route.
  const gallerySourcePhp = fs.readFileSync(path.join(ROOT, 'include/gallery-data.php'), 'utf8');
  const used = new Set([...gallerySourcePhp.matchAll(/'src'\s*=>\s*'([^']+)'/g)].map(m => m[1]));
  if (fs.existsSync(galleryDir)) {
    for (const name of fs.readdirSync(galleryDir).filter(n => n.endsWith('.json'))) {
      const record = JSON.parse(fs.readFileSync(path.join(galleryDir, name), 'utf8'));
      if (record.image && typeof record.image.src === 'string') used.add(record.image.src);
    }
  }
  const candidates = ['img/desert12.jpg', 'img/s9.jpg', 'img/j2.jpg', 'img/j6.jpg'];
  const gallerySrc = candidates.find(candidate => fs.existsSync(path.join(ROOT, candidate)) && !used.has(candidate) && !fs.lstatSync(path.join(ROOT, candidate)).isSymbolicLink());
  assert.ok(gallerySrc, 'an existing unused Gallery image should be available for the test');
  result = await requestJson(STUDIO_URL + '/api/gallery', 'POST', {
    category: 'hotel', status: 'live', src: gallerySrc,
    alt: 'A temporary additional image for Content Studio integration testing',
    caption: 'Temporary CI verification photo', crop: 'landscape',
  });
  assert.equal(result.response.status, 201, 'live Gallery entry should be created: ' + JSON.stringify(result.body));
  galleryRecordPath = path.join(galleryDir, result.body.id + '.json');
  ownedPaths.add(galleryRecordPath);
  response = await fetch(PHP_URL + '/gallery.php');
  assert.equal(response.status, 200);
  html = await response.text();
  assert.ok(html.includes('src="' + gallerySrc + '"'), 'new Gallery image should render on the existing Gallery route');

  // Upload signature guard: a fake JPG is rejected before any file is written.
  const badUpload = await fetch(STUDIO_URL + '/api/upload?filename=not-a-real-photo.jpg', {
    method: 'POST', headers: { 'Content-Type': 'image/jpeg' }, body: Buffer.from('this is not a JPEG image'),
  });
  assert.equal(badUpload.status, 400, 'invalid image bytes should be rejected');
}

main()
  .then(() => process.stdout.write('PASS Content Studio publishes live tour/blog/Gallery records and keeps draft/overwrite boundaries.\n'))
  .catch(error => {
    process.stderr.write('FAIL Content Studio publishing integration: ' + (error.stack || error.message || String(error)) + '\n');
    process.stderr.write(studioOutput + studioErrors + phpOutput + phpErrors);
    process.exitCode = 1;
  })
  .finally(() => {
    if (studio) studio.kill('SIGTERM');
    if (phpServer) phpServer.kill('SIGTERM');
    for (const file of ownedPaths) {
      try { if (fs.existsSync(file) && !fs.lstatSync(file).isSymbolicLink()) fs.unlinkSync(file); } catch (_) {}
    }
    try { if (sitemapTouched) fs.writeFileSync(sitemapPath, originalSitemap); } catch (_) {}
    for (const dir of contentDirs) {
      if (!existedDirectories.get(dir)) {
        try {
          if (fs.existsSync(dir) && fs.readdirSync(dir).length === 0) fs.rmdirSync(dir);
        } catch (_) {}
      }
    }
  });
