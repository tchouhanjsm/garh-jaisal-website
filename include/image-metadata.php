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
        $imageRoot = realpath(dirname(__DIR__) . '/img');
        $candidate = dirname(__DIR__) . '/' . $relative;
        if (
            !is_string($root)
            || !is_string($imageRoot)
            || !is_file($candidate)
            || is_link($candidate)
        ) {
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
