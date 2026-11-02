"""Validate CMS gallery records and expose their image sources to build/package tooling."""

from __future__ import annotations

import json
import re
from pathlib import Path, PurePosixPath


ALLOWED_CATEGORIES = {"hotel", "rooms", "desert", "restaurant"}
ALLOWED_STATUSES = {"live", "draft", "hidden"}
ALLOWED_CROPS = {"arch", "landscape", "portrait", "standard"}
ID_PATTERN = re.compile(r"^[a-z0-9]+(?:-[a-z0-9]+)*$")
SOURCE_PATTERN = re.compile(r"^img/[A-Za-z0-9._/-]+\.(?:avif|jpe?g|png|webp)$", re.IGNORECASE)


def gallery_record_image_sources(root: Path) -> list[str]:
    """Return safe image paths from all Live, Draft and Hidden gallery entries.

    Draft and Hidden assets are also returned: deployments retain them, and
    responsive derivatives are ready if the owner later publishes the entry.
    Legacy Content Studio records without an explicit order remain valid.
    """
    root = Path(root)
    content_root = root / "content" / "gallery"
    if content_root.is_symlink():
        raise ValueError("CMS gallery content directory must not be a symlink.")
    if not content_root.exists():
        return []
    if not content_root.is_dir():
        raise ValueError("CMS gallery content path must be a directory.")

    image_root = root / "img"
    if image_root.is_symlink() or not image_root.is_dir():
        raise ValueError("CMS gallery image root is missing or unsafe.")
    image_root_real = image_root.resolve()
    sources: list[str] = []
    seen_ids: set[str] = set()
    seen_sources: set[str] = set()

    for record_path in sorted(content_root.glob("*.json")):
        if record_path.is_symlink() or not record_path.is_file():
            raise ValueError("CMS gallery record must be a regular file: " + record_path.name)
        try:
            record = json.loads(record_path.read_text(encoding="utf-8"))
        except (OSError, UnicodeDecodeError, json.JSONDecodeError) as error:
            raise ValueError("CMS gallery record is invalid JSON: " + record_path.name) from error

        record_id = record_path.stem
        if (
            not isinstance(record, dict)
            or not ID_PATTERN.fullmatch(record_id)
            or record.get("id") != record_id
            or record_id in seen_ids
            or record.get("category") not in ALLOWED_CATEGORIES
            or record.get("status") not in ALLOWED_STATUSES
            or (
                "order" in record
                and (
                    isinstance(record.get("order"), bool)
                    or not isinstance(record.get("order"), int)
                    or record["order"] < 1
                )
            )
        ):
            raise ValueError("CMS gallery identity, category, status or order is invalid: " + record_path.name)

        image = record.get("image")
        if not isinstance(image, dict):
            raise ValueError("CMS gallery record has no image object: " + record_id)
        source, alt, crop = image.get("src"), image.get("alt"), image.get("crop")
        if (
            not isinstance(source, str)
            or not SOURCE_PATTERN.fullmatch(source)
            or ".." in source
            or chr(92) in source
            or not isinstance(alt, str)
            or not alt.strip()
            or crop not in ALLOWED_CROPS
        ):
            raise ValueError("CMS gallery image metadata is invalid: " + record_id)

        pure = PurePosixPath(source)
        if pure.is_absolute() or len(pure.parts) < 2 or pure.parts[0] != "img" or ".." in pure.parts:
            raise ValueError("CMS gallery image path is unsafe: " + source)
        cursor = root.resolve()
        for part in pure.parts:
            cursor = cursor / part
            if cursor.is_symlink():
                raise ValueError("CMS gallery image path contains a symlink: " + source)

        candidate = root.joinpath(*pure.parts)
        if not candidate.is_file() or candidate.stat().st_size <= 0:
            raise ValueError("CMS gallery image is missing or empty: " + source)
        if not candidate.resolve().is_relative_to(image_root_real):
            raise ValueError("CMS gallery image escapes img/: " + source)
        if source in seen_sources:
            raise ValueError("CMS gallery image is referenced more than once: " + source)

        seen_ids.add(record_id)
        seen_sources.add(source)
        sources.append(source)

    return sources
