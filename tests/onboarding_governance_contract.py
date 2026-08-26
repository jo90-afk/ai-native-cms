#!/usr/bin/env python3
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def text(path: str) -> str:
    file = ROOT / path
    if not file.is_file():
        fail(f"missing onboarding/governance file: {path}")
    return file.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def main() -> None:
    metadata = json.loads(text("release/release.json"))
    distribution = metadata.get("distribution", {})
    license_meta = distribution.get("license", {})
    if metadata.get("version") != "0.1.0-rc.3" or metadata.get("schemaVersion") != 8:
        fail("rc.3 metadata/version/schema boundary is wrong")
    if distribution.get("public") is not False or distribution.get("licenseSelected") is not True:
        fail("rc.3 must remain private while carrying selected license terms")
    expected_license = {
        "base": "Apache-2.0",
        "condition": "Commons Clause License Condition v1.0",
        "sourceAvailable": True,
        "osiApproved": False,
        "attributionRequired": True,
    }
    for key, value in expected_license.items():
        if license_meta.get(key) != value:
            fail(f"license metadata disagrees on {key}")

    license_text = text("LICENSE")
    notice = text("NOTICE")
    require(license_text, [
        "Commons Clause",
        "Apache License, Version 2.0",
        "right to Sell the Software",
        "commercial",
    ], "source-available license")
    require(notice, ["AI Native CMS", "attribution"], "attribution notice")

    starter_files = [
        "index.html",
        "about.html",
        "writing.html",
        "assets/styles.css",
        "assets/site.js",
        "templates/article.html",
    ]
    for path in starter_files:
        text(path)
    for path in ["index.html", "about.html", "writing.html"]:
        page = text(path)
        require(page, ["id=\"site-nav\"", "brand-mark", "brand-name", "data-cms-id="], f"starter page {path}")

    config = text("config/site.example.php")
    require(config, [
        "'article_template' => 'templates/article.html'",
        "'stylesheet' => 'assets/styles.css'",
        "'accent' => [",
        "'contentWidth' => [",
        "'css'=>'--content-width'",
    ], "starter configuration")

    setup = text("setup/site.php")
    require(setup, [
        "PHP_SAPI==='cli'",
        "siteSetupUrl",
        "siteSetupConfig",
        "siteSetupWrite",
        "config/site.php already exists",
        "never reads or writes credentials",
    ], "safe public site initializer")
    for forbidden in ["$_POST", "$_GET", "REQUEST_METHOD", "db()", "siteSecret("]:
        if forbidden in setup:
            fail(f"site initializer crossed the browser/secret/database boundary: {forbidden}")

    onboarding = text("api/onboarding.php")
    onboarding_api = text("api/cms-onboarding.php")
    onboarding_ui = text("cms/onboarding.php")
    onboarding_js = text("cms/onboarding.js")
    require(onboarding, [
        "onboardingStarterFiles",
        "onboardingSiteIdentity",
        "readinessReport($root)",
        "brandingState()",
        "navigationState($root)",
        "content.authority",
        "$identity['customized']&&$starterReady",
        "Repository-owned structure and code change through Git branches/review.",
    ], "state-derived onboarding model")
    for forbidden in ["INSERT INTO", "UPDATE ", "DELETE FROM", "file_put_contents", "cmsAtomicWrite", "$_POST"]:
        if forbidden in onboarding:
            fail(f"onboarding state model became mutating: {forbidden}")
    require(onboarding_api, ["requireCmsAuth(true)", "$method!=='GET'", "onboardingState($root)"], "read-only authenticated onboarding API")
    require(onboarding_ui, [
        "/cms/onboarding.js",
        "Start with a working site. Make it specific.",
        "Structure lives in Git. Accepted content lives in the CMS.",
        "Keep iterating without losing the source of truth.",
    ], "onboarding workspace")
    require(onboarding_js, ["/api/cms-onboarding.php", "replaceChildren", "progress"], "onboarding browser client")
    if ".innerHTML" in onboarding_js or "insertAdjacentHTML" in onboarding_js or "document.write" in onboarding_js:
        fail("onboarding browser client introduced an HTML injection rendering path")

    cms_index = text("cms/index.php")
    cms_js = text("cms/cms.js")
    require(cms_index, ["/cms/onboarding.php", "onboardingState($root)", "/cms/pages.php"], "state-aware authenticated CMS entry")
    require(cms_js, ["data.onboarding?.ready ? '/cms/pages.php' : '/cms/onboarding.php'"], "state-aware post-login handoff")

    workspace_paths = [
        "cms/pages.php", "cms/composer.php", "cms/media.php", "cms/navigation.php", "cms/branding.php",
        "cms/writing.php", "cms/seo.php", "cms/redirects.php", "cms/readiness.php",
    ]
    for path in workspace_paths:
        require(text(path), ['href="/cms/onboarding.php"'], f"shared onboarding navigation in {path}")

    repo_ops = text("docs/REPOSITORY-OPERATIONS.md")
    llm = text("docs/LLM-COLLABORATION.md")
    agents = text("AGENTS.md")
    require(repo_ops, [
        "Commit to Git",
        "Keep in canonical MySQL state",
        "Generated public output",
        "Branch and pull-request workflow",
        "SSH pull-to-host",
        "reviewed artifact/copy",
        "Database backups and migrations",
        "Rollback",
        "Working with an LLM on the repository",
    ], "repository/hosting operations guide")
    require(llm, [
        "The four kinds of state",
        "Repository-owned state",
        "Canonical CMS state",
        "Generated public projection",
        "Host/provider state",
        "Interface / design iteration",
        "Content iteration",
        "Feature work",
        "Bug fix",
        "Schema / migration work",
        "Release work",
        "Conversation",
    ], "LLM collaboration guide")
    require(agents, [
        "Source-of-truth order",
        "Human and agent writers use the same canonical contracts",
        "Change packets",
        "Conversation memory is context, not authority",
    ], "repository agent contract")

    print("PASS: licensed rc3 onboarding and governance contract")


if __name__ == "__main__":
    main()
