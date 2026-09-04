from __future__ import annotations

import json
import re
import subprocess
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def run_projection(site: Path, base: str = "https://example.test", discover: bool = True) -> dict:
    runner = site / "run-discovery.php"
    runner.write_text(
        "<?php\ndeclare(strict_types=1);\n"
        "require $argv[1];\n"
        "$site=['name'=>'Example Knowledge Site','description'=>'A neutral public site.','base_url'=>$argv[3]];\n"
        "$discovery=$argv[4]==='yes'?discoveryProject($argv[2],$site):[];\n"
        "$llms=llmsProject($argv[2]);\n"
        "echo json_encode(['discovery'=>$discovery,'llms'=>$llms],JSON_UNESCAPED_SLASHES);\n",
        encoding="utf-8",
    )
    completed = subprocess.run(
        ["php", str(runner), str(ROOT / "api/llms-projection.php"), str(site), base, "yes" if discover else "no"],
        check=True,
        capture_output=True,
        text=True,
    )
    return json.loads(completed.stdout)


def must_fail(site: Path, message: str) -> None:
    try:
        run_projection(site)
    except subprocess.CalledProcessError as exc:
        assert message in exc.stderr, exc.stderr
    else:
        raise AssertionError("Projection falsely reported success")


def put(site: Path, relative: str, text: str) -> Path:
    path = site / relative
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(text, encoding="utf-8")
    return path


def html(title: str, canonical: str, description: str = "", robots: str = "") -> str:
    desc = f'<meta name="description" content="{description}">' if description else ""
    robot = f'<meta name="robots" content="{robots}">' if robots else ""
    return f'<!doctype html><html><head><title>{title}</title>{desc}{robot}<link rel="canonical" href="{canonical}"></head><body>Public body</body></html>'


def assert_source_contract() -> None:
    discovery = (ROOT / "api/discovery-projection.php").read_text(encoding="utf-8")
    llms = (ROOT / "api/llms-projection.php").read_text(encoding="utf-8")
    markdown = (ROOT / "api/markdown-projection.php").read_text(encoding="utf-8")
    rebuild = (ROOT / "api/content-rebuild.php").read_text(encoding="utf-8")
    assert "function discoveryProject(" in discovery
    assert "presentationPublicHtmlFiles" in discovery
    assert "function llmsProject(" in llms
    assert "markdownProject($root,$index)" in llms
    assert "function markdownProject(" in markdown
    assert 'rel="describedby" href="/llms.txt"' in llms
    assert "subscriber data" in llms and "credentials" in llms
    assert rebuild.index("pageProjectionProjectManagedCleanRoutes") < rebuild.index("discoveryProject($root)") < rebuild.index("llmsProject($root)")
    subprocess.run(["php", "-l", str(ROOT / "api/discovery-projection.php")], check=True, stdout=subprocess.DEVNULL)
    subprocess.run(["php", "-l", str(ROOT / "api/llms-projection.php")], check=True, stdout=subprocess.DEVNULL)
    subprocess.run(["php", "-l", str(ROOT / "api/markdown-projection.php")], check=True, stdout=subprocess.DEVNULL)


