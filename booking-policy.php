<?php
$siteConfig = require __DIR__ . '/include/site-config.php';
$page = [
    'title' => 'Booking and Cancellation Policy (Draft) | Garh Jaisal Haveli',
    'description' => 'Unapproved booking and cancellation policy draft for Garh Jaisal Haveli. This page remains noindex until the owner supplies and approves the actual terms.',
    'path' => '/booking-policy.php',
    'robots' => 'noindex,follow',
    'css' => ['css/article.css'],
];
$policyDraft = [
    'breadcrumb' => 'Booking policy draft',
    'eyebrow' => 'Draft — owner approval required',
    'h1' => 'Booking and cancellation policy (draft)',
    'lede' => 'A placeholder for the hotel’s booking terms. No cancellation deadline, penalty, payment obligation or refund promise is being stated until the owner approves the real policy.',
    'sections' => [
        [
            'heading' => 'Reservations and payments',
            'items' => [
                'NEEDS OWNER INPUT: confirm when a booking becomes confirmed and whether any advance payment or deposit is required.',
                'NEEDS OWNER INPUT: confirm the payment methods, due dates, taxes and any other booking charges that may apply.',
            ],
        ],
        [
            'heading' => 'Changes and cancellations',
            'items' => [
                'NEEDS OWNER INPUT: provide the cancellation windows and any applicable fees or exceptions.',
                'NEEDS OWNER INPUT: define modification, no-show, late-arrival and early-departure handling.',
                'NEEDS OWNER INPUT: specify refund eligibility, method and expected processing timeframe.',
            ],
        ],
        [
            'heading' => 'Experiences and third-party reservations',
            'items' => [
                'NEEDS OWNER INPUT: confirm whether tour, transfer and dining enquiries follow the same terms as room reservations or need separate conditions.',
                'NEEDS OWNER INPUT: clarify which terms are set by the hotel and which may be set by the external booking provider.',
            ],
        ],
        [
            'heading' => 'Before this page can be published',
            'items' => [
                'The owner must approve complete guest-facing wording and any required jurisdiction-specific review.',
                'Until approval, this draft remains noindex, is not linked from the footer or navigation, and is excluded from the sitemap.',
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
