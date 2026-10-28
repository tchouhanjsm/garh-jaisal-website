<?php
/**
 * Render the route-specific planning path from the shared reviewed registry.
 * The component is included inside each public page's main landmark.
 */
$planningPathRegistry = require __DIR__ . '/../planning-paths-data.php';
$planningPathRoute = isset($page['path']) && is_string($page['path']) ? $page['path'] : '';
$planningPath = $planningPathRegistry[$planningPathRoute] ?? null;

if (!is_array($planningPath)) {
    return;
}

$planningPathTitle = trim((string) ($planningPath['title'] ?? ''));
$planningPathIntro = trim((string) ($planningPath['intro'] ?? ''));
$planningPathLinks = $planningPath['links'] ?? [];

if ($planningPathTitle === '' || $planningPathIntro === '' || !is_array($planningPathLinks) || count($planningPathLinks) !== 3) {
    throw new InvalidArgumentException('Planning path requires a title, introduction and exactly three links.');
}

$escapePlanningPath = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$seenPlanningHrefs = [];
foreach ($planningPathLinks as $link) {
    if (!is_array($link)) {
        throw new InvalidArgumentException('Each planning path link must be an array.');
    }
    $label = trim((string) ($link['label'] ?? ''));
    $href = trim((string) ($link['href'] ?? ''));
    $description = trim((string) ($link['description'] ?? ''));
    if ($label === '' || $description === '' || preg_match('/^[a-z0-9-]+\.php$/iD', $href) !== 1) {
        throw new InvalidArgumentException('Planning path links require copy and a same-site PHP destination.');
    }
    if (in_array($href, $seenPlanningHrefs, true) || '/' . $href === $planningPathRoute) {
        throw new InvalidArgumentException('Planning path destinations must be distinct and must not link to the current route.');
    }
    $seenPlanningHrefs[] = $href;
}
?>
<section class="planning-path section" aria-labelledby="planning-path-title">
  <div class="site-shell">
    <div class="planning-path-intro">
      <p class="eyebrow">Continue planning</p>
      <h2 id="planning-path-title"><?= $escapePlanningPath($planningPathTitle) ?></h2>
      <p><?= $escapePlanningPath($planningPathIntro) ?></p>
    </div>
    <nav class="planning-path-grid" aria-label="Related planning pages">
      <?php foreach ($planningPathLinks as $index => $link): ?>
        <a class="planning-path-card" href="<?= $escapePlanningPath(trim((string) $link['href'])) ?>" data-planning-path-link data-planning-path-position="<?= (int) ($index + 1) ?>">
          <span class="planning-path-index" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></span>
          <span class="planning-path-copy">
            <strong><?= $escapePlanningPath(trim((string) $link['label'])) ?></strong>
            <small><?= $escapePlanningPath(trim((string) $link['description'])) ?></small>
          </span>
          <span class="planning-path-arrow" aria-hidden="true">↗</span>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
</section>
<?php unset($planningPathRegistry, $planningPathRoute, $planningPath, $planningPathTitle, $planningPathIntro, $planningPathLinks, $escapePlanningPath, $seenPlanningHrefs, $link, $label, $href, $description); ?>