def assert_projection_contract() -> None:
    with tempfile.TemporaryDirectory() as tmp:
        site = Path(tmp)
        (site / "index.html").write_text(html("Example Knowledge Site", "https://example.test/", "A neutral public site."), encoding="utf-8")
        (site / "about.html").write_text(html("About legacy", "https://example.test/about/", "About this site."), encoding="utf-8")
        clean = site / "about" / "index.html"
        clean.parent.mkdir(parents=True)
        clean.write_text(html("About", "https://example.test/about/", "About this site."), encoding="utf-8")
        article = site / "writing" / "first" / "index.html"
        article.parent.mkdir(parents=True)
        article.write_text(html("First essay", "https://example.test/writing/first/", "A public essay."), encoding="utf-8")
        (site / "private.html").write_text(html("Private", "https://example.test/private.html", "Not discoverable.", "noindex,nofollow"), encoding="utf-8")
        (site / "llms-full.txt").write_text("# Stale index\n\n---\n\n# Expanded public context\n\nPreserved public context.\n", encoding="utf-8")

        first = run_projection(site)
        assert first["discovery"]["pages"] == 3
        assert first["llms"]["htmlDiscoveryLinks"] >= 4
        assert first["llms"]["markdown"]["generated"] == 3
        assert first["llms"]["markdown"]["alternates"] == ["about/index.md", "index.md", "writing/first/index.md"]
        index = json.loads((site / "site-index.json").read_text(encoding="utf-8"))
        urls = [page["url"] for page in index["pages"]]
        assert urls == sorted(set(urls))
        assert urls == ["https://example.test/", "https://example.test/about/", "https://example.test/writing/first/"]
        assert not any("private" in url for url in urls)

        text = (site / "llms.txt").read_text(encoding="utf-8")
        assert text.startswith("# Example Knowledge Site\n\n> A neutral public site.")
        assert text.count("# Example Knowledge Site") == 1
        assert "## Pages" in text and "## Writing" in text and "## Machine-readable sources" in text
        assert "https://example.test/about/index.md" in text and "https://example.test/writing/first/index.md" in text
        assert "private.html" not in text and "/cms/" not in text
        for match in re.finditer(r"^- \[[^\]]+\]\(([^)]+)\)", text, re.MULTILINE):
            assert match.group(1).startswith(("https://", "http://")), match.group(1)

        full = (site / "llms-full.txt").read_text(encoding="utf-8")
        assert full.startswith(text.rstrip() + "\n\n---\n\n# Expanded public context")
        assert "Preserved public context." in full and "# Stale index" not in full
        sitemap = (site / "sitemap.txt").read_text(encoding="utf-8").splitlines()
        assert sitemap == urls
        assert not (site / "about.md").exists(), "A legacy/clean pair has one canonical alternate"
        assert 'href="/about/index.md"' in (site / "about.html").read_text()
        assert 'rel="alternate" type="text/markdown"' in clean.read_text()
        assert "Canonical: https://example.test/about/" in (site / "about/index.md").read_text()
        snapshot = {p.relative_to(site): p.read_bytes() for p in site.rglob("*") if p.is_file()}

        second = run_projection(site)
        assert second["llms"]["htmlDiscoveryLinks"] == 0
        assert second["llms"]["markdown"]["htmlAlternateLinks"] == 0
        assert snapshot == {p.relative_to(site): p.read_bytes() for p in site.rglob("*") if p.is_file()}
        for page in site.rglob("*.html"):
            text = page.read_text(encoding="utf-8")
            if page.name == "private.html":
                assert 'rel="describedby"' not in text and 'text/markdown' not in text
            else:
                assert text.count('rel="describedby" href="/llms.txt"') == 1


