<?php
require_once dirname(__DIR__) . '/include/media-library-audit.php';

header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: private, no-store, max-age=0, must-revalidate');

$isJson = isset($_GET['format']) && $_GET['format'] === 'json';

try {
    $report = gj_media_library_audit(dirname(__DIR__));
    $report['error'] = null;
} catch (Throwable $error) {
    http_response_code(503);
    $report = [
        'generated_at' => date(DATE_ATOM),
        'assets' => [],
        'duplicate_groups' => [],
        'missing_gallery_references' => [],
        'warnings' => ['The media inventory is temporarily unavailable.'],
        'summary' => [
            'total_assets' => 0,
            'referenced_assets' => 0,
            'no_literal_reference' => 0,
            'responsive_derivatives' => 0,
            'duplicate_assets' => 0,
            'duplicate_groups' => 0,
            'total_bytes' => 0,
            'large_file_assets' => 0,
            'oversized_dimension_assets' => 0,
            'optimization_review_assets' => 0,
            'large_file_threshold_bytes' => 524288,
            'large_dimension_threshold_px' => 2560,
            'source_files_scanned' => 0,
            'source_files_skipped' => 0,
            'source_scan_complete' => false,
            'missing_gallery_references' => 0,
        ],
        'error' => 'Media inventory unavailable.',
    ];
}

$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$encodedReport = json_encode($report, $jsonFlags);
if (!is_string($encodedReport)) {
    http_response_code(500);
    $encodedReport = '{"assets":[],"warnings":["The media inventory could not be encoded."],"summary":{},"error":"Media inventory unavailable."}';
}

