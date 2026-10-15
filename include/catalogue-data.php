<?php
/**
 * Reviewed catalogue order and product source references.
 *
 * Experience copy, imagery, duration and publication status come from
 * include/experience-data.php. Room facts come from include/rooms-data.php.
 * This file intentionally contains no unconfirmed prices or availability.
 */
return [
    [
        'id' => 'heritage-room-stay',
        'source' => 'rooms',
        'category' => 'Stay inside the Fort',
        'title' => 'Heritage room stay',
        'description' => 'Choose from seven individually styled rooms on the ground and first floors inside Jaisalmer Fort. Compare the room details, then check current rates for your dates.',
        'url' => 'room.php',
        'image' => 'img/v2/purple-room.jpg',
        'alt' => 'Sweet Purple Room at Garh Jaisal Haveli, with a carved bed and magenta curtains',
        'trip_group' => 'stay',
    ],
    [
        'id' => 'sunset-camel-safari',
        'source' => 'experience',
        'slug' => 'sunset-camel-safari',
        'category' => 'Desert · same evening',
    ],
    [
        'id' => 'cultural-safari',
        'source' => 'experience',
        'slug' => 'cultural-safari',
        'category' => 'Desert · same evening',
    ],
    [
        'id' => 'sleep-under-the-stars',
        'source' => 'experience',
        'slug' => 'sleep-under-the-stars',
        'category' => 'Overnight · open sky',
    ],
    [
        'id' => 'swiss-tent-safari',
        'source' => 'experience',
        'slug' => 'swiss-tent-safari',
        'category' => 'Overnight · Swiss tent',
    ],
    [
        'id' => 'half-day-jaisalmer-tour',
        'source' => 'experience',
        'slug' => 'half-day-jaisalmer-tour',
        'category' => 'Guided sightseeing',
    ],
    [
        'id' => 'full-day-jaisalmer-tour',
        'source' => 'experience',
        'slug' => 'full-day-jaisalmer-tour',
        'category' => 'Guided sightseeing',
    ],
    [
        'id' => 'car-arrangements',
        'source' => 'service',
        'category' => 'Transport',
        'title' => 'SUV and sedan arrangements',
        'description' => 'Ask about desert pickup and drop-off or intercity journeys across Rajasthan and Delhi. The route, vehicle, availability and quote are confirmed for each enquiry.',
        'url' => 'car-rental.php',
        'image' => 'img/desert3.jpg',
        'alt' => 'Sand dunes in the Thar Desert near Jaisalmer',
    ],
    [
        'id' => 'rooftop-dining',
        'source' => 'service',
        'category' => 'Dining',
        'title' => 'Rooftop dining',
        'description' => 'Plan a rooftop meal at Garh Jaisal’s Kitchen. The menu includes vegetarian, vegan and non-vegetarian dishes; confirm current dishes, prices and table availability directly with the hotel.',
        'url' => 'roof.php',
        'image' => 'img/v2/fort-from-roof.jpg',
        'alt' => 'Rooftop table at dusk overlooking the lights of Jaisalmer',
    ],
];
