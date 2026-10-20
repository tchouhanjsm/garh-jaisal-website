<?php
/**
 * Curated, source-backed photo selection for the public Gallery route.
 * Keep the full owner-provided source library in the experience registry;
 * this is the deliberately smaller 36-photo introduction to those assets.
 */
$gallerySections = [
    'hotel' => [
        'eyebrow' => 'Hotel',
        'title' => 'The haveli',
        'description' => 'Shared spaces, sandstone details and views around Garh Jaisal Haveli.',
        'images' => [
            ['src' => 'img/v2/facade.jpg', 'alt' => 'Carved facade of Garh Jaisal Haveli inside Jaisalmer Fort', 'crop' => 'arch'],
            ['src' => 'img/v2/courtyard.jpg', 'alt' => 'Courtyard balconies inside Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/v2/sitting-room.jpg', 'alt' => 'Sitting room with carved wood at Garh Jaisal Haveli', 'crop' => 'standard'],
            ['src' => 'img/v2/fort-from-roof.jpg', 'alt' => 'Jaisalmer Fort seen from the rooftop at dusk', 'crop' => 'landscape'],
            ['src' => 'img/recep1.jpg', 'alt' => 'Reception area at Garh Jaisal Haveli', 'crop' => 'standard'],
            ['src' => 'img/s1.jpg', 'alt' => 'Interior of Garh Jaisal Haveli', 'crop' => 'standard'],
            ['src' => 'img/s2.jpg', 'alt' => 'Heritage interior at Garh Jaisal Haveli', 'crop' => 'standard'],
            ['src' => 'img/s3.jpg', 'alt' => 'Sandstone details at Garh Jaisal Haveli', 'crop' => 'portrait'],
            ['src' => 'img/s5.jpg', 'alt' => 'Heritage interior at Garh Jaisal Haveli', 'crop' => 'standard'],
        ],
    ],
    'rooms' => [
        'eyebrow' => 'Rooms',
        'title' => 'Rooms with character',
        'description' => 'The rooms featured on the current Rooms page, shown from different angles and details.',
        'images' => [
            ['src' => 'img/maharaniroom.jpg', 'alt' => 'Red Maharani Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/redmaha1.jpg', 'alt' => 'Red Maharani Room interior', 'crop' => 'portrait'],
            ['src' => 'img/opium1.jpg', 'alt' => 'Opium Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/purple-room.jpg', 'alt' => 'Sweet Purple Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/risingroom.jpg', 'alt' => 'Rising Sun Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/sunt1.jpg', 'alt' => 'Rising Sun Room detail', 'crop' => 'portrait'],
            ['src' => 'img/scarlet.jpg', 'alt' => 'Scarlet Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/scar-t.jpg', 'alt' => 'Scarlet Room detail', 'crop' => 'portrait'],
            ['src' => 'img/green-room.jpg', 'alt' => 'Green Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/greent.jpg', 'alt' => 'Green Room detail', 'crop' => 'portrait'],
            ['src' => 'img/rr3.jpg', 'alt' => 'BlueSky Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/rr2.jpg', 'alt' => 'BlueSky Room interior', 'crop' => 'portrait'],
        ],
    ],
    'desert' => [
        'eyebrow' => 'Desert',
        'title' => 'Desert experiences',
        'description' => 'Explore the desert camp, Swiss tent interiors and bathroom, evening programme, and the open-air Sleep Under the Stars experience.',
        'images' => [
            ['src' => 'img/Desert_Camp/camp_from_above.jpeg', 'alt' => 'Desert camp viewed from above near Jaisalmer', 'crop' => 'landscape'],
            ['src' => 'img/Desert_Camp/camp_evening.jpeg', 'alt' => 'Evening at the desert camp near Jaisalmer', 'crop' => 'landscape'],
            ['src' => 'img/Desert_Camp/tent_deluxe.jpeg', 'alt' => 'Deluxe tent accommodation at the desert camp near Jaisalmer', 'crop' => 'arch'],
            ['src' => 'img/Desert_Camp/Bathroom.jpeg', 'alt' => 'Bathroom inside a Swiss tent with a shower, basin and toilet', 'crop' => 'portrait'],
            ['src' => 'img/Desert_Camp/Prog.jpeg', 'alt' => 'Musicians performing at an evening programme in the desert camp', 'crop' => 'portrait'],
            ['src' => 'img/Desert_Camp/Tent.jpeg', 'alt' => 'Double bed and patterned wall panels inside a Swiss tent', 'crop' => 'arch'],
            ['src' => 'img/Desert_Camp/Tentout.jpeg', 'alt' => 'White tents with planted greenery outside the accommodation', 'crop' => 'landscape'],
            ['src' => 'img/sleeping_under_stars/Tour_1.jpeg', 'alt' => 'Mattress and bedding set out in the desert beside a small tent', 'crop' => 'landscape'],
            ['src' => 'img/sleeping_under_stars/tour_4.jpeg', 'alt' => 'Traveller riding a camel across the desert dunes', 'crop' => 'portrait'],
            ['src' => 'img/sleeping_under_stars/tour_6.jpeg', 'alt' => 'Ripples across the sand dunes in the Jaisalmer desert', 'crop' => 'landscape'],
        ],
        'cta' => [
            'title' => 'Choosing an overnight desert stay?',
            'text' => 'Compare the open-air Sleep Under the Stars experience with a Swiss Deluxe Tent stay, then confirm current availability and inclusions with the hotel.',
            'label' => 'Compare overnight options',
            'href' => 'camping.php',
        ],
    ],
    'restaurant' => [
        'eyebrow' => 'Restaurant',
        'title' => 'Dining',
        'description' => 'Dining spaces and rooftop restaurant views at Garh Jaisal Haveli.',
        'images' => [
            ['src' => 'img/res1.jpg', 'alt' => 'Restaurant at Garh Jaisal Haveli', 'crop' => 'portrait'],
            ['src' => 'img/rest2.jpg', 'alt' => 'Dining space at Garh Jaisal Haveli', 'crop' => 'landscape'],
            ['src' => 'img/rest3.jpg', 'alt' => 'Rooftop dining at Garh Jaisal Haveli', 'crop' => 'landscape'],
            ['src' => 'img/rest4.jpg', 'alt' => 'Restaurant view at Garh Jaisal Haveli', 'crop' => 'landscape'],
            ['src' => 'img/rest5.jpg', 'alt' => 'Dining at Garh Jaisal Haveli', 'crop' => 'portrait'],
        ],
    ],
];

