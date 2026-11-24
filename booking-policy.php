<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
require_once __DIR__ . '/include/schema.php';
$policyTitle = 'Booking Terms and Conditions';
$policyContentPath = __DIR__ . '/include/ui/booking-terms-content.php';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => $policyTitle],
];
$page = [
    'title' => $policyTitle . ' | Garh Jaisal Haveli',
    'description' => 'Read Garh Jaisal Haveli’s booking terms covering rates, advance payment, cancellations, check-in, Heritage Room occupancy and hotel policies.',
    'path' => '/booking-policy.php',
    'css' => ['css/article.css'],
    'schema' => [gj_schema_breadcrumb_list($siteConfig['site_url'], '/booking-policy.php', $breadcrumbs)],
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
