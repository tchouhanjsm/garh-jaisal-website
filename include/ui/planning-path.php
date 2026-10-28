<?php
/**
 * Render a contextual three-link planning path for a published route.
 *
 * The calling page supplies $planningPath with a title, intro and three
 * same-site PHP destinations. Links are validated and all output is escaped.
 */
if (!isset($planningPath) || !is_array($planningPath)) {
    throw new InvalidArgumentException('Planning path requires a configuration array.');
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

foreach ($planningPathLinks as $link) {
    if (!is_array($link)) {
        throw new InvalidArgumentException('Each planning path link must be an array.');
    }
    $label = trim((string) ($link['label'] ?? ''));
    $href = trim((string) ($link['href'] ?? ''));
    $description = trim((string) ($link['description'] ?? ''));
    if (
        $label === ''
        || $description === ''
        || preg_match('/^[a-z0-9-]+\.php(?:\?[a-z0-9_=&%-]+)?$/iD', $href) !== 1
    ) {
        throw new InvalidArgumentException('Planning path links must have copy and a valid same-site PHP destination.');
    }
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
        <a class="planning-path-card" href="<?= $escapePlanningPath(trim((string) $link['href'])) ?>">
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
