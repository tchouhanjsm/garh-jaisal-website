#!/usr/bin/env python3
"""Generate checked, non-upscaled responsive WebP variants for the PHP site.

The originals remain untouched. Accepted outputs live in img/responsive/ and are
re-created deterministically by the browser and deployment-package workflows.
Candidates must be smaller than their source by the configured minimum percentage.
"""

from __future__ import annotations

import argparse
import json
import shutil
from pathlib import Path
from typing import Any

from PIL import Image, ImageDraw, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[1]
MANIFEST_PATH = ROOT / "scripts" / "responsive-image-assets-manifest.json"
OUTPUT_ROOT = ROOT / "img" / "responsive"
REPORT_DIR = ROOT / "artifacts" / "responsive-image-assets"
QUALITY = 82
METHOD = 6


def output_path(source_relative: str, target_width: int) -> Path:
    source = Path(source_relative)
    relative = source.relative_to("img")
    return OUTPUT_ROOT / relative.parent / (relative.stem + f"-{target_width}.webp")


def load_manifest() -> dict[str, Any]:
    manifest = json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))
    widths = manifest.get("target_widths")
    sources = manifest.get("sources")
    if not isinstance(widths, list) or not widths or any(
        not isinstance(width, int) or width < 1 for width in widths
    ):
        raise ValueError("target_widths must be a non-empty list of positive integers")
    if widths != sorted(set(widths)):
        raise ValueError("target_widths must be unique and sorted ascending")
    if not isinstance(sources, list) or not sources:
        raise ValueError("sources must be a non-empty list")
    if len(sources) != len(set(sources)):
        raise ValueError("sources must not contain duplicates")

    output_paths: set[str] = set()
    for source in sources:
        if not isinstance(source, str):
            raise ValueError(f"Unsafe or unsupported source path: {source!r}")
        source_path = Path(source)
        if (
            source_path.is_absolute()
            or len(source_path.parts) < 2
            or source_path.parts[0] != "img"
            or ".." in source_path.parts
            or "\\" in source
            or source_path.suffix.lower() not in {".jpg", ".jpeg", ".png", ".webp", ".avif"}
        ):
            raise ValueError(f"Unsafe or unsupported source path: {source!r}")
        candidate = ROOT / source_path
        if not candidate.is_file() or candidate.is_symlink():
            raise FileNotFoundError(f"Manifest source is missing or is a symlink: {source}")
        for width in widths:
            generated = output_path(source, width).relative_to(ROOT).as_posix()
            if generated in output_paths:
                raise ValueError(f"Manifest sources collide on generated output path: {generated}")
            output_paths.add(generated)

    if manifest.get("format") != "WEBP":
        raise ValueError("Only the reviewed WEBP format is currently supported")
    quality = int(manifest.get("quality", QUALITY))
    method = int(manifest.get("method", METHOD))
    savings = float(manifest.get("minimum_savings_percent", 5))
    if not 1 <= quality <= 100 or not 0 <= method <= 6 or not 0 <= savings < 100:
        raise ValueError("Invalid quality, method or minimum_savings_percent setting")

    quality_overrides = manifest.get("quality_overrides", {})
    if not isinstance(quality_overrides, dict):
        raise ValueError("quality_overrides must be a source-to-quality object")
    for override_source, override_quality in quality_overrides.items():
        if override_source not in sources:
            raise ValueError(f"quality_overrides source is not listed in sources: {override_source!r}")
        if (
            isinstance(override_quality, bool)
            or not isinstance(override_quality, int)
            or not 1 <= override_quality <= 100
        ):
            raise ValueError(f"Invalid quality override for {override_source!r}: {override_quality!r}")
    return manifest


def quality_for_source(manifest: dict[str, Any], source_relative: str) -> int:
    """Use a narrowly scoped override while preserving the manifest default."""
    overrides = manifest.get("quality_overrides", {})
    return int(overrides.get(source_relative, manifest["quality"]))


