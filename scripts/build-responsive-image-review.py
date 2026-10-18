#!/usr/bin/env python3
"""Build non-public responsive WebP review candidates for large tracked photos.

This is a review artifact generator: it never modifies original media, writes
under artifacts/ (gitignored), or changes PHP/CSS references. Promote only after
the owner visually approves the full-size variants and their route mappings.
"""
from __future__ import annotations

import json
from pathlib import Path
from typing import Any

from PIL import Image, ImageDraw, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "artifacts" / "responsive-image-review"
VARIANT_DIR = OUT_DIR / "variants"
WIDTHS = (480, 800, 1200)
QUALITY = 82
METHOD = 6

# Source images selected for responsive review, including large route-level payload candidates.
# Originals remain untouched; the outputs are review-only and never public by this script.
SOURCES = (
    "img/tent1.jpg",
    "img/v2/facade.jpg",
    "img/v2/courtyard.jpg",
    "img/v2/green-room.jpg",
    "img/v2/fort-from-roof.jpg",
    "img/big6.jpg",
    "img/courtyard.jpg",
    "img/v2/rooftop-dusk.jpg",
    "img/Desert_Camp/camp_from_above.jpeg",
    "img/v2/purple-room.jpg",
    "img/v2/sitting-room.jpg",
    "img/5.webp",
    "img/j7.jpg",
    "img/Desert_Camp/CampingInJsm.jpeg",
    "img/j10.jpg",
    "img/trans.jpg",
    "img/j1.jpg",
    "img/j2.jpg",
    "img/j4.jpg",
    "img/j8.jpg",
    "img/j9.jpg",
    "img/desert11.jpg",
)

TILE_W = 320
TILE_H = 230
COLUMNS = 4


def webp_name(source: str, target_width: int) -> str:
    stem = Path(source).with_suffix("").as_posix().replace("/", "__")
    return f"{stem}-{target_width}w.webp"


def encode_variant(image: Image.Image, target_width: int, destination: Path) -> tuple[int, int]:
    if image.width <= target_width:
        raise ValueError("Upscaling is not allowed")
    target_height = max(1, round(image.height * target_width / image.width))
    resized = image.resize((target_width, target_height), Image.Resampling.LANCZOS)
    options: dict[str, Any] = {
        "format": "WEBP",
        "quality": QUALITY,
        "method": METHOD,
        "exact": True,
    }
    profile = image.info.get("icc_profile")
    if profile:
        options["icc_profile"] = profile
    resized.save(destination, **options)
    return target_width, target_height


def thumbnail(image: Image.Image, size: tuple[int, int]) -> Image.Image:
    result = image.convert("RGB")
    result.thumbnail(size, Image.Resampling.LANCZOS)
    return result


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    VARIANT_DIR.mkdir(parents=True, exist_ok=True)
    font = ImageFont.load_default()
    columns = COLUMNS
    rows: list[dict[str, Any]] = []
    sheet = Image.new("RGB", (columns * TILE_W, len(SOURCES) * TILE_H), "white")
    draw = ImageDraw.Draw(sheet)

    for row_index, source_relative in enumerate(SOURCES):
        source_path = ROOT / source_relative
        if not source_path.is_file():
            raise FileNotFoundError(f"Required review source is missing: {source_relative}")

        with Image.open(source_path) as opened:
            opened.load()
            image = ImageOps.exif_transpose(opened)
            source_format = opened.format or source_path.suffix.lstrip(".").upper()
            source_dimensions = list(image.size)
            source_bytes = source_path.stat().st_size
            source_thumb = thumbnail(image, (TILE_W - 20, 155))

            source_panel_x = 0
            draw.text((source_panel_x + 8, row_index * TILE_H + 6), source_relative, fill="black", font=font)
            draw.text(
                (source_panel_x + 8, row_index * TILE_H + 20),
                f"Original {source_dimensions[0]}x{source_dimensions[1]} · {source_bytes:,} B",
                fill="black",
                font=font,
            )
            sheet.paste(source_thumb, (source_panel_x + 8, row_index * TILE_H + 42))

            variants: list[dict[str, Any]] = []
            for col_index, target_width in enumerate(WIDTHS, start=1):
                panel_x = col_index * TILE_W
                if image.width <= target_width:
                    item = {
                        "target_width": target_width,
                        "status": "skipped-no-upscale",
                        "width": image.width,
                        "height": image.height,
                        "bytes": None,
                        "saved_bytes": None,
                        "saved_percent": None,
                        "relative_path": None,
                    }
                    draw.text((panel_x + 8, row_index * TILE_H + 6), f"{target_width}w: not generated", fill="black", font=font)
                    draw.text((panel_x + 8, row_index * TILE_H + 26), "Source is at or below target width.", fill="black", font=font)
                else:
                    filename = webp_name(source_relative, target_width)
                    output_path = VARIANT_DIR / filename
                    width, height = encode_variant(image, target_width, output_path)
                    variant_bytes = output_path.stat().st_size
                    saved_bytes = source_bytes - variant_bytes
                    saved_percent = (saved_bytes / source_bytes * 100) if source_bytes else 0.0
                    item = {
                        "target_width": target_width,
                        "status": "review-only",
                        "width": width,
                        "height": height,
                        "bytes": variant_bytes,
                        "saved_bytes": saved_bytes,
                        "saved_percent": round(saved_percent, 3),
                        "relative_path": output_path.relative_to(ROOT).as_posix(),
                    }
                    with Image.open(output_path) as variant:
                        variant.load()
                        variant_thumb = thumbnail(variant, (TILE_W - 20, 155))
                    sheet.paste(variant_thumb, (panel_x + 8, row_index * TILE_H + 42))
                    draw.text(
                        (panel_x + 8, row_index * TILE_H + 6),
                        f"{target_width}w ({width}x{height})",
                        fill="black",
                        font=font,
                    )
                    draw.text(
                        (panel_x + 8, row_index * TILE_H + 20),
                        f"{variant_bytes:,} B · {saved_percent:.1f}% smaller",
                        fill="black",
                        font=font,
                    )
                variants.append(item)

        rows.append({
            "source": source_relative,
            "source_bytes": source_bytes,
            "source_dimensions": source_dimensions,
            "source_format": source_format,
            "variants": variants,
        })

    sheet_path = OUT_DIR / "responsive-image-review-sheet.jpg"
    sheet.save(sheet_path, format="JPEG", quality=90, optimize=True)
    report = {
        "generated_by": "scripts/build-responsive-image-review.py",
        "source_context": "Tracked repository source images; exact filesystem byte sizes.",
        "usage": "Review-only artifact. Originals remain unchanged and generated files are under gitignored artifacts/.",
        "settings": {
            "target_widths": list(WIDTHS),
            "quality": QUALITY,
            "method": METHOD,
            "upscaling": False,
            "crop": False,
        },
        "source_count": len(rows),
        "variants_generated": sum(1 for row in rows for item in row["variants"] if item["status"] == "review-only"),
        "variants_skipped": sum(1 for row in rows for item in row["variants"] if item["status"] == "skipped-no-upscale"),
        "sources": rows,
        "human_visual_review_required": True,
        "runtime_references_changed": False,
    }
    report_path = OUT_DIR / "responsive-image-review.json"
    report_path.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")

    print(json.dumps({
        "source_count": report["source_count"],
        "variants_generated": report["variants_generated"],
        "variants_skipped": report["variants_skipped"],
        "review_sheet": sheet_path.relative_to(ROOT).as_posix(),
        "report": report_path.relative_to(ROOT).as_posix(),
        "runtime_references_changed": False,
    }, indent=2))


if __name__ == "__main__":
    main()
