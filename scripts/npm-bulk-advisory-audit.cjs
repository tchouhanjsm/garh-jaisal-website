'use strict';

// CI dependency audit using npm's supported Bulk Advisory endpoint directly.
// npm's registry edge has returned gzip bodies without Content-Encoding, causing
// npm CLI to misparse them and fall back to the retired Quick Audit endpoint.
// This auditor reads the lockfile, normalizes mislabeled gzip responses, and
// fails closed on transport, schema, or unknown-severity errors.

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const https = require('node:https');
const { gunzipSync, gzipSync } = require('node:zlib');

const ROOT = path.resolve(__dirname, '..');
const LOCKFILE = path.join(ROOT, 'package-lock.json');
const REGISTRY_HOST = 'registry.npmjs.org';
const BULK_PATH = '/-/npm/v1/security/advisories/bulk';
const MAX_REQUEST_BYTES = 2 * 1024 * 1024;
const MAX_RESPONSE_BYTES = 10 * 1024 * 1024;
const MAX_DECOMPRESSED_BYTES = 20 * 1024 * 1024;
const SEVERITIES = Object.freeze(['info', 'low', 'moderate', 'high', 'critical']);
const SEVERITY_WEIGHT = Object.freeze({ info: 0, low: 1, moderate: 2, high: 3, critical: 4 });

function hasGzipSignature(buffer) {
  return Buffer.isBuffer(buffer) && buffer.length >= 2
    && buffer[0] === 0x1f && buffer[1] === 0x8b;
}

function decodeJsonBody(rawBody) {
  let body = Buffer.isBuffer(rawBody) ? rawBody : Buffer.from(rawBody || '');
  const wasGzip = hasGzipSignature(body);
  if (wasGzip) body = gunzipSync(body, { maxOutputLength: MAX_DECOMPRESSED_BYTES });
  if (body.length > MAX_DECOMPRESSED_BYTES) {
    throw new Error('Decoded npm advisory response exceeded the safety limit.');
  }

  let parsed;
  try {
    parsed = JSON.parse(body.toString('utf8'));
  } catch (error) {
    throw new Error('npm registry bulk advisory response was not valid JSON: ' + error.message);
  }
  return { parsed, wasGzip, byteLength: body.length };
}

function packageNameFromLocation(location) {
  if (typeof location !== 'string' || !location.includes('node_modules/')) return null;
  const index = location.lastIndexOf('node_modules/');
  const remaining = location.slice(index + 'node_modules/'.length);
  if (!remaining || remaining.startsWith('.bin/')) return null;
  if (remaining.startsWith('@')) {
    const parts = remaining.split('/');
    return parts.length >= 2 ? parts[0] + '/' + parts[1] : null;
  }
  return remaining.split('/')[0] || null;
}

function buildAdvisoryPayload(lockfile) {
  if (!lockfile || typeof lockfile !== 'object' || Array.isArray(lockfile)
    || !lockfile.packages || typeof lockfile.packages !== 'object'
    || Array.isArray(lockfile.packages)) {
    throw new Error('package-lock.json is missing its supported packages map; refusing to skip the dependency audit.');
  }
  if (!Number.isInteger(lockfile.lockfileVersion) || lockfile.lockfileVersion < 2) {
    throw new Error('The dependency audit requires package-lock.json lockfileVersion 2 or newer.');
  }

  const versionsByName = new Map();
  for (const [location, entry] of Object.entries(lockfile.packages)) {
    if (!location || !entry || typeof entry !== 'object' || Array.isArray(entry) || entry.link === true) continue;
    if (typeof entry.version !== 'string' || !entry.version.trim()) continue;

    const name = typeof entry.name === 'string' && entry.name.trim()
      ? entry.name.trim()
      : packageNameFromLocation(location);
    if (!name) {
      throw new Error('A versioned lockfile entry could not be mapped to a package name: ' + location);
    }
    if (name.includes('\u0000') || /\s/.test(name)) {
      throw new Error('A lockfile package name is invalid: ' + location);
    }

    if (!versionsByName.has(name)) versionsByName.set(name, new Set());
    versionsByName.get(name).add(entry.version.trim());
  }

  if (versionsByName.size === 0) {
    throw new Error('No versioned dependencies were found in package-lock.json; refusing to claim an audit passed.');
  }

  const payload = {};
  for (const [name, versions] of [...versionsByName].sort(([a], [b]) => a.localeCompare(b))) {
    payload[name] = [...versions].sort();
  }
  const body = Buffer.from(JSON.stringify(payload));
  if (body.length > MAX_REQUEST_BYTES) {
    throw new Error('The generated npm advisory request exceeded the safety limit.');
  }
  return { payload, body, packageCount: versionsByName.size };
}

