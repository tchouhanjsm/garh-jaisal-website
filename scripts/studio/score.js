'use strict';
// Page score: plain-language SEO, readability and house-style checks. Deterministic, offline, no dependencies.
// Style rules mirror docs/BRAIN.md (no invented claims, no hype). Optional external tools (Vale, Lighthouse) can run on top.
const STOP = new Set('a an and are as at be by for from has have in is it its of on or that the this to was were will with your you our we near about into than then them they their there these those which while who how what when where why can could should would may might not no yes more most very just also all any each both such own same so too out up down over under again further once here only'.split(' '));
const HYPE = ['best', 'cheapest', 'perfect', 'amazing', 'world-class', 'world class', 'must-see', 'must see', 'guaranteed', 'unforgettable', 'once-in-a-lifetime', 'once in a lifetime', 'breathtaking', 'magical', 'ultimate', 'number one', '#1', 'unbeatable', 'incredible'];

const words = t => (String(t).toLowerCase().match(/[a-z][a-z'-]*/g) || []);
function syllables(w) { w = w.replace(/e$/, ''); const m = w.match(/[aeiouy]+/g); return Math.max(1, m ? m.length : 1); }
function readingEase(text) {
  const sentences = (String(text).match(/[^.!?]+[.!?]*/g) || []).filter(s => words(s).length > 0);
  const ws = words(text);
  if (!sentences.length || !ws.length) return { ease: null, avgSentence: 0, words: 0 };
  const syl = ws.reduce((n, w) => n + syllables(w), 0);
  const ease = 206.835 - 1.015 * (ws.length / sentences.length) - 84.6 * (syl / ws.length);
  return { ease: Math.round(ease), avgSentence: Math.round(ws.length / sentences.length), words: ws.length };
}

function bodyText(type, r) {
  const parts = [r.summary, r.lede, r.best_for, r.description];
  if (type === 'tour') { (r.timeline || []).forEach(s => parts.push(s.text)); (r.faq || []).forEach(f => parts.push(f.answer)); }
  if (type === 'blog') (r.sections || []).forEach(s => (s.paragraphs || []).forEach(p => parts.push(p)));
  return parts.filter(Boolean).join(' ');
}

function scoreRecord(type, r) {
  const checks = [];
  const add = (id, label, status, msg) => checks.push({ id, label, status, msg });
  const title = String(r.title || ''), desc = String(r.description || '');
  const L = title.length, D = desc.length;
  add('title', 'Page title', L >= 25 && L <= 60 ? 'green' : L >= 8 && L <= 60 ? 'amber' : 'red', L >= 25 && L <= 60 ? 'Good length for search results.' : L > 60 ? 'Too long: search results will cut it off.' : 'A little short: add what makes it specific (place, duration).');
  add('description', 'Search description', D >= 110 && D <= 160 ? 'green' : 'amber', D >= 110 && D <= 160 ? 'Good length for search results.' : D < 110 ? 'Too short to be useful in search results.' : 'Too long: it will be cut off.');
  const key = [...new Set(words(r.h1 || title).filter(w => !STOP.has(w) && w.length > 2))];
  const inDesc = key.filter(w => desc.toLowerCase().includes(w));
  add('keywords', 'Title words in the description', key.length && inDesc.length >= Math.ceil(key.length / 2) ? 'green' : 'amber', key.length && inDesc.length >= Math.ceil(key.length / 2) ? 'The description repeats the main words people will search.' : 'Use the main words from the title in the description.');
  const text = bodyText(type, r);
  const rd = readingEase(text);
  if (rd.ease !== null) {
    add('readability', 'Easy to read', rd.ease >= 55 && rd.avgSentence <= 24 ? 'green' : rd.ease >= 40 ? 'amber' : 'red', rd.ease >= 55 && rd.avgSentence <= 24 ? 'Short, clear sentences.' : 'Shorten the sentences and prefer simple words (reading ease ' + rd.ease + ', average ' + rd.avgSentence + ' words per sentence).');
  }
  const wc = rd.words;
  if (type === 'blog') add('depth', 'Enough to read', wc >= 300 ? 'green' : wc >= 150 ? 'amber' : 'red', wc >= 300 ? 'Good length for a guide.' : 'Short for a guide (' + wc + ' words). Add sections with real details.');
  if (type === 'tour') {
    const steps = (r.timeline || []).length;
    add('depth', 'Itinerary detail', steps >= 3 ? 'green' : steps >= 1 ? 'amber' : 'red', steps >= 3 ? 'Clear step-by-step plan.' : steps ? 'Add more steps so guests know what happens.' : 'No itinerary yet: guests need to know what happens.');
    add('inclusions', 'Inclusions listed', (r.included || []).length ? 'green' : 'amber', (r.included || []).length ? 'Guests can see what is included.' : 'List what is included before going Live.');
  }
  const imgs = Array.isArray(r.images) ? r.images : [];
  const noAlt = imgs.filter(i => String(i.alt || '').length < 10).length;
  add('photos', 'Photos and descriptions', imgs.length && !noAlt ? 'green' : imgs.length ? 'amber' : 'red', imgs.length && !noAlt ? imgs.length + ' photo(s), each described for screen readers.' : imgs.length ? 'Some photos have no useful description (alt text).' : 'No photos yet.');
  const lower = text.toLowerCase();
  const hype = HYPE.filter(h => lower.includes(h));
  const shouty = (text.match(/\b[A-Z]{4,}\b/g) || []).length;
  const bangs = (text.match(/!/g) || []).length;
  add('tone', 'Honest, calm tone', !hype.length && !bangs && shouty < 2 ? 'green' : 'amber', !hype.length && !bangs && shouty < 2 ? 'No hype or shouting.' : 'Remove hype' + (hype.length ? ' ("' + hype.slice(0, 3).join('", "') + '")' : '') + (bangs ? ' and exclamation marks' : '') + ': guests trust plain facts.');
  const money = /(?:rs\.?|inr|₹|\$|€|£)\s?\d|\d+\s?(?:rupees|usd|eur)/i.test(text);
  if (money) add('price', 'Prices', r.facts_confirmed ? 'green' : 'amber', r.facts_confirmed ? 'Prices are marked as confirmed.' : 'A price appears but facts are not confirmed yet: confirm it with the hotel first.');
  if (type === 'tour') add('facts', 'Facts confirmed by the owner', r.facts_confirmed ? 'green' : 'amber', r.facts_confirmed ? 'Confirmed' + (r.last_verified ? ' on ' + r.last_verified : '') + '.' : 'Needed before this can go Live (prices, timings, inclusions, pickup).');
  const weight = { green: 1, amber: 0.5, red: 0 };
  const score = Math.round(100 * checks.reduce((n, c) => n + weight[c.status], 0) / checks.length);
  const anyRed = checks.some(c => c.status === 'red');
  return { score, grade: anyRed || score < 60 ? 'red' : score >= 85 ? 'green' : 'amber', checks, words: wc };
}

// Rule ids to plain sentences for non-technical readers.
const PLAIN = { 'RC-01': 'A required detail is missing.', 'RC-05': 'The page title is the wrong length for search results.', 'RC-06': 'The search description is the wrong length.', 'RC-07': 'A photo needs a short description of what it shows.', 'RC-09': 'Add the steps of the tour so guests know what happens.', 'RC-10': 'List what is included.', 'RC-11': 'Add at least one section of text.', 'RC-12': 'Say where the facts come from (sources).', 'RC-19': 'The owner must confirm the facts before this can go Live.', 'RC-21': 'Something that looks like a password or key is in the text; remove it.', 'RC-22': 'The text contains instruction-like or script content; remove it.', 'RC-23': 'Phone numbers and emails should not be in the text; guests use the contact page.' };
const plain = f => PLAIN[f.rule] || f.message;

module.exports = { scoreRecord, readingEase, plain, HYPE };
