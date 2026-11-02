'use strict';

const assert = require('node:assert/strict');
const { normalise } = require('../../scripts/studio/route-snapshot');

const baseline = `<!doctype html>
<link rel="preload" as="image" href="/img/desert.jpg" imagesrcset="/img/responsive/desert-480.webp 480w, /img/desert.jpg 1600w" imagesizes="100vw">
<main><img src="/img/desert.jpg" alt="Desert at sunset" width="1600" height="900" loading="lazy"></main>`;

const responsiveVariant = `<!doctype html>
<link rel="preload" as="image" href="/img/desert.jpg" imagesrcset="/img/responsive/desert-480.webp 480w, /img/responsive/desert-1200.webp 1200w, /img/desert.jpg 1600w" imagesizes="(max-width: 760px) 100vw, 50vw">
<main><img src="/img/desert.jpg" srcset="/img/responsive/desert-480.webp 480w, /img/responsive/desert-1200.webp 1200w, /img/desert.jpg 1600w" sizes="(max-width: 760px) 100vw, 50vw" alt="Desert at sunset" width="1600" height="900" loading="lazy"></main>`;

assert.equal(
  normalise(responsiveVariant),
  normalise(baseline),
  'adding or changing responsive image candidates must not make a route content snapshot fail'
);

assert.notEqual(
  normalise(responsiveVariant.replace('alt="Desert at sunset"', 'alt="Different photograph"')),
  normalise(baseline),
  'accessible image descriptions remain part of route content'
);
assert.notEqual(
  normalise(responsiveVariant.replace('src="/img/desert.jpg"', 'src="/img/other.jpg"')),
  normalise(baseline),
  'the selected source image remains part of route content'
);
assert.notEqual(
  normalise(responsiveVariant.replace('Desert at sunset', 'Desert at sunrise')),
  normalise(baseline),
  'visible content remains part of route content'
);

console.log('PASS route snapshot ignores responsive delivery hints without ignoring image identity or content.');
