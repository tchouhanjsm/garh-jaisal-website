#!/usr/bin/env node
'use strict';

// Local-only authoring utility. Intentionally uses Node built-ins only.
// Run from the repository root with: node scripts/content-studio.js
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const crypto = require('node:crypto');
const { spawnSync } = require('node:child_process');

const ROOT = path.resolve(__dirname, '..');
const PORT = Number(process.env.CONTENT_STUDIO_PORT || 4173);
const UI_PATH = path.join(ROOT, 'tools', 'content-studio', 'index.html');
const CONTENT_ROOT = path.join(ROOT, 'content');
const IMAGE_ROOT = path.join(ROOT, 'img');
const MAX_JSON_BYTES = 1024 * 1024;
const MAX_IMAGE_BYTES = 15 * 1024 * 1024;
const PREVIEW_TTL_MS = 15 * 60 * 1000;
const PREVIEW_SESSIONS = new Map();
const REVIEW_STATUSES = Object.freeze(['draft', 'ready_for_review', 'approved_for_release']);
const IMAGE_TYPES = {
  '.jpg': { mime: 'image/jpeg', signature: b => b.length > 3 && b[0] === 0xff && b[1] === 0xd8 && b[2] === 0xff },
  '.jpeg': { mime: 'image/jpeg', signature: b => b.length > 3 && b[0] === 0xff && b[1] === 0xd8 && b[2] === 0xff },
  '.png': { mime: 'image/png', signature: b => b.length > 8 && b.subarray(0, 8).equals(Buffer.from([137,80,78,71,13,10,26,10])) },
  '.webp': { mime: 'image/webp', signature: b => b.length > 12 && b.toString('ascii', 0, 4) === 'RIFF' && b.toString('ascii', 8, 12) === 'WEBP' },
  '.avif': { mime: 'image/avif', signature: b => b.length > 12 && b.toString('ascii', 4, 8) === 'ftyp' && /(?:avif|avis|mif1)/.test(b.toString('ascii', 8, Math.min(32, b.length))) },
};
const CATEGORIES = ['hotel', 'rooms', 'desert', 'restaurant'];
const CROPS = ['arch', 'landscape', 'portrait', 'standard'];
const PAGE_THEMES = Object.freeze({
  heritage: Object.freeze({
    surface: 'var(--cream)', surfaceAlt: 'var(--sand-light)', text: 'var(--ink)',
    muted: 'var(--muted)', accent: 'var(--brown)', heroBg: 'var(--dark)',
    heroText: '#fff', heroMuted: '#e5ddd4', cardBg: '#fff', buttonText: 'var(--dark)',
  }),
  sandstone: Object.freeze({
    surface: 'var(--sand-light)', surfaceAlt: '#fff', text: 'var(--ink)',
    muted: 'var(--muted)', accent: 'var(--brown)', heroBg: 'var(--brown)',
    heroText: '#fff', heroMuted: '#efe5da', cardBg: '#fff', buttonText: 'var(--dark)',
  }),
  'desert-night': Object.freeze({
    surface: 'var(--dark)', surfaceAlt: '#24221d', text: '#fff',
    muted: '#ded6ce', accent: '#f3d8b7', heroBg: '#171513',
    heroText: '#fff', heroMuted: '#e5ddd4', cardBg: '#24221d', buttonText: 'var(--dark)',
  }),
});
const PAGE_LAYOUTS = Object.freeze(['split', 'centered']);
const PAGE_SECTION_TYPES = Object.freeze(['intro', 'image_text', 'feature_list', 'faq', 'cta']);

// Approved render contracts for Content Studio V2.
// This registry is deliberately data-only: staff select a template, never a PHP or CSS path.
const PAGE_TEMPLATE_REGISTRY = Object.freeze({
  'experience-detail': Object.freeze({
    id: 'experience-detail', label: 'Tour or experience', renderer: 'include/experience-page.php',
    routePattern: '/{slug}.php', recordDirectory: 'content/experiences',
    stylesheetPaths: Object.freeze(['css/garh-v2.css', 'css/experiences.css', 'css/thar-sunset-story.css']),
    sharedIncludes: Object.freeze(['include/page-head.php', 'include/header-v2.php', 'include/footer-v2.php']),
    requiredFields: Object.freeze(['title', 'description', 'h1', 'hero_image', 'hero_alt', 'summary']),
    recordType: 'tour'
  }),
  'editorial-guide': Object.freeze({
    id: 'editorial-guide', label: 'Blog or travel guide', renderer: 'include/editorial-guide-page.php',
    routePattern: '/{slug}.php', recordDirectory: 'content/editorial',
    stylesheetPaths: Object.freeze(['css/garh-v2.css', 'css/article.css', 'css/thar-sunset-story.css']),
    sharedIncludes: Object.freeze(['include/page-head.php', 'include/header-v2.php', 'include/footer-v2.php']),
    requiredFields: Object.freeze(['title', 'description', 'h1', 'hero_image', 'hero_alt', 'lede', 'sections', 'sources']),
    recordType: 'blog'
  }),
  'landing-page': Object.freeze({
    id: 'landing-page', label: 'Landing or campaign page', renderer: 'include/studio-page.php',
    routePattern: '/{slug}.php', recordDirectory: 'content/pages',
    stylesheetPaths: Object.freeze(['css/garh-v2.css', 'css/page-builder.css']),
    sharedIncludes: Object.freeze(['include/page-head.php', 'include/header-v2.php', 'include/footer-v2.php']),
    requiredFields: Object.freeze(['title', 'description', 'h1', 'hero_image', 'hero_alt', 'sections']),
    recordType: 'page'
  })
});
const STUDIO_MANAGED_PAGE_REGISTRY = Object.freeze({
  '/pack.php': Object.freeze({ label: 'Tours and experiences', templateId: 'marketing-hub', editableFields: Object.freeze(['title', 'description']) }),
  '/blog.php': Object.freeze({ label: 'Journal index', templateId: 'editorial-index', editableFields: Object.freeze(['title', 'description']) }),
  '/gallery.php': Object.freeze({ label: 'Photo gallery', templateId: 'gallery', editableFields: Object.freeze(['title', 'description']) }),
  '/tariff.php': Object.freeze({ label: 'Rates and tariffs', templateId: 'rates', editableFields: Object.freeze([]) })
});
const APPROVED_STYLESHEET_PATHS = new Set([
  'css/article.css',
  'css/booking.css',
  'css/contact.css',
  'css/directions.css',
  'css/editorial-index.css',
  'css/experiences.css',
  'css/gallery.css',
  'css/garh-v2.css',
  'css/packages.css',
  'css/page-builder.css',
  'css/restaurant.css',
  'css/rooms.css',
  'css/tariff.css',
  'css/thar-sunset-story.css'
]);

function registryError(message) {
  const error = new Error(message);
  error.code = 'CONTENT_STUDIO_REGISTRY_INVALID';
  return error;
}

function isInsideRoot(candidate) {
  const relative = path.relative(ROOT, candidate);
  return relative !== '..' && !relative.startsWith('..' + path.sep) && !path.isAbsolute(relative);
}

function assertRegularRepoFile(relativePath, label) {
  if (typeof relativePath !== 'string' || !relativePath || path.isAbsolute(relativePath) || relativePath.includes('\\') || relativePath.split(/[\\/]+/).some(part => part === '..' || part === '.')) {
    throw registryError((label || 'Path') + ' must be a normalized repository-relative path.');
  }
  const absolute = path.resolve(ROOT, relativePath);
  if (!isInsideRoot(absolute)) throw registryError((label || 'Path') + ' is outside the repository.');
  let cursor = ROOT;
  for (const part of path.relative(ROOT, absolute).split(path.sep)) {
    cursor = path.join(cursor, part);
    let stat;
    try { stat = fs.lstatSync(cursor); }
    catch (_) { throw registryError((label || 'Path') + ' does not exist: ' + relativePath); }
    if (stat.isSymbolicLink()) throw registryError((label || 'Path') + ' cannot traverse a symbolic link: ' + relativePath);
    if (cursor !== absolute && !stat.isDirectory()) throw registryError((label || 'Path') + ' traverses a non-directory: ' + relativePath);
  }
  let finalStat;
  try { finalStat = fs.lstatSync(absolute); } catch (_) { throw registryError((label || 'Path') + ' does not exist: ' + relativePath); }
  if (!finalStat.isFile() || finalStat.isSymbolicLink()) throw registryError((label || 'Path') + ' must be a regular file: ' + relativePath);
  return absolute;
}

