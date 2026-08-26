#!/usr/bin/env python3
from __future__ import annotations

from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def text(path: str) -> str:
    file = ROOT / path
    if not file.is_file():
        fail(f"missing M-003 file: {path}")
    return file.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def main() -> None:
    rebuild = text("api/content-rebuild.php")
    reconcile = text("database/reconcile.php")
    auth = text("api/cms-auth.php")
    login = text("cms/index.php")
    pages = text("cms/pages.php")
    js = text("cms/cms.js")
    css = text("cms/cms.css")
    config = text("config/site.example.php")

    require(rebuild, [
        "before_documents",
        "after_documents",
        "before_pages",
        "after_pages",
        "finalize",
        "pathInside($full,$root)",
        "is_callable",
        "contentAuthorityProjectConfiguredDocuments",
        "contentAuthorityProjectPages",
    ], "rebuild hook registry")
    if "eval(" in rebuild or "shell_exec" in rebuild or "exec(" in rebuild:
        fail("rebuild hook registry introduced dynamic shell/eval execution")

    require(reconcile, ["contentRebuild($root)"], "reconciliation orchestration")
    require(config, ["'hooks' => [", "'script' => 'adapters/discovery.php'", "'callable' => 'projectDiscovery'"], "projection configuration")

    require(auth, [
        "enforceCmsHttps(true)",
        "requireSameOrigin(true)",
        "enforceRateLimit('cms-login'",
        "requireCmsCsrf()",
        "cmsAuthenticate",
        "cmsLogout",
    ], "authentication endpoint")

    require(pages, ["requireCmsAuth(false)", "meta name=\"cms-csrf\"", "/cms/cms.js", "/cms/cms.css"], "page editor shell")
    require(login, ["cmsCurrentUser", "/cms/cms.js", "/cms/cms.css"], "login shell")

    for name, source in [("login", login), ("pages", pages)]:
        if re.search(r"<script(?!\s+src=)[^>]*>", source, flags=re.I):
            fail(f"{name} surface contains inline executable script")

    require(js, [
        "X-CMS-CSRF",
        "/api/cms-auth.php",
        "/api/cms-pages.php",
        "dataset.hash",
        "dataset.original",
        "response.status === 401",
    ], "CMS browser client")
    if ".innerHTML" in js or "insertAdjacentHTML" in js or "document.write" in js:
        fail("CMS browser client introduced an HTML injection rendering path")
    if "http://" in js or "https://" in js:
        fail("CMS browser client introduced a third-party/network origin")

    if not css.strip():
        fail("CMS stylesheet is empty")

    print("PASS: rebuild and native CMS UI contract")


if __name__ == "__main__":
    main()
