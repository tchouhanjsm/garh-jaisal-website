'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawn, spawnSync } = require('node:child_process');
const http = require('node:http');
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
const pageSlug = 'content-studio-ci-landing-page';
const pageRouteFile = path.join(ROOT, pageSlug + '.php');
const pageRecordFile = path.join(ROOT, 'content/pages/' + pageSlug + '.json');
const pageCssDirectory = path.join(ROOT, 'css/pages');
const pageCssFile = path.join(pageCssDirectory, pageSlug + '.css');
const existedPageCssDirectory = fs.existsSync(pageCssDirectory);
const galleryDir = path.join(ROOT, 'content/gallery');
const galleryPrefix = 'content-studio-ci-photo-';
const sitemapPath = path.join(ROOT, 'sitemap.xml');
const originalSitemap = fs.readFileSync(sitemapPath);
const responsiveManifestPath = path.join(ROOT, 'scripts/responsive-image-assets-manifest.json');
const originalResponsiveManifest = fs.readFileSync(responsiveManifestPath);
const contentDirs = ['content/experiences', 'content/editorial', 'content/gallery', 'content/pages'].map(p => path.join(ROOT, p));
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
  for (const file of [...routeFiles, ...recordFiles, pageRouteFile, pageRecordFile, pageCssFile]) {
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

async function transitionReview(type, slug, reviewStatus) {
  const result = await requestJson(STUDIO_URL + '/api/content/' + type + '/' + slug + '/review', 'PATCH', { reviewStatus });
  assert.equal(result.response.status, 200, 'review transition to ' + reviewStatus + ' should succeed: ' + JSON.stringify(result.body));
  return result;
}

function requestStatusWithHost(url, host) {
  return new Promise((resolve, reject) => {
    const request = http.request(url, { headers: { Host: host } }, response => {
      response.resume();
      resolve({ statusCode: response.statusCode });
    });
    request.on('error', reject);
    request.end();
  });
}

function landingPagePayload(slug, theme = 'heritage') {
  return {
    h1: 'Content Studio Landing Page Integration Test',
    slug,
    status: 'draft',
    seoTitle: 'Content Studio Landing Page Integration Test',
    description: 'A temporary landing page used to validate the reusable design and publication workflow.',
    eyebrow: 'Integration test',
    theme,
    heroLayout: 'split',
    heroIntro: 'A temporary landing page introduction used to confirm the real shared PHP renderer.',
    heroImage: 'img/desert3.jpg',
    heroAlt: 'Camel riders travelling over dunes near Jaisalmer',
    heroCtaLabel: 'Contact the hotel',
    heroCtaHref: '/contact.php',
    sections: JSON.stringify([
      { type: 'intro', id: 'plan-your-visit', heading: 'Plan your visit', body: 'This temporary introduction confirms that authored plain text is rendered through a safe page section.' },
      { type: 'image_text', id: 'a-closer-look', heading: 'A closer look', body: 'A reusable image-and-text block keeps the page consistent with the site design.', image: 'img/desert12.jpg', alt: 'A curated Jaisalmer destination photograph', image_side: 'right', link_label: 'Explore the rooms', link_href: '/room.php' },
      { type: 'feature_list', id: 'what-to-know', heading: 'What to know', items: ['Confirm current arrangements', 'Check details for your travel date'] },
      { type: 'faq', id: 'common-questions', heading: 'Common questions', items: [{ question: 'What should guests confirm?', answer: 'Ask the hotel to confirm current details before departure.' }] },
      { type: 'cta', id: 'next-step', heading: 'Plan the next step', body: 'Contact the hotel to discuss the requirements for your selected dates.', button_label: 'Contact the hotel', button_href: '/contact.php' },
    ]),
  };
}

function tourPayload(slug, status) {
  return {
    title: slug === slugs[0] ? 'Content Studio Integration Test Tour' : 'Content Studio Draft Test Tour',
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
  const registry = api.PAGE_TEMPLATE_REGISTRY;
  const result = { templateCount: Object.keys(registry).length, managedPageCount: Object.keys(api.STUDIO_MANAGED_PAGE_REGISTRY).length };
  for (const templateId of Object.keys(registry)) {
    const contract = api.getTemplateContract(templateId);
    assert.ok(contract.renderer.endsWith('.php'), 'registered renderers must remain PHP templates');
  }
  assert.equal(result.templateCount, 3, 'only explicit renderer-backed templates should be available');
  assert.deepEqual(Array.from(api.getTemplateContract('landing-page').stylesheetPaths), ['css/garh-v2.css', 'css/page-builder.css']);
  assert.ok(api.getTemplateContract('landing-page').requiredFields.includes('sections'), 'landing-page schema must include controlled sections');
  assert.equal(result.managedPageCount, 4, 'known routes should be registered for supported-field editing');
  assert.deepEqual(Array.from(api.getTemplateContract('experience-detail').stylesheetPaths), ['css/garh-v2.css', 'css/experiences.css', 'css/thar-sunset-story.css']);
  assert.deepEqual(Array.from(api.getTemplateContract('editorial-guide').stylesheetPaths), ['css/garh-v2.css', 'css/article.css', 'css/thar-sunset-story.css']);
  for (const unsafe of ['../include/page-head.php', '/etc/passwd', 'css/../../index.php', 'css\\garh-v2.css']) {
    assert.throws(() => api.assertRegularRepoFile(unsafe, 'Test path'), /normalized repository-relative path/);
  }
  assert.throws(() => api.assertApprovedStylesheet('https://example.com/evil.css'), /not approved/);
  for (const unapproved of ['css/secret.css', 'index.php', 'https://example.com/remote.css']) {
    assert.throws(() => api.assertApprovedStylesheet(unapproved), /not approved/);
  }
  assert.throws(() => api.getTemplateContract('arbitrary-php'), /supported page template/);
  assert.throws(() => api.validateRouteForTemplate('pack', 'experience-detail'), /already exists/);
  assert.throws(() => api.validateRouteForTemplate('../unsafe-route', 'experience-detail'), /route slug/);
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
  for (const id of ['blog-section-list', 'tour-step-list', 'tour-faq-list', 'blog-source-list', 'page-form', 'page-section-list', 'page-add-section', 'page-preview', 'managed-content-list', 'refresh-content']) {
    assert.ok(html.includes('id="' + id + '"'), 'guided authoring control should exist: ' + id);
  }
  assert.ok(html.includes('id="tab-manage"'), 'saved content manager tab should exist');
  assert.ok(html.includes('card.dataset.recordKey='), 'manager should assign a stable data-record-key identifier to each rendered record');
  assert.ok(html.includes("const method=edit?'PUT':'POST'"), 'the authoring forms should save edits back to the selected record');
  assert.ok(html.includes("method:'PATCH'"), 'the manager should change publication state explicitly');
  assert.ok(html.includes('Delete this Draft from the local working tree?'), 'deletion must require a confirmation');
  assert.ok(html.includes('Four steps to add content'), 'the editor should explain the workflow in plain language');
  assert.ok(html.includes('Live locally — never public by itself'), 'the UI must state that publication is local-only');
  assert.ok(html.includes('Preview PHP-rendered draft'), 'tour and blog authoring must expose the actual PHP-rendered preview');
  assert.ok(html.includes('createPageSection'), 'landing pages must use the guided reusable section builder');
  assert.ok(html.includes('type:\'landing-page\'' ) || html.includes('data-pane="pages"'), 'landing-page authoring must be exposed in Content Studio');
  assert.ok(html.includes('Approved for release'), 'the UI must explain release approval separately from publication');
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
  for (const file of [...routeFiles, ...recordFiles, pageRouteFile, pageRecordFile, pageCssFile]) ownedPaths.add(file);
  sitemapTouched = true;
  studio = start(process.execPath, ['scripts/content-studio.js'], { CONTENT_STUDIO_PORT: String(STUDIO_PORT) }, s => { studioOutput += s; }, s => { studioErrors += s; });
  const studioReadyResponse = await waitFor(STUDIO_URL + '/api/status', studio, 'Content Studio');
  const studioCsp = studioReadyResponse.headers.get('content-security-policy') || '';
  assert.match(studioCsp, /frame-src[^;]*http:\/\/127\.0\.0\.1:\*[^;]*http:\/\/localhost:\*/,
    'Content Studio must allow its validated loopback PHP preview port in frames while keeping other origins blocked.');

  phpServer = start('php', ['-S', '127.0.0.1:' + PHP_PORT, '-t', ROOT], {}, s => { phpOutput += s; }, s => { phpErrors += s; });
  await waitFor(PHP_URL + '/blog.php', phpServer, 'PHP preview server');

  // The new preview route renders an unsaved, token-bound draft through the real PHP experience template.
  let result = await requestJson(STUDIO_URL + '/api/preview', 'POST', { type: 'tours', payload: tourPayload('temporary-preview-input', 'draft') });
  assert.equal(result.response.status, 201, 'a PHP-rendered draft preview should be created: ' + JSON.stringify(result.body));
  assert.match(result.body.pagePath, /^\/tools\/content-studio\/preview\.php\?type=tours&token=[a-f0-9]{24}$/);
  const previewToken = result.body.token;
  const previewSlug = 'content-studio-preview-' + previewToken;
  const previewRecordPath = path.join(ROOT, 'content/experiences/' + previewSlug + '.json');
  ownedPaths.add(previewRecordPath);
  assert.ok(fs.existsSync(previewRecordPath), 'preview should create an ephemeral record without writing a PHP wrapper');
  assert.ok(!fs.existsSync(path.join(ROOT, previewSlug + '.php')), 'preview must not create a public route wrapper');
  assert.ok(!fs.readFileSync(sitemapPath, 'utf8').includes('/' + previewSlug + '.php</loc>'), 'preview must not modify sitemap membership');
  let response = await fetch(PHP_URL + result.body.pagePath);
  assert.equal(response.status, 200, 'preview route should return a rendered PHP page');
  let html = await response.text();
  assert.ok(html.includes('Content Studio Draft Test Tour'), 'preview should render form data through the existing experience renderer');
  assert.match(html, /noindex,follow/, 'preview must be non-indexable');
  const foreignHostPreview = await requestStatusWithHost(PHP_URL + result.body.pagePath, 'example.com');
  assert.equal(foreignHostPreview.statusCode, 404, 'preview route must reject requests with a non-local Host header');
  let managedResponse = await requestJson(STUDIO_URL + '/api/content', 'GET');
  assert.ok(!managedResponse.body.items.some(item => item.slug === previewSlug), 'ephemeral preview records must not appear in content manager');
  let cleanupResponse = await requestJson(STUDIO_URL + '/api/preview/cleanup', 'POST', { token: previewToken });
  assert.equal(cleanupResponse.response.status, 200);
  assert.equal(cleanupResponse.body.removed, true);
  assert.ok(!fs.existsSync(previewRecordPath), 'preview cleanup should remove only its token-matched JSON record');
  response = await fetch(PHP_URL + result.body.pagePath);
  assert.equal(response.status, 404, 'a closed preview token must no longer render');
  cleanupResponse = await requestJson(STUDIO_URL + '/api/preview/cleanup', 'POST', { token: '../etc/passwd' });
  assert.equal(cleanupResponse.response.status, 200, 'invalid cleanup tokens must not reach filesystem paths');
  assert.equal(cleanupResponse.body.removed, false);

  // Landing-page previews use the same guarded PHP renderer, generated design-token CSS, and token cleanup as tours/blogs.
  const landingPreview = await requestJson(STUDIO_URL + '/api/preview', 'POST', {
    type: 'pages', payload: landingPagePayload('temporary-preview-input'),
  });
  assert.equal(landingPreview.response.status, 201, 'landing-page PHP preview should be created: ' + JSON.stringify(landingPreview.body));
  assert.match(landingPreview.body.pagePath, /^\/tools\/content-studio\/preview\.php\?type=pages&token=[a-f0-9]{24}$/);
  const landingPreviewToken = landingPreview.body.token;
  const landingPreviewSlug = 'content-studio-preview-' + landingPreviewToken;
  const landingPreviewRecord = path.join(ROOT, 'content/pages/' + landingPreviewSlug + '.json');
  const landingPreviewCss = path.join(ROOT, 'css/pages/' + landingPreviewSlug + '.css');
  ownedPaths.add(landingPreviewRecord);
  ownedPaths.add(landingPreviewCss);
  assert.ok(fs.existsSync(landingPreviewRecord), 'landing preview must create a token-bound JSON record');
  assert.ok(fs.existsSync(landingPreviewCss), 'landing preview must render through a generated approved-token stylesheet');
  assert.ok(!fs.existsSync(path.join(ROOT, landingPreviewSlug + '.php')), 'landing preview must not create a routable PHP wrapper');
  assert.ok(!fs.readFileSync(sitemapPath, 'utf8').includes('/' + landingPreviewSlug + '.php</loc>'), 'landing preview must not enter the sitemap');
  response = await fetch(PHP_URL + landingPreview.body.pagePath);
  assert.equal(response.status, 200, 'landing preview should render with the actual PHP landing-page renderer');
  html = await response.text();
  assert.ok(html.includes('Content Studio Landing Page Integration Test'), 'landing preview should render its authored H1');
  assert.ok(html.includes('Plan your visit') && html.includes('Common questions'), 'landing preview should render the authored content blocks');
  assert.ok(html.includes('<h2>Plan your visit</h2>') && html.includes('<h2>Common questions</h2>'), 'landing preview must render authored section headings, not only their body content');
  assert.ok(html.includes('css/page-builder.css') && html.includes('css/pages/' + landingPreviewSlug + '.css'), 'landing preview should use shared CSS and the generated page token CSS');
  assert.match(html, /noindex,follow/, 'unsaved landing-page preview must be non-indexable');
  cleanupResponse = await requestJson(STUDIO_URL + '/api/preview/cleanup', 'POST', { token: landingPreviewToken });
  assert.equal(cleanupResponse.response.status, 200);
  assert.equal(cleanupResponse.body.removed, true);
  assert.ok(!fs.existsSync(landingPreviewRecord) && !fs.existsSync(landingPreviewCss), 'landing preview cleanup must remove its data and generated stylesheet');
  response = await fetch(PHP_URL + landingPreview.body.pagePath);
  assert.equal(response.status, 404, 'closed landing preview token must stop rendering');

  // New records cannot bypass the review workflow by being created directly as Live.
  result = await requestJson(STUDIO_URL + '/api/tours', 'POST', tourPayload(slugs[0], 'live'));
  assert.equal(result.response.status, 409, 'a new tour must not skip Draft, Ready for review and Approved for release');
  assert.ok(!fs.existsSync(routeFiles[0]), 'rejected direct-Live creation must not write a route');

  // Tour: Draft record, explicit review/approval, then local publication through the real renderer.
  result = await requestJson(STUDIO_URL + '/api/tours', 'POST', tourPayload(slugs[0], 'draft'));
  assert.equal(result.response.status, 201, 'tour draft should be created: ' + JSON.stringify(result.body));
  assert.ok(fs.existsSync(routeFiles[0]), 'tour route wrapper should exist');
  assert.equal(JSON.parse(fs.readFileSync(recordFiles[0], 'utf8')).status, 'draft');
  assert.equal(JSON.parse(fs.readFileSync(recordFiles[0], 'utf8')).review_status, 'draft');
  let sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(!sitemap.includes('/' + slugs[0] + '.php</loc>'), 'a newly saved draft must not appear in sitemap');
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[0] + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 409, 'Draft must not be made Live before release approval');
  await transitionReview('tours', slugs[0], 'ready_for_review');
  assert.equal(JSON.parse(fs.readFileSync(recordFiles[0], 'utf8')).status, 'draft', 'Ready for review must not publish content');
  await transitionReview('tours', slugs[0], 'approved_for_release');
  assert.ok(!fs.readFileSync(sitemapPath, 'utf8').includes('/' + slugs[0] + '.php</loc>'), 'approval alone must not change the sitemap');
  result = await requestJson(STUDIO_URL + '/api/content/tours/' + slugs[0] + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'approved tour can be marked Live in the local working tree');
  assert.ok(JSON.parse(fs.readFileSync(recordFiles[0], 'utf8')).review_status === 'approved_for_release');
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(sitemap.includes('/' + slugs[0] + '.php</loc>'), 'live tour should appear in sitemap');
  response = await fetch(PHP_URL + '/' + slugs[0] + '.php');
  assert.equal(response.status, 200, 'new tour should render through the shared PHP renderer');
  html = await response.text();
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

  await transitionReview('tours', slugs[1], 'ready_for_review');
  await transitionReview('tours', slugs[1], 'approved_for_release');
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
    h1: 'Content Studio Integration Test Guide', slug: slugs[2], status: 'draft',
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
  assert.equal(result.response.status, 201, 'blog draft should be created: ' + JSON.stringify(result.body));
  assert.ok(fs.existsSync(routeFiles[2]));
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(!sitemap.includes('/' + slugs[2] + '.php</loc>'), 'a blog draft must not appear in sitemap');
  await transitionReview('blogs', slugs[2], 'ready_for_review');
  await transitionReview('blogs', slugs[2], 'approved_for_release');
  result = await requestJson(STUDIO_URL + '/api/content/blogs/' + slugs[2] + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'approved blog can be made Live locally');
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(sitemap.includes('/' + slugs[2] + '.php</loc>'), 'approved blog should appear in sitemap');
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
  assert.equal(updatedBlogRecord.status, 'draft', 'editing a previously live blog must save it back to Draft');
  assert.equal(updatedBlogRecord.review_status, 'draft', 'editing content must reset its review approval');
  response = await fetch(PHP_URL + '/blog.php');
  html = await response.text();
  assert.ok(!html.includes('Content Studio Revised Archive Card'), 'edited draft must not appear in the Blog archive before re-approval');
  response = await fetch(PHP_URL + '/' + slugs[2] + '.php');
  html = await response.text();
  assert.ok(html.includes('Content Studio Integration Test Guide Revised'), 'edited article should render through the PHP renderer');
  assert.match(html, /noindex,follow/, 'edited draft must be noindex until released');
  await transitionReview('blogs', slugs[2], 'ready_for_review');
  await transitionReview('blogs', slugs[2], 'approved_for_release');
  result = await requestJson(STUDIO_URL + '/api/content/blogs/' + slugs[2] + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'edited blog can be republished only after re-approval');
  response = await fetch(PHP_URL + '/blog.php');
  html = await response.text();
  assert.ok(html.includes('Content Studio Revised Archive Card'), 'approved edited title should update the Blog archive card');
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

  // Landing page: validate safe content blocks, then exercise the full draft/review/release lifecycle.
  const directLivePage = await requestJson(STUDIO_URL + '/api/pages', 'POST', { ...landingPagePayload(pageSlug), status: 'live' });
  assert.equal(directLivePage.response.status, 409, 'a new landing page must not skip review approval');
  assert.ok(!fs.existsSync(pageRouteFile) && !fs.existsSync(pageRecordFile), 'rejected direct-Live landing page must not write artifacts');
  const unsafeSectionPage = landingPagePayload(pageSlug);
  unsafeSectionPage.sections = JSON.stringify([{ type: '<script>', heading: 'Unsafe', body: 'An unsupported type must not be rendered.' }]);
  result = await requestJson(STUDIO_URL + '/api/pages', 'POST', unsafeSectionPage);
  assert.equal(result.response.status, 400, 'arbitrary section types/HTML must be rejected');
  assert.ok(!fs.existsSync(pageRouteFile) && !fs.existsSync(pageRecordFile), 'invalid section payload must not create page artifacts');

  result = await requestJson(STUDIO_URL + '/api/pages', 'POST', landingPagePayload(pageSlug));
  assert.equal(result.response.status, 201, 'landing-page Draft should be created: ' + JSON.stringify(result.body));
  assert.equal(result.body.status, 'draft');
  assert.ok(fs.existsSync(pageRouteFile), 'landing page wrapper should be generated from the trusted renderer');
  assert.ok(fs.existsSync(pageRecordFile), 'landing page JSON should be persisted');
  assert.ok(fs.existsSync(pageCssFile), 'landing page token CSS should be generated');
  let pageRecord = JSON.parse(fs.readFileSync(pageRecordFile, 'utf8'));
  assert.equal(pageRecord.status, 'draft');
  assert.equal(pageRecord.review_status, 'draft');
  assert.equal(pageRecord.sections.length, 5, 'the builder should preserve structured reusable sections');
  assert.doesNotMatch(fs.readFileSync(pageCssFile, 'utf8'), /<script|expression\s*\(|url\s*\(\s*https?:/i, 'generated CSS must contain only approved tokens, not executable author content');
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(!sitemap.includes('/' + pageSlug + '.php</loc>'), 'landing-page Draft must be absent from sitemap');
  result = await requestJson(STUDIO_URL + '/api/content', 'GET');
  assert.ok(result.body.items.some(item => item.type === 'pages' && item.slug === pageSlug && item.status === 'draft'), 'landing page should appear in the unified manager');
  result = await requestJson(STUDIO_URL + '/api/content/pages/' + pageSlug + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 409, 'landing-page Draft must not be published before review');

  await transitionReview('pages', pageSlug, 'ready_for_review');
  await transitionReview('pages', pageSlug, 'approved_for_release');
  assert.ok(!fs.readFileSync(sitemapPath, 'utf8').includes('/' + pageSlug + '.php</loc>'), 'approval is not publication');
  result = await requestJson(STUDIO_URL + '/api/content/pages/' + pageSlug + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'approved landing page should publish locally');
  sitemap = fs.readFileSync(sitemapPath, 'utf8');
  assert.ok(sitemap.includes('/' + pageSlug + '.php</loc>'), 'live landing page should be added to sitemap');
  response = await fetch(PHP_URL + '/' + pageSlug + '.php');
  assert.equal(response.status, 200, 'published landing page should render using the shared PHP shell');
  html = await response.text();
  assert.ok(html.includes('Content Studio Landing Page Integration Test'));
  assert.ok(html.includes('A reusable image-and-text block'));
  const renderedSectionHeadings = Array.from(html.matchAll(/<h2\b[^>]*>([\s\S]*?)<\/h2>/gi), match => match[1].replace(/<[^>]*>/g, '').replace(/&amp;/g, '&').trim());
  assert.ok(
    ['Plan your visit', 'A closer look', 'What to know'].every(heading => renderedSectionHeadings.includes(heading)),
    'published page must render each authored block heading; observed: ' + JSON.stringify(renderedSectionHeadings),
  );
  assert.doesNotMatch(phpErrors, /Unknown modifier|preg_match\(\):/, 'landing renderer must not emit PHP regex warnings');
  assert.ok(html.includes('Confirm current arrangements'));
  assert.ok(html.includes('css/page-builder.css') && html.includes('css/pages/' + pageSlug + '.css'));
  assert.doesNotMatch(html, /noindex,follow/, 'released landing page should be indexable');
  assert.ok(html.includes('og:title') && html.includes('application/ld+json'), 'landing page must retain site SEO metadata and structured schema');

  const editedLandingPage = landingPagePayload(pageSlug, 'sandstone');
  editedLandingPage.h1 = 'Content Studio Landing Page Revised';
  editedLandingPage.seoTitle = 'Content Studio Landing Page Revised';
  editedLandingPage.heroIntro = 'This updated introduction validates that editing resets the release approval and regenerates the safe theme stylesheet.';
  result = await requestJson(STUDIO_URL + '/api/content/pages/' + pageSlug, 'PUT', editedLandingPage);
  assert.equal(result.response.status, 200, 'landing page should be editable through the unified manager');
  pageRecord = JSON.parse(fs.readFileSync(pageRecordFile, 'utf8'));
  assert.equal(pageRecord.status, 'draft', 'editing a live landing page must return it to Draft');
  assert.equal(pageRecord.review_status, 'draft', 'editing must reset approval');
  const regeneratedPageCss = fs.readFileSync(pageCssFile, 'utf8');
  assert.ok(regeneratedPageCss.includes('--studio-page-hero-bg: var(--brown)'), 'editing the theme should regenerate CSS from the approved design token preset');
  assert.ok(!fs.readFileSync(sitemapPath, 'utf8').includes('/' + pageSlug + '.php</loc>'), 'edited landing Draft must be removed from sitemap');
  response = await fetch(PHP_URL + '/' + pageSlug + '.php');
  html = await response.text();
  assert.ok(html.includes('Content Studio Landing Page Revised'));
  assert.match(html, /noindex,follow/, 'edited landing page remains non-indexable until re-approved');

  await transitionReview('pages', pageSlug, 'ready_for_review');
  await transitionReview('pages', pageSlug, 'approved_for_release');
  result = await requestJson(STUDIO_URL + '/api/content/pages/' + pageSlug + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'edited landing page may be republished only after fresh approval');
  result = await requestJson(STUDIO_URL + '/api/content/pages/' + pageSlug + '/status', 'PATCH', { status: 'draft' });
  assert.equal(result.response.status, 200, 'landing page may be unpublished safely');
  assert.ok(!fs.readFileSync(sitemapPath, 'utf8').includes('/' + pageSlug + '.php</loc>'));
  result = await requestJson(STUDIO_URL + '/api/content/pages/' + pageSlug, 'DELETE');
  assert.equal(result.response.status, 200, 'unpublished landing-page Draft should be removable');
  assert.ok(!fs.existsSync(pageRouteFile));
  assert.ok(!fs.existsSync(pageRecordFile));
  assert.ok(!fs.existsSync(pageCssFile), 'deleting the page should remove generated CSS but not user-uploaded source images');
  assert.ok(fs.existsSync(path.join(ROOT, 'img/desert3.jpg')), 'page deletion must retain the source image');

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
    category: 'hotel', status: 'draft', src: gallerySrc,
    alt: 'A temporary additional image for Content Studio integration testing',
    caption: 'Temporary CI verification photo', crop: 'landscape',
  });
  assert.equal(result.response.status, 201, 'Gallery draft should be created: ' + JSON.stringify(result.body));
  galleryRecordPath = path.join(galleryDir, result.body.id + '.json');
  ownedPaths.add(galleryRecordPath);
  let galleryDraftRecord = JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8'));
  assert.equal(galleryDraftRecord.status, 'draft');
  const galleryId = path.basename(galleryRecordPath, '.json');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 409, 'Gallery draft must not bypass review approval');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId, 'PUT', {
    category: 'hotel', status: 'live', src: gallerySrc,
    alt: 'Attempt to bypass Gallery review using the edit endpoint',
    caption: 'Must remain a Draft', crop: 'landscape',
  });
  assert.equal(result.response.status, 409, 'editing a Gallery Draft must not bypass release approval');
  galleryDraftRecord = JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8'));
  assert.equal(galleryDraftRecord.status, 'draft', 'rejected Gallery edit must leave the stored publication state unchanged');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 409, 'rejected Gallery edit must not mark content Live');

  await transitionReview('gallery', galleryId, 'ready_for_review');
  await transitionReview('gallery', galleryId, 'approved_for_release');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'approved Gallery entry can be made Live locally');
  response = await fetch(PHP_URL + '/gallery.php');
  assert.equal(response.status, 200);
  html = await response.text();
  assert.ok(html.includes('src="' + gallerySrc + '"'), 'new Gallery image should render on the existing Gallery route');
  result = await requestJson(STUDIO_URL + '/api/content', 'GET');
  assert.ok(result.body.items.some(item => item.type === 'gallery' && item.slug === galleryId && item.status === 'live'));
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId, 'PUT', {
    category: 'hotel', status: 'draft', src: gallerySrc,
    alt: 'An updated temporary additional image for Content Studio integration testing',
    caption: 'Updated temporary CI verification photo', crop: 'portrait',
  });
  assert.equal(result.response.status, 200, 'editing Live Gallery content must move it back to Draft');
  let galleryRecord = JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8'));
  assert.equal(galleryRecord.image.alt, 'An updated temporary additional image for Content Studio integration testing');
  assert.equal(galleryRecord.image.crop, 'portrait');
  assert.equal(galleryRecord.status, 'draft');
  assert.equal(galleryRecord.review_status, 'draft');
  response = await fetch(PHP_URL + '/gallery.php');
  html = await response.text();
  assert.ok(!html.includes('src="' + gallerySrc + '"'), 'edited Gallery draft must not appear before review');
  await transitionReview('gallery', galleryId, 'ready_for_review');
  await transitionReview('gallery', galleryId, 'approved_for_release');
  result = await requestJson(STUDIO_URL + '/api/content/gallery/' + galleryId + '/status', 'PATCH', { status: 'live' });
  assert.equal(result.response.status, 200, 'edited Gallery metadata can be published after re-approval');
  assert.equal(JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8')).status, 'live');
  assert.equal(JSON.parse(fs.readFileSync(galleryRecordPath, 'utf8')).image.crop, 'portrait');
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
  .then(() => process.stdout.write('PASS Content Studio validates and publishes tour/blog/landing-page/Gallery records with preview, review and overwrite boundaries.\n'))
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
    if (!existedPageCssDirectory) {
      try {
        if (fs.existsSync(pageCssDirectory) && fs.readdirSync(pageCssDirectory).length === 0) fs.rmdirSync(pageCssDirectory);
      } catch (_) {}
    }
  });
