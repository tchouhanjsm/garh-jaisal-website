#!/usr/bin/env python3
"""Create a reproducible, runtime-only cPanel deployment package.

The package contains only tracked website runtime files. It never deploys anything.
Archive generation is deliberately restricted to a clean main-branch checkout.
"""
from __future__ import annotations

import argparse
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import re
import subprocess
import sys
from urllib.parse import unquote, urlsplit
import zipfile

ROOT = Path(__file__).resolve().parents[1]
RUNTIME_DIRECTORIES = {"css", "img", "include", "js"}
ROOT_RUNTIME_FILES = {".htaccess", "robots.txt", "sitemap.xml"}
ROOT_RUNTIME_SUFFIXES = {
    ".php", ".css", ".js", ".png", ".jpg", ".jpeg", ".webp", ".gif",
    ".svg", ".ico", ".avif", ".xml", ".txt", ".webmanifest",
}
REQUIRED_RUNTIME_FILES = {
    ".htaccess", "404.php", "contact.php", "index.php", "mail.php",
    "robots.txt", "sitemap.xml", "include/page-head.php", "include/site-config.php",
}
PRIVATE_CONFIG_PATH = "include/site-config.local.php"
EXAMPLE_CONFIG_PATH = "include/site-config.example.php"
NON_RUNTIME_ROOT_FILES = {"package.json", "playwright.config.js"}
CANONICAL_HOST = "www.hotelgarhjaisal.com"
ZIP_TIMESTAMP = (1980, 1, 1, 0, 0, 0)


class PackageError(RuntimeError):
    """An unsafe or incomplete deployment package was detected."""


def run_git(root: Path, *arguments: str) -> str:
    result = subprocess.run(
        ["git", *arguments], cwd=root, check=False,
        stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True,
    )
    if result.returncode != 0:
        raise PackageError(
            "git " + " ".join(arguments) + " failed: " + result.stderr.strip()
        )
    return result.stdout.strip()


def get_tracked_paths(root: Path = ROOT) -> list[str]:
    result = subprocess.run(
        ["git", "ls-files", "-z"], cwd=root, check=False,
        stdout=subprocess.PIPE, stderr=subprocess.PIPE,
    )
    if result.returncode != 0:
        raise PackageError(
            "Unable to list tracked files: " + result.stderr.decode("utf-8", "replace")
        )
    return sorted(path.decode("utf-8") for path in result.stdout.split(b"\0") if path)


def ensure_tracked_worktree_clean(root: Path = ROOT) -> None:
    for arguments in (("diff", "--quiet", "HEAD", "--"), ("diff", "--cached", "--quiet")):
        result = subprocess.run(
            ["git", *arguments], cwd=root, check=False,
            stdout=subprocess.DEVNULL, stderr=subprocess.PIPE,
        )
        if result.returncode != 0:
            raise PackageError(
                "Tracked worktree changes detected; commit or discard them before packaging."
            )


def is_runtime_path(path: str) -> bool:
    pure = PurePosixPath(path)
    if pure.is_absolute() or ".." in pure.parts or "\\" in path:
        raise PackageError("Unsafe tracked path: " + path)
    if path == PRIVATE_CONFIG_PATH:
        raise PackageError(
            PRIVATE_CONFIG_PATH + " must never be tracked; keep it server-only."
        )
    if path == EXAMPLE_CONFIG_PATH or path in NON_RUNTIME_ROOT_FILES:
        return False
    if path in ROOT_RUNTIME_FILES:
        return True
    if len(pure.parts) == 1 and pure.suffix.lower() in ROOT_RUNTIME_SUFFIXES:
        return True
    return bool(pure.parts and pure.parts[0] in RUNTIME_DIRECTORIES and len(pure.parts) > 1)


def select_runtime_paths(tracked_paths: list[str]) -> list[str]:
    return sorted(path for path in set(tracked_paths) if is_runtime_path(path))


def sitemap_route_targets(sitemap_text: str) -> list[tuple[str, str]]:
    locations = re.findall(
        r"<loc>\s*(https?://[^<]+)\s*</loc>", sitemap_text, flags=re.IGNORECASE
    )
    if not locations:
        raise PackageError("sitemap.xml contains no absolute <loc> URLs.")

    targets = []
    for location in locations:
        parsed = urlsplit(location.strip())
        if parsed.scheme != "https" or parsed.hostname != CANONICAL_HOST:
            raise PackageError("Unexpected sitemap host/scheme: " + location)
        if parsed.query or parsed.fragment:
            raise PackageError("Sitemap route must not contain a query or fragment: " + location)
        path = unquote(parsed.path or "/")
        if "\\" in path or any(part == ".." for part in path.split("/")):
            raise PackageError("Unsafe sitemap route: " + location)
        if path in ("", "/"):
            target = "index.php"
        else:
            target = path.lstrip("/")
            if "/" in target:
                raise PackageError("Unexpected nested published route: " + location)
        targets.append((path, target))
    return sorted(set(targets), key=lambda pair: (pair[0] != "/", pair[0]))


