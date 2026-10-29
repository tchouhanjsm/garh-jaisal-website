import json
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

import importlib.util
import sys

from PIL import Image

SCRIPT_PATH = Path(__file__).resolve().parents[1] / "scripts" / "build-responsive-image-assets.py"
SPEC = importlib.util.spec_from_file_location("build_responsive_image_assets", SCRIPT_PATH)
if SPEC is None or SPEC.loader is None:
    raise ImportError(f"Unable to load image asset generator from {SCRIPT_PATH}")
assets = importlib.util.module_from_spec(SPEC)
sys.modules[SPEC.name] = assets
SPEC.loader.exec_module(assets)


class ResponsiveImageAssetTests(unittest.TestCase):
    def test_repository_manifest_is_valid_and_has_unique_outputs(self):
        manifest = assets.load_manifest()
        self.assertEqual(manifest["target_widths"], sorted(set(manifest["target_widths"])))
        self.assertEqual(len(manifest["sources"]), len(set(manifest["sources"])))
        self.assertGreaterEqual(len(manifest["sources"]), 40)
        self.assertEqual(manifest["quality_overrides"], {
            "img/v2/facade.jpg": 70,
            "img/v2/courtyard.jpg": 70,
        })
        self.assertEqual(assets.quality_for_source(manifest, "img/v2/facade.jpg"), 70)
        self.assertEqual(assets.quality_for_source(manifest, "img/v2/courtyard.jpg"), 70)
        self.assertEqual(assets.quality_for_source(manifest, "img/j2.jpg"), manifest["quality"])
        self.assertEqual(manifest["variant_max_bytes"], {
            "img/responsive/v2/courtyard-480.webp": 76800,
            "img/responsive/v2/facade-1200.webp": 281600,
            "img/responsive/v2/courtyard-1200.webp": 281600,
        })

    def test_variant_coverage_report_counts_generated_candidates_and_verified_fallbacks(self):
        rows = [
            {"source": "img/wide.jpg", "source_bytes": 1000, "source_dimensions": [1200, 600],
             "variants": [{"target_width": 480, "status": "generated"}, {"target_width": 800, "status": "generated"}]},
            {"source": "img/partial.jpg", "source_bytes": 800, "source_dimensions": [600, 400],
             "variants": [{"target_width": 480, "status": "generated"}, {"target_width": 800, "status": "skipped-no-upscale"}]},
            {"source": "img/small.jpg", "source_bytes": 200, "source_dimensions": [320, 180],
             "variants": [{"target_width": 480, "status": "skipped-no-upscale"}, {"target_width": 800, "status": "skipped-no-upscale"}]},
        ]
        summary = assets.summarize_variant_coverage(rows, [480, 800])
        self.assertEqual(summary["variant_opportunities"], 6)
        self.assertEqual(summary["responsive_variant_coverage_percent"], 50.0)
        self.assertEqual(summary["sources_with_complete_variant_coverage"], 1)
        self.assertEqual(summary["sources_with_partial_variant_coverage"], 1)
        self.assertEqual(summary["sources_fallback_only"], 1)
        self.assertEqual(summary["fallback_variant_opportunities"], 3)
        self.assertEqual(summary["fallback_opportunities_with_verified_source"], 3)
        self.assertEqual(summary["fallback_coverage_percent"], 100.0)
        self.assertEqual(summary["variant_status_counts"], {"generated": 3, "skipped-no-upscale": 3})
        self.assertEqual(rows[1]["generated_widths"], [480])
        self.assertEqual(rows[1]["fallback_widths"], [800])
        self.assertEqual(rows[2]["responsive_variant_coverage_percent"], 0.0)

    def test_encode_variant_skips_upscaling(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            source = root / "source.png"
            destination = root / "source-480.webp"
            Image.new("RGB", (320, 180), (80, 120, 160)).save(source, format="PNG")
            result = assets.encode_variant(source, destination, 480, 82, 6, 5.0)
            self.assertEqual(result["status"], "skipped-no-upscale")
            self.assertFalse(destination.exists())

    def test_encode_variant_rejects_output_above_a_configured_byte_budget(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            source = root / "source.jpg"
            destination = root / "source-320.webp"
            Image.effect_noise((640, 320), 55).convert("RGB").save(source, format="JPEG", quality=96)
            result = assets.encode_variant(source, destination, 320, 82, 6, 5.0, max_bytes=1)
            self.assertEqual(result["status"], "skipped-byte-budget")
            self.assertEqual(result["max_bytes"], 1)
            self.assertGreater(result["attempted_bytes"], result["max_bytes"])
            self.assertFalse(destination.exists())

    def test_encode_variant_preserves_ratio_and_drops_exif(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            source = root / "source.jpg"
            destination = root / "source-320.webp"
            image = Image.effect_noise((640, 320), 55).convert("RGB")
            exif = Image.Exif()
            exif[0x010E] = "do not copy metadata"
            image.save(source, format="JPEG", quality=96, exif=exif)
            result = assets.encode_variant(source, destination, 320, 82, 6, 5.0)
            self.assertEqual(result["status"], "generated")
            self.assertTrue(destination.is_file())
            with Image.open(destination) as derivative:
                derivative.load()
                self.assertEqual(derivative.format, "WEBP")
                self.assertEqual(derivative.size, (320, 160))
                self.assertEqual(dict(derivative.getexif()), {})
                self.assertNotIn("xmp", derivative.info)
            self.assertLess(result["bytes"], source.stat().st_size)

    def test_manifest_savings_threshold_is_enforced(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            source = root / "source.jpg"
            destination = root / "source-320.webp"
            Image.effect_noise((640, 320), 55).convert("RGB").save(source, format="JPEG", quality=96)
            result = assets.encode_variant(source, destination, 320, 82, 6, 99.9)
            self.assertEqual(result["status"], "skipped-insufficient-savings")
            self.assertFalse(destination.exists())
            self.assertLess(result["attempted_saved_percent"], 99.9)

    def test_manifest_rejects_invalid_quality_overrides(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            image_dir = root / "img"
            image_dir.mkdir()
            Image.new("RGB", (640, 320), (20, 30, 40)).save(image_dir / "same.jpg", format="JPEG")
            manifest_path = root / "manifest.json"
            base_manifest = {
                "version": 1,
                "format": "WEBP",
                "quality": 82,
                "method": 6,
                "minimum_savings_percent": 5,
                "target_widths": [320, 480],
                "sources": ["img/same.jpg"],
            }
            invalid_overrides = [
                ({"img/missing.jpg": 74}, "not listed in sources"),
                ({"img/same.jpg": True}, "Invalid quality override"),
                ({"img/same.jpg": 101}, "Invalid quality override"),
            ]
            with patch.object(assets, "ROOT", root), patch.object(assets, "OUTPUT_ROOT", root / "img" / "responsive"), patch.object(assets, "MANIFEST_PATH", manifest_path):
                for overrides, expected_error in invalid_overrides:
                    with self.subTest(overrides=overrides):
                        manifest = dict(base_manifest, quality_overrides=overrides)
                        manifest_path.write_text(json.dumps(manifest), encoding="utf-8")
                        with self.assertRaisesRegex(ValueError, expected_error):
                            assets.load_manifest()

    def test_manifest_rejects_invalid_variant_byte_budgets(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            image_dir = root / "img"
            image_dir.mkdir()
            Image.new("RGB", (640, 320), (20, 30, 40)).save(image_dir / "same.jpg", format="JPEG")
            manifest_path = root / "manifest.json"
            base_manifest = {
                "version": 1,
                "format": "WEBP",
                "quality": 82,
                "method": 6,
                "minimum_savings_percent": 5,
                "target_widths": [320, 480],
                "sources": ["img/same.jpg"],
            }
            invalid_budgets = [
                ({"img/responsive/not-listed.webp": 1000}, "not a generated manifest candidate"),
                ({"img/responsive/same-320.webp": True}, "Invalid byte budget"),
                ({"img/responsive/same-320.webp": 0}, "Invalid byte budget"),
            ]
            with patch.object(assets, "ROOT", root), patch.object(assets, "OUTPUT_ROOT", root / "img" / "responsive"), patch.object(assets, "MANIFEST_PATH", manifest_path):
                for budgets, expected_error in invalid_budgets:
                    with self.subTest(budgets=budgets):
                        manifest_path.write_text(json.dumps(dict(base_manifest, variant_max_bytes=budgets)), encoding="utf-8")
                        with self.assertRaisesRegex(ValueError, expected_error):
                            assets.load_manifest()

    def test_manifest_rejects_source_output_name_collisions(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            image_dir = root / "img"
            image_dir.mkdir()
            Image.new("RGB", (640, 320), (20, 30, 40)).save(image_dir / "same.jpg", format="JPEG")
            Image.new("RGB", (640, 320), (20, 30, 40)).save(image_dir / "same.png", format="PNG")
            manifest_path = root / "manifest.json"
            manifest_path.write_text(json.dumps({
                "version": 1,
                "format": "WEBP",
                "quality": 82,
                "method": 6,
                "minimum_savings_percent": 5,
                "target_widths": [320, 480],
                "sources": ["img/same.jpg", "img/same.png"],
            }), encoding="utf-8")
            with patch.object(assets, "ROOT", root), patch.object(assets, "OUTPUT_ROOT", root / "img" / "responsive"), patch.object(assets, "MANIFEST_PATH", manifest_path):
                with self.assertRaisesRegex(ValueError, "collide"):
                    assets.load_manifest()


if __name__ == "__main__":
    unittest.main()