def summarize_variant_coverage(rows: list[dict[str, Any]], widths: list[int]) -> dict[str, Any]:
    """Report generated-candidate coverage and verify skipped widths retain source fallbacks."""
    width_count = len(widths)
    variants = [variant for row in rows for variant in row.get("variants", [])]
    generated = [variant for variant in variants if variant.get("status") == "generated"]
    skipped = [variant for variant in variants if variant.get("status") != "generated"]
    status_counts: dict[str, int] = {}
    for variant in variants:
        status = str(variant.get("status", "unknown"))
        status_counts[status] = status_counts.get(status, 0) + 1

    complete_sources = partial_sources = fallback_only_sources = 0
    verified_fallback_opportunities = 0
    for row in rows:
        dimensions = row.get("source_dimensions")
        verified = (
            isinstance(row.get("source_bytes"), int)
            and row["source_bytes"] > 0
            and isinstance(dimensions, (list, tuple))
            and len(dimensions) == 2
            and all(isinstance(value, int) and value > 0 for value in dimensions)
        )
        row_variants = row.get("variants", [])
        generated_widths = sorted(
            int(item["target_width"]) for item in row_variants
            if item.get("status") == "generated" and isinstance(item.get("target_width"), int)
        )
        fallback_widths = sorted(
            int(item["target_width"]) for item in row_variants
            if item.get("status") != "generated" and isinstance(item.get("target_width"), int)
        )
        row["generated_widths"] = generated_widths
        row["fallback_widths"] = fallback_widths
        row["responsive_variant_coverage_percent"] = round(
            len(generated_widths) / width_count * 100, 3
        ) if width_count else 100.0
        row["source_fallback_verified"] = verified
        if len(generated_widths) == width_count:
            complete_sources += 1
        elif generated_widths:
            partial_sources += 1
        else:
            fallback_only_sources += 1
        if verified:
            verified_fallback_opportunities += len(fallback_widths)

    opportunities = len(rows) * width_count
    fallback_opportunities = len(skipped)
    return {
        "variant_opportunities": opportunities,
        "responsive_variant_coverage_percent": round(
            len(generated) / opportunities * 100, 3
        ) if opportunities else 100.0,
        "sources_with_complete_variant_coverage": complete_sources,
        "sources_with_partial_variant_coverage": partial_sources,
        "sources_fallback_only": fallback_only_sources,
        "fallback_variant_opportunities": fallback_opportunities,
        "fallback_opportunities_with_verified_source": verified_fallback_opportunities,
        "fallback_coverage_percent": round(
            verified_fallback_opportunities / fallback_opportunities * 100, 3
        ) if fallback_opportunities else 100.0,
        "variant_status_counts": dict(sorted(status_counts.items())),
    }


def encode_variant(
    source_path: Path,
    destination: Path,
    target_width: int,
    quality: int,
    method: int,
    minimum_savings_percent: float,
) -> dict[str, Any]:
    with Image.open(source_path) as opened:
        opened.load()
        original_size = opened.size
        if opened.width <= target_width:
            return {
                "status": "skipped-no-upscale",
                "width": opened.width,
                "height": opened.height,
                "bytes": None,
                "saved_bytes": None,
                "saved_percent": None,
            }

        target_height = max(1, round(opened.height * target_width / opened.width))
        has_alpha = "A" in opened.getbands() or "transparency" in opened.info
        encoded = opened.convert("RGBA" if has_alpha else "RGB")
        options: dict[str, Any] = {
            "format": "WEBP",
            "quality": quality,
            "method": method,
            "exact": True,
        }
        # Preserve color interpretation, but omit EXIF/XMP metadata that can
        # contain device identifiers, GPS coordinates, or editorial history.
        profile = opened.info.get("icc_profile")
        if profile:
            options["icc_profile"] = profile
        encoded = encoded.resize((target_width, target_height), Image.Resampling.LANCZOS)
        destination.parent.mkdir(parents=True, exist_ok=True)
        encoded.save(destination, **options)

    with Image.open(destination) as check:
        check.load()
        if check.format != "WEBP":
            raise ValueError(f"Variant encoder did not write WebP: {destination}")
        if check.size != (target_width, target_height):
            raise ValueError(f"Unexpected variant dimensions for {destination}: {check.size}")
        if check.width > original_size[0]:
            raise ValueError(f"Upscaling is not allowed: {destination}")

    source_bytes = source_path.stat().st_size
    variant_bytes = destination.stat().st_size
    saved_bytes = source_bytes - variant_bytes
    saved_percent = (saved_bytes / source_bytes * 100) if source_bytes else 0.0
    if saved_bytes <= 0 or saved_percent < minimum_savings_percent:
        destination.unlink(missing_ok=True)
        return {
            "status": "skipped-insufficient-savings",
            "width": target_width,
            "height": target_height,
            "bytes": None,
            "saved_bytes": None,
            "saved_percent": None,
            "attempted_bytes": variant_bytes,
            "attempted_saved_percent": round(saved_percent, 3),
        }

    return {
        "status": "generated",
        "width": target_width,
        "height": target_height,
        "bytes": variant_bytes,
        "saved_bytes": saved_bytes,
        "saved_percent": round(saved_percent, 3),
    }


