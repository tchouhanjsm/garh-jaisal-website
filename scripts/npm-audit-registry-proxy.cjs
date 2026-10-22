'use strict';

// CI-only compatibility proxy for the npm registry's bulk advisory endpoint.
// Some registry edges have returned gzip bytes without a Content-Encoding header.
// npm then falls back to the retired /security/audits/quick endpoint and fails.
// This proxy forwards only the bulk-audit POST to the fixed HTTPS npm registry,
// normalizes mislabeled gzip responses, and never forwards auth/cookie headers.

const assert = require('node:assert/strict');
const http = require('node:http');
const https = require('node:https');
const { gunzipSync, gzipSync } = require('node:zlib');
const { spawn } = require('node:child_process');

const HOST = '127.0.0.1';
const PORT = 8099;
const BULK_PATH = '/-/npm/v1/security/advisories/bulk';
const MAX_REQUEST_BYTES = 2 * 1024 * 1024;
const MAX_RESPONSE_BYTES = 10 * 1024 * 1024;

function hasGzipSignature(buffer) {
  return Buffer.isBuffer(buffer) && buffer.length >= 2
    && buffer[0] === 0x1f && buffer[1] === 0x8b;
}

function decodeAuditRequestBody(rawBody) {
  const body = Buffer.isBuffer(rawBody) ? rawBody : Buffer.from(rawBody || '');
  if (!hasGzipSignature(body)) return body;
  return gunzipSync(body, { maxOutputLength: MAX_REQUEST_BYTES });
}

function normalizeAdvisoryResponse(statusCode, upstreamHeaders, rawBody) {
  let body = Buffer.isBuffer(rawBody) ? rawBody : Buffer.from(rawBody || '');
  const headers = { ...(upstreamHeaders || {}) };
  const encoded = String(headers['content-encoding'] || '').toLowerCase();
  const mislabeledGzip = !encoded && hasGzipSignature(body);
  const gzipBody = hasGzipSignature(body);

  if (gzipBody) {
    body = gunzipSync(body);
    delete headers['content-encoding'];
    // Entity validators and lengths apply to the compressed payload, not this body.
    delete headers['content-length'];
    delete headers['content-md5'];
    delete headers['content-digest'];
    delete headers.digest;
    delete headers.etag;
    delete headers['transfer-encoding'];
    delete headers['accept-ranges'];
  }

  // Hop-by-hop headers must not cross the local proxy boundary.
  delete headers.connection;
  delete headers['keep-alive'];
  delete headers['proxy-authenticate'];
  delete headers['proxy-authorization'];
  delete headers.te;
  delete headers.trailer;
  delete headers.upgrade;

  headers['content-type'] = headers['content-type'] || 'application/json; charset=utf-8';
  headers['cache-control'] = 'no-store';
  headers['content-length'] = String(body.length);

  return {
    statusCode,
    headers,
    body,
    normalizedGzip: gzipBody,
    correctedMissingEncoding: mislabeledGzip,
  };
}

function isAllowedRequest(method, requestUrl) {
  if (method !== 'POST' || typeof requestUrl !== 'string') return false;
  try {
    const parsed = new URL(requestUrl, 'http://' + HOST + ':' + PORT);
    const pathname = parsed.pathname.endsWith('/') ? parsed.pathname.slice(0, -1) : parsed.pathname;
    return pathname === BULK_PATH && !parsed.search && !parsed.hash;
  } catch (_) {
    return false;
  }
}

function jsonResponse(response, statusCode, message) {
  const body = Buffer.from(JSON.stringify({ error: message }) + '\n');
  response.writeHead(statusCode, {
    'content-type': 'application/json; charset=utf-8',
    'content-length': String(body.length),
    'cache-control': 'no-store',
  });
  response.end(body);
}

