<?php
/**
 * Shared experience content for the V2 Packages / Tours architecture.
 *
 * Keep operational details here so the same information is not duplicated
 * across the hub pages and individual experience pages.
 *
 * IMPORTANT:
 * Timing language intentionally uses approximate sequence rather than fixed
 * clock times. Desert timings vary with season, sunset and operational
 * arrangements. Confirm exact timing with the hotel before publishing a
 * fixed schedule.
 */

$experiences = [
    'desert-safari' => [
        'slug' => 'desert-safari',
        'category' => 'desert',
        'parent' => 'pack',
        'menu_label' => 'Desert Tours',
        'duration_type' => 'hub',
        'order' => 10,
        'status' => 'live',
        'title' => 'Jaisalmer Desert Safari | Camel Safaris & Overnight Stays',
        'description' => 'Explore desert safari options arranged from Garh Jaisal Haveli, including sunset camel safari, cultural evening, sleeping under the stars and Swiss tent stays.',
        'h1' => 'Desert Safari in Jaisalmer',
        'eyebrow' => 'Desert safari',
        'intro' => 'Choose a desert experience based on how much time you have, whether you want to return the same evening, and how you would like to spend the night.',
        'hero_image' => 'img/desert3.jpg',
        'hero_width' => 1366,
        'hero_height' => 768,
        'hero_alt' => 'Sand dunes in the Thar Desert near Jaisalmer',
        'hero_caption' => 'A broad sweep of Thar Desert dunes sets the scene for sunset outings and overnight safari options from Jaisalmer.',
    ],

    'guided-jaisalmer-tour' => [
        'slug' => 'guided-jaisalmer-tour',
        'category' => 'city',
        'parent' => 'pack',
        'menu_label' => 'City Tours',
        'duration_type' => 'hub',
        'order' => 20,
        'status' => 'live',
        'title' => 'Guided Jaisalmer Tours | Half-Day & Full-Day Tours',
        'description' => 'Explore Jaisalmer with half-day and full-day guided tours from Garh Jaisal Haveli, covering the Fort, havelis, markets and heritage areas.',
        'h1' => 'Guided Jaisalmer Tours',
        'eyebrow' => 'Jaisalmer tours',
        'intro' => 'See more of Jaisalmer with a locally arranged guided tour. Choose a shorter heritage walk when time is limited or a fuller day when you want more of the city.',
        'hero_image' => 'img/j10.jpg',
        'hero_width' => 1333,
        'hero_height' => 2000,
        'hero_alt' => 'Ornately carved sandstone architecture in Jaisalmer\'s historic centre',
        'hero_caption' => 'Carved sandstone balconies and historic lanes reward a guided walk through Jaisalmer.',
    ],

    'sunset-camel-safari' => [
        'slug' => 'sunset-camel-safari',
        'category' => 'desert',
        'parent' => 'desert-safari',
        'menu_label' => 'Sunset camel safari',
        'duration_type' => 'half-day',
        'order' => 10,
        'status' => 'live',
        'title' => 'Sunset Camel Safari in Jaisalmer | Garh Jaisal Haveli',
        'description' => 'A sunset camel safari from Jaisalmer with desert transport, a camel ride, sunset in the dunes, tea and snacks, dinner prepared in the desert and return to town.',
        'h1' => 'Sunset Camel Safari in Jaisalmer',
        'eyebrow' => 'Sunset and evening',
        'hero_image' => 'img/desert7.jpg',
        'hero_width' => 1101,
        'hero_height' => 619,
        'hero_alt' => 'Camel riders travelling across the dunes near Jaisalmer',
        'hero_caption' => 'Sunset timing and the route vary with season; confirm both before departure.',
        'summary' => 'Travel into the desert for a camel ride, sunset and an evening meal before returning to Jaisalmer.',
        'at_a_glance' => [
            'Duration' => 'Afternoon to evening',
            'Overnight' => 'No',
            'Camel ride' => 'Yes',
            'Meals' => 'Tea, snacks and dinner',
            'Transport' => 'Jeep transport',
            'Return' => 'Jaisalmer the same evening',
        ],
        'timeline_label' => 'Typical sequence',
        'timeline_note' => 'The exact departure and sunset timing changes with the season. The hotel confirms the final schedule for your date.',
        'timeline' => [
            ['step' => '01', 'title' => 'Depart Jaisalmer', 'text' => 'Leave town by jeep and travel towards the desert route.'],
            ['step' => '02', 'title' => 'Kuldhara and local route', 'text' => 'Visit the abandoned village of Kuldhara before continuing deeper into the desert.'],
            ['step' => '03', 'title' => 'Natural pond and nomadic village', 'text' => 'Continue through the route with stops described in the current package itinerary.'],
            ['step' => '04', 'title' => 'Camel ride towards the dunes', 'text' => 'Meet the camel riders and continue into the dunes.'],
            ['step' => '05', 'title' => 'Sunset in the dunes', 'text' => 'Watch the light change across the dunes with tea, coffee and snacks.'],
            ['step' => '06', 'title' => 'Dinner in the desert', 'text' => 'Have dinner prepared in the desert before returning to town.'],
        ],
        'included' => [
            'Jeep transport',
            'Visit to the abandoned village of Kuldhara',
            'Drive to a natural pond',
            'Visit to a nomadic village',
            'Camel riding',
            'Tea, coffee and snacks at sunset',
            'Dinner prepared in the desert',
        ],
        'not_included' => [
            'Personal expenses',
            'Anything not specifically listed in the confirmed package',
        ],
        'best_for' => 'Guests who want a desert sunset and camel experience without staying overnight.',
        'before_book' => 'Ask for the current price, pickup arrangement, approximate departure time and the exact inclusions for your travel date.',
        'faq' => [
            ['question' => 'How long is the sunset camel safari?', 'answer' => 'It is an afternoon-to-evening experience. The exact departure and return timing depends on the season and the operating plan for your date.'],
            ['question' => 'Does the safari include a camel ride?', 'answer' => 'Yes. The current package description includes camel riding, followed by time in the dunes around sunset.'],
            ['question' => 'Do we return to Jaisalmer the same day?', 'answer' => 'Yes. This experience is designed as a same-evening desert trip rather than an overnight stay.'],
        ],
        
        'related' => ['sleep-under-the-stars', 'cultural-safari', 'swiss-tent-safari'],
    ],

    'sleep-under-the-stars' => [
        'slug' => 'sleep-under-the-stars',
        'category' => 'desert',
        'parent' => 'desert-safari',
        'menu_label' => 'Sleep under the stars',
        'duration_type' => 'overnight',
        'order' => 20,
        'status' => 'live',
        'title' => 'Sleep Under the Stars in Jaisalmer | Garh Jaisal Haveli',
        'description' => 'Spend a night in the Jaisalmer desert with camel riding, sunset in the dunes, dinner prepared in the desert, sleeping under the stars and breakfast.',
        'h1' => 'Sleep Under the Stars in the Jaisalmer Desert',
        'eyebrow' => 'Overnight desert experience',
        'hero_image' => 'img/sleeping_under_stars/tour_9.jpeg',
        'hero_width' => 1280,
        'hero_height' => 854,
        'hero_alt' => 'Desert campfire, shelter and jeep at dusk',
        'hero_caption' => 'The camp settles into dusk; confirm the overnight setup, weather provisions and transport before committing.',
        'comparison_image' => 'img/sleeping_under_stars/Tour_1.jpeg',
        'comparison_image_alt' => 'Mattress and bedding set out in the desert beside a small tent',
        'gallery_title' => 'A closer look at Sleep Under the Stars',
        'gallery_intro' => 'Explore the sleeping setup, camel ride, desert landscape, evening campfire and food shown in these photos.',
        'gallery' => [
            ['src' => 'img/sleeping_under_stars/Tour_1.jpeg', 'alt' => 'Mattress and bedding set out in the desert beside a small tent', 'caption' => 'The open-air sleeping setup'],
            ['src' => 'img/sleeping_under_stars/tour_2.jpeg', 'alt' => 'People gathered around a small desert campfire at night', 'caption' => 'An evening around the campfire'],
            ['src' => 'img/sleeping_under_stars/tour_3.jpeg', 'alt' => 'Jeep parked beside a thatched shelter among the sand dunes', 'caption' => 'A stop in the desert'],
            ['src' => 'img/sleeping_under_stars/tour_4.jpeg', 'alt' => 'Traveller riding a camel across the desert dunes', 'caption' => 'A camel ride through the dunes'],
            ['src' => 'img/sleeping_under_stars/tour_6.jpeg', 'alt' => 'Ripples across the sand dunes in the Jaisalmer desert', 'caption' => 'Sand dunes in daylight'],
            ['src' => 'img/sleeping_under_stars/tour_7.jpeg', 'alt' => 'Mattress and patterned pillows set out on the sand', 'caption' => 'Bedding beneath the open sky'],
            ['src' => 'img/sleeping_under_stars/tour_8.jpeg', 'alt' => 'Jeep travelling over sandy ground with dunes in the background', 'caption' => 'Travel through the desert'],
            ['src' => 'img/sleeping_under_stars/tour_9.jpeg', 'alt' => 'Desert campfire, shelter and jeep at dusk', 'caption' => 'The camp after sunset'],
            ['src' => 'img/sleeping_under_stars/tour_10.jpeg', 'alt' => 'Fruit and sandwiches arranged on a patterned mat', 'caption' => 'Food served during the overnight experience'],
        ],
        'summary' => 'Spend the evening in the desert and stay overnight beneath the open sky with a mattress and blankets.',
        'at_a_glance' => [
            'Duration' => '1 night',
            'Overnight' => 'Open-air under the stars',
            'Camel ride' => 'Yes',
            'Meals' => 'Dinner and breakfast',
            'Transport' => 'Jeep transport',
            'Return' => 'Following day',
        ],
        'comparison_label' => 'Open-sky overnight',
        'comparison_facts' => [
            ['label' => 'Sleep setup', 'value' => 'Open-air, with a mattress and blankets.'],
            ['label' => 'Bathroom', 'value' => 'Not the same setup as a Swiss Deluxe Tent; ask the hotel about the current arrangement before booking.'],
        ],
        'timeline_label' => 'Typical sequence',
        'timeline_note' => 'The exact departure and return times vary by season and operating conditions. Confirm the final schedule with the hotel.',
        'timeline' => [
            ['step' => '01', 'title' => 'Depart Jaisalmer', 'text' => 'Travel by jeep from Jaisalmer towards the desert.'],
            ['step' => '02', 'title' => 'Kuldhara and local stops', 'text' => 'Visit Kuldhara, continue to the natural pond and a nomadic village as part of the route.'],
            ['step' => '03', 'title' => 'Camel ride', 'text' => 'Ride by camel towards the dunes.'],
            ['step' => '04', 'title' => 'Sunset', 'text' => 'Watch the sunset from the dunes with tea, coffee and snacks.'],
            ['step' => '05', 'title' => 'Desert dinner', 'text' => 'Have a pure-vegetarian dinner prepared in the desert.'],
            ['step' => '06', 'title' => 'Sleep under the stars', 'text' => 'Settle into a mattress and blankets beneath the open sky.'],
            ['step' => '07', 'title' => 'Breakfast and return', 'text' => 'Have breakfast in the morning, then return to Jaisalmer.'],
        ],
        'included' => [
            'Jeep transport',
            'Visit to the abandoned village of Kuldhara',
            'Drive to a natural pond',
            'Visit to a nomadic village',
            'Camel riding',
            'Tea, coffee and snacks at sunset',
            'Pure-vegetarian dinner prepared in the desert',
            'Mattress and blankets for sleeping under the stars',
            'Morning breakfast',
        ],
        'not_included' => [
            'Personal expenses',
            'Anything not specifically listed in the confirmed package',
        ],
        'best_for' => 'Guests who want a simple overnight desert experience and an open-sky night rather than tent accommodation.',
        'before_book' => 'Night temperatures can be low in winter. Ask about bedding, weather conditions and the current overnight arrangement before confirming.',
        'faq' => [
            ['question' => 'Where do we sleep?', 'answer' => 'The overnight arrangement is open-air in the desert, with a mattress and blankets rather than a tent room.'],
            ['question' => 'Is there a bathroom in the desert?', 'answer' => 'This is an open-sky desert experience, so it does not provide the same bathroom setup as a Swiss Deluxe Tent. Ask the hotel about the current arrangements before booking.'],
            ['question' => 'Is it suitable in winter?', 'answer' => 'Winter nights in the desert can be cold. Ask about current bedding and weather conditions for your travel date.'],
        ],
        
        'related' => ['sunset-camel-safari', 'swiss-tent-safari', 'cultural-safari'],
    ],

    'swiss-tent-safari' => [
        'slug' => 'swiss-tent-safari',
        'category' => 'desert',
        'parent' => 'desert-safari',
        'menu_label' => 'Swiss tent safari',
        'duration_type' => 'overnight',
        'order' => 30,
        'status' => 'live',
        'title' => 'Swiss Tent Safari in Jaisalmer | Garh Jaisal Haveli',
        'description' => 'Spend a night in the Jaisalmer desert in a Swiss Deluxe Tent with camel riding, sunset, cultural programme, dinner and breakfast.',
        'h1' => 'Overnight Safari in a Swiss Tent',
        'eyebrow' => 'Overnight Swiss tent',
        'hero_image' => 'img/v2/swiss-tent.webp',
        'hero_width' => 750,
        'hero_height' => 750,
        'hero_alt' => 'White Swiss-style tents at a desert accommodation near Jaisalmer',
        'hero_caption' => 'Check the tent style, facilities and date-specific availability before choosing this stay.',
        'comparison_image' => 'img/Desert_Camp/Tent.jpeg',
        'comparison_image_alt' => 'Double bed and patterned wall panels inside a Swiss tent',
        'summary' => 'Spend an evening in the desert with a camel ride and cultural programme, then stay overnight in a fully equipped Swiss Deluxe Tent.',
        'at_a_glance' => [
            'Duration' => '1 night',
            'Accommodation' => 'Swiss Deluxe Tent',
            'Camel ride' => 'Yes',
            'Meals' => 'High tea, dinner and breakfast',
            'Evening' => 'Cultural programme and folk music',
            'Transport' => 'Jeep transport',
        ],
        'comparison_label' => 'Swiss tent overnight',
        'comparison_facts' => [
            ['label' => 'Sleep setup', 'value' => 'Swiss Deluxe Tent accommodation.'],
            ['label' => 'Bathroom', 'value' => 'Attached bathroom with hot and cold shower.'],
        ],
        'facilities' => [
            'King-size bed',
            'Attached bathroom with hot and cold shower',
            'Tea and coffee maker',
            'Toiletries',
            'Study table',
            'Full-length mirror',
            'Towels',
        ],
        'gallery_title' => 'The camp and Swiss tents',
        'gallery_intro' => 'Explore the desert camp, tent accommodation and dining setting before you enquire.',
        'gallery' => [
            ['src' => 'img/Desert_Camp/camp_from_above.jpeg', 'alt' => 'Desert camp viewed from above near Jaisalmer', 'caption' => 'The desert camp viewed from above'],
            ['src' => 'img/Desert_Camp/camp_evening.jpeg', 'alt' => 'Evening at the desert camp near Jaisalmer', 'caption' => 'The camp in the evening'],
            ['src' => 'img/Desert_Camp/CampingInJsm.jpeg', 'alt' => 'Camping experience near Jaisalmer', 'caption' => 'The desert camping experience'],
            ['src' => 'img/Desert_Camp/tent_deluxe.jpeg', 'alt' => 'Deluxe tent accommodation at the desert camp near Jaisalmer', 'caption' => 'Swiss Deluxe Tent accommodation'],
            ['src' => 'img/Desert_Camp/camp_dinner.jpeg', 'alt' => 'Dinner at the desert camp near Jaisalmer', 'caption' => 'Dining at the desert camp'],
            ['src' => 'img/Desert_Camp/Bathroom.jpeg', 'alt' => 'Bathroom inside a Swiss tent with a shower, basin and toilet', 'caption' => 'The attached bathroom'],
            ['src' => 'img/Desert_Camp/Outtent.jpeg', 'alt' => 'White Swiss-style tent exterior at the desert camp', 'caption' => 'Swiss tent exterior'],
            ['src' => 'img/Desert_Camp/Tent.jpeg', 'alt' => 'Double bed and patterned wall panels inside a Swiss tent', 'caption' => 'Inside the Swiss Deluxe Tent'],
            ['src' => 'img/Desert_Camp/Tentout.jpeg', 'alt' => 'White tents with planted greenery outside the accommodation', 'caption' => 'The camp during the day'],
            ['src' => 'img/Desert_Camp/tentout1.jpeg', 'alt' => 'A row of white desert tents at dusk', 'caption' => 'The camp at dusk'],
        ],
        'timeline_label' => 'Typical sequence',
        'timeline_note' => 'The final departure, sunset and programme times depend on the season and camp operations.',
        'timeline' => [
            ['step' => '01', 'title' => 'Depart Jaisalmer', 'text' => 'Travel by jeep into the desert.'],
            ['step' => '02', 'title' => 'Route and local stops', 'text' => 'Visit Kuldhara, continue through the natural pond and nomadic village route.'],
            ['step' => '03', 'title' => 'Camel ride', 'text' => 'Continue into the dunes by camel.'],
            ['step' => '04', 'title' => 'Welcome at the camp', 'text' => 'Arrive at camp and settle into the evening programme.'],
            ['step' => '05', 'title' => 'High tea and cultural programme', 'text' => 'Enjoy high tea with snacks followed by folk music and the cultural programme.'],
            ['step' => '06', 'title' => 'Dinner and overnight', 'text' => 'Have a vegetarian buffet dinner and spend the night in the Swiss Deluxe Tent.'],
            ['step' => '07', 'title' => 'Breakfast and return', 'text' => 'Have breakfast before returning to Jaisalmer.'],
        ],
        'included' => [
            'Jeep transport',
            'Visit to the abandoned village of Kuldhara',
            'Drive to a natural pond',
            'Visit to a nomadic village',
            'Camel riding',
            'Welcome at the camp',
            'High tea with snacks',
            'Cultural programme and folk music',
            'Buffet vegetarian dinner',
            'Accommodation in a Swiss Deluxe Tent',
            'Morning breakfast',
        ],
        'not_included' => [
            'Alcoholic beverages',
            'Soft drinks',
            'Separate jeep safari excursion (must be booked separately)',
            'Personal expenses',
            'Anything not specifically listed in the confirmed package',
        ],
        'best_for' => 'Guests who want an overnight desert experience with tent accommodation instead of sleeping in the open.',
        'before_book' => 'Every Swiss tent has a king-size bed, attached bathroom with hot and cold shower, tea and coffee maker, toiletries, study table, full-length mirror and towels. Contact the hotel to confirm availability and the final itinerary for your travel date.',
        'faq' => [
            ['question' => 'What is the accommodation?', 'answer' => 'The experience is an overnight stay in a Swiss Deluxe Tent. Every tent has a king-size bed and an attached bathroom with hot and cold shower.'],
            ['question' => 'What facilities are provided in the tent?', 'answer' => 'Each tent includes a tea and coffee maker, toiletries, study table, full-length mirror and towels.'],
            ['question' => 'Does it include a cultural programme?', 'answer' => 'The current package description includes an evening cultural programme with folk music, subject to camp operations.'],
            ['question' => 'Is this different from sleeping under the stars?', 'answer' => 'Yes. The Swiss Tent option provides tent accommodation, while Sleep Under the Stars is an open-air overnight experience.'],
        ],
        
        'related' => ['sleep-under-the-stars', 'cultural-safari', 'sunset-camel-safari'],
    ],

    'cultural-safari' => [
        'slug' => 'cultural-safari',
        'category' => 'desert',
        'parent' => 'desert-safari',
        'menu_label' => 'Cultural evening',
        'duration_type' => 'half-day',
        'order' => 40,
        'status' => 'live',
        'title' => 'Cultural Desert Safari in Jaisalmer | Garh Jaisal Haveli',
        'description' => 'Combine a desert safari and camel ride with tea, snacks, folk music and a vegetarian dinner before returning to Jaisalmer.',
        'h1' => 'Cultural Desert Safari in Jaisalmer',
        'eyebrow' => 'Desert and cultural evening',
        'hero_image' => 'img/Desert_Camp/Prog.jpeg',
        'hero_width' => 1366,
        'hero_height' => 768,
        'hero_alt' => 'An evening cultural programme at a desert camp near Jaisalmer',
        'hero_caption' => 'Ask the hotel to confirm the current performance programme and package inclusions.',
        'summary' => 'A desert evening that combines the route and camel experience with music, cultural programme and dinner at camp.',
        'at_a_glance' => [
            'Duration' => 'Afternoon to evening',
            'Overnight' => 'No',
            'Camel ride' => 'Yes',
            'Evening' => 'Folk music and cultural programme',
            'Meals' => 'Tea, snacks and vegetarian dinner',
            'Transport' => 'Jeep transport',
        ],
        'timeline_label' => 'Typical sequence',
        'timeline_note' => 'The timing of sunset and the cultural programme varies with the season and camp schedule.',
        'timeline' => [
            ['step' => '01', 'title' => 'Depart Jaisalmer', 'text' => 'Travel by jeep into the desert.'],
            ['step' => '02', 'title' => 'Route and local stops', 'text' => 'Visit Kuldhara, continue to the natural pond and nomadic village route.'],
            ['step' => '03', 'title' => 'Camel ride', 'text' => 'Ride through the dunes by camel.'],
            ['step' => '04', 'title' => 'Camp arrival', 'text' => 'Arrive at camp and settle in for the evening.'],
            ['step' => '05', 'title' => 'Tea and cultural programme', 'text' => 'Enjoy tea and snacks followed by folk music and the cultural programme.'],
            ['step' => '06', 'title' => 'Dinner', 'text' => 'Have a vegetarian buffet dinner at the camp.'],
            ['step' => '07', 'title' => 'Return to Jaisalmer', 'text' => 'Return to town after the evening programme.'],
        ],
        'included' => [
            'Jeep transport',
            'Visit to the abandoned village of Kuldhara',
            'Drive to a natural pond',
            'Visit to a nomadic village',
            'Camel riding',
            'Welcome at the camp',
            'Tea and snacks',
            'Cultural programme and folk music',
            'Buffet vegetarian dinner',
        ],
        'not_included' => [
            'Accommodation',
            'Personal expenses',
            'Anything not specifically listed in the confirmed package',
        ],
        'best_for' => 'Guests who want an evening desert experience with music and dinner but do not need an overnight stay.',
        'before_book' => 'Ask for the current cultural programme, approximate return time and dinner arrangements for your date.',
        'faq' => [
            ['question' => 'What happens during the cultural evening?', 'answer' => 'The experience includes desert time followed by an evening cultural programme and dinner, with the exact programme depending on camp operations.'],
            ['question' => 'Does the experience include an overnight stay?', 'answer' => 'The current package description should be checked for your selected option. Ask the hotel to confirm whether the version you are booking is same-day or overnight.'],
            ['question' => 'Can the timing change by season?', 'answer' => 'Yes. Desert departure, sunset and evening programme times change through the year.'],
        ],
        
        'related' => ['sunset-camel-safari', 'swiss-tent-safari', 'sleep-under-the-stars'],
    ],

    'explore-the-desert' => [
        'slug' => 'explore-the-desert',
        'category' => 'desert',
        'parent' => 'desert-safari',
        'menu_label' => 'Custom desert day',
        'duration_type' => 'flexible',
        'order' => 50,
        'status' => 'live',
        'enquiry_only' => true,
        'planning_url' => 'custom-itinerary.php?idea=custom-desert',
        'title' => 'Custom Desert Day in Jaisalmer | Garh Jaisal Haveli',
        'description' => 'Ask about a customised desert day around Jaisalmer, with heritage stops, landscapes and camel-free or camel-ride options subject to confirmation.',
        'h1' => 'Explore the Desert Your Way',
        'eyebrow' => 'Custom desert enquiry',
        'hero_image' => 'img/desert10.jpg',
        'hero_width' => 1366,
        'hero_height' => 768,
        'hero_alt' => 'A jeep driving across sand dunes near Jaisalmer',
        'hero_caption' => 'Explore the desert landscape and discuss the kind of outing that suits your group.',
        'summary' => 'Prefer a flexible desert day? Ask about village heritage, photography, sunset, jeep travel or a camel ride. We will confirm a suitable route before you decide.',
        'at_a_glance' => [
            'Format' => 'Custom enquiry',
            'Duration' => 'Agree for your dates',
            'Transport' => 'Ask about jeep options',
            'Camel ride' => 'Optional request',
            'Return' => 'Ask about same-day routes',
        ],
        'timeline_label' => 'Choose your interests',
        'timeline_note' => 'These are ideas to discuss, not an operating itinerary.',
        'timeline' => [
            [
                'step' => '01',
                'title' => 'Heritage and landscapes',
                'text' => 'Ask about Kuldhara, village stops or desert viewpoints.',
            ],
            [
                'step' => '02',
                'title' => 'Choose your desert style',
                'text' => 'Tell us if you prefer a camel ride, camel-free travel, sunrise or sunset.',
            ],
            [
                'step' => '03',
                'title' => 'Agree your route',
                'text' => 'Confirm timing, vehicle, refreshments, access and price with the hotel.',
            ],
        ],
        'included' => [
            'Pickup and transport preferences',
            'Desired activities, pace and stops',
            'Refreshments and any fees to be quoted',
        ],
        'not_included' => [],
        'best_for' => 'Guests who want a tailored outing rather than a fixed safari.',
        'before_book' => 'There is no fixed package on this page. Compare the published sunset and overnight experiences.',
        'faq' => [
            [
                'question' => 'Can I ask for a camel-free outing?',
                'answer' => 'Yes, you can request one. Transport, route and availability need confirmation.',
            ],
            [
                'question' => 'Is this a fixed full-day safari?',
                'answer' => 'No. This page helps you ask for a custom route; the sunset safari remains a separate published option.',
            ],
        ],
        'related' => [
            'sunset-camel-safari',
            'cultural-safari',
            'sleep-under-the-stars',
        ],
    ],

    'half-day-jaisalmer-tour' => [
        'slug' => 'half-day-jaisalmer-tour',
        'category' => 'city',
        'parent' => 'guided-jaisalmer-tour',
        'menu_label' => 'Half-day Jaisalmer tour',
        'duration_type' => 'half-day',
        'order' => 10,
        'status' => 'live',
        'title' => 'Half-Day Guided Jaisalmer Tour | Garh Jaisal Haveli',
        'description' => 'Explore Jaisalmer Fort on a half-day guided tour with Jain temples, a local temple, heritage lanes and Patwon Ki Haveli. Enquire for your dates.',
        'h1' => 'Half-Day Guided Jaisalmer Tour',
        'eyebrow' => 'Half-day city tour',
        'hero_image' => 'img/j5.jpg',
        'hero_width' => 1366,
        'hero_height' => 768,
        'hero_alt' => 'Carved sandstone balconies on a historic Jaisalmer haveli',
        'hero_caption' => 'See the living Fort and carved sandstone at Patwon Ki Haveli.',
        'summary' => 'Walk the lanes of Jaisalmer Fort with a guide, visit the Jain temples and a local temple, then continue to Patwon Ki Haveli.',
        'at_a_glance' => [
            'Duration' => 'Half day',
            'Format' => 'Guided tour',
            'Focus' => 'Fort temples, heritage lanes and Patwon Ki Haveli',
            'Starting point' => 'Garh Jaisal Haveli / Jaisalmer Fort',
            'Best for' => 'First-time visitors with limited time',
            'Guide languages' => 'English, Hindi, Bengali, Italian, French, Spanish and German (confirm availability)',
        ],
        'timeline_label' => 'Typical sequence',
        'timeline_note' => 'The exact route can be adapted to opening hours, weather, guest interests and guide availability.',
        'timeline' => [
            ['step' => '01', 'title' => 'Meet at the haveli', 'text' => 'Begin from Garh Jaisal Haveli inside Jaisalmer Fort.'],
            ['step' => '02', 'title' => 'Temples and lanes inside the Fort', 'text' => 'Visit the Jain temples and a local temple, then walk through the historic lanes with your guide.'],
            ['step' => '03', 'title' => 'Patwon Ki Haveli', 'text' => 'Continue to Patwon Ki Haveli to see the carved sandstone architecture.'],
            ['step' => '04', 'title' => 'Finish in the old city', 'text' => 'Finish the guided portion from the agreed end point inside or around the old city.'],
        ],
        'included' => [
            'Local guide',
            'Guided walking route through Jaisalmer heritage areas',
            'Fort and heritage context',
            'Fort temples, a street walk and Patwon Ki Haveli',
        ],
        'not_included' => [
            'Entry fees where applicable',
            'Meals and personal purchases',
            'Private transport unless specifically arranged',
        ],
        'best_for' => 'Guests with half a day available who want a focused introduction to Jaisalmer.',
        'before_book' => 'Message on WhatsApp to confirm the current price, guide-language availability, starting time, route, entry fees and any special interests for your date.',
        'faq' => [
            ['question' => 'What does the half-day tour cover?', 'answer' => 'The half-day tour covers the Jain temples and a local temple inside Jaisalmer Fort, a guided street walk and Patwon Ki Haveli. Access depends on opening hours.'],
            ['question' => 'Does it include entry fees?', 'answer' => 'Entry fees are not included unless specifically confirmed in the booking.'],
            ['question' => 'Can the tour start from the hotel?', 'answer' => 'The planned starting point is Garh Jaisal Haveli inside Jaisalmer Fort, subject to the confirmed itinerary. Ask on WhatsApp to confirm guide availability in English, Hindi, Bengali, Italian, French, Spanish or German and the current price for your date.'],
        ],
        
        'related' => ['full-day-jaisalmer-tour', 'sunset-camel-safari'],
    ],

    'full-day-jaisalmer-tour' => [
        'slug' => 'full-day-jaisalmer-tour',
        'category' => 'city',
        'parent' => 'guided-jaisalmer-tour',
        'menu_label' => 'Full-day Jaisalmer tour',
        'duration_type' => 'full-day',
        'order' => 20,
        'status' => 'live',
        'title' => 'Full-Day Guided Jaisalmer Tour | Garh Jaisal Haveli',
        'description' => 'Take a full-day guided Jaisalmer tour covering the Fort temples, heritage lanes, three historic havelis and a walk beside Gadisar Lake.',
        'h1' => 'Full-Day Guided Jaisalmer Tour',
        'eyebrow' => 'Full-day city tour',
        'hero_image' => 'img/j2.jpg',
        'hero_width' => 1333,
        'hero_height' => 2000,
        'hero_alt' => 'Carved sandstone architecture in historic Jaisalmer',
        'hero_caption' => 'Plan enough time for each stop included in the route confirmed with your guide.',
        'summary' => 'Follow the half-day Fort route, then see all three historic havelis and walk beside Gadisar Lake.',
        'at_a_glance' => [
            'Duration' => 'Full day',
            'Format' => 'Guided tour',
            'Focus' => 'Fort temples, three havelis and Gadisar Lake',
            'Starting point' => 'Garh Jaisal Haveli / Jaisalmer Fort',
            'Best for' => 'Guests who want a broader city introduction',
            'Guide languages' => 'English, Hindi, Bengali, Italian, French, Spanish and German (confirm availability)',
        ],
        'timeline_label' => 'Typical sequence',
        'timeline_note' => 'The route is planned around current opening hours, weather, traffic and the sights guests want to prioritise.',
        'timeline' => [
            ['step' => '01', 'title' => 'Meet and start inside the Fort', 'text' => 'Begin at Garh Jaisal Haveli and start the guided heritage route through Jaisalmer Fort.'],
            ['step' => '02', 'title' => 'Temples and lanes inside the Fort', 'text' => 'Follow the half-day walk through the Fort lanes, with stops at the Jain temples and a local temple.'],
            ['step' => '03', 'title' => 'Three historic havelis', 'text' => 'See Patwon Ki Haveli, Nathmal Ki Haveli and Salim Singh Ki Haveli. Ask your guide which interiors are open for your date.'],
            ['step' => '04', 'title' => 'Gadisar Lake walk', 'text' => 'Walk beside Gadisar Lake and take time to see the lakeside pavilions.'],
            ['step' => '05', 'title' => 'Tour finish', 'text' => 'Conclude at the agreed end point in Jaisalmer and return independently or continue with your next plan.'],
        ],
        'included' => [
            'Local guide',
            'Guided heritage route through Jaisalmer',
            'Fort temples and lanes, three historic havelis and a walk beside Gadisar Lake',
            'Time for questions and local context',
        ],
        'not_included' => [
            'Entry fees where applicable',
            'Meals and personal purchases',
            'Private transport unless specifically arranged',
        ],
        'best_for' => 'Guests who want to spend most of a day understanding Jaisalmer rather than seeing only the main highlights.',
        'before_book' => 'Message on WhatsApp to confirm the current price, guide-language availability, starting time, route, entry fees and any specific places you want included.',
        'faq' => [
            ['question' => 'What does the full-day tour cover?', 'answer' => 'The full-day tour adds Nathmal Ki Haveli, Salim Singh Ki Haveli and a walk beside Gadisar Lake to the half-day route, which already includes the Fort temples, lanes and Patwon Ki Haveli.'],
            ['question' => 'Are meals included?', 'answer' => 'Meals are not included unless specifically confirmed in the booking.'],
            ['question' => 'Can we request specific places?', 'answer' => 'Yes. Tell the hotel what you want to prioritise so the route can be discussed with the guide before the tour. Ask on WhatsApp to confirm guide availability in English, Hindi, Bengali, Italian, French, Spanish or German and the current price for your date.'],
        ],
        
        'related' => ['half-day-jaisalmer-tour', 'sunset-camel-safari'],
    ],

    'longewala-tour' => [
        'slug' => 'longewala-tour',
        'category' => 'city',
        'parent' => 'car-tours',
        'menu_label' => 'Longewala enquiry',
        'duration_type' => 'flexible',
        'order' => 80,
        'status' => 'live',
        'enquiry_only' => true,
        'planning_url' => 'contact.php?topic=longewala-history',
        'title' => 'Longewala Excursion from Jaisalmer | Garh Jaisal',
        'description' => 'Enquire about a possible Longewala heritage excursion from Jaisalmer. Ask the hotel to confirm visitor access, transport, timings and a quote.',
        'h1' => 'Longewala Excursion Enquiry',
        'eyebrow' => 'Heritage day-trip idea',
        'hero_image' => 'img/desert10.jpg',
        'hero_width' => 1333,
        'hero_height' => 2000,
        'hero_alt' => 'A jeep crossing open sand on a desert route near Jaisalmer',
        'hero_caption' => 'A regional desert landscape; exact excursion stops and access are confirmed separately.',
        'summary' => 'Interested in the Longewala War Memorial or a regional heritage excursion? Share your dates and preferred stops so we can check feasibility and private transport.',
        'at_a_glance' => [
            'Format' => 'Enquiry only',
            'Focus' => 'Regional history',
            'Transport' => 'Ask about a private car',
            'Access' => 'Must be checked',
            'Price' => 'Quote on request',
        ],
        'timeline_label' => 'Plan your enquiry',
        'timeline_note' => 'No fixed route or departure time is offered.',
        'timeline' => [
            [
                'step' => '01',
                'title' => 'Share your dates',
                'text' => 'Tell us your group size and available time.',
            ],
            [
                'step' => '02',
                'title' => 'Choose your interests',
                'text' => 'Ask whether Longewala or Tanot can be included; separate outings may be needed.',
            ],
            [
                'step' => '03',
                'title' => 'Confirm feasibility',
                'text' => 'Agree current access, route, transport, timing and total price before travel.',
            ],
        ],
        'included' => [
            'Route and visitor-access checks',
            'Vehicle, pickup and return arrangements',
            'Any guide, entry fees or meals to be quoted',
        ],
        'not_included' => [],
        'best_for' => 'Guests interested in regional history and a possible private excursion.',
        'before_book' => 'Availability and visitor requirements vary. Do not make onward arrangements until the hotel confirms the route.',
        'faq' => [
            [
                'question' => 'Is this a confirmed tour?',
                'answer' => 'No. The hotel must confirm whether the excursion can be arranged.',
            ],
            [
                'question' => 'Can Tanot be combined with Longewala?',
                'answer' => 'Ask the hotel to check whether both stops are practical for your date and available time.',
            ],
        ],
        'related' => [
            'full-day-jaisalmer-tour',
            'rajasthan-tours',
        ],
    ],
    'rajasthan-tours' => [
        'slug' => 'rajasthan-tours',
        'category' => 'city',
        'parent' => 'car-tours',
        'menu_label' => 'Rajasthan journeys',
        'duration_type' => 'flexible',
        'order' => 90,
        'status' => 'live',
        'enquiry_only' => true,
        'planning_url' => 'custom-itinerary.php?mode=rajasthan',
        'title' => 'Private Rajasthan Car & Hotel Tours | Garh Jaisal',
        'description' => 'Plan a private Rajasthan journey with a driver, or ask for car and hotel arrangements together. Share cities, dates and preferences for a tailored quote.',
        'h1' => 'Private Rajasthan Journeys',
        'eyebrow' => 'Car-only or car with hotels',
        'hero_image' => 'img/1.webp',
        'hero_width' => 1333,
        'hero_height' => 2000,
        'hero_alt' => 'Panoramic view across Jaisalmer\'s golden sandstone city',
        'hero_caption' => 'Start or finish your Rajasthan journey in Jaisalmer and discuss other destinations with the hotel.',
        'summary' => 'Travel across Rajasthan at your own pace. Request a private car with driver, or ask us to arrange the car and hotels together, including Delhi Airport transfers.',
        'at_a_glance' => [
            'Travel style' => 'Private car enquiry',
            'Options' => 'Car only or car + hotels',
            'Route' => 'Tailored to your dates',
            'Pickup' => 'Rajasthan or Delhi Airport',
            'Price' => 'Quote on request',
        ],
        'timeline_label' => 'Private journeys by road',
        'timeline_note' => 'Pick a sample route and ask for a private-car quote, with or without hotels. Drive times are planning estimates between cities. Allow extra time for stops, traffic and flights.',
        'timeline' => [],
        'sample_routes' => require __DIR__ . '/rajasthan-route-ideas.php',
        'included' => [
            'Private car and driver requirements',
            'Hotel preferences if accommodation is requested',
            'Pickup, drop-off and preferred stops',
            'Written quote with inclusions and exclusions',
        ],
        'not_included' => [],
        'best_for' => 'Couples, families and groups planning a flexible multi-city journey.',
        'before_book' => 'Share your dates, group size, preferred route and airport or hotel pickup. Ask for a written quote covering the vehicle, hotel stays if requested, tolls, parking, driver costs, guides and entry fees.',
        'faq' => [
            [
                'question' => 'Can I book only the car?',
                'answer' => 'Yes, request car-only travel or ask for accommodation to be arranged as well.',
            ],
            [
                'question' => 'Can I start or finish at Delhi Airport?',
                'answer' => 'Delhi Airport pickup and return can be requested. Share flight and route details for a quote.',
            ],
        ],
        'related' => [
            'full-day-jaisalmer-tour',
            'sunset-camel-safari',
            'explore-the-desert',
        ],
    ],
];

