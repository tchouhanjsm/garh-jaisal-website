<?php
/**
 * Presentation-level photo collections assembled from images already in the
 * public Gallery and the live experience registry. No asset is copied,
 * renamed or newly published here; captions describe the pictured scene, not
 * a guarantee that every stop or facility belongs to every package.
 */
$photoCollectionGallerySections = require __DIR__ . '/gallery-data.php';
$photoCollectionExperiences = isset($experiences) && is_array($experiences)
    ? $experiences
    : require __DIR__ . '/experience-data.php';

$photoCollectionIndex = [];
foreach ($photoCollectionGallerySections as $photoCollectionSection) {
    foreach (($photoCollectionSection['images'] ?? []) as $photoCollectionImage) {
        if (!is_array($photoCollectionImage) || empty($photoCollectionImage['src']) || empty($photoCollectionImage['alt'])) {
            continue;
        }
        $photoCollectionImage['caption'] = $photoCollectionImage['caption'] ?? $photoCollectionImage['alt'];
        $photoCollectionIndex[$photoCollectionImage['src']] = $photoCollectionImage;
    }
}
foreach ($photoCollectionExperiences as $photoCollectionExperience) {
    foreach (($photoCollectionExperience['gallery'] ?? []) as $photoCollectionImage) {
        if (!is_array($photoCollectionImage) || empty($photoCollectionImage['src']) || empty($photoCollectionImage['alt'])) {
            continue;
        }
        $photoCollectionImage['caption'] = $photoCollectionImage['caption'] ?? $photoCollectionImage['alt'];
        $photoCollectionIndex[$photoCollectionImage['src']] = $photoCollectionImage;
    }
}

$pickPhotoCollection = static function (array $sources) use ($photoCollectionIndex) {
    $slides = [];
    foreach ($sources as $source) {
        if (isset($photoCollectionIndex[$source])) {
            $slides[] = $photoCollectionIndex[$source];
        }
    }
    return $slides;
};

$cityPhotoCollection = [
    [
        'src' => 'img/j10.jpg',
        'alt' => $photoCollectionExperiences['guided-jaisalmer-tour']['hero_alt'] ?? 'Ornately carved sandstone architecture in Jaisalmer’s historic centre',
        'caption' => 'Carved sandstone architecture in the historic centre',
    ],
    [
        'src' => 'img/j5.jpg',
        'alt' => 'Carved sandstone balconies on a historic Jaisalmer haveli',
        'caption' => 'Carved sandstone balconies',
    ],
    [
        'src' => 'img/j6.jpg',
        'alt' => $photoCollectionExperiences['half-day-jaisalmer-tour']['hero_alt'] ?? 'A sandstone passageway in Jaisalmer’s historic centre',
        'caption' => 'A historic sandstone passageway',
    ],
    [
        'src' => 'img/j2.jpg',
        'alt' => $photoCollectionExperiences['full-day-jaisalmer-tour']['hero_alt'] ?? 'Carved sandstone architecture in historic Jaisalmer',
        'caption' => 'Sandstone architecture across Jaisalmer',
    ],
    $photoCollectionIndex['img/v2/fort-from-roof.jpg'] ?? [
        'src' => 'img/v2/fort-from-roof.jpg',
        'alt' => 'Jaisalmer Fort seen from the rooftop at dusk',
        'caption' => 'Views across Jaisalmer Fort',
    ],
    $photoCollectionIndex['img/v2/facade.jpg'] ?? [
        'src' => 'img/v2/facade.jpg',
        'alt' => 'Carved facade of Garh Jaisal Haveli inside Jaisalmer Fort',
        'caption' => 'Carved heritage details inside the Fort',
    ],
];

$photoCollections = [
    'desert-safari' => [
        'title' => 'Explore the desert experiences in pictures',
        'intro' => 'These photos show a range of settings across the currently listed desert options, from camel riding and open dunes to desert-camp and overnight accommodation scenes. Confirm the exact route, programme, accommodation and inclusions for the option you choose.',
        'slides' => array_values($photoCollectionGallerySections['desert']['images'] ?? []),
    ],
    'sunset-camel-safari' => [
        'title' => 'Camel riding and the open desert',
        'intro' => 'These photos from the existing desert photo collection illustrate camel riding, jeep travel and dune landscapes. The route, schedule and included arrangements for the sunset safari are confirmed with the hotel for your date.',
        'slides' => $pickPhotoCollection([
            'img/sleeping_under_stars/tour_3.jpeg',
            'img/sleeping_under_stars/tour_4.jpeg',
            'img/sleeping_under_stars/tour_6.jpeg',
            'img/sleeping_under_stars/tour_8.jpeg',
        ]),
    ],
    'cultural-safari' => [
        'title' => 'A closer look at the desert-camp setting',
        'intro' => 'These existing camp photos show the setting, evening programme and dining scene. Confirm the current programme, meal arrangement, route and availability for your travel date.',
        'slides' => $pickPhotoCollection([
            'img/Desert_Camp/camp_from_above.jpeg',
            'img/Desert_Camp/camp_evening.jpeg',
            'img/Desert_Camp/Prog.jpeg',
            'img/Desert_Camp/camp_dinner.jpeg',
        ]),
    ],
    'guided-jaisalmer-tour' => [
        'title' => 'Jaisalmer heritage in pictures',
        'intro' => 'These photos are examples from the site’s existing Jaisalmer and heritage photo library. Ask the hotel to confirm the exact stops, route and any applicable entry fees for your chosen tour and date.',
        'slides' => $cityPhotoCollection,
    ],
    'half-day-jaisalmer-tour' => [
        'title' => 'Jaisalmer heritage in pictures',
        'intro' => 'These photos are examples from the site’s existing Jaisalmer and heritage photo library. Ask the hotel to confirm the exact stops, route and any applicable entry fees for your chosen tour and date.',
        'slides' => $cityPhotoCollection,
    ],
    'full-day-jaisalmer-tour' => [
        'title' => 'Jaisalmer heritage in pictures',
        'intro' => 'These photos are examples from the site’s existing Jaisalmer and heritage photo library. Ask the hotel to confirm the exact stops, route and any applicable entry fees for your chosen tour and date.',
        'slides' => $cityPhotoCollection,
    ],
];

unset(
    $photoCollectionGallerySections,
    $photoCollectionExperiences,
    $photoCollectionIndex,
    $pickPhotoCollection,
    $photoCollectionSection,
    $photoCollectionImage,
    $photoCollectionExperience,
    $cityPhotoCollection
);

return $photoCollections;