def validate_package_paths(
    selected_paths: list[str], sitemap_text: str
) -> list[tuple[str, str]]:
    selected = set(selected_paths)
    missing_required = sorted(REQUIRED_RUNTIME_FILES - selected)
    if missing_required:
        raise PackageError("Required runtime files are missing: " + ", ".join(missing_required))

    forbidden_prefixes = (
        ".git/", ".github/", ".agents/", ".claude/", "docs/", "scripts/",
        "tests/", "node_modules/", "artifacts/",
    )
    forbidden_paths = sorted(
        path for path in selected
        if path.startswith(forbidden_prefixes)
        or path in {PRIVATE_CONFIG_PATH, EXAMPLE_CONFIG_PATH, "package.json", "playwright.config.js"}
    )
    if forbidden_paths:
        raise PackageError("Non-runtime or private files selected: " + ", ".join(forbidden_paths))

    routes = sitemap_route_targets(sitemap_text)
    missing_routes = sorted((route, target) for route, target in routes if target not in selected)
    if missing_routes:
        details = ", ".join(route or "/" for route, _ in missing_routes)
        raise PackageError("Published sitemap routes are missing from the package: " + details)
    return routes


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def build_deployment_package(root: Path, output_dir: Path) -> dict:
    ensure_tracked_worktree_clean(root)
    selected = select_runtime_paths(get_tracked_paths(root))
    sitemap_path = root / "sitemap.xml"
    if not sitemap_path.is_file():
        raise PackageError("sitemap.xml is missing.")
    routes = validate_package_paths(selected, sitemap_path.read_text(encoding="utf-8"))

    if os.environ.get("GITHUB_ACTIONS", "").lower() == "true":
        if os.environ.get("GITHUB_REF") != "refs/heads/main":
            raise PackageError("Archive generation is allowed only for refs/heads/main.")
    elif run_git(root, "branch", "--show-current") != "main":
        raise PackageError("Archive generation is allowed only from the local main branch.")

    commit_sha = run_git(root, "rev-parse", "HEAD")
    workflow_sha = os.environ.get("GITHUB_SHA")
    if workflow_sha and workflow_sha != commit_sha:
        raise PackageError("Checked-out commit does not match GITHUB_SHA.")

    entries = []
    total_bytes = 0
    for relative in selected:
        source = root / relative
        if source.is_symlink() or not source.is_file():
            raise PackageError("Runtime file is missing or is a symlink: " + relative)
        data = source.read_bytes()
        total_bytes += len(data)
        entries.append({"path": relative, "size_bytes": len(data), "sha256": sha256_bytes(data)})

    output_dir.mkdir(parents=True, exist_ok=True)
    stem = "garh-jaisal-website-" + commit_sha[:12]
    archive_path = output_dir / (stem + ".zip")
    with zipfile.ZipFile(
        archive_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9
    ) as archive:
        for entry in entries:
            data = (root / entry["path"]).read_bytes()
            info = zipfile.ZipInfo(entry["path"], date_time=ZIP_TIMESTAMP)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.create_system = 3
            info.external_attr = (0o100644 & 0xFFFF) << 16
            archive.writestr(
                info, data, compress_type=zipfile.ZIP_DEFLATED, compresslevel=9
            )

    archive_hash = sha256_bytes(archive_path.read_bytes())
    manifest = {
        "schema_version": 1,
        "repository": "tchouhanjsm/garh-jaisal-website",
        "commit": commit_sha,
        "package": archive_path.name,
        "package_sha256": archive_hash,
        "archive_is_deployment_only": True,
        "deployment_performed": False,
        "published_route_count": len(routes),
        "published_routes": [route or "/" for route, _ in routes],
        "file_count": len(entries),
        "total_uncompressed_bytes": total_bytes,
        "excluded_server_only_or_development_files": [
            PRIVATE_CONFIG_PATH, EXAMPLE_CONFIG_PATH, ".git/", ".github/",
            ".agents/", ".claude/", "docs/", "scripts/", "tests/",
            "node_modules/", "artifacts/",
        ],
        "files": entries,
    }
    manifest_path = output_dir / (stem + ".manifest.json")
    manifest_path.write_text(
        json.dumps(manifest, indent=2, sort_keys=True) + "\n", encoding="utf-8"
    )
    checksum_path = output_dir / (stem + ".sha256")
    checksum_path.write_text(
        archive_hash + "  " + archive_path.name + "\n", encoding="utf-8"
    )
    return {
        "status": "PASS", "commit": commit_sha, "package": str(archive_path),
        "manifest": str(manifest_path), "checksum": str(checksum_path),
        "package_sha256": archive_hash, "file_count": len(entries),
        "published_route_count": len(routes), "total_uncompressed_bytes": total_bytes,
    }


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--check", action="store_true",
        help="Validate the tracked deployment file set without creating a ZIP.",
    )
    parser.add_argument(
        "--output-dir", type=Path,
        help="Create a cPanel ZIP package and sidecar manifest/checksum (main branch only).",
    )
    arguments = parser.parse_args()
    if arguments.check == (arguments.output_dir is not None):
        parser.error("choose exactly one of --check or --output-dir")

    try:
        if arguments.check:
            tracked = get_tracked_paths(ROOT)
            selected = select_runtime_paths(tracked)
            sitemap = (ROOT / "sitemap.xml").read_text(encoding="utf-8")
            routes = validate_package_paths(selected, sitemap)
            result = {
                "status": "PASS", "mode": "check-only", "file_count": len(selected),
                "published_route_count": len(routes),
                "total_uncompressed_bytes": sum((ROOT / path).stat().st_size for path in selected),
                "local_config_tracked": False, "development_directories_excluded": True,
            }
        else:
            output_dir = arguments.output_dir
            if not output_dir.is_absolute():
                output_dir = ROOT / output_dir
            result = build_deployment_package(ROOT, output_dir)
        print(json.dumps(result, indent=2, sort_keys=True))
        return 0
    except (OSError, PackageError, subprocess.SubprocessError, UnicodeError) as error:
        print("DEPLOYMENT PACKAGE ERROR: " + str(error), file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
