# Image optimization batch

This batch generates WebP derivatives for the reviewed candidate list in `scripts/image-optimization-manifest.json`. The original photographs remain unchanged. Generated files mirror source paths under `img/optimized/`, e.g. `img/v2/facade.jpg` maps to `img/optimized/v2/facade.webp`.

## Reproduce the output

Use Python 3.12 with Pillow 11.3.0 installed:

```bash
python -m pip install Pillow==11.3.0
python scripts/optimize-image-candidates.py
```

The tool writes exact source/derivative byte counts, saved bytes and percentage, dimensions, encoder settings, and PSNR to `artifacts/image-optimization/image-optimization-report.json` and `.csv`. It also writes a side-by-side visual-review sheet. A derivative is retained only when it preserves pixel dimensions, saves at least 10% of source bytes, and clears the 35 dB PSNR floor. These thresholds are a deterministic screening gate, not a substitute for visual review.

## Runtime safeguards

- Original source files are never overwritten or deleted.
- The shared image-dimension helper advertises a WebP `srcset` only if a corresponding local derivative exists, is valid WebP, has exactly the original pixel dimensions, and is smaller than the source.
- If a derivative is absent or fails validation, the existing `src` remains the fallback.
- The browser regression checks actual rendered `currentSrc` selection on sitemap routes and checks exact repository file sizes; it does not treat Resource Timing overhead as file size.
- No image is renamed, resized, cropped, or mapped to a different room/experience. Visual review is required before merge.
