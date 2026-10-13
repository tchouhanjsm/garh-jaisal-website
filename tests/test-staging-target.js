const assert = require('node:assert/strict');
const test = require('node:test');
const { validateStagingTarget } = require('../scripts/validate-staging-target');

const sha = 'a'.repeat(40);
const base = {
  stagingUrl: 'https://staging.hotelgarhjaisal.com',
  expectedCommitSha: sha,
  checkedOutCommitSha: sha,
  githubRef: 'refs/heads/main',
};

test('accepts a dedicated HTTPS staging hostname when the package SHA is exact', () => {
  assert.deepEqual(validateStagingTarget(base), {
    status: 'PASS',
    stagingOrigin: 'https://staging.hotelgarhjaisal.com',
    stagedPackageCommit: sha,
    checkedOutCommit: sha,
    deploymentPerformed: false,
  });
});

test('accepts a separately hosted HTTPS staging origin', () => {
  const result = validateStagingTarget({ ...base, stagingUrl: 'https://stage.example.net/' });
  assert.equal(result.stagingOrigin, 'https://stage.example.net');
});

test('rejects both production host aliases', () => {
  for (const stagingUrl of ['https://hotelgarhjaisal.com', 'https://www.hotelgarhjaisal.com']) {
    assert.throws(() => validateStagingTarget({ ...base, stagingUrl }), /Production hostnames/);
  }
});

test('rejects plain HTTP, localhost, and IP-address targets', () => {
  for (const stagingUrl of ['http://staging.example.net', 'https://localhost', 'https://127.0.0.1', 'https://[::1]']) {
    assert.throws(() => validateStagingTarget({ ...base, stagingUrl }));
  }
});

test('rejects path, query, fragment, or embedded credentials', () => {
  for (const stagingUrl of [
    'https://stage.example.net/preview',
    'https://stage.example.net/?token=x',
    'https://stage.example.net/#top',
    'https://user:pass@stage.example.net',
  ]) {
    assert.throws(() => validateStagingTarget({ ...base, stagingUrl }));
  }
});

test('requires the exact staged commit SHA and full SHA format', () => {
  assert.throws(() => validateStagingTarget({ ...base, expectedCommitSha: 'abc123' }), /full 40-character/);
  assert.throws(() => validateStagingTarget({ ...base, checkedOutCommitSha: 'b'.repeat(40) }), /match the exact commit/);
});

test('rejects external staging dispatch outside main', () => {
  assert.throws(() => validateStagingTarget({ ...base, githubRef: 'refs/heads/feature/foo' }), /only from refs\/heads\/main/);
});
