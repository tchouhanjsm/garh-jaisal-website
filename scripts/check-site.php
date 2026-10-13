<?php
/**
 * Local + CI checks for the plain-PHP website.
 *
 * Usage:
 *   php scripts/check-site.php
 *   php scripts/check-site.php --strict-images
 *
 * Errors fail the build. Warnings are reported separately and do not fail it.
 */
$root = dirname(__DIR__);
$strictImages = in_array('--strict-images', $argv, true);
$errors = [];
$warnings = [];

$public = array_values(array_filter(
    glob($root . '/*.php') ?: [],
    static function ($file) {
        return !in_array(basename($file), ['mail.php', 'thank-you.php', 'tariff.php'], true);
    }
));

$readFile = static function ($path) {
    $content = file_get_contents($path);
    return is_string($content) ? $content : '';
};

$addUnique = static function (&$list, $message) {
    if (!in_array($message, $list, true)) {
        $list[] = $message;
    }
};

$experienceRendererSource = $readFile($root . '/include/experience-page.php');
$rendererUsesSharedHead = strpos($experienceRendererSource, 'page-head.php') !== false;

// 1. Basic SEO tags. Pages using the shared renderer are validated through
// their configuration contract and rendered-browser regression tests.
foreach ($public as $file) {
    $name = basename($file);
    $source = $readFile($file);
    $usesExperienceRenderer = strpos($source, 'experience-page.php') !== false && $rendererUsesSharedHead;
    $usesSharedHead = strpos($source, 'include/page-head.php') !== false || $usesExperienceRenderer;
    $missing = [];

    if (!$usesSharedHead) {
        foreach ([
            '/<title\b/i' => 'title',
            '/<meta\s+name=["\']description["\']/i' => 'meta description',
            '/<link\s+rel=["\']canonical["\']/i' => 'canonical',
        ] as $pattern => $label) {
            if (!preg_match($pattern, $source)) {
                $missing[] = $label;
            }
        }

        if ($missing) {
            $message = $name . ': ' . implode(', ', $missing) . ' not found literally; verify rendered head output';
            $addUnique($warnings, $message);
        }
    }

    if (!$usesExperienceRenderer) {
        $h1Count = preg_match_all('/<h1\b/i', $source);
        if ($h1Count !== false && $h1Count > 1) {
            $addUnique($warnings, $name . ': multiple literal H1 elements found');
        } elseif ($h1Count === 0) {
            $addUnique($warnings, $name . ': no literal H1 found; verify rendered page');
        }
    }
}

// All public pages must now use the shared page-head renderer directly or
// inherit it from include/experience-page.php. Rendered output is tested in CI.
$requireSharedPageHead = true;
foreach ($public as $file) {
    $name = basename($file);
    $source = $readFile($file);
    $usesSharedHead = strpos($source, 'include/page-head.php') !== false;
    if (!$usesSharedHead && strpos($source, 'experience-page.php') !== false && $rendererUsesSharedHead) {
        $usesSharedHead = true;
    }

    if (!$usesSharedHead) {
        $message = $name . ': public page does not use the shared page-head renderer';
        if ($requireSharedPageHead) {
            $errors[] = $message;
        } else {
            $addUnique($warnings, $message);
        }
    }
}

// 2. Sitemap references must point to local files when a URL is a site path.
$sitemapPath = $root . '/sitemap.xml';
$sitemap = is_file($sitemapPath) ? $readFile($sitemapPath) : '';
if ($sitemap === '') {
    $errors[] = 'sitemap.xml is missing or empty';
} else {
    preg_match_all('#<loc>https?://[^/]+/([^<]*)</loc>#i', $sitemap, $matches);
    $sitemapPaths = $matches[1] ?? [];

    foreach ($sitemapPaths as $path) {
        $path = trim($path);
        if ($path === '') {
            continue;
        }
        $decoded = rawurldecode($path);
        if (strpos($decoded, '..') !== false || strpos($decoded, '\\') !== false) {
            $errors[] = 'sitemap.xml contains an unsafe local path: ' . $path;
            continue;
        }
        if (!is_file($root . '/' . ltrim($decoded, '/'))) {
            $errors[] = 'sitemap.xml lists missing file: ' . $path;
        }
    }

    foreach ($public as $file) {
        $name = basename($file);
        if ($name !== 'index.php' && !in_array($name, $sitemapPaths, true)) {
            $addUnique($warnings, $name . ': public page not listed in sitemap.xml');
        }
    }

    if (strpos($sitemap, '<lastmod>') === false) {
        $addUnique($warnings, 'sitemap.xml has no <lastmod>; add only dates supported by repository history');
    }
}