def thumbnail(source: Path, size: tuple[int, int]) -> Image.Image:
    with Image.open(source) as opened:
        opened.load()
        return ImageOps.contain(opened.convert("RGB"), size, method=Image.Resampling.LANCZOS)


def build_review_sheet(rows: list[dict[str, Any]], widths: list[int]) -> Path | None:
    if not rows:
        return None
    REPORT_DIR.mkdir(parents=True, exist_ok=True)
    panel_width = 320
    panel_height = 196
    columns = 1 + len(widths)
    sheet = Image.new("RGB", (columns * panel_width, len(rows) * panel_height), "white")
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.load_default()

    for row_index, row in enumerate(rows):
        y = row_index * panel_height
        source = ROOT / row["source"]
        draw.text((8, y + 5), row["source"], fill="black", font=font)
        source_image = thumbnail(source, (panel_width - 16, 148))
        sheet.paste(source_image, (8, y + 25))
        draw.text((8, y + 176), f'Original {row["source_bytes"]:,} B', fill="black", font=font)

        for column, width in enumerate(widths, start=1):
            x = column * panel_width
            variant = next((item for item in row["variants"] if item["target_width"] == width), None)
            draw.text((x + 8, y + 5), f"{width}px target", fill="black", font=font)
            if variant and variant["status"] == "generated":
                candidate = ROOT / variant["relative_path"]
                preview = thumbnail(candidate, (panel_width - 16, 148))
                sheet.paste(preview, (x + 8, y + 25))
                draw.text(
                    (x + 8, y + 176),
                    f'{variant["bytes"]:,} B · -{variant["saved_percent"]:.1f}%',
                    fill="black",
                    font=font,
                )
            else:
                status = (variant or {}).get("status", "not generated")
                draw.text((x + 8, y + 80), status, fill="black", font=font)

    path = REPORT_DIR / "responsive-image-delivery-review.jpg"
    sheet.save(path, format="JPEG", quality=88, optimize=True)
    return path


