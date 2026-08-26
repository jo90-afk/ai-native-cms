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
        fail(f"missing publishing/SEO file: {path}")
    return file.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def ordered(source: str, needles: list[str], label: str) -> None:
    positions = [source.find(item) for item in needles]
    if any(pos < 0 for pos in positions) or positions != sorted(positions):
        fail(f"{label} order is not preserved")


def main() -> None:
    store = text("api/post-store.php")
    renderer = text("api/post-renderer.php")
    writing_api = text("api/cms-writing.php")
    seo = text("api/seo.php")
    seo_api = text("api/cms-seo.php")
    rebuild = text("api/content-rebuild.php")
    writing_ui = text("cms/writing.php")
    writing_js = text("cms/writing.js")
    seo_ui = text("cms/seo.php")
    seo_js = text("cms/seo.js")
    config = text("config/site.example.php")

    require(store, [
        "post_revisions",
        "FOR UPDATE",
        "expectedHash",
        "Post changed since it was opened",
        "restorePostRevision",
        "dbJsonEncode(postRevisionPayload",
    ], "post revision authority")

    require(renderer, [
        "postEscape($value)",
        "cmsSafeInlineHref",
        "renderPostMarkdown",
        "projectPublishedPosts",
        "rebuildPostIndex",
        "article_template",
    ], "static post projection")
    for forbidden in ["essayVisual", "publicPoems", "commonSchema", "linkedin.com"]:
        if forbidden in renderer:
            fail(f"site-specific renderer behavior leaked into public core: {forbidden}")

    require(writing_api, [
        "requireCmsAuth(true)",
        "requireSameOrigin(true)",
        "requireCmsCsrf()",
        "enforceRateLimit('cms-writing-save'",
        "savePostToDatabase",
        "restorePostRevision",
        "projectPost",
        "removePostProjection",
        "rebuildPostIndex",
    ], "guarded writing API")

    require(seo, [
        "seoCanonicalAllowed",
        "normalizedOrigin",
        "seo_overrides",
        "seoApplyToHtml",
        "seoProjectAll",
        "seoExpectedCanonical",
    ], "SEO authority")
    require(seo_api, [
        "requireCmsAuth(true)",
        "requireSameOrigin(true)",
        "requireCmsCsrf()",
        "enforceRateLimit('cms-seo-save'",
        "contentAuthorityBackupPage",
        "seoWriteOverride",
        "seoApplyToHtml",
    ], "guarded SEO API")

    # M-009 moved SEO and site-wide projectors behind one explicit finalization
    # boundary. Preserve M-004's substantive guarantee (published output exists
    # before SEO) without requiring the obsolete pre-finalizer hook ordering.
    if "function contentFinalizePublicProjections" not in rebuild or "function contentRebuild(" not in rebuild:
        fail("site-wide projection finalizer is missing")
    finalizer = rebuild.split("function contentFinalizePublicProjections", 1)[1].split("function contentRebuild(", 1)[0]
    rebuild_body = rebuild.split("function contentRebuild(", 1)[1]
    ordered(rebuild_body, [
        "contentAuthorityProjectPages($root)",
        "projectPublishedPosts($root)",
        "contentFinalizePublicProjections($root",
    ], "page -> publishing -> finalization rebuild")
    ordered(finalizer, [
        "contentRebuildRunHooks($hooks,'after_pages'",
        "seoProjectAll($root)",
        "contentRebuildRunHooks($hooks,'after_seo'",
    ], "after_pages -> SEO -> after_seo discovery finalization")

    require(config, ["'writing' => [", "'route_root' => 'writing'", "'index_path' => 'content/posts/index.json'", "'article_template' => ''"], "publishing configuration")
    require(writing_ui, ["/cms/writing.js", "Body (Markdown)", "Revision history", "/cms/seo.php"], "writing workspace")
    require(seo_ui, ["/cms/seo.js", "Canonical URL", "Discovery controls", "/cms/writing.php"], "SEO workspace")

    for name, source in [("writing", writing_js), ("seo", seo_js)]:
        require(source, ["X-CMS-CSRF", "/api/cms-auth.php"], f"{name} browser client")
        if ".innerHTML" in source or "insertAdjacentHTML" in source or "document.write" in source:
            fail(f"{name} browser client introduced an HTML injection rendering path")
        if "http://" in source or "https://" in source:
            fail(f"{name} browser client introduced a hard-coded external origin")

    print("PASS: publishing and SEO contract")


if __name__ == "__main__":
    main()
