<?php
/**
 * Shared site-wide Schema.org entities.
 *
 * Values are copied from the approved homepage graph so page migrations can
 * reference one consistent Hotel and WebSite node without duplicating data.
 */
function gj_schema_hotel($siteUrl)
{
    $baseUrl = rtrim((string) $siteUrl, '/');

    return [
        '@type' => 'Hotel',
        '@id' => $baseUrl . '/#hotel',
        'name' => 'Hotel Garh Jaisal Haveli',
        'url' => $baseUrl . '/',
        'telephone' => '+91-7727047567',
        'image' => [
            $baseUrl . '/img/v2/facade.jpg',
            $baseUrl . '/img/5.webp',
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Fort Kotari Para, Inside Jaisalmer Fort',
            'addressLocality' => 'Jaisalmer',
            'addressRegion' => 'Rajasthan',
            'postalCode' => '345001',
            'addressCountry' => 'IN',
        ],
    ];
}

function gj_schema_website($siteUrl)
{
    $baseUrl = rtrim((string) $siteUrl, '/');

    return [
        '@type' => 'WebSite',
        '@id' => $baseUrl . '/#website',
        'url' => $baseUrl . '/',
        'name' => 'Garh Jaisal Haveli',
        'publisher' => [
            '@id' => $baseUrl . '/#hotel',
        ],
    ];
}

/**
 * Build FAQPage structured data from the exact Q&A array rendered on the page.
 * Returns no node when no valid visible questions are supplied.
 */
function gj_schema_faq($faqItems, $pageUrl)
{
    $questions = [];
    if (!is_array($faqItems)) {
        return [];
    }
    foreach ($faqItems as $item) {
        if (!is_array($item)) {
            continue;
        }
        $question = isset($item['question']) && is_string($item['question']) ? trim($item['question']) : '';
        $answer = isset($item['answer']) && is_string($item['answer']) ? trim($item['answer']) : '';
        if ($question === '' || $answer === '') {
            continue;
        }
        $questions[] = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
        ];
    }
    if (!$questions) {
        return [];
    }
    $url = rtrim((string) $pageUrl, '/');
    return ['@type' => 'FAQPage', '@id' => $url . '#faq', 'url' => $url, 'mainEntity' => $questions];
}
