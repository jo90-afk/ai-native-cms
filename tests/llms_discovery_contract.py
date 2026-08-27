from __future__ import annotations

import json
import re
import subprocess
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def run_projection(site: Path) -> dict:
    runner = site / "run-discovery.php"
    runner.write_text(
        "<?php\ndeclare(strict_types=1);\n"
        "require $argv[1];\n"
        "$site=['name'=>'Example Knowledge Site','description'=>'A neutral public site.','base_url'=>'https://example.test'];\n"
        "$discovery=discoveryProject($argv[2],$site);\n"
        "$llms=llmsProject($argv[2]);\n"
        "echo json_encode(['discovery'=>$discovery,'llms'=>$llms],JSON_UNESCAPED_SLASHES);\n",
        encoding="utf-8",
    )
    completed = subprocess.run(
        ["php", str(runner), str(ROOT / "api/llms-projection.php"), str(site)],
        check=True,
        capture_output=True,
        text=True,
    )
    return json.loads(completed.stdout)


def html(title: str, canonical: str, description: str = "", robots: str = "") -> str:
    desc = f'<meta name="description" content="{description}">' if description else ""
    robot = f'<meta name="robots" content="{robots}">' if robots else ""
    return f'<!doctype html><html><head><title>{title}</title>{desc}{robot}<link rel="canonical" href="{canonical}"></head><body>Public body</body></html>'


def assert_source_contract() -> None:
    discovery = (ROOT / "api/discovery-projection.php").read_text(encoding="utf-8")
    llms = (ROOT / "api/llms-projection.php").read_text(encoding="utf-8")
    rebuild = (ROOT / "api/content-rebuild.php").read_text(encoding="utf-8")
    assert "function discoveryProject(" in discovery
    assert "presentationPublicHtmlFiles" in discovery
    assert "function llmsProject(" in llms
    assert 'rel="describedby" href="/llms.txt"' in llms
    assert "subscriber data" in llms and "credentials" in llms
    assert rebuild.index("pageProjectionProjectManagedCleanRoutes") < rebuild.index("discoveryProject($root)") < rebuild.index("llmsProject($root)")
    subprocess.run(["php", "-l", str(ROOT / "api/discovery-projection.php")], check=True, stdout=subprocess.DEVNULL)
    subprocess.run(["php", "-l", str(ROOT / "api/llms-projection.php")], check=True, stdout=subprocess.DEVNULL)


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
        index = json.loads((site / "site-index.json").read_text(encoding="utf-8"))
        urls = [page["url"] for page in index["pages"]]
        assert urls == sorted(set(urls))
        assert urls == ["https://example.test/", "https://example.test/about/", "https://example.test/writing/first/"]
        assert not any("private" in url for url in urls)

        text = (site / "llms.txt").read_text(encoding="utf-8")
        assert text.startswith("# Example Knowledge Site\n\n> A neutral public site.")
        assert text.count("# Example Knowledge Site") == 1
        assert "## Pages" in text and "## Writing" in text and "## Machine-readable sources" in text
        assert "https://example.test/about/" in text and "https://example.test/writing/first/" in text
        assert "private.html" not in text and "/cms/" not in text
        for match in re.finditer(r"^- \[[^\]]+\]\(([^)]+)\)", text, re.MULTILINE):
            assert match.group(1).startswith(("https://", "http://")), match.group(1)

        full = (site / "llms-full.txt").read_text(encoding="utf-8")
        assert full.startswith(text.rstrip() + "\n\n---\n\n# Expanded public context")
        assert "Preserved public context." in full and "# Stale index" not in full
        sitemap = (site / "sitemap.txt").read_text(encoding="utf-8").splitlines()
        assert sitemap == urls

        second = run_projection(site)
        assert second["llms"]["htmlDiscoveryLinks"] == 0
        for page in site.rglob("*.html"):
            assert page.read_text(encoding="utf-8").count('rel="describedby" href="/llms.txt"') == 1


def main() -> None:
    assert_source_contract()
    assert_projection_contract()
    print("generic llms discovery projection contract: ok")


if __name__ == "__main__":
    main()
