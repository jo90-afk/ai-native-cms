#!/usr/bin/env python3
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
REFERENCE_SITE_MARKER = "jude" + "oneill"


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def read(path: str) -> str:
    full = ROOT / path
    if not full.is_file():
        fail(f"missing deployment-adapter file: {path}")
    return full.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def main() -> None:
    public = read("adapters/apache/public.htaccess.example")
    private = read("adapters/apache/private.htaccess.example")
    docs = read("docs/DEPLOYMENT-ADAPTERS.md")
    builder = read("tools/build_release.py")

    redirect_contract = [
        "RewriteCond %{REQUEST_FILENAME} -f [OR]",
        "RewriteCond %{REQUEST_FILENAME} -d",
        "RewriteRule ^ - [L]",
        "RewriteRule ^ __redirect.php [L]",
        '<Files "__redirect-map.php">',
        "Require all denied",
    ]
    require(public, redirect_contract, "Apache public redirect interception")
    require(private, redirect_contract, "Apache private redirect interception")

    require(public, [
        'Cache-Control "public, max-age=86400, stale-while-revalidate=604800"',
        'Cache-Control "public, max-age=2592000, stale-while-revalidate=604800"',
        'Cache-Control "public, max-age=3600, stale-while-revalidate=86400"',
        'Cache-Control "public, max-age=0, must-revalidate"',
        "<IfModule mod_deflate.c>",
        "AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript application/json application/xml text/xml image/svg+xml",
    ], "Apache public cache/compression policy")

    require(private, [
        'Header always set Cache-Control "no-store, private"',
        "<IfModule mod_deflate.c>",
        "AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript application/json application/xml text/xml image/svg+xml",
    ], "Apache private transport policy")
    for forbidden in [
        "max-age=86400, stale-while-revalidate=604800",
        "max-age=2592000, stale-while-revalidate=604800",
        "max-age=3600, stale-while-revalidate=86400",
    ]:
        if forbidden in private:
            fail(f"public cache lifetime leaked into private adapter: {forbidden}")

    for name, source in [("public", public), ("private", private)]:
        lowered = source.lower()
        for forbidden in ["bluehost", "cpanel", REFERENCE_SITE_MARKER, "authuserfile", "authname", "require valid-user"]:
            if forbidden in lowered:
                fail(f"{name} Apache adapter contains provider/site/access-control assumption: {forbidden}")

    require(docs, [
        "Existing public files and directories are served normally",
        "unresolved request",
        "never connects to MySQL",
        "Public cache policy",
        "Private and preview deployments",
        "Apache reference files",
        "Other adapters",
    ], "host-neutral deployment contract")

    if 'INCLUDE_ROOTS = {"api", "cms", "config", "database", "docs", "adapters"}' not in builder:
        fail("release builder does not include deployment adapters")
    if '".example"' not in builder:
        fail("release builder does not scan example deployment configs as text")

    print("PASS: deployment adapter contract")


if __name__ == "__main__":
    main()
