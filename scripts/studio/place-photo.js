'use strict';
// place_photo: validates role and number, converts to webp within the size caps, and writes
// to the exact folder for the entity inside a stage folder. The code chooses the path.
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const L = require('./lib');

const PY = `
import sys, os
from PIL import Image, ImageOps, ImageFilter, ImageStat
src, dst, max_w, max_b, min_w = sys.argv[1], sys.argv[2], int(sys.argv[3]), int(sys.argv[4]), int(sys.argv[5])
im = ImageOps.exif_transpose(Image.open(src)).convert('RGB')
if im.width < min_w:
    sys.exit(4)
if im.width > max_w:
    im = im.resize((max_w, round(im.height * max_w / im.width)), Image.LANCZOS)
for q in (82, 74, 66, 58, 50, 42, 34):
    im.save(dst, 'WEBP', quality=q, method=6)
    if os.path.getsize(dst) <= max_b:
        g = im.convert('L')
        sharp = ImageStat.Stat(g.filter(ImageFilter.FIND_EDGES)).var[0]
        print(os.path.getsize(dst), im.width, im.height, round(sharp, 1)); sys.exit(0)
sys.exit(3)
`;

// Reads a photo once and reports what the automatic flow needs: size after rotation, sharpness, a perceptual
// hash for duplicate detection and the capture time (EXIF) for ordering. Never writes anything.
const ANALYZE = `
import sys, json
from PIL import Image, ImageOps, ImageFilter, ImageStat
im0 = Image.open(sys.argv[1])
taken = ''
try:
    ex = im0.getexif()
    taken = str(ex.get_ifd(0x8769).get(36867) or ex.get(306) or '')
except Exception:
    pass
im = ImageOps.exif_transpose(im0).convert('RGB')
g = im.convert('L')
small = g.resize((9, 8), Image.LANCZOS)
px = list(small.getdata())
bits = ''.join('1' if px[r*9+c] > px[r*9+c+1] else '0' for r in range(8) for c in range(8))
sharp = ImageStat.Stat(g.resize((min(g.width, 800), max(1, round(g.height * min(g.width, 800) / g.width)))).filter(ImageFilter.FIND_EDGES)).var[0]
print(json.dumps({'width': im.width, 'height': im.height, 'sharp': round(sharp, 1), 'dhash': format(int(bits, 2), '016x'), 'taken': taken}))
`;

function analyzePhoto(file) {
  const r = spawnSync('python3', ['-c', ANALYZE, file], { encoding: 'utf8' });
  if (r.status !== 0) throw new Error('Could not read this file as a photo (needs a real JPG, PNG or WebP and python3 with Pillow).');
  return JSON.parse(r.stdout);
}
const hamming = (a, b) => { let x = BigInt('0x' + a) ^ BigInt('0x' + b), n = 0; while (x) { n += Number(x & 1n); x >>= 1n; } return n; };


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
  const r = spawnSync('python3', ['-c', PY, file, dest, String(cap.max_width), String(cap.max_bytes), String(role === 'hero' ? 800 : 600)], { encoding: 'utf8' });
  if (r.status !== 0) {
    try { fs.unlinkSync(dest); } catch (_) {}
    throw new Error(r.status === 4 ? 'Photo is too small after rotation: use at least ' + (role === 'hero' ? 800 : 600) + ' px wide.' : r.status === 3 ? 'Could not fit the photo within ' + cap.max_bytes + ' bytes; crop it or use a simpler image.' : 'Image conversion failed (needs python3 with Pillow, and a real image): ' + String(r.stderr || '').trim().split('\n').pop());
  }
  const [bytes, width, height, sharp] = r.stdout.trim().split(/\s+/).map(Number);
  return { path: rel, bytes, width, height, sharp, role, alt_needed: true };
}

module.exports = { placePhoto, analyzePhoto, hamming };
