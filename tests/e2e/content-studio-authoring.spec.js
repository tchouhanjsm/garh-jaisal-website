const fs = require('node:fs');
const path = require('node:path');
const { spawn } = require('node:child_process');
const { test, expect } = require('@playwright/test');

test('Content Studio guides a nontechnical user through tour and blog drafts', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Run the local Content Studio workflow once per browser matrix.');
  test.setTimeout(120_000);

  const root = path.resolve(__dirname, '../..');
  const port = 43000 + (process.pid % 10000);
  const studioUrl = 'http://127.0.0.1:' + port;
  const suffix = String(process.pid);
  const tourSlug = 'content-studio-ui-tour-' + suffix;
  const blogSlug = 'content-studio-ui-blog-' + suffix;
  const pageSlug = 'content-studio-ui-page-' + suffix;
  const pageCssDirectory = path.join(root, 'css/pages');
  const existedPageCssDirectory = fs.existsSync(pageCssDirectory);
  const uploadedImageDirectory = path.join(root, 'img/content-studio');
  const existedUploadedImageDirectory = fs.existsSync(uploadedImageDirectory);
  const pageRoute = path.join(root, pageSlug + '.php');
  const pageRecord = path.join(root, 'content/pages/' + pageSlug + '.json');
  const pageCss = path.join(pageCssDirectory, pageSlug + '.css');
  const sitemapPath = path.join(root, 'sitemap.xml');
  const originalSitemap = fs.readFileSync(sitemapPath);
  const manifestPath = path.join(root, 'scripts/responsive-image-assets-manifest.json');
  const originalManifest = fs.readFileSync(manifestPath);
  const phpPreview = 'http://127.0.0.1:8080';
  let galleryRecordPath = null;
  const galleryFolder = path.join(root, 'content/gallery');
  const gallerySourcePhp = fs.readFileSync(path.join(root, 'include/gallery-data.php'), 'utf8');
  const usedGallerySources = new Set([...gallerySourcePhp.matchAll(/'src'\\s*=>\\s*'([^']+)'/g)].map(match => match[1]));
  if (fs.existsSync(galleryFolder)) {
    for (const name of fs.readdirSync(galleryFolder).filter(name => name.endsWith('.json'))) {
      const record = JSON.parse(fs.readFileSync(path.join(galleryFolder, name), 'utf8'));
      if (record.image && typeof record.image.src === 'string') usedGallerySources.add(record.image.src);
    }
  }
  const gallerySource = ['img/desert12.jpg', 'img/s9.jpg', 'img/j2.jpg', 'img/j6.jpg'].find(src => {
    const file = path.join(root, src);
    try { return fs.lstatSync(file).isFile() && !fs.lstatSync(file).isSymbolicLink() && !usedGallerySources.has(src); }
    catch (_) { return false; }
  });
  if (!gallerySource) throw new Error('No safe unused Gallery source is available for the Content Studio manager test.');
  const ownedPaths = [
    path.join(root, tourSlug + '.php'),
    path.join(root, 'content/experiences/' + tourSlug + '.json'),
    path.join(root, blogSlug + '.php'),
    path.join(root, 'content/editorial/' + blogSlug + '.json'),
    pageRoute,
    pageRecord,
    pageCss,
  ];
  for (const file of ownedPaths) {
    if (fs.existsSync(file)) throw new Error('Refusing to overwrite an existing test fixture: ' + path.relative(root, file));
  }

  let output = '';
  let errors = '';
  page.on('dialog', dialog => dialog.accept());
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
    await page.getByRole('button', { name: /Advanced forms/ }).click();
    await page.getByRole('tab', { name: 'Add a tour' }).click();
    await page.locator('#preview-base').fill(phpPreview);
    await expect(page.locator('#statusline')).toContainText('Local authoring only');
    await page.getByText('How it works, and what is saved').click();
    await expect(page.getByText('Four steps to add content')).toBeVisible();

    // Tour form: the author adds separate itinerary and FAQ fields, not delimiter syntax.
    await page.getByRole('tab', { name: 'Add a tour' }).click();
    await page.locator('#tour-title').fill('Browser UI test tour');
    await page.locator('#tour-slug').fill(tourSlug);
    await page.locator('#tour-description').fill('A temporary automated test tour used to verify the guided content authoring experience.');
    await page.locator('#tour-summary').fill('A temporary test of the guided itinerary editor and draft publishing.');
    await page.locator('#tour-facts-confirmed').selectOption('yes');
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

    const previewRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/preview' && request.method() === 'POST');
    const previewResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/preview' && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Preview PHP-rendered draft' }).first().click();
    const [previewRequest, previewResponse] = await Promise.all([previewRequestPromise, previewResponsePromise]);
    expect(previewResponse.status()).toBe(201);
    expect(previewRequest.postDataJSON().type).toBe('tours');
    expect(previewRequest.postDataJSON().payload.status).toBe('draft');
    await expect(page.locator('#php-preview-frame')).toHaveAttribute('src', /preview\.php\?type=tours&token=/);
    await expect(page.locator('#preview-state')).toContainText('unsaved Draft');
    const phpPage = page.frameLocator('#php-preview-frame');
    await expect(phpPage.getByRole('heading', { name: 'Browser UI test tour', exact: true }).first()).toBeVisible({ timeout: 10000 });
    await expect(phpPage.locator('base')).toHaveAttribute('href', '/');
    await expect(phpPage.locator('link[rel="stylesheet"][href="css/garh-v2.css"]')).toHaveCount(1);
    await expect.poll(() => phpPage.locator('.experience-hero-media img').evaluate((image) => image.complete && image.naturalWidth > 0)).toBe(true);
    await page.locator('#preview-mobile').click();
    await expect(page.locator('#php-preview-frame')).toHaveClass(/mobile/);
    await page.locator('#preview-desktop').click();
    await expect(page.locator('#php-preview-frame')).not.toHaveClass(/mobile/);

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
    await expect(page.locator('#pane-blog')).toBeVisible();
    await expect(page.locator('#blog-preview')).toBeVisible();
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

    const blogPreviewResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/preview' && response.request().method() === 'POST');
    await page.locator('#blog-preview').click();
    const blogPreviewResponse = await blogPreviewResponsePromise;
    expect(blogPreviewResponse.status()).toBe(201);
    const blogPreviewFrame = page.frameLocator('#php-preview-frame');
    await expect(page.locator('#php-preview-frame')).toHaveAttribute('src', /preview\.php\?type=blogs&token=/);
    await expect(blogPreviewFrame.getByRole('heading', { name: 'Browser UI test travel guide', exact: true }).first()).toBeVisible({ timeout: 10000 });

    const blogRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/blogs' && request.method() === 'POST');
    const blogResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/blogs' && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Validate and save blog' }).click();
    const [blogRequest, blogResponse] = await Promise.all([blogRequestPromise, blogResponsePromise]);
    expect(blogResponse.status()).toBe(201);
    const blogPayload = blogRequest.postDataJSON();
    expect(blogPayload.status).toBe('draft');
    const draftBlogResponse = await fetch(phpPreview + '/' + blogSlug + '.php');
    expect(draftBlogResponse.status).toBe(404);
    expect(await draftBlogResponse.text()).not.toContain('Browser UI test travel guide');
    expect(blogPayload.sections).toContain('## Plan the Fort visit');
    expect(blogPayload.sections).toContain('- Confirm the meeting point');
    expect(blogPayload.sections).toContain('## Before you travel');
    expect(blogPayload.sources).toContain('Official tourism guidance | https://www.tourism.rajasthan.gov.in/');
    expect(blogPayload.sources).toContain('Official destination information | https://www.incredibleindia.gov.in/');
    await expect(page.locator('#blog-result')).toContainText('Draft created');
    expect(fs.existsSync(ownedPaths[2])).toBeTruthy();
    expect(fs.existsSync(ownedPaths[3])).toBeTruthy();
    expect(fs.readFileSync(sitemapPath, 'utf8')).not.toContain('/' + blogSlug + '.php</loc>');

    // Landing Page Builder: create a page with approved blocks, preview the real PHP renderer, and exercise release approval.
    await page.getByRole('tab', { name: 'Build a page' }).click();
    await expect(page.locator('#pane-pages')).toBeVisible();
    await page.locator('#page-h1').fill('Browser UI test landing page');
    await page.locator('#page-slug').fill(pageSlug);
    await page.locator('#page-seo-title').fill('Browser UI test landing page');
    await page.locator('#page-description').fill('A temporary landing page to verify guided sections, consistent templates and safe local release workflow.');
    await page.locator('#page-eyebrow').fill('Browser UI acceptance test');
    await page.locator('#page-theme').selectOption('sandstone');
    await page.locator('#page-hero-layout').selectOption('centered');
    await page.locator('#page-hero-intro').fill('This temporary introduction checks the guided landing page editor and actual shared site shell.');
    await page.locator('#page-hero-image').fill('img/desert12.jpg');
    await page.locator('#page-hero-alt').fill('A previous image description that must clear when the photograph changes');
    await page.locator('[data-asset-picker-target="page-hero-image"]').click();
    const assetDialog = page.locator('#asset-picker');
    await expect(assetDialog).toBeVisible();
    await assetDialog.locator('#asset-picker-search').fill('desert3.jpg');
    const heroLibraryChoice = assetDialog.locator('[data-asset-src="img/desert3.jpg"]');
    await expect(heroLibraryChoice).toHaveCount(1);
    await expect.poll(() => heroLibraryChoice.locator('img').evaluate(image => image.complete && image.naturalWidth > 0)).toBe(true);
    await heroLibraryChoice.click();
    await expect(assetDialog).not.toBeVisible();
    await expect(page.locator('#page-hero-image')).toHaveValue('img/desert3.jpg');
    await expect(page.locator('#page-hero-alt')).toHaveValue('');
    await page.locator('#page-hero-alt').fill('Camel riders travelling over dunes near Jaisalmer');
    await page.locator('#page-hero-cta-label').fill('Contact the hotel');
    await page.locator('#page-hero-cta-href').fill('/contact.php');
    let pageSection = page.locator('#page-section-list .page-section-editor').nth(0);
    await pageSection.locator('[data-page-field="heading"]').fill('Plan your visit');
    await pageSection.locator('[data-page-field="body"]').fill('Use this temporary content block to validate the text introduction layout and the final rendered page.');
    await page.locator('#page-section-type').selectOption('image_text');
    await page.locator('#page-add-section').click();
    pageSection = page.locator('#page-section-list .page-section-editor').nth(1);
    await pageSection.locator('[data-page-field="heading"]').fill('A closer look');
    await pageSection.locator('[data-page-field="body"]').fill('A reusable photo and text block lets the team explain another part of the stay without changing the site design.');
    await pageSection.locator('[data-page-field="alt"]').fill('An old photo description that must clear when the image changes');
    await pageSection.locator('[data-asset-picker-section]').click();
    await expect(assetDialog).toBeVisible();
    await assetDialog.locator('#asset-picker-search').fill('desert12.jpg');
    const sectionLibraryChoice = assetDialog.locator('[data-asset-src="img/desert12.jpg"]');
    await expect(sectionLibraryChoice).toHaveCount(1);
    await expect.poll(() => sectionLibraryChoice.locator('img').evaluate(image => image.complete && image.naturalWidth > 0)).toBe(true);
    await sectionLibraryChoice.click();
    await expect(pageSection.locator('[data-page-field="image"]')).toHaveValue('img/desert12.jpg');
    await expect(pageSection.locator('[data-page-field="alt"]')).toHaveValue('');
    const sectionUploadResponsePromise = page.waitForResponse(response => response.url().includes('/api/upload?filename=') && response.request().method() === 'POST');
    await pageSection.locator('input[type="file"][data-upload-target]').setInputFiles(path.join(root, 'img/desert12.jpg'));
    const sectionUploadResponse = await sectionUploadResponsePromise;
    expect(sectionUploadResponse.status()).toBe(201);
    const sectionImagePath = pageSection.locator('[data-page-field="image"]');
    // Avoid reading the response body through Chromium's DevTools cache: this
    // test runs late in a large suite, where Network.getResponseBody may evict it.
    await expect(sectionImagePath).toHaveValue(/^img\/content-studio\/[a-z0-9-]+\.jpg$/);
    await expect(pageSection.locator('[data-page-field="alt"]')).toHaveValue('');
    await pageSection.locator('[data-page-field="alt"]').fill('A sandstone desert scene near Jaisalmer');
    const sectionUploadSrc = await sectionImagePath.inputValue();
    ownedPaths.push(path.join(root, sectionUploadSrc));

    await page.locator('#page-section-type').selectOption('feature_list');
    await page.locator('#page-add-section').click();
    pageSection = page.locator('#page-section-list .page-section-editor').nth(2);
    await pageSection.locator('[data-page-field="heading"]').fill('Useful details');
    await pageSection.locator('[data-page-field="items"]').fill('Confirm current arrangements\nReview details for your selected date');
    await expect(page.locator('#page-outline-preview')).toContainText('Plan your visit');
    await expect(page.locator('#page-outline-preview')).toContainText('2 feature item(s)');
    expect(JSON.parse(await page.locator('#page-sections').inputValue())).toHaveLength(3);
    expect(await page.locator('#page-sections').inputValue()).toContain('"type":"feature_list"');

    const pagePreviewRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/preview' && request.method() === 'POST');
    const pagePreviewResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/preview' && response.request().method() === 'POST');
    await page.locator('#page-preview').click();
    const [pagePreviewRequest, pagePreviewResponse] = await Promise.all([pagePreviewRequestPromise, pagePreviewResponsePromise]);
    expect(pagePreviewResponse.status()).toBe(201);
    expect(pagePreviewRequest.postDataJSON().type).toBe('pages');
    expect(pagePreviewRequest.postDataJSON().payload.status).toBe('draft');
    const pagePreviewResult = await pagePreviewResponse.json();
    const pagePreviewToken = pagePreviewResult.token;
    const pagePreviewSlug = 'content-studio-preview-' + pagePreviewToken;
    ownedPaths.push(path.join(root, 'content/pages/' + pagePreviewSlug + '.json'));
    ownedPaths.push(path.join(root, 'css/pages/' + pagePreviewSlug + '.css'));
    await expect(page.locator('#php-preview-frame')).toHaveAttribute('src', /preview\.php\?type=pages&token=/);
    const landingPreviewFrame = page.frameLocator('#php-preview-frame');
    await expect(landingPreviewFrame.getByRole('heading', { name: 'Browser UI test landing page', exact: true })).toBeVisible({ timeout: 10000 });
    await expect(landingPreviewFrame.getByRole('heading', { name: 'Plan your visit', level: 2 })).toBeVisible();
    await expect(landingPreviewFrame.getByRole('heading', { name: 'A closer look', level: 2 })).toBeVisible();
    await expect(landingPreviewFrame.getByRole('heading', { name: 'Useful details', level: 2 })).toBeVisible();
    await expect(landingPreviewFrame.locator('main.studio-page')).toHaveAttribute('data-page-theme', 'sandstone');
    await expect(landingPreviewFrame.locator('link[rel="stylesheet"][href="css/page-builder.css"]')).toHaveCount(1);
    await expect(landingPreviewFrame.locator('link[rel="stylesheet"][href="css/pages/' + pagePreviewSlug + '.css"]')).toHaveCount(1);
    await expect.poll(() => landingPreviewFrame.locator('.studio-page-hero-media img').evaluate(image => image.complete && image.naturalWidth > 0)).toBe(true);
    const landingHero = landingPreviewFrame.locator('.studio-page-hero-media img');
    await expect(landingHero).toHaveAttribute('aria-describedby', 'studio-page-hero-caption');
    await expect(landingPreviewFrame.locator('figcaption#studio-page-hero-caption')).toBeVisible();
    const landingHeroSize = await landingHero.evaluate(image => ({
      width: Number(image.getAttribute('width') || 0),
      height: Number(image.getAttribute('height') || 0),
      naturalWidth: image.naturalWidth,
      naturalHeight: image.naturalHeight,
    }));
    expect(landingHeroSize.width).toBeGreaterThan(0);
    expect(landingHeroSize.height).toBeGreaterThan(0);
    const declaredHeroRatio = landingHeroSize.width / landingHeroSize.height;
    const loadedHeroRatio = landingHeroSize.naturalWidth / landingHeroSize.naturalHeight;
    expect(Math.abs(declaredHeroRatio / loadedHeroRatio - 1)).toBeLessThanOrEqual(0.01);

    const pageCreateRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/pages' && request.method() === 'POST');
    const pageCreateResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/pages' && response.request().method() === 'POST');
    await page.locator('#page-submit').click();
    const [pageCreateRequest, pageCreateResponse] = await Promise.all([pageCreateRequestPromise, pageCreateResponsePromise]);
    expect(pageCreateResponse.status()).toBe(201);
    const savedPagePayload = pageCreateRequest.postDataJSON();
    expect(savedPagePayload.status).toBe('draft');
    const savedPageSections = JSON.parse(savedPagePayload.sections);
    expect(savedPageSections).toHaveLength(3);
    expect(savedPageSections.map(section => section.type)).toEqual(['intro', 'image_text', 'feature_list']);
    expect(savedPageSections[1].image).toBe(sectionUploadSrc);
    expect(savedPageSections[1].alt).toBe('A sandstone desert scene near Jaisalmer');
    expect(savedPageSections[2].items).toEqual(['Confirm current arrangements', 'Review details for your selected date']);
    expect(fs.existsSync(pageRoute)).toBeTruthy();
    expect(fs.existsSync(pageRecord)).toBeTruthy();
    expect(fs.existsSync(pageCss)).toBeTruthy();
    expect(fs.readFileSync(sitemapPath, 'utf8')).not.toContain('/' + pageSlug + '.php</loc>');

    // Create a Gallery draft, then verify every content type can be managed from the same screen.
    await page.getByRole('tab', { name: 'Add Gallery photos' }).click();
    await page.locator('#gallery-src').fill(gallerySource);
    await page.locator('#gallery-alt').fill('Browser UI test image showing a Jaisalmer heritage property');
    await page.locator('#gallery-caption').fill('Browser UI test Gallery caption');
    const galleryRequestPromise = page.waitForRequest(request => request.url() === studioUrl + '/api/gallery' && request.method() === 'POST');
    const galleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/gallery' && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Validate and add photo' }).click();
    const [galleryRequest, galleryResponse] = await Promise.all([galleryRequestPromise, galleryResponsePromise]);
    expect(galleryResponse.status()).toBe(201);
    const galleryPayload = galleryRequest.postDataJSON();
    expect(galleryPayload.status).toBe('draft');
    const galleryResult = await galleryResponse.json();
    galleryRecordPath = path.join(galleryFolder, galleryResult.id + '.json');
    ownedPaths.push(galleryRecordPath);
    expect(fs.existsSync(galleryRecordPath)).toBeTruthy();

    // Tour: reopen the saved draft, edit it, publish locally, unpublish, then delete.
    await page.getByRole('tab', { name: 'Manage saved content' }).click();
    let tourCard = page.locator('.content-record[data-record-key="tours:' + tourSlug + '"]');
    await expect(tourCard).toContainText('DRAFT');
    await expect(tourCard.locator('.record-media-status')).toHaveText('MEDIA NEEDS ATTENTION');
    await expect(tourCard.locator('.record-media-issues')).toContainText('reviewed hero caption');
    await tourCard.getByRole('button', { name: 'Edit' }).click();
    await page.locator('#tour-title').fill('Browser UI test tour revised');
    await page.locator('#tour-summary').fill('A revised summary used to validate editing a saved Content Studio draft record.');
    await page.locator('#tour-hero-caption').fill('Camel riders travel over the dunes near Jaisalmer during the test tour preview.');
    const updateTourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/tours/' + tourSlug && response.request().method() === 'PUT');
    await page.getByRole('button', { name: 'Save changes' }).click();
    expect((await updateTourResponsePromise).status()).toBe(200);
    tourCard = page.locator('.content-record[data-record-key="tours:' + tourSlug + '"]');
    await expect(tourCard).toContainText('Browser UI test tour revised');
    await expect(tourCard.locator('.record-media-status')).toHaveText('MEDIA READY');
    const readyTourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/tours/' + tourSlug + '/review' && response.request().method() === 'PATCH');
    await tourCard.getByRole('button', { name: 'Ready for review' }).click();
    expect((await readyTourResponsePromise).status()).toBe(200);
    await expect(tourCard).toContainText('READY FOR REVIEW');
    const approveTourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/tours/' + tourSlug + '/review' && response.request().method() === 'PATCH');
    await tourCard.getByRole('button', { name: 'Approve for release' }).click();
    expect((await approveTourResponsePromise).status()).toBe(200);
    await expect(tourCard).toContainText('APPROVED FOR RELEASE');
    const publishTourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/tours/' + tourSlug + '/status' && response.request().method() === 'PATCH');
    await tourCard.getByRole('button', { name: 'Publish locally' }).click();
    expect((await publishTourResponsePromise).status()).toBe(200);
    await expect(tourCard).toContainText('LIVE');
    expect(fs.readFileSync(sitemapPath, 'utf8')).toContain('/' + tourSlug + '.php</loc>');
    const localTourResponse = await fetch(phpPreview + '/' + tourSlug + '.php');
    expect(localTourResponse.status).toBe(200);
    expect(await localTourResponse.text()).not.toMatch(/noindex,follow/);
    const unpublishTourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/tours/' + tourSlug + '/status' && response.request().method() === 'PATCH');
    await tourCard.getByRole('button', { name: 'Set to Draft' }).click();
    expect((await unpublishTourResponsePromise).status()).toBe(200);
    await expect(tourCard).toContainText('DRAFT');
    expect(fs.readFileSync(sitemapPath, 'utf8')).not.toContain('/' + tourSlug + '.php</loc>');
    const draftTourResponse = await fetch(phpPreview + '/' + tourSlug + '.php');
    expect(draftTourResponse.status).toBe(404);
    expect(await draftTourResponse.text()).not.toContain('Browser UI test tour');
    const deleteTourResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/tours/' + tourSlug && response.request().method() === 'DELETE');
    await tourCard.getByRole('button', { name: 'Delete draft' }).click();
    expect((await deleteTourResponsePromise).status()).toBe(200);
    await expect(page.locator('.content-record[data-record-key="tours:' + tourSlug + '"]')).toHaveCount(0);
    expect(fs.existsSync(ownedPaths[0])).toBeFalsy();
    expect(fs.existsSync(ownedPaths[1])).toBeFalsy();

    // Blog: the manager can reopen sections/sources, save edits, and safely remove a draft.
    let blogCard = page.locator('.content-record[data-record-key="blogs:' + blogSlug + '"]');
    await blogCard.getByRole('button', { name: 'Edit' }).click();
    await page.locator('#blog-h1').fill('Browser UI test travel guide revised');
    await page.locator('#blog-card-title').fill('Browser UI revised blog card');
    const updateBlogResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/blogs/' + blogSlug && response.request().method() === 'PUT');
    await page.getByRole('button', { name: 'Save changes' }).click();
    expect((await updateBlogResponsePromise).status()).toBe(200);
    blogCard = page.locator('.content-record[data-record-key="blogs:' + blogSlug + '"]');
    await expect(blogCard).toContainText('Browser UI test travel guide revised');
    const deleteBlogResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/blogs/' + blogSlug && response.request().method() === 'DELETE');
    await blogCard.getByRole('button', { name: 'Delete draft' }).click();
    expect((await deleteBlogResponsePromise).status()).toBe(200);
    await expect(page.locator('.content-record[data-record-key="blogs:' + blogSlug + '"]')).toHaveCount(0);

    // Gallery: edit metadata, publish, unpublish and delete the Draft record without deleting its source image.
    const galleryId = galleryResult.id;
    let galleryCard = page.locator('.content-record[data-record-key="gallery:' + galleryId + '"]');
    await galleryCard.getByRole('button', { name: 'Edit' }).click();
    await page.locator('#gallery-alt').fill('Browser UI updated description of a Jaisalmer heritage property');
    await page.locator('#gallery-caption').fill('Browser UI updated Gallery caption');
    const updateGalleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/gallery/' + galleryId && response.request().method() === 'PUT');
    await page.getByRole('button', { name: 'Save changes' }).click();
    expect((await updateGalleryResponsePromise).status()).toBe(200);
    galleryCard = page.locator('.content-record[data-record-key="gallery:' + galleryId + '"]');
    await expect(galleryCard).toContainText('Browser UI updated Gallery caption');
    const readyGalleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/gallery/' + galleryId + '/review' && response.request().method() === 'PATCH');
    await galleryCard.getByRole('button', { name: 'Ready for review' }).click();
    expect((await readyGalleryResponsePromise).status()).toBe(200);
    await expect(galleryCard).toContainText('READY FOR REVIEW');
    const approveGalleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/gallery/' + galleryId + '/review' && response.request().method() === 'PATCH');
    await galleryCard.getByRole('button', { name: 'Approve for release' }).click();
    expect((await approveGalleryResponsePromise).status()).toBe(200);
    await expect(galleryCard).toContainText('APPROVED FOR RELEASE');
    const publishGalleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/gallery/' + galleryId + '/status' && response.request().method() === 'PATCH');
    await galleryCard.getByRole('button', { name: 'Publish locally' }).click();
    expect((await publishGalleryResponsePromise).status()).toBe(200);
    await expect(galleryCard).toContainText('LIVE');
    const unpublishGalleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/gallery/' + galleryId + '/status' && response.request().method() === 'PATCH');
    await galleryCard.getByRole('button', { name: 'Set to Draft' }).click();
    expect((await unpublishGalleryResponsePromise).status()).toBe(200);
    await expect(galleryCard).toContainText('DRAFT');
    const deleteGalleryResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/gallery/' + galleryId && response.request().method() === 'DELETE');
    await galleryCard.getByRole('button', { name: 'Delete draft' }).click();
    expect((await deleteGalleryResponsePromise).status()).toBe(200);
    await expect(page.locator('.content-record[data-record-key="gallery:' + galleryId + '"]')).toHaveCount(0);
    expect(fs.existsSync(galleryRecordPath)).toBeFalsy();
    expect(fs.existsSync(path.join(root, gallerySource))).toBeTruthy();

    // Landing page: approval, publication, edit-triggered re-approval, unpublish and delete.
    await page.getByRole('tab', { name: 'Manage saved content' }).click();
    let pageCard = page.locator('.content-record[data-record-key="pages:' + pageSlug + '"]');
    await expect(pageCard).toContainText('DRAFT');
    await pageCard.getByRole('button', { name: 'Ready for review' }).click();
    await expect(pageCard).toContainText('READY FOR REVIEW');
    await pageCard.getByRole('button', { name: 'Approve for release' }).click();
    await expect(pageCard).toContainText('APPROVED FOR RELEASE');
    const publishPageResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/pages/' + pageSlug + '/status' && response.request().method() === 'PATCH');
    await pageCard.getByRole('button', { name: 'Publish locally' }).click();
    expect((await publishPageResponsePromise).status()).toBe(200);
    pageCard = page.locator('.content-record[data-record-key="pages:' + pageSlug + '"]');
    await expect(pageCard).toContainText('LIVE');
    expect(fs.readFileSync(sitemapPath, 'utf8')).toContain('/' + pageSlug + '.php</loc>');
    const livePageHtml = await (await fetch(phpPreview + '/' + pageSlug + '.php')).text();
    expect(livePageHtml).toContain('Browser UI test landing page');
    expect(livePageHtml).not.toMatch(/noindex,follow/);

    await pageCard.getByRole('button', { name: 'Edit' }).click();
    await expect(page.locator('#pane-pages')).toBeVisible();
    await page.locator('#page-h1').fill('Browser UI test landing page revised');
    await page.locator('#page-seo-title').fill('Browser UI test landing page revised');
    const pageUpdateResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/pages/' + pageSlug && response.request().method() === 'PUT');
    await page.locator('#page-submit').click();
    expect((await pageUpdateResponsePromise).status()).toBe(200);
    await expect(page.locator('#pane-manage')).toBeVisible();
    pageCard = page.locator('.content-record[data-record-key="pages:' + pageSlug + '"]');
    await expect(pageCard).toContainText('DRAFT');
    await expect(pageCard).toContainText('REVIEW: DRAFT');
    expect(fs.readFileSync(sitemapPath, 'utf8')).not.toContain('/' + pageSlug + '.php</loc>');
    const draftPageResponse = await fetch(phpPreview + '/' + pageSlug + '.php');
    expect(draftPageResponse.status).toBe(404);
    expect(await draftPageResponse.text()).not.toContain('Browser UI test landing page revised');

    await pageCard.getByRole('button', { name: 'Ready for review' }).click();
    await pageCard.getByRole('button', { name: 'Approve for release' }).click();
    const republishPageResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/pages/' + pageSlug + '/status' && response.request().method() === 'PATCH');
    await pageCard.getByRole('button', { name: 'Publish locally' }).click();
    expect((await republishPageResponsePromise).status()).toBe(200);
    pageCard = page.locator('.content-record[data-record-key="pages:' + pageSlug + '"]');
    await expect(pageCard).toContainText('LIVE');

    const unpublishPageResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/pages/' + pageSlug + '/status' && response.request().method() === 'PATCH');
    await pageCard.getByRole('button', { name: 'Set to Draft' }).click();
    expect((await unpublishPageResponsePromise).status()).toBe(200);
    await expect(pageCard).toContainText('DRAFT');
    expect(fs.readFileSync(sitemapPath, 'utf8')).not.toContain('/' + pageSlug + '.php</loc>');
    const deletePageResponsePromise = page.waitForResponse(response => response.url() === studioUrl + '/api/content/pages/' + pageSlug && response.request().method() === 'DELETE');
    await pageCard.getByRole('button', { name: 'Delete draft' }).click();
    expect((await deletePageResponsePromise).status()).toBe(200);
    expect(fs.existsSync(pageRoute)).toBeFalsy();
    expect(fs.existsSync(pageRecord)).toBeFalsy();
    expect(fs.existsSync(pageCss)).toBeFalsy();
    expect(fs.existsSync(path.join(root, 'img/desert3.jpg'))).toBeTruthy();

  } finally {
    if (server.exitCode === null) {
      server.kill('SIGTERM');
      await Promise.race([
        new Promise(resolve => server.once('exit', resolve)),
        new Promise(resolve => setTimeout(resolve, 2_000)),
      ]);
    }
    for (const file of ownedPaths) {
      try { if (fs.existsSync(file) && !fs.lstatSync(file).isSymbolicLink()) fs.unlinkSync(file); } catch (_) {}
    }
    try {
      if (!fs.readFileSync(sitemapPath).equals(originalSitemap)) fs.writeFileSync(sitemapPath, originalSitemap);
    } catch (_) {}
    try {
      if (!fs.readFileSync(manifestPath).equals(originalManifest)) fs.writeFileSync(manifestPath, originalManifest);
    } catch (_) {}
    if (!existedPageCssDirectory) {
      try {
        if (fs.existsSync(pageCssDirectory) && fs.readdirSync(pageCssDirectory).length === 0) fs.rmdirSync(pageCssDirectory);
      } catch (_) {}
    }
    if (!existedUploadedImageDirectory) {
      try {
        if (fs.existsSync(uploadedImageDirectory) && fs.readdirSync(uploadedImageDirectory).length === 0) fs.rmdirSync(uploadedImageDirectory);
      } catch (_) {}
    }
  }
});
