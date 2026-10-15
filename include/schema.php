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

/**
 * Build visible-breadcrumb-equivalent structured data from the same reviewed
 * breadcrumb array passed to include/ui/breadcrumb.php.
 *
 * $pagePath must be a public site-relative path such as "/contact.php".
 */
function gj_schema_breadcrumb_list($siteUrl, $pagePath, $breadcrumbs)
{
    $baseUrl = rtrim((string) $siteUrl, '/');
    $path = (string) $pagePath;
    if ($baseUrl === '' || strpos($path, '/') !== 0 || strpos($path, '..') !== false || !is_array($breadcrumbs)) {
        return [];
    }

    $validItems = [];
    foreach ($breadcrumbs as $item) {
        if (!is_array($item)) {
            continue;
        }
        $label = isset($item['label']) && is_string($item['label']) ? trim($item['label']) : '';
        if ($label === '') {
            continue;
        }
        $validItems[] = [
            'label' => $label,
            'href' => isset($item['href']) && is_string($item['href']) ? trim($item['href']) : '',
        ];
    }
    if (count($validItems) < 2) {
        return [];
    }

    $listItems = [];
    $lastIndex = count($validItems) - 1;
    foreach ($validItems as $index => $item) {
        if ($index === $lastIndex) {
            $itemUrl = $baseUrl . $path;
        } else {
            $href = $item['href'];
            if (
                $href === ''
                || strpos($href, '/') !== 0
                || strpos($href, '//') === 0
                || strpos($href, '..') !== false
                || preg_match('#^[a-z][a-z0-9+.-]*:#i', $href)
            ) {
                continue;
            }
            $itemUrl = $href === '/' ? $baseUrl . '/' : $baseUrl . '/' . ltrim($href, '/');
        }

        $listItems[] = [
            '@type' => 'ListItem',
            'position' => count($listItems) + 1,
            'name' => $item['label'],
            'item' => $itemUrl,
        ];
    }

    if (count($listItems) < 2) {
        return [];
    }

    return [
        '@type' => 'BreadcrumbList',
        '@id' => $baseUrl . $path . '#breadcrumb',
        'itemListElement' => $listItems,
    ];
}

/**
 * Describe room cards as HotelRoom entities using only the visible room
 * registry. Do not synthesize rates, offers, availability or reviews.
 */
function gj_schema_hotel_rooms($rooms, $siteUrl, $pageUrl)
{
    if (!is_array($rooms)) {
        return [];
    }

    $baseUrl = rtrim((string) $siteUrl, '/');
    $canonical = rtrim((string) $pageUrl, '/');
    if ($baseUrl === '' || $canonical === '') {
        return [];
    }

    $nodes = [];
    foreach ($rooms as $room) {
        if (!is_array($room)) {
            continue;
        }
        $name = isset($room['name']) && is_string($room['name']) ? trim($room['name']) : '';
        $description = isset($room['description']) && is_string($room['description']) ? trim($room['description']) : '';
        $image = isset($room['image']) && is_string($room['image']) ? trim($room['image']) : '';
        if (
            $name === ''
            || $description === ''
            || !preg_match('~^img/[A-Za-z0-9._/-]+$~D', $image)
            || strpos($image, '..') !== false
        ) {
            continue;
        }

        $slug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-');
        if ($slug === '') {
            continue;
        }
        $roomUrl = $canonical . '#room-' . $slug;
        $features = [];
        foreach (($room['features'] ?? []) as $feature) {
            if (!is_string($feature) || trim($feature) === '') {
                continue;
            }
            $features[] = [
                '@type' => 'LocationFeatureSpecification',
                'name' => trim($feature),
                'value' => true,
            ];
        }

        $node = [
            '@type' => 'HotelRoom',
            '@id' => $roomUrl,
            'url' => $roomUrl,
            'name' => $name,
            'description' => $description,
            'image' => $baseUrl . '/' . ltrim($image, '/'),
            'containedInPlace' => ['@id' => $baseUrl . '/#hotel'],
        ];
        if ($features) {
            $node['amenityFeature'] = $features;
        }
        $nodes[] = $node;
    }

    return $nodes;
}

