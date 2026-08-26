#!/usr/bin/env python3
from __future__ import annotations

import json
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[1]

BANNED_TEXT = {
    "legacy environment prefix": "JUDE_",
    "personal domain": "judeoneill.com",
    "private account/repository owner": "jo90-afk",
    "personal display name": "Jude O",
}

TEXT_SUFFIXES = {
    ".php", ".py", ".js", ".mjs", ".json", ".md", ".sql", ".yml", ".yaml",
    ".css", ".html", ".txt", ".ini", ".sh",
}

# Historical/source-analysis material is deliberately not shipped in this repository.
IGNORED_PARTS = {".git", ".lattice"}


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def iter_text_files():
    for path in ROOT.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in TEXT_SUFFIXES:
            continue
        if any(part in IGNORED_PARTS for part in path.parts):
            continue
        yield path


def check_sanitization() -> None:
    hits: list[str] = []
    for path in iter_text_files():
        text = path.read_text(encoding="utf-8", errors="replace")
        for label, needle in BANNED_TEXT.items():
            if needle.lower() in text.lower():
                hits.append(f"{path.relative_to(ROOT)}: {label} ({needle})")
    if hits:
        fail("public-release identifiers found:\n  " + "\n  ".join(hits))


def check_required_files() -> None:
    required = [
        "README.md",
        "api/database.php",
        "api/runtime.php",
        "config/site.example.php",
        "database/schema.sql",
        "docs/ARCHITECTURE.md",
        "docs/UPSTREAMING.md",
    ]
    missing = [path for path in required if not (ROOT / path).is_file()]
    if missing:
        fail("required foundation files are missing: " + ", ".join(missing))


def check_environment_contract() -> None:
    runtime = (ROOT / "api/runtime.php").read_text(encoding="utf-8")
    database = (ROOT / "api/database.php").read_text(encoding="utf-8")
    combined = runtime + "\n" + database
    required = [
        "AINCMS_PUBLIC_ORIGIN",
        "AINCMS_CMS_ENABLED",
        "AINCMS_CMS_USER",
        "AINCMS_CMS_PASSWORD_HASH",
        "AINCMS_RATE_LIMIT_SECRET",
        "AINCMS_DB_HOST",
        "AINCMS_DB_NAME",
        "AINCMS_DB_USER",
        "AINCMS_DB_PASSWORD",
    ]
    missing = [key for key in required if key not in combined]
    if missing:
        fail("required generic environment keys are missing: " + ", ".join(missing))


def check_schema_contract() -> None:
    schema = (ROOT / "database/schema.sql").read_text(encoding="utf-8")
    required_tables = [
        "cms_users",
        "cms_activity",
        "page_blocks",
        "page_block_templates",
        "page_compositions",
        "site_branding",
        "media_library",
        "site_navigation",
        "content_documents",
        "content_change_log",
        "content_update_sets",
        "page_revisions",
        "posts",
        "post_revisions",
        "seo_overrides",
    ]
    missing = [name for name in required_tables if not re.search(rf"CREATE TABLE IF NOT EXISTS\s+{re.escape(name)}\b", schema)]
    if missing:
        fail("canonical schema tables are missing: " + ", ".join(missing))

    if "source_sha256" not in schema or "content_sha256" not in schema:
        fail("schema no longer carries canonical/source lineage hashes")


def check_lattice_capsule() -> None:
    caps = ROOT / ".lattice/project/capabilities.json"
    if not caps.is_file():
        fail("Lattice capabilities are missing")
    data = json.loads(caps.read_text(encoding="utf-8"))
    if data.get("project_id") != "ai-native-cms-001":
        fail("unexpected Lattice project identity")
    platforms = set(data.get("application_platforms", []))
    if not {"web", "php", "mysql"}.issubset(platforms):
        fail("Lattice capabilities do not describe the application platform")


def main() -> None:
    check_required_files()
    check_sanitization()
    check_environment_contract()
    check_schema_contract()
    check_lattice_capsule()
    print("PASS: public-release foundation contract")


if __name__ == "__main__":
    main()
