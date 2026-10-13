<?php
/**
 * Shared renderer for owner-pending policy drafts.
 * Input: $policyDraft = ['title' => ..., 'description' => ..., 'path' => ...,
 *   'eyebrow' => ..., 'h1' => ..., 'lede' => ..., 'sections' => [['heading' => ..., 'items' => [...]]]].
 * The page head should set robots to noindex,follow. Drafts must stay off navigation and sitemap.
 */
$uiPolicyDraft = isset($policyDraft) && is_array($policyDraft) ? $policyDraft : [];
$uiPolicyEscape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => $uiPolicyDraft['breadcrumb'] ?? 'Policy draft'],
];
?>
<main class="article-page policy-draft-page" id="main-content" tabindex="-1">
  <header class="article-hero">
    <div class="site-shell article-hero-inner">
      <?php require __DIR__ . '/breadcrumb.php'; ?>
      <p class="eyebrow"><?= $uiPolicyEscape($uiPolicyDraft['eyebrow'] ?? 'Draft — not approved') ?></p>
      <h1><?= $uiPolicyEscape($uiPolicyDraft['h1'] ?? 'Policy draft') ?></h1>
      <p class="lede"><?= $uiPolicyEscape($uiPolicyDraft['lede'] ?? '') ?></p>
    </div>
  </header>
  <section class="section">
    <div class="site-shell">
      <article class="article-content">
        <p class="policy-draft-notice" role="note">This is an unpublished draft, not an approved statement of hotel policy. It is marked noindex and will remain out of site navigation until the owner confirms the wording.</p>
        <?php foreach (($uiPolicyDraft['sections'] ?? []) as $uiPolicySection): ?>
          <?php if (!is_array($uiPolicySection)) { continue; } ?>
          <section>
            <h2><?= $uiPolicyEscape($uiPolicySection['heading'] ?? '') ?></h2>
            <?php if (!empty($uiPolicySection['intro'])): ?>
              <p><?= $uiPolicyEscape($uiPolicySection['intro']) ?></p>
            <?php endif; ?>
            <?php if (!empty($uiPolicySection['items']) && is_array($uiPolicySection['items'])): ?>
              <ul>
                <?php foreach ($uiPolicySection['items'] as $uiPolicyItem): ?>
                  <li><?= $uiPolicyEscape($uiPolicyItem) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>
        <p>For a current reservation question, <a href="contact.php">contact the hotel directly</a>.</p>
      </article>
    </div>
  </section>
</main>
