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

    def test_encode_variant_skips_upscaling(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            source = root / "source.png"
            destination = root / "source-480.webp"
            Image.new("RGB", (320, 180), (80, 120, 160)).save(source, format="PNG")
            result = assets.encode_variant(source, destination, 480, 82, 6, 5.0)
            self.assertEqual(result["status"], "skipped-no-upscale")
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
            with patch.object(assets, "ROOT", root), patch.object(assets, "MANIFEST_PATH", manifest_path):
                with self.assertRaisesRegex(ValueError, "collide"):
                    assets.load_manifest()


if __name__ == "__main__":
    unittest.main()
