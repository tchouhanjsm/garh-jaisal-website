'use strict';
// place_photo: validates role and number, converts to webp within the size caps, and writes
// to the exact folder for the entity inside a stage folder. The code chooses the path.
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const L = require('./lib');

const PY = `
import sys
from PIL import Image
src, dst, max_w, max_b = sys.argv[1], sys.argv[2], int(sys.argv[3]), int(sys.argv[4])
im = Image.open(src)
im = im.convert('RGB')
if im.width > max_w:
    im = im.resize((max_w, round(im.height * max_w / im.width)), Image.LANCZOS)
for q in (82, 74, 66, 58, 50, 42, 34):
    im.save(dst, 'WEBP', quality=q, method=6)
    import os
    if os.path.getsize(dst) <= max_b:
        print(os.path.getsize(dst), im.width, im.height); sys.exit(0)
sys.exit(3)
`;

function placePhoto({ type, slug, role, n, file, category, stage }) {
  if (!L.TYPES.includes(type)) throw new Error('Unknown type "' + type + '". Use: ' + L.TYPES.join(', ') + '.');
  if (!L.SLUG_RE.test(String(slug || ''))) throw new Error('Invalid slug. Use lowercase letters, digits and hyphens.');
  if (!L.ROLES.includes(role)) throw new Error('Unknown role "' + role + '". Use: ' + L.ROLES.join(', ') + '.');
  if (!Number.isInteger(n) || n < 1 || n > 99) throw new Error('Photo number must be 1 to 99.');
  if (type === 'gallery' && !L.typeConfig('gallery').categories.includes(category)) throw new Error('Gallery photos need a category: ' + L.typeConfig('gallery').categories.join(', ') + '.');
  const rel = L.imageDir(type, slug, category) + slug + '-' + role + '-' + String(n).padStart(2, '0') + '.webp';
  const dest = path.join(stage, rel);
  if (fs.existsSync(dest)) throw new Error(rel + ' is already placed. Use the next number, or remove it deliberately to replace it.');
  const cap = role === 'hero' ? L.PATHS.limits.hero : L.PATHS.limits.other;
  const stat = fs.statSync(file);
  if (!stat.isFile() || stat.size > 25 * 1024 * 1024) throw new Error('Photo must be a regular file under 25 MB.');
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  const r = spawnSync('python3', ['-c', PY, file, dest, String(cap.max_width), String(cap.max_bytes)], { encoding: 'utf8' });
  if (r.status !== 0) {
    try { fs.unlinkSync(dest); } catch (_) {}
    throw new Error(r.status === 3 ? 'Could not fit the photo within ' + cap.max_bytes + ' bytes; crop it or use a simpler image.' : 'Image conversion failed (needs python3 with Pillow, and a real image): ' + String(r.stderr || '').trim().split('\n').pop());
  }
  const [bytes, width, height] = r.stdout.trim().split(/\s+/).map(Number);
  return { path: rel, bytes, width, height, role, alt_needed: true };
}

module.exports = { placePhoto };
