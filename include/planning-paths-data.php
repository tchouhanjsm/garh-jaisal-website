<?php
/**
 * Reviewed planning destinations for every live route in sitemap.xml.
 * Keep all destinations on published, same-site PHP pages.
 */
$choices = [
    'ask_room' => [
        'label' => 'Ask about room availability',
        'href' => 'contact.php?topic=room-availability',
        'description' => 'Share your dates and room preferences so the hotel can confirm rates, facilities and availability.',
    ],
    'ask_car' => [
        'label' => 'Ask about car arrangements',
        'href' => 'contact.php?topic=car-arrangements',
        'description' => 'Share your pickup, destination, date, passenger count and luggage so the hotel can confirm options.',
    ],
    'ask_arrival' => [
        'label' => 'Confirm arrival arrangements',
        'href' => 'contact.php?topic=arrival',
        'description' => 'Share your expected arrival time so the hotel can confirm current instructions for your date.',
    ],
    'ask_dining' => [
        'label' => 'Ask about rooftop dining',
        'href' => 'contact.php?topic=dining',
        'description' => 'Share your date, preferred time and number of guests to ask about dining options.',
    ],
    'ask_visit' => [
        'label' => 'Ask about your visit',
        'href' => 'contact.php?topic=visit-planning',
        'description' => 'Share your dates, trip length and interests to ask about current options.',
    ],
    'ask_custom' => [
        'label' => 'Ask about a custom itinerary',
        'href' => 'contact.php?topic=custom-itinerary',
        'description' => 'Share your dates, available time and interests so the hotel can review a possible route.',
    ],
    'ask_packing' => [
        'label' => 'Ask about safari preparation',
        'href' => 'contact.php?topic=desert-packing',
        'description' => 'Share the experience and date to check current inclusions and practical preparation details.',
    ],
    'ask_fort' => [
        'label' => 'Ask about a Fort visit',
        'href' => 'contact.php?topic=fort-guide',
        'description' => 'Share your visit date and preferred guide language to confirm route and access details.',
    ],
    'ask_desert' => [
        'label' => 'Ask about desert safari options',
        'href' => 'contact.php?topic=desert-safari',
        'description' => 'Share your date and sunset or overnight preference to confirm current options and inclusions.',
    ],
    'ask_two_day' => [
        'label' => 'Ask about a two-day itinerary',
        'href' => 'contact.php?topic=two-day-itinerary',
        'description' => 'Share your travel dates and priorities to discuss a realistic two-day plan.',
    ],
    'ask_three_day' => [
        'label' => 'Ask about a three-day itinerary',
        'href' => 'contact.php?topic=three-day-itinerary',
        'description' => 'Share your travel dates and priorities to discuss a flexible three-day plan.',
    ],
    'ask_honeymoon' => [
        'label' => 'Ask about honeymoon planning',
        'href' => 'contact.php?topic=honeymoon',
        'description' => 'Share your dates and room, dining or desert priorities to discuss date-specific options.',
    ],
    'ask_havelis' => [
        'label' => 'Ask about a heritage route',
        'href' => 'contact.php?topic=heritage-havelis',
        'description' => 'Share your date and architecture interests to ask about a possible heritage route.',
    ],
    'rooms' => [
        'label' => 'Compare rooms',
        'href' => 'room.php',
        'description' => 'Review room features and check current availability for your dates.',
    ],
    'booking' => [
        'label' => 'Direct booking guide',
        'href' => 'book-rooms-in-jaisalmer.php',
        'description' => 'Review the booking steps and details to confirm before your stay.',
    ],
    'arrival' => [
        'label' => 'Plan your arrival',
        'href' => 'direction.php',
        'description' => 'Read where vehicles stop and plan the final walk inside the Fort.',
    ],
    'dining' => [
        'label' => 'Rooftop dining',
        'href' => 'roof.php',
        'description' => 'Explore rooftop dining and confirm current hours and menu options.',
    ],
    'restaurant' => [
        'label' => 'Restaurant details',
        'href' => 'restaurant.php',
        'description' => 'Review dining information and ask about a table for your date.',
    ],
    'desert' => [
        'label' => 'Desert experiences',
        'href' => 'desert-safari.php',
        'description' => 'Compare desert options and confirm date-specific inclusions.',
    ],
    'camping' => [
        'label' => 'Overnight desert stays',
        'href' => 'camping.php',
        'description' => 'Compare the open-sky and tented overnight options.',
    ],
    'tours' => [
        'label' => 'Guided city tours',
        'href' => 'guided-jaisalmer-tour.php',
        'description' => 'Compare the published guided tour options and confirm availability.',
    ],
    'fort_walk' => [
        'label' => 'Fort walk',
        'href' => 'city-tour.php',
        'description' => 'Review the Fort walk and confirm current access and guide details.',
    ],
    'car_tours' => [
        'label' => 'Car and city tours',
        'href' => 'car-tours.php',
        'description' => 'Explore existing tour options and enquire about car arrangements.',
    ],
    'car' => [
        'label' => 'Car arrangements',
        'href' => 'car-rental.php',
        'description' => 'Share your route and dates so the hotel can confirm vehicle options.',
    ],
    'custom' => [
        'label' => 'Custom itinerary enquiry',
        'href' => 'custom-itinerary.php',
        'description' => 'Share your interests and ask about possible route combinations.',
    ],
    'journal' => [
        'label' => 'Jaisalmer travel guides',
        'href' => 'blog.php',
        'description' => 'Read practical guides for the Fort, desert and planning your visit.',
    ],
    'fort_guide' => [
        'label' => 'Jaisalmer Fort guide',
        'href' => 'jaisalmer-fort-guide.php',
        'description' => 'Explore the Fort guide before planning your route.',
    ],
    'safari_guide' => [
        'label' => 'Desert safari guide',
        'href' => 'desert-safari-guide.php',
        'description' => 'Compare safari considerations and details to confirm for your date.',
    ],
    'two_day' => [
        'label' => 'Two-day itinerary',
        'href' => 'jaisalmer-2-day-itinerary.php',
        'description' => 'Use a two-day plan to organise Fort, city and desert time.',
    ],
    'three_day' => [
        'label' => 'Three-day itinerary',
        'href' => 'jaisalmer-3-day-itinerary.php',
        'description' => 'Plan a longer stay with space for heritage, dining and desert time.',
    ],
    'honeymoon' => [
        'label' => 'Honeymoon guide',
        'href' => 'jaisalmer-honeymoon-guide.php',
        'description' => 'Plan a couples trip and confirm date-specific dining and desert options.',
    ],
    'season' => [
        'label' => 'Best time to visit',
        'href' => 'best-time-to-visit-jaisalmer.php',
        'description' => 'Check seasonal planning notes before choosing your travel dates.',
    ],
    'reach' => [
        'label' => 'How to reach Jaisalmer',
        'href' => 'how-to-reach-jaisalmer.php',
        'description' => 'Review arrival options and the final approach to the Fort.',
    ],
    'packing' => [
        'label' => 'Desert packing list',
        'href' => 'jaisalmer-desert-safari-packing-list.php',
        'description' => 'Prepare for a desert outing and confirm arrangements before travel.',
    ],
    'havelis' => [
        'label' => 'Havelis and architecture',
        'href' => 'jaisalmer-havelis-guide.php',
        'description' => 'Discover carved sandstone architecture and heritage streets.',
    ],
    'food' => [
        'label' => 'Jaisalmer food guide',
        'href' => 'jaisalmer-food-guide.php',
        'description' => 'Explore local food planning and rooftop dining options.',
    ],
    'beyond' => [
        'label' => 'Beyond the Fort',
        'href' => 'jaisalmer-beyond-the-fort-guide.php',
        'description' => 'Explore places beyond the Fort and plan the route in advance.',
    ],
    'markets' => [
        'label' => 'Markets and crafts',
        'href' => 'jaisalmer-markets-and-crafts-guide.php',
        'description' => 'Plan time for local markets, crafts and heritage shopping.',
    ],
    'gallery' => [
        'label' => 'View the property gallery',
        'href' => 'gallery.php',
        'description' => 'Browse the curated photographs of the haveli and its surroundings.',
    ],
    'shortlist' => [
        'label' => 'Build a trip shortlist',
        'href' => 'catalogue.php',
        'description' => 'Collect published room, tour and experience options before enquiring.',
    ],
    'sunset' => [
        'label' => 'Sunset camel safari',
        'href' => 'sunset-camel-safari.php',
        'description' => 'Review the sunset option and confirm timing, route and current price.',
    ],
    'stars' => [
        'label' => 'Sleep Under the Stars',
        'href' => 'sleep-under-the-stars.php',
        'description' => 'Review the open-sky overnight option and ask about its current setup.',
    ],
    'tent' => [
        'label' => 'Swiss Tent Safari',
        'href' => 'swiss-tent-safari.php',
        'description' => 'Review the tented stay and confirm meals, transfers and inclusions.',
    ],
    'culture' => [
        'label' => 'Cultural desert safari',
        'href' => 'cultural-safari.php',
        'description' => 'Explore the cultural evening option and confirm the current programme.',
    ],
    'half_day' => [
        'label' => 'Half-day Jaisalmer tour',
        'href' => 'half-day-jaisalmer-tour.php',
        'description' => 'Review the half-day option and confirm guide availability and route.',
    ],
    'full_day' => [
        'label' => 'Full-day Jaisalmer tour',
        'href' => 'full-day-jaisalmer-tour.php',
        'description' => 'Review the full-day option and confirm itinerary and current price.',
    ],
];