def assert_markdown_behavior() -> None:
    with tempfile.TemporaryDirectory() as tmp:
        site = Path(tmp)
        body = """<script>const shell = '<main>SCRIPT_MAIN_SECRET</main>';</script><template><main>TEMPLATE_MAIN_SECRET</main></template>
        <header>GLOBAL_NAV_SECRET</header><main><h1>Public guide</h1>
        <p>Readable <strong>emphasis</strong>, <em>nuance</em>, and café.</p>
        <p><a href="../about/?view=one#part">About us</a> and <a href="#local">this section</a>.</p>
        <p><a href="https://reference.test/source">Public reference</a>
        <a href="javascript:alert(1)">Unsafe scheme</a><a href="/api/private">Private API</a></p>
        <ol><li>First item</li><li>Second item</li></ol><blockquote>Published quotation.</blockquote>
        <pre><code>if (a &lt; b) {\n  return `value`;\n}</code></pre><img alt="A useful diagram" src="diagram.png">
        <div hidden><div>NESTED_HIDDEN_SECRET</div>AFTER_CHILD_SECRET</div>
        <p aria-hidden="true">ARIA_SECRET</p><p style="display: none">STYLE_SECRET</p>
        <form><p>FORM_SECRET</p><input value="INPUT_SECRET > STILL_SECRET"></form>
        <script>const x = 'SCRIPT_SECRET';</script><template>TEMPLATE_SECRET</template>
        <svg><text>SVG_SECRET</text></svg><!-- COMMENT_SECRET -->
        <p>Visible ending.</p></main><footer>FOOTER_SECRET</footer>"""
        page = put(site, "guides/first/index.html", html("Guide title", "https://example.test/guides/first/").replace("Public body", body))
        run_projection(site)
        text = (page.parent / "index.md").read_text()
        for expected in ["# Public guide", "**emphasis**", "*nuance*", "café", "1. First item", "2. Second item", "> Published quotation.", "  return `value`;", "if (a < b)", "A useful diagram", "Visible ending."]:
            assert expected in text, (expected, text)
        assert "https://example.test/guides/about/?view=one#part" in text
        assert "https://example.test/guides/first/#local" in text
        assert "https://reference.test/source" in text
        assert "_SECRET" not in text and "javascript:" not in text and "/api/private" not in text
        assert "<main" not in text and "<pre" not in text
        hidden_root = put(site, "hidden-root.html", html("Hidden region", "https://example.test/hidden-root.html").replace("Public body", '<div hidden><main><p>HIDDEN_ROOT_SECRET</p></main></div><article aria-hidden="true">HIDDEN_ARTICLE_SECRET</article><p>Visible fallback.</p>'))
        run_projection(site)
        assert "_SECRET" not in hidden_root.with_suffix(".md").read_text()
        assert "Visible fallback." in hidden_root.with_suffix(".md").read_text()


def assert_cleanup_and_privacy() -> None:
    with tempfile.TemporaryDirectory() as tmp, tempfile.TemporaryDirectory() as outside:
        site = Path(tmp)
        put(site, "index.html", html("Public home", "https://example.test/"))
        private_names = ["api", "cms", "setup", "database", "tests", "tools", "scripts", "runtime", "config", "docs", "templates", "adapters", "uploads", "private", "drafts", ".hidden"]
        for name in private_names:
            put(site, f"{name}/leak.html", html("PRIVATE_TITLE_SECRET", f"https://example.test/{name}/leak.html"))
        put(site, "external.html", html("EXTERNAL_SECRET", "https://outside.test/"))
        put(site, "port.html", html("PORT_SECRET", "https://example.test:444/"))
        put(site, "scheme.html", html("SCHEME_SECRET", "http://example.test/"))
        put(site, "encoded.html", html("ENCODED_SECRET", "https://example.test/%2e%2e/cms/"))
        put(site, "internal.html", html("INTERNAL_SECRET", "https://example.test/cms/"))
        put(site, "credentials.html", html("CREDENTIAL_SECRET", "https://user:password@example.test/"))
        secret = put(Path(outside), "outside.html", html("SYMLINK_SECRET", "https://example.test/symlink.html"))
        (site / "symlink.html").symlink_to(secret)
        (site / "linked-directory").symlink_to(Path(outside), target_is_directory=True)
        result = run_projection(site)
        assert result["discovery"]["pages"] == 1
        assert result["llms"]["markdown"]["generated"] == 1
        for filename in ["llms.txt", "site-index.json", "sitemap.xml", "index.md"]:
            assert "_SECRET" not in (site / filename).read_text(), filename
        assert not (Path(outside) / "outside.md").exists()
        index = json.loads((site / "site-index.json").read_text())
        index["pages"] = [{"title": "FORGED_SECRET", "url": "https://example.test/leak.html", "sourcePath": "../outside.html"}]
        (site / "site-index.json").write_text(json.dumps(index))
        run_projection(site, discover=False)
        assert "FORGED_SECRET" not in (site / "llms.txt").read_text()
        manual = put(site, "notes.md", "# Authored notes\n")
        article = put(site, "writing/current/index.html", html("Current", "https://example.test/writing/current/"))
        run_projection(site)
        assert article.with_suffix(".md").is_file()
        article.write_text(article.read_text().replace("</head>", '<meta name="robots" content="noindex"></head>'))
        result = run_projection(site)
        assert result["llms"]["markdown"]["removed"] == 1
        assert not article.with_suffix(".md").exists() and "text/markdown" not in article.read_text()
        assert manual.read_text() == "# Authored notes\n"
        (site / "index.html").unlink()
        result = run_projection(site)
        assert result["llms"]["markdown"]["removed"] == 1 and not (site / "index.md").exists()


