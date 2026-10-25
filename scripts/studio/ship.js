'use strict';
// ship: after approval and verification, put exactly the pack's files on a new branch, push it and open a pull request.
// Never merges, never touches other files, never uses hosting credentials. Uses git and (if installed) the gh CLI.
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const L = require('./lib');
const inbox = require('./inbox');
const run = require('./run');

const git = (root, args, input) => spawnSync('git', ['-C', root, ...args], { encoding: 'utf8', input });

function ship(id, { remote = 'origin', base = 'main' } = {}) {
  const root = L.repoRoot();
  const file = path.join(inbox.inboxRoot(), id, 'approved.json');
  if (!fs.existsSync(file)) throw new Error('This batch has not been approved yet.');
  const a = JSON.parse(fs.readFileSync(file, 'utf8'));
  const state = run.readRun(a.run);
  if (state.state !== 'RELEASE') throw new Error('The run is at ' + state.state + '; it must pass verification (RELEASE) before shipping.');
  if (a.files.some(f => !L.safeRelative(f))) throw new Error('Unsafe file path in the approved list.');
  const current = git(root, ['rev-parse', '--abbrev-ref', 'HEAD']).stdout.trim();
  if (!current) throw new Error('This folder is not a git checkout.');
  let branch = 'content/' + a.slug + '-' + new Date().toISOString().slice(0, 10);
  for (let n = 2; git(root, ['rev-parse', '--verify', '--quiet', branch]).status === 0; n++) branch = branch.replace(/-\d+$/, '') + '-' + n;
  if (git(root, ['checkout', '-q', '-b', branch]).status !== 0) throw new Error('Could not create the branch ' + branch + '.');
  let result;
  try {
    if (git(root, ['add', '--', ...a.files]).status !== 0) throw new Error('git add failed.');
    const msg = 'content: add ' + a.type + ' "' + a.slug + '" as draft\n\nBuilt by Content Studio Quick add. Pack: ' + a.zip + '. Approved by ' + a.by + '. Draft only; facts must be confirmed before Live.';
    const c = git(root, ['commit', '-q', '-m', msg, '--', ...a.files]);
    if (c.status !== 0) throw new Error('Nothing to commit or commit failed: ' + String(c.stderr || c.stdout).trim());
    const p = git(root, ['push', '-q', '-u', remote, branch]);
    if (p.status !== 0) throw new Error('Push failed: ' + String(p.stderr).trim().split('\n').pop());
    let pr = null;
    if (spawnSync('gh', ['--version'], { stdio: 'ignore' }).status === 0) {
      const g = spawnSync('gh', ['pr', 'create', '--base', base, '--head', branch, '--title', 'content: ' + a.type + ' ' + a.slug + ' (draft)', '--body', 'Draft content built by Content Studio Quick add. Approved by ' + a.by + '. Files: ' + a.files.join(', ') + '. Facts must be confirmed before Live.'], { cwd: root, encoding: 'utf8', env: { ...process.env, GH_PROMPT_DISABLED: '1' } });
      pr = g.status === 0 ? String(g.stdout).trim().split('\n').pop() : null;
    }
    result = { ok: true, branch, pr, files: a.files, next: pr ? 'Review and merge: ' + pr : 'Saved and sent. Ask the owner to open GitHub and review branch ' + branch + '.' };
  } finally { git(root, ['checkout', '-q', current]); }
  run.advance(a.run, { pass: true, evidence: 'shipped on ' + branch + (result.pr ? ' ' + result.pr : '') });
  return result;
}

module.exports = { ship };
