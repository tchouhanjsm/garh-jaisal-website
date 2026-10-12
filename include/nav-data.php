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
                    'label' => 'City Tours',
                    'url' => 'guided-jaisalmer-tour.php',
                    'children' => [
                        ['label' => 'Guided Jaisalmer tours', 'url' => 'guided-jaisalmer-tour.php'],
                        ['label' => 'Half-day Jaisalmer tour', 'url' => 'half-day-jaisalmer-tour.php'],
                        ['label' => 'Full-day Jaisalmer tour', 'url' => 'full-day-jaisalmer-tour.php'],
                        ['label' => 'Walk inside Jaisalmer Fort', 'url' => 'city-tour.php'],
                        ['label' => 'Car for arrival or a desert day', 'url' => 'car-rental.php'],
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

    // Footer "Plan your stay" column.
    'footer' => [
        ['label' => 'Rooms', 'url' => 'room.php'],
        ['label' => 'Rooftop dining', 'url' => 'roof.php'],
        ['label' => 'Restaurant and menu', 'url' => 'restaurant.php'],
        ['label' => 'Desert tours', 'url' => 'desert-safari.php'],
        ['label' => 'Car & City Tours', 'url' => 'car-tours.php'],
        ['label' => 'Gallery', 'url' => 'gallery.php'],
        ['label' => 'Blog', 'url' => 'blog.php'],
    ],
];
