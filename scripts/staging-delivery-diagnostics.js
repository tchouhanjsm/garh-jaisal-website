'use strict';
const MAX_RELEASE_MARKER_BYTES = 128;
function headerValue(headers, name) {
  if (!headers) return '';
  if (typeof headers.get === 'function') return headers.get(name) || '';
  const target = name.toLowerCase();
  const key = Object.keys(headers).find(item => item.toLowerCase() === target);
  return key ? String(headers[key] || '') : '';
}
function parseReleaseMarker(content) {
  if (typeof content !== 'string' || Buffer.byteLength(content, 'utf8') > MAX_RELEASE_MARKER_BYTES) return null;
  const value = content.endsWith('\n') ? content.slice(0, -1) : content;
  const normalized = value.endsWith('\r') ? value.slice(0, -1) : value;
  const match = normalized.match(/^GARH_JAISAL_RELEASE_SHA=([0-9a-f]{40})$/i);
  return match ? match[1].toLowerCase() : null;
}
function cacheMaxAgeSeconds(headers, nowMs = Date.now()) {
  const cacheControl = headerValue(headers, 'cache-control');
  if (/\b(?:no-store|no-cache)\b/i.test(cacheControl)) return 0;
  const maxAge = cacheControl.match(/(?:^|,)\s*max-age\s*=\s*"?([0-9]+)"?/i);
  if (maxAge) return Number(maxAge[1]);
  const expires = Date.parse(headerValue(headers, 'expires'));
  if (!Number.isFinite(expires)) return null;
  const serverDate = Date.parse(headerValue(headers, 'date'));
  const baseline = Number.isFinite(serverDate) ? serverDate : nowMs;
  return Math.max(0, Math.floor((expires - baseline) / 1000));
}
function normalizeImagePolicySnapshot(snapshot) {
  const source = snapshot && typeof snapshot === 'object' ? snapshot : {};
  const normalize = (property, attribute, allowed, fallback) => {
    const raw = typeof source[property] === 'string' ? source[property]
      : typeof source[attribute] === 'string' ? source[attribute] : fallback;
    const value = raw.toLowerCase();
    return allowed.includes(value) ? value : fallback;
  };
  return {
    loading: normalize('loadingProperty', 'loadingAttribute', ['eager', 'lazy'], 'eager'),
    loadingAttribute: typeof source.loadingAttribute === 'string' ? source.loadingAttribute : null,
    fetchPriority: normalize('fetchPriorityProperty', 'fetchPriorityAttribute', ['high', 'low', 'auto'], 'auto'),
    fetchPriorityAttribute: typeof source.fetchPriorityAttribute === 'string' ? source.fetchPriorityAttribute : null,
    decoding: normalize('decodingProperty', 'decodingAttribute', ['sync', 'async', 'auto'], 'auto'),
    decodingAttribute: typeof source.decodingAttribute === 'string' ? source.decodingAttribute : null,
    visibleAtInitialViewport: source.visibleAtInitialViewport === true,
    policyCapturedBeforeAuditMutation: source.captured === true,
  };
}
function parseSrcsetCandidates(srcset, baseUrl) {
  if (typeof srcset !== 'string' || !srcset.trim()) return [];
  return srcset.split(',').map(candidate => {
    const parts = candidate.trim().split(/\s+/);
    if (parts.length !== 2 || !/^[1-9][0-9]*w$/i.test(parts[1])) return null;
    try { return { url: new URL(parts[0], baseUrl).href, width: Number(parts[1].slice(0, -1)), descriptor: parts[1] }; }
    catch { return null; }
  }).filter(Boolean);
}
function selectedSrcsetWidth(srcset, currentSrc, baseUrl) {
  if (typeof currentSrc !== 'string' || !currentSrc) return null;
  let selected;
  try { selected = new URL(currentSrc, baseUrl).href; } catch { return null; }
  const candidate = parseSrcsetCandidates(srcset, baseUrl).find(item => item.url === selected);
  return candidate ? candidate.width : null;
}
function normalizedSrcset(srcset, baseUrl) {
  return parseSrcsetCandidates(srcset, baseUrl).map(item => item.url + ' ' + item.descriptor).sort().join(', ');
}
module.exports = { cacheMaxAgeSeconds, normalizeImagePolicySnapshot, normalizedSrcset, parseReleaseMarker, selectedSrcsetWidth };
