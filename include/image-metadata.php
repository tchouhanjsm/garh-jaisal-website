<?php
/**
 * Safe metadata lookup for locally controlled editorial images.
 *
 * Only files rooted under img/ are inspected. Symlinks, path traversal and
 * remote URLs are never converted into local filesystem paths.
 */
if (!function_exists('gj_local_image_metadata')) {
    function gj_local_image_metadata($source)
    {
        static $cache = [];

        if (!is_string($source)) {
            return null;
        }

        $source = trim($source);
        if (
            preg_match('~^/?img/[A-Za-z0-9._/-]+$~D', $source) !== 1
            || strpos($source, '..') !== false
            || strpos($source, chr(92)) !== false
        ) {
            return null;
        }

        $relative = ltrim($source, '/');
        if (array_key_exists($relative, $cache)) {
            return $cache[$relative];
        }

        $root = realpath(dirname(__DIR__));
        if (!is_string($root)) {
            $cache[$relative] = null;
            return null;
        }

        $imageDirectory = $root . DIRECTORY_SEPARATOR . 'img';
        $imageRoot = realpath($imageDirectory);
        if (
            !is_string($imageRoot)
            || $imageRoot !== $imageDirectory
            || is_link($imageDirectory)
        ) {
            $cache[$relative] = null;
            return null;
        }

        // Inspect every path component, not just the final image. A symlinked
        // parent folder can otherwise escape the approved asset tree.
        $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $cursor = $root;
        $parts = explode('/', $relative);
        foreach ($parts as $index => $part) {
            $cursor .= DIRECTORY_SEPARATOR . $part;
            // Missing files are a normal fallback case for optional responsive
            // variants. Check before lstat so warning-to-exception handlers stay quiet.
            if (is_link($cursor) || !file_exists($cursor)) {
                $cache[$relative] = null;
                return null;
            }
            $stat = @lstat($cursor);
            if (!is_array($stat) || is_link($cursor)) {
                $cache[$relative] = null;
                return null;
            }
            if ($index < count($parts) - 1 && !is_dir($cursor)) {
                $cache[$relative] = null;
                return null;
            }
        }

        if (!is_file($candidate)) {
            $cache[$relative] = null;
            return null;
        }

        $realPath = realpath($candidate);
        if (
            !is_string($realPath)
            || strpos($realPath, $imageRoot . DIRECTORY_SEPARATOR) !== 0
        ) {
            $cache[$relative] = null;
            return null;
        }

        $size = @getimagesize($realPath);
        $bytes = @filesize($realPath);
        if (
            !is_array($size)
            || !isset($size[0], $size[1])
            || (int) $size[0] < 1
            || (int) $size[1] < 1
        ) {
            $cache[$relative] = null;
            return null;
        }

        $metadata = [
            'path' => $relative,
            'url_path' => '/' . $relative,
            'width' => (int) $size[0],
            'height' => (int) $size[1],
            'mime' => isset($size['mime']) && is_string($size['mime']) ? $size['mime'] : '',
            'bytes' => is_int($bytes) ? $bytes : null,
        ];
        $cache[$relative] = $metadata;
        return $metadata;
    }
}

/**
 * Return intrinsic dimensions and one validated responsive delivery contract.
 *
 * Both width-descriptor candidates and same-size optimized fallbacks are read
 * through gj_local_image_metadata, so dimensions, MIME, byte size and path
 * safety are checked by one implementation.
 */
/**
 * Return verified generated responsive candidates plus a native-width source
 * fallback when the best derivative would cover less than 90% of source pixels.
 * The structured result is shared by rendered img srcset and its preload descriptor.
 */
if (!function_exists('gj_image_responsive_candidates')) {
    function gj_image_responsive_candidates($source)
    {
        if (!is_string($source)) {
            return [];
        }

        $sourceMetadata = gj_local_image_metadata($source);
        if (!is_array($sourceMetadata)) {
            return [];
        }

        $sourceKey = $sourceMetadata['path'];
        $sourceWidth = $sourceMetadata['width'];
        $sourceHeight = $sourceMetadata['height'];
        $responsiveRelative = substr($sourceKey, 4);
        if (strncmp($responsiveRelative, 'optimized/', 10) === 0) {
            $responsiveRelative = substr($responsiveRelative, 10);
        }

        $responsiveInfo = pathinfo($responsiveRelative);
        $responsiveDirectory = ($responsiveInfo['dirname'] ?? '.') === '.'
            ? ''
            : $responsiveInfo['dirname'] . '/';
        $candidates = [];

        foreach ([480, 800, 1200] as $candidateWidth) {
            if ($candidateWidth >= $sourceWidth) {
                continue;
            }

            $candidateSource = 'img/responsive/' . $responsiveDirectory . $responsiveInfo['filename'] . '-' . $candidateWidth . '.webp';
            $candidateMetadata = gj_local_image_metadata($candidateSource);
            if (
                !is_array($candidateMetadata)
                || $candidateMetadata['mime'] !== 'image/webp'
                || $candidateMetadata['width'] !== $candidateWidth
                || $candidateMetadata['height'] < 1
                || $candidateMetadata['width'] >= $sourceWidth
                || !is_int($sourceMetadata['bytes'])
                || !is_int($candidateMetadata['bytes'])
                || $sourceMetadata['bytes'] <= 0
                || $candidateMetadata['bytes'] <= 0
                || $candidateMetadata['bytes'] >= $sourceMetadata['bytes']
                || abs(($candidateMetadata['width'] / $candidateMetadata['height']) - ($sourceWidth / $sourceHeight)) > 0.02
            ) {
                continue;
            }

            $candidates[] = [
                'path' => $candidateSource,
                'width' => $candidateWidth,
                'bytes' => $candidateMetadata['bytes'],
            ];
        }

        if ($candidates) {
            $largestCandidate = $candidates[count($candidates) - 1];
            $sourcePixelCoverage = $largestCandidate['width'] / $sourceWidth;

            // Never strand a wide slot on a low-resolution derivative when the
            // original source has meaningful extra pixels. The source is already
            // path/MIME/dimension/byte validated above; expose it as the final
            // width-descriptor fallback rather than fabricating an upscale.
            if ($sourcePixelCoverage < 0.9) {
                $candidates[] = [
                    'path' => $sourceMetadata['path'],
                    'width' => $sourceWidth,
                    'bytes' => $sourceMetadata['bytes'],
                ];
            }
        }

        return $candidates;
    }
}