$makePlanningPath = static function ($title, $intro, $choiceKeys) use ($choices) {
    $links = [];
    foreach ($choiceKeys as $key) {
        if (!isset($choices[$key])) {
            throw new RuntimeException('Unknown planning destination key: ' . (string) $key);
        }
        $links[] = $choices[$key];
    }
    return ['title' => $title, 'intro' => $intro, 'links' => $links];
};

$planningPathRegistry = [
    '/' => $makePlanningPath(
        'Choose the next step for your Fort stay',
        'Start with your room, then plan rooftop dining or an experience around your travel dates.',
        ['rooms', 'dining', 'ask_visit']
    ),
    '/room.php' => $makePlanningPath(
        'Build the rest of your stay',
        'Once you have shortlisted a room, plan dining and local experiences around your dates and interests.',
        ['booking', 'arrival', 'ask_room']
    ),
    '/pack.php' => $makePlanningPath(
        'Turn your interests into a Jaisalmer plan',
        'Compare desert and city options, then ask the hotel to confirm the route, timing and current inclusions.',
        ['desert', 'tours', 'ask_visit']
    ),
    '/catalogue.php' => $makePlanningPath(
        'Shortlist the options that fit your trip',
        'Collect ideas for your stay and experiences, then confirm availability and details with the hotel.',
        ['rooms', 'desert', 'ask_visit']
    ),
    '/direction.php' => $makePlanningPath(
        'Ready to plan your stay?',
        'Use the arrival guide with room details and direct contact so practical information can be confirmed before travel.',
        ['booking', 'rooms', 'ask_arrival']
    ),
    '/roof.php' => $makePlanningPath(
        'Complete your Fort visit',
        'Make dining part of a wider stay by checking room availability and planning experiences in advance.',
        ['rooms', 'restaurant', 'ask_visit']
    ),
    '/restaurant.php' => $makePlanningPath(
        'Make dining part of your stay',
        'Review rooftop dining details, compare rooms and contact the hotel to confirm current menu, hours and table options.',
        ['dining', 'rooms', 'ask_visit']
    ),
    '/gallery.php' => $makePlanningPath(
        'See the place, then plan your visit',
        'Explore the property photographs alongside room details, arrival information and practical travel guides.',
        ['rooms', 'arrival', 'ask_visit']
    ),
    '/about.php' => $makePlanningPath(
        'Explore your stay',
        'Move from the story of the haveli to the practical details for rooms, arrival and experiences.',
        ['rooms', 'arrival', 'ask_visit']
    ),
    '/contact.php' => $makePlanningPath(
        'Choose the right next step',
        'Use the room and planning guides to share a more specific question with the hotel.',
        ['booking', 'rooms', 'desert']
    ),
    '/blog.php' => $makePlanningPath(
        'Turn your reading into a travel plan',
        'Use the guides to prepare your Fort visit, work out the length of your stay and compare practical options.',
        ['fort_guide', 'two_day', 'ask_visit']
    ),
    '/book-rooms-in-jaisalmer.php' => $makePlanningPath(
        'Continue planning your Fort stay',
        'Compare room options, review the final walk through the Fort and ask the hotel to confirm arrangements for your dates.',
        ['rooms', 'arrival', 'ask_room']
    ),
    '/jaisalmer-fort-guide.php' => $makePlanningPath(
        'Plan your Fort visit',
        'Combine the Fort guide with arrival information and a route that fits the time you have available.',
        ['arrival', 'fort_walk', 'ask_fort']
    ),
    '/desert-safari-guide.php' => $makePlanningPath(
        'Prepare for a desert experience',
        'Compare the safari guide with overnight options and the information worth confirming before you book.',
        ['desert', 'packing', 'ask_desert']
    ),
    '/jaisalmer-2-day-itinerary.php' => $makePlanningPath(
        'Organise your two days in Jaisalmer',
        'Use this itinerary with room options, a guided visit and a desert experience that suits your timing.',
        ['rooms', 'tours', 'ask_two_day']
    ),
    '/jaisalmer-3-day-itinerary.php' => $makePlanningPath(
        'Make room for a longer Jaisalmer stay',
        'Use the longer itinerary to balance Fort time with local experiences, dining and a desert outing.',
        ['two_day', 'dining', 'ask_three_day']
    ),
    '/jaisalmer-honeymoon-guide.php' => $makePlanningPath(
        'Shape a couples trip around your dates',
        'Pair a heritage stay with rooftop dining and a desert option, confirming availability before you commit.',
        ['rooms', 'dining', 'ask_honeymoon']
    ),
    '/best-time-to-visit-jaisalmer.php' => $makePlanningPath(
        'Prepare for your travel dates',
        'Use seasonal guidance with a practical packing list, desert information and room availability.',
        ['reach', 'packing', 'ask_visit']
    ),
    '/how-to-reach-jaisalmer.php' => $makePlanningPath(
        'Plan the journey and final arrival',
        'Review transport information and the final walk into Jaisalmer Fort before your stay.',
        ['arrival', 'booking', 'ask_room']
    ),
    '/jaisalmer-desert-safari-packing-list.php' => $makePlanningPath(
        'Get ready for the desert',
        'Pair the packing checklist with safari options and overnight plans that require date-specific confirmation.',
        ['safari_guide', 'desert', 'ask_packing']
    ),
    '/jaisalmer-havelis-guide.php' => $makePlanningPath(
        'Explore Jaisalmer’s heritage',
        'Combine architectural reading with Fort information and practical details for your stay.',
        ['fort_guide', 'beyond', 'ask_havelis']
    ),
    '/jaisalmer-food-guide.php' => $makePlanningPath(
        'Plan what to taste and where to stay',
        'Explore the food guide with rooftop dining information and local markets for your visit.',
        ['dining', 'restaurant', 'ask_dining']
    ),
    '/jaisalmer-beyond-the-fort-guide.php' => $makePlanningPath(
        'Plan beyond the Fort walls',
        'Use the guide with tour options and a custom enquiry for the interests you want to include.',
        ['fort_guide', 'car_tours', 'ask_custom']
    ),
    '/jaisalmer-markets-and-crafts-guide.php' => $makePlanningPath(
        'Add local crafts to your Jaisalmer visit',
        'Combine market time with the food guide and property photographs as you plan your trip.',
        ['food', 'beyond', 'ask_custom']
    ),
    '/camping.php' => $makePlanningPath(
        'Compare your overnight desert options',
        'Choose between an open-sky experience and tented accommodation, then review the details for your date.',
        ['sunset', 'stars', 'ask_visit']
    ),
    '/car-rental.php' => $makePlanningPath(
        'Coordinate transport with your route',
        'Use the arrival guide and tour pages to share the right pickup, destination and travel-date details.',
        ['arrival', 'car_tours', 'ask_visit']
    ),
    '/city-tour.php' => $makePlanningPath(
        'Plan the rest of your route',
        'Use the Fort walk as part of a wider stay, with arrival and itinerary details confirmed before you travel.',
        ['tours', 'custom', 'ask_visit']
    ),
    '/car-tours.php' => $makePlanningPath(
        'Choose a route for your time in Jaisalmer',
        'Compare guided options and car arrangements, then ask the hotel to confirm what is possible for your dates.',
        ['tours', 'fort_walk', 'ask_visit']
    ),
    '/custom-itinerary.php' => $makePlanningPath(
        'Start with a published option or combine ideas',
        'Review guided, desert and transport options first, then ask about a route shaped around your interests.',
        ['car_tours', 'desert', 'ask_visit']
    ),
    '/desert-safari.php' => $makePlanningPath(
        'Combine desert time with a Fort stay',
        'Compare the desert experience with room options and a guided visit, then confirm timing and inclusions for your date.',
        ['camping', 'tours', 'ask_visit']
    ),
    '/guided-jaisalmer-tour.php' => $makePlanningPath(
        'Explore more of Jaisalmer',
        'Pair a guided walk with a Fort visit or desert outing. Routes, guide availability and current prices need confirmation.',
        ['fort_walk', 'car_tours', 'ask_visit']
    ),
    '/sunset-camel-safari.php' => $makePlanningPath(
        'Plan around your sunset safari',
        'Review other desert options and your Fort stay, checking timings, inclusions and current availability for your date.',
        ['desert', 'camping', 'ask_visit']
    ),
    '/sleep-under-the-stars.php' => $makePlanningPath(
        'Complete your open-sky overnight plan',
        'Compare the other overnight options and plan your room before or after the desert experience.',
        ['camping', 'sunset', 'ask_visit']
    ),
    '/swiss-tent-safari.php' => $makePlanningPath(
        'Compare your desert stay choices',
        'Review the tented experience alongside the open-sky option and the wider desert overview.',
        ['camping', 'stars', 'ask_visit']
    ),
    '/cultural-safari.php' => $makePlanningPath(
        'Make a plan for your desert evening',
        'Compare the cultural option with a sunset safari, overnight stays and your Fort accommodation.',
        ['desert', 'sunset', 'ask_visit']
    ),
    '/half-day-jaisalmer-tour.php' => $makePlanningPath(
        'Fit a guided visit into your day',
        'Compare the half-day option with the Fort walk and custom itinerary enquiry, confirming timings in advance.',
        ['tours', 'fort_walk', 'ask_visit']
    ),
    '/full-day-jaisalmer-tour.php' => $makePlanningPath(
        'Plan a full day around Jaisalmer',
        'Pair the full-day tour with a custom itinerary or desert experience and confirm the final route for your date.',
        ['tours', 'custom', 'ask_visit']
    ),
];
unset($choices, $makePlanningPath);
return $planningPathRegistry;
