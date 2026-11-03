<?php
/**
 * Safe reader for fixed Home, Rooms, Dining and Experience image slots.
 * Only a signed, approved record may replace the reviewed PHP fallback.
 */
require_once __DIR__ . '/page-media-approval.php';

if (!function_exists('gj_load_site_media_slots')) {
    function gj_load_site_media_slots($rootPath = null, $approvalKey = null, $approvers = null)
    {
        static $cache = [];
        $rootPath = is_string($rootPath) && $rootPath !== '' ? $rootPath : dirname(__DIR__);
        $rootReal = realpath($rootPath);
        if (!is_string($rootReal) || !is_dir($rootReal)) return [];

        if ($approvalKey === null || $approvers === null) {
            $configPath = $rootReal . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'site-config.php';
            $runtimeConfig = [];
            if (is_file($configPath) && !is_link($configPath)) {
                $loadedConfig = require $configPath;
                if (is_array($loadedConfig)) $runtimeConfig = $loadedConfig;
            }
            if ($approvalKey === null) $approvalKey = $runtimeConfig['media_approval_hmac_key'] ?? '';
            if ($approvers === null) $approvers = $runtimeConfig['media_approvers'] ?? [];
        }
        if (!is_string($approvalKey)) $approvalKey = '';
        if (!is_array($approvers)) $approvers = [];
        $approvers = array_values(array_unique(array_filter($approvers, static function ($value) {
            return is_string($value) && trim($value) !== '';
        })));
        sort($approvers, SORT_STRING);
        $cacheKey = $rootReal . ':' . hash('sha256', $approvalKey . "\0" . implode("\0", $approvers));
        if (array_key_exists($cacheKey, $cache)) return $cache[$cacheKey];

        $contentRoot = $rootReal . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'page-media';
        $imageRoot = $rootReal . DIRECTORY_SEPARATOR . 'img';
        if (is_link($contentRoot) || !is_dir($contentRoot) || is_link($imageRoot) || !is_dir($imageRoot)) {
            $cache[$cacheKey] = [];
            return [];
        }
        $imageRootReal = realpath($imageRoot);
        if (!is_string($imageRootReal)) {
            $cache[$cacheKey] = [];
            return [];
        }

        $definitions = [
            'home' => [
                'home-hero-mobile' => 'Home hero — mobile',
                'home-hero-desktop' => 'Home hero — desktop',
                'home-heritage-intro' => 'Home — heritage introduction',
                'home-rooftop-feature' => 'Home — rooftop dining feature',
                'home-desert-safari-feature' => 'Home — camel safari feature',
                'home-swiss-tent-feature' => 'Home — Swiss tent feature',
                'home-sleep-under-stars-feature' => 'Home — sleep under the stars',
                'home-city-discovery-feature' => 'Home — city discovery feature',
                'home-gallery-city' => 'Home gallery — city view',
                'home-gallery-courtyard' => 'Home gallery — courtyard',
                'home-gallery-reception' => 'Home gallery — reception',
                'home-gallery-rooftop' => 'Home gallery — rooftop view',
                'home-gallery-entrance' => 'Home gallery — carved entrance',
            ],
            'rooms' => [
                'rooms-hero' => 'Rooms page — hero',
                'room-sweet-purple-room-primary' => 'Sweet Purple Room — primary photo',
                'room-rising-sun-room-primary' => 'Rising Sun Room — primary photo',
                'room-scarlet-room-primary' => 'Scarlet Room — primary photo',
                'room-opium-room-primary' => 'Opium Room — primary photo',
                'room-red-maharani-room-primary' => 'Red Maharani Room — primary photo',
                'room-green-room-primary' => 'Green Room — primary photo',
                'room-bluesky-room-primary' => 'BlueSky Room — primary photo',
            ],
            'dining' => [
                'dining-hero' => 'Dining page — rooftop restaurant hero',
            ],
            'experiences' => [
                'experience-desert-safari-hero' => 'Desert Safari — hero',
                'experience-guided-jaisalmer-tour-hero' => 'Guided Jaisalmer Tour — hero',
                'experience-sunset-camel-safari-hero' => 'Sunset Camel Safari — hero',
                'experience-sleep-under-the-stars-hero' => 'Sleep Under the Stars — hero',
                'experience-swiss-tent-safari-hero' => 'Swiss Tent Safari — hero',
                'experience-cultural-safari-hero' => 'Cultural Safari — hero',
                'experience-explore-the-desert-hero' => 'Explore the Desert — hero',
                'experience-half-day-jaisalmer-tour-hero' => 'Half-Day Jaisalmer Tour — hero',
                'experience-full-day-jaisalmer-tour-hero' => 'Full-Day Jaisalmer Tour — hero',
                'experience-longewala-tour-hero' => 'Longewala Tour — hero',
                'experience-rajasthan-tours-hero' => 'Rajasthan Tours — draft hero',
            ],
        ];

        $slots = [];
        foreach ($definitions as $area => $areaSlots) {
            $file = $contentRoot . DIRECTORY_SEPARATOR . $area . '.json';
            if (is_link($file) || !is_file($file)) continue;
            $raw = @file_get_contents($file);
            $records = is_string($raw) ? json_decode($raw, true) : null;
            if (!is_array($records) || json_last_error() !== JSON_ERROR_NONE) continue;

            foreach ($areaSlots as $id => $label) {
                $record = $records[$id] ?? null;
                if (!is_array($record)) continue;
                $source = $record['src'] ?? null;
                $alt = $record['alt'] ?? null;
                $caption = $record['caption'] ?? '';
                if (
                    !is_string($source)
                    || preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$~iD', $source) !== 1
                    || strpos($source, '..') !== false
                    || strpos($source, chr(92)) !== false
                    || !is_string($alt)
                    || strlen(trim($alt)) < 15
                    || strlen($alt) > 300
                    || !is_string($caption)
                    || strlen($caption) > 180
                ) continue;

                $cursor = $rootReal;
                $safePath = true;
                $parts = explode('/', $source);
                foreach ($parts as $index => $part) {
                    $cursor .= DIRECTORY_SEPARATOR . $part;
                    if (is_link($cursor) || !file_exists($cursor)) {
                        $safePath = false;
                        break;
                    }
                    if ($index < count($parts) - 1 && !is_dir($cursor)) {
                        $safePath = false;
                        break;
                    }
                }
                if (!$safePath || !is_file($cursor)) continue;
                $realPath = realpath($cursor);
                $imageInfo = is_string($realPath) ? @getimagesize($realPath) : false;
                $bytes = is_string($realPath) ? @filesize($realPath) : false;
                if (
                    !is_string($realPath)
                    || strpos($realPath, $imageRootReal . DIRECTORY_SEPARATOR) !== 0
                    || !is_array($imageInfo)
                    || !isset($imageInfo[0], $imageInfo[1])
                    || (int) $imageInfo[0] < 1
                    || (int) $imageInfo[1] < 1
                    || !is_int($bytes)
                    || $bytes < 1
                ) continue;

                $approval = is_array($record['approval'] ?? null) ? $record['approval'] : [];
                $approvalStatus = is_string($approval['status'] ?? null) ? $approval['status'] : 'pending';
                $approved = gj_page_media_approval_is_valid(
                    $id, $source, trim($alt), trim($caption), $approval,
                    $realPath, $approvalKey, $approvers
                );
                $slots[$id] = [
                    'id' => $id,
                    'area' => $area,
                    'label' => $label,
                    'approved' => $approved,
                    'approval_status' => $approvalStatus,
                    'image' => ['src' => $source, 'alt' => trim($alt), 'caption' => trim($caption)],
                ];
            }
        }
        $cache[$cacheKey] = $slots;
        return $slots;
    }
}

if (!function_exists('gj_site_media_slot')) {
    /** Resolve only a signed, approved CMS slot; otherwise preserve its PHP fallback. */
    function gj_site_media_slot($slots, $slotId, $fallback)
    {
        if (!is_array($slots) || !is_string($slotId)
            || !isset($slots[$slotId]['approved']) || $slots[$slotId]['approved'] !== true
            || !isset($slots[$slotId]['image']) || !is_array($slots[$slotId]['image'])) {
            return is_array($fallback) ? $fallback : [];
        }
        return array_replace(is_array($fallback) ? $fallback : [], $slots[$slotId]['image']);
    }
}

return gj_load_site_media_slots(dirname(__DIR__));
