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
RUNTIME_DIRECTORIES = {"content", "css", "img", "include", "js"}
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
NON_RUNTIME_ROOT_FILES = {"package.json", "package-lock.json", "playwright.config.js"}
EXPLICIT_DEPLOY_EXCLUDES = {
    "img/tent1.jpg",              # Large master used by documentation only.
    "restaurant.css",             # Stale root copy; live site uses css/restaurant.css.
    "restaurant.js",              # Stale root copy; live site uses js/restaurant.js.
    "thanx.png",                  # Unreferenced legacy asset.
    "css/owl.video.play.html",    # Stray artifact, not a stylesheet.
}
CANONICAL_HOST = "www.hotelgarhjaisal.com"
RELEASE_MARKER_PATH = "release-info.txt"
RELEASE_MARKER_PREFIX = "GARH_JAISAL_RELEASE_SHA="
FULL_COMMIT_SHA = re.compile(r"^[0-9a-f]{40}$", re.IGNORECASE)
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
    if path == RELEASE_MARKER_PATH:
        raise PackageError(RELEASE_MARKER_PATH + " is generated during package creation and must not be tracked.")
    if path == EXAMPLE_CONFIG_PATH or path in NON_RUNTIME_ROOT_FILES or path in EXPLICIT_DEPLOY_EXCLUDES:
        return False
    if path in ROOT_RUNTIME_FILES:
        return True
    if len(pure.parts) == 1 and pure.suffix.lower() in ROOT_RUNTIME_SUFFIXES:
        return True
    return bool(pure.parts and pure.parts[0] in RUNTIME_DIRECTORIES and len(pure.parts) > 1)


def select_runtime_paths(tracked_paths: list[str]) -> list[str]:
    return sorted(path for path in set(tracked_paths) if is_runtime_path(path))


def get_generated_responsive_paths(root: Path = ROOT) -> list[str]:
    """Select manifest-approved responsive variants; reject unlisted files and symlinks."""
    manifest_path = root / "scripts" / "responsive-image-assets-manifest.json"
    output_root = root / "img" / "responsive"
    if not manifest_path.is_file() or manifest_path.is_symlink():
        raise PackageError("Responsive image asset manifest is missing.")
    if not output_root.exists():
        return []
    if output_root.is_symlink() or not output_root.is_dir():
        raise PackageError("Generated responsive image directory is missing or is a symlink.")

    try:
        manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
        widths = manifest["target_widths"]
        sources = manifest["sources"]
    except (OSError, KeyError, TypeError, json.JSONDecodeError) as error:
        raise PackageError("Responsive image manifest is invalid: " + str(error)) from error
    if not isinstance(widths, list) or not widths or not isinstance(sources, list) or not sources:
        raise PackageError("Responsive image manifest must define sources and target widths.")

    source_target_widths = manifest.get("source_target_widths", {})
    if not isinstance(source_target_widths, dict):
        raise PackageError("Responsive image source-specific widths must be a source-to-width-list object.")
    for source, additional_widths in source_target_widths.items():
        if source not in sources:
            raise PackageError("Responsive image source-specific widths target an unlisted source: " + str(source))
        if (
            not isinstance(additional_widths, list)
            or not additional_widths
            or any(isinstance(width, bool) or not isinstance(width, int) or width < 1 for width in additional_widths)
            or additional_widths != sorted(set(additional_widths))
        ):
            raise PackageError("Responsive image source-specific widths must be positive, unique and sorted: " + str(source))

    allowed: set[str] = set()
    for source in sources:
        if not isinstance(source, str):
            raise PackageError("Responsive image source paths must be strings.")
        pure = PurePosixPath(source)
        if (
            pure.is_absolute() or len(pure.parts) < 2 or pure.parts[0] != "img"
            or ".." in pure.parts or "\\" in source
        ):
            raise PackageError("Unsafe responsive image source path: " + source)
        relative = pure.relative_to("img")
        candidate_widths = sorted(set(widths) | set(source_target_widths.get(source, [])))
        for width in candidate_widths:
            if not isinstance(width, int) or isinstance(width, bool) or width < 1:
                raise PackageError("Responsive image widths must be positive integers.")
            generated = PurePosixPath(
                "img", "responsive", relative.parent.as_posix(), f"{relative.stem}-{width}.webp"
            )
            allowed.add(generated.as_posix())

    variant_max_bytes = manifest.get("variant_max_bytes", {})
    if not isinstance(variant_max_bytes, dict):
        raise PackageError("Responsive image byte budgets must be a candidate-path-to-byte-limit object.")
    for relative, maximum in variant_max_bytes.items():
        if not isinstance(relative, str) or relative not in allowed:
            raise PackageError("Responsive image byte budget targets an unlisted candidate: " + str(relative))
        if isinstance(maximum, bool) or not isinstance(maximum, int) or maximum < 1:
            raise PackageError("Responsive image byte budget must be a positive integer: " + relative)

    actual: list[str] = []
    actual_bytes: dict[str, int] = {}
    for item in output_root.rglob("*"):
        if item.is_symlink():
            raise PackageError("Symlinks are not allowed in generated responsive images: " + item.relative_to(root).as_posix())
        if item.is_file():
            relative = item.relative_to(root).as_posix()
            if relative not in allowed:
                raise PackageError("Unexpected generated responsive image path: " + relative)
            size_bytes = item.stat().st_size
            if item.suffix.lower() != ".webp" or size_bytes <= 0:
                raise PackageError("Generated responsive image is empty or not WebP: " + relative)
            actual.append(relative)
            actual_bytes[relative] = size_bytes

    for relative, maximum in sorted(variant_max_bytes.items()):
        if relative not in actual_bytes:
            raise PackageError("Budgeted responsive image candidate is missing: " + relative)
        if actual_bytes[relative] > maximum:
            raise PackageError(
                "Responsive image exceeds its manifest byte budget: "
                + f"{relative} is {actual_bytes[relative]:,} bytes; maximum {maximum:,} bytes."
            )
    return sorted(actual)


