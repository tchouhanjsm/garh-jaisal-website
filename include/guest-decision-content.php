<?php
/**
 * Guest decision content: separate information shown on the site from details
 * that still require confirmation for a guest's dates.
 *
 * "shown" means the information is already represented in public content or
 * a maintained registry. It does not remove the owner's duty to keep facts
 * current. Never publish a new value until the owner approves it.
 */
return [
    'home' => [
        'cta_id' => 'enquiry-home-guest-decision',
        'cta_location' => 'home-guest-decision',
        'title' => 'Know what is clear before you decide.',
        'intro' => 'Review the practical information already published, then ask us to confirm anything that depends on your travel dates, room choice or current operations.',
        'shown' => [
            ['label' => 'A stay inside Jaisalmer Fort', 'detail' => 'The haveli is inside the Fort. Vehicles cannot enter the fort lanes, so the final approach is on foot.'],
            ['label' => 'Room-by-room information', 'detail' => 'Compare each room\'s published photographs, floor and listed features. Facilities vary by room.'],
            ['label' => 'A practical arrival guide', 'detail' => 'Review the route from Fort Parking, walking distance and surface information before travelling.'],
        ],
        'confirm' => [
            ['label' => 'Stay terms and room suitability', 'detail' => 'Confirm check-in and check-out times, cancellation terms, room occupancy, bed configuration and dimensions before payment.'],
            ['label' => 'Dining on your date', 'detail' => 'Ask for current menu items, prices, service hours, dietary options and table availability.'],
            ['label' => 'Experiences and transport', 'detail' => 'Confirm the feasible route, timings, guide or vehicle availability, inclusions and current quote for your date.'],
        ],
        'cta_href' => 'contact.php?topic=visit-planning',
        'cta_label' => 'Ask about your dates',
    ],
    'stay' => [
        'cta_id' => 'enquiry-room-details',
        'cta_location' => 'rooms-guest-decision',
        'title' => 'Choose with the details that matter.',
        'intro' => 'The room directory shows the photographs, floor and features currently recorded for each room. Ask the team to confirm anything that is not listed before you book.',
        'shown' => [
            ['label' => 'Room identity and floor', 'detail' => 'Each room has its own name, published photographs and a ground-floor or first-floor location.'],
            ['label' => 'Listed room features', 'detail' => 'Compare the features shown on each room card. A feature not listed for a room should be treated as unconfirmed, not automatically absent.'],
            ['label' => 'Live rates and availability', 'detail' => 'Use the booking engine to check the current rate and availability for your exact dates.'],
        ],
        'confirm' => [
            ['label' => 'Occupancy, beds and room dimensions', 'detail' => 'Where a room page does not state maximum occupancy, exact bed configuration or room size, ask the hotel to confirm the selected room.'],
            ['label' => 'Bathroom and access requirements', 'detail' => 'Confirm any bathroom, stair, mobility or other access requirement for the specific room before choosing.'],
            ['label' => 'Check-in and cancellation terms', 'detail' => 'Ask for the current check-in/check-out times, cancellation policy, early-arrival access and luggage-storage arrangements before payment.'],
        ],
        'cta_href' => 'contact.php?topic=stay',
        'cta_label' => 'Ask about room details',
    ],
    'dining' => [
        'cta_id' => 'enquiry-dining-details',
        'cta_location' => 'dining-guest-decision',
        'title' => 'Plan the meal, then confirm the details.',
        'intro' => 'The site introduces the rooftop setting and the table-enquiry route. Current service information is confirmed by the hotel for the date you have in mind.',
        'shown' => [
            ['label' => 'Rooftop dining inside the Fort', 'detail' => 'Garh Jaisal\'s Kitchen is the rooftop restaurant at Garh Jaisal Haveli inside Jaisalmer Fort.'],
            ['label' => 'A table enquiry is a request', 'detail' => 'Sending a request does not reserve a table. Wait for the hotel to confirm the service hours and availability.'],
        ],
        'confirm' => [
            ['label' => 'Current menu and prices', 'detail' => 'Ask which dishes and prices are current for your visit. Unapproved menu previews are not presented as the live menu.'],
            ['label' => 'Service hours and last seating', 'detail' => 'Confirm opening hours, closed days, last seating and any same-day request cut-off.'],
            ['label' => 'Dietary needs and table availability', 'detail' => 'Discuss Jain, allergy or other dietary requirements, group size and the preferred date and time before relying on an arrangement.'],
        ],
        'cta_href' => 'contact.php?topic=dining',
        'cta_label' => 'Ask about dining',
    ],
    'arrival' => [
        'cta_id' => 'enquiry-arrival-details',
        'cta_location' => 'directions-guest-decision',
        'title' => 'Arrive knowing what to expect.',
        'intro' => 'The final walk into the living Fort is part of the stay. Review the route, then confirm any assistance or time-sensitive arrangements directly.',
        'shown' => [
            ['label' => 'Vehicle access', 'detail' => 'Vehicles cannot enter Jaisalmer Fort. Fort Parking is the drop-off point.'],
            ['label' => 'The final walk', 'detail' => 'The route from Fort Parking is approximately 300 metres and usually takes 5–6 minutes through narrow lanes with uneven cobbles and short stair sections.'],
        ],
        'confirm' => [
            ['label' => 'Reception and late arrival', 'detail' => 'Confirm current reception hours and whether an arrival outside those hours can be accommodated for your expected time.'],
            ['label' => 'Luggage and meet-and-assist', 'detail' => 'Ask whether staff assistance or luggage help can be arranged for your date and arrival time. Do not rely on it until the hotel confirms.'],
            ['label' => 'Transfers and local access', 'detail' => 'Confirm the pickup point, vehicle, current fare, timing and local rickshaw access rules before travelling.'],
        ],
        'cta_href' => 'contact.php?topic=arrival',
        'cta_label' => 'Confirm arrival arrangements',
    ],
    'contact' => [
        'cta_id' => 'enquiry-contact-form',
        'cta_location' => 'contact-guest-decision',
        'title' => 'Know what happens after you enquire.',
        'intro' => 'Use the form for a question or planning request. The hotel must confirm rates, availability and arrangements before anything is treated as booked.',
        'shown' => [
            ['label' => 'Choose a useful enquiry topic', 'detail' => 'Room, arrival, dining and visit-planning links can prefill the message with the details the team needs to check.'],
            ['label' => 'A message is not a booking', 'detail' => 'A submitted enquiry is a request for information. It does not reserve a room, table, tour or transfer.'],
        ],
        'confirm' => [
            ['label' => 'Dates and availability', 'detail' => 'Include your travel date, preferred time and group size where relevant so the hotel can check the current position.'],
            ['label' => 'Terms and operating details', 'detail' => 'Ask for the applicable policy, current price, operating hours and practical arrangements before making a payment or relying on a service.'],
        ],
        'cta_href' => '#contact-form',
        'cta_label' => 'Go to the enquiry form',
    ],
];