// 3. Literal local href/src references. External URLs, fragments, query values,
// and PHP-generated dynamic URLs need browser smoke tests rather than guessing.
$scanFiles = array_values(array_filter(
    array_merge(
        glob($root . '/*.php') ?: [],
        glob($root . '/include/*.php') ?: []
    ),
    static function ($file) use ($root) {
        // Legacy files are retained for reference during Phase 0. They are not
        // used by the V2 page templates and are audited before removal.
        return !in_array(basename($file), ['header.php', 'footer.php', 'header-v2.backup.php'], true);
    }
));
$referencePattern = '/(?:href|src)\s*=\s*["\']([^"\']+)["\']/i';
$localExtensions = '/\.(?:php|css|js|png|jpe?g|webp|svg|ico|gif|woff2?|ttf|xml)(?:[?#].*)?$/i';

foreach ($scanFiles as $file) {
    $source = $readFile($file);
    if (!preg_match_all($referencePattern, $source, $refs)) {
        continue;
    }

    foreach (array_unique($refs[1]) as $reference) {
        $reference = trim($reference);
        if ($reference === '' || $reference[0] === '#' || $reference[0] === '?' ||
            preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $reference) ||
            strpos($reference, '<?') !== false ||
            !preg_match($localExtensions, $reference)) {
            continue;
        }

        $path = preg_replace('/[?#].*$/', '', $reference);
        if (!is_string($path) || $path === '') {
            continue;
        }
        $decoded = rawurldecode($path);
        if (strpos($decoded, '..') !== false || strpos($decoded, '\\') !== false) {
            $errors[] = basename($file) . ': unsafe local reference ' . $reference;
            continue;
        }

        $target = $root . '/' . ltrim($decoded, '/');
        if (!is_file($target)) {
            $message = basename($file) . ': broken local ref ' . $reference;
            if ($strictImages || !preg_match('/\.(?:png|jpe?g|webp|gif|svg|ico)$/i', $decoded)) {
                $addUnique($errors, $message);
            } else {
                $addUnique($warnings, $message);
            }
        }
    }
}

// 4. Confirm the pages expected to use the shared renderer actually do so.
// The other two experience-related landing pages are intentionally standalone.
$sharedExperiencePages = [
    'sunset-camel-safari.php' => 'sunset-camel-safari',
    'sleep-under-the-stars.php' => 'sleep-under-the-stars',
    'cultural-safari.php' => 'cultural-safari',
    'swiss-tent-safari.php' => 'swiss-tent-safari',
    'explore-the-desert.php' => 'explore-the-desert',
];
foreach ($sharedExperiencePages as $relative => $expectedSlug) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        $errors[] = 'required experience page missing: ' . $relative;
        continue;
    }
    $source = $readFile($path);
    if (strpos($source, 'experience-page.php') === false) {
        $errors[] = $relative . ': expected shared experience renderer';
    }
    if (!preg_match('/\$experienceSlug\s*=\s*[\'"]' . preg_quote($expectedSlug, '/') . '[\'"]/', $source)) {
        $errors[] = $relative . ': expected experience slug ' . $expectedSlug;
    }
}
$standaloneExperiencePages = ['camping.php', 'city-tour.php'];
foreach ($standaloneExperiencePages as $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        $errors[] = 'required experience landing page missing: ' . $relative;
        continue;
    }
    $source = $readFile($path);
    if (strpos($source, 'include/page-head.php') === false) {
        $errors[] = $relative . ': standalone landing page must use the shared page-head renderer';
    }
}
$rendererPath = $root . '/include/experience-page.php';
if (!is_file($rendererPath)) {
    $errors[] = 'include/experience-page.php is missing';
} else {
    $renderer = $readFile($rendererPath);
    foreach ([
        'page-head.php',
        "'title' => $experience['title']",
        "'description' => $experience['description']",
        "'path' => '/' . $experience['slug'] . '.php'",
        "'robots' => $isIndexableExperience ? 'index,follow' : 'noindex,follow'",
    ] as $marker) {
        if (strpos($renderer, $marker) === false) {
            $errors[] = 'experience renderer is missing shared-head contract marker: ' . $marker;
        }
    }
}

