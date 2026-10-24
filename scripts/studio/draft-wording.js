'use strict';
// Optional local drafting helper (Ollama on this machine). Off unless a local Ollama answers on loopback.
// It may only reword facts that are in the notes: output is rejected if it adds numbers, prices, links or hype.
const { HYPE } = require('./score');

const DEFAULT_HOST = process.env.OLLAMA_HOST || 'http://127.0.0.1:11434';
function loopback(host) { try { const u = new URL(host); return ['127.0.0.1', 'localhost', '[::1]', '::1'].includes(u.hostname); } catch (_) { return false; } }

async function aiStatus(host = DEFAULT_HOST) {
  if (!loopback(host)) return { available: false, reason: 'Only a local (loopback) Ollama is allowed.' };
  try {
    const r = await fetch(host.replace(/\/$/, '') + '/api/tags', { signal: AbortSignal.timeout(1500) });
    if (!r.ok) return { available: false, reason: 'Ollama did not answer.' };
    const d = await r.json();
    return { available: Array.isArray(d.models) && d.models.length > 0, models: (d.models || []).map(m => m.name), reason: d.models && d.models.length ? '' : 'No local model installed (try: ollama pull llama3.2).' };
  } catch (_) { return { available: false, reason: 'Ollama is not running on this computer.' }; }
}

function validate(out, source) {
  const reasons = [];
  const known = new Set((source.match(/\d+/g) || []));
  const clean = (label, text, max) => {
    const t = String(text || '').replace(/\s+/g, ' ').trim();
    if (!t) return '';
    if (t.length > max) reasons.push(label + ' is too long.');
    for (const n of (t.match(/\d+/g) || [])) if (!known.has(n)) { reasons.push(label + ' contains a number (' + n + ') that is not in your notes.'); break; }
    if (/(https?:|www\.|@)/i.test(t)) reasons.push(label + ' contains a link or address.');
    if (/!/.test(t)) reasons.push(label + ' contains an exclamation mark.');
    const hype = HYPE.find(h => t.toLowerCase().includes(h)); if (hype) reasons.push(label + ' contains hype ("' + hype + '").');
    return t;
  };
  const res = { summary: clean('Summary', out.summary, 300), best_for: clean('Best for', out.best_for, 160), faq: [] };
  for (const f of (Array.isArray(out.faq) ? out.faq.slice(0, 3) : [])) { const q = clean('FAQ question', f.question, 140), a = clean('FAQ answer', f.answer, 300); if (q && a) res.faq.push({ question: q, answer: a }); }
  return { ok: reasons.length === 0, suggestions: res, rejected: reasons };
}

async function suggestWording({ type, title, notes, host = DEFAULT_HOST, model }) {
  const status = await aiStatus(host);
  if (!status.available) return { ok: false, rejected: [status.reason || 'Local AI is not available.'] };
  const source = String(title || '') + '\n' + String(notes || '');
  const prompt = 'You write short, calm website copy for a heritage hotel in Jaisalmer. Use ONLY facts that appear in TITLE and NOTES below. If a detail is not there, leave it out. Never write prices, times, distances, numbers, links or superlatives unless they appear in NOTES. No exclamation marks. Reply as JSON: {"summary": string (1-2 sentences), "best_for": string (one short sentence), "faq": [{"question": string, "answer": string}] (0-3 items, only if NOTES answer them)}.\nTYPE: ' + type + '\nTITLE: ' + title + '\nNOTES:\n' + String(notes || '(none)').slice(0, 4000);
  try {
    const r = await fetch(host.replace(/\/$/, '') + '/api/generate', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ model: model || process.env.OLLAMA_MODEL || status.models[0], prompt, stream: false, format: 'json', options: { temperature: 0.2 } }), signal: AbortSignal.timeout(120000) });
    const d = await r.json();
    return validate(JSON.parse(d.response), source);
  } catch (_) { return { ok: false, rejected: ['The local model did not return usable wording.'] }; }
}

module.exports = { aiStatus, suggestWording, validate, loopback };
