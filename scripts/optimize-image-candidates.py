#!/usr/bin/env python3
"""Create reviewed, same-dimension WebP derivatives for approved image candidates.

Original assets are never modified. Only manifest-listed source files are processed.
A derivative is retained only if it clears the configured byte-saving and PSNR floors.
The JSON/CSV report and side-by-side review sheets are build artifacts, not site assets.
"""
from __future__ import annotations

import csv
import json
import math
from pathlib import Path
from typing import Any

from PIL import Image, ImageChops, ImageDraw, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[1]
MANIFEST_PATH = ROOT / "scripts" / "image-optimization-manifest.json"
REPORT_DIR = ROOT / "artifacts" / "image-optimization"


def output_for(source_relative: str) -> Path:
    source = Path(source_relative)
    return ROOT / "img" / "optimized" / source.relative_to("img").with_suffix(".webp")


def psnr_db(original_path: Path, derivative_path: Path) -> float:
    with Image.open(original_path) as original, Image.open(derivative_path) as derivative:
        left = original.convert("RGB")
        right = derivative.convert("RGB")
        if left.size != right.size:
            raise ValueError(f"Dimension mismatch: {original_path} {left.size} vs {derivative_path} {right.size}")
        diff = ImageChops.difference(left, right)
        rms = ImageStatRms(diff)
        mse = sum(value * value for value in rms) / len(rms)
        return 99.0 if mse == 0 else 20 * math.log10(255 / math.sqrt(mse))


def ImageStatRms(image: Image.Image) -> list[float]:
    # Avoid a NumPy dependency; Pillow's channel histogram gives deterministic RMSE.
    histogram = image.histogram()
    values: list[float] = []
    bands = image.getbands()
    for channel_index, _band in enumerate(bands):
        counts = histogram[channel_index * 256:(channel_index + 1) * 256]
        squared_error = sum(index * index * count for index, count in enumerate(counts))
        total = sum(counts)
        values.append(math.sqrt(squared_error / total) if total else 0.0)
    return values


