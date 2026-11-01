<?php
/** Contract for the shared on-demand Google Maps preview and its route wiring. */
$root = dirname(__DIR__, 2);
$errors = [];

$renderMapPreview = static function ($config) use ($root) {
    $mapPreview = $config;
    ob_start();
    require $root . '/include/ui/map-preview.php';
    return (string) ob_get_clean();
};

$valid = [
    'class' => 'arrival-map-frame',
    'title' => 'Fort map <script>alert("not markup")</script>',
    'description' => 'Open the map only when needed.',
    'embed_url' => 'https://www.google.com/maps?q=Jaisalmer&output=embed',
    'frame_title' => 'Map of Garh Jaisal Haveli inside Jaisalmer Fort',
];
$html = $renderMapPreview($valid);
foreach ([
    'class="arrival-map-frame map-preview"',
    'data-map-preview',
    'data-map-preview-label',
    'Load interactive map',
    'data-map-src="https://www.google.com/maps?q=Jaisalmer&amp;output=embed"',
    '&lt;script&gt;alert(&quot;not markup&quot;)&lt;/script&gt;',
    '<noscript>',
] as $needle) {
    if (strpos($html, $needle) === false) {
        $errors[] = 'rendered map preview is missing: ' . $needle;
    }
}
if (preg_match('/<iframe\b[^>]*\ssrc=/i', $html)) {
    $errors[] = 'map iframe must not have a src before explicit user activation';
}
if (strpos($html, '<script>') !== false) {
    $errors[] = 'map preview text must be escaped';
}

$unsafeClass = $valid;
$unsafeClass['class'] = 'arrival-map-frame" onclick="alert(1)';
$unsafeClassHtml = $renderMapPreview($unsafeClass);
if (strpos($unsafeClassHtml, 'onclick=') !== false || strpos($unsafeClassHtml, 'class="map-preview"') === false) {
    $errors[] = 'invalid wrapper classes must fall back to the shared map-preview class';
}

$unsafeHost = $valid;
$unsafeHost['embed_url'] = 'https://www.google.com.evil.example/maps?q=Jaisalmer';
if ($renderMapPreview($unsafeHost) !== '') {
    $errors[] = 'non-Google embed hosts must not render an iframe';
}

foreach ([
    'direction.php' => ['include/ui/map-preview.php', 'css/map-preview.css', 'js/map-preview.js', 'arrival-map-frame'],
    'restaurant.php' => ['include/ui/map-preview.php', 'css/map-preview.css', 'js/map-preview.js', 'restaurant-map-frame'],
    'roof.php' => ['restaurant.php#reserve', 'enquiry-rooftop-table', 'Enquire about a table'],
] as $path => $needles) {
    $source = is_file($root . '/' . $path) ? file_get_contents($root . '/' . $path) : false;
    if (!is_string($source)) {
        $errors[] = $path . ' is missing or unreadable';
        continue;
    }
    foreach ($needles as $needle) {
        if (strpos($source, $needle) === false) {
            $errors[] = $path . ' is missing route marker: ' . $needle;
        }
    }
}

$scriptPath = $root . '/js/map-preview.js';
$script = is_file($scriptPath) ? file_get_contents($scriptPath) : false;
if (!is_string($script) || strpos($script, "setAttribute('src'") === false || strpos($script, "details.open") === false) {
    $errors[] = 'map preview script must attach the iframe src only from the open details state';
}
if (is_string($script) && (strpos($script, 'fetch(') !== false || strpos($script, 'XMLHttpRequest') !== false)) {
    $errors[] = 'map preview script must not create a background network request';
}

$cssPath = $root . '/css/map-preview.css';
$css = is_file($cssPath) ? file_get_contents($cssPath) : false;
if (!is_string($css)) {
    $errors[] = 'css/map-preview.css is missing or unreadable';
} else {
    foreach (['.map-preview__summary', '.map-preview__frame iframe', ':focus-visible', '@media (max-width: 600px)', '@media (prefers-reduced-motion: reduce)'] as $needle) {
        if (strpos($css, $needle) === false) {
            $errors[] = 'map preview stylesheet is missing: ' . $needle;
        }
    }
}

if ($errors) {
    fwrite(STDERR, "FAIL map preview contract:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}
echo "PASS on-demand map preview and route contract.\n";