function createProxyServer() {
  return http.createServer((request, response) => {
    process.stderr.write('[npm audit proxy] Incoming ' + request.method + ' ' + request.url + '\\n');
    if (request.method === 'GET' && request.url === '/healthz') {
      const body = Buffer.from('ok\n');
      response.writeHead(200, { 'content-type': 'text/plain; charset=utf-8', 'content-length': String(body.length) });
      response.end(body);
      return;
    }

    if (!isAllowedRequest(request.method, request.url)) {
      process.stderr.write('[npm audit proxy] Rejected route: method=' + request.method + ', path=' + request.url + '\\n');
      jsonResponse(response, 404, 'Only the npm bulk-advisory POST route is supported by this local CI proxy.');
      return;
    }

    const contentType = String(request.headers['content-type'] || '').toLowerCase();
    if (!contentType.includes('application/json')) {
      process.stderr.write('[npm audit proxy] Rejected bulk request content type: ' + (contentType || '<missing>') + '\\n');
      jsonResponse(response, 415, 'The npm bulk-advisory request must be JSON.');
      return;
    }

    const chunks = [];
    let requestBytes = 0;
    let finished = false;
    request.on('data', chunk => {
      if (finished) return;
      requestBytes += chunk.length;
      if (requestBytes > MAX_REQUEST_BYTES) {
        finished = true;
        jsonResponse(response, 413, 'The npm bulk-advisory request exceeded the local safety limit.');
        request.destroy();
        return;
      }
      chunks.push(chunk);
    });

    request.on('error', () => {
      if (!finished && !response.headersSent) jsonResponse(response, 400, 'Could not read the npm bulk-advisory request.');
    });

    request.on('end', () => {
      if (finished || response.writableEnded) return;
      let body = Buffer.concat(chunks);
      const compressedRequest = hasGzipSignature(body);
      try {
        body = decodeAuditRequestBody(body);
        if (body.length > MAX_REQUEST_BYTES) throw new Error('Decoded request exceeded the local safety limit.');
        if (compressedRequest) {
          process.stderr.write('[npm audit proxy] Decompressed gzip-encoded bulk request body; bytes=' + body.length + '\\n');
        }
        const payload = JSON.parse(body.toString('utf8'));
        if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
          throw new Error('Expected an object');
        }
      } catch (error) {
        process.stderr.write('[npm audit proxy] Invalid bulk request body: ' + error.message + '\\n');
        jsonResponse(response, 400, 'The npm bulk-advisory request was not a valid bounded JSON object.');
        return;
      }

      const upstream = https.request({
        hostname: 'registry.npmjs.org',
        port: 443,
        method: 'POST',
        path: BULK_PATH,
        headers: {
          host: 'registry.npmjs.org',
          'content-type': 'application/json',
          accept: 'application/json',
          // Request identity encoding; still normalize if the edge ignores it.
          'accept-encoding': 'identity',
          'content-length': String(body.length),
          'user-agent': 'Garh-Jaisal-Website-CI-Audit-Proxy/1.0',
        },
      }, upstreamResponse => {
        const responseChunks = [];
        let responseBytes = 0;
        let overflow = false;
        upstreamResponse.on('data', chunk => {
          if (overflow) return;
          responseBytes += chunk.length;
          if (responseBytes > MAX_RESPONSE_BYTES) {
            overflow = true;
            upstreamResponse.destroy(new Error('npm advisory response exceeded the local safety limit'));
            return;
          }
          responseChunks.push(chunk);
        });
        upstreamResponse.on('end', () => {
          if (overflow || response.writableEnded) return;
          try {
            const rawBody = Buffer.concat(responseChunks);
            process.stderr.write(
              '[npm audit proxy] Upstream bulk response: status=' + String(upstreamResponse.statusCode || 502)
              + ', bytes=' + String(rawBody.length)
              + ', content-encoding=' + String(upstreamResponse.headers['content-encoding'] || '<missing>')
              + ', content-type=' + String(upstreamResponse.headers['content-type'] || '<missing>')
              + ', gzip-magic=' + String(hasGzipSignature(rawBody)) + '\n',
            );
            const normalized = normalizeAdvisoryResponse(
              upstreamResponse.statusCode || 502,
              upstreamResponse.headers,
              rawBody,
            );
            if (normalized.correctedMissingEncoding) {
              process.stderr.write('[npm audit proxy] Corrected an npm registry gzip response missing Content-Encoding.\n');
            }
            response.writeHead(normalized.statusCode, normalized.headers);
            response.end(normalized.body);
          } catch (error) {
            process.stderr.write('[npm audit proxy] Could not decode the advisory response: ' + error.message + '\n');
            jsonResponse(response, 502, 'The npm registry returned an invalid bulk-advisory response.');
          }
        });
      });

      upstream.setTimeout(60_000, () => upstream.destroy(new Error('npm advisory request timed out')));
      upstream.on('error', error => {
        process.stderr.write('[npm audit proxy] Upstream request failed: ' + error.message + '\n');
        if (!response.headersSent && !response.writableEnded) {
          jsonResponse(response, 502, 'The npm registry bulk-advisory endpoint could not be reached.');
        }
      });
      upstream.end(body);
    });
  });
}