def build() -> dict[str, Any]:
    manifest = load_manifest()
    widths = manifest["target_widths"]
    quality = int(manifest["quality"])
    method = int(manifest["method"])
    minimum_savings = float(manifest["minimum_savings_percent"])

    if OUTPUT_ROOT.exists():
        if OUTPUT_ROOT.is_symlink():
            raise ValueError("Refusing to replace a symlinked responsive image directory")
        shutil.rmtree(OUTPUT_ROOT)
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    REPORT_DIR.mkdir(parents=True, exist_ok=True)

    rows: list[dict[str, Any]] = []
    generated_paths: list[str] = []

    for source_relative in manifest["sources"]:
        source = ROOT / source_relative
        with Image.open(source) as opened:
            opened.load()
            source_dimensions = [opened.width, opened.height]
            source_bytes = source.stat().st_size
            source_format = opened.format or source.suffix.lstrip(".").upper()

        variants = []
        for width in widths:
            path = output_path(source_relative, width)
            source_quality = quality_for_source(manifest, source_relative)
            result = encode_variant(source, path, width, source_quality, method, minimum_savings)
            result.update({
                "target_width": width,
                "source_width": source_dimensions[0],
                "source_height": source_dimensions[1],
                "quality": source_quality,
                "relative_path": path.relative_to(ROOT).as_posix() if result["status"] == "generated" else None,
            })
            variants.append(result)
            if result["status"] == "generated":
                generated_paths.append(result["relative_path"])

        row = {
            "source": source_relative,
            "source_format": source_format,
            "source_dimensions": source_dimensions,
            "source_bytes": source_bytes,
            "quality": source_quality,
            "variants": variants,
        }
        rows.append(row)

    if not generated_paths:
        raise RuntimeError("No responsive image asset passed the byte-saving gate")

    required_hero_variants = {
        f"img/responsive/v2/{stem}-{width}.webp"
        for stem in ("facade", "fort-from-roof", "courtyard")
        for width in widths
    }
    missing_hero_variants = sorted(required_hero_variants - set(generated_paths))
    if missing_hero_variants:
        raise RuntimeError(
            "Required homepage hero responsive variants failed the savings/width gate: "
            + ", ".join(missing_hero_variants)
        )

    review_sheet = build_review_sheet(rows, widths)
    coverage_metrics = summarize_variant_coverage(rows, widths)
    summary = {
        "source_count": len(rows),
        "variants_generated": len(generated_paths),
        "variants_skipped": sum(1 for row in rows for v in row["variants"] if v["status"] != "generated"),
        "source_bytes": sum(row["source_bytes"] for row in rows),
        "generated_variant_bytes": sum(
            variant["bytes"] for row in rows for variant in row["variants"] if variant["status"] == "generated"
        ),
        "generated_saved_bytes_vs_each_source": sum(
            variant["saved_bytes"] for row in rows for variant in row["variants"] if variant["status"] == "generated"
        ),
        "human_visual_review_required": True,
        **coverage_metrics,
        "required_hero_variant_count": len(required_hero_variants),
        "required_hero_variant_coverage_percent": round(
            len(required_hero_variants & set(generated_paths)) / len(required_hero_variants) * 100, 3
        ) if required_hero_variants else 100.0,
    }
    report = {
        "generated_by": "scripts/build-responsive-image-assets.py",
        "settings": {
            "widths": widths,
            "format": manifest["format"],
            "quality": quality,
            "quality_overrides": dict(manifest.get("quality_overrides", {})),
            "method": method,
            "minimum_savings_percent": minimum_savings,
            "upscaling": False,
            "cropping": False,
            "originals_modified": False,
            "exif_xmp_metadata_copied": False,
        },
        "summary": summary,
        "sources": rows,
        "generated_paths": sorted(generated_paths),
        "required_hero_variants": {
            "required": sorted(required_hero_variants),
            "generated": sorted(required_hero_variants & set(generated_paths)),
            "missing": missing_hero_variants,
            "coverage_percent": summary["required_hero_variant_coverage_percent"],
        },
        "review_sheet": review_sheet.relative_to(ROOT).as_posix() if review_sheet else None,
    }
    report_path = REPORT_DIR / "responsive-image-delivery-report.json"
    report_path.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")
    return {"summary": summary, "report": report_path.relative_to(ROOT).as_posix(), "review_sheet": review_sheet.relative_to(ROOT).as_posix() if review_sheet else None}


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check", action="store_true", help="Validate the manifest and decode every source without generating assets.")
    args = parser.parse_args()

    try:
        manifest = load_manifest()
        if args.check:
            total_source_bytes = 0
            for relative in manifest["sources"]:
                source = ROOT / relative
                # verify() validates the container; load() fully decodes the pixels.
                with Image.open(source) as image:
                    image.verify()
                with Image.open(source) as image:
                    image.load()
                    if image.width < 1 or image.height < 1:
                        raise ValueError(f"Source has invalid dimensions: {relative}")
                total_source_bytes += source.stat().st_size
            print(json.dumps({
                "status": "PASS",
                "source_count": len(manifest["sources"]),
                "source_bytes": total_source_bytes,
                "target_widths": manifest["target_widths"],
                "minimum_savings_percent": manifest["minimum_savings_percent"],
                "manifest": MANIFEST_PATH.relative_to(ROOT).as_posix(),
                "assets_generated": False,
            }, indent=2))
            return 0
        result = build()
        print(json.dumps({"status": "PASS", **result}, indent=2))
        return 0
    except (OSError, ValueError, RuntimeError, json.JSONDecodeError) as error:
        print("RESPONSIVE IMAGE DELIVERY ERROR: " + str(error))
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
