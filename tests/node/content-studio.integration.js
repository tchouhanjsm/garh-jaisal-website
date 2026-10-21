'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawn, spawnSync } = require('node:child_process');
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
const responsiveManifestPath = path.join(ROOT, 'scripts/responsive-image-assets-manifest.json');
const originalResponsiveManifest = fs.readFileSync(responsiveManifestPath);
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


function verifyV2TemplateAndAssetRegistry() {
  const file = path.join(ROOT, 'scripts/content-studio.js');
  const source = fs.readFileSync(file, 'utf8');
  const registryStart = source.indexOf('const PAGE_TEMPLATE_REGISTRY');
  const failStart = source.indexOf('function fail(statusCode, message)');
  assert.ok(registryStart >= 0 && failStart > registryStart, 'template registry should be declared before request handlers');
  const registrySource = source.slice(registryStart, failStart);
  const sandbox = { Object, Set, path, fs, ROOT, require: moduleName => { if (moduleName === 'node:path') return path; if (moduleName === 'node:fs') return fs; throw new Error('Unexpected registry test dependency: ' + moduleName); } };
  const exported = 'result = { PAGE_TEMPLATE_REGISTRY, STUDIO_MANAGED_PAGE_REGISTRY, APPROVED_STYLESHEET_PATHS, assertRegularRepoFile, assertApprovedStylesheet, getTemplateContract, validateRouteForTemplate, validateTemplateRegistry };';
  const compile = new vm.Script(registrySource + String.fromCharCode(10) + exported);
  vm.createContext(sandbox);
  compile.runInContext(sandbox, { timeout: 2000 });
  const api = sandbox.result;
  const result = api.validateTemplateRegistry();
  assert.equal(result.templateCount, 2, 'only explicit renderer-backed templates should be available');
  assert.equal(result.managedPageCount, 4, 'known routes should be registered for supported-field editing');
  assert.deepEqual(Array.from(api.getTemplateContract('experience-detail').stylesheetPaths), ['css/garh-v2.css', 'css/experiences.css', 'css/thar-sunset-story.css']);
  assert.deepEqual(Array.from(api.getTemplateContract('editorial-guide').stylesheetPaths), ['css/garh-v2.css', 'css/article.css', 'css/thar-sunset-story.css']);
  for (const unsafe of ['../include/page-head.php', '/etc/passwd', 'css/../../index.php', 'css\\garh-v2.css', 'https://example.com/evil.css']) {
    assert.throws(() => api.assertRegularRepoFile(unsafe, 'Test path'), /normalized repository-relative path/);
  }
  for (const unapproved of ['css/secret.css', 'index.php', 'https://example.com/remote.css']) {
    assert.throws(() => api.assertApprovedStylesheet(unapproved), /not approved/);
  }
  assert.throws(() => api.getTemplateContract('arbitrary-php'), /supported page template/);
  assert.throws(() => api.validateRouteForTemplate('same-route', 'experience-detail'), /already exists/);
  const symlinkCandidate = path.join(ROOT, '.content-studio-v2-test-link');
  assert.ok(!fs.existsSync(symlinkCandidate), 'symlink fixture must be safe and unused');
  try {
    fs.symlinkSync(path.join(ROOT, 'include', 'page-head.php'), symlinkCandidate);
    assert.throws(() => api.assertRegularRepoFile('.content-studio-v2-test-link', 'Test path'), /symbolic link/);
  } finally { try { fs.unlinkSync(symlinkCandidate); } catch (_) {} }
  const linkDirCandidate = path.join(ROOT, '.content-studio-v2-test-dir-link');
  assert.ok(!fs.existsSync(linkDirCandidate), 'directory symlink fixture must be safe and unused');
  try {
    fs.symlinkSync(path.join(ROOT, 'include'), linkDirCandidate, 'dir');
    assert.throws(() => api.assertRegularRepoFile('.content-studio-v2-test-dir-link/page-head.php', 'Test path'), /symbolic link/);
  } finally { try { fs.unlinkSync(linkDirCandidate); } catch (_) {} }
}

