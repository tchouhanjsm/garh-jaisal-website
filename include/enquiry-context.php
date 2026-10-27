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
    if ($type === 'rooms') {
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
