#!/usr/bin/env node
'use strict';
// Cross-platform launcher (macOS, Windows, Linux): starts the Studio and the PHP preview. Run: npm run studio:start
const path = require('node:path');
const { spawn, spawnSync } = require('node:child_process');

const ROOT = path.resolve(__dirname, '../..');
const PHP = process.env.PHP_BIN || 'php';
const studioPort = process.env.CONTENT_STUDIO_PORT || '4173';
const previewPort = process.env.CONTENT_PREVIEW_PORT || '8088';

if (spawnSync(PHP, ['--version'], { stdio: 'ignore' }).status !== 0) { console.error('PHP was not found. Install PHP or set PHP_BIN to its full path, then run again.'); process.exit(1); }
const children = [
  spawn(PHP, ['-S', '127.0.0.1:' + previewPort, '-t', ROOT], { stdio: 'inherit' }),
  spawn(process.execPath, [path.join(ROOT, 'scripts/content-studio.js')], { stdio: 'inherit', env: { ...process.env, CONTENT_STUDIO_PORT: studioPort } }),
];
console.log('Content Studio: http://127.0.0.1:' + studioPort + '\nPreview:        http://127.0.0.1:' + previewPort + '/blog.php\nPress Ctrl+C to stop both.');
const stop = () => { children.forEach(c => { try { c.kill(); } catch (_) {} }); process.exit(0); };
process.on('SIGINT', stop); process.on('SIGTERM', stop);
children.forEach(c => c.on('exit', code => { if (code) { console.error('A server stopped (exit ' + code + ').'); stop(); } }));
