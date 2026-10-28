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
$editorialGuides = require $root . '/include/editorial-guides-data.php';
$studioPages = require $root . '/include/studio-pages-data.php';
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
    ['path' => '/catalogue.php', 'priority' => '0.8'],
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
    ['path' => '/best-time-to-visit-jaisalmer.php', 'priority' => '0.7'],
    ['path' => '/how-to-reach-jaisalmer.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-desert-safari-packing-list.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-havelis-guide.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-food-guide.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-beyond-the-fort-guide.php', 'priority' => '0.7'],
    ['path' => '/jaisalmer-markets-and-crafts-guide.php', 'priority' => '0.7'],
    ['path' => '/camping.php', 'priority' => '0.5'],
    ['path' => '/car-rental.php', 'priority' => '0.5'],
    ['path' => '/city-tour.php', 'priority' => '0.5'],
    ['path' => '/car-tours.php', 'priority' => '0.8'],
    ['path' => '/custom-itinerary.php', 'priority' => '0.7'],
];

$urls = [];
foreach ($staticPages as $page) {
    $path = (string) $page['path'];
    $routeSlug = $path === '/' ? '' : basename($path, '.php');
    // A guide present in the static legacy allowlist is still governed by its registry status.
    if (
        $routeSlug !== ''
        && isset($editorialGuides[$routeSlug])
        && ($editorialGuides[$routeSlug]['status'] ?? 'live') !== 'live'
    ) {
        continue;
    }
    $urls[$path] = $page['priority'];
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

// New live editorial guides are included automatically from the same registry
// that renders their pages and archive cards.
foreach ($editorialGuides as $slug => $guide) {
    $status = $guide['status'] ?? 'live';
    if (!in_array($status, ['live', 'draft', 'hidden'], true)) {
        fwrite(STDERR, 'ERROR invalid editorial publication status: ' . (string) $slug . PHP_EOL);
        exit(1);
    }
    if (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) {
        fwrite(STDERR, "ERROR invalid editorial slug in editorial-guides-data.php" . PHP_EOL);
        exit(1);
    }
    if (($guide['slug'] ?? null) !== $slug) {
        fwrite(STDERR, 'ERROR editorial key/slug mismatch: ' . $slug . PHP_EOL);
        exit(1);
    }
    if ($status !== 'live') {
        continue;
    }
    if (!is_file($root . '/' . $slug . '.php')) {
        fwrite(STDERR, 'ERROR live editorial guide has no page file: ' . $slug . '.php' . PHP_EOL);
        exit(1);
    }
    $urls['/' . $slug . '.php'] = '0.7';
}

// Content Studio landing pages are discoverable only when their JSON record is
// explicitly Live and the generated route exists. Draft and hidden pages never
// enter the public sitemap.
foreach ($studioPages as $slug => $studioPage) {
    $status = $studioPage['status'] ?? 'draft';
    if (!in_array($status, ['live', 'draft', 'hidden'], true)) {
        fwrite(STDERR, 'ERROR invalid landing-page publication status: ' . (string) $slug . PHP_EOL);
        exit(1);
    }
    if (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) {
        fwrite(STDERR, "ERROR invalid landing-page slug in studio-pages-data.php" . PHP_EOL);
        exit(1);
    }
    if (($studioPage['slug'] ?? null) !== $slug) {
        fwrite(STDERR, 'ERROR landing-page key/slug mismatch: ' . $slug . PHP_EOL);
        exit(1);
    }
    if ($status !== 'live') {
        continue;
    }
    if (!is_file($root . '/' . $slug . '.php')) {
        fwrite(STDERR, 'ERROR live landing page has no page file: ' . $slug . '.php' . PHP_EOL);
        exit(1);
    }
    $urls['/' . $slug . '.php'] = '0.7';
}

$lastmodForSource = static function ($relativePaths) use ($root) {
    $relativePaths = is_array($relativePaths) ? array_values(array_unique($relativePaths)) : [$relativePaths];
    if (!function_exists('exec')) {
        fwrite(STDERR, "ERROR git lastmod generation requires the PHP CLI exec function and Git.\n");
        exit(1);
    }
    $dates = [];
    foreach ($relativePaths as $relativePath) {
        $filePath = $root . '/' . $relativePath;
        if (!is_file($filePath)) {
            fwrite(STDERR, 'ERROR cannot derive sitemap lastmod; source file is missing: ' . $relativePath . PHP_EOL);
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
        $dates[] = $date;
    }
    rsort($dates, SORT_STRING);
    return $dates[0];
};

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $path => $priority) {
    $sourceFile = $path === '/' ? 'index.php' : ltrim($path, '/');
    $sourceFiles = [$sourceFile, 'include/planning-paths-data.php', 'include/ui/planning-path.php', 'include/contact-topics.php'];
    $routeSlug = $path === '/' ? '' : basename($path, '.php');
    if (array_key_exists($routeSlug, $experiences)) {
        $sourceFiles[] = 'include/experience-data.php';
        $sourceFiles[] = 'include/experience-page.php';
    }
    if (array_key_exists($routeSlug, $editorialGuides)) {
        $sourceFiles[] = 'include/editorial-guides-data.php';
        $sourceFiles[] = 'include/editorial-guide-page.php';
    }
    if (array_key_exists($routeSlug, $studioPages)) {
        $sourceFiles[] = 'include/studio-pages-data.php';
        $sourceFiles[] = 'include/studio-page.php';
    }
    if ($path === '/blog.php') {
        $sourceFiles[] = 'include/editorial-guides-data.php';
    } elseif ($path === '/gallery.php') {
        $sourceFiles[] = 'include/gallery-data.php';
    } elseif ($path === '/pack.php' || $path === '/catalogue.php') {
        $sourceFiles[] = 'include/experience-data.php';
        if ($path === '/catalogue.php') {
            $sourceFiles[] = 'include/catalogue-data.php';
        }
    }

    // Local Content Studio records have explicit dates so a generated sitemap is
    // deterministic before their first Git commit.
    $contentRecordPath = null;
    if ($routeSlug !== '') {
        foreach ([
            'content/experiences/' . $routeSlug . '.json',
            'content/editorial/' . $routeSlug . '.json',
            'content/pages/' . $routeSlug . '.json',
        ] as $candidate) {
            if (is_file($root . '/' . $candidate) && !is_link($root . '/' . $candidate)) {
                $contentRecordPath = $root . '/' . $candidate;
                break;
            }
        }
    }
    $lastmod = null;
    if ($contentRecordPath !== null) {
        $contentRecord = json_decode((string) file_get_contents($contentRecordPath), true);
        $contentDate = is_array($contentRecord)
            ? (string) ($contentRecord['date_modified'] ?? $contentRecord['date_published'] ?? '')
            : '';
        if (
            preg_match('/^\d{4}-\d{2}-\d{2}$/D', $contentDate)
            && checkdate((int) substr($contentDate, 5, 2), (int) substr($contentDate, 8, 2), (int) substr($contentDate, 0, 4))
        ) {
            $lastmod = $contentDate;
        }
    }
    if ($lastmod === null) {
        $lastmod = $lastmodForSource($sourceFiles);
    }
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
