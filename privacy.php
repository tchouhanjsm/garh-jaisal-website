<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
require_once __DIR__ . '/include/schema.php';
$policyTitle = 'Privacy Policy';
$policyContentPath = __DIR__ . '/include/ui/privacy-policy-content.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => $policyTitle],
];
$page = [
    'title' => $policyTitle . ' | Garh Jaisal Haveli',
    'description' => 'Read how Garh Jaisal Haveli collects, uses and secures guest and website information, including cookies and communication preferences.',
    'path' => '/privacy.php',
    'css' => ['css/article.css'],
    'schema' => [gj_schema_breadcrumb_list($siteConfig['site_url'], '/privacy.php', $breadcrumbs)],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<?php require __DIR__ . '/include/ui/policy-page.php'; ?>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
