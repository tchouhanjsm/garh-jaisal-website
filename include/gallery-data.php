<?php
/**
 * Curated, source-backed photo selection for the public Gallery route.
 * Keep the full owner-provided source library in the experience registry;
 * this is the deliberately smaller 36-photo introduction to those assets.
 */
return [
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
            ['src' => 'img/s3.jpg', 'alt' => 'Sandstone details at Garh Jaisal Haveli', 'crop' => 'portrait'],
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
            ['src' => 'img/opium2.jpg', 'alt' => 'Opium Room interior', 'crop' => 'portrait'],
            ['src' => 'img/purple-room.jpg', 'alt' => 'Sweet Purple Room at Garh Jaisal Haveli', 'crop' => 'arch'],
            ['src' => 'img/purple2.jpg', 'alt' => 'Sweet Purple Room interior detail', 'crop' => 'portrait'],
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