$studioRoot = dirname(__DIR__) . '/content/gallery';
$studioFiles = is_dir($studioRoot) ? (glob($studioRoot . '/*.json') ?: []) : [];
sort($studioFiles, SORT_STRING);
$allowedCategories = ['hotel', 'rooms', 'desert', 'restaurant'];
$allowedCrops = ['arch', 'landscape', 'portrait', 'standard'];
$usedGallerySources = [];
foreach ($gallerySections as $section) foreach (($section['images'] ?? []) as $photo) {
    if (is_array($photo) && isset($photo['src']) && is_string($photo['src'])) $usedGallerySources[$photo['src']] = true;
}
foreach ($studioFiles as $studioFile) {
    if (is_link($studioFile) || !is_file($studioFile)) throw new RuntimeException('Unsafe Content Studio gallery record: ' . basename($studioFile));
    $record = json_decode((string) file_get_contents($studioFile), true);
    $id = basename($studioFile, '.json');
    $category = is_array($record) ? (string) ($record['category'] ?? '') : '';
    $status = is_array($record) ? (string) ($record['status'] ?? '') : '';
    $photo = is_array($record) ? ($record['image'] ?? null) : null;
    $src = is_array($photo) ? (string) ($photo['src'] ?? '') : '';
    $alt = is_array($photo) ? trim((string) ($photo['alt'] ?? '')) : '';
    $crop = is_array($photo) ? (string) ($photo['crop'] ?? '') : '';
    if (
        !is_array($record) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id)
        || ($record['id'] ?? null) !== $id || !in_array($category, $allowedCategories, true)
        || !in_array($status, ['live', 'draft', 'hidden'], true) || !is_array($photo)
        || !preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$~iD', $src)
        || strpos($src, '..') !== false || strpos($src, chr(92)) !== false || $alt === ''
        || !in_array($crop, $allowedCrops, true)
    ) throw new RuntimeException('Invalid Content Studio gallery record: ' . $id);
    $imagePath = dirname(__DIR__) . '/' . $src;
    if (is_link($imagePath) || !is_file($imagePath)) throw new RuntimeException('Missing or unsafe Content Studio gallery image: ' . $src);
    if (isset($usedGallerySources[$src])) throw new RuntimeException('Duplicate Gallery source path: ' . $src);
    $usedGallerySources[$src] = true;
    if ($status !== 'live') continue;
    $gallerySections[$category]['images'][] = [
        'src' => $src, 'alt' => $alt,
        'caption' => trim((string) ($photo['caption'] ?? '')) !== '' ? trim((string) $photo['caption']) : $alt,
        'crop' => $crop,
    ];
}
return $gallerySections;

