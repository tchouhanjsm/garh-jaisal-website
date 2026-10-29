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