if ($isJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo $encodedReport;
    exit;
}
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <title>Media Library Intelligence · Garh Jaisal Haveli</title>
  <style>
    :root {
      color-scheme: light;
      --ink: #24211d;
      --muted: #716b62;
      --line: #e8e0d5;
      --paper: #fbf9f5;
      --surface: #fff;
      --sand: #e9dfd0;
      --gold: #9a6b35;
      --green: #376b52;
      --red: #a4483e;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: var(--paper);
      color: var(--ink);
    }
    * { box-sizing: border-box; }
    body { margin: 0; min-width: 320px; }
    a { color: inherit; }
    .shell { width: min(1240px, calc(100% - 40px)); margin: 0 auto; }
    .masthead { border-bottom: 1px solid var(--line); background: rgba(255,255,255,.86); }
    .masthead-inner { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
    .brand { display: flex; flex-direction: column; gap: 3px; text-decoration: none; }
    .brand strong { letter-spacing: .08em; font-family: Georgia, serif; font-size: 17px; }
    .brand span { color: var(--muted); font-size: 11px; letter-spacing: .14em; text-transform: uppercase; }
    .nav { display: flex; gap: 10px; flex-wrap: wrap; }
    .nav a { border: 1px solid var(--line); border-radius: 999px; padding: 10px 15px; font-size: 13px; text-decoration: none; background: var(--surface); }
    .nav a.primary { background: var(--ink); border-color: var(--ink); color: white; }
    main { padding: 54px 0 76px; }
    .eyebrow { color: var(--gold); font-size: 11px; letter-spacing: .18em; text-transform: uppercase; font-weight: 700; }
    h1 { max-width: 720px; margin: 12px 0; font: 500 clamp(32px, 5vw, 54px)/1.05 Georgia, serif; letter-spacing: -.035em; }
    .lede { max-width: 720px; margin: 0; color: var(--muted); line-height: 1.7; }
    .status-note { margin: 26px 0 30px; padding: 16px 18px; border: 1px solid #e6d8c5; border-radius: 12px; background: #f4eee5; color: #51483d; line-height: 1.55; font-size: 13px; }
    .stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin: 28px 0; }
    .stat { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 18px; min-width: 0; }
    .stat span { display: block; color: var(--muted); font-size: 11px; letter-spacing: .08em; text-transform: uppercase; }
    .stat strong { display: block; margin-top: 8px; font: 500 27px/1 Georgia, serif; }
    .toolbar { display: grid; grid-template-columns: minmax(180px, 1fr) 190px 210px; gap: 12px; padding: 14px; background: var(--surface); border: 1px solid var(--line); border-radius: 14px; }
    .toolbar label { display: flex; flex-direction: column; gap: 7px; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .optimization-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin: 12px 0 24px; }
    .optimization-metric { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 16px; border: 1px solid var(--line); border-radius: 12px; background: #f4eee5; }
    .optimization-metric span { color: var(--muted); font-size: 12px; }
    .optimization-metric strong { font: 500 22px/1 Georgia, serif; }
    .optimization-summary-note { grid-column: 1 / -1; margin: 0; color: var(--muted); font-size: 11px; line-height: 1.5; }
    .toolbar input, .toolbar select { width: 100%; min-height: 44px; border: 1px solid #d8d0c5; border-radius: 8px; padding: 0 12px; color: var(--ink); background: white; font: inherit; font-size: 14px; }
    .results-line { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 18px 2px; color: var(--muted); font-size: 13px; }
    .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .asset-card { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; min-width: 0; }
    .preview { height: 190px; background: #eee8de; display: grid; place-items: center; overflow: hidden; }
    .preview img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .preview-fallback { color: var(--muted); font: 14px Georgia, serif; }
    .asset-body { padding: 15px; }
    .asset-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .asset-head h2 { min-width: 0; margin: 0; font-size: 14px; line-height: 1.4; overflow-wrap: anywhere; }
    .tag { flex: 0 0 auto; display: inline-flex; align-items: center; padding: 5px 8px; border-radius: 999px; background: #f0ede8; color: #5f574d; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
    .tag.live { background: #e8f1eb; color: var(--green); }
    .tag.draft { background: #f7efe0; color: #8a632a; }
    .tag.hidden { background: #f1e9e8; color: var(--red); }
    .tag.warning { background: #f7efe0; color: #8a632a; }
    .asset-path { margin: 8px 0 12px; color: var(--muted); font: 11px/1.5 ui-monospace, SFMono-Regular, Menlo, monospace; overflow-wrap: anywhere; }
    .meta { display: flex; flex-wrap: wrap; gap: 7px 12px; color: var(--muted); font-size: 11px; }
    .uses { margin-top: 13px; padding-top: 12px; border-top: 1px solid var(--line); }
    .uses summary { cursor: pointer; color: #5d5143; font-size: 12px; font-weight: 650; }
    .uses ul { margin: 10px 0 0; padding-left: 17px; color: var(--muted); font: 11px/1.6 ui-monospace, SFMono-Regular, Menlo, monospace; overflow-wrap: anywhere; }
    .empty { grid-column: 1 / -1; padding: 40px 20px; text-align: center; color: var(--muted); background: white; border: 1px dashed #d8d0c5; border-radius: 14px; }
    .warnings { margin: 24px 0; color: var(--red); font-size: 13px; }
    .warnings ul { padding-left: 20px; }
    .footnote { margin-top: 28px; padding-top: 18px; border-top: 1px solid var(--line); color: var(--muted); font-size: 12px; line-height: 1.65; }
    .footnote strong { color: var(--ink); }
    @media (max-width: 900px) { .stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 620px) {
      .shell { width: min(100% - 28px, 1240px); }
      .masthead-inner { align-items: flex-start; flex-direction: column; justify-content: center; padding: 15px 0; }
      main { padding-top: 36px; }
      .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .optimization-summary { grid-template-columns: 1fr; }
      .optimization-summary-note { grid-column: auto; }
      .toolbar { grid-template-columns: 1fr; }
      .grid { grid-template-columns: 1fr; }
      .preview { height: 220px; }
      .results-line { align-items: flex-start; flex-direction: column; }
    }
  </style>
</head>
<body>
<header class="masthead">
  <div class="shell masthead-inner">
    <a class="brand" href="/admin/"><strong>GARH JAISAL HAVELI</strong><span>Owner media tools</span></a>
    <nav class="nav" aria-label="Media tools">
      <a href="/gallery.php">View public gallery</a>
      <a class="primary" href="/admin/">Open Sveltia CMS</a>
    </nav>
  </div>
</header>
<main class="shell">
  <p class="eyebrow">Media operations · Read-only inventory</p>
  <h1>Media library intelligence</h1>
  <p class="lede">Understand where repository images are referenced, which Gallery records use them, and where identical files exist. Use Sveltia CMS to edit Gallery records and upload replacements.</p>

  <div class="status-note" role="note">
    <strong>Safety first:</strong> this report does not upload, replace, hide or delete files. “No literal reference found” is not proof that an image is unused; pages may build image paths dynamically and responsive-image variants are selected by shared code. Never delete from this report alone.
  </div>

  <section class="stats" aria-label="Media inventory summary">
    <div class="stat"><span>Total image files</span><strong id="stat-total">—</strong></div>
    <div class="stat"><span>Total image storage</span><strong id="stat-storage">—</strong></div>
    <div class="stat"><span>Literal references found</span><strong id="stat-referenced">—</strong></div>
    <div class="stat"><span>No literal reference</span><strong id="stat-unreferenced">—</strong></div>
    <div class="stat"><span>Responsive derivatives</span><strong id="stat-responsive">—</strong></div>
    <div class="stat"><span>Exact duplicate files</span><strong id="stat-duplicates">—</strong></div>
  </section>

  <section class="toolbar" aria-label="Filter media inventory">
    <label>Search by filename or source
      <input id="media-search" type="search" placeholder="Try: facade, room, responsive…" autocomplete="off">
    </label>
    <label>Gallery category
      <select id="media-category">
        <option value="all">All media</option>
        <option value="hotel">Hotel &amp; fort</option>
        <option value="rooms">Rooms</option>
        <option value="desert">Desert</option>
        <option value="restaurant">Restaurant</option>
        <option value="other">Not in Gallery</option>
      </select>
    </label>
    <label>Usage &amp; optimisation
      <select id="media-usage">
        <option value="all">All usage states</option>
        <option value="referenced">Literal reference found</option>
        <option value="no-literal-reference">No literal reference found</option>
        <option value="responsive-derivative">Responsive derivative</option>
        <option value="duplicate">Exact duplicate content</option>
        <option value="large-file">Large files</option>
        <option value="large-dimensions">Large dimensions</option>
        <option value="optimization-review">All optimisation flags</option>
      </select>
    </label>
  </section>

  <section class="optimization-summary" aria-label="Image optimisation review">
    <div class="optimization-metric"><span id="large-file-label">Files over 512 KB</span><strong id="stat-large-files">—</strong></div>
    <div class="optimization-metric"><span id="large-dimension-label">Images over 2,560 px</span><strong id="stat-large-dimensions">—</strong></div>
    <p class="optimization-summary-note">These thresholds identify files for manual review only. They do not mean an image is defective: original quality, intended display size, responsive derivatives and visual detail all matter.</p>
  </section>

  <div class="results-line">
    <span id="result-count" role="status" aria-live="polite">Preparing inventory…</span>
    <span id="scan-coverage" role="status" aria-live="polite"></span>
    <span id="generated-at"></span>
  </div>

  <div id="media-warnings" class="warnings" aria-live="polite"></div>
  <section id="media-grid" class="grid" aria-label="Image files"></section>

  <p class="footnote">
    <strong>How to interpret this report:</strong> usage is based on literal paths found in the public PHP, HTML, CSS, JavaScript, JSON, XML and configuration source files scanned by the site. Dynamic path construction may not be detected. Duplicate groups use an exact SHA-256 file-content match, not visual similarity. The report is generated on request and is read-only.
  </p>
</main>
<script id="media-library-data" type="application/json"><?= $encodedReport ?></script>
<script>
(() => {
  'use strict';
  const reportNode = document.getElementById('media-library-data');
  const report = JSON.parse(reportNode.textContent || '{}');
  const assets = Array.isArray(report.assets) ? report.assets : [];
  const duplicatePathSet = new Set((report.duplicate_groups || []).flat());
  const grid = document.getElementById('media-grid');
  const search = document.getElementById('media-search');
  const category = document.getElementById('media-category');
  const usage = document.getElementById('media-usage');
  const number = (value) => new Intl.NumberFormat().format(Number(value || 0));
  const bytes = (value) => {
    const amount = Number(value || 0);
    if (amount < 1024) return amount + ' B';
    if (amount < 1024 * 1024) return (amount / 1024).toFixed(1) + ' KB';
    return (amount / (1024 * 1024)).toFixed(2) + ' MB';
  };
  const make = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };
  const summary = report.summary || {};
  document.getElementById('stat-total').textContent = number(summary.total_assets);
  document.getElementById('stat-storage').textContent = bytes(summary.total_bytes);
  document.getElementById('stat-large-files').textContent = number(summary.large_file_assets);
  document.getElementById('stat-large-dimensions').textContent = number(summary.oversized_dimension_assets);
  document.getElementById('large-file-label').textContent = 'Files over ' + bytes(summary.large_file_threshold_bytes);
  document.getElementById('large-dimension-label').textContent = 'Images over ' + number(summary.large_dimension_threshold_px) + ' px';
  document.getElementById('stat-referenced').textContent = number(summary.referenced_assets);
  document.getElementById('stat-unreferenced').textContent = number(summary.no_literal_reference);
  document.getElementById('stat-responsive').textContent = number(summary.responsive_derivatives);
  document.getElementById('stat-duplicates').textContent = number(summary.duplicate_assets);
  const scanCoverage = document.getElementById('scan-coverage');
  scanCoverage.textContent = summary.source_scan_complete
    ? 'Source scan complete · ' + number(summary.source_files_scanned) + ' files inspected'
    : 'Source scan partial · ' + number(summary.source_files_skipped) + ' skipped; usage counts may be incomplete';
  scanCoverage.setAttribute('data-scan-complete', summary.source_scan_complete ? 'true' : 'false');
  document.getElementById('generated-at').textContent = report.generated_at ? 'Generated ' + new Date(report.generated_at).toLocaleString() : '';

  const warningList = document.getElementById('media-warnings');
  const notices = [];
  if (report.error) notices.push(report.error);
  (report.warnings || []).forEach((warning) => notices.push(warning));
  if ((report.missing_gallery_references || []).length) {
    notices.push(report.missing_gallery_references.length + ' Gallery record(s) reference missing or unsafe image paths. Review the records before publishing.');
  }
  if (notices.length) {
    const list = make('ul');
    notices.forEach((notice) => list.append(make('li', '', notice)));
    warningList.append(list);
  }

  function render() {
    const query = search.value.trim().toLowerCase();
    const selectedCategory = category.value;
    const selectedUsage = usage.value;
    const filtered = assets.filter((asset) => {
      const galleryEntries = asset.gallery_entries || [];
      const inCategory = selectedCategory === 'all'
        || (selectedCategory === 'other' ? galleryEntries.length === 0 : galleryEntries.some((entry) => entry.category === selectedCategory));
      if (!inCategory) return false;
      const isDuplicate = duplicatePathSet.has(asset.path);
      const optimizationFlags = Array.isArray(asset.optimization_flags) ? asset.optimization_flags : [];
      const stateMatches = selectedUsage === 'all'
        || (selectedUsage === 'duplicate' && isDuplicate)
        || (selectedUsage === 'large-file' && optimizationFlags.includes('large-file'))
        || (selectedUsage === 'large-dimensions' && optimizationFlags.includes('large-dimensions'))
        || (selectedUsage === 'optimization-review' && optimizationFlags.length > 0)
        || (['referenced', 'no-literal-reference', 'responsive-derivative'].includes(selectedUsage) && asset.usage_state === selectedUsage);
      if (!stateMatches) return false;
      const haystack = [
        asset.path,
        ...(asset.usage_files || []),
        ...galleryEntries.map((entry) => [entry.id, entry.category, entry.status].join(' ')),
      ].join(' ').toLowerCase();
      return !query || haystack.includes(query);
    });

    document.getElementById('result-count').textContent =
      number(filtered.length) + ' of ' + number(assets.length) + ' image files';
    grid.replaceChildren();

    if (!filtered.length) {
      grid.append(make('p', 'empty', 'No images match these filters.'));
      return;
    }

    filtered.forEach((asset) => {
      const card = make('article', 'asset-card');
      card.dataset.mediaCard = 'true';
      const preview = make('div', 'preview');
      const image = document.createElement('img');
      image.src = '/' + asset.path;
      image.alt = '';
      image.loading = 'lazy';
      image.decoding = 'async';
      image.addEventListener('error', () => {
        image.remove();
        preview.append(make('span', 'preview-fallback', 'Preview unavailable'));
      }, { once: true });
      preview.append(image);
      card.append(preview);

      const body = make('div', 'asset-body');
      const head = make('div', 'asset-head');
      const title = make('h2', '', asset.path.split('/').pop());
      head.append(title);

      const galleryEntries = asset.gallery_entries || [];
      if (galleryEntries.length) {
        const statuses = Array.from(new Set(galleryEntries.map((entry) => entry.status)));
        const tag = make('span', 'tag ' + (statuses.length === 1 ? statuses[0] : ''), statuses.join(' / '));
        head.append(tag);
      } else if (asset.usage_state === 'responsive-derivative') {
        head.append(make('span', 'tag', 'Derivative'));
      } else if (asset.usage_state === 'no-literal-reference') {
        head.append(make('span', 'tag', 'No literal path'));
      } else {
        head.append(make('span', 'tag live', 'Referenced'));
      }
      body.append(head);
      body.append(make('p', 'asset-path', asset.path));

      const meta = make('div', 'meta');
      meta.append(make('span', '', bytes(asset.bytes)));
      meta.append(make('span', '', asset.width && asset.height ? asset.width + ' × ' + asset.height : asset.extension.toUpperCase()));
      meta.append(make('span', '', number(asset.usage_count) + ' source file(s)'));
      if (optimizationFlags.includes('large-file')) meta.append(make('span', 'tag warning', 'Large file'));
      if (optimizationFlags.includes('large-dimensions')) meta.append(make('span', 'tag warning', 'Large dimensions'));
      if (asset.is_duplicate) meta.append(make('span', '', 'Exact duplicate'));
      body.append(meta);

      if (galleryEntries.length) {
        galleryEntries.forEach((entry) => {
          const label = make('p', 'asset-path', 'Gallery · ' + entry.category + ' · ' + entry.id);
          body.append(label);
        });
      }

      if (asset.derived_from) {
        body.append(make('p', 'asset-path', 'Generated from ' + asset.derived_from));
      }

      const details = make('details', 'uses');
      details.append(make('summary', '', asset.usage_count
        ? 'Detected in ' + number(asset.usage_count) + ' source file(s)'
        : (asset.derived_from ? 'Managed responsive variant' : 'No literal reference detected')));
      if ((asset.usage_files || []).length) {
        const list = make('ul');
        asset.usage_files.slice(0, 8).forEach((path) => list.append(make('li', '', path)));
        if (asset.usage_files.length > 8) list.append(make('li', '', '+' + number(asset.usage_files.length - 8) + ' more source files'));
        details.append(list);
      } else if (asset.derived_from) {
        details.append(make('p', 'asset-path', 'Usage is inherited from the original image source and responsive-image resolver.'));
      } else {
        details.append(make('p', 'asset-path', 'This is not a deletion recommendation. Check dynamic references and the full route inventory first.'));
      }
      body.append(details);

      card.append(body);
      grid.append(card);
    });
  }

  search.addEventListener('input', render);
  category.addEventListener('change', render);
  usage.addEventListener('change', render);
  render();
})();
</script>
</body>
</html>