def build_contact_sheet(rows: list[dict[str, Any]]) -> None:
    accepted = [row for row in rows if row["status"] == "optimized"]
    if not accepted:
        return

    columns = 3
    panel_w, panel_h = 520, 292
    padding = 12
    sheet = Image.new("RGB", (columns * panel_w, math.ceil(len(accepted) / columns) * panel_h), "white")
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.load_default()

    for index, row in enumerate(accepted):
        col = index % columns
        line = index // columns
        x, y = col * panel_w, line * panel_h
        source_path = ROOT / row["source"]
        derivative_path = ROOT / row["derivative"]
        draw.text((x + padding, y + 7), row["source"], fill="black", font=font)
        draw.text((x + padding, y + 25), f'Original: {row["source_bytes"]:,} B', fill="black", font=font)
        draw.text((x + panel_w // 2 + 2, y + 25), f'WebP q84: {row["derivative_bytes"]:,} B', fill="black", font=font)

        with Image.open(source_path) as image:
            left = image.convert("RGB")
            left.thumbnail((panel_w // 2 - padding * 2, 208), Image.Resampling.LANCZOS)
            sheet.paste(left, (x + padding + (panel_w // 2 - padding * 2 - left.width) // 2, y + 48 + (208 - left.height) // 2))
        with Image.open(derivative_path) as image:
            right = image.convert("RGB")
            right.thumbnail((panel_w // 2 - padding * 2, 208), Image.Resampling.LANCZOS)
            sheet.paste(right, (x + panel_w // 2 + padding + (panel_w // 2 - padding * 2 - right.width) // 2, y + 48 + (208 - right.height) // 2))

        draw.text((x + padding, y + 262), f'Saved {row["saved_bytes"]:,} B ({row["saved_percent"]:.1f}%)', fill="black", font=font)
        draw.text((x + panel_w // 2 + 2, y + 262), f'PSNR {row["psnr_db"]:.2f} dB', fill="black", font=font)

    sheet.save(REPORT_DIR / "quality-review-sheet.jpg", format="JPEG", quality=92, optimize=True)


def main() -> None:
    manifest = json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))
    quality = int(manifest.get("quality", 84))
    method = int(manifest.get("method", 6))
    min_savings = float(manifest.get("minimum_savings_percent", 10))
    min_psnr = float(manifest.get("minimum_psnr_db", 35))
    REPORT_DIR.mkdir(parents=True, exist_ok=True)

    rows: list[dict[str, Any]] = []
    candidate_outputs: list[Path] = []

    for source_relative in manifest["sources"]:
        source_path = ROOT / source_relative
        derivative_path = output_for(source_relative)
        derivative_relative = derivative_path.relative_to(ROOT).as_posix()
        if not source_path.is_file():
            raise FileNotFoundError(f"Manifest source is missing: {source_relative}")

        # Remove only the manifest-owned derivative before a deterministic rebuild.
        if derivative_path.exists():
            derivative_path.unlink()
        derivative_path.parent.mkdir(parents=True, exist_ok=True)

        source_bytes = source_path.stat().st_size
        with Image.open(source_path) as opened:
            opened.load()
            original_size = opened.size
            source_info = dict(opened.info)
            has_alpha = "A" in opened.getbands() or "transparency" in source_info
            encoded = opened.convert("RGBA" if has_alpha else "RGB")
            save_options: dict[str, Any] = {
                "format": "WEBP",
                "quality": quality,
                "method": method,
                "exact": True,
            }
            for key in ("exif", "icc_profile", "xmp"):
                value = source_info.get(key)
                if value:
                    save_options[key] = value
            encoded.save(derivative_path, **save_options)

        with Image.open(derivative_path) as derivative:
            derivative.load()
            if derivative.format != "WEBP":
                raise ValueError(f"Encoder did not produce WebP: {derivative_relative}")
            if derivative.size != original_size:
                raise ValueError(f"Unexpected resize: {source_relative} {original_size} -> {derivative.size}")

        derivative_bytes = derivative_path.stat().st_size
        saved_bytes = source_bytes - derivative_bytes
        saved_percent = (saved_bytes / source_bytes * 100) if source_bytes else 0.0
        measured_psnr = psnr_db(source_path, derivative_path)

        status = "optimized"
        note = "Original retained; output dimensions and local color profile/EXIF metadata preserved where present."
        if saved_percent < min_savings:
            status = "skipped-insufficient-savings"
            note = f"Saved less than the configured {min_savings:.1f}% floor."
            derivative_path.unlink(missing_ok=True)
        elif measured_psnr < min_psnr:
            status = "skipped-below-psnr-floor"
            note = f"PSNR was below the configured {min_psnr:.1f} dB floor."
            derivative_path.unlink(missing_ok=True)
        else:
            candidate_outputs.append(derivative_path)

        rows.append({
            "source": source_relative,
            "derivative": derivative_relative,
            "status": status,
            "source_bytes": source_bytes,
            "derivative_bytes": derivative_bytes if status == "optimized" else None,
            "saved_bytes": saved_bytes if status == "optimized" else None,
            "saved_percent": round(saved_percent, 3) if status == "optimized" else None,
            "psnr_db": round(measured_psnr, 3),
            "width": original_size[0],
            "height": original_size[1],
            "encoder": {"format": "WEBP", "quality": quality, "method": method},
            "note": note,
        })

    optimized_count = len(candidate_outputs)
    if optimized_count == 0:
        raise RuntimeError("No candidate passed both the minimum byte-saving and PSNR quality floors.")

    build_contact_sheet(rows)
    report = {
        "generated_by": "scripts/optimize-image-candidates.py",
        "source_context": "Repository assets; exact filesystem byte sizes, not browser Resource Timing estimates.",
        "thresholds": {
            "quality": quality,
            "method": method,
            "minimum_savings_percent": min_savings,
            "minimum_psnr_db": min_psnr,
            "dimensions_changed": False,
        },
        "summary": {
            "candidate_count": len(rows),
            "optimized_count": optimized_count,
            "skipped_count": len(rows) - optimized_count,
            "source_bytes_for_optimized": sum(row["source_bytes"] for row in rows if row["status"] == "optimized"),
            "derivative_bytes": sum(row["derivative_bytes"] for row in rows if row["status"] == "optimized"),
            "saved_bytes": sum(row["saved_bytes"] for row in rows if row["status"] == "optimized"),
            "saved_percent_weighted": round(
                sum(row["saved_bytes"] for row in rows if row["status"] == "optimized")
                / max(1, sum(row["source_bytes"] for row in rows if row["status"] == "optimized")) * 100, 3
            ),
            "human_visual_review_required": True,
        },
        "candidates": rows,
    }
    (REPORT_DIR / "image-optimization-report.json").write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")
    with (REPORT_DIR / "image-optimization-report.csv").open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=[
            "source", "derivative", "status", "source_bytes", "derivative_bytes",
            "saved_bytes", "saved_percent", "psnr_db", "width", "height", "note",
        ])
        writer.writeheader()
        for row in rows:
            writer.writerow({key: row.get(key) for key in writer.fieldnames})

    print(json.dumps(report["summary"], indent=2))
    for row in rows:
        print(f'{row["status"]}: {row["source"]} -> {row["derivative"]} '
              f'({row["saved_percent"]}% saved; PSNR {row["psnr_db"]} dB)')


if __name__ == "__main__":
    main()
