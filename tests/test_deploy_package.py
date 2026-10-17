import json
import tempfile
import unittest
from pathlib import Path

from scripts.build_deploy_package import (
    EXAMPLE_CONFIG_PATH,
    PackageError,
    PRIVATE_CONFIG_PATH,
    get_generated_responsive_paths,
    select_runtime_paths,
    sitemap_route_targets,
    validate_package_paths,
)


SITEMAP = """<?xml version="1.0" encoding="UTF-8"?>
<urlset>
  <url><loc>https://www.hotelgarhjaisal.com/</loc></url>
  <url><loc>https://www.hotelgarhjaisal.com/room.php</loc></url>
  <url><loc>https://www.hotelgarhjaisal.com/contact.php</loc></url>
</urlset>
"""


class DeploymentPackageSelectionTests(unittest.TestCase):
    def test_selects_runtime_files_and_excludes_development_files(self):
        tracked = [
            ".htaccess", "index.php", "room.php", "robots.txt", "sitemap.xml",
            "contact.php", "404.php", "mail.php", "include/page-head.php",
            "include/site-config.php", EXAMPLE_CONFIG_PATH, "css/garh-v2.css",
            "img/facade.webp", "js/garh-v2.js", "docs/DEPLOY-CPANEL.md",
            "scripts/check-site.php", "tests/e2e/site-smoke.spec.js",
            ".github/workflows/ci.yml", "package.json", "playwright.config.js",
        ]
        selected = select_runtime_paths(tracked)
        self.assertIn(".htaccess", selected)
        self.assertIn("include/site-config.php", selected)
        self.assertIn("css/garh-v2.css", selected)
        self.assertIn("img/facade.webp", selected)
        self.assertIn("js/garh-v2.js", selected)
        self.assertNotIn(EXAMPLE_CONFIG_PATH, selected)
        for development_path in (
            "docs/DEPLOY-CPANEL.md", "scripts/check-site.php",
            "tests/e2e/site-smoke.spec.js", ".github/workflows/ci.yml",
            "package.json", "playwright.config.js",
        ):
            self.assertNotIn(development_path, selected)

    def test_known_obsolete_and_documentation_only_assets_are_not_deployed(self):
        selected = select_runtime_paths([
            ".htaccess", "index.php", "robots.txt", "sitemap.xml",
            "include/page-head.php", "include/site-config.php", "404.php",
            "contact.php", "mail.php", "img/tent1.jpg", "restaurant.css",
            "restaurant.js", "thanx.png", "css/owl.video.play.html",
            "css/restaurant.css", "js/restaurant.js", "img/optimized/v2/purple-room.webp",
        ])
        for excluded in ("img/tent1.jpg", "restaurant.css", "restaurant.js", "thanx.png", "css/owl.video.play.html"):
            self.assertNotIn(excluded, selected)
        self.assertIn("css/restaurant.css", selected)
        self.assertIn("js/restaurant.js", selected)
        self.assertIn("img/optimized/v2/purple-room.webp", selected)

    def test_private_local_config_is_never_packageable(self):
        with self.assertRaisesRegex(PackageError, "must never be tracked"):
            select_runtime_paths([PRIVATE_CONFIG_PATH])

    def test_every_sitemap_route_must_be_included(self):
        selected = [
            ".htaccess", "404.php", "contact.php", "index.php", "mail.php",
            "robots.txt", "sitemap.xml", "include/page-head.php",
            "include/site-config.php", "room.php",
        ]
        routes = validate_package_paths(selected, SITEMAP)
        self.assertEqual([route for route, _ in routes], ["/", "/contact.php", "/room.php"])
        with self.assertRaisesRegex(PackageError, "room.php"):
            validate_package_paths([p for p in selected if p != "room.php"], SITEMAP)

    def test_rejects_noncanonical_sitemap_hosts_and_paths(self):
        with self.assertRaisesRegex(PackageError, "Unexpected sitemap host/scheme"):
            sitemap_route_targets(
                "<urlset><url><loc>https://example.com/contact.php</loc></url></urlset>"
            )
        with self.assertRaisesRegex(PackageError, "Unsafe sitemap route"):
            sitemap_route_targets(
                "<urlset><url><loc>https://www.hotelgarhjaisal.com/%2e%2e/secret.php</loc></url></urlset>"
            )

    def test_generated_responsive_paths_are_selected_from_manifest(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scripts").mkdir()
            output = root / "img" / "responsive" / "v2"
            output.mkdir(parents=True)
            (root / "scripts" / "responsive-image-assets-manifest.json").write_text(json.dumps({
                "target_widths": [480, 800],
                "sources": ["img/v2/facade.jpg"],
            }), encoding="utf-8")
            (output / "facade-480.webp").write_bytes(b"test-webp-480")
            (output / "facade-800.webp").write_bytes(b"test-webp-800")
            self.assertEqual(get_generated_responsive_paths(root), [
                "img/responsive/v2/facade-480.webp",
                "img/responsive/v2/facade-800.webp",
            ])

    def test_generated_responsive_paths_reject_unlisted_file(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scripts").mkdir()
            output = root / "img" / "responsive"
            output.mkdir(parents=True)
            (root / "scripts" / "responsive-image-assets-manifest.json").write_text(json.dumps({
                "target_widths": [480],
                "sources": ["img/facade.jpg"],
            }), encoding="utf-8")
            (output / "unlisted.webp").write_bytes(b"unlisted")
            with self.assertRaisesRegex(PackageError, "Unexpected generated responsive image path"):
                get_generated_responsive_paths(root)

    def test_nested_published_route_fails_closed(self):
        with self.assertRaisesRegex(PackageError, "Unexpected nested published route"):
            sitemap_route_targets(
                "<urlset><url><loc>https://www.hotelgarhjaisal.com/nested/room.php</loc></url></urlset>"
            )


if __name__ == "__main__":
    unittest.main()