function runSelfTest() {
  const json = Buffer.from(JSON.stringify({ '@playwright/test': [], playwright: [], 'playwright-core': [] }));
  const compressed = gzipSync(json);
  const normalized = normalizeAdvisoryResponse(200, {
    'content-type': 'application/json',
    'content-length': String(compressed.length),
  }, compressed);

  assert.equal(normalized.statusCode, 200);
  assert.deepEqual(normalized.body, json);
  assert.equal(normalized.headers['content-encoding'], undefined);
  assert.equal(normalized.headers['content-length'], String(json.length));
  assert.equal(normalized.correctedMissingEncoding, true);
  assert.equal(isAllowedRequest('POST', BULK_PATH), true);
  assert.equal(isAllowedRequest('POST', BULK_PATH + '/'), true);
  assert.equal(isAllowedRequest('GET', BULK_PATH), false);
  const compressedRequest = gzipSync(Buffer.from('{"@playwright/test":["1.64.1"]}'));
  assert.equal(decodeAuditRequestBody(compressedRequest).toString('utf8'), '{"@playwright/test":["1.64.1"]}');
  assert.equal(decodeAuditRequestBody(Buffer.from('{"playwright":["1.64.1"]}')).toString('utf8'), '{"playwright":["1.64.1"]}');
  assert.equal(isAllowedRequest('POST', '/-/npm/v1/security/audits/quick'), false);
  assert.equal(isAllowedRequest('POST', 'https://example.invalid/'), false);
  process.stdout.write('npm audit registry proxy self-test passed.\n');
}

async function runAudit() {
  const server = createProxyServer();
  await new Promise((resolve, reject) => {
    server.once('error', reject);
    server.listen(PORT, HOST, resolve);
  });

  process.stdout.write('Checking dependency advisories through a loopback-only npm registry compatibility proxy.\n');
  const child = spawn('npm', [
    'audit',
    '--audit-level=high',
    '--registry=http://' + HOST + ':' + PORT,
  ], { stdio: 'inherit' });

  let finished = false;
  const finish = async code => {
    if (finished) return;
    finished = true;
    await new Promise(resolve => server.close(resolve));
    process.exitCode = Number.isInteger(code) ? code : 1;
  };

  child.once('error', error => {
    process.stderr.write('Could not launch npm audit: ' + error.message + '\n');
    finish(1);
  });
  child.once('exit', (code, signal) => {
    if (signal) process.stderr.write('npm audit ended from signal ' + signal + '.\n');
    finish(code);
  });
}

if (require.main === module) {
  const mode = process.argv[2];
  if (mode === '--self-test') runSelfTest();
  else if (mode === '--audit') runAudit().catch(error => {
    process.stderr.write('npm audit proxy could not start: ' + error.message + '\n');
    process.exitCode = 1;
  });
  else {
    process.stderr.write('Usage: node scripts/npm-audit-registry-proxy.cjs --self-test|--audit\n');
    process.exitCode = 2;
  }
}

module.exports = { hasGzipSignature, decodeAuditRequestBody, normalizeAdvisoryResponse, isAllowedRequest };