function verifyContentStudioAuthoringUi() {
  const launcherFile = path.join(ROOT, 'tools/content-studio/Start Content Studio.command');
  assert.ok(fs.existsSync(launcherFile), 'one-click macOS launcher should be present');
  const launcherSyntax = spawnSync('bash', ['-n', launcherFile], { encoding: 'utf8' });
  assert.equal(launcherSyntax.status, 0, 'macOS launcher shell syntax should be valid: ' + launcherSyntax.stderr);
  assert.ok((fs.statSync(launcherFile).mode & 0o111) !== 0, 'macOS launcher must be executable in Git');
  const launcher = fs.readFileSync(launcherFile, 'utf8');
  assert.match(launcher, /127\.0\.0\.1/, 'both local services must bind to loopback only');
  assert.match(launcher, /Garh Jaisal Content Studio is ready/);
  assert.match(launcher, /trap cleanup EXIT/, 'launcher must stop local services on exit');
  assert.doesNotMatch(launcher, /0\.0\.0\.0/, 'launcher must not expose the local servers to the network');

  const file = path.join(ROOT, 'tools/content-studio/index.html');
  const html = fs.readFileSync(file, 'utf8');
  const scripts = [...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)];
  assert.ok(scripts.length > 0, 'Content Studio must include its browser interaction script');
  scripts.forEach((match, index) => {
    new vm.Script(match[1], { filename: 'tools/content-studio/index.html inline script ' + (index + 1) });
  });
  for (const id of ['blog-section-list', 'tour-step-list', 'tour-faq-list', 'blog-source-list', 'managed-content-list', 'refresh-content']) {
    assert.ok(html.includes('id="' + id + '"'), 'guided authoring control should exist: ' + id);
  }
  assert.ok(html.includes('id="tab-manage"'), 'saved content manager tab should exist');
  assert.ok(html.includes('card.dataset.recordKey='), 'manager should assign a stable data-record-key identifier to each rendered record');
  assert.ok(html.includes("const method=edit?'PUT':'POST'"), 'the authoring forms should save edits back to the selected record');
  assert.ok(html.includes("method:'PATCH'"), 'the manager should change publication state explicitly');
  assert.ok(html.includes('Delete this Draft from the local working tree?'), 'deletion must require a confirmation');
  assert.ok(html.includes('Four steps to add content'), 'the editor should explain the workflow in plain language');
  assert.ok(html.includes('Live in local files — still not public'), 'the UI must state that saving does not publish to production');
  process.stdout.write('PASS Content Studio UI script syntax and nontechnical guided authoring controls.\\n');
}

async function verifyOneClickLauncher() {
  let output = '';
  let errors = '';
  const launcherFile = path.join(ROOT, 'tools/content-studio/Start Content Studio.command');
  const child = start(
    launcherFile,
    [],
    { CONTENT_STUDIO_PORT: String(STUDIO_PORT), CONTENT_PREVIEW_PORT: String(PHP_PORT) },
    chunk => { output += chunk; },
    chunk => { errors += chunk; },
  );

  async function waitForEndpoint(url, label) {
    let lastError = 'no response';
    for (let attempt = 0; attempt < 80; attempt++) {
      if (child.exitCode !== null || child.signalCode !== null) {
        throw new Error(label + ' stopped before ready.\n' + output + errors);
      }
      try {
        const response = await fetch(url, { signal: AbortSignal.timeout(800) });
        if (response.ok) return response;
        lastError = 'HTTP ' + response.status;
      } catch (error) {
        lastError = error.message;
      }
      await sleep(100);
    }
    throw new Error(label + ' did not become ready: ' + lastError + '\n' + output + errors);
  }

  async function assertStopped(url, label) {
    for (let attempt = 0; attempt < 30; attempt++) {
      try {
        await fetch(url, { signal: AbortSignal.timeout(300) });
      } catch (_) {
        return;
      }
      await sleep(100);
    }
    assert.fail(label + ' remained reachable after the launcher shut down.');
  }

  try {
    const statusResponse = await waitForEndpoint(STUDIO_URL + '/api/status', 'Content Studio');
    assert.equal(statusResponse.status, 200);
    const previewResponse = await waitForEndpoint(PHP_URL + '/blog.php', 'PHP preview');
    assert.equal(previewResponse.status, 200);
    for (let attempt = 0; attempt < 30 && !output.includes('Garh Jaisal Content Studio is ready'); attempt++) {
      await sleep(100);
    }
    assert.ok(
      output.includes('Garh Jaisal Content Studio is ready'),
      'launcher should report that both services are ready. stdout: ' + output + '\\nstderr: ' + errors,
    );
    assert.ok(output.includes(STUDIO_URL), 'launcher output should include the Content Studio address. stdout: ' + output);
    assert.ok(output.includes(PHP_URL), 'launcher output should include the PHP preview address. stdout: ' + output);
  } finally {
    if (child.exitCode === null && child.signalCode === null) {
      const exited = new Promise(resolve => child.once('exit', resolve));
      child.kill('SIGTERM');
      await Promise.race([exited, sleep(5000)]);
    }
  }

  await assertStopped(STUDIO_URL + '/api/status', 'Content Studio');
  await assertStopped(PHP_URL + '/blog.php', 'PHP preview');
  process.stdout.write('PASS one-click launcher starts the local editor and preview, then stops both services.\n');
}

