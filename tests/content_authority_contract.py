#!/usr/bin/env python3
from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def text(path: str) -> str:
    file = ROOT / path
    if not file.is_file():
        fail(f"missing required content-authority file: {path}")
    return file.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def ordered(source: str, needles: list[str], label: str) -> None:
    positions = [source.find(item) for item in needles]
    if any(pos < 0 for pos in positions) or positions != sorted(positions):
        fail(f"{label} order is no longer preserved")


def main() -> None:
    core = text("api/content-core.php")
    authority = text("api/content-authority.php")
    sync = text("api/content-sync.php")
    pages = text("api/cms-pages.php")
    reconcile = text("database/reconcile.php")
    config = text("config/site.example.php")

    combined = core + authority + sync + pages + reconcile
    if "siteBuilder" in combined:
        fail("content authority slice depends on the later site-builder layer")

    require(authority, [
        "content_sha256",
        "source_sha256",
        "FOR UPDATE",
        "preserved_newer",
        "contentAuthorityOverlayBlocks",
        "contentAuthorityProjectPage",
        "page_revisions",
    ], "canonical authority")

    require(sync, [
        "hash_equals($canonical,$previousSource)",
        "content_update_sets",
        "standing",
        "contentSyncApplyUpdateSet",
        "preserved_newer",
        "source_sha256",
    ], "three-way reconciliation")

    require(pages, [
        "requireCmsAuth(true)",
        "requireSameOrigin(true)",
        "requireCmsCsrf()",
        "enforceRateLimit('cms-page-save'",
        "contentAuthorityStoreBlockChanges",
        "contentAuthorityProjectPage",
    ], "page editing endpoint")

    require(config, ["'documents' => [", "'editable_pages' => ["], "adopter configuration")

    rebuild_path = ROOT / "api/content-rebuild.php"
    if rebuild_path.is_file():
        rebuild = rebuild_path.read_text(encoding="utf-8")
        ordered(reconcile, [
            "contentSyncRepository($root,$sourceRef)",
            "contentSyncApplyUpdateDirectory($root,$sourceRef)",
            "contentRebuild($root)",
        ], "reconcile -> update sets -> rebuild")
        ordered(rebuild, [
            "contentAuthorityProjectConfiguredDocuments($root)",
            "contentAuthorityProjectPages($root)",
        ], "configured document -> page projection")
    else:
        ordered(reconcile, [
            "contentSyncRepository($root,$sourceRef)",
            "contentSyncApplyUpdateDirectory($root,$sourceRef)",
            "contentAuthorityProjectConfiguredDocuments($root)",
            "contentAuthorityProjectPages($root)",
        ], "reconcile -> update sets -> projection")

    require(core, [
        "cmsSafePublicFile",
        "cmsSanitizeRichHtml",
        "cmsReplaceEditableBlock",
        "['http','https','mailto','tel']",
        "data-cms-id",
    ], "content safety primitives")

    print("PASS: content authority contract")


if __name__ == "__main__":
    main()
