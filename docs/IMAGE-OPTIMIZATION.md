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

## Generated batch — 2026-10-13

The first successful generation run produced the following exact repository-byte summary:

- 15 candidates reviewed by the generator.
- 11 accepted derivatives; the accepted source set totalled **5,901,701 bytes** and derivative set totalled **2,992,592 bytes**.
- Net reduction for that accepted subset: **2,909,109 bytes (49.293%)**.
- Four sources were not served with a derivative:
  - `img/big6.jpg` and `img/5.webp`: the candidate did not pass the 10% minimum savings check.
  - `img/s5.jpg` and `img/desert4.jpg`: PSNR was below the 35 dB screening floor.
- The detailed JSON/CSV and side-by-side contact sheet are published in [the GitHub Actions image-optimization review artifact](https://github.com/tchouhanjsm/garh-jaisal-website/actions/runs/38341517801/artifacts/11787176332).

These results measure exact tracked file sizes in the repository, not production transfer, CDN cache behaviour, Core Web Vitals, or Lighthouse results. The contact sheet is a screening review at thumbnail scale. Full-size original/derivative visual approval remains required before merge. The 49.293% result applies only to the 11 accepted source files, not to all site images or total page weight.

## Workflow cleanup

The bootstrap workflow used to generate and commit the derivatives is temporary and should be removed once the generated files are committed to the feature branch. Normal PHP and browser CI remain the final merge gates.