$scheduledDesertDeparture = (require __DIR__ . '/guest-booking-facts.php')['desert_departures'];
foreach (['sunset-camel-safari', 'cultural-safari', 'sleep-under-the-stars', 'swiss-tent-safari'] as $scheduledDesertSlug) {
    $facts = $experiences[$scheduledDesertSlug]['at_a_glance'];
    $experiences[$scheduledDesertSlug]['at_a_glance'] = array_slice($facts, 0, 1, true)
        + ['Departure' => $scheduledDesertDeparture] + $facts;
    $experiences[$scheduledDesertSlug]['timeline'][0]['text'] =
        'Depart at ' . $scheduledDesertDeparture . '. ' . $experiences[$scheduledDesertSlug]['timeline'][0]['text'];
}
unset($scheduledDesertDeparture, $scheduledDesertSlug, $facts);

$studioRoot = dirname(__DIR__) . '/content/experiences';
$studioFiles = is_dir($studioRoot) ? (glob($studioRoot . '/*.json') ?: []) : [];
sort($studioFiles, SORT_STRING);
foreach ($studioFiles as $studioFile) {
    if (is_link($studioFile) || !is_file($studioFile)) throw new RuntimeException('Unsafe Content Studio experience record: ' . basename($studioFile));
    $record = json_decode((string) file_get_contents($studioFile), true);
    $slug = basename($studioFile, '.json');
    if (
        !is_array($record) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)
        || ($record['slug'] ?? null) !== $slug
        || !in_array(($record['status'] ?? ''), ['live', 'draft', 'hidden'], true)
        || !in_array(($record['category'] ?? ''), ['desert', 'city'], true)
        || !is_string($record['title'] ?? null) || !is_string($record['description'] ?? null)
        || !is_string($record['h1'] ?? null) || !is_string($record['hero_image'] ?? null)
        || !is_string($record['hero_alt'] ?? null) || trim($record['hero_alt']) === '' || !is_string($record['summary'] ?? null)
        || !is_array($record['at_a_glance'] ?? null) || !is_array($record['timeline'] ?? null)
        || !is_array($record['included'] ?? null) || !is_array($record['not_included'] ?? null)
    ) throw new RuntimeException('Invalid Content Studio experience record: ' . $slug);
    if (array_key_exists($slug, $experiences)) throw new RuntimeException('Duplicate experience slug in Content Studio registry: ' . $slug);
    $experiences[$slug] = $record;
}

$experiences = (static function ($experienceRecords) {
    // Keep all temporary names in closure scope: this file is required inside
    // the public route wrapper, where $experienceSlug identifies the request.
    $pageMediaSlots = require __DIR__ . '/site-media-slots.php';
    foreach ($experienceRecords as $pageMediaSlug => &$pageMediaRecord) {
        $pageMediaImage = gj_site_media_slot($pageMediaSlots, 'experience-' . $pageMediaSlug . '-hero', [
            'src' => $pageMediaRecord['hero_image'],
            'alt' => $pageMediaRecord['hero_alt'],
            'caption' => $pageMediaRecord['hero_caption'] ?? '',
        ]);
        $pageMediaRecord['hero_image'] = $pageMediaImage['src'];
        $pageMediaRecord['hero_alt'] = $pageMediaImage['alt'];
        if (trim((string) ($pageMediaImage['caption'] ?? '')) !== '') {
            $pageMediaRecord['hero_caption'] = $pageMediaImage['caption'];
        }
    }
    unset($pageMediaRecord, $pageMediaSlug, $pageMediaImage, $pageMediaSlots);
    return $experienceRecords;
})($experiences);

return $experiences;

