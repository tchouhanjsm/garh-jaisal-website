<?php
/**
 * Copy to include/site-config.local.php on staging/production.
 * Never commit site-config.local.php or place server secrets in public source.
 */
return [
    'recaptcha_secret' => 'PASTE_SERVER_ONLY_RECAPTCHA_SECRET_HERE',
    'inquiry_recipients' => [
        'REPLACE_WITH_APPROVED_ENQUIRY_ADDRESS',
    ],
];
