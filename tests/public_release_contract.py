#!/usr/bin/env python3
from __future__ import annotations

import json
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]

# Build forbidden markers without embedding them contiguously in this test file.
# That keeps the sanitization scan self-applicable instead of exempting its own source.
BANNED_TEXT = {
    "legacy environment prefix": "JU" + "DE_",
    "personal domain": "jude" + "oneill.com",
    "private account/repository owner": "jo90" + "-afk",
    "personal display name": "Jude" + " O",
}

TEXT_SUFFIXES = {
    ".php", ".py", ".js", ".mjs", ".json", ".md", ".sql", ".yml", ".yaml",
    ".css", ".html", ".txt", ".ini", ".sh", ".example", "",
}

# A public repository exposes governance files too, so only Git internals are exempt.
IGNORED_PARTS = {".git"}


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
        "LICENSE",
        "LICENSE-APACHE-2.0.txt",
        "NOTICE",
        "api/database.php",
        "api/runtime.php",
        "config/site.example.php",
        "database/schema.sql",
        "docs/ARCHITECTURE.md",
        "docs/UPSTREAMING.md",
        ".github/workflows/publish-release.yml",
        "release/RELEASE-NOTES-0.1.0-rc.3.md",
    ]
    missing = [path for path in required if not (ROOT / path).is_file()]
    if missing:
        fail("required public-release files are missing: " + ", ".join(missing))


def check_distribution_contract() -> None:
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    metadata = json.loads((ROOT / "release/release.json").read_text(encoding="utf-8"))
    distribution = metadata.get("distribution", {})
    if version != "0.1.0-rc.3" or metadata.get("version") != version:
        fail("unexpected public release version")
    if metadata.get("channel") != "public-release-candidate":
        fail("release metadata is not on the public release-candidate channel")
    if distribution.get("public") is not True:
        fail("public distribution has not been authorized in release metadata")
    if distribution.get("tagRequired") is not True or distribution.get("tag") != "v0.1.0-rc.3":
        fail("public release tag contract is wrong")


def check_license_contract() -> None:
    license_text = (ROOT / "LICENSE").read_text(encoding="utf-8")
    apache = (ROOT / "LICENSE-APACHE-2.0.txt").read_text(encoding="utf-8")
    notice = (ROOT / "NOTICE").read_text(encoding="utf-8")
    for token in ["Commons Clause", "Apache License", "right to Sell the Software", "source-available"]:
        if token.lower() not in license_text.lower():
            fail("selected license is missing: " + token)
    if "Version 2.0, January 2004" not in apache or "TERMS AND CONDITIONS FOR USE, REPRODUCTION, AND DISTRIBUTION" not in apache:
        fail("Apache 2.0 base license is incomplete")
    if "AI Native CMS" not in notice or "Commons Clause" not in notice:
        fail("NOTICE does not preserve project attribution/license condition")


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
    check_distribution_contract()
    check_license_contract()
    check_environment_contract()
    check_schema_contract()
    check_lattice_capsule()
    print("PASS: public-release repository contract")


if __name__ == "__main__":
    main()
