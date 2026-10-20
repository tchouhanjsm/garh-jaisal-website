const fs = require('node:fs');
const path = require('node:path');
const { spawn } = require('node:child_process');
const { test, expect } = require('@playwright/test');

test('Content Studio guides a nontechnical user through tour and blog drafts', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run the local Content Studio workflow once per browser matrix.');
  test.setTimeout(60_000);

  const root = path.resolve(__dirname, '../..');
  const port = 43000 + (process.pid % 10000);
  const studioUrl = 'http://127.0.0.1:' + port;
  const suffix = String(process.pid);
  const tourSlug = 'content-studio-ui-tour-' + suffix;
  const blogSlug = 'content-studio-ui-blog-' + suffix;
  const ownedPaths = [
    path.join(root, tourSlug + '.php'),
    path.join(root, 'content/experiences/' + tourSlug + '.json'),
    path.join(root, blogSlug + '.php'),
    path.join(root, 'content/editorial/' + blogSlug + '.json'),
  ];
  for (const file of ownedPaths) {
    if (fs.existsSync(file)) throw new Error('Refusing to overwrite an existing test fixture: ' + path.relative(root, file));
  }

  let output = '';
  let errors = '';
  const server = spawn(process.execPath, ['scripts/content-studio.js'], {
    cwd: root,
    env: { ...process.env, CONTENT_STUDIO_PORT: String(port) },
    stdio: ['ignore', 'pipe', 'pipe'],
  });
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
    await expect(page.locator('#statusline')).toContainText('Local authoring only');
    await expect(page.getByText('Four steps to add content')).toBeVisible();

    // Tour form: the author adds separate itinerary and FAQ fields, not delimiter syntax.
    await page.getByRole('tab', { name: 'Add a tour' }).click();
    await page.locator('#tour-title').fill('Browser UI test tour');
    await page.locator('#tour-slug').fill(tourSlug);
    await page.locator('#tour-description').fill('A temporary automated test tour used to verify the guided content authoring experience.');
    await page.locator('#tour-summary').fill('A temporary test of the guided itinerary editor and draft publishing.');
    await page.locator('#tour-best-for').fill('Guests and automated tests of local content authoring.');
    await page.locator('#tour-hero-image').fill('img/desert3.jpg');
    await page.locator('#tour-hero-alt').fill('Camel riders travelling over dunes near Jaisalmer');
    let step = page.locator('#tour-step-list .section-editor').nth(0);
    await step.locator('[data-key="title"]').fill('Meet the guide');
    await step.locator('[data-key="description"]').fill('Confirm the meeting point and timing for the selected travel date.');
    await page.locator('#tour-add-step').click();
    step = page.locator('#tour-step-list .section-editor').nth(1);
    await step.locator('[data-key="title"]').fill('Explore the old city');
    await step.locator('[data-key="description"]').fill('Follow the agreed route and verify access arrangements before departure.');
    await page.locator('#tour-add-faq').click();
    const faq = page.locator('#tour-faq-list .section-editor').nth(0);
    await faq.locator('[data-key="question"]').fill('What should I confirm before departure?');
    await faq.locator('[data-key="answer"]').fill('Ask the hotel to confirm the current time, meeting point and inclusions.');

    const tourRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/tours' && request.method() === 'POST');
    const tourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/tours' && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Validate and save tour' }).click();
    const [tourRequest, tourResponse] = await Promise.all([tourRequestPromise, tourResponsePromise]);
    expect(tourResponse.status()).toBe(201);
    const tourPayload = tourRequest.postDataJSON();
    expect(tourPayload.status).toBe('draft');
    expect(tourPayload.timeline).toContain('Meet the guide | Confirm the meeting point');
    expect(tourPayload.timeline).toContain('Explore the old city | Follow the agreed route');
    expect(tourPayload.faq).toContain('What should I confirm before departure? | Ask the hotel');
    await expect(page.locator('#tour-result')).toContainText('Draft created');
    expect(fs.existsSync(ownedPaths[0])).toBeTruthy();
    expect(fs.existsSync(ownedPaths[1])).toBeTruthy();
    expect(fs.readFileSync(path.join(root, 'sitemap.xml'), 'utf8')).not.toContain('/' + tourSlug + '.php</loc>');

    // Blog form: section and source builders create the format the server expects.
    await page.getByRole('tab', { name: 'Add a blog' }).click();
    await page.locator('#blog-h1').fill('Browser UI test travel guide');
    await page.locator('#blog-slug').fill(blogSlug);
    await page.locator('#blog-seo-title').fill('Browser UI test travel guide');
    await page.locator('#blog-description').fill('A temporary travel guide for verifying the local content editor and its guided article authoring workflow.');
    await page.locator('#blog-lede').fill('This temporary introduction verifies that a new guide can be assembled without Markdown or source delimiters.');
    await page.locator('#blog-hero-image').fill('img/desert3.jpg');
    await page.locator('#blog-hero-alt').fill('Camel riders travelling over dunes near Jaisalmer');
    let section = page.locator('#blog-section-list .section-editor').nth(0);
    await section.locator('[data-section-heading]').fill('Plan the Fort visit');
    await section.locator('[data-section-body]').fill('Start with the time available, confirm the arrangements for your date and leave enough room for walking through the historic lanes.');
    await section.locator('[data-section-bullets]').fill('Confirm the meeting point\nCheck current access arrangements');
    await page.locator('#add-blog-section').click();
    section = page.locator('#blog-section-list .section-editor').nth(1);
    await section.locator('[data-section-heading]').fill('Before you travel');
    await section.locator('[data-section-body]').fill('Check current information shortly before departure and avoid relying on old prices, schedules or availability claims.');
    await expect(page.locator('#blog-outline-preview')).toContainText('Plan the Fort visit');
    let source = page.locator('#blog-source-list .section-editor').nth(0);
    await source.locator('[data-key="label"]').fill('Official tourism guidance');
    await source.locator('[data-key="url"]').fill('https://www.tourism.rajasthan.gov.in/');
    await page.locator('#blog-add-source').click();
    source = page.locator('#blog-source-list .section-editor').nth(1);
    await source.locator('[data-key="label"]').fill('Official destination information');
    await source.locator('[data-key="url"]').fill('https://www.incredibleindia.gov.in/');

    const blogRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/blogs' && request.method() === 'POST');
    const blogResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/blogs' && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Validate and save blog' }).click();
    const [blogRequest, blogResponse] = await Promise.all([blogRequestPromise, blogResponsePromise]);
    expect(blogResponse.status()).toBe(201);
    const blogPayload = blogRequest.postDataJSON();
    expect(blogPayload.status).toBe('draft');
    expect(blogPayload.sections).toContain('## Plan the Fort visit');
    expect(blogPayload.sections).toContain('- Confirm the meeting point');
    expect(blogPayload.sections).toContain('## Before you travel');
    expect(blogPayload.sources).toContain('Official tourism guidance | https://www.tourism.rajasthan.gov.in/');
    expect(blogPayload.sources).toContain('Official destination information | https://www.incredibleindia.gov.in/');
    await expect(page.locator('#blog-result')).toContainText('Draft created');
    expect(fs.existsSync(ownedPaths[2])).toBeTruthy();
    expect(fs.existsSync(ownedPaths[3])).toBeTruthy();
    expect(fs.readFileSync(path.join(root, 'sitemap.xml'), 'utf8')).not.toContain('/' + blogSlug + '.php</loc>');
  } finally {
    if (server.exitCode === null) {
      server.kill('SIGTERM');
      await Promise.race([
        new Promise(resolve => server.once('exit', resolve)),
        new Promise(resolve => setTimeout(resolve, 2_000)),
      ]);
    }
    for (const file of ownedPaths) {
      try { if (fs.existsSync(file)) fs.unlinkSync(file); } catch (_) {}
    }
  }
});