// 5. Configuration and junk checks. site-config.example.php may contain
// clearly labelled placeholders; the local secrets file must never be tracked.
$forbiddenFiles = [
    'include/site-config.local.php',
    'error_log',
];
foreach ($forbiddenFiles as $relative) {
    if (is_file($root . '/' . $relative) && getenv('CI')) {
        $errors[] = $relative . ' exists in CI workspace; ensure local configuration is not committed';
    }
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $entry) {
    if (!$entry->isFile()) {
        continue;
    }
    $path = $entry->getPathname();
    $relative = ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);
    if (strpos($relative, '.git' . DIRECTORY_SEPARATOR) === 0) {
        continue;
    }

    if (preg_match('/(?:^|\/)(?:\.DS_Store|Thumbs\.db|error_log)$/i', $relative) ||
        preg_match('/\.(?:backup|bak)$/i', $relative) ||
        preg_match('/(?:^|\/)loader\.html$/i', $relative)) {
        $addUnique($warnings, 'repository hygiene candidate: ' . $relative);
    }

    if (preg_match('/\.(?:php|js|txt|md|yml|yaml|json)$/i', $relative)) {
        $contents = $readFile($path);
        $looksLikeSecret = preg_match(
            '/(?:secret|password|passwd|api[_-]?key|access[_-]?token)\s*[\'"]?\s*[:=>]+\s*[\'"][^\'"\s]{12,}/i',
            $contents
        );
        if ($looksLikeSecret && basename($path) !== 'check-site.php' &&
            strpos($relative, 'example') === false && strpos($relative, 'template') === false) {
            $addUnique($errors, 'possible hard-coded secret pattern in ' . $relative . '; inspect manually');
        }
    }
}

// 6. Accessibility and safe external-link diagnostics. These are warnings
// because PHP templates may construct attributes dynamically; verify rendered HTML.
$markupFiles = array_values(array_filter(
    array_merge(glob($root . '/*.php') ?: [], glob($root . '/include/*.php') ?: []),
    static function ($file) {
        return !in_array(basename($file), ['header.php', 'footer.php', 'header-v2.backup.php'], true);
    }
));
foreach ($markupFiles as $file) {
    $source = $readFile($file);
    $name = basename($file);
    if (preg_match_all('/<img\\b([^>]*)>/i', $source, $images, PREG_SET_ORDER)) {
        foreach ($images as $image) {
            if (!preg_match('/\\balt\\s*=/i', $image[1])) {
                $addUnique($warnings, $name . ': image without a literal alt attribute; verify rendered markup');
                break;
            }
        }
    }
    if (preg_match_all('/<a\\b([^>]*)>/i', $source, $anchors, PREG_SET_ORDER)) {
        foreach ($anchors as $anchor) {
            if (preg_match('/\\btarget\\s*=\\s*(?:"_blank"|\\x27_blank\\x27)/i', $anchor[1]) &&
                !preg_match('/\\brel\\s*=/i', $anchor[1])) {
                $addUnique($warnings, $name . ': target="_blank" link without literal rel attribute; verify rendered markup');
                break;
            }
        }
    }
}

foreach ($warnings as $warning) {
    echo 'WARN  ' . $warning . PHP_EOL;
}
foreach ($errors as $error) {
    echo 'ERROR ' . $error . PHP_EOL;
}
printf("\n%d error(s), %d warning(s)\n", count($errors), count($warnings));
exit($errors ? 1 : 0);
