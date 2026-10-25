#!/usr/bin/env node
'use strict';
// Cross-platform launcher (macOS, Windows, Linux): starts the Studio and the PHP preview. Run: npm run studio:start
const path = require('node:path');
const { spawn, spawnSync } = require('node:child_process');

const ROOT = path.resolve(__dirname, '../..');
const PHP = process.env.PHP_BIN || 'php';
const studioPort = process.env.CONTENT_STUDIO_PORT || '4173';
const previewPort = process.env.CONTENT_PREVIEW_PORT || '8088';

if (spawnSync(PHP, ['--version'], { stdio: 'ignore' }).status !== 0) { console.error('PHP is needed for the page preview and was not found. Ask the owner to install PHP, then run this again.'); process.exit(1); }
const children = [
  spawn(PHP, ['-S', '127.0.0.1:' + previewPort, '-t', ROOT], { stdio: 'inherit' }),
  spawn(process.execPath, [path.join(ROOT, 'scripts/content-studio.js')], { stdio: 'inherit', env: { ...process.env, CONTENT_STUDIO_PORT: studioPort } }),
];
console.log('Content Studio: http://127.0.0.1:' + studioPort + '\nPreview:        http://127.0.0.1:' + previewPort + '/blog.php\nPress Ctrl+C to stop both.');
const stop = () => { children.forEach(c => { try { c.kill(); } catch (_) {} }); process.exit(0); };
if (!process.env.NO_BROWSER) { const url = 'http://127.0.0.1:' + studioPort; setTimeout(() => { try { if (process.platform === 'win32') spawn('cmd', ['/c', 'start', '', url], { stdio: 'ignore', detached: true }).unref(); else spawn(process.platform === 'darwin' ? 'open' : 'xdg-open', [url], { stdio: 'ignore', detached: true }).unref(); } catch (_) {} }, 1500); }
console.log('Studio is running. Keep this window open while you work. Close it to stop.');
process.on('SIGINT', stop); process.on('SIGTERM', stop);
children.forEach(c => c.on('exit', code => { if (code) { console.error('A server stopped (exit ' + code + ').'); stop(); } }));
