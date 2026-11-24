<?php

require_once __DIR__ . '/lead-storage.php';

$showReservationForm = true; // Keep enquiry capture visible independently of email availability.

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$waLink = $whatsappUrl . '?text=' . rawurlencode(
    'Hello, I would like to enquire about a table at ' . $restaurant['name']
    . '. Preferred date: [add date]. Preferred time: [add time]. Number of guests: [add number].'
    . ' Please confirm current service hours, table availability, menu options and prices,'
    . ' and available dietary options for my preferred date.'
);

$errors = [];
$sent = false;
$reservationReference = '';
$reservationEmailNotified = false;
$old = [
    'name' => '',
    'email' => '',
    'whatsapp' => '',
    'date' => '',
    'time' => '',
    'guests' => '2',
    'note' => '',
    'consent' => '',
];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

if (empty($_SESSION['gjk_csrf'])) {
    $_SESSION['gjk_csrf'] = bin2hex(random_bytes(16));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($old as $key => $_) {
        $old[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if (!empty($_POST['website'])) {
        http_response_code(400);
        exit;
    }

    if (!hash_equals($_SESSION['gjk_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors['form'] = 'Your session expired. Please reload the page and try again.';
    }

    $phone = preg_replace('/[^0-9+]/', '', $old['whatsapp']);
    $today = date('Y-m-d');

    $nameLength = function_exists('mb_strlen') ? mb_strlen($old['name'], 'UTF-8') : strlen($old['name']);
    if ($old['name'] === '' || $nameLength > 80) {
        $errors['name'] = 'Enter your name.';
    }
    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors['whatsapp'] = 'Enter a valid WhatsApp number.';
    }
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($old['date'] === '' || $old['date'] < $today) {
        $errors['date'] = 'Choose today or a future date.';
    }
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):(?:00|30)$/D', $old['time'])) {
        $errors['time'] = 'Choose a preferred time on the hour or half-hour.';
    }
    if ((int) $old['guests'] < 1 || (int) $old['guests'] > 20) {
        $errors['guests'] = 'Guests must be between 1 and 20.';
    }
    if (strlen($old['note']) > 500) {
        $errors['note'] = 'Keep the special note to 500 characters or fewer.';
    }
    if ($old['consent'] !== '1') {
        $errors['consent'] = 'Please agree that the hotel may use these details to respond to your request.';
    }

    // Enforce only schedule values explicitly supplied by the owner in private config.
    if ($old['date'] !== '' && $old['date'] >= $today) {
        $requestedWeekday = (int) date('w', strtotime($old['date']));
        $requestedWeekdayName = strtolower(date('l', strtotime($old['date'])));
        $closedDays = $siteConfig['restaurant_closed_days'] ?? [];
        if (!is_array($closedDays)) {
            $closedDays = [];
        }
        $closedDaysNormalized = array_map(static function ($day) {
            if (is_string($day)) {
                $day = strtolower(trim($day));
                return ctype_digit($day) ? (int) $day : $day;
            }
            return $day;
        }, $closedDays);
        if (in_array($requestedWeekday, $closedDaysNormalized, true)
            || in_array($requestedWeekdayName, $closedDaysNormalized, true)) {
            $errors['date'] = 'The restaurant is marked closed on that day. Choose another date or contact the hotel.';
        }
        $sameDayCutoff = max(0, (int) ($siteConfig['restaurant_same_day_cutoff_minutes'] ?? 0));
        if ($old['date'] === $today && $sameDayCutoff > 0
            && preg_match('/^(?:[01][0-9]|2[0-3]):(?:00|30)$/D', $old['time'])
            && strtotime($old['date'] . ' ' . $old['time']) < time() + ($sameDayCutoff * 60)) {
            $errors['time'] = 'That time is inside the same-day request cut-off. Please call or message the hotel.';
        }
    }

    if (!$errors) {
        $leadResult = gj_store_lead(
            'restaurant_reservation',
            [
                'name' => $old['name'],
                'phone' => $phone,
                'email' => $old['email'],
                'requested_date' => $old['date'],
                'requested_time' => $old['time'],
                'guests' => (int) $old['guests'],
                'note' => $old['note'],
                'consent' => true,
            ],
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            $phone,
            ['max_per_ip' => 8, 'max_per_phone' => 3]
        );

        if (empty($leadResult['ok'])) {
            $errors['form'] = ($leadResult['error'] ?? '') === 'rate_limited'
                ? 'Too many requests have been received from this connection or number today. Please call or message the hotel.'
                : 'We could not securely record the request. Please call or message the hotel so your enquiry is not lost.';
        } else {
            $reservationReference = $leadResult['reference'];
            $subject = 'Restaurant table enquiry [' . $reservationReference . ']';
            $body = "A new restaurant table enquiry was recorded. This is not a confirmed booking.\n\n"
                . "Reference: {$reservationReference}\n"
                . "Name: {$old['name']}\n"
                . "WhatsApp: {$phone}\n"
                . "Email: " . ($old['email'] ?: '-') . "\n"
                . "Preferred date: {$old['date']}\n"
                . "Preferred time: {$old['time']}\n"
                . "Guests: {$old['guests']}\n"
                . "Special note: " . ($old['note'] !== '' ? $old['note'] : '-') . "\n\n"
                . "Please confirm current service hours and availability directly with the guest.";

            $recipients = array_values(array_filter((array) ($siteConfig['inquiry_recipients'] ?? []), static function ($address) {
                return is_string($address) && !preg_match('/[\r\n]/', $address)
                    && filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
            }));
            if ($recipients === [] && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) !== false) {
                $recipients = [$hotelEmail];
            }
            $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            if (filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) !== false && !preg_match('/[\r\n]/', $hotelEmail)) {
                $headers .= 'From: ' . $hotelEmail . "\r\n";
            }
            if ($old['email'] !== '' && filter_var($old['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $headers .= 'Reply-To: ' . str_replace(["\r", "\n"], '', $old['email']) . "\r\n";
            }

            foreach ($recipients as $recipient) {
                if (@mail($recipient, $subject, $body, $headers)) {
                    $reservationEmailNotified = true;
                }
            }
            gj_record_lead_event($reservationReference, $reservationEmailNotified ? 'owner_notification_sent' : 'owner_notification_failed');

            if ($reservationEmailNotified && $old['email'] !== '' && filter_var($old['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $guestSubject = 'We received your table enquiry - ' . $reservationReference;
                $guestBody = "Thank you for contacting Garh Jaisal's Kitchen.\n\n"
                    . "Your enquiry reference is {$reservationReference}. This is not a confirmed booking. "
                    . "The hotel must confirm current service hours and availability before the table is reserved.\n\n"
                    . "For urgent confirmation, call {$hotelPhoneDisplay} or contact the hotel on WhatsApp.";
                $guestHeaders = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                if (filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) !== false) {
                    $guestHeaders .= 'From: ' . $hotelEmail . "\r\n";
                }
                $guestSent = @mail($old['email'], $guestSubject, $guestBody, $guestHeaders);
                gj_record_lead_event($reservationReference, $guestSent ? 'guest_acknowledgement_sent' : 'guest_acknowledgement_failed');
            }

            $sent = true;
            $_SESSION['gjk_csrf'] = bin2hex(random_bytes(16));
        }
    }
}