async function main() {
  verifyV2TemplateAndAssetRegistry();
  verifyContentStudioAuthoringUi();
  await verifyOneClickLauncher();
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

  // Management lists only Content Studio JSON overlays; it must not edit legacy PHP registry entries.
  result = await requestJson(STUDIO_URL + '/api/content', 'GET');
  assert.equal(result.response.status, 200);
  assert.ok(result.body.items.some(item => item.type === 'tours' && item.slug === slugs[1] && item.status === 'draft'));
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1], 'GET');
  assert.equal(result.response.status, 200);
  assert.equal(result.body.record.slug, slugs[1]);
  result = await requestJson(STUDIO_URL + '/api/content/tours/longewala-tour', 'GET');
  assert.equal(result.response.status, 404, 'a legacy PHP registry entry must not be editable through the JSON-overlay manager');

  const updatedDraftTour = { ...tourPayload(slugs[1], 'draft'), title: 'Updated Content Studio Draft Test Tour', summary: 'An edited draft summary for validating safe content management updates.' };
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1], 'PUT', updatedDraftTour);
  assert.equal(result.response.status, 200, 'existing draft tour should be editable');
  assert.equal(JSON.parse(fs.readFileSync(recordFiles[1], 'utf8')).h1, 'Updated Content Studio Draft Test Tour');
  response = await fetch(PHP_URL + '/' + slugs[1] + '.php');
  html = await response.text();
  assert.ok(html.includes('Updated Content Studio Draft Test Tour'), 'edited copy should render through the existing page');

  // Hidden records and externally modified route wrappers are read-only through the manager.
  const draftTourBytes = fs.readFileSync(recordFiles[1]);
  const hiddenTour = JSON.parse(draftTourBytes.toString('utf8'));
  hiddenTour.status = 'hidden';
  fs.writeFileSync(recordFiles[1], JSON.stringify(hiddenTour, null, 2) + '\n');
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1], 'PUT', updatedDraftTour);
  assert.equal(result.response.status, 409, 'a Hidden record must not be edited through the manager');
  fs.writeFileSync(recordFiles[1], draftTourBytes);

  const generatedDraftRoute = fs.readFileSync(routeFiles[1]);
  fs.writeFileSync(routeFiles[1], Buffer.concat([generatedDraftRoute, Buffer.from('\n// operator change\n')]));
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1] + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 409, 'publishing must not modify a route wrapper changed outside Content Studio');
  fs.writeFileSync(routeFiles[1], generatedDraftRoute);

  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1] + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'a valid draft should be publishable locally');
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(sitemap.includes('/' + slugs[1] + '.php</loc>'), 'publishing should add the route to the sitemap');
  response = await fetch(PHP_URL + '/' + slugs[1] + '.php');
  html = await response.text();
  assert.doesNotMatch(html, /noindex,follow/, 'published tour should no longer be noindex');
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1], 'DELETE');
  assert.equal(result.response.status, 409, 'a live record must never be deleted directly');

  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1] + '/status', 'PATCH', { status: 'draft' });
  assert.equal(result.response.status, 200, 'a live record should be safely unpublishable');
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(!sitemap.includes('/' + slugs[1] + '.php</loc>'), 'unpublishing should remove the route from the sitemap');
  response = await fetch(PHP_URL + '/' + slugs[1] + '.php');
  html = await response.text();
  assert.match(html, /noindex,follow/, 'unpublished route should become noindex');
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1], 'DELETE');
  assert.equal(result.response.status, 200, 'a Draft may be deleted after it is unpublished');
  assert.ok(!fs.existsSync(routeFiles[1]));
  assert.ok(!fs.existsSync(recordFiles[1]));
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[1], 'DELETE');
  assert.equal(result.response.status, 404, 'deleted records should not be addressable again');

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

  const originalPublishedDate = JSON.parse(fs.readFileSync(recordFiles[2], 'utf8')).date_published;
  const editedBlog = { ...blog, h1: 'Content Studio Integration Test Guide Revised', seoTitle: 'Content Studio Integration Test Guide Revised', cardTitle: 'Content Studio Revised Archive Card' };
  result = await requestJson(STUDIO_URL + '/api/content/blogs/' + slugs[2], 'PUT', editedBlog);
  assert.equal(result.response.status, 200, 'existing blog guide should be editable');
  const updatedBlogRecord = JSON.parse(fs.readFileSync(recordFiles[2], 'utf8'));
  assert.equal(updatedBlogRecord.h1, 'Content Studio Integration Test Guide Revised');
  assert.equal(updatedBlogRecord.date_published, originalPublishedDate, 'editing must preserve the original publication date');
  response = await fetch(PHP_URL + '/blog.php');
  html = await response.text();
  assert.ok(html.includes('Content Studio Revised Archive Card'), 'edited title should update the Blog archive card');
  response = await fetch(PHP_URL + '/' + slugs[2] + '.php');
  html = await response.text();
  assert.ok(html.includes('Content Studio Integration Test Guide Revised'), 'edited article should render');
  result = await requestJson(STUDIO_URL + '/api/content/blogs/' + slugs[2] + '/status', 'PATCH', { status: 'draft' });
  assert.equal(result.response.status, 200);
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(!sitemap.includes('/' + slugs[2] + '.php</loc>'), 'unpublishing an article should remove it from the sitemap');
  response = await fetch(PHP_URL + '/' + slugs[2] + '.php');
  html = await response.text();
  assert.match(html, /noindex,follow/, 'unpublished article should become noindex');
  result = await requestJson(STUDIO_URL + '/api/content/blogs/' + slugs[2], 'DELETE');
  assert.equal(result.response.status, 200);
  assert.ok(!fs.existsSync(routeFiles[2]));
  assert.ok(!fs.existsSync(recordFiles[2]));

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
  const galleryId = path.basename(galleryRecordPath, '.json');
  result = await requestJson(STUDIO_URL + '/api/content', 'GET');
  assert.ok(result.body.items.some(item => item.type === 'gallery' && item.slug === galleryId && item.status === 'live'));
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId, 'PUT', {
    category: 'hotel', status: 'live', src: gallerySrc,
    alt: 'An updated temporary additional image for Content Studio integration testing',
    caption: 'Updated temporary CI verification photo', crop: 'portrait',
  });
  assert.equal(result.response.status, 200, 'Gallery metadata should be editable');
  let galleryRecord = JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8'));
  assert.equal(galleryRecord.image.alt, 'An updated temporary additional image for Content Studio integration testing');
  assert.equal(galleryRecord.image.crop, 'portrait');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId + '/status', 'PATCH', { status: 'draft' });
  assert.equal(result.response.status, 200, 'Gallery entries can be unpublished without removing the source image');
  galleryRecord = JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8'));
  assert.equal(galleryRecord.status, 'draft');
  response = await fetch(PHP_URL + '/gallery.php');
  html = await response.text();
  assert.ok(!html.includes('src="' + gallerySrc + '"'), 'Draft Gallery entries should not appear on the public Gallery route');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId, 'DELETE');
  assert.equal(result.response.status, 200, 'Draft Gallery entries can be removed');
  assert.ok(!fs.existsSync(galleryRecordPath));
  assert.ok(fs.existsSync(path.join(ROOT, gallerySrc)), 'deleting a Gallery record must retain its source image');

  // Upload signature guard: a fake JPG is rejected before any file is written.
  const badUpload = await fetch(STUDIO_URL + '/api/upload?filename=not-a-real-photo.jpg', {
    method: 'POST', headers: { 'Content-Type': 'image/jpeg' }, body: Buffer.from('this is not a JPEG image'),
  });
  assert.equal(badUpload.status, 400, 'invalid image bytes should be rejected');

  // A symlinked image directory must be rejected even if the final image file exists behind it.
  const unsafeImageLink = path.join(ROOT, 'img/content-studio-ci-symlink');
  assert.ok(!fs.existsSync(unsafeImageLink), 'refusing to overwrite a pre-existing image-path safety fixture');
  fs.symlinkSync(path.join(ROOT, 'img/Desert_Camp'), unsafeImageLink, 'dir');
  try {
    const symlinkImage = await requestJson(STUDIO_URL + '/api/gallery', 'POST', {
      category: 'hotel', status: 'draft', src: 'img/content-studio-ci-symlink/Tent.jpeg',
      alt: 'A test path that traverses a symlinked image directory', crop: 'landscape',
    });
    assert.equal(symlinkImage.response.status, 400, 'symlinked image directories must not be accepted');
  } finally {
    try { if (fs.lstatSync(unsafeImageLink).isSymbolicLink()) fs.unlinkSync(unsafeImageLink); } catch (_) {}
  }
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
    try {
      if (!fs.readFileSync(responsiveManifestPath).equals(originalResponsiveManifest)) {
        fs.writeFileSync(responsiveManifestPath, originalResponsiveManifest);
      }
    } catch (_) {}
    for (const dir of contentDirs) {
      if (!existedDirectories.get(dir)) {
        try {
          if (fs.existsSync(dir) && fs.readdirSync(dir).length === 0) fs.rmdirSync(dir);
        } catch (_) {}
      }
    }
  });