def assert_optional_corpus_and_collisions() -> None:
    with tempfile.TemporaryDirectory() as tmp:
        site = Path(tmp)
        put(site, "index.html", html("Home", "https://example.test/"))
        result = run_projection(site)
        assert result["llms"]["llmsFullSynchronized"] is False
        assert "[Expanded LLM context]" not in (site / "llms.txt").read_text()
        old = (site / "llms.txt").read_bytes()
        full = put(site, "llms-full.txt", "# Unseparated context\n")
        must_fail(site, "cannot be synchronized")
        assert (site / "llms.txt").read_bytes() == old
        assert full.read_text() == "# Unseparated context\n"
        corpus = "\n---\n\n# Essays\n\nOriginal public corpus.\n"
        full.write_text("# Stale prefix\n" + corpus)
        assert run_projection(site)["llms"]["llmsFullSynchronized"] is True
        assert full.read_text() == (site / "llms.txt").read_text().rstrip() + "\n" + corpus
        full.unlink()
        full.mkdir()
        must_fail(site, "regular local file")
        full.rmdir()
        full.symlink_to(site / "missing.txt")
        must_fail(site, "regular local file")
        full.unlink()
        (site / "index.md").write_text("# My authored document\n")
        must_fail(site, "overwrite an authored file")
        assert (site / "index.md").read_text() == "# My authored document\n"
        (site / "index.md").unlink()
        (site / "index.md").symlink_to(site / "notes.md")
        must_fail(site, "Unsafe Markdown projection destination")
        (site / "index.md").unlink()
        page = site / "index.html"
        authored = html("Home", "https://example.test/").replace("</head>", '<link rel="alternate" type="text/markdown" href="/authored.md"></head>')
        page.write_text(authored)
        must_fail(site, "replace an authored alternate link")
        assert page.read_text() == authored and not (site / "index.md").exists()
        page.write_text(authored.replace("</head>", '<meta name="robots" content="noindex"></head>'))
        run_projection(site)
        assert 'href="/authored.md"' in page.read_text(), "An ineligible page retains its authored alternate"


def assert_base_path_and_alternate_idempotence() -> None:
    with tempfile.TemporaryDirectory() as tmp:
        site = Path(tmp)
        page = put(site, "index.html", html("Manual", "https://example.test/manual/"))
        original = page.read_text().replace("</head>", '<link type="text/markdown" href="/old.md" rel="alternate" data-aincms-projection="markdown"><link rel="alternate" type="text/markdown" href="/older.md" data-aincms-projection="markdown"></head>')
        page.write_text(original)
        put(site, "outside.html", html("OUTSIDE_BASE_SECRET", "https://example.test/other/"))
        first = run_projection(site, base="https://example.test/manual")
        assert first["discovery"]["pages"] == 1
        assert "https://example.test/manual/index.md" in (site / "llms.txt").read_text()
        assert 'href="/manual/index.md"' in page.read_text()
        assert page.read_text().count('type="text/markdown"') == 1
        assert "OUTSIDE_BASE_SECRET" not in (site / "llms.txt").read_text()
        second = run_projection(site, base="https://example.test/manual")
        assert second["llms"]["markdown"]["htmlAlternateLinks"] == 0
        page.write_text(page.read_text().replace(' data-aincms-projection="markdown"', ''))
        run_projection(site, base="https://example.test/manual")
        assert page.read_text().count('type="text/markdown"') == 1
        assert 'data-aincms-projection="markdown"' in page.read_text(), "A correct existing relation can be adopted"


def main() -> None:
    assert_source_contract()
    assert_projection_contract()
    assert_markdown_behavior()
    assert_cleanup_and_privacy()
    assert_optional_corpus_and_collisions()
    assert_base_path_and_alternate_idempotence()
    print("generic llms discovery projection contract: ok")


if __name__ == "__main__":
    main()
