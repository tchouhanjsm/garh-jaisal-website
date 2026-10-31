import hashlib
import json
import os
import subprocess
import tempfile
import unittest
import zipfile
from pathlib import Path
from unittest.mock import patch

from scripts.build_deploy_package import (
    EXAMPLE_CONFIG_PATH,
    RELEASE_MARKER_PATH,
    PackageError,
    build_release_marker,
    build_deployment_package,
    PRIVATE_CONFIG_PATH,
    get_generated_responsive_paths,
    select_runtime_paths,
    validate_responsive_fallback_sources,
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


def create_package_git_fixture(root: Path) -> str:
    """Create a minimal clean main checkout with generated, manifest-approved WebP."""
    runtime_files = {
        ".htaccess": "Options -Indexes\n",
        "404.php": "<?php http_response_code(404);\n",
        "contact.php": "<?php\n",
        "index.php": "<?php\n",
        "mail.php": "<?php\n",
        "robots.txt": "User-agent: *\n",
        "sitemap.xml": SITEMAP,
        "include/page-head.php": "<?php\n",
        "include/site-config.php": "<?php\n",
        "room.php": "<?php\n",
        "img/facade.jpg": "original-image-fallback",
        "scripts/responsive-image-assets-manifest.json": json.dumps({
            "target_widths": [480],
            "sources": ["img/facade.jpg"],
            "variant_max_bytes": {"img/responsive/facade-480.webp": 256},
        }),
        ".gitignore": "img/responsive/\n",
    }
    for relative, content in runtime_files.items():
        target = root / relative
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(content, encoding="utf-8")

    generated = root / "img" / "responsive" / "facade-480.webp"
    generated.parent.mkdir(parents=True, exist_ok=True)
    generated.write_bytes(b"approved-webp-candidate")

    subprocess.run(
        ["git", "init", "--initial-branch=main", str(root)],
        check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True,
    )
    subprocess.run(["git", "config", "user.name", "Package Fixture"], cwd=root, check=True)
    subprocess.run(["git", "config", "user.email", "package-fixture@example.invalid"], cwd=root, check=True)
    subprocess.run(["git", "add", "--all"], cwd=root, check=True)
    subprocess.run(
        ["git", "commit", "-m", "fixture: clean deployment source"],
        cwd=root, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True,
    )
    return subprocess.run(
        ["git", "rev-parse", "HEAD"], cwd=root, check=True,
        stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True,
    ).stdout.strip()


class DeploymentPackageSelectionTests(unittest.TestCase):
    def test_selects_runtime_files_and_excludes_development_files(self):
        tracked = [
            ".htaccess", "index.php", "room.php", "robots.txt", "sitemap.xml",
            "contact.php", "404.php", "mail.php", "include/page-head.php",
            "include/site-config.php", EXAMPLE_CONFIG_PATH, "css/garh-v2.css",
            "img/facade.webp", "js/garh-v2.js", "docs/DEPLOY-CPANEL.md",
            "scripts/check-site.php", "tests/e2e/site-smoke.spec.js",
            ".github/workflows/ci.yml", "package.json", "package-lock.json", "playwright.config.js",
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
            "package.json", "package-lock.json", "playwright.config.js",
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

    def test_release_marker_is_generated_from_a_full_commit_sha(self):
        commit_sha = "A" * 40
        expected = "GARH_JAISAL_RELEASE_SHA=" + ("a" * 40) + chr(10)
        self.assertEqual(build_release_marker(commit_sha).decode("ascii"), expected)
        for invalid in ("abc123", "g" * 40, "", None):
            with self.subTest(invalid=invalid):
                with self.assertRaisesRegex(PackageError, "full 40-character Git commit SHA"):
                    build_release_marker(invalid)

    def test_release_marker_path_cannot_be_supplied_as_a_tracked_file(self):
        with self.assertRaisesRegex(PackageError, "generated during package creation"):
            select_runtime_paths([RELEASE_MARKER_PATH])

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

    def test_responsive_fallback_sources_must_be_regular_files_in_the_runtime_package(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scripts").mkdir()
            source_dir = root / "img" / "v2"
            source_dir.mkdir(parents=True)
            (source_dir / "facade.jpg").write_bytes(b"original-fallback")
            (source_dir / "courtyard.jpg").write_bytes(b"original-fallback")
            (root / "scripts" / "responsive-image-assets-manifest.json").write_text(json.dumps({
                "target_widths": [480, 800, 1200],
                "sources": ["img/v2/facade.jpg", "img/v2/courtyard.jpg"],
            }), encoding="utf-8")
            selected = ["img/v2/facade.jpg", "img/v2/courtyard.jpg"]
            self.assertEqual(validate_responsive_fallback_sources(selected, root),
                             ["img/v2/courtyard.jpg", "img/v2/facade.jpg"])
            with self.assertRaisesRegex(PackageError, "missing from the runtime package"):
                validate_responsive_fallback_sources(["img/v2/facade.jpg"], root)

    def test_responsive_fallback_source_symlink_is_rejected(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scripts").mkdir()
            source_dir = root / "img" / "v2"
            source_dir.mkdir(parents=True)
            outside = root / "outside.jpg"
            outside.write_bytes(b"outside")
            (root / "scripts" / "responsive-image-assets-manifest.json").write_text(json.dumps({
                "target_widths": [480],
                "sources": ["img/v2/facade.jpg"],
            }), encoding="utf-8")
            try:
                (source_dir / "facade.jpg").symlink_to(outside)
            except (OSError, NotImplementedError):
                self.skipTest("Temporary symlinks are unavailable on this runner")
            with self.assertRaisesRegex(PackageError, "must not use symlinks"):
                validate_responsive_fallback_sources(["img/v2/facade.jpg"], root)

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

    def test_generated_responsive_paths_enforce_manifest_byte_budgets(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scripts").mkdir()
            output = root / "img" / "responsive" / "v2"
            output.mkdir(parents=True)
            manifest = {
                "target_widths": [480, 800],
                "sources": ["img/v2/facade.jpg"],
                "variant_max_bytes": {"img/responsive/v2/facade-480.webp": 12},
            }
            (root / "scripts" / "responsive-image-assets-manifest.json").write_text(json.dumps(manifest), encoding="utf-8")
            (output / "facade-480.webp").write_bytes(b"candidate-over-budget")
            (output / "facade-800.webp").write_bytes(b"within-list-but-not-budgeted")
            with self.assertRaisesRegex(PackageError, "exceeds its manifest byte budget"):
                get_generated_responsive_paths(root)

    def test_generated_responsive_paths_require_budgeted_candidates_to_exist(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scripts").mkdir()
            output = root / "img" / "responsive" / "v2"
            output.mkdir(parents=True)
            manifest = {
                "target_widths": [480, 800],
                "sources": ["img/v2/facade.jpg"],
                "variant_max_bytes": {"img/responsive/v2/facade-480.webp": 1024},
            }
            (root / "scripts" / "responsive-image-assets-manifest.json").write_text(json.dumps(manifest), encoding="utf-8")
            (output / "facade-800.webp").write_bytes(b"candidate")
            with self.assertRaisesRegex(PackageError, "Budgeted responsive image candidate is missing"):
                get_generated_responsive_paths(root)

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


    def test_archive_release_marker_manifest_checksum_and_reproducibility(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory) / "repo"
            root.mkdir()
            commit_sha = create_package_git_fixture(root)
            first_output = Path(directory) / "package-one"
            second_output = Path(directory) / "package-two"
            workflow_env = {
                "GITHUB_ACTIONS": "true",
                "GITHUB_REF": "refs/heads/main",
                "GITHUB_SHA": commit_sha,
            }

            with patch.dict(os.environ, workflow_env):
                first = build_deployment_package(root, first_output)
                second = build_deployment_package(root, second_output)

            self.assertEqual(first["commit"], commit_sha)
            self.assertEqual(first["package_sha256"], second["package_sha256"])

            archive_path = Path(first["package"])
            manifest = json.loads(Path(first["manifest"]).read_text(encoding="utf-8"))
            checksum = Path(first["checksum"]).read_text(encoding="utf-8")
            archive_bytes = archive_path.read_bytes()
            expected_marker = build_release_marker(commit_sha)

            self.assertEqual(hashlib.sha256(archive_bytes).hexdigest(), manifest["package_sha256"])
            self.assertEqual(checksum, manifest["package_sha256"] + "  " + archive_path.name + "\n")
            self.assertEqual(manifest["release_marker"]["path"], RELEASE_MARKER_PATH)
            self.assertEqual(hashlib.sha256(expected_marker).hexdigest(), manifest["release_marker"]["sha256"])

            marker_entries = [item for item in manifest["files"] if item["path"] == RELEASE_MARKER_PATH]
            self.assertEqual(len(marker_entries), 1)
            self.assertEqual(marker_entries[0]["sha256"], manifest["release_marker"]["sha256"])
            with zipfile.ZipFile(archive_path) as archive:
                self.assertIn(RELEASE_MARKER_PATH, archive.namelist())
                self.assertEqual(archive.read(RELEASE_MARKER_PATH), expected_marker)
                self.assertNotIn("include/site-config.local.php", archive.namelist())
                self.assertNotIn("scripts/responsive-image-assets-manifest.json", archive.namelist())

    def test_archive_build_rejects_a_workflow_sha_that_does_not_match_the_checkout(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory) / "repo"
            root.mkdir()
            create_package_git_fixture(root)
            with patch.dict(os.environ, {
                "GITHUB_ACTIONS": "true",
                "GITHUB_REF": "refs/heads/main",
                "GITHUB_SHA": "f" * 40,
            }):
                with self.assertRaisesRegex(PackageError, "does not match GITHUB_SHA"):
                    build_deployment_package(root, Path(directory) / "package")


if __name__ == "__main__":
    unittest.main()
