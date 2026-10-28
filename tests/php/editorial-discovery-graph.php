<?php
/**
 * Regression contract for editorial taxonomy and cross-guide discovery.
 * Compatible with the site's PHP 7.4 CI matrix.
 */
$root = dirname(__DIR__, 2);
$guides = require $root . '/include/editorial-guides-data.php';
$topics = require $root . '/include/editorial-topic-taxonomy.php';
$archive = is_file($root . '/blog.php') ? file_get_contents($root . '/blog.php') : false;
$renderer = is_file($root . '/include/editorial-guide-page.php') ? file_get_contents($root . '/include/editorial-guide-page.php') : false;
$failures = [];
$assert = static function ($condition, $message) use (&$failures) {
    if (!$condition) $failures[] = $message;
};
$assert(is_array($guides), 'Editorial registry must return an array.');
$assert(is_array($topics) && count($topics) >= 4, 'Editorial topic taxonomy must define at least four topic groups.');
if (!is_array($guides) || !is_array($topics) || !is_string($archive) || !is_string($renderer)) {
    fwrite(STDERR, "FAIL editorial discovery graph: required registry or template is unavailable.\n");
    exit(1);
}
foreach ($topics as $topicId => $topicLabel) {
    $assert(is_string($topicId) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $topicId), 'Topic IDs must be URL-safe archive fragment identifiers.');
    $assert(is_string($topicLabel) && trim($topicLabel) !== '', 'Every topic ID must have a visible label: ' . (string) $topicId);
}
$liveGuides = [];
$topicCounts = array_fill_keys(array_keys($topics), 0);
$relatedEdges = 0;
foreach ($guides as $slug => $guide) {
    if (!is_string($slug) || !is_array($guide)) {
        $assert(false, 'Editorial registry entry must be a slug-keyed array.');
        continue;
    }
    if (($guide['status'] ?? 'live') !== 'live') continue;
    $liveGuides[$slug] = $guide;
    $topicId = (string) ($guide['topic'] ?? 'trip-planning');
    $assert(isset($topics[$topicId]), 'Live guide has an unknown editorial topic: ' . $slug . ' → ' . $topicId);
    if (isset($topicCounts[$topicId])) $topicCounts[$topicId]++;
    $assert(is_file($root . '/' . $slug . '.php') && !is_link($root . '/' . $slug . '.php'), 'Live editorial route must resolve to a regular local PHP file: ' . $slug);
}
foreach ($topicCounts as $topicId => $count) {
    $assert($count > 0, 'Every topic taxonomy entry must contain at least one live guide: ' . $topicId);
}
foreach ($liveGuides as $slug => $guide) {
    $destinations = [];
    foreach (($guide['related'] ?? []) as $item) {
        if (!is_array($item) || !is_string($item['href'] ?? null)) continue;
        $href = trim($item['href']);
        if ($href === '' || preg_match('#^(?:https?:)?//#i', $href) || strpos($href, '@') === 0) continue;
        $path = parse_url($href, PHP_URL_PATH);
        if (!is_string($path) || substr($path, -4) !== '.php') continue;
        $destination = basename($path, '.php');
        if (!isset($liveGuides[$destination])) {
            if (isset($guides[$destination]) && ($guides[$destination]['status'] ?? 'live') !== 'live') {
                $assert(false, 'Live guide must not point its related cards at a non-live editorial destination: ' . $slug . ' → ' . $destination);
            }
            continue;
        }
        $assert($destination !== $slug, 'A guide must not link to itself in related cards: ' . $slug);
        $assert(!isset($destinations[$destination]), 'Duplicate related editorial destination: ' . $slug . ' → ' . $destination);
        $destinations[$destination] = true;
        $relatedEdges++;
    }
}
$assert($relatedEdges > 0, 'Published editorial guides must have a connected related-guide graph.');
$assert(strpos($archive, "require __DIR__ . '/include/editorial-topic-taxonomy.php'") !== false, 'Journal archive must use the shared editorial taxonomy.');
$assert(strpos($archive, '$journalTopicAnchors') !== false && strpos($archive, '$editorialTopicTaxonomy as $topicSlug => $topicLabel') !== false, 'Journal topic navigation must be derived from live records.');
$assert(strpos($archive, '$journalTopicAnchors[$cardTopic]') !== false, 'Topic anchors must target real guide cards rather than duplicate/placeholder cards.');
$assert(strpos($renderer, '$editorialGuides as $candidateSlug => $candidateGuide') !== false, 'Related discovery candidates must be generated from the editorial registry.');
$assert(strpos($renderer, "'same_topic' => " . '$candidateTopic === $guideTopic') !== false, 'Related candidates must carry topic relevance for ranking.');
$assert(strpos($renderer, "return " . '$left[\'same_topic\'] ? -1 : 1;') !== false, 'Related guides must prioritise same-topic candidates.');
$assert(strpos($renderer, 'data-topic="<?= $escapeGuide($guideTopic) ?>"') !== false, 'Article discovery nav must expose the current topic.');
$assert(strpos($renderer, "['href' => 'jaisalmer-fort-guide.php'") === false, 'Related discovery must not regress to a hard-coded guide list.');
if ($failures) {
    fwrite(STDERR, 'FAIL editorial discovery graph (' . count($failures) . ' issue(s)):' . PHP_EOL);
    foreach ($failures as $failure) fwrite(STDERR, ' - ' . $failure . PHP_EOL);
    exit(1);
}
echo 'PASS editorial discovery graph: ' . count($liveGuides) . ' live guides, ' . count($topics) . ' taxonomy topics, ' . $relatedEdges . ' related editorial edges.' . PHP_EOL;
