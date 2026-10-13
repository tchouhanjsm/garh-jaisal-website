const fs = require('node:fs');
const path = require('node:path');
const { test, expect } = require('@playwright/test');

const root = path.resolve(__dirname, '../..');
const manifest = require('../../scripts/image-optimization-manifest.json');
const sourceSet = new Set(manifest.sources);
const sources = manifest.sources.map(source => {
  const relative = path.posix.relative('img', source);
  const parsed = path.posix.parse(relative);
  const derivative = path.posix.join('img', 'optimized', parsed.dir, `${parsed.name}.webp`);
  return {
    source,
    derivative,
    sourceBytes: fs.statSync(path.join(root, source)).size,
    derivativeExists: fs.existsSync(path.join(root, derivative)),
    derivativeBytes: fs.existsSync(path.join(root, derivative)) ? fs.statSync(path.join(root, derivative)).size : null,
  };
});

function getSitemapRoutes() {
  const sitemap = fs.readFileSync(path.join(root, 'sitemap.xml'), 'utf8');
  return [...new Set(
    [...sitemap.matchAll(/<loc>\s*(https?:\/\/[^<]+)\s*<\/loc>/gi)]
      .map(([, value]) => new URL(value.trim()))
      .filter(url => url.hostname === 'www.hotelgarhjaisal.com')
      .map(url => url.pathname || '/')
  )].sort((a, b) => a === '/' ? -1 : b === '/' ? 1 : a.localeCompare(b));
}

function localPath(value, baseUrl) {
  try {
    const url = new URL(value, baseUrl);
    return decodeURIComponent(url.pathname).replace(/^\/+/, '');
  } catch {
    return '';
  }
}

