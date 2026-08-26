#!/usr/bin/env python3
from __future__ import annotations

import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import subprocess
import zipfile

ROOT = Path(__file__).resolve().parents[1]
TEXT_SUFFIXES = {
    ".php", ".py", ".js", ".mjs", ".json", ".md", ".sql", ".yml", ".yaml",
    ".css", ".html", ".txt", ".ini", ".sh", ".example", "",
}
TOP_LEVEL_FILES = {
    "README.md", "SECURITY.md", "VERSION", "LICENSE", "LICENSE-APACHE-2.0.txt", "NOTICE",
    "__redirect.php", "__redirect-map.php",
}
INCLUDE_ROOTS = {"api", "cms", "config", "database", "docs", "adapters"}
EXCLUDED_PARTS = {".git", ".github", ".lattice", "tests", "tools", "dist", "runtime", "uploads", "__pycache__"}
EXCLUDED_FILES = {"config/site.php"}
ALLOWED_INI = {"database/private-config.example.ini"}
BANNED_TEXT = {
    "legacy environment prefix": "JU" + "DE_",
    "personal domain": "jude" + "oneill.com",
    "private account/repository owner": "jo90" + "-afk",
    "personal display name": "Jude" + " O",
}
SECRET_PATTERNS = {
    "private key": re.compile(r"-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----"),
    "GitHub classic token": re.compile(r"\bgh[pousr]_[A-Za-z0-9]{20,}\b"),
    "GitHub fine-grained token": re.compile(r"\bgithub_pat_[A-Za-z0-9_]{20,}\b"),
    "AWS access key": re.compile(r"\bAKIA[0-9A-Z]{16}\b"),
}
FIXED_ZIP_TIME = (1980, 1, 1, 0, 0, 0)


def fail(message: str) -> None:
    raise RuntimeError(message)


def load_release_metadata() -> dict:
    path = ROOT / "release/release.json"
    if not path.is_file():
        fail("release/release.json is missing")
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        fail("release metadata must be an object")
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    if data.get("version") != version:
        fail("VERSION and release/release.json disagree")
    schema = (ROOT / "database/schema.sql").read_text(encoding="utf-8")
    match = re.search(
        r"INSERT\s+INTO\s+app_meta\s*\([^)]*schema_version[^)]*\)\s*VALUES\s*\(\s*1\s*,\s*(\d+)\s*\)",
        schema,
        re.IGNORECASE,
    )
    if not match:
        fail("schema version could not be derived from database/schema.sql")
    if int(data.get("schemaVersion", -1)) != int(match.group(1)):
        fail("release metadata schemaVersion does not match database/schema.sql")
    if data.get("channel") != "internal-release-candidate":
        fail("release metadata must remain an internal release candidate")
    distribution = data.get("distribution")
    if not isinstance(distribution, dict) or distribution.get("public") is not False:
        fail("release metadata crossed the public-distribution boundary")
    if distribution.get("licenseSelected") is not True:
        fail("release metadata must record the selected source-available license")
    license_meta = distribution.get("license")
    if not isinstance(license_meta, dict):
        fail("selected license metadata is missing")
    expected = {
        "base": "Apache-2.0",
        "condition": "Commons Clause License Condition v1.0",
        "sourceAvailable": True,
        "osiApproved": False,
        "attributionRequired": True,
    }
    for key, value in expected.items():
        if license_meta.get(key) != value:
            fail(f"release license metadata disagrees on {key}")
    return data


def candidate_files() -> list[Path]:
    files: list[Path] = []
    for name in sorted(TOP_LEVEL_FILES):
        path = ROOT / name
        if not path.is_file():
            fail(f"required release file is missing: {name}")
        files.append(path)
    release_meta = ROOT / "release/release.json"
    if not release_meta.is_file():
        fail("release/release.json is missing")
    files.append(release_meta)
    for root_name in sorted(INCLUDE_ROOTS):
        root = ROOT / root_name
        if not root.is_dir():
            fail(f"required release root is missing: {root_name}")
        for path in sorted(root.rglob("*")):
            if not path.is_file():
                continue
            relative = path.relative_to(ROOT).as_posix()
            if any(part in EXCLUDED_PARTS for part in path.relative_to(ROOT).parts):
                continue
            if relative in EXCLUDED_FILES:
                continue
            if path.suffix.lower() == ".ini" and relative not in ALLOWED_INI:
                continue
            files.append(path)
    unique = sorted({path.relative_to(ROOT).as_posix(): path for path in files}.values(), key=lambda p: p.relative_to(ROOT).as_posix())
    if not unique:
        fail("release candidate contains no files")
    return unique


