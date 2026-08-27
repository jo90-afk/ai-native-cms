#!/usr/bin/env python3
from __future__ import annotations

import json
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]

# Build forbidden markers without embedding them contiguously in this test file.
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
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    required = [
        "README.md", "LICENSE", "LICENSE-APACHE-2.0.txt", "NOTICE", "AGENTS.md",
        "api/database.php", "api/runtime.php", "api/block-presets.php", "api/composition-store.php",
        "api/page-routes.php", "api/page-projection.php", "api/discovery-projection.php", "api/llms-projection.php",
        "api/audience.php", "api/audience-subscribe.php", "api/mail-transport.php",
        "cms/composer.php", "cms/blocks.php", "cms/audience.php",
        "config/site.example.php", "database/schema.sql",
        "database/migrations/7-to-8.php", "database/migrations/8-to-9.php", "database/migrations/9-to-10.php",
        "docs/ARCHITECTURE.md", "docs/INSTALLATION.md", "docs/RELEASE.md", "docs/CPANEL-EMAIL.md", "docs/UPSTREAMING.md",
        ".github/workflows/publish-release.yml", f"release/RELEASE-NOTES-{version}.md",
    ]
    missing = [path for path in required if not (ROOT / path).is_file()]
    if missing:
        fail("required public-release files are missing: " + ", ".join(missing))


def check_distribution_contract() -> None:
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    metadata = json.loads((ROOT / "release/release.json").read_text(encoding="utf-8"))
    distribution = metadata.get("distribution", {})
    if version != "0.1.0-rc.4" or metadata.get("version") != version:
        fail("unexpected public release version")
    if metadata.get("channel") != "public-release-candidate" or metadata.get("schemaVersion") != 10:
        fail("release metadata is not the schema-v10 public release candidate")
    if distribution.get("public") is not True or distribution.get("licenseSelected") is not True:
        fail("public distribution/license state is incomplete")
    if distribution.get("tagRequired") is not True or distribution.get("tag") != f"v{version}":
        fail("public release tag contract is wrong")
    if metadata.get("packageRoot") != f"ai-native-cms-{version}":
        fail("release package root does not follow the current version")


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
    combined = "\n".join((ROOT / path).read_text(encoding="utf-8") for path in ["api/runtime.php", "api/database.php", "api/mail-transport.php"])
    required = [
        "AINCMS_PUBLIC_ORIGIN", "AINCMS_CMS_ENABLED", "AINCMS_CMS_USER", "AINCMS_CMS_PASSWORD_HASH",
        "AINCMS_RATE_LIMIT_SECRET", "AINCMS_DB_HOST", "AINCMS_DB_NAME", "AINCMS_DB_USER", "AINCMS_DB_PASSWORD",
        "AINCMS_MAIL_TRANSPORT", "AINCMS_MAIL_HOST", "AINCMS_MAIL_USERNAME", "AINCMS_MAIL_PASSWORD", "AINCMS_MAIL_FROM",
    ]
    missing = [key for key in required if key not in combined]
    if missing:
        fail("required generic environment keys are missing: " + ", ".join(missing))


def check_schema_contract() -> None:
    schema = (ROOT / "database/schema.sql").read_text(encoding="utf-8")
    if "schema v10" not in schema or "VALUES (1, 10)" not in schema:
        fail("fresh-install schema is not v10")
    required_tables = [
        "cms_users", "cms_activity", "page_blocks", "block_presets", "page_compositions", "site_branding",
        "media_library", "site_navigation", "content_documents", "content_change_log", "content_update_sets",
        "page_revisions", "posts", "post_revisions", "seo_overrides", "redirect_records",
        "audience_lists", "audience_subscriptions", "mail_outbox",
    ]
    missing = [name for name in required_tables if not re.search(rf"CREATE TABLE IF NOT EXISTS\s+{re.escape(name)}\b", schema)]
    if missing:
        fail("canonical schema tables are missing: " + ", ".join(missing))
    for retired in ["page_block_templates", "subscribers"]:
        if re.search(rf"CREATE TABLE IF NOT EXISTS\s+{retired}\b", schema):
            fail("retired authority remains active in fresh schema: " + retired)
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
    check_required_files(); check_sanitization(); check_distribution_contract(); check_license_contract(); check_environment_contract(); check_schema_contract(); check_lattice_capsule()
    print("PASS: rc.4 public-release repository contract")


if __name__ == "__main__":
    main()
