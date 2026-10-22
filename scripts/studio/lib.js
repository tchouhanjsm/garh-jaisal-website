'use strict';
// Shared helpers for the Content Studio path-pack workflow. Node built-ins only.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');

const ROOT = path.resolve(__dirname, '../..');
const SLUG_RE = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
const PATHS = JSON.parse(fs.readFileSync(path.join(ROOT, 'content/_paths.json'), 'utf8'));
const TYPES = Object.keys(PATHS.types);
const ROLES = PATHS.roles;
const EXTS = PATHS.image_extensions;

const sha256 = buf => crypto.createHash('sha256').update(buf).digest('hex');
const finding = (rule, file, message, fix) => ({ rule, file: file || '', message, fix: fix || '' });

function typeConfig(type) {
  const cfg = PATHS.types[type];
  if (!cfg) throw new Error('Unknown content type "' + type + '". Use one of: ' + TYPES.join(', ') + '.');
  return cfg;
}

function recordPath(type, slug) { return typeConfig(type).record_dir + '/' + slug + '.json'; }
function pagePath(type, slug) { const p = typeConfig(type).page; return p ? p.replace('{slug}', slug) : null; }
function imageDir(type, slug, category) {
  return typeConfig(type).images.replace('{slug}', slug).replace('{category}', category || '');
}

// The exact two-line wrapper the Studio writes for a page route.
function wrapperText(type, slug) {
  const cfg = typeConfig(type);
  return "<?php\n$" + cfg.wrapper_var + " = '" + slug + "';\nrequire __DIR__ . '/include/" + cfg.renderer + "';\n";
}

// Photo names: <slug>-<role>-<nn>.<ext>
function photoNameRe(slug) {
  return new RegExp('^' + slug.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '-(' + ROLES.join('|') + ')-(\\d{2})\\.(' + EXTS.join('|') + ')$');
}

// Returns { kind:'webp'|'avif'|null, width:number|null }
function sniffImage(buf) {
  if (buf.length >= 30 && buf.toString('latin1', 0, 4) === 'RIFF' && buf.toString('latin1', 8, 12) === 'WEBP') {
    const fmt = buf.toString('latin1', 12, 16);
    let width = null;
    if (fmt === 'VP8X') width = 1 + (buf[24] | (buf[25] << 8) | (buf[26] << 16));
    else if (fmt === 'VP8 ') width = buf.readUInt16LE(26) & 0x3fff;
    else if (fmt === 'VP8L') width = 1 + (((buf[22] << 8) | buf[21]) & 0x3fff);
    return { kind: 'webp', width };
  }
  if (buf.length >= 12 && buf.toString('latin1', 4, 8) === 'ftyp' && /^(avif|avis)$/.test(buf.toString('latin1', 8, 12))) return { kind: 'avif', width: null };
  return { kind: null, width: null };
}

function atomicWrite(file, data) {
  fs.mkdirSync(path.dirname(file), { recursive: true });
  const tmp = file + '.tmp-' + process.pid + '-' + crypto.randomBytes(4).toString('hex');
  fs.writeFileSync(tmp, data);
  fs.renameSync(tmp, file);
}

// A repo-relative path that stays inside the repository and is a plain forward-slash path.
function safeRelative(p) {
  if (typeof p !== 'string' || !p || p.startsWith('/') || p.includes('\\') || /^[a-zA-Z]:/.test(p) || p.includes('\0')) return false;
  return p.split('/').every(seg => seg && seg !== '.' && seg !== '..');
}

// The site root can be redirected (tests, dry runs on a copy) with CONTENT_STUDIO_ROOT.
const repoRoot = () => (process.env.CONTENT_STUDIO_ROOT ? path.resolve(process.env.CONTENT_STUDIO_ROOT) : ROOT);
function existsInRepo(rel) { return fs.existsSync(path.join(repoRoot(), rel)); }
function hashInRepo(rel) {
  const abs = path.join(repoRoot(), rel);
  return fs.existsSync(abs) ? sha256(fs.readFileSync(abs)) : null;
}
function recordExists(type, slug) {
  return existsInRepo(recordPath(type, slug)) || (pagePath(type, slug) !== null && existsInRepo(pagePath(type, slug)));
}

module.exports = { ROOT, repoRoot, SLUG_RE, PATHS, TYPES, ROLES, EXTS, sha256, finding, typeConfig, recordPath, pagePath, imageDir, wrapperText, photoNameRe, sniffImage, atomicWrite, safeRelative, existsInRepo, hashInRepo, recordExists };
