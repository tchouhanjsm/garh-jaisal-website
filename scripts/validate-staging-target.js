#!/usr/bin/env node
'use strict';

const { isIP } = require('node:net');

const PRODUCTION_HOSTS = new Set([
  'hotelgarhjaisal.com',
  'www.hotelgarhjaisal.com',
]);
const FULL_SHA_PATTERN = /^[0-9a-f]{40}$/i;

function validateStagingTarget({ stagingUrl, expectedCommitSha, checkedOutCommitSha, githubRef }) {
  if (githubRef !== 'refs/heads/main') {
    throw new Error('External staging QA may run only from refs/heads/main.');
  }
  if (typeof stagingUrl !== 'string' || stagingUrl.trim() === '') {
    throw new Error('STAGING_BASE_URL is required for an external staging run.');
  }

  let target;
  try {
    target = new URL(stagingUrl.trim());
  } catch {
    throw new Error('Staging URL must be a valid absolute HTTPS origin.');
  }
  if (target.protocol !== 'https:') {
    throw new Error('Staging URL must use HTTPS.');
  }
  if (PRODUCTION_HOSTS.has(target.hostname.toLowerCase())) {
    throw new Error('Production hostnames are forbidden as staging targets.');
  }
  const unbracketedHost = target.hostname.replace(/^\[|\]$/g, '');
  if (
    target.hostname === 'localhost'
    || target.hostname.endsWith('.localhost')
    || isIP(unbracketedHost) !== 0
    || !target.hostname.includes('.')
  ) {
    throw new Error('Staging URL must use a real HTTPS hostname, not localhost or an IP address.');
  }
  if (target.username || target.password) {
    throw new Error('Credentials must not be embedded in the staging URL.');
  }
  if (target.pathname !== '/' || target.search || target.hash) {
    throw new Error('Enter only the staging origin (scheme and hostname), without a path, query or fragment.');
  }

  if (!FULL_SHA_PATTERN.test(String(expectedCommitSha || ''))) {
    throw new Error('The staged package commit must be a full 40-character Git SHA.');
  }
  if (!FULL_SHA_PATTERN.test(String(checkedOutCommitSha || ''))) {
    throw new Error('The workflow checkout commit is not a full Git SHA.');
  }
  if (expectedCommitSha.toLowerCase() !== checkedOutCommitSha.toLowerCase()) {
    throw new Error('Staged package SHA must match the exact commit checked out by this main-branch workflow.');
  }

  return {
    status: 'PASS',
    stagingOrigin: target.origin,
    stagedPackageCommit: expectedCommitSha.toLowerCase(),
    checkedOutCommit: checkedOutCommitSha.toLowerCase(),
    deploymentPerformed: false,
  };
}

if (require.main === module) {
  try {
    const result = validateStagingTarget({
      stagingUrl: process.env.STAGING_BASE_URL,
      expectedCommitSha: process.env.STAGING_EXPECTED_COMMIT_SHA,
      checkedOutCommitSha: process.env.CHECKED_OUT_COMMIT_SHA,
      githubRef: process.env.GITHUB_REF,
    });
    process.stdout.write(JSON.stringify(result, null, 2) + '\n');
  } catch (error) {
    process.stderr.write('STAGING TARGET REJECTED: ' + error.message + '\n');
    process.exitCode = 1;
  }
}

module.exports = { validateStagingTarget };
