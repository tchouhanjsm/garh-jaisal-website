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
const IMAGE_TYPES = {
  '.jpg': { mime: 'image/jpeg', signature: b => b.length > 3 && b[0] === 0xff && b[1] === 0xd8 && b[2] === 0xff },
  '.jpeg': { mime: 'image/jpeg', signature: b => b.length > 3 && b[0] === 0xff && b[1] === 0xd8 && b[2] === 0xff },
  '.png': { mime: 'image/png', signature: b => b.length > 8 && b.subarray(0, 8).equals(Buffer.from([137,80,78,71,13,10,26,10])) },
  '.webp': { mime: 'image/webp', signature: b => b.length > 12 && b.toString('ascii', 0, 4) === 'RIFF' && b.toString('ascii', 8, 12) === 'WEBP' },
  '.avif': { mime: 'image/avif', signature: b => b.length > 12 && b.toString('ascii', 4, 8) === 'ftyp' && /(?:avif|avis|mif1)/.test(b.toString('ascii', 8, Math.min(32, b.length))) },
};
const CATEGORIES = ['hotel', 'rooms', 'desert', 'restaurant'];
const CROPS = ['arch', 'landscape', 'portrait', 'standard'];

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
    'Content-Security-Policy': "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; base-uri 'none'; frame-ancestors 'none'",
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

function today() {
  return new Date().toISOString().slice(0, 10);
}

function displayDate(iso) {
  return new Intl.DateTimeFormat('en-GB', {
    day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC',
  }).format(new Date(iso + 'T00:00:00Z'));
}

function imagePath(value) {
  const src = typeof value === 'string' ? value.trim() : '';
  if (!/^img\/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$/i.test(src) || src.includes('..') || src.includes('\\')) {
    throw fail(400, 'Choose an image under img/ or upload it using Content Studio.');
  }
  const full = path.resolve(ROOT, src);
  const prefix = path.resolve(IMAGE_ROOT) + path.sep;
  if (!full.startsWith(prefix)) throw fail(400, 'Image path is outside img/.');
  try {
    const st = fs.lstatSync(full);
    if (!st.isFile() || st.isSymbolicLink()) throw fail(400, 'Image must be a regular local file.');
  } catch (error) {
    if (error.statusCode) throw error;
    throw fail(400, 'Image file does not exist: ' + src);
  }
  return src;
}

function ensureNewSlug(slug, type) {
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw fail(400, 'Use a URL slug made from lowercase letters, digits and hyphens.');
  if (fs.existsSync(path.join(ROOT, slug + '.php')) || fs.existsSync(path.join(CONTENT_ROOT, type, slug + '.json'))) {
    throw fail(409, 'That slug already exists. Existing pages are never overwritten.');
  }
  return {
    route: path.join(ROOT, slug + '.php'),
    record: path.join(CONTENT_ROOT, type, slug + '.json'),
  };
}

function wrapper(variable, slug) {
  const renderer = variable === 'experienceSlug' ? 'experience-page.php' : 'editorial-guide-page.php';
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
  let recordCreated = false;
  let routeCreated = false;
  try {
    atomicWrite(files.record, JSON.stringify(record, null, 2) + '\n');
    recordCreated = true;
    atomicWrite(files.route, wrapper(variable, slug));
    routeCreated = true;
    let sitemapMessage = 'Draft saved. It is excluded from the public archive and sitemap.';
    if (record.status === 'live') sitemapMessage = runSitemap();
    return { slug, status: record.status, pagePath: '/' + slug + '.php', sitemapMessage };
  } catch (error) {
    if (routeCreated) { try { fs.unlinkSync(files.route); } catch (_) {} }
    if (recordCreated) { try { fs.unlinkSync(files.record); } catch (_) {} }
    try { atomicWrite(sitemapPath, oldSitemap); } catch (_) {}
    throw error;
  }
}

function createTour(input) {
  const h1 = textField(input.title, 'Tour name', 3, 120);
  const slug = slugify(input.slug || h1);
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
    duration_type: durationType, order: 900, status,
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
  return createPage('experiences', slug, record, 'experienceSlug');
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

function createBlog(input) {
  const h1 = textField(input.h1, 'Article heading', 8, 160);
  const slug = slugify(input.slug || h1);
  const date = today();
  const eyebrow = textField(input.eyebrow || 'Jaisalmer travel guide', 'Guide category', 2, 80);
  const description = textField(input.description, 'SEO description', 40, 300);
  const heroImage = imagePath(input.heroImage);
  const heroAlt = textField(input.heroAlt, 'Hero image alternative text', 10, 240);
  const record = {
    slug, status: statusValue(input.status),
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
  return createPage('editorial', slug, record, 'editorialGuideSlug');
}

function existingGallerySources() {
  const sources = new Set();
  const php = fs.readFileSync(path.join(ROOT, 'include', 'gallery-data.php'), 'utf8');
  for (const match of php.matchAll(/'src'\s*=>\s*'([^']+)'/g)) sources.add(match[1]);
  const directory = path.join(CONTENT_ROOT, 'gallery');
  if (fs.existsSync(directory)) {
    for (const name of fs.readdirSync(directory).filter(name => name.endsWith('.json'))) {
      const entry = JSON.parse(fs.readFileSync(path.join(directory, name), 'utf8'));
      if (entry && entry.image && typeof entry.image.src === 'string') sources.add(entry.image.src);
    }
  }
  return sources;
}

function createGallery(input) {
  const category = String(input.category || '');
  if (!CATEGORIES.includes(category)) throw fail(400, 'Choose one of the Gallery categories.');
  const src = imagePath(input.src);
  if (existingGallerySources().has(src)) throw fail(409, 'That image is already in the Gallery.');
  const alt = textField(input.alt, 'Alternative text', 10, 240);
  const caption = String(input.caption || alt).trim().slice(0, 240);
  const crop = String(input.crop || 'landscape');
  if (!CROPS.includes(crop)) throw fail(400, 'Choose a supported image crop.');
  const status = statusValue(input.status);
  const id = slugify(category + '-' + path.parse(src).name + '-' + crypto.randomBytes(3).toString('hex'));
  const record = { id, category, status, image: { src, alt, caption, crop } };
  atomicWrite(path.join(CONTENT_ROOT, 'gallery', id + '.json'), JSON.stringify(record, null, 2) + '\n');
  return {
    id, status, category, src, pagePath: '/gallery.php',
    message: status === 'live' ? 'Photo added to the local Gallery data. Review it in staging before production.' : 'Draft photo saved; it will not appear in the public Gallery yet.',
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
  fs.mkdirSync(folder, { recursive: true });
  const filename = Date.now().toString(36) + '-' + crypto.randomBytes(4).toString('hex') + '-' + name + ext;
  const full = path.join(folder, filename);
  const src = 'img/content-studio/' + filename;
  fs.writeFileSync(full, buffer, { flag: 'wx', mode: 0o644 });
  try { updateImageManifest(src); }
  catch (error) { try { fs.unlinkSync(full); } catch (_) {} throw error; }
  return { src, message: 'Uploaded. The source image is registered for responsive-image generation.' };
}

function statusPayload() {
  const summary = dir => {
    const folder = path.join(CONTENT_ROOT, dir);
    if (!fs.existsSync(folder)) return { total: 0, live: 0, drafts: 0, invalid: 0 };
    const records = fs.readdirSync(folder).filter(name => name.endsWith('.json')).map(name => {
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
    if (req.method === 'GET' && url.pathname === '/api/status') return respondJson(res, 200, statusPayload());
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
