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