test('approved WebP derivatives preserve dimensions, map to the same source photos, and reduce bytes', async ({ browser }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop-1280', 'Image optimization regression runs once at desktop width');
  test.setTimeout(180_000);

  const optimized = sources.filter(item => item.derivativeExists);
  expect(optimized.length, 'The optimization batch must include at least one measured derivative').toBeGreaterThan(0);

  const report = {
    routesChecked: 0,
    candidates: sources.map(item => ({
      ...item,
      savedBytes: item.derivativeExists ? item.sourceBytes - item.derivativeBytes : null,
      savedPercent: item.derivativeExists ? Number(((item.sourceBytes - item.derivativeBytes) / item.sourceBytes * 100).toFixed(3)) : null,
      matchedUsages: 0,
      optimizedUsages: 0,
    })),
    findings: [],
  };
  const bySource = new Map(report.candidates.map(item => [item.source, item]));
  const routes = getSitemapRoutes();
  const context = await browser.newContext({
    baseURL: 'http://127.0.0.1:8080',
    viewport: { width: 1280, height: 800 },
    deviceScaleFactor: 1,
  });
  const page = await context.newPage();

  try {
    for (const route of routes) {
      const response = await page.goto(route, { waitUntil: 'domcontentloaded', timeout: 20_000 });
      expect(response, `No HTTP response for ${route}`).not.toBeNull();
      expect(response.status(), `Unexpected status for ${route}`).toBeLessThan(400);
      // Force the candidate images to resolve their srcset before reading currentSrc.
      // Below-fold lazy images otherwise may still report the fallback src at DOMContentLoaded.
      const optimizedSourcePaths = sources.filter(item => item.derivativeExists).map(item => item.source);
      await page.locator('img').evaluateAll(async (nodes, candidateSources) => {
        const candidates = nodes.filter(image => {
          try {
            const path = decodeURIComponent(new URL(image.src, document.baseURI).pathname).replace(/^\/+/, '');
            return candidateSources.includes(path);
          } catch {
            return false;
          }
        });
        await Promise.all(candidates.map(async image => {
          image.loading = 'eager';
          image.srcset = image.getAttribute('srcset') || '';
          if (image.decode) {
            try { await image.decode(); } catch { /* The assertion below reports if fallback is selected. */ }
          }
        }));
      }, optimizedSourcePaths);

      const images = await page.locator('img').evaluateAll(nodes => nodes.map(image => {
        const toPath = value => {
          try { return decodeURIComponent(new URL(value, document.baseURI).pathname).replace(/^\/+/, ''); }
          catch { return ''; }
        };
        return {
          source: toPath(image.src),
          current: toPath(image.currentSrc || image.src),
          srcset: image.getAttribute('srcset') || '',
          width: Number(image.getAttribute('width')),
          height: Number(image.getAttribute('height')),
          naturalWidth: image.naturalWidth,
          naturalHeight: image.naturalHeight,
        };
      }));

      for (const image of images) {
        if (!sourceSet.has(image.source)) continue;
        const candidate = bySource.get(image.source);
        candidate.matchedUsages += 1;
        if (!candidate.derivativeExists) {
          continue;
        }
        const hasExpectedSourceSet = image.srcset.split(',').some(item => {
          const candidateUrl = item.trim().split(/\s+/)[0];
          return localPath(candidateUrl, 'http://127.0.0.1:8080') === candidate.derivative;
        });
        if (!hasExpectedSourceSet) {
          report.findings.push({ route, source: candidate.source, issue: 'optimized-srcset-missing', srcset: image.srcset });
          continue;
        }
        if (image.current !== candidate.derivative) {
          report.findings.push({ route, source: candidate.source, issue: 'browser-did-not-select-optimized-derivative', current: image.current, expected: candidate.derivative });
          continue;
        }
        candidate.optimizedUsages += 1;
        if (!(image.width > 0 && image.height > 0)) {
          report.findings.push({ route, source: candidate.source, issue: 'missing-positive-dimensions' });
        }
        if (image.naturalWidth > 0 && (image.naturalWidth !== image.width || image.naturalHeight !== image.height)) {
          report.findings.push({ route, source: candidate.source, issue: 'intrinsic-dimensions-do-not-match-declared', width: image.width, height: image.height, naturalWidth: image.naturalWidth, naturalHeight: image.naturalHeight });
        }
      }
      report.routesChecked += 1;
    }
  } finally {
    await context.close();
  }

  for (const candidate of report.candidates) {
    if (candidate.matchedUsages === 0) {
      report.findings.push({ source: candidate.source, issue: 'candidate-not-found-on-sitemap-routes' });
    }
    if (candidate.derivativeExists) {
      if (!(candidate.derivativeBytes > 0 && candidate.derivativeBytes < candidate.sourceBytes)) {
        report.findings.push({ source: candidate.source, issue: 'derivative-not-smaller-than-original', sourceBytes: candidate.sourceBytes, derivativeBytes: candidate.derivativeBytes });
      }
      if (candidate.savedPercent < manifest.minimum_savings_percent) {
        report.findings.push({ source: candidate.source, issue: 'derivative-below-minimum-saving', savedPercent: candidate.savedPercent });
      }
      if (candidate.optimizedUsages !== candidate.matchedUsages) {
        report.findings.push({ source: candidate.source, issue: 'not-all-rendered-usages-select-optimized-derivative', optimizedUsages: candidate.optimizedUsages, matchedUsages: candidate.matchedUsages });
      }
    }
  }

  const reportDir = path.join(root, 'artifacts', 'image-optimization');
  fs.mkdirSync(reportDir, { recursive: true });
  fs.writeFileSync(path.join(reportDir, 'browser-image-optimization-regression.json'), JSON.stringify({
    generatedAt: new Date().toISOString(),
    source: 'Rendered local-PHP site in Chromium; compares checked-in derivative bytes against the exact checked-in source files.',
    summary: {
      routesChecked: report.routesChecked,
      manifestCandidates: report.candidates.length,
      optimizedCandidates: optimized.length,
      totalSourceBytes: optimized.reduce((sum, item) => sum + item.sourceBytes, 0),
      totalDerivativeBytes: optimized.reduce((sum, item) => sum + item.derivativeBytes, 0),
      totalSavedBytes: optimized.reduce((sum, item) => sum + item.sourceBytes - item.derivativeBytes, 0),
      findings: report.findings.length,
    },
    candidates: report.candidates,
    findings: report.findings,
  }, null, 2) + '\n');

  expect(report.findings, 'Optimized image files must be smaller, dimension-stable, and selected for all rendered candidate usages').toEqual([]);
});
