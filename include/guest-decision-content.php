<?php
/**
 * Guest decision content: separate information shown on the site from details
 * that still require confirmation for a guest's dates.
 *
 * "shown" means the information is already represented in public content or
 * a maintained registry. It does not remove the owner's duty to keep facts
 * current. Never publish a new value until the owner approves it.
 */
$guestDecisionRooms = require __DIR__ . '/rooms-data.php';
$guestDecisionRooms = is_array($guestDecisionRooms) ? $guestDecisionRooms : [];
$guestDecisionRoomFloors = [];
foreach ($guestDecisionRooms as $guestDecisionRoom) {
    if (is_array($guestDecisionRoom) && is_string($guestDecisionRoom['kicker'] ?? null)) {
        $floor = trim($guestDecisionRoom['kicker']);
        if ($floor !== '') {
            $guestDecisionRoomFloors[] = $floor;
        }
    }
}
$guestDecisionRoomFloors = array_values(array_unique($guestDecisionRoomFloors));
$guestDecisionRoomFloorLabel = $guestDecisionRoomFloors ? implode(' and ', $guestDecisionRoomFloors) : 'the published room directory';
$guestDecisionRoomCount = count($guestDecisionRooms);

return [
    'home' => [
        'cta_id' => 'enquiry-home-guest-decision',
        'cta_location' => 'home-guest-decision',
        'title' => 'Know what to expect before you book.',
        'intro' => 'Plan your stay in the Fort.',
        'shown' => [
            ['label' => 'A stay inside Jaisalmer Fort', 'detail' => 'The haveli is inside the Fort. Vehicles cannot enter the fort lanes, so the final approach is on foot.'],
            ['label' => 'Room-by-room information', 'detail' => 'Individually styled rooms on two floors.'],
            ['label' => 'A practical arrival guide', 'detail' => 'Walking route and access from Fort Parking.'],
        ],
        'confirm' => [
            ['label' => 'Stay terms and room suitability', 'detail' => 'Ask about room suitability and check-in times.'],
            ['label' => 'Dining on your date', 'detail' => 'Ask about table availability and dietary needs.'],
            ['label' => 'Experiences and transport', 'detail' => 'Request a tailored tour or transfer quote.'],
        ],
        'cta_href' => 'contact.php?topic=visit-planning',
        'cta_label' => 'Ask about your dates',
    ],
    'stay' => [
        'cta_id' => 'enquiry-room-details',
        'cta_location' => 'rooms-guest-decision',
        'title' => 'Choose with the details that matter.',
        'intro' => 'Find the right room for your stay.',
        'shown' => [
            ['label' => 'Our rooms', 'detail' => sprintf('%d named rooms across %s.', $guestDecisionRoomCount, $guestDecisionRoomFloorLabel)],
            ['label' => 'Room-specific features', 'detail' => 'Room layouts and facilities vary.'],
            ['label' => 'Live rates and availability', 'detail' => 'Check availability for your dates.'],
        ],
        'confirm' => [
            ['label' => 'Room dimensions', 'detail' => 'Ask about dimensions for your stay.'],
            ['label' => 'Bathroom and access requirements', 'detail' => 'Discuss stair access, bathroom or mobility needs.'],
            ['label' => 'Check-in and luggage', 'detail' => 'Ask about early arrival or luggage assistance.'],
        ],
        'cta_href' => 'contact.php?topic=stay',
        'cta_label' => 'Ask about room details',
    ],
    'dining' => [
        'cta_id' => 'enquiry-dining-details',
        'cta_location' => 'dining-guest-decision',
        'title' => 'Plan the meal, then confirm the details.',
        'intro' => 'Enjoy a meal overlooking Jaisalmer.',
        'shown' => [
            ['label' => 'Rooftop dining inside the Fort', 'detail' => 'Garh Jaisal\'s Kitchen is the rooftop restaurant at Garh Jaisal Haveli inside Jaisalmer Fort.'],
            ['label' => 'A table enquiry is a request', 'detail' => 'Table enquiries are subject to confirmation.'],
        ],
        'confirm' => [
            ['label' => 'Dish availability', 'detail' => 'Ask about a particular dish.'],
            ['label' => 'Dietary needs and table availability', 'detail' => 'Tell us about dietary needs and group size.'],
        ],
        'cta_href' => 'contact.php?topic=dining',
        'cta_label' => 'Ask about dining',
    ],
    'arrival' => [
        'cta_id' => 'enquiry-arrival-details',
        'cta_location' => 'directions-guest-decision',
        'title' => 'Arrive knowing what to expect.',
        'intro' => 'Plan your arrival inside Jaisalmer Fort.',
        'shown' => [
            ['label' => 'Route and walking information', 'detail' => 'Walking route from Fort Parking.'],
            ['label' => 'Driver handoff details', 'detail' => 'Share the drop-off address with your driver.'],
        ],
        'confirm' => [
            ['label' => 'Reception and late arrival', 'detail' => 'Contact us about late arrivals.'],
            ['label' => 'Luggage and meet-and-assist', 'detail' => 'Ask in advance about luggage assistance.'],
            ['label' => 'Transfers and local access', 'detail' => 'Enquire about pickup, fare and local access.'],
        ],
        'cta_href' => 'contact.php?topic=arrival',
        'cta_label' => 'Confirm arrival arrangements',
    ],
    'contact' => [
        'cta_id' => 'enquiry-contact-form',
        'cta_location' => 'contact-guest-decision',
        'title' => 'Know what happens after you enquire.',
        'intro' => 'Tell us what you have in mind.',
        'shown' => [
            ['label' => 'Rooms, dining and experiences', 'detail' => 'Ask about a stay, a meal or a tailor-made trip.'],
            ['label' => 'A message is not a booking', 'detail' => 'Bookings are confirmed separately.'],
        ],
        'confirm' => [
            ['label' => 'Dates and availability', 'detail' => 'Include your dates and group size.'],
            ['label' => 'Terms and operating details', 'detail' => 'Ask about rates and any special requirements.'],
        ],
        'cta_href' => '#contact-form',
        'cta_label' => 'Go to the enquiry form',
    ],
];
