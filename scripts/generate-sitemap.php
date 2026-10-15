<?php
/**
 * Generate sitemap.xml from explicit public pages and live experience data.
 *
 * Usage:
 *   php scripts/generate-sitemap.php       # write sitemap.xml
 *   php scripts/generate-sitemap.php --check # fail if sitemap.xml is stale
 *
 * Each lastmod is the latest Git committer date that changed that URL's PHP
 * source file. Run only in a full-history Git checkout; production serves the
 * committed sitemap.xml and does not need Git or shell execution.
 */
$root = dirname(__DIR__);
$config = require $root . '/include/site-config.php';
$experiences = require $root . '/include/experience-data.php';
$siteUrl = rtrim($config['site_url'] ?? '', '/');

if ($siteUrl === '' || !preg_match('#^https://#i', $siteUrl)) {
    fwrite(STDERR, "ERROR site-config.php must provide an absolute HTTPS site_url\n");
    exit(1);
}

// Non-experience pages remain an explicit reviewed allowlist. Do not infer
// publication from every PHP file: mail, thank-you, tariff and future drafts
// must never enter the sitemap automatically.
$staticPages = [
    ['path' => '/', 'priority' => '1.0'],
    ['path' => '/room.php', 'priority' => '0.9'],
    ['path' => '/pack.php', 'priority' => '0.9'],
    ['path' => '/direction.php', 'priority' => '0.9'],
    ['path' => '/roof.php', 'priority' => '0.8'],
    ['path' => '/restaurant.php', 'priority' => '0.8'],
    ['path' => '/gallery.php', 'priority' => '0.8'],
    ['path' => '/about.php', 'priority' => '0.7'],
    ['path' => '/contact.php', 'priority' => '0.8'],
    ['path' => '/blog.php', 'priority' => '0.6'],
    ['path' => '/book-rooms-in-jaisalmer.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-fort-guide.php', 'priority' => '0.7'],
    ['path' => '/desert-safari-guide.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-2-day-itinerary.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-3-day-itinerary.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-honeymoon-guide.php', 'priority' => '0.7'],
    ['path' => '/camping.php', 'priority' => '0.5'],
    ['path' => '/car-rental.php', 'priority' => '0.5'],
    ['path' => '/city-tour.php', 'priority' => '0.5'],
    ['path' => '/car-tours.php', 'priority' => '0.8'],
];

$urls = [];
foreach ($staticPages as $page) {
    $urls[$page['path']] = $page['priority'];
}

foreach ($experiences as $slug => $experience) {
    $status = $experience['status'] ?? 'live';
    if (!in_array($status, ['live', 'draft', 'hidden'], true)) {
        fwrite(STDERR, 'ERROR invalid publication status for experience: ' . (string) $slug . "\\n");
        exit(1);
    }
    if (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        fwrite(STDERR, "ERROR invalid experience slug in experience-data.php\n");
        exit(1);
    }
    if (($experience['slug'] ?? null) !== $slug) {
        fwrite(STDERR, 'ERROR experience key/slug mismatch: ' . $slug . "\\n");
        exit(1);
    }
    if ($status !== 'live') {
        continue;
    }
    if (!is_file($root . '/' . $slug . '.php')) {
        fwrite(STDERR, 'ERROR live experience has no page file: ' . $slug . '.php' . "\\n");
        exit(1);
    }
    $existingPriorities = [
        'desert-safari' => '0.9',
        'guided-jaisalmer-tour' => '0.9',
        'half-day-jaisalmer-tour' => '0.8',
        'full-day-jaisalmer-tour' => '0.8',
        'sunset-camel-safari' => '0.8',
        'sleep-under-the-stars' => '0.8',
        'cultural-safari' => '0.7',
        'swiss-tent-safari' => '0.8',
        'explore-the-desert' => '0.7',
    ];
    $priority = $existingPriorities[$slug] ?? '0.7';
    $urls['/' . $slug . '.php'] = $priority;
}

$lastmodForSource = static function ($relativePath) use ($root) {
    $filePath = $root . '/' . $relativePath;
    if (!is_file($filePath)) {
        fwrite(STDERR, 'ERROR cannot derive sitemap lastmod; source file is missing: ' . $relativePath . PHP_EOL);
        exit(1);
    }
    if (!function_exists('exec')) {
        fwrite(STDERR, "ERROR git lastmod generation requires the PHP CLI exec function and Git.\n");
        exit(1);
    }

    $command = 'git -C ' . escapeshellarg($root)
        . ' log -1 --format=%cs -- ' . escapeshellarg($relativePath);
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);
    $date = trim(implode("\n", $output));

    if (
        $exitCode !== 0
        || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)
        || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
    ) {
        fwrite(STDERR, 'ERROR no trustworthy Git lastmod date for ' . $relativePath . PHP_EOL);
        exit(1);
    }

    return $date;
};

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $path => $priority) {
    $sourceFile = $path === '/' ? 'index.php' : ltrim($path, '/');
    $lastmod = $lastmodForSource($sourceFile);
    $loc = htmlspecialchars($siteUrl . ($path === '/' ? '/' : $path), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $xml .= '  <url><loc>' . $loc . '</loc><lastmod>' . $lastmod . '</lastmod><priority>' . $priority . "</priority></url>\n";
}
$xml .= "</urlset>\n";

$sitemapPath = $root . '/sitemap.xml';
if (in_array('--check', $argv, true)) {
    $current = is_file($sitemapPath) ? file_get_contents($sitemapPath) : false;
    if (!is_string($current) || $current !== $xml) {
        fwrite(STDERR, "ERROR sitemap.xml is stale. Run: php scripts/generate-sitemap.php\n");
        exit(1);
    }
    echo 'PASS sitemap.xml matches the reviewed static page allowlist and live experience data (' . count($urls) . " URLs).\n";
    exit(0);
}

if (file_put_contents($sitemapPath, $xml) === false) {
    fwrite(STDERR, "ERROR could not write sitemap.xml\n");
    exit(1);
}
echo 'Wrote sitemap.xml with ' . count($urls) . " URLs.\n";
