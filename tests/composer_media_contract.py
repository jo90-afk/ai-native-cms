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
        fail(f"missing M-005 file: {path}")
    return file.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def main() -> None:
    composer = text("api/composer.php")
    store = text("api/composition-store.php")
    composer_api = text("api/cms-composer.php")
    media = text("api/media.php")
    media_api = text("api/cms-media.php")
    page_api = text("api/cms-pages.php")
    sync = text("api/content-sync.php")
    rebuild = text("api/content-rebuild.php")
    composer_js = text("cms/composer.js")
    media_js = text("cms/media.js")

    require(composer, [
        "composerTemplateVariables",
        "'type'=>'richtext'",
        "'type'=>'media'",
        "'type'=>'link'",
        "cmsSanitizeRichHtml",
        "composerSafeHref",
        "composerRekeyEditableIds",
        "data-composer-instance",
        "cmsConfiguredPages($root)",
    ], "typed template core")

    require(store, [
        "page_compositions",
        "compositionHashValue",
        "expectedHash",
        "contentAuthorityBackupPage",
        "compositionSyncCanonicalBlocks",
        "contentAuthorityOverlayBlocks",
        "Page is not CMS-managed",
        "A composed public page must contain exactly one H1 heading",
        "source_ref",
        "'composition'",
    ], "canonical composition store")

    require(composer_api, [
        "requireCmsAuth(true)",
        "requireSameOrigin(true)",
        "requireCmsCsrf()",
        "enforceRateLimit('cms-composer'",
        "compositionSave",
        "composerRefreshTemplates",
        "mediaRefreshLibrary",
    ], "composer API guard")

    require(media, [
        "mediaAllowedPath",
        "pathInside",
        "is_uploaded_file",
        "finfo(FILEINFO_MIME_TYPE)",
        "getimagesize",
        "move_uploaded_file",
        "image/jpeg",
        "image/png",
        "image/webp",
        "image/gif",
    ], "media safety")
    if "image/svg+xml'=>'svg'" in media:
        fail("SVG must not be accepted through the upload path")

    require(media_api, [
        "requireCmsAuth(true)",
        "requireSameOrigin(true)",
        "requireCmsCsrf()",
        "enforceRateLimit('cms-media'",
        "multipart/form-data",
        "mediaUpload",
    ], "media API guard")

    require(sync, ["if(compositionExists($path))continue;"], "composition-aware repository reconciliation")
    require(page_api, ["projectCmsPage($root,$path)", "'composed'=>compositionExists($path)"], "composition-aware page editing")

    page_pos = rebuild.find("contentAuthorityProjectPages($root)")
    composition_pos = rebuild.find("compositionProjectAll($root)")
    publish_pos = rebuild.find("projectPublishedPosts($root)")
    if min(page_pos, composition_pos, publish_pos) < 0 or not page_pos < composition_pos < publish_pos:
        fail("rebuild must project base pages -> compositions -> publishing")

    for path, source in [("cms/composer.js", composer_js), ("cms/media.js", media_js)]:
        if "innerHTML" in source:
            fail(f"{path} reintroduced innerHTML")
        if "http://" in source or "https://" in source:
            fail(f"{path} contains a third-party/network runtime dependency")

    if "html:" in composer_js or "structuralHtml" in composer_js:
        fail("composer browser payload appears to submit structural HTML")
    require(composer_js, ["templateKey", "instanceId", "values", "expectedHash"], "composer browser payload")

    print("PASS: composer and media contract")


if __name__ == "__main__":
    main()