def get_responsive_variant_budget_report(
    generated_paths: list[str], root: Path = ROOT
) -> list[dict]:
    """Return exact byte-budget evidence for every protected responsive candidate."""
    manifest_path = root / "scripts" / "responsive-image-assets-manifest.json"
    if not manifest_path.is_file() or manifest_path.is_symlink():
        raise PackageError("Responsive image asset manifest is missing or unsafe.")
    try:
        manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    except (OSError, TypeError, json.JSONDecodeError) as error:
        raise PackageError("Responsive image manifest is invalid: " + str(error)) from error
    budgets = manifest.get("variant_max_bytes", {})
    if not isinstance(budgets, dict):
        raise PackageError("Responsive image byte budgets must be a candidate-path-to-byte-limit object.")

    available = set(generated_paths)
    records = []
    for relative, maximum in sorted(budgets.items()):
        if not isinstance(relative, str) or relative not in available:
            raise PackageError("Budgeted responsive image candidate is missing: " + str(relative))
        if isinstance(maximum, bool) or not isinstance(maximum, int) or maximum < 1:
            raise PackageError("Responsive image byte budget must be a positive integer: " + relative)
        candidate = root / relative
        if candidate.is_symlink() or not candidate.is_file():
            raise PackageError("Budgeted responsive image is missing or unsafe: " + relative)
        actual_bytes = candidate.stat().st_size
        if actual_bytes <= 0:
            raise PackageError("Budgeted responsive image is empty: " + relative)
        if actual_bytes > maximum:
            raise PackageError(
                "Responsive image exceeds its manifest byte budget: "
                + f"{relative} is {actual_bytes:,} bytes; maximum {maximum:,} bytes."
            )
        records.append({
            "path": relative,
            "max_bytes": maximum,
            "actual_bytes": actual_bytes,
            "headroom_bytes": maximum - actual_bytes,
            "status": "pass",
        })
    return records


