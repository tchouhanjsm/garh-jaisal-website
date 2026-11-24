<?php
/**
 * Single source of truth for site navigation (header, mobile menu, footer).
 *
 * Rules (see docs/SITE-ARCHITECTURE.md):
 * - Every parent item is a real page; its submenu never replaces the link.
 * - Only link to pages that exist and are live. Draft or hidden pages stay out.
 * - 'active' lists the $activePage values that highlight the item.
 */
return [
    'main' => [
        [
            'key' => 'about',
            'label' => 'About',
            'url' => 'about.php',
            'active' => ['about', 'gallery'],
            'children' => [
                ['label' => 'Our story', 'url' => 'about.php'],
                ['label' => 'Gallery', 'url' => 'gallery.php'],
            ],
        ],
        [
            'key' => 'stay',
            'label' => 'Stay',
            'url' => 'room.php',
            'active' => ['rooms'],
            'children' => [
                ['label' => 'Heritage rooms', 'url' => 'room.php'],
                ['label' => 'Book direct', 'url' => 'book-rooms-in-jaisalmer.php'],
                ['label' => 'Swiss tent desert stay', 'url' => 'swiss-tent-safari.php'],
            ],
        ],
        [
            'key' => 'dining',
            'label' => 'Dining',
            'url' => 'roof.php',
            'active' => ['dining'],
        ],
        [
            'key' => 'packages',
            'label' => 'Packages',
            'url' => 'pack.php',
            'active' => ['packages'],
            'groups' => [
                [
                    'label' => 'Desert Tours',
                    'url' => 'desert-safari.php',
                    'children' => [
                        ['label' => 'Sunset camel safari', 'url' => 'sunset-camel-safari.php'],
                        ['label' => 'Cultural evening and folk dance', 'url' => 'cultural-safari.php'],
                        ['label' => 'Sleep under the stars', 'url' => 'sleep-under-the-stars.php'],
                        ['label' => 'Swiss tent safari', 'url' => 'swiss-tent-safari.php'],
                    ],
                ],
                [
                    'label' => 'Car & City Tours',
                    'url' => 'car-tours.php',
                    'children' => [
                        ['label' => 'Guided Jaisalmer tours', 'url' => 'guided-jaisalmer-tour.php'],
                        ['label' => 'Half-day Jaisalmer tour', 'url' => 'half-day-jaisalmer-tour.php'],
                        ['label' => 'Full-day Jaisalmer tour', 'url' => 'full-day-jaisalmer-tour.php'],
                        ['label' => 'Walk inside Jaisalmer Fort', 'url' => 'city-tour.php'],
                        ['label' => 'Delhi Airport & Rajasthan transfers', 'url' => 'car-rental.php'],
                    ],
                ],
            ],
            'footer_link' => ['label' => 'All packages', 'url' => 'pack.php'],
        ],
        [
            'key' => 'directions',
            'label' => 'Directions',
            'url' => 'direction.php',
            'active' => ['directions'],
        ],
        [
            'key' => 'blog',
            'label' => 'Blog',
            'url' => 'blog.php',
            'active' => ['blog'],
        ],
        [
            'key' => 'contact',
            'label' => 'Contact',
            'url' => 'contact.php',
            'active' => ['contact'],
        ],
    ],

    'footer' => [
        [
            'label' => 'Stay & dine',
            'children' => [
                ['label' => 'Heritage Rooms', 'url' => 'room.php'],
                ['label' => 'Book direct guide', 'url' => 'book-rooms-in-jaisalmer.php'],
                ['label' => 'Rooftop dining', 'url' => 'roof.php'],
                ['label' => 'Garh Jaisal’s Kitchen', 'url' => 'restaurant.php'],
            ],
        ],
        [
            'label' => 'Explore',
            'children' => [
                ['label' => 'Tours & experiences', 'url' => 'pack.php'],
                ['label' => 'Delhi Airport & Rajasthan transfers', 'url' => 'car-rental.php'],
                ['label' => 'Build a trip', 'url' => 'catalogue.php'],
                ['label' => 'Travel Journal', 'url' => 'blog.php'],
                ['label' => 'Photo gallery', 'url' => 'gallery.php'],
            ],
        ],
    ],
];
