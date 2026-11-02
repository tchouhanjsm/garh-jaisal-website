<?php
/**
 * Read-only media inventory for the owner dashboard.
 *
 * This report deliberately detects literal image paths in public source files.
 * It does not prove that an asset is unused: application code can construct
 * paths dynamically, and responsive derivatives are selected by shared code.
 */

if (!function_exists('gj_media_library_audit')) {
    /**
     * Build a bounded, read-only image inventory.
     *
     * @param string $rootPath Site root directory.
     * @return array<string, mixed>
     */
    function gj_media_library_audit($rootPath)
    {
        $rootReal = realpath($rootPath);
        if (!is_string($rootReal) || !is_dir($rootReal)) {
            throw new RuntimeException('Site root cannot be resolved.');
        }

        $imageRoot = $rootReal . DIRECTORY_SEPARATOR . 'img';
        if (is_link($imageRoot) || !is_dir($imageRoot)) {
            throw new RuntimeException('Image library is missing or unsafe.');
        }
        $imageRootReal = realpath($imageRoot);
        if (!is_string($imageRootReal)) {
            throw new RuntimeException('Image library cannot be resolved.');
        }

        $warnings = [];
        $assets = [];
        $assetIndex = [];
        $maxAssets = 3000;
        $assetCount = 0;
        $imageExtensions = ['avif', 'gif', 'jpg', 'jpeg', 'png', 'webp', 'svg'];
        // Review thresholds are triage signals, not automatic quality failures.
        $largeFileThresholdBytes = 512 * 1024;
        $largeDimensionThresholdPx = 2560;

        $imageIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($imageRootReal, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($imageIterator as $fileInfo) {
            if ($fileInfo->isLink() || !$fileInfo->isFile()) {
                continue;
            }

            $extension = strtolower($fileInfo->getExtension());
            if (!in_array($extension, $imageExtensions, true)) {
                continue;
            }
            if ($assetCount >= $maxAssets) {
                $warnings[] = 'Image inventory reached its safety limit; some files may be omitted.';
                break;
            }

            $absolutePath = $fileInfo->getPathname();
            $realPath = realpath($absolutePath);
            if (
                !is_string($realPath)
                || strpos($realPath, $imageRootReal . DIRECTORY_SEPARATOR) !== 0
            ) {
                $warnings[] = 'An image outside the expected img/ directory was skipped.';
                continue;
            }

            $relativePath = 'img/' . str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr($realPath, strlen($imageRootReal) + 1)
            );
            if (
                strpos($relativePath, '..') !== false
                || !preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|gif|jpe?g|png|webp|svg)$~iD', $relativePath)
            ) {
                $warnings[] = 'An image with an unsafe path was skipped.';
                continue;
            }

            $sizeBytes = @filesize($realPath);
            if (!is_int($sizeBytes) || $sizeBytes < 0) {
                $warnings[] = 'An image with unreadable file metadata was skipped: ' . basename($relativePath);
                continue;
            }

            $dimensions = null;
            if ($extension !== 'svg') {
                $imageInfo = @getimagesize($realPath);
                if (is_array($imageInfo) && isset($imageInfo[0], $imageInfo[1])) {
                    $dimensions = [
                        'width' => (int) $imageInfo[0],
                        'height' => (int) $imageInfo[1],
                    ];
                }
            }

            $digest = @hash_file('sha256', $realPath);
            $asset = [
                'path' => $relativePath,
                'bytes' => $sizeBytes,
                'width' => is_array($dimensions) ? $dimensions['width'] : null,
                'height' => is_array($dimensions) ? $dimensions['height'] : null,
                'extension' => $extension,
                'sha256' => is_string($digest) ? $digest : null,
                'usage_files' => [],
                'gallery_entries' => [],
                'derived_from' => null,
            ];

            $assets[] = $asset;
            $assetIndex[$relativePath] = count($assets) - 1;
            $assetCount++;
        }

        // Scan only source-like files. Excluded directories and private local
        // configuration are never read by this report.
        $allowedSourceExtensions = ['php', 'phtml', 'html', 'htm', 'js', 'css', 'json', 'xml', 'yml', 'yaml', 'txt'];
        $excludedDirectories = ['.git', '.github', '.agents', '.claude', 'docs', 'tests', 'tools', 'marketing', 'node_modules', 'vendor'];
        $excludedFiles = ['include/site-config.local.php'];
        $maxSourceFiles = 2500;
        $maxSourceBytes = 2 * 1024 * 1024;
        $maxTotalSourceBytes = 24 * 1024 * 1024;
        $sourceFilesScanned = 0;
        $sourceFilesSkipped = 0;
        $sourceBytesScanned = 0;
        $sourceTruncated = false;
        $referencePattern = "~((?:(?:\\.\\./)+)?/?img/[A-Za-z0-9._/-]+\\.(?:avif|gif|jpe?g|png|webp|svg))(?=$|[\\s\\\"'?#),;<>.])~i";

        $sourceDirectory = new RecursiveDirectoryIterator($rootReal, FilesystemIterator::SKIP_DOTS);
        $sourceFilter = new RecursiveCallbackFilterIterator(
            $sourceDirectory,
            static function ($current) use ($rootReal, $excludedDirectories) {
                if ($current->isLink()) {
                    return false;
                }
                if ($current->isDir()) {
                    $relative = ltrim(str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        substr($current->getPathname(), strlen($rootReal))
                    ), '/');
                    $firstPart = explode('/', $relative)[0];
                    return !in_array($firstPart, $excludedDirectories, true);
                }
                return true;
            }
        );
        $sourceIterator = new RecursiveIteratorIterator($sourceFilter, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($sourceIterator as $fileInfo) {
            if ($fileInfo->isLink() || !$fileInfo->isFile()) {
                continue;
            }

            $relativePath = ltrim(str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr($fileInfo->getPathname(), strlen($rootReal))
            ), '/');

            if (
                in_array($relativePath, $excludedFiles, true)
                || preg_match('~(^|/)(?:\\.env(?:\\.[^/]*)?|[^/]*\\.local\\.php|private|secrets?)(?:/|$)~i', $relativePath)
            ) {
                continue;
            }
            $extension = strtolower($fileInfo->getExtension());
            if (!in_array($extension, $allowedSourceExtensions, true)) {
                continue;
            }
            if ($sourceFilesScanned >= $maxSourceFiles || $sourceBytesScanned >= $maxTotalSourceBytes) {
                $sourceTruncated = true;
                break;
            }

            $fileSize = @filesize($fileInfo->getPathname());
            if (!is_int($fileSize) || $fileSize < 0 || $fileSize > $maxSourceBytes) {
                $sourceFilesSkipped++;
                continue;
            }
            if ($sourceBytesScanned + $fileSize > $maxTotalSourceBytes) {
                $sourceTruncated = true;
                break;
            }

            $source = @file_get_contents($fileInfo->getPathname());
            if (!is_string($source)) {
                $sourceFilesSkipped++;
                continue;
            }

            $sourceFilesScanned++;
            $sourceBytesScanned += $fileSize;
            $matches = [];
            if (!preg_match_all($referencePattern, $source, $matches)) {
                continue;
            }

            foreach ($matches[1] as $reference) {
                $normalized = str_replace('\\', '/', $reference);
                $normalized = preg_replace('~^(?:\.\./)+~', '', $normalized);
                $normalized = ltrim($normalized, '/');
                if (!is_string($normalized) || !isset($assetIndex[$normalized])) {
                    continue;
                }
                $index = $assetIndex[$normalized];
                if (!in_array($relativePath, $assets[$index]['usage_files'], true)) {
                    $assets[$index]['usage_files'][] = $relativePath;
                }
            }
        }

        if ($sourceTruncated) {
            $warnings[] = 'Source scan reached its total file or byte safety limit; usage counts may be incomplete.';
        }
        if ($sourceFilesSkipped > 0) {
            $warnings[] = 'One or more source files exceeded the per-file scan limit or could not be read; usage counts may be incomplete.';
        }
        $sourceScanComplete = !$sourceTruncated && $sourceFilesSkipped === 0;

        // Attach Gallery entry metadata. A missing Gallery source is surfaced
        // as an integrity warning, never as a deletion recommendation.
        $missingGalleryReferences = [];
        $galleryDirectory = $rootReal . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'gallery';
        if (is_link($galleryDirectory)) {
            $warnings[] = 'The Gallery content directory is a symlink and was not inspected.';
        } elseif (is_dir($galleryDirectory)) {
            $galleryFiles = glob($galleryDirectory . DIRECTORY_SEPARATOR . '*.json') ?: [];
            sort($galleryFiles, SORT_STRING);
            foreach ($galleryFiles as $galleryFile) {
                if (is_link($galleryFile) || !is_file($galleryFile)) {
                    continue;
                }
                $rawRecord = @file_get_contents($galleryFile);
                $record = is_string($rawRecord) ? json_decode($rawRecord, true) : null;
                if (!is_array($record) || !is_array($record['image'] ?? null)) {
                    $warnings[] = 'An unreadable Gallery record was skipped: ' . basename($galleryFile);
                    continue;
                }
                $source = $record['image']['src'] ?? null;
                if (!is_string($source)) {
                    continue;
                }
                $source = ltrim(str_replace('\\', '/', trim($source)), '/');
                if (
                    strpos($source, 'img/') !== 0
                    || strpos($source, '..') !== false
                    || !preg_match('~^img/[A-Za-z0-9._/-]+\.(?:avif|gif|jpe?g|png|webp|svg)$~iD', $source)
                ) {
                    $missingGalleryReferences[] = [
                        'id' => is_string($record['id'] ?? null) ? $record['id'] : basename($galleryFile, '.json'),
                        'source' => $source,
                        'reason' => 'unsafe',
                    ];
                    continue;
                }
                if (!isset($assetIndex[$source])) {
                    $missingGalleryReferences[] = [
                        'id' => is_string($record['id'] ?? null) ? $record['id'] : basename($galleryFile, '.json'),
                        'source' => $source,
                        'reason' => 'missing',
                    ];
                    continue;
                }

                $index = $assetIndex[$source];
                $galleryRecordPath = 'content/gallery/' . basename($galleryFile);
                if (!in_array($galleryRecordPath, $assets[$index]['usage_files'], true)) {
                    $assets[$index]['usage_files'][] = $galleryRecordPath;
                }
                $assets[$index]['gallery_entries'][] = [
                    'id' => is_string($record['id'] ?? null) ? $record['id'] : basename($galleryFile, '.json'),
                    'category' => is_string($record['category'] ?? null) ? $record['category'] : 'unknown',
                    'status' => is_string($record['status'] ?? null) ? $record['status'] : 'unknown',
                ];
            }
        }

        // Responsive derivatives are generated from originals by the existing
        // build pipeline. They are never presented as deletion candidates.
        foreach ($assets as $index => $asset) {
            if (strpos($asset['path'], 'img/responsive/') !== 0) {
                continue;
            }
            $relative = substr($asset['path'], strlen('img/responsive/'));
            if (preg_match('~^(.*)-[0-9]+\.webp$~i', $relative, $parts)) {
                foreach (['jpg', 'jpeg', 'png', 'webp', 'avif'] as $sourceExtension) {
                    $possibleSource = 'img/' . $parts[1] . '.' . $sourceExtension;
                    if (isset($assetIndex[$possibleSource])) {
                        $sourceIndex = $assetIndex[$possibleSource];
                        $assets[$index]['derived_from'] = $possibleSource;
                        // A derivative belongs to the same Gallery categories as
                        // its original. Do not copy direct usage paths: the page
                        // references the original and the image resolver selects
                        // the derivative at runtime.
                        $assets[$index]['gallery_entries'] = $assets[$sourceIndex]['gallery_entries'];
                        break;
                    }
                }
            }
        }

        $duplicateGroups = [];
        foreach ($assets as $asset) {
            if (is_string($asset['sha256'])) {
                $duplicateGroups[$asset['sha256']][] = $asset['path'];
            }
        }
        $duplicates = [];
        foreach ($duplicateGroups as $paths) {
            if (count($paths) > 1) {
                sort($paths, SORT_STRING);
                $duplicates[] = $paths;
            }
        }
        usort($duplicates, static function ($left, $right) {
            return strcmp($left[0], $right[0]);
        });

        $totalBytes = 0;
        $referencedAssets = 0;
        $unreferencedCandidates = 0;
        $responsiveDerivatives = 0;
        $duplicateAssetCount = 0;
        $largeFileAssets = 0;
        $oversizedDimensionAssets = 0;
        $optimizationReviewAssets = 0;
        $duplicatePathSet = [];
        foreach ($duplicates as $group) {
            foreach ($group as $path) {
                $duplicatePathSet[$path] = true;
            }
        }

        foreach ($assets as $index => $asset) {
            $usageCount = count($asset['usage_files']);
            $assets[$index]['usage_count'] = $usageCount;
            $assets[$index]['gallery_entry_count'] = count($asset['gallery_entries']);
            $assets[$index]['is_responsive_derivative'] = is_string($asset['derived_from']);
            $assets[$index]['is_duplicate'] = isset($duplicatePathSet[$asset['path']]);
            $optimizationFlags = [];
            if ($asset['bytes'] > $largeFileThresholdBytes) {
                $optimizationFlags[] = 'large-file';
                $largeFileAssets++;
            }
            if (
                is_int($asset['width'])
                && is_int($asset['height'])
                && max($asset['width'], $asset['height']) > $largeDimensionThresholdPx
            ) {
                $optimizationFlags[] = 'large-dimensions';
                $oversizedDimensionAssets++;
            }
            $assets[$index]['optimization_flags'] = $optimizationFlags;
            if ($optimizationFlags) {
                $optimizationReviewAssets++;
            }
            $assets[$index]['usage_state'] = $assets[$index]['is_responsive_derivative']
                ? 'responsive-derivative'
                : ($usageCount > 0 ? 'referenced' : 'no-literal-reference');

            $totalBytes += $asset['bytes'];
            if ($usageCount > 0) {
                $referencedAssets++;
            }
            if ($usageCount === 0 && !$assets[$index]['is_responsive_derivative']) {
                $unreferencedCandidates++;
            }
            if ($assets[$index]['is_responsive_derivative']) {
                $responsiveDerivatives++;
            }
            if ($assets[$index]['is_duplicate']) {
                $duplicateAssetCount++;
            }
            sort($assets[$index]['usage_files'], SORT_STRING);
        }

        usort($assets, static function ($left, $right) {
            return strcmp($left['path'], $right['path']);
        });

        return [
            'generated_at' => date(DATE_ATOM),
            'assets' => $assets,
            'duplicate_groups' => $duplicates,
            'missing_gallery_references' => $missingGalleryReferences,
            'warnings' => $warnings,
            'summary' => [
                'total_assets' => count($assets),
                'referenced_assets' => $referencedAssets,
                'no_literal_reference' => $unreferencedCandidates,
                'responsive_derivatives' => $responsiveDerivatives,
                'duplicate_assets' => $duplicateAssetCount,
                'duplicate_groups' => count($duplicates),
                'total_bytes' => $totalBytes,
                'large_file_assets' => $largeFileAssets,
                'oversized_dimension_assets' => $oversizedDimensionAssets,
                'optimization_review_assets' => $optimizationReviewAssets,
                'large_file_threshold_bytes' => $largeFileThresholdBytes,
                'large_dimension_threshold_px' => $largeDimensionThresholdPx,
                'source_files_scanned' => $sourceFilesScanned,
                'source_files_skipped' => $sourceFilesSkipped,
                'source_scan_complete' => $sourceScanComplete,
                'missing_gallery_references' => count($missingGalleryReferences),
            ],
        ];
    }
}
