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



PAGE_MEDIA_SLOT_IDS = {
    "home": ["home-hero-mobile","home-hero-desktop","home-heritage-intro","home-rooftop-feature","home-desert-safari-feature","home-swiss-tent-feature","home-sleep-under-stars-feature","home-city-discovery-feature","home-gallery-city","home-gallery-courtyard","home-gallery-reception","home-gallery-rooftop","home-gallery-entrance"],
    "rooms": ["rooms-hero","room-sweet-purple-room-primary","room-rising-sun-room-primary","room-scarlet-room-primary","room-opium-room-primary","room-red-maharani-room-primary","room-green-room-primary","room-bluesky-room-primary"],
    "dining": ["dining-hero"],
    "experiences": ["experience-desert-safari-hero","experience-guided-jaisalmer-tour-hero","experience-sunset-camel-safari-hero","experience-sleep-under-the-stars-hero","experience-swiss-tent-safari-hero","experience-cultural-safari-hero","experience-explore-the-desert-hero","experience-half-day-jaisalmer-tour-hero","experience-full-day-jaisalmer-tour-hero","experience-longewala-tour-hero","experience-rajasthan-tours-hero"],
}


def site_media_slot_image_sources(root: Path) -> list[str]:
    """Return safe image paths referenced by the four fixed page-media JSON files."""
    root = Path(root)
    slots_root = root / "content" / "page-media"
    if slots_root.is_symlink():
        raise ValueError("CMS page image slot directory must not be a symlink.")
    # Isolated unit fixtures may omit CMS page-media files entirely.
    # Dedicated repository contracts require all four fixed files to exist.
    if not slots_root.exists():
        return []
    if not slots_root.is_dir():
        raise ValueError("CMS page image slot directory is unsafe.")
    image_root = root / "img"
    if image_root.is_symlink() or not image_root.is_dir():
        raise ValueError("CMS page image slot image root is missing or unsafe.")
    image_root_real = image_root.resolve()
    sources: set[str] = set()
    for area, allowed_ids in PAGE_MEDIA_SLOT_IDS.items():
        record_path = slots_root / (area + ".json")
        if record_path.is_symlink() or not record_path.is_file():
            raise ValueError("Required CMS page image slot file is missing or unsafe: " + record_path.name)
        try:
            records = json.loads(record_path.read_text(encoding="utf-8"))
        except (OSError, UnicodeDecodeError, json.JSONDecodeError) as error:
            raise ValueError("CMS page image slot JSON is invalid: " + record_path.name) from error
        if not isinstance(records, dict) or set(records) != set(allowed_ids):
            raise ValueError("CMS page image slot IDs do not match the fixed " + area + " allowlist.")
        for slot_id, image in records.items():
            if not isinstance(image, dict):
                raise ValueError("CMS page image slot metadata is invalid: " + slot_id)
            source, alt = image.get("src"), image.get("alt")
            caption = image.get("caption", "")
            if (
                not isinstance(source, str)
                or not SOURCE_PATTERN.fullmatch(source)
                or ".." in source
                or chr(92) in source
                or not isinstance(alt, str)
                or len(alt.strip()) < 15
                or len(alt) > 300
                or not isinstance(caption, str)
                or len(caption) > 180
            ):
                raise ValueError("CMS page image slot metadata is invalid: " + slot_id)
            pure = PurePosixPath(source)
            if pure.is_absolute() or len(pure.parts) < 2 or pure.parts[0] != "img" or ".." in pure.parts:
                raise ValueError("CMS page image slot path is unsafe: " + source)
            cursor = root.resolve()
            for part in pure.parts:
                cursor = cursor / part
                if cursor.is_symlink():
                    raise ValueError("CMS page image slot path contains a symlink: " + source)
            candidate = root.joinpath(*pure.parts)
            if not candidate.is_file() or candidate.stat().st_size <= 0:
                raise ValueError("CMS page image slot source is missing or empty: " + source)
            if not candidate.resolve().is_relative_to(image_root_real):
                raise ValueError("CMS page image slot source escapes img/: " + source)
            sources.add(source)
    return sorted(sources)


def cms_managed_image_sources(root: Path) -> list[str]:
    """Return Gallery and fixed page-slot images for responsive generation and package safety."""
    return sorted(set(gallery_record_image_sources(root)) | set(site_media_slot_image_sources(root)))
