'use strict';
const assert = require('node:assert/strict');
const test = require('node:test');
const { cacheMaxAgeSeconds, normalizedSrcset, parseReleaseMarker, selectedSrcsetWidth } = require('../scripts/staging-delivery-diagnostics');
const sha = 'a'.repeat(40);
test('parses only a bounded full-SHA release marker', () => {
  assert.equal(parseReleaseMarker('GARH_JAISAL_RELEASE_SHA=' + sha + '\n'), sha);
  assert.equal(parseReleaseMarker('GARH_JAISAL_RELEASE_SHA=' + sha.toUpperCase()), sha);
  assert.equal(parseReleaseMarker('GARH_JAISAL_RELEASE_SHA=short'), null);
  assert.equal(parseReleaseMarker('../include/site-config.local.php'), null);
  assert.equal(parseReleaseMarker('x'.repeat(129)), null);
});
test('uses Cache-Control max-age as the effective image cache lifetime', () => {
  assert.equal(cacheMaxAgeSeconds({ 'cache-control': 'public, max-age=2592000', expires: 'Wed, 30 Dec 2026 00:00:00 GMT', date: 'Mon, 30 Nov 2026 00:00:00 GMT' }), 2592000);
});
test('treats no-store and no-cache as non-cacheable', () => {
  assert.equal(cacheMaxAgeSeconds({ 'cache-control': 'public, no-cache, max-age=3600' }), 0);
  assert.equal(cacheMaxAgeSeconds({ 'cache-control': 'no-store' }), 0);
});
test('derives cache lifetime from Expires when Cache-Control max-age is absent', () => {
  assert.equal(cacheMaxAgeSeconds({ expires: 'Tue, 01 Dec 2026 00:00:00 GMT', date: 'Mon, 30 Nov 2026 00:00:00 GMT' }), 86400);
});
test('returns null when the host provides no cache lifetime', () => {
  assert.equal(cacheMaxAgeSeconds({ 'content-type': 'image/webp' }), null);
});
test('matches selected responsive candidate by its width descriptor', () => {
  const srcset = '/img/responsive/hero-480.webp 480w, /img/responsive/hero-800.webp 800w';
  assert.equal(selectedSrcsetWidth(srcset, 'https://stage.example.net/img/responsive/hero-800.webp', 'https://stage.example.net/'), 800);
  assert.equal(selectedSrcsetWidth(srcset, 'https://stage.example.net/img/original.jpg', 'https://stage.example.net/'), null);
});
test('normalizes candidate URLs before comparing preload parity', () => {
  const left = 'img/responsive/hero-480.webp 480w, /img/responsive/hero-800.webp 800w';
  const right = '/img/responsive/hero-800.webp 800w, /img/responsive/hero-480.webp 480w';
  assert.equal(normalizedSrcset(left, 'https://stage.example.net/'), normalizedSrcset(right, 'https://stage.example.net/'));
});
