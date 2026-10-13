<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
$page = [
    'title' => 'Privacy Notice (Draft) | Garh Jaisal Haveli',
    'description' => 'Unapproved privacy notice draft for Garh Jaisal Haveli. This page remains noindex until the owner confirms the data practices and contact.',
    'path' => '/privacy.php',
    'robots' => 'noindex,follow',
    'css' => ['css/article.css'],
];
$policyDraft = [
    'breadcrumb' => 'Privacy notice draft',
    'eyebrow' => 'Draft — owner approval required',
    'h1' => 'Privacy notice (draft)',
    'lede' => 'A placeholder for the hotel’s privacy notice. It does not yet describe or commit to the hotel’s data-handling practices.',
    'sections' => [
        [
            'heading' => 'Data controller and contact',
            'items' => [
                'NEEDS OWNER INPUT: confirm the legal name of the data controller.',
                'NEEDS OWNER INPUT: confirm the email address for privacy questions and data requests.',
            ],
        ],
        [
            'heading' => 'Information collected and purposes',
            'items' => [
                'NEEDS OWNER INPUT: list the information received through the website enquiry form and explain its purposes and handling.',
                'NEEDS OWNER INPUT: confirm what information is processed by the external booking engine and which party controls it.',
                'NEEDS OWNER INPUT: confirm whether any additional personal information is collected through phone, WhatsApp or other channels referenced by the site.',
            ],
        ],
        [
            'heading' => 'Analytics and service providers',
            'items' => [
                'NEEDS OWNER INPUT: approve the description of Google Analytics, reCAPTCHA and other third-party services used by the site.',
                'NEEDS OWNER INPUT: confirm applicable retention periods, processing locations and any cross-border transfer statements.',
            ],
        ],
        [
            'heading' => 'Requests, retention and security',
            'items' => [
                'NEEDS OWNER INPUT: confirm the process guests should use to request access, correction or deletion of information.',
                'NEEDS OWNER INPUT: approve retention, security and complaint-handling wording before publication.',
            ],
        ],
    ],
];
require __DIR__ . '/include/page-head.php';
?>
<body>
<?php include __DIR__ . '/include/header-v2.php'; ?>
<?php require __DIR__ . '/include/ui/policy-draft.php'; ?>
<?php include __DIR__ . '/include/footer-v2.php'; ?>
<script src="js/garh-v2.js"></script>
</body>
</html>
