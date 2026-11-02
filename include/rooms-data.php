<?php

$rooms = [
    [
        'name' => 'Sweet Purple Room',
        'kicker' => 'Ground floor',
        'image' => 'img/v2/purple-room.jpg',
        'alt' => 'Sweet Purple Room at Garh Jaisal Haveli, with a carved bed and magenta curtains',
        'description' => 'A colourful heritage room with a city view, a carved bed and a second bed.',
        'features' => [
            'Carved bed',
            'Second bed',
            'Air conditioning',
            'Ceiling fan',
            'Private bathroom',
            'City view',
        ],
    ],
    [
        'name' => 'Rising Sun Room',
        'kicker' => 'Ground floor',
        'image' => 'img/sun2.jpg',
        'alt' => 'Rising Sun Room at Garh Jaisal Haveli',
        'description' => 'A main-floor room with a window seat and a view towards the morning light.',
        'features' => [
            'Comfortable room',
            'Private bathroom',
            'Ceiling fan',
            'Air conditioning',
            'City view',
            'Window seat',
        ],
    ],
    [
        'name' => 'Scarlet Room',
        'kicker' => 'Ground floor',
        'image' => 'img/scarlet.jpg',
        'alt' => 'Scarlet Room at Garh Jaisal Haveli',
        'description' => 'A cosy room with warm colour, a city view and a window seat facing the fort entrance.',
        'features' => [
            'Comfortable room',
            'Ceiling fan',
            'Private bathroom',
            'Air conditioning',
            'City view',
            'Window seat',
        ],
    ],
    [
        'name' => 'Opium Room',
        'kicker' => 'First floor',
        'image' => 'img/opium-room.jpg',
        'alt' => 'Opium Room at Garh Jaisal Haveli',
        'description' => 'A heritage room with traditional character and views across Jaisalmer.',
        'features' => [
            'Air conditioning',
            'Ceiling fan',
            'Private bathroom',
            'City view',
        ],
    ],
    [
        'name' => 'Red Maharani Room',
        'kicker' => 'First floor',
        'image' => 'img/maharaniroom.jpg',
        'alt' => 'Red Maharani Room at Garh Jaisal Haveli',
        'description' => 'A red-toned heritage room with a king bed and window seat.',
        'features' => [
            'King bed',
            'Air conditioning',
            'Ceiling fan',
            'Private bathroom',
            'City view',
            'Window seat',
        ],
    ],
    [
        'name' => 'Green Room',
        'kicker' => 'First floor',
        'image' => 'img/v2/green-room.jpg',
        'alt' => 'Green Room at Garh Jaisal Haveli, with a window seat and green bedding',
        'description' => 'A traditional room with green bedding, a window seat and a view over Jaisalmer.',
        'features' => [
            'King bed',
            'Window seat',
            'Air conditioning',
            'Ceiling fan',
            'Private bathroom',
        ],
    ],
    [
        'name' => 'BlueSky Room',
        'kicker' => 'First floor',
        'image' => 'img/rr3.jpg',
        'alt' => 'BlueSky Room at Garh Jaisal Haveli',
        'description' => 'A comfortable heritage room with a city view and window seat.',
        'features' => [
            'Comfortable room',
            'Ceiling fan',
            'Private bathroom',
            'Air conditioning',
            'City view',
            'Window seat',
        ],
    ],
];

$rooms = (static function ($roomRecords) {
    // The rooms include can run inside pages that already use $room/$roomSlug.
    $pageMediaSlots = require __DIR__ . '/site-media-slots.php';
    foreach ($roomRecords as &$pageMediaRoom) {
        $pageMediaRoomSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $pageMediaRoom['name']), '-'));
        $pageMediaImage = gj_site_media_slot($pageMediaSlots, 'room-' . $pageMediaRoomSlug . '-primary', [
            'src' => $pageMediaRoom['image'],
            'alt' => $pageMediaRoom['alt'],
        ]);
        $pageMediaRoom['image'] = $pageMediaImage['src'];
        $pageMediaRoom['alt'] = $pageMediaImage['alt'];
    }
    unset($pageMediaRoom, $pageMediaRoomSlug, $pageMediaImage, $pageMediaSlots);
    return $roomRecords;
})($rooms);

return $rooms;
