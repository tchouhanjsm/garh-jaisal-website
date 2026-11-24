<?php
/**
 * Shared policy layout. The calling page supplies the title, breadcrumbs and
 * a local content partial.
 */
?>
<main class="article-page policy-page" id="main-content" tabindex="-1">
  <header class="article-hero">
    <div class="site-shell article-hero-inner">
      <?php require __DIR__ . '/breadcrumb.php'; ?>
      <h1><?= htmlspecialchars($policyTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
  </header>
  <section class="section">
    <div class="site-shell">
      <article class="article-content">
        <?php require $policyContentPath; ?>
      </article>
    </div>
  </section>
</main>