def validate_candidate_path(path: Path) -> None:
    relative = path.relative_to(ROOT)
    rel = relative.as_posix()
    if path.is_symlink():
        fail(f"release candidate refuses symlinks: {rel}")
    if any(part in EXCLUDED_PARTS for part in relative.parts):
        fail(f"excluded path entered release candidate: {rel}")
    if rel in EXCLUDED_FILES:
        fail(f"adopter-local file entered release candidate: {rel}")
    if path.suffix.lower() == ".ini" and rel not in ALLOWED_INI:
        fail(f"non-example INI entered release candidate: {rel}")


def validate_candidate_content(path: Path, data: bytes) -> None:
    relative = path.relative_to(ROOT).as_posix()
    if path.suffix.lower() not in TEXT_SUFFIXES:
        return
    text = data.decode("utf-8", errors="replace")
    lowered = text.lower()
    for label, needle in BANNED_TEXT.items():
        if needle.lower() in lowered:
            fail(f"{relative}: forbidden {label}")
    for label, pattern in SECRET_PATTERNS.items():
        if pattern.search(text):
            fail(f"{relative}: possible {label} detected")


def source_revision(explicit: str | None) -> str:
    if explicit:
        return explicit.strip()
    env = os.environ.get("GITHUB_SHA", "").strip()
    if env:
        return env
    try:
        value = subprocess.check_output(["git", "rev-parse", "HEAD"], cwd=ROOT, text=True, stderr=subprocess.DEVNULL).strip()
        if value:
            return value
    except Exception:
        pass
    return "unknown"


def file_record(relative: str, data: bytes) -> dict:
    return {
        "path": relative,
        "bytes": len(data),
        "sha256": hashlib.sha256(data).hexdigest(),
    }


def manifest(metadata: dict, revision: str, records: list[dict]) -> dict:
    return {
        "product": metadata["product"],
        "version": metadata["version"],
        "channel": metadata["channel"],
        "sourceRevision": revision,
        "schemaVersion": metadata["schemaVersion"],
        "runtime": metadata["runtime"],
        "distribution": metadata["distribution"],
        "packageRoot": metadata["packageRoot"],
        "validation": {
            "candidatePolicy": "release/release.json + tools/build_release.py",
            "publicReleaseBoundaryPreserved": True,
        },
        "files": records,
    }


def zip_entry(name: str, data: bytes) -> tuple[zipfile.ZipInfo, bytes]:
    info = zipfile.ZipInfo(name, FIXED_ZIP_TIME)
    info.compress_type = zipfile.ZIP_DEFLATED
    info.create_system = 3
    info.external_attr = (0o100644 & 0xFFFF) << 16
    info.flag_bits |= 0x800
    return info, data


def build(output_dir: Path, explicit_source_ref: str | None = None) -> dict:
    metadata = load_release_metadata()
    revision = source_revision(explicit_source_ref)
    files = candidate_files()
    records: list[dict] = []
    payloads: list[tuple[str, bytes]] = []
    for path in files:
        validate_candidate_path(path)
        data = path.read_bytes()
        validate_candidate_content(path, data)
        relative = path.relative_to(ROOT).as_posix()
        records.append(file_record(relative, data))
        payloads.append((relative, data))
    records.sort(key=lambda row: row["path"])
    payloads.sort(key=lambda row: row[0])
    manifest_data = manifest(metadata, revision, records)
    manifest_bytes = (json.dumps(manifest_data, indent=2, sort_keys=True, ensure_ascii=False) + "\n").encode("utf-8")

    output_dir.mkdir(parents=True, exist_ok=True)
    base = f"{metadata['product']}-{metadata['version']}"
    zip_path = output_dir / f"{base}.zip"
    manifest_path = output_dir / f"{base}.manifest.json"
    checksum_path = output_dir / f"{base}.sha256"
    root_name = str(metadata["packageRoot"]).strip("/")

    with zipfile.ZipFile(zip_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for relative, data in payloads:
            info, payload = zip_entry(f"{root_name}/{relative}", data)
            archive.writestr(info, payload, compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)
        info, payload = zip_entry(f"{root_name}/RELEASE-MANIFEST.json", manifest_bytes)
        archive.writestr(info, payload, compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)

    manifest_path.write_bytes(manifest_bytes)
    digest = hashlib.sha256(zip_path.read_bytes()).hexdigest()
    checksum_path.write_text(f"{digest}  {zip_path.name}\n", encoding="utf-8")
    return {
        "zip": str(zip_path),
        "manifest": str(manifest_path),
        "checksum": str(checksum_path),
        "sha256": digest,
        "files": len(records),
        "sourceRevision": revision,
        "version": metadata["version"],
    }


def main() -> None:
    parser = argparse.ArgumentParser(description="Build a deterministic internal AI Native CMS release candidate.")
    parser.add_argument("--output-dir", default="dist", help="Directory for candidate artifacts (default: dist)")
    parser.add_argument("--source-ref", default=None, help="Source revision recorded in the manifest")
    args = parser.parse_args()
    result = build((ROOT / args.output_dir).resolve(), args.source_ref)
    print(json.dumps(result, indent=2, sort_keys=True))


if __name__ == "__main__":
    main()