def validate_responsive_fallback_sources(
    selected_paths: list[str], root: Path = ROOT
) -> list[str]:
    """Require every manifest original to remain packageable as a safe fallback."""
    manifest_path = root / "scripts" / "responsive-image-assets-manifest.json"
    if not manifest_path.is_file() or manifest_path.is_symlink():
        raise PackageError("Responsive image asset manifest is missing or unsafe.")
    try:
        sources = json.loads(manifest_path.read_text(encoding="utf-8"))["sources"]
    except (OSError, KeyError, TypeError, json.JSONDecodeError) as error:
        raise PackageError("Responsive image manifest is invalid: " + str(error)) from error
    if not isinstance(sources, list) or not sources or any(not isinstance(item, str) for item in sources):
        raise PackageError("Responsive image manifest must define fallback source paths.")
    if len(sources) != len(set(sources)):
        raise PackageError("Responsive image fallback source paths must be unique.")

    selected = set(selected_paths)
    verified: list[str] = []
    missing: list[str] = []
    root_resolved = root.resolve()
    image_root = root / "img"
    if image_root.is_symlink() or not image_root.is_dir():
        raise PackageError("Original image fallback directory is missing or is a symlink.")
    for source in sources:
        pure = PurePosixPath(source)
        if pure.is_absolute() or len(pure.parts) < 2 or pure.parts[0] != "img" or ".." in pure.parts or "\\" in source:
            raise PackageError("Unsafe responsive image fallback source path: " + source)
        candidate = root.joinpath(*pure.parts)
        cursor = root_resolved
        for part in pure.parts:
            cursor = cursor / part
            if cursor.is_symlink():
                raise PackageError("Responsive image fallback must not use symlinks: " + source)
        if not candidate.is_file() or candidate.stat().st_size <= 0:
            raise PackageError("Responsive image fallback source is missing or empty: " + source)
        if not candidate.resolve().is_relative_to(image_root.resolve()):
            raise PackageError("Responsive image fallback escapes the approved img directory: " + source)
        verified.append(source)
        if source not in selected:
            missing.append(source)
    if missing:
        raise PackageError("Responsive image fallback sources are missing from the runtime package: " + ", ".join(sorted(missing)))
    return sorted(verified)


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
        "tests/", "node_modules/", "artifacts/", *sorted(EXPLICIT_DEPLOY_EXCLUDES), *sorted(EXPLICIT_DEPLOY_EXCLUDES),
    )
    forbidden_paths = sorted(
        path for path in selected
        if path.startswith(forbidden_prefixes)
        or path in {PRIVATE_CONFIG_PATH, EXAMPLE_CONFIG_PATH, "package.json", "package-lock.json", "playwright.config.js"}
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


def build_release_marker(commit_sha: str) -> bytes:
    """Build public, non-secret provenance for the exact packaged Git commit."""
    if not isinstance(commit_sha, str) or not FULL_COMMIT_SHA.fullmatch(commit_sha):
        raise PackageError("Release marker requires a full 40-character Git commit SHA.")
    return (RELEASE_MARKER_PREFIX + commit_sha.lower() + "\n").encode("ascii")


def build_deployment_package(root: Path, output_dir: Path) -> dict:
    ensure_tracked_worktree_clean(root)
    generated_responsive_paths = get_generated_responsive_paths(root)
    if not generated_responsive_paths:
        raise PackageError("No generated responsive WebP assets found; run scripts/build-responsive-image-assets.py first.")
    selected = sorted(set(select_runtime_paths(get_tracked_paths(root)) + generated_responsive_paths))
    responsive_fallback_sources = validate_responsive_fallback_sources(selected, root)
    responsive_variant_budgets = get_responsive_variant_budget_report(generated_responsive_paths, root)
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

    release_marker_data = build_release_marker(commit_sha)
    selected = sorted(set(selected + [RELEASE_MARKER_PATH]))
    routes = validate_package_paths(selected, sitemap_path.read_text(encoding="utf-8"))

    entries = []
    total_bytes = 0
    for relative in selected:
        if relative == RELEASE_MARKER_PATH:
            data = release_marker_data
        else:
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
            data = release_marker_data if entry["path"] == RELEASE_MARKER_PATH else (root / entry["path"]).read_bytes()
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
        "generated_responsive_image_count": len(generated_responsive_paths),
        "responsive_fallback_source_count": len(responsive_fallback_sources),
        "responsive_variant_budget_count": len(responsive_variant_budgets),
        "responsive_variant_budgets": responsive_variant_budgets,
        "release_marker": {
            "path": RELEASE_MARKER_PATH,
            "format": RELEASE_MARKER_PREFIX + "<40-character-commit-sha>",
            "sha256": sha256_bytes(release_marker_data),
        },
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
            generated_responsive_paths = get_generated_responsive_paths(ROOT)
            if not generated_responsive_paths:
                raise PackageError("No generated responsive WebP assets found; run scripts/build-responsive-image-assets.py first.")
            selected = sorted(set(select_runtime_paths(tracked) + generated_responsive_paths))
            responsive_fallback_sources = validate_responsive_fallback_sources(selected, ROOT)
            responsive_variant_budgets = get_responsive_variant_budget_report(generated_responsive_paths, ROOT)
            sitemap = (ROOT / "sitemap.xml").read_text(encoding="utf-8")
            routes = validate_package_paths(selected, sitemap)
            result = {
                "status": "PASS", "mode": "check-only", "file_count": len(selected),
                "published_route_count": len(routes),
                "generated_responsive_image_count": len(generated_responsive_paths),
                "responsive_fallback_source_count": len(responsive_fallback_sources),
                "responsive_variant_budget_count": len(responsive_variant_budgets),
                "responsive_variant_budgets": responsive_variant_budgets,
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