function validateAdvisories(response, requestedNames) {
  if (!response || typeof response !== 'object' || Array.isArray(response)) {
    throw new Error('npm registry bulk advisory response must be a JSON object.');
  }

  const advisories = [];
  for (const [packageName, items] of Object.entries(response)) {
    if (!requestedNames.has(packageName)) {
      throw new Error('npm registry returned advisories for a package not submitted for audit: ' + packageName);
    }
    if (!Array.isArray(items)) {
      throw new Error('npm registry returned an invalid advisory list for package: ' + packageName);
    }
    for (const item of items) {
      if (!item || typeof item !== 'object' || Array.isArray(item)
        || typeof item.title !== 'string' || !item.title.trim()
        || typeof item.url !== 'string'
        || !SEVERITIES.includes(String(item.severity || '').toLowerCase())) {
        throw new Error('npm registry returned an advisory with a missing or unknown severity/schema for package: ' + packageName);
      }
      let advisoryUrl;
      try { advisoryUrl = new URL(item.url); } catch (_) {
        throw new Error('npm registry returned an invalid advisory URL for package: ' + packageName);
      }
      if (advisoryUrl.protocol !== 'https:' || advisoryUrl.username || advisoryUrl.password) {
        throw new Error('npm registry returned a non-HTTPS advisory URL for package: ' + packageName);
      }
      advisories.push({
        packageName,
        title: item.title.trim(),
        url: advisoryUrl.toString(),
        severity: String(item.severity).toLowerCase(),
        id: item.id == null ? null : item.id,
      });
    }
  }
  return advisories;
}

function auditFailureMessage(advisories) {
  const blockers = advisories.filter(item => SEVERITY_WEIGHT[item.severity] >= SEVERITY_WEIGHT.high);
  if (!blockers.length) return null;
  const lines = blockers
    .sort((a, b) => SEVERITY_WEIGHT[b.severity] - SEVERITY_WEIGHT[a.severity]
      || a.packageName.localeCompare(b.packageName)
      || a.title.localeCompare(b.title))
    .map(item => '  - [' + item.severity.toUpperCase() + '] ' + item.packageName + ': ' + item.title + ' (' + item.url + ')');
  return 'Dependency audit failed: found ' + blockers.length + ' high/critical advisory finding(s):\n' + lines.join('\n');
}

function requestBulkAdvisories(body) {
  return new Promise((resolve, reject) => {
    const request = https.request({
      hostname: REGISTRY_HOST,
      port: 443,
      method: 'POST',
      path: BULK_PATH,
      headers: {
        host: REGISTRY_HOST,
        'content-type': 'application/json',
        accept: 'application/json',
        // The registry edge may ignore this and gzip the response without headers.
        'accept-encoding': 'identity',
        'content-length': String(body.length),
        'user-agent': 'Garh-Jaisal-Website-Dependency-Audit/1.0',
      },
    }, response => {
      const chunks = [];
      let total = 0;
      let overLimit = false;
      response.on('data', chunk => {
        if (overLimit) return;
        total += chunk.length;
        if (total > MAX_RESPONSE_BYTES) {
          overLimit = true;
          response.destroy(new Error('npm advisory response exceeded the compressed-response safety limit.'));
          return;
        }
        chunks.push(chunk);
      });
      response.on('end', () => {
        if (overLimit) return;
        const raw = Buffer.concat(chunks);
        const status = response.statusCode || 0;
        if (status !== 200) {
          reject(new Error('npm registry bulk advisory endpoint returned HTTP ' + status
            + (raw.length ? ': ' + raw.toString('utf8', 0, Math.min(raw.length, 500)) : '')));
          return;
        }
        try {
          const decoded = decodeJsonBody(raw);
          process.stdout.write('Registry response: HTTP 200, ' + raw.length + ' wire byte(s)'
            + (decoded.wasGzip ? ', gzip-normalized' : '') + ', ' + decoded.byteLength + ' decoded byte(s).\n');
          resolve(decoded.parsed);
        } catch (error) {
          reject(error);
        }
      });
    });

    request.setTimeout(60_000, () => request.destroy(new Error('npm registry bulk advisory request timed out.')));
    request.on('error', reject);
    request.end(body);
  });
}

