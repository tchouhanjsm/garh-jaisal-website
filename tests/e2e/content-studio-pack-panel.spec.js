'use strict';
const fs = require('node:fs');
const path = require('node:path');
const { spawn } = require('node:child_process');
const { test, expect } = require('@playwright/test');

test('Content Studio Pack check is useful and remains a read-only dry run', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run the local Content Studio workflow once per browser matrix.');
  test.setTimeout(45_000);

  const root = path.resolve(__dirname, '../..');
  const port = 43000 + (process.pid % 10000);
  const studioUrl = 'http://127.0.0.1:' + port;
  const server = spawn(process.execPath, ['scripts/content-studio.js'], {
    cwd: root,
    env: { ...process.env, CONTENT_STUDIO_PORT: String(port) },
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  let output = '';
  let errors = '';
  server.stdout.on('data', chunk => { output += chunk.toString(); });
  server.stderr.on('data', chunk => { errors += chunk.toString(); });

  try {
    let ready = false;
    let lastError = 'No response yet';
    for (let attempt = 0; attempt < 80; attempt++) {
      if (server.exitCode !== null) throw new Error('Content Studio exited early: ' + errors);
      try {
        const response = await fetch(studioUrl + '/api/status', { signal: AbortSignal.timeout(800) });
        if (response.ok) { ready = true; break; }
        lastError = 'HTTP ' + response.status;
      } catch (error) {
        lastError = error.message;
      }
      await new Promise(resolve => setTimeout(resolve, 100));
    }
    if (!ready) throw new Error('Content Studio did not start: ' + lastError + '\n' + output + errors);

    await page.goto(studioUrl + '/', { waitUntil: 'domcontentloaded' });
    await page.getByRole('button', { name: /Advanced forms/ }).click();
    await page.getByRole('tab', { name: 'Add a tour' }).click();
    await page.getByRole('tab', { name: 'Pack check' }).click();
    await expect(page.locator('#pane-pack')).toBeVisible();

    // Malformed JSON should produce an owner-readable error, not a silent failure.
    await page.locator('#pack-record').fill('{');
    await page.locator('#pack-plan').click();
    await expect(page.locator('#pack-result')).toContainText('Cannot plan:');

    // Unsafe slug is rejected by the deterministic plan engine with no operations.
    await page.locator('#pack-slug').fill('../pack-panel-escape');
    await page.locator('#pack-record').fill('{}');
    const unsafePromise = page.waitForResponse(response =>
      response.url() === studioUrl + '/api/pack/plan' && response.request().method() === 'POST');
    await page.locator('#pack-plan').click();
    const unsafeResponse = await unsafePromise;
    expect(unsafeResponse.status()).toBe(200);
    const unsafePlan = await unsafeResponse.json();
    expect(unsafePlan.ok).toBe(false);
    expect((unsafePlan.findings || []).map(finding => finding.rule)).toContain('PL-02');
    expect(unsafePlan.operations || []).toHaveLength(0);
    await expect(page.locator('#pack-result')).toContainText('NOT READY:');
    await expect(page.locator('#pack-result')).toContainText('[PL-02]');

    // A structurally incomplete record still shows the mapped output paths, but writes none.
    const slug = 'content-studio-pack-dry-run-' + process.pid;
    const routePath = path.join(root, slug + '.php');
    const recordPath = path.join(root, 'content/experiences', slug + '.json');
    expect(fs.existsSync(routePath)).toBe(false);
    expect(fs.existsSync(recordPath)).toBe(false);
    await page.locator('#pack-slug').fill(slug);
    await page.locator('#pack-record').fill('{}');
    const planPromise = page.waitForResponse(response =>
      response.url() === studioUrl + '/api/pack/plan' && response.request().method() === 'POST');
    await page.locator('#pack-plan').click();
    const planResponse = await planPromise;
    expect(planResponse.status()).toBe(200);
    const plan = await planResponse.json();
    expect(plan.ok).toBe(false);
    expect((plan.findings || []).some(finding => finding.rule === 'RC-01')).toBe(true);
    expect((plan.operations || []).map(operation => operation.path)).toContain('content/experiences/' + slug + '.json');
    expect((plan.operations || []).map(operation => operation.path)).toContain(slug + '.php');
    expect(fs.existsSync(routePath)).toBe(false);
    expect(fs.existsSync(recordPath)).toBe(false);
    await expect(page.locator('#pack-result')).toContainText('NOT READY:');
  } finally {
    if (server.exitCode === null && server.signalCode === null) {
      server.kill('SIGTERM');
      await Promise.race([
        new Promise(resolve => server.once('exit', resolve)),
        new Promise(resolve => setTimeout(resolve, 2_000)),
      ]);
    }
  }
});
