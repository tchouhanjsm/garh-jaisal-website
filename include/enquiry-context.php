<?php
/** Resolve session-bound enquiry intent against public, allowlisted registries. */
function gj_general_enquiry_context(): array
{
    return ['type' => 'general', 'type_label' => 'General', 'slug' => '', 'label' => 'General enquiry'];
}

function gj_resolve_enquiry_context($type, $slug): array
{
    if (!is_string($type) || !is_string($slug)) {
        return gj_general_enquiry_context();
    }
    $type = strtolower(trim($type));
    $slug = trim($slug);

    if ($type === 'rooms') {
        $roomSlugs = explode(',', $slug);
        if (count($roomSlugs) < 2 || count($roomSlugs) > 3 || count(array_unique($roomSlugs)) !== count($roomSlugs)) {
            return gj_general_enquiry_context();
        }

        $roomContexts = [];
        foreach ($roomSlugs as $roomSlug) {
            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $roomSlug)) {
                return gj_general_enquiry_context();
            }
            $roomContext = gj_resolve_enquiry_context('room', $roomSlug);
            if (($roomContext['type'] ?? '') !== 'room') {
                return gj_general_enquiry_context();
            }
            $roomContexts[] = $roomContext;
        }

        return [
            'type' => 'rooms',
            'type_label' => 'Room shortlist',
            'slug' => implode(',', array_column($roomContexts, 'slug')),
            'label' => implode(', ', array_column($roomContexts, 'label')),
            'items' => $roomContexts,
        ];
    }

    if ($type === 'trip') {
        $tripIds = explode(',', $slug);
        if (count($tripIds) < 1 || count($tripIds) > 12 || count(array_unique($tripIds)) !== count($tripIds)) {
            return gj_general_enquiry_context();
        }

        $catalogue = require __DIR__ . '/catalogue-data.php';
        $experiences = require __DIR__ . '/experience-data.php';
        $rooms = require __DIR__ . '/rooms-data.php';
        $tripLookup = [];

        foreach ((array) $catalogue as $definition) {
            if (!is_array($definition)) {
                continue;
            }
            $id = (string) ($definition['id'] ?? '');
            if (!preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/D', $id)) {
                continue;
            }
            $source = (string) ($definition['source'] ?? '');
            if ($source === 'experience') {
                $experienceSlug = (string) ($definition['slug'] ?? $id);
                $candidate = $experiences[$experienceSlug] ?? null;
                if (!is_array($candidate) || ($candidate['status'] ?? '') !== 'live'
                    || !is_file(dirname(__DIR__) . '/' . $experienceSlug . '.php')) {
                    continue;
                }
                $label = trim((string) ($candidate['h1'] ?? $candidate['title'] ?? ''));
                $url = $experienceSlug . '.php';
            } else {
                $label = trim((string) ($definition['title'] ?? ''));
                $url = (string) ($definition['url'] ?? '');
            }
            if ($label !== '' && preg_match('/^[a-z0-9-]+\.php(?:#[a-z0-9-]+)?$/iD', $url)) {
                $tripLookup[$id] = ['slug' => $id, 'label' => $label, 'url' => $url];
            }
        }

        // Any live public detail route rendered by the shared experience template can be added from its CTA.
        foreach ((array) $experiences as $experienceSlug => $experience) {
            if (!is_array($experience) || ($experience['status'] ?? '') !== 'live'
                || !is_string($experience['h1'] ?? null)
                || trim($experience['h1']) === ''
                || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', (string) $experienceSlug)
                || !is_file(dirname(__DIR__) . '/' . $experienceSlug . '.php')) {
                continue;
            }
            if (!isset($tripLookup[$experienceSlug])) {
                $tripLookup[$experienceSlug] = [
                    'slug' => (string) $experienceSlug,
                    'label' => trim($experience['h1']),
                    'url' => $experienceSlug . '.php',
                ];
            }
        }

        // A published room card uses room-{room-slug} so the exact chosen room is retained.
        foreach ((array) $rooms as $room) {
            if (!is_array($room) || !is_string($room['name'] ?? null)) {
                continue;
            }
            $roomSlug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($room['name'])), '-');
            if ($roomSlug !== '') {
                $tripLookup['room-' . $roomSlug] = [
                    'slug' => 'room-' . $roomSlug,
                    'label' => trim($room['name']),
                    'url' => 'room.php#room-' . $roomSlug,
                ];
            }
        }

        // These are explicit, published trip-tray options rendered outside the main catalogue registry.
        $tripLookup['fort-walk'] = ['slug' => 'fort-walk', 'label' => 'A Walk inside Jaisalmer Fort', 'url' => 'city-tour.php'];
        $tripLookup['transport-desert-pickup'] = ['slug' => 'transport-desert-pickup', 'label' => 'Desert pickup or drop-off', 'url' => 'car-rental.php'];
        $tripLookup['transport-intercity'] = ['slug' => 'transport-intercity', 'label' => 'Intercity car journey', 'url' => 'car-rental.php'];
        $tripLookup['transport-fort-arrival'] = ['slug' => 'transport-fort-arrival', 'label' => 'Arrival at the living Fort', 'url' => 'direction.php'];

        $resolvedItems = [];
        foreach ($tripIds as $tripId) {
            if (!preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/D', $tripId) || !isset($tripLookup[$tripId])) {
                return gj_general_enquiry_context();
            }
            $resolvedItems[] = $tripLookup[$tripId];
        }

        return [
            'type' => 'trip',
            'type_label' => 'Trip shortlist',
            'slug' => implode(',', array_column($resolvedItems, 'slug')),
            'label' => implode(', ', array_column($resolvedItems, 'label')),
            'items' => $resolvedItems,
        ];
    }

    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) {
        return gj_general_enquiry_context();
    }

    if ($type === 'experience') {
        $registry = require __DIR__ . '/experience-data.php';
        $candidate = is_array($registry) ? ($registry[$slug] ?? null) : null;
        if (
            is_array($candidate)
            && ($candidate['slug'] ?? '') === $slug
            && ($candidate['status'] ?? '') === 'live'
            && ($candidate['duration_type'] ?? 'hub') !== 'hub'
            && is_string($candidate['h1'] ?? null)
            && trim($candidate['h1']) !== ''
        ) {
            return ['type' => 'experience', 'type_label' => 'Experience', 'slug' => $slug, 'label' => trim($candidate['h1'])];
        }
    } elseif ($type === 'room') {
        $rooms = require __DIR__ . '/rooms-data.php';
        if (is_array($rooms)) {
            foreach ($rooms as $candidate) {
                if (!is_array($candidate) || !is_string($candidate['name'] ?? null)) {
                    continue;
                }
                $candidateSlug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($candidate['name'])), '-');
                if ($candidateSlug === $slug && trim($candidate['name']) !== '') {
                    return ['type' => 'room', 'type_label' => 'Room', 'slug' => $slug, 'label' => trim($candidate['name'])];
                }
            }
        }
    } elseif ($type === 'topic') {
        $topics = require __DIR__ . '/contact-topics.php';
        $candidate = is_array($topics) ? ($topics[$slug] ?? null) : null;
        if (is_array($candidate) && is_string($candidate['label'] ?? null) && trim($candidate['label']) !== '') {
            return ['type' => 'topic', 'type_label' => 'Topic', 'slug' => $slug, 'label' => trim($candidate['label'])];
        }
    }
    return gj_general_enquiry_context();
}

function gj_contact_context_return_url(array $context, string $error = ''): string
{
    $query = [];
    $type = (string) ($context['type'] ?? 'general');
    $slug = (string) ($context['slug'] ?? '');
    if ($type === 'trip') {
        $resolved = gj_resolve_enquiry_context('trip', $slug);
        if (($resolved['type'] ?? '') === 'trip') {
            $query['trip'] = explode(',', $resolved['slug']);
        }
    } elseif ($type === 'rooms') {
        $resolved = gj_resolve_enquiry_context('rooms', $slug);
        if (($resolved['type'] ?? '') === 'rooms') {
            $query['rooms'] = explode(',', $resolved['slug']);
        }
    } elseif (
        in_array($type, ['room', 'experience', 'topic'], true)
        && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)
    ) {
        $query[$type === 'topic' ? 'topic' : $type] = $slug;
    }
    if (in_array($error, ['captcha', 'invalid', 'send', 'config', 'storage'], true)) {
        $query['error'] = $error;
    }
    return 'contact.php' . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
}