function assertApprovedStylesheet(relativePath) {
  if (!APPROVED_STYLESHEET_PATHS.has(relativePath)) {
    throw registryError('Stylesheet is not approved for Content Studio templates: ' + String(relativePath));
  }
  return assertRegularRepoFile(relativePath, 'Stylesheet');
}

function getTemplateContract(templateId) {
  const template = PAGE_TEMPLATE_REGISTRY[templateId];
  if (!template) throw registryError('Choose a supported page template.');
  for (const includePath of template.sharedIncludes) assertRegularRepoFile(includePath, 'Shared include');
  for (const stylesheet of template.stylesheetPaths) assertApprovedStylesheet(stylesheet);
  if (template.recordType === 'blog') {
    const guideRegistryPath = assertRegularRepoFile('include/editorial-guides-data.php', 'Editorial content registry');
    const guideRegistrySource = fs.readFileSync(guideRegistryPath, 'utf8');
    const declaredStylesheets = [...guideRegistrySource.matchAll(/'css'\s*=>\s*\[([\s\S]*?)\]/g)]
      .flatMap(entry => [...entry[1].matchAll(/'([^']+\\.css)'/g)].map(match => match[1]));
    declaredStylesheets.push('css/article.css', 'css/thar-sunset-story.css');
    for (const stylesheet of [...new Set(declaredStylesheets)]) {
      if (typeof stylesheet !== 'string' || !stylesheet.startsWith('css/')) {
        throw registryError('Editorial stylesheet dependencies must be approved repository-relative CSS paths.');
      }
      assertApprovedStylesheet(stylesheet);
    }
  }
  assertRegularRepoFile(template.renderer, 'Template renderer');
  return template;
}

function validateRouteForTemplate(slug, templateId) {
  const template = getTemplateContract(templateId);
  if (typeof slug !== 'string' || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
    throw registryError('Use a route slug made from lowercase letters, digits and hyphens.');
  }
  const routeRelative = slug + '.php';
  const routeAbsolute = path.resolve(ROOT, routeRelative);
  if (!isInsideRoot(routeAbsolute)) throw registryError('Route is outside the repository.');
  if (fs.existsSync(routeAbsolute) || fs.existsSync(path.join(ROOT, template.recordDirectory, slug + '.json')) || Object.prototype.hasOwnProperty.call(PAGE_TEMPLATE_REGISTRY, slug)) {
    throw registryError('That route or content record already exists. Existing routes are never overwritten.');
  }
  return { route: routeRelative, renderer: template.renderer, stylesheets: [...template.stylesheetPaths] };
}

function validateTemplateRegistry() {
  const seen = new Set();
  for (const [id, template] of Object.entries(PAGE_TEMPLATE_REGISTRY)) {
    if (id !== template.id) throw registryError('Template registry key does not match its ID: ' + id);
    if (seen.has(template.routePattern + ':' + template.recordDirectory)) throw registryError('Duplicate template record mapping: ' + id);
    seen.add(template.routePattern + ':' + template.recordDirectory);
    getTemplateContract(id);
  }
  for (const [route, page] of Object.entries(STUDIO_MANAGED_PAGE_REGISTRY)) {
    if (!/^\/[a-z0-9-]+\.php$/.test(route)) throw registryError('Managed page routes must be fixed root PHP routes.');
    if (!/^[a-z][a-z0-9-]*$/.test(page.templateId)) throw registryError('Managed page template identifier is invalid for ' + route);
    for (const field of page.editableFields) {
      if (!/^[a-z][a-z0-9_]*$/.test(field)) throw registryError('Managed page editable field is invalid for ' + route);
    }
  }
  return { templateCount: Object.keys(PAGE_TEMPLATE_REGISTRY).length, managedPageCount: Object.keys(STUDIO_MANAGED_PAGE_REGISTRY).length };
}

function fail(statusCode, message) {
  const error = new Error(message);
  error.statusCode = statusCode;
  return error;
}

function respond(res, status, type, body) {
  const data = Buffer.isBuffer(body) ? body : Buffer.from(String(body));
  res.writeHead(status, {
    'Content-Type': type,
    'Content-Length': data.length,
    'Cache-Control': 'no-store',
    'X-Content-Type-Options': 'nosniff',
    'Referrer-Policy': 'no-referrer',
    'Content-Security-Policy': "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; frame-src 'self' http://127.0.0.1:* http://localhost:*; base-uri 'none'; frame-ancestors 'none'",
  });
  res.end(data);
}

function respondJson(res, status, data) {
  respond(res, status, 'application/json; charset=utf-8', JSON.stringify(data));
}

function assertLocalRequest(req) {
  const host = String(req.headers.host || '').toLowerCase();
  if (!/^(?:127\.0\.0\.1|localhost)(?::\d{1,5})?$/.test(host)) {
    throw fail(403, 'Content Studio accepts local connections only.');
  }
  if (req.headers.origin) {
    let origin;
    try { origin = new URL(req.headers.origin); } catch (_) { throw fail(403, 'Invalid request origin.'); }
    if (origin.protocol !== 'http:' || origin.host.toLowerCase() !== host) {
      throw fail(403, 'Cross-origin requests are not allowed.');
    }
  }
}

function readBody(req, maximum) {
  return new Promise((resolve, reject) => {
    let size = 0;
    let tooLarge = false;
    const chunks = [];
    req.on('data', chunk => {
      size += chunk.length;
      if (size > maximum) {
        tooLarge = true;
        chunks.length = 0;
      } else if (!tooLarge) chunks.push(chunk);
    });
    req.on('end', () => {
      if (tooLarge) return reject(fail(413, 'The request exceeds the permitted size.'));
      resolve(Buffer.concat(chunks));
    });
    req.on('error', reject);
  });
}

async function readJson(req) {
  const body = await readBody(req, MAX_JSON_BYTES);
  try { return JSON.parse(body.toString('utf8')); }
  catch (_) { throw fail(400, 'Request body must be valid JSON.'); }
}

function atomicWrite(file, contents) {
  fs.mkdirSync(path.dirname(file), { recursive: true });
  const temp = file + '.tmp-' + process.pid + '-' + crypto.randomBytes(5).toString('hex');
  try {
    fs.writeFileSync(temp, contents, { flag: 'wx', mode: 0o644 });
    fs.renameSync(temp, file);
  } catch (error) {
    try { if (fs.existsSync(temp)) fs.unlinkSync(temp); } catch (_) {}
    throw error;
  }
}

function slugify(value) {
  return String(value || '').normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
    .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
    .slice(0, 80).replace(/-+$/g, '');
}

function textField(value, label, min, max) {
  const text = typeof value === 'string' ? value.trim() : '';
  if (text.length < min || text.length > max) {
    throw fail(400, label + ' must contain between ' + min + ' and ' + max + ' characters.');
  }
  return text;
}

function lines(value, label, required, maxCount = 40, maxLength = 500) {
  const items = String(value || '').split(/\r?\n/).map(item => item.trim()).filter(Boolean);
  if (required && !items.length) throw fail(400, label + ' needs at least one item.');
  if (items.length > maxCount || items.some(item => item.length > maxLength)) {
    throw fail(400, label + ' is too long; use at most ' + maxCount + ' lines of ' + maxLength + ' characters.');
  }
  return items;
}

function pipeRows(value, label, required, maxCount = 30) {
  const rows = [];
  for (const line of String(value || '').split(/\r?\n/).map(item => item.trim()).filter(Boolean)) {
    const parts = line.split('|').map(item => item.trim());
    if (parts.length < 2 || !parts[0] || !parts.slice(1).join('|').trim()) {
      throw fail(400, label + ' lines must use: first field | second field.');
    }
    rows.push(parts);
  }
  if (required && !rows.length) throw fail(400, label + ' needs at least one item.');
  if (rows.length > maxCount) throw fail(400, label + ' allows at most ' + maxCount + ' items.');
  return rows;
}

function statusValue(value) {
  const status = String(value || 'draft');
  if (!['draft', 'live'].includes(status)) throw fail(400, 'Publication state must be Draft or Live.');
  return status;
}

function reviewStatusForRecord(record) {
  if (REVIEW_STATUSES.includes(record.review_status)) return record.review_status;
  // Legacy records already marked Live pre-date explicit review metadata; do not silently unpublish them.
  return record.status === 'live' ? 'approved_for_release' : 'draft';
}

function reviewStatusValue(value) {
  const status = String(value || '');
  if (!REVIEW_STATUSES.includes(status)) {
    throw fail(400, 'Choose Draft, Ready for review, or Approved for release.');
  }
  return status;
}

function today() {
  return new Date().toISOString().slice(0, 10);
}

function displayDate(iso) {
  return new Intl.DateTimeFormat('en-GB', {
    day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC',
  }).format(new Date(iso + 'T00:00:00Z'));
}

function assertImageDirectoryChain(targetDirectory, createMissing = false) {
  const root = path.resolve(IMAGE_ROOT);
  let rootStat;
  try { rootStat = fs.lstatSync(root); }
  catch (error) { throw fail(400, 'The img/ directory is missing or unavailable.'); }
  if (!rootStat.isDirectory() || rootStat.isSymbolicLink()) throw fail(400, 'The img/ root must be a real local directory.');

  const relative = path.relative(root, path.resolve(targetDirectory));
  if (relative === '..' || relative.startsWith('..' + path.sep) || path.isAbsolute(relative)) {
    throw fail(400, 'Image directory is outside img/.');
  }
  let current = root;
  for (const part of relative.split(path.sep).filter(Boolean)) {
    current = path.join(current, part);
    let stat;
    try { stat = fs.lstatSync(current); }
    catch (error) {
      if (error.code !== 'ENOENT' || !createMissing) throw fail(400, 'Image directory is missing or unavailable: ' + path.relative(ROOT, current));
      try { fs.mkdirSync(current); }
      catch (mkdirError) {
        if (mkdirError.code !== 'EEXIST') throw fail(400, 'Could not create the local image directory: ' + path.relative(ROOT, current));
      }
      try { stat = fs.lstatSync(current); }
      catch (_) { throw fail(400, 'Could not verify the local image directory: ' + path.relative(ROOT, current)); }
    }
    if (!stat.isDirectory() || stat.isSymbolicLink()) throw fail(400, 'Image paths cannot traverse a symbolic link: ' + path.relative(ROOT, current));
  }
}

function imagePath(value) {
  const src = typeof value === 'string' ? value.trim() : '';
  if (!/^img\/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$/i.test(src) || src.includes('..') || src.includes('\\')) {
    throw fail(400, 'Choose an image under img/ or upload it using Content Studio.');
  }
  const full = path.resolve(ROOT, src);
  const prefix = path.resolve(IMAGE_ROOT) + path.sep;
  if (!full.startsWith(prefix)) throw fail(400, 'Image path is outside img/.');
  assertImageDirectoryChain(path.dirname(full));
  try {
    const st = fs.lstatSync(full);
    if (!st.isFile() || st.isSymbolicLink()) throw fail(400, 'Image must be a regular local file.');
  } catch (error) {
    if (error.statusCode) throw error;
    throw fail(400, 'Image file does not exist: ' + src);
  }
  return src;
}

function assertStudioRouteAvailable(slug, type) {
  const templateId = type === 'experiences' ? 'experience-detail' : type === 'editorial' ? 'editorial-guide' : type === 'pages' ? 'landing-page' : null;
  if (!templateId) throw fail(400, 'Choose a supported Content Studio page template.');
  try {
    return validateRouteForTemplate(slug, templateId);
  } catch (error) {
    const message = String(error.message || 'Page template validation failed.');
    throw fail(/already exists/i.test(message) ? 409 : 400, message);
  }
}

function ensureNewSlug(slug, type) {
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw fail(400, 'Use a URL slug made from lowercase letters, digits and hyphens.');
  assertStudioRouteAvailable(slug, type);
  const directories = { experiences: 'experiences', editorial: 'editorial', pages: 'pages' };
  if (!Object.prototype.hasOwnProperty.call(directories, type)) throw fail(400, 'Choose a supported Content Studio content directory.');
  return {
    route: path.join(ROOT, slug + '.php'),
    record: path.join(CONTENT_ROOT, directories[type], slug + '.json'),
  };
}

function generatedPageCssPath(slug, createDirectory = false) {
  if (typeof slug !== 'string' || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
    throw fail(400, 'Invalid generated page stylesheet identifier.');
  }
  const cssRoot = path.join(ROOT, 'css');
  const cssRootStat = fs.lstatSync(cssRoot);
  if (!cssRootStat.isDirectory() || cssRootStat.isSymbolicLink()) throw fail(400, 'The CSS directory must be a real local directory.');
  const directory = path.join(cssRoot, 'pages');
  let stat;
  try { stat = fs.lstatSync(directory); }
  catch (error) {
    if (error.code !== 'ENOENT' || !createDirectory) throw fail(400, 'Generated page CSS directory is missing or unavailable.');
    fs.mkdirSync(directory, { mode: 0o755 });
    stat = fs.lstatSync(directory);
  }
  if (!stat.isDirectory() || stat.isSymbolicLink()) throw fail(400, 'Generated page CSS directory must be a real local directory.');
  return path.join(directory, slug + '.css');
}

function buildLandingPageCss(slug, record) {
  const theme = PAGE_THEMES[record.theme];
  if (!theme || !PAGE_LAYOUTS.includes(record.hero_layout)) throw fail(400, 'Choose an approved landing page theme and hero layout.');
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw fail(400, 'Invalid landing page stylesheet identifier.');
  return [
    '/* Generated by Content Studio from approved design tokens. Do not hand-edit. */',
    '.studio-page--' + slug + ' {',
    '  --studio-page-surface: ' + theme.surface + ';',
    '  --studio-page-surface-alt: ' + theme.surfaceAlt + ';',
    '  --studio-page-text: ' + theme.text + ';',
    '  --studio-page-muted: ' + theme.muted + ';',
    '  --studio-page-accent: ' + theme.accent + ';',
    '  --studio-page-hero-bg: ' + theme.heroBg + ';',
    '  --studio-page-hero-text: ' + theme.heroText + ';',
    '  --studio-page-hero-muted: ' + theme.heroMuted + ';',
    '  --studio-page-card-bg: ' + theme.cardBg + ';',
    '  --studio-page-button-text: ' + theme.buttonText + ';',
    '}',
    '',
  ].join('\n');
}

function wrapper(variable, slug) {
  const renderers = {
    experienceSlug: 'experience-page.php',
    editorialGuideSlug: 'editorial-guide-page.php',
    studioPageSlug: 'studio-page.php',
  };
  const renderer = renderers[variable];
  if (!renderer || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw fail(400, 'Unsupported local page wrapper.');
  return "<?php\n$" + variable + " = '" + slug + "';\nrequire __DIR__ . '/include/" + renderer + "';\n";
}

function runSitemap() {
  const result = require('node:child_process').spawnSync(process.env.PHP_BIN || 'php', ['scripts/generate-sitemap.php'], {
    cwd: ROOT, encoding: 'utf8', timeout: 45000, maxBuffer: 2 * 1024 * 1024,
  });
  if (result.error || result.status !== 0) {
    const detail = String(result.stderr || result.stdout || (result.error && result.error.message) || 'unknown error').trim();
    throw fail(500, 'Could not refresh sitemap.xml. Check PHP CLI and the working-tree Git history. ' + detail);
  }
  return String(result.stdout || 'Sitemap refreshed.').trim();
}

function createPage(type, slug, record, variable) {
  const files = ensureNewSlug(slug, type);
  const sitemapPath = path.join(ROOT, 'sitemap.xml');
  const oldSitemap = fs.readFileSync(sitemapPath);
  assertContentDirectoryChain(path.dirname(files.record), true);
  const cssFile = type === 'pages' ? generatedPageCssPath(slug, true) : null;
  let recordCreated = false;
  let routeCreated = false;
  let cssCreated = false;
  try {
    atomicWrite(files.record, JSON.stringify(record, null, 2) + '\n');
    recordCreated = true;
    atomicWrite(files.route, wrapper(variable, slug));
    routeCreated = true;
    if (cssFile) {
      atomicWrite(cssFile, buildLandingPageCss(slug, record), { flag: 'wx', mode: 0o644 });
      cssCreated = true;
    }
    let sitemapMessage = 'Draft saved. It is excluded from the public archive and sitemap.';
    if (record.status === 'live') sitemapMessage = runSitemap();
    return { slug, status: record.status, pagePath: '/' + slug + '.php', sitemapMessage };
  } catch (error) {
    if (cssCreated) { try { fs.unlinkSync(cssFile); } catch (_) {} }
    if (routeCreated) { try { fs.unlinkSync(files.route); } catch (_) {} }
    if (recordCreated) { try { fs.unlinkSync(files.record); } catch (_) {} }
    try { atomicWrite(sitemapPath, oldSitemap); } catch (_) {}
    throw error;
  }
}

function assertContentDirectoryChain(targetDirectory, createMissing = false) {
  const root = path.resolve(CONTENT_ROOT);
  let rootStat;
  try { rootStat = fs.lstatSync(root); }
  catch (error) {
    if (error.code !== 'ENOENT' || !createMissing) throw fail(400, 'The content/ directory is missing or unavailable.');
    try { fs.mkdirSync(root); } catch (mkdirError) {
      if (mkdirError.code !== 'EEXIST') throw fail(400, 'Could not create the local content/ directory.');
    }
    try { rootStat = fs.lstatSync(root); } catch (_) { throw fail(400, 'Could not verify the local content/ directory.'); }
  }
  if (!rootStat.isDirectory() || rootStat.isSymbolicLink()) throw fail(400, 'The content/ root must be a real local directory.');

  const relative = path.relative(root, path.resolve(targetDirectory));
  if (relative === '..' || relative.startsWith('..' + path.sep) || path.isAbsolute(relative)) {
    throw fail(400, 'Content directory is outside content/.');
  }
  let current = root;
  for (const part of relative.split(path.sep).filter(Boolean)) {
    current = path.join(current, part);
    let stat;
    try { stat = fs.lstatSync(current); }
    catch (error) {
      if (error.code !== 'ENOENT' || !createMissing) throw fail(400, 'Content directory is missing or unavailable: ' + path.relative(ROOT, current));
      try { fs.mkdirSync(current); }
      catch (mkdirError) {
        if (mkdirError.code !== 'EEXIST') throw fail(400, 'Could not create the local content directory: ' + path.relative(ROOT, current));
      }
      try { stat = fs.lstatSync(current); }
      catch (_) { throw fail(400, 'Could not verify the local content directory: ' + path.relative(ROOT, current)); }
    }
    if (!stat.isDirectory() || stat.isSymbolicLink()) throw fail(400, 'Content paths cannot traverse a symbolic link: ' + path.relative(ROOT, current));
  }
}

function managedPaths(type, slug) {
  const directories = { tours: 'experiences', blogs: 'editorial', gallery: 'gallery' };
  if (!Object.prototype.hasOwnProperty.call(directories, type)) throw fail(404, 'Unknown Content Studio content type.');
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(String(slug || ''))) throw fail(400, 'Invalid content identifier.');
  const record = path.join(CONTENT_ROOT, directories[type], slug + '.json');
  return {
    record,
    route: type === 'gallery' ? null : path.join(ROOT, slug + '.php'),
    directory: path.join(CONTENT_ROOT, directories[type]),
  };
}

function readManagedRecord(type, slug) {
  const files = managedPaths(type, slug);
  for (const directory of [CONTENT_ROOT, files.directory]) {
    try {
      const directoryStat = fs.lstatSync(directory);
      if (!directoryStat.isDirectory() || directoryStat.isSymbolicLink()) {
        throw fail(400, 'Content Studio data directories must be real local directories.');
      }
    } catch (error) {
      if (error.statusCode) throw error;
      if (error.code !== 'ENOENT') throw error;
    }
  }
  let stat;
  try { stat = fs.lstatSync(files.record); }
  catch (error) {
    if (error.code === 'ENOENT') throw fail(404, 'This Content Studio record does not exist.');
    throw error;
  }
  if (!stat.isFile() || stat.isSymbolicLink()) throw fail(400, 'Content Studio records must be regular local files.');
  let record;
  try { record = JSON.parse(fs.readFileSync(files.record, 'utf8')); }
  catch (_) { throw fail(422, 'This saved record is invalid JSON. Ask the operator to repair it before making changes.'); }
  if (!record || typeof record !== 'object' || Array.isArray(record)) throw fail(422, 'This saved record has an invalid structure.');
  const storedId = type === 'gallery' ? record.id : record.slug;
  if (storedId !== slug) throw fail(422, 'The saved record identifier does not match its filename.');
  if (!['draft', 'live', 'hidden'].includes(record.status)) throw fail(422, 'The saved record has an invalid publication state.');
  if (record.review_status !== undefined && !REVIEW_STATUSES.includes(record.review_status)) {
    throw fail(422, 'The saved record has an invalid review state. Ask the operator to review it before changing publication.');
  }
  if (record.status === 'live' && record.review_status !== undefined && record.review_status !== 'approved_for_release') {
    throw fail(422, 'A Live record must have an Approved for release review state. Ask the operator to reconcile this record.');
  }
  return { files, record };
}

function safeManagedRoute(route, type, slug) {
  let stat;
  try { stat = fs.lstatSync(route); }
  catch (error) {
    if (error.code === 'ENOENT') throw fail(409, 'The page route is missing. Do not rewrite it automatically; ask the operator to repair the route.');
    throw error;
  }
  if (!stat.isFile() || stat.isSymbolicLink()) throw fail(409, 'The page route is not a regular file and cannot be changed safely.');
  const variable = type === 'tours' ? 'experienceSlug' : 'editorialGuideSlug';
  if (fs.readFileSync(route, 'utf8') !== wrapper(variable, slug)) {
    throw fail(409, 'This page route no longer matches the generated Content Studio wrapper. Ask the operator to review it before editing or deleting this record.');
  }
}

function verifyPublishableRecord(type, record) {
  if (type === 'tours') {
    if (!['desert', 'city'].includes(record.category)
      || typeof record.h1 !== 'string' || record.h1.trim().length < 3
      || typeof record.title !== 'string' || record.title.trim().length < 8
      || typeof record.description !== 'string' || record.description.trim().length < 40
      || typeof record.summary !== 'string' || record.summary.trim().length < 20
      || !Array.isArray(record.timeline) || !record.timeline.length
      || record.timeline.some(step => !step || typeof step.title !== 'string' || step.title.trim().length < 2
        || typeof step.text !== 'string' || step.text.trim().length < 4)
      || !record.at_a_glance || typeof record.at_a_glance !== 'object' || Array.isArray(record.at_a_glance)
      || !Array.isArray(record.included) || !Array.isArray(record.not_included)
      || !Array.isArray(record.faq) || record.faq.some(item => !item || typeof item.question !== 'string' || item.question.trim().length < 5
        || typeof item.answer !== 'string' || item.answer.trim().length < 5)) {
      throw fail(422, 'This tour is missing required fields and cannot be made Live.');
    }
    imagePath(record.hero_image);
    textField(record.hero_alt, 'Hero image alternative text', 10, 240);
    return;
  }
  if (type === 'blogs') {
    if (typeof record.h1 !== 'string' || record.h1.trim().length < 8
      || typeof record.title !== 'string' || record.title.trim().length < 8
      || typeof record.description !== 'string' || record.description.trim().length < 40
      || typeof record.lede !== 'string' || record.lede.trim().length < 30
      || !Array.isArray(record.toc) || !record.toc.length
      || !Array.isArray(record.sections) || !record.sections.length
      || record.sections.some(section => !section || typeof section.heading !== 'string' || section.heading.trim().length < 2
        || !Array.isArray(section.paragraphs) || !Array.isArray(section.items)
        || (!section.paragraphs.some(paragraph => typeof paragraph === 'string' && paragraph.trim())
          && !section.items.some(item => typeof item === 'string' && item.trim()))
        || section.paragraphs.some(paragraph => typeof paragraph !== 'string' || !paragraph.trim())
        || section.items.some(item => typeof item !== 'string' || !item.trim()))
      || !Array.isArray(record.sources) || !record.sources.length
      || record.sources.some(source => {
        if (!source || typeof source.label !== 'string' || !source.label.trim() || typeof source.url !== 'string') return true;
        try { return new URL(source.url).protocol !== 'https:'; } catch (_) { return true; }
      })) {
      throw fail(422, 'This blog guide is missing required fields and cannot be made Live.');
    }
    imagePath(record.hero_image);
    textField(record.hero_alt, 'Hero image alternative text', 10, 240);
    return;
  }
  if (type === 'gallery') {
    const photo = record.image;
    if (!['hotel', 'rooms', 'desert', 'restaurant'].includes(record.category)
      || !photo || typeof photo !== 'object'
      || !CROPS.includes(photo.crop)) {
      throw fail(422, 'This Gallery entry is missing required fields and cannot be made Live.');
    }
    imagePath(photo.src);
    textField(photo.alt, 'Alternative text', 10, 240);
  }
}

function listManagedContent() {
  const items = [];
  for (const type of ['tours', 'blogs', 'gallery']) {
    const folder = managedPaths(type, 'content-studio-placeholder').directory;
    if (!fs.existsSync(folder)) continue;
    const names = fs.readdirSync(folder).filter(name => name.endsWith('.json')).sort((a, b) => a.localeCompare(b));
    for (const name of names) {
      const slug = name.slice(0, -5);
      if (/^content-studio-preview-[a-f0-9]{24}$/.test(slug)) continue;
      try {
        const { record } = readManagedRecord(type, slug);
        const photo = type === 'gallery' ? record.image : null;
        items.push({
          type,
          slug,
          status: record.status,
          reviewStatus: reviewStatusForRecord(record),
          title: type === 'gallery'
            ? String(photo.caption || photo.alt || photo.src)
            : String(record.h1 || record.title || slug),
          detail: type === 'gallery'
            ? String(record.category || 'Gallery') + ' · ' + String(photo.src || '')
            : String(record.eyebrow || (type === 'tours' ? record.category : 'Travel guide')),
          modified: String(record.date_modified || record.date_published || ''),
          pagePath: type === 'gallery' ? '/gallery.php#' + encodeURIComponent(record.category) : '/' + slug + '.php',
          invalid: false,
        });
      } catch (error) {
        items.push({
          type, slug, status: 'invalid', title: name,
          detail: String(error.message || 'This record needs operator repair.'),
          modified: '', pagePath: '', invalid: true,
        });
      }
    }
  }
  const rank = status => status === 'draft' ? 0 : status === 'live' ? 1 : 2;
  items.sort((a, b) => rank(a.status) - rank(b.status)
    || a.type.localeCompare(b.type)
    || a.title.localeCompare(b.title));
  return { items };
}

function readManagedContent(type, slug) {
  const { record } = readManagedRecord(type, slug);
  return { type, slug, record };
}

function updateManagedPage(type, slug, record) {
  const { files, record: previous } = readManagedRecord(type, slug);
  if (previous.status === 'hidden') throw fail(409, 'This record uses Hidden status. Ask the operator to review it before editing.');
  if (record.status === 'live') throw fail(409, 'Set the page to Draft before editing it. Edited content must pass Ready for review and Approved for release again before it can be made Live locally.');
  record.review_status = 'draft';
  if (type === 'gallery') throw fail(400, 'Gallery records do not use PHP page routes.');
  safeManagedRoute(files.route, type, slug);
  // Preserve fields the form does not expose, including operator-customized ordering and related links.
  record = { ...previous, ...record };
  if (type === 'tours') {
    for (const key of ['parent', 'order', 'timeline_label', 'related']) {
      if (previous[key] !== undefined) record[key] = previous[key];
    }
  } else if (type === 'blogs') {
    for (const key of ['related', 'cta_eyebrow', 'cta_primary_label', 'cta_primary_href', 'cta_secondary_label', 'cta_secondary_href']) {
      if (previous[key] !== undefined) record[key] = previous[key];
    }
  }
  const oldRecord = fs.readFileSync(files.record);
  const sitemapPath = path.join(ROOT, 'sitemap.xml');
  const oldSitemap = fs.readFileSync(sitemapPath);
  if (type === 'blogs') {
    record.date_published = previous.date_published || record.date_published || today();
    record.date_display = previous.date_display || displayDate(record.date_published);
    record.date_modified = today();
  } else {
    record.date_modified = today();
  }
  try {
    atomicWrite(files.record, JSON.stringify(record, null, 2) + '\n');
    const sitemapMessage = runSitemap();
    return { slug, status: record.status, pagePath: '/' + slug + '.php', sitemapMessage, updated: true };
  } catch (error) {
    try { atomicWrite(files.record, oldRecord); } catch (_) {}
    try { atomicWrite(sitemapPath, oldSitemap); } catch (_) {}
    throw error;
  }
}

function setManagedStatus(type, slug, nextStatus) {
  const status = statusValue(nextStatus);
  const { files, record } = readManagedRecord(type, slug);
  if (record.status === 'hidden') throw fail(409, 'This record uses Hidden status. Ask the operator to review it before changing publication.');
  if (record.status === status) return { type, slug, status, message: 'No publication-state change was needed.' };
  if (status === 'live' && reviewStatusForRecord(record) !== 'approved_for_release') {
    throw fail(409, 'Content must be Approved for release before it can be marked Live locally. Approval does not deploy or upload anything.');
  }
  if (status === 'live') verifyPublishableRecord(type, record);
  if (type !== 'gallery') safeManagedRoute(files.route, type, slug);
  const oldRecord = fs.readFileSync(files.record);
  const sitemapPath = path.join(ROOT, 'sitemap.xml');
  const oldSitemap = type === 'gallery' ? null : fs.readFileSync(sitemapPath);

  const changeState = () => {
    record.status = status;
    if (status === 'draft') record.review_status = 'draft';
    else record.review_status = 'approved_for_release';
    if (type !== 'gallery') record.date_modified = today();
    try {
      atomicWrite(files.record, JSON.stringify(record, null, 2) + '\n');
      const sitemapMessage = type === 'gallery' ? 'Gallery publication state updated.' : runSitemap();
      return { type, slug, status, pagePath: type === 'gallery' ? '/gallery.php' : '/' + slug + '.php', sitemapMessage };
    } catch (error) {
      try { atomicWrite(files.record, oldRecord); } catch (_) {}
      if (oldSitemap) { try { atomicWrite(sitemapPath, oldSitemap); } catch (_) {} }
      throw error;
    }
  };

  if (status !== 'live') return changeState();
  const source = type === 'gallery' ? record.image.src : record.hero_image;
  return withManifestSource(source, changeState);
}

function setReviewStatus(type, slug, nextReviewStatus) {
  const reviewStatus = reviewStatusValue(nextReviewStatus);
  const { files, record } = readManagedRecord(type, slug);
  if (record.status === 'hidden') throw fail(409, 'Hidden content must be reviewed by the operator before it can enter the review workflow.');
  const current = reviewStatusForRecord(record);
  if (current === reviewStatus) return { type, slug, reviewStatus, message: 'No review-state change was needed.' };
  if (record.status !== 'draft') throw fail(409, 'Set the content to Draft before changing its review state.');
  if (reviewStatus === 'ready_for_review' && current !== 'draft') {
    throw fail(409, 'Return the review state to Draft before resubmitting it for review.');
  }
  if (reviewStatus === 'approved_for_release' && current !== 'ready_for_review') {
    throw fail(409, 'Approve content only after it has been marked Ready for review.');
  }
  if (reviewStatus === 'ready_for_review' || reviewStatus === 'approved_for_release') {
    verifyPublishableRecord(type, record);
    if (type !== 'gallery') safeManagedRoute(files.route, type, slug);
  }
  const oldRecord = fs.readFileSync(files.record);
  record.review_status = reviewStatus;
  if (type !== 'gallery') record.date_modified = today();
  try {
    atomicWrite(files.record, JSON.stringify(record, null, 2) + '\n');
  } catch (error) {
    try { atomicWrite(files.record, oldRecord); } catch (_) {}
    throw error;
  }
  const labels = { draft: 'Draft', ready_for_review: 'Ready for review', approved_for_release: 'Approved for release' };
  return {
    type, slug, reviewStatus,
    message: 'Review state updated to ' + labels[reviewStatus] + '. This changes local review metadata only; it does not upload, deploy, or make the page public.',
  };
}

function deleteManagedDraft(type, slug) {
  const { files, record } = readManagedRecord(type, slug);
  if (record.status !== 'draft') throw fail(409, 'Only Draft content can be deleted. Set it to Draft first, then review the removal.');
  if (type !== 'gallery') safeManagedRoute(files.route, type, slug);
  const oldRecord = fs.readFileSync(files.record);
  const oldRoute = type === 'gallery' ? null : fs.readFileSync(files.route);
  const sitemapPath = path.join(ROOT, 'sitemap.xml');
  const oldSitemap = type === 'gallery' ? null : fs.readFileSync(sitemapPath);
  let routeRemoved = false;
  let recordRemoved = false;
  try {
    if (type !== 'gallery') {
      fs.unlinkSync(files.route);
      routeRemoved = true;
    }
    fs.unlinkSync(files.record);
    recordRemoved = true;
    if (type !== 'gallery') runSitemap();
    return { type, slug, deleted: true, message: 'Draft removed from the local working tree. Source images are retained.' };
  } catch (error) {
    if (recordRemoved) { try { atomicWrite(files.record, oldRecord); } catch (_) {} }
    if (routeRemoved && oldRoute) { try { atomicWrite(files.route, oldRoute); } catch (_) {} }
    if (oldSitemap) { try { atomicWrite(sitemapPath, oldSitemap); } catch (_) {} }
    throw error;
  }
}

function createTour(input, existingSlug = null, previewOnly = false) {
  const h1 = textField(input.title, 'Tour name', 3, 120);
  const requestedSlug = slugify(input.slug || h1);
  const slug = existingSlug || requestedSlug;
  if (existingSlug && input.slug && requestedSlug !== existingSlug) throw fail(400, 'The URL slug cannot be changed in the editor because existing links may point to it.');
  if (!existingSlug) assertStudioRouteAvailable(slug, 'experiences');
  const category = String(input.category || '');
  if (!['desert', 'city'].includes(category)) throw fail(400, 'Choose a desert or city tour category.');
  const durationType = String(input.durationType || '');
  if (!['half-day', 'full-day', 'overnight', 'multi-day'].includes(durationType)) throw fail(400, 'Choose a supported duration type.');
  const status = statusValue(input.status);
  const description = textField(input.description, 'SEO description', 40, 300);
  const summary = textField(input.summary, 'Card summary', 20, 500);
  const heroImage = imagePath(input.heroImage);
  const heroAlt = textField(input.heroAlt, 'Hero image alternative text', 10, 240);
  const itinerary = pipeRows(input.timeline, 'Itinerary', true, 12).map((row, index) => ({
    step: String(index + 1).padStart(2, '0'),
    title: textField(row[0], 'Itinerary step title', 2, 100),
    text: textField(row.slice(1).join(' | '), 'Itinerary step description', 4, 400),
  }));
  const faq = pipeRows(input.faq, 'FAQ', false, 12).map(row => ({
    question: textField(row[0], 'FAQ question', 5, 180),
    answer: textField(row.slice(1).join(' | '), 'FAQ answer', 5, 500),
  }));
  const date = today();
  const record = {
    slug, category, parent: category === 'city' ? 'guided-jaisalmer-tour' : 'desert-safari',
    menu_label: textField(input.menuLabel || h1, 'Short label', 3, 70),
    duration_type: durationType, order: 900, status, review_status: 'draft',
    title: textField(input.seoTitle || h1, 'SEO title', 8, 180),
    description, h1,
    eyebrow: textField(input.eyebrow || (category === 'city' ? 'Jaisalmer tour' : 'Desert experience'), 'Eyebrow', 2, 80),
    hero_image: heroImage, hero_width: 1280, hero_height: 800, hero_alt: heroAlt,
    summary,
    at_a_glance: {
      Duration: textField(input.durationLabel || durationType, 'Duration label', 2, 100),
      Overnight: textField(input.overnight || 'Confirm with the hotel', 'Overnight arrangement', 2, 100),
      'Camel ride': textField(input.camelRide || 'Confirm with the hotel', 'Camel ride', 2, 100),
      Meals: textField(input.meals || 'Confirm with the hotel', 'Meals', 2, 160),
      Transport: textField(input.transport || 'Confirm with the hotel', 'Transport', 2, 160),
      Return: textField(input.returnPlan || 'Confirm with the hotel', 'Return plan', 2, 160),
    },
    timeline_label: 'Typical sequence',
    timeline_note: textField(input.timelineNote || 'Exact timing and arrangements should be confirmed directly with the hotel for your travel date.', 'Itinerary note', 20, 400),
    timeline: itinerary,
    included: lines(input.included, 'Inclusions', false),
    not_included: lines(input.excluded, 'Exclusions', false),
    best_for: textField(input.bestFor, 'Best suited to', 10, 400),
    before_book: textField(input.beforeBook || 'Ask the hotel to confirm the current price, availability, pickup arrangements, timing and exact inclusions before booking.', 'Before booking note', 20, 500),
    faq,
    related: category === 'city' ? ['half-day-jaisalmer-tour', 'full-day-jaisalmer-tour'] : ['sleep-under-the-stars', 'swiss-tent-safari'],
    date_modified: date,
  };
  if (previewOnly) return { slug, status: 'draft', pagePath: '/' + slug + '.php', record: { ...record, status: 'draft', review_status: 'draft' } };
  if (!existingSlug && status === 'live') throw fail(409, 'New content must be saved as Draft, marked Ready for review, and Approved for release before it can be made Live locally.');
  return withManifestSource(heroImage, () => existingSlug
    ? updateManagedPage('tours', slug, record)
    : createPage('experiences', slug, record, 'experienceSlug'));
}

function parseArticle(value) {
  const sections = [];
  let section = null;
  let paragraphs = [];
  const flushParagraph = () => {
    if (section && paragraphs.join(' ').trim()) section.paragraphs.push(paragraphs.join(' ').trim());
    paragraphs = [];
  };
  const finish = () => {
    flushParagraph();
    if (section) sections.push(section);
    section = null;
  };
  for (const raw of String(value || '').replace(/\r/g, '').split('\n')) {
    const line = raw.trim();
    if (line.startsWith('## ')) {
      finish();
      section = { id: slugify(line.slice(3)) || 'section-' + (sections.length + 1), heading: textField(line.slice(3), 'Section heading', 2, 140), paragraphs: [], items: [] };
    } else if (!line) {
      flushParagraph();
    } else if (line.startsWith('- ')) {
      flushParagraph();
      if (!section) throw fail(400, 'Start an article section with a heading such as ## What to know.');
      section.items.push(textField(line.slice(2), 'Bullet item', 2, 400));
    } else {
      if (!section) throw fail(400, 'Every article needs a ## section heading before its body text.');
      paragraphs.push(line);
    }
  }
  finish();
  if (!sections.length) throw fail(400, 'Add at least one ## heading and body text.');
  if (sections.length > 30) throw fail(400, 'Use at most 30 article sections.');
  const used = new Set();
  for (const item of sections) {
    const initial = item.id;
    let n = 2;
    while (used.has(item.id)) item.id = initial + '-' + n++;
    used.add(item.id);
  }
  return sections;
}

function parseSources(value) {
  return pipeRows(value, 'Sources', true, 20).map(row => {
    const label = textField(row[0], 'Source label', 2, 120);
    const rawUrl = textField(row[1], 'Source URL', 8, 500);
    let url;
    try { url = new URL(rawUrl); } catch (_) { throw fail(400, 'Sources must be complete HTTPS URLs.'); }
    if (url.protocol !== 'https:' || !url.hostname) throw fail(400, 'Sources must use HTTPS.');
    return { label, url: url.toString(), note: textField(row.slice(2).join(' | ') || 'Reference for further reading.', 'Source note', 5, 300) };
  });
}

function createBlog(input, existingSlug = null, previewOnly = false) {
  const h1 = textField(input.h1, 'Article heading', 8, 160);
  const requestedSlug = slugify(input.slug || h1);
  const slug = existingSlug || requestedSlug;
  if (existingSlug && input.slug && requestedSlug !== existingSlug) throw fail(400, 'The URL slug cannot be changed in the editor because existing links may point to it.');
  if (!existingSlug) assertStudioRouteAvailable(slug, 'editorial');
  const date = today();
  const eyebrow = textField(input.eyebrow || 'Jaisalmer travel guide', 'Guide category', 2, 80);
  const description = textField(input.description, 'SEO description', 40, 300);
  const heroImage = imagePath(input.heroImage);
  const heroAlt = textField(input.heroAlt, 'Hero image alternative text', 10, 240);
  const record = {
    slug, status: statusValue(input.status), review_status: 'draft',
    title: textField(input.seoTitle || h1, 'SEO title', 8, 180),
    description, h1, eyebrow,
    lede: textField(input.lede, 'Article introduction', 30, 700),
    hero_image: heroImage, hero_alt: heroAlt,
    date_published: date, date_modified: date, date_display: displayDate(date),
    archive_title: textField(input.cardTitle || h1, 'Archive card heading', 3, 140),
    archive_description: textField(input.cardDescription || description, 'Archive card description', 10, 260),
    archive_category: eyebrow,
    toc: [],
    sections: parseArticle(input.sections),
    sources: parseSources(input.sources),
    related: [
      { label: 'Build your trip', title: 'Compare tours and experiences', href: 'catalogue.php', text: 'Shortlist options and ask the hotel to confirm current details.' },
      { label: 'Stay inside the Fort', title: 'Explore the rooms', href: 'room.php', text: 'Review room information before checking dates and rates.' },
      { label: 'Next step', title: 'Ask the hotel directly', href: 'contact.php', text: 'Confirm details that depend on your dates and requirements.' },
    ],
    cta_eyebrow: 'Plan with Garh Jaisal Haveli',
    cta_title: textField(input.ctaTitle || 'Plan your Jaisalmer stay.', 'Call-to-action heading', 5, 140),
    cta_text: textField(input.ctaText || 'Confirm current arrangements with the hotel before making travel plans.', 'Call-to-action text', 15, 400),
    cta_primary_label: 'Explore tours', cta_primary_href: 'catalogue.php',
    cta_secondary_label: 'Contact the hotel', cta_secondary_href: 'contact.php',
  };
  record.toc = record.sections.map(item => ({ id: item.id, label: item.heading }));
  if (previewOnly) return { slug, status: 'draft', pagePath: '/' + slug + '.php', record: { ...record, status: 'draft', review_status: 'draft' } };
  if (!existingSlug && record.status === 'live') throw fail(409, 'New content must be saved as Draft, marked Ready for review, and Approved for release before it can be made Live locally.');
  return withManifestSource(heroImage, () => existingSlug
    ? updateManagedPage('blogs', slug, record)
    : createPage('editorial', slug, record, 'editorialGuideSlug'));
}

function existingGallerySources(excludeId = null) {
  const sources = new Set();
  const php = fs.readFileSync(path.join(ROOT, 'include', 'gallery-data.php'), 'utf8');
  for (const match of php.matchAll(/'src'\s*=>\s*'([^']+)'/g)) sources.add(match[1]);
  const directory = path.join(CONTENT_ROOT, 'gallery');
  if (fs.existsSync(directory)) {
    for (const name of fs.readdirSync(directory).filter(name => name.endsWith('.json'))) {
      if (excludeId && name === excludeId + '.json') continue;
      const entry = JSON.parse(fs.readFileSync(path.join(directory, name), 'utf8'));
      if (entry && entry.image && typeof entry.image.src === 'string') sources.add(entry.image.src);
    }
  }
  return sources;
}

function createGallery(input, existingId = null) {
  const category = String(input.category || '');
  if (!CATEGORIES.includes(category)) throw fail(400, 'Choose one of the Gallery categories.');
  const src = imagePath(input.src);
  if (existingGallerySources(existingId).has(src)) throw fail(409, 'That image is already in the Gallery.');
  const alt = textField(input.alt, 'Alternative text', 10, 240);
  const caption = String(input.caption || alt).trim().slice(0, 240);
  const crop = String(input.crop || 'landscape');
  if (!CROPS.includes(crop)) throw fail(400, 'Choose a supported image crop.');
  const status = statusValue(input.status);
  const id = existingId || slugify(category + '-' + path.parse(src).name + '-' + crypto.randomBytes(3).toString('hex'));
  const previousManaged = existingId ? readManagedRecord('gallery', existingId) : null;
  if (!existingId && status === 'live') throw fail(409, 'New Gallery entries must be Draft, Ready for review, and Approved for release before they can be made Live locally.');
  if (previousManaged && previousManaged.record.status === 'hidden') throw fail(409, 'This record uses Hidden status. Ask the operator to review it before editing.');
  if (previousManaged && status === 'live') throw fail(409, 'Save Gallery edits as Draft, mark them Ready for review, and Approve for release before making them Live locally.');
  const record = { id, category, status, review_status: 'draft', image: { src, alt, caption, crop } };
  const file = previousManaged ? previousManaged.files.record : path.join(CONTENT_ROOT, 'gallery', id + '.json');
  assertContentDirectoryChain(path.dirname(file), true);
  const previous = previousManaged ? fs.readFileSync(file) : null;
  const manifestFile = path.join(ROOT, 'scripts', 'responsive-image-assets-manifest.json');
  const originalManifest = fs.readFileSync(manifestFile);
  try {
    updateImageManifest(src);
    const safeRecord = previousManaged
      ? { ...previousManaged.record, ...record, review_status: 'draft', image: { ...previousManaged.record.image, ...record.image } }
      : record;
    atomicWrite(file, JSON.stringify(safeRecord, null, 2) + '\n');
  } catch (error) {
    if (previous) { try { atomicWrite(file, previous); } catch (_) {} }
    try { atomicWrite(manifestFile, originalManifest); } catch (_) {}
    throw error;
  }
  return {
    id, status, category, src, pagePath: '/gallery.php',
    message: existingId ? 'Gallery entry updated in local files.' : status === 'live' ? 'Photo added to the local Gallery data. Review it in staging before production.' : 'Draft photo saved; it will not appear in the public Gallery yet.',
    updated: Boolean(existingId),
  };
}

function updateImageManifest(src) {
  const file = path.join(ROOT, 'scripts', 'responsive-image-assets-manifest.json');
  const manifest = JSON.parse(fs.readFileSync(file, 'utf8'));
  if (!Array.isArray(manifest.sources)) throw fail(500, 'Responsive-image manifest is invalid.');
  if (!manifest.sources.includes(src)) {
    manifest.sources.push(src);
    manifest.sources.sort();
    atomicWrite(file, JSON.stringify(manifest, null, 2) + '\n');
  }
}

function withManifestSource(src, action) {
  const file = path.join(ROOT, 'scripts', 'responsive-image-assets-manifest.json');
  const original = fs.readFileSync(file);
  try {
    updateImageManifest(src);
    return action();
  } catch (error) {
    try {
      if (!fs.readFileSync(file).equals(original)) atomicWrite(file, original);
    } catch (_) {}
    throw error;
  }
}

async function upload(req, url) {
  const originalName = path.basename(url.searchParams.get('filename') || 'upload.jpg');
  const ext = path.extname(originalName).toLowerCase();
  const type = IMAGE_TYPES[ext];
  if (!type) throw fail(415, 'Upload JPG, PNG, WebP or AVIF. SVG and executable files are not accepted.');
  if (String(req.headers['content-type'] || '').split(';')[0].trim().toLowerCase() !== type.mime) {
    throw fail(415, 'Uploaded MIME type does not match the file extension.');
  }
  const buffer = await readBody(req, MAX_IMAGE_BYTES);
  if (buffer.length < 16 || !type.signature(buffer)) throw fail(400, 'The uploaded file signature does not match its image type.');
  const name = slugify(path.parse(originalName).name).slice(0, 64) || 'photo';
  const folder = path.join(IMAGE_ROOT, 'content-studio');
  assertImageDirectoryChain(folder, true);
  const filename = Date.now().toString(36) + '-' + crypto.randomBytes(4).toString('hex') + '-' + name + ext;
  const full = path.join(folder, filename);
  const src = 'img/content-studio/' + filename;
  fs.writeFileSync(full, buffer, { flag: 'wx', mode: 0o644 });
  try { updateImageManifest(src); }
  catch (error) { try { fs.unlinkSync(full); } catch (_) {} throw error; }
  return { src, message: 'Uploaded. The source image is registered for responsive-image generation.' };
}

function previewRecordPaths() {
  return [
    { directory: path.join(CONTENT_ROOT, 'experiences'), type: 'tours' },
    { directory: path.join(CONTENT_ROOT, 'editorial'), type: 'blogs' },
  ];
}

function pruneExpiredPreviewRecords() {
  const now = Date.now();
  for (const { directory } of previewRecordPaths()) {
    let directoryStat;
    try { directoryStat = fs.lstatSync(directory); }
    catch (error) { if (error.code === 'ENOENT') continue; else throw error; }
    if (!directoryStat.isDirectory() || directoryStat.isSymbolicLink()) continue;
    for (const name of fs.readdirSync(directory)) {
      const match = name.match(/^content-studio-preview-([a-f0-9]{24})\.json$/);
      if (!match) continue;
      const file = path.join(directory, name);
      let stat;
      try { stat = fs.lstatSync(file); } catch (error) { if (error.code === 'ENOENT') continue; else throw error; }
      if (!stat.isFile() || stat.isSymbolicLink()) continue;
      try {
        const record = JSON.parse(fs.readFileSync(file, 'utf8'));
        const slug = name.slice(0, -5);
        if (record && record.slug === slug && record.studio_preview_token === match[1]
          && Number.isFinite(record.studio_preview_expires_at)
          && record.studio_preview_expires_at <= now) {
          fs.unlinkSync(file);
          const active = PREVIEW_SESSIONS.get(match[1]);
          if (active) {
            clearTimeout(active.timer);
            PREVIEW_SESSIONS.delete(match[1]);
          }
        }
      } catch (_) {
        // Malformed or manually altered records are left for operator review, never executed or guessed at.
      }
    }
  }
}

function cleanupPreviewSession(token) {
  if (typeof token !== 'string' || !/^[a-f0-9]{24}$/.test(token)) return false;
  const session = PREVIEW_SESSIONS.get(token);
  if (!session) return false;
  clearTimeout(session.timer);
  try {
    const stat = fs.lstatSync(session.file);
    if (!stat.isFile() || stat.isSymbolicLink()) throw fail(409, 'The preview record changed on disk and was not removed.');
    const record = JSON.parse(fs.readFileSync(session.file, 'utf8'));
    if (!record || record.slug !== session.slug || record.studio_preview_token !== token) {
      throw fail(409, 'The preview record no longer matches this session and was not removed.');
    }
    fs.unlinkSync(session.file);
  } catch (error) {
    if (error.code !== 'ENOENT') {
      PREVIEW_SESSIONS.delete(token);
      throw error;
    }
  }
  PREVIEW_SESSIONS.delete(token);
  return true;
}

function createPreview(input) {
  if (!input || typeof input !== 'object' || Array.isArray(input)) throw fail(400, 'Preview request must be an object.');
  const type = String(input.type || '');
  if (!['tours', 'blogs'].includes(type)) throw fail(400, 'Choose a supported page type to preview.');
  if (!input.payload || typeof input.payload !== 'object' || Array.isArray(input.payload)) throw fail(400, 'Complete the guided form before previewing.');
  const templateId = type === 'tours' ? 'experience-detail' : 'editorial-guide';
  const contract = getTemplateContract(templateId);
  if (!contract || contract.recordType !== (type === 'tours' ? 'tour' : 'blog')) throw fail(400, 'The selected page template is unavailable.');
  pruneExpiredPreviewRecords();
  const token = crypto.randomBytes(12).toString('hex');
  const slug = 'content-studio-preview-' + token;
  const payload = { ...input.payload, slug, status: 'draft' };
  const prepared = type === 'tours'
    ? createTour(payload, null, true)
    : createBlog(payload, null, true);
  const directory = path.join(CONTENT_ROOT, type === 'tours' ? 'experiences' : 'editorial');
  assertContentDirectoryChain(directory, true);
  const file = path.join(directory, slug + '.json');
  try {
    fs.lstatSync(file);
    throw fail(409, 'Could not reserve a unique local preview record. Generate the preview again.');
  } catch (error) {
    if (error.statusCode) throw error;
    if (error.code !== 'ENOENT') throw error;
  }
  const expiresAt = Date.now() + PREVIEW_TTL_MS;
  const record = {
    ...prepared.record,
    slug,
    status: 'draft',
    review_status: 'draft',
    studio_preview_token: token,
    studio_preview_expires_at: expiresAt,
  };
  atomicWrite(file, JSON.stringify(record, null, 2) + '\n');
  const session = { file, slug, type, timer: null };
  session.timer = setTimeout(() => {
    try { cleanupPreviewSession(token); } catch (_) {}
  }, PREVIEW_TTL_MS);
  if (session.timer && typeof session.timer.unref === 'function') session.timer.unref();
  PREVIEW_SESSIONS.set(token, session);
  return {
    token, type, slug,
    pagePath: '/tools/content-studio/preview.php?type=' + type + '&token=' + token,
    expiresAt: new Date(expiresAt).toISOString(),
    expiresInSeconds: Math.floor(PREVIEW_TTL_MS / 1000),
    message: 'Private, unsaved PHP preview. It expires automatically and does not change the sitemap, source image manifest, or publication state.',
  };
}

function statusPayload() {
  const summary = dir => {
    const folder = path.join(CONTENT_ROOT, dir);
    if (!fs.existsSync(folder)) return { total: 0, live: 0, drafts: 0, invalid: 0 };
    const records = fs.readdirSync(folder).filter(name => name.endsWith('.json') && !/^content-studio-preview-[a-f0-9]{24}\.json$/.test(name)).map(name => {
      try { return JSON.parse(fs.readFileSync(path.join(folder, name), 'utf8')).status || 'draft'; }
      catch (_) { return 'invalid'; }
    });
    return {
      total: records.length,
      live: records.filter(s => s === 'live').length,
      drafts: records.filter(s => s === 'draft').length,
      invalid: records.filter(s => s === 'invalid').length,
    };
  };
  return {
    message: 'Local authoring only. Changes stay in the Git working tree until you review and deploy them.',
    tours: summary('experiences'),
    blogs: summary('editorial'),
    gallery: summary('gallery'),
  };
}

const server = http.createServer(async (req, res) => {
  try {
    assertLocalRequest(req);
    const host = String(req.headers.host || '127.0.0.1:' + PORT);
    const url = new URL(req.url || '/', 'http://' + host);
    if (req.method === 'GET' && (url.pathname === '/' || url.pathname === '/index.html')) {
      return respond(res, 200, 'text/html; charset=utf-8', fs.readFileSync(UI_PATH));
    }
    if (req.method === 'GET' && url.pathname === '/api/status') return respondJson(res, 200, { ...statusPayload(), v2Registry: validateTemplateRegistry() });
    if (req.method === 'GET' && url.pathname === '/api/templates') return respondJson(res, 200, { templates: Object.keys(PAGE_TEMPLATE_REGISTRY).map(id => { const item = getTemplateContract(id); return { id: item.id, label: item.label, routePattern: item.routePattern, stylesheetPaths: [...item.stylesheetPaths], requiredFields: [...item.requiredFields], recordType: item.recordType }; }), managedPages: Object.entries(STUDIO_MANAGED_PAGE_REGISTRY).map(([route, page]) => ({ route, label: page.label, templateId: page.templateId, editableFields: [...page.editableFields] })) });
    if (req.method === 'POST' && url.pathname === '/api/preview') return respondJson(res, 201, createPreview(await readJson(req)));
    if (req.method === 'POST' && url.pathname === '/api/preview/cleanup') {
      const input = await readJson(req);
      return respondJson(res, 200, { removed: cleanupPreviewSession(input.token), message: 'The local preview session was closed. No public page or deployment was changed.' });
    }
    const reviewMatch = url.pathname.match(/^\/api\/content\/(tours|blogs|gallery)\/([a-z0-9]+(?:-[a-z0-9]+)*)\/review$/);
    if (reviewMatch && req.method === 'PATCH') {
      const [, type, slug] = reviewMatch;
      const input = await readJson(req);
      return respondJson(res, 200, setReviewStatus(type, slug, input.reviewStatus));
    }
    if (req.method === 'GET' && url.pathname === '/api/content') return respondJson(res, 200, listManagedContent());
    const managedMatch = url.pathname.match(/^\/api\/content\/(tours|blogs|gallery)\/([a-z0-9]+(?:-[a-z0-9]+)*)(\/status)?$/);
    if (managedMatch) {
      const [, type, slug, statusPath] = managedMatch;
      if (req.method === 'GET' && !statusPath) return respondJson(res, 200, readManagedContent(type, slug));
      if (req.method === 'PUT' && !statusPath) {
        const input = await readJson(req);
        const result = type === 'tours'
          ? createTour(input, slug)
          : type === 'blogs'
            ? createBlog(input, slug)
            : createGallery(input, slug);
        return respondJson(res, 200, result);
      }
      if (req.method === 'PATCH' && statusPath) {
        const input = await readJson(req);
        return respondJson(res, 200, setManagedStatus(type, slug, input.status));
      }
      if (req.method === 'DELETE' && !statusPath) return respondJson(res, 200, deleteManagedDraft(type, slug));
    }
    if (req.method === 'POST' && url.pathname === '/api/upload') return respondJson(res, 201, await upload(req, url));
    if (req.method === 'POST' && url.pathname === '/api/tours') return respondJson(res, 201, createTour(await readJson(req)));
    if (req.method === 'POST' && url.pathname === '/api/blogs') return respondJson(res, 201, createBlog(await readJson(req)));
    if (req.method === 'POST' && url.pathname === '/api/gallery') return respondJson(res, 201, createGallery(await readJson(req)));
    if (req.method === 'POST' && url.pathname === '/api/optimize') {
      const result = spawnSync('python3', ['scripts/build-responsive-image-assets.py'], {
        cwd: ROOT, encoding: 'utf8', timeout: 300000, maxBuffer: 5 * 1024 * 1024,
      });
      if (result.error || result.status !== 0) {
        throw fail(500, 'Responsive-image generation failed. Check Python 3/Pillow and source image formats. ' + String(result.stderr || result.stdout || result.error?.message || '').trim());
      }
      return respondJson(res, 200, { message: 'Responsive image build completed.', output: String(result.stdout || '').trim() });
    }
    if (req.method === 'GET' && url.pathname === '/favicon.ico') { res.writeHead(204); return res.end(); }
    return respondJson(res, 404, { error: 'No Content Studio route matches this request.' });
  } catch (error) {
    const status = Number(error.statusCode) || 500;
    return respondJson(res, status, { error: String(error.message || 'Content Studio failed.') });
  }
});

server.requestTimeout = 120000;
server.headersTimeout = 15000;
server.listen(PORT, '127.0.0.1', () => {
  process.stdout.write('Garh Jaisal Content Studio is local-only: http://127.0.0.1:' + PORT + '\n');
  process.stdout.write('It does not deploy and is excluded from the cPanel runtime package.\n');
});