async function runAudit() {
  let lockfile;
  try { lockfile = JSON.parse(fs.readFileSync(LOCKFILE, 'utf8')); }
  catch (error) { throw new Error('Could not read a valid package-lock.json: ' + error.message); }

  const request = buildAdvisoryPayload(lockfile);
  process.stdout.write('Auditing ' + request.packageCount + ' package name(s) from package-lock.json against npm Bulk Advisory API.\n');
  const response = await requestBulkAdvisories(request.body);
  const advisories = validateAdvisories(response, new Set(Object.keys(request.payload)));
  const bySeverity = Object.fromEntries(SEVERITIES.map(level => [level, advisories.filter(item => item.severity === level).length]));
  process.stdout.write('Advisory counts: ' + SEVERITIES.map(level => level + '=' + bySeverity[level]).join(', ') + '.\n');

  const failure = auditFailureMessage(advisories);
  if (failure) throw new Error(failure);
  process.stdout.write('Dependency audit passed: no high or critical advisories were returned for the locked package versions.\n');
}

function runSelfTest() {
  const sampleLock = {
    lockfileVersion: 3,
    packages: {
      '': { name: 'audit-fixture', version: '1.0.0' },
      'node_modules/@scope/example': { version: '1.2.3' },
      'node_modules/example': { name: 'example', version: '2.0.0' },
      'node_modules/example/node_modules/nested': { name: 'nested', version: '3.0.0' },
      'node_modules/workspace-link': { link: true },
      'node_modules/no-version': { name: 'no-version' },
    },
  };
  const built = buildAdvisoryPayload(sampleLock);
  assert.equal(built.packageCount, 3);
  assert.deepEqual(built.payload['@scope/example'], ['1.2.3']);
  assert.deepEqual(built.payload.example, ['2.0.0']);
  assert.deepEqual(built.payload.nested, ['3.0.0']);

  assert.deepEqual(validateAdvisories({}, new Set(Object.keys(built.payload))), []);
  assert.throws(() => buildAdvisoryPayload({ lockfileVersion: 3, packages: {} }), /No versioned dependencies/);
  assert.throws(() => validateAdvisories([], new Set()), /must be a JSON object/);
  assert.throws(() => validateAdvisories({ example: [{}] }, new Set(['example'])), /missing or unknown severity\/schema/);
  assert.throws(() => validateAdvisories({ unrequested: [] }, new Set(['example'])), /not submitted for audit/);

  const gzipResponse = gzipSync(Buffer.from('{}'));
  const decoded = decodeJsonBody(gzipResponse);
  assert.equal(decoded.wasGzip, true);
  assert.deepEqual(decoded.parsed, {});
  const knownHigh = validateAdvisories({
    example: [{
      id: 12345,
      title: 'Synthetic test high-severity advisory',
      url: 'https://github.com/advisories/GHSA-xxxx-xxxx-xxxx',
      severity: 'high',
    }],
  }, new Set(['example']));
  assert.match(auditFailureMessage(knownHigh), /high\/critical advisory/);
  assert.equal(auditFailureMessage(validateAdvisories({
    example: [{
      id: 12346,
      title: 'Synthetic moderate advisory',
      url: 'https://github.com/advisories/GHSA-yyyy-yyyy-yyyy',
      severity: 'moderate',
    }],
  }, new Set(['example']))), null);
  assert.throws(() => decodeJsonBody(Buffer.from('not json')), /not valid JSON/);
  process.stdout.write('npm Bulk Advisory auditor self-test passed.\n');
}

if (require.main === module) {
  const mode = process.argv[2];
  if (mode === '--self-test') {
    try { runSelfTest(); } catch (error) {
      process.stderr.write(error.stack + '\n');
      process.exitCode = 1;
    }
  } else if (mode === '--audit') {
    runAudit().catch(error => {
      process.stderr.write(error.message + '\n');
      process.exitCode = 1;
    });
  } else {
    process.stderr.write('Usage: node scripts/npm-bulk-advisory-audit.cjs --self-test|--audit\n');
    process.exitCode = 2;
  }
}

module.exports = {
  hasGzipSignature,
  decodeJsonBody,
  packageNameFromLocation,
  buildAdvisoryPayload,
  validateAdvisories,
  auditFailureMessage,
};
