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


## Sprint 3B — large-source inventory and responsive-size review

The current tracked tree contains 16 original source images larger than 400,000 bytes (excluding files already under `img/optimized/`). Largest source: `img/tent1.jpg` at 6,138,540 bytes (~5.85 MiB). The complete candidate set is encoded in `scripts/build-responsive-image-review.py` so every run is repeatable.

Run the candidate generator locally with Python 3.12 and Pillow 11.3.0:

```bash
python -m pip install Pillow==11.3.0
python scripts/build-responsive-image-review.py
```

It writes source-preserving WebP review candidates for 480px, 800px and 1200px maximum widths under `artifacts/responsive-image-review/variants/`, plus an exact-byte JSON report and a side-by-side contact sheet. The same command runs in CI and uploads the `responsive-image-review` artifact. It never changes or deletes original photos and never adds a public runtime reference.

The already accepted same-dimension derivatives remain the only image outputs enabled by the site's helper. Newly resized variants remain review-only until the owner checks them against the full-size photos; this avoids changing room/tour imagery or sharpness by filename assumption. The 6.14 MB tent photo is retained even though it is not part of the currently rendered 26-route payload audit; its other repository uses should be reviewed before changing its delivery path.

## Sprint 35 — art-directed homepage hero and tracked-byte budgets (PR #84 in review)

The homepage uses a portrait facade below 768px and the existing rooftop/fort panorama from 768px upward. Both alternatives use already-checked-in, same-dimension WebP derivatives and retain their separate intrinsic dimensions; the original source photos are unchanged. The panoramic derivative is 388,404 tracked bytes compared with 591,152 bytes for the portrait facade (202,748 bytes, about 34.3%, lower for the desktop/tablet hero selection).

`tests/e2e/home-hero-art-direction.spec.js` locks source selection at 320, 390, 768, 1280 and 1440px, validates dimensions/loading and enforces 600 KiB / 400 KiB tracked-file ceilings. This is an asset-level regression guard, not a total homepage byte budget or a production/Core Web Vitals measurement. The separate resized 480/800/1200px candidate set remains review-only pending owner visual approval.
