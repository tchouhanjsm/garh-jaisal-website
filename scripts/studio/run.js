'use strict';
// A run is a saved state file: one step at a time, an exit test per step, a one-line result each.
const fs = require('node:fs');
const path = require('node:path');
const L = require('./lib');

const STATES = ['INTAKE', 'PLAN', 'BUILD', 'APPROVE', 'PACK', 'APPLY', 'VERIFY', 'RELEASE', 'CONFIRM'];
const runFile = id => path.join(L.repoRoot(), 'content/_runs', id + '.json');

function newRun({ type, slug, author, approver }) {
  const id = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 12) + '-' + slug;
  const run = { id, type, slug, author, approver, state: 'INTAKE', history: [] };
  L.atomicWrite(runFile(id), JSON.stringify(run, null, 2) + '\n');
  return run;
}
function readRun(id) { return JSON.parse(fs.readFileSync(runFile(id), 'utf8')); }

// Records the result of the active step; advances only on pass. Approval needs approver != author.
function advance(id, { pass, evidence, by }) {
  const run = readRun(id);
  if (run.state === 'DONE') throw new Error('Run is already complete.');
  if (run.state === 'APPROVE' && pass && (!by || by !== run.approver || by === run.author)) throw new Error('APPROVE must be signed by the approver, who cannot be the author.');
  run.history.push({ time: new Date().toISOString(), step: run.state, result: pass ? 'pass' : 'fail', evidence: evidence || '', by: by || '' });
  if (pass) { const i = STATES.indexOf(run.state); run.state = i === STATES.length - 1 ? 'DONE' : STATES[i + 1]; }
  L.atomicWrite(runFile(id), JSON.stringify(run, null, 2) + '\n');
  return run;
}

module.exports = { STATES, newRun, readRun, advance };