/**
 * Build a high-priority preload from the exact candidate list used by rendered
 * image markup. Return null when no verified width-descriptor candidate exists,
 * avoiding a preload that could duplicate an optimized fallback request.
 */
if (!function_exists('gj_image_responsive_preload_descriptor')) {
    function gj_image_responsive_preload_descriptor($source, $responsiveSizes = '100vw')
    {
        if (!is_string($responsiveSizes) || trim($responsiveSizes) === '') {
            return null;
        }

        $metadata = gj_local_image_metadata($source);
        if (!is_array($metadata)) {
            return null;
        }

        $candidates = gj_image_responsive_candidates($metadata['path']);
        if (!$candidates) {
            return null;
        }

        $srcset = [];
        foreach ($candidates as $candidate) {
            $srcset[] = $candidate['path'] . ' ' . $candidate['width'] . 'w';
        }

        return [
            'href' => $metadata['path'],
            'fetchpriority' => 'high',
            'imagesrcset' => implode(', ', $srcset),
            'imagesizes' => trim($responsiveSizes),
        ];
    }
}

if (!function_exists('gj_image_dimension_attributes')) {
    function gj_image_dimension_attributes($source, $responsiveSizes = null)
    {
        static $deliveryCache = [];

        if (!is_string($source)) {
            return '';
        }

        $source = trim($source);
        if (
            preg_match('~^/?img/[A-Za-z0-9._/-]+$~D', $source) !== 1
            || strpos($source, '..') !== false
            || strpos($source, chr(92)) !== false
        ) {
            return '';
        }

        $sourceMetadata = gj_local_image_metadata($source);
        if (!is_array($sourceMetadata)) {
            return '';
        }

        $sourceKey = $sourceMetadata['path'];
        $width = $sourceMetadata['width'];
        $height = $sourceMetadata['height'];
        $attributes = 'width="' . $width . '" height="' . $height . '"';

        // Use the shared verified candidate list for both rendering and preloading.
        if (is_string($responsiveSizes) && trim($responsiveSizes) !== '') {
            $responsiveCandidates = gj_image_responsive_candidates($sourceKey);
            $responsiveSrcset = [];
            foreach ($responsiveCandidates as $candidate) {
                $responsiveSrcset[] = '/' . htmlspecialchars($candidate['path'], ENT_QUOTES, 'UTF-8') . ' ' . $candidate['width'] . 'w';
            }

            if ($responsiveSrcset) {
                return $attributes
                    . ' srcset="' . htmlspecialchars(implode(', ', $responsiveSrcset), ENT_QUOTES, 'UTF-8') . '"'
                    . ' sizes="' . htmlspecialchars(trim($responsiveSizes), ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        // If the input is already the optimized derivative, it cannot benefit
        // from pointing its own 1x srcset back to another optimized path.
        if (!array_key_exists($sourceKey, $deliveryCache)) {
            if (strncmp(substr($sourceKey, 4), 'optimized/', 10) === 0) {
                $deliveryCache[$sourceKey] = null;
            } else {
                $relativeSource = substr($sourceKey, 4);
                $sourceInfo = pathinfo($relativeSource);
                $sourceDirectory = ($sourceInfo['dirname'] ?? '.') === '.'
                    ? ''
                    : $sourceInfo['dirname'] . '/';
                $optimizedSource = 'img/optimized/' . $sourceDirectory . $sourceInfo['filename'] . '.webp';
                $optimizedMetadata = gj_local_image_metadata($optimizedSource);
                $deliveryCache[$sourceKey] = (
                    is_array($optimizedMetadata)
                    && $optimizedMetadata['mime'] === 'image/webp'
                    && $optimizedMetadata['width'] === $width
                    && $optimizedMetadata['height'] === $height
                    && is_int($sourceMetadata['bytes'])
                    && is_int($optimizedMetadata['bytes'])
                    && $sourceMetadata['bytes'] > 0
                    && $optimizedMetadata['bytes'] > 0
                    && $optimizedMetadata['bytes'] < $sourceMetadata['bytes']
                ) ? $optimizedSource : null;
            }
        }

        if ($deliveryCache[$sourceKey] !== null) {
            $attributes .= ' srcset="/' . htmlspecialchars($deliveryCache[$sourceKey], ENT_QUOTES, 'UTF-8') . ' 1x"';
        }

        return $attributes;
    }
}
