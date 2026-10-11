<?php
// Local + CI site checks. Usage: php scripts/check-site.php [--strict-images]
// Exit 1 on any ERROR. WARN never fails the build.
$root = dirname(__DIR__);
$strictImages = in_array('--strict-images', $argv, true);
$errors = []; $warns = [];
$public = array_values(array_filter(glob("$root/*.php"), fn($f) => !in_array(basename($f), ['mail.php','thank-you.php','tariff.php'])));

// 1. Per-page SEO basics (pages that pull meta from a shared include are accepted)
foreach ($public as $f) {
  $n = basename($f); $s = file_get_contents($f);
  $uses = preg_match('/include|require/', $s);
  $miss = [];
  foreach (['<title>' => 'title', 'name="description"' => 'meta description', 'rel="canonical"' => 'canonical'] as $needle => $label)
    if (strpos($s, $needle) === false) $miss[] = $label;
  if ($miss && !$uses) $errors[] = "$n: missing " . implode(', ', $miss);
  elseif ($miss) $warns[] = "$n: " . implode(', ', $miss) . " not literal (confirm the shared include renders them)";
  if (substr_count($s, '<h1') !== 1 && strpos($s,'<h1')!==false) $warns[] = "$n: expected exactly one <h1>";
}

// 2. Sitemap <-> files
$sm = file_get_contents("$root/sitemap.xml");
preg_match_all('#<loc>https?://[^/]+/([^<]*)</loc>#', $sm, $m);
foreach ($m[1] as $p) if ($p !== '' && !is_file("$root/$p")) $errors[] = "sitemap.xml lists missing file: $p";
foreach ($public as $f) if (!in_array(basename($f), $m[1], true) && basename($f) !== 'index.php') $warns[] = basename($f) . ": public page not in sitemap.xml";
if (strpos($sm, '<lastmod>') === false) $warns[] = "sitemap.xml has no <lastmod>";

// 3. Local href/src targets exist (php, css, js, img)
foreach (array_merge(glob("$root/*.php"), glob("$root/include/*.php")) as $f) {
  $s = file_get_contents($f);
  preg_match_all('/(?:href|src)="([^"#?:]+\.(?:php|css|js|png|jpe?g|webp|svg|ico))"/i', $s, $mm);
  foreach (array_unique($mm[1]) as $u) {
    if (strpos($u, '<?') !== false || strpos($u, '//') === 0) continue;
    if (!is_file("$root/" . ltrim($u, '/'))) {
      $msg = basename($f) . ": broken local ref $u";
      if ($strictImages || !preg_match('/\.(png|jpe?g|webp)$/i', $u)) $errors[] = $msg; else $warns[] = $msg;
    }
  }
}

// 4. Secrets / junk that must not ship
foreach (['include/site-config.local.php','error_log'] as $x) if (is_file("$root/$x") && getenv('CI')) $errors[] = "$x must not be committed";
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $file) {
  $p = $file->getPathname(); if (strpos($p, '/.git/') !== false) continue;
  if (preg_match('/\.(php|js|txt|md)$/', $p) && preg_match('/(?:secret|password|passwd)\s*[\'"]?\s*[:=>]+\s*[\'"][^\'"\s]{8,}/i', file_get_contents($p)) && basename($p) !== 'check-site.php' && !preg_match('/example/', $p))
    $errors[] = "possible hard-coded secret in " . substr($p, strlen($root) + 1);
  if (preg_match('/(\.backup|\.bak|\.DS_Store|Thumbs\.db)$/', $p)) $warns[] = "junk file: " . substr($p, strlen($root) + 1);
}

foreach ($warns as $w) echo "WARN  $w\n";
foreach ($errors as $e) echo "ERROR $e\n";
printf("\n%d error(s), %d warning(s)\n", count($errors), count($warns));
exit($errors ? 1 : 0);
