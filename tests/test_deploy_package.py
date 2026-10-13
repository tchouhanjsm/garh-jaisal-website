import unittest

from scripts.build_deploy_package import (
    EXAMPLE_CONFIG_PATH,
    PackageError,
    PRIVATE_CONFIG_PATH,
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

    def test_nested_published_route_fails_closed(self):
        with self.assertRaisesRegex(PackageError, "Unexpected nested published route"):
            sitemap_route_targets(
                "<urlset><url><loc>https://www.hotelgarhjaisal.com/nested/room.php</loc></url></urlset>"
            )


if __name__ == "__main__":
    unittest.main()
