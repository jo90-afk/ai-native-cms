#!/usr/bin/env python3
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def read(path: str) -> str:
    full = ROOT / path
    if not full.is_file():
        fail(f"missing SEO parity file: {path}")
    return full.read_text(encoding="utf-8")


def require(source: str, needles: list[str], label: str) -> None:
    missing = [needle for needle in needles if needle not in source]
    if missing:
        fail(f"{label} is missing: {', '.join(missing)}")


def main() -> None:
    audit = read("api/seo-quality.php")
    projection = read("api/seo-projection.php")
    cms = read("api/cms-seo.php")
    sync = read("api/content-sync.php")
    sync_seo = read("api/content-sync-seo.php")
    rebuild = read("api/content-rebuild.php")
    config = read("config/site.example.php")
    setup = read("setup/site.php")
    ui = read("cms/seo.php")
    js = read("cms/seo.js")
    cli = read("database/seo-audit.php")

    require(audit, [
        "duplicate_title", "duplicate_description", "broken_internal_link", "orphan_page",
        "h1_count", "open_graph_incomplete", "twitter_incomplete", "schema_missing",
        "image_alt_missing", "sitemap_foreign_url", "robots_sitemap_missing",
        "canonical_target_missing", "canonical_target_noindex", "noncanonical_in_sitemap",
        "presentationPublicHtmlFiles($root)", "siteConfigValue('site','base_url'",
    ], "site-wide SEO audit")
    require(projection, [
        "seoProjectAllPublicPages", "seoProjectEnhancements", "seoProjectionFallbackSchema",
        "socialMode", "summary_large_image", "application/ld+json", "siteConfigValue('seo','social_image'",
        "presentationPublicHtmlFiles($root)",
    ], "site-wide SEO projection")
    require(rebuild, ["seoProjectAllPublicPages($root)", "after_seo", "navigationProject($root)", "brandingProject($root)"], "final projection order")
    require(cms, ["seoQualitySite($root,$targets)", "siteFindings", "seoErrors", "seoWarnings", "dbRequireSchemaVersion(8)"], "CMS SEO quality surface")
    require(sync, ["$kind==='seo'", "contentSyncApplySeoOverride"], "release update dispatch")
    require(sync_seo, ["expectedHash", "preserved_newer", "SELECT page_path", "FOR UPDATE", "seoCanonicalAllowed"], "SEO compare-and-swap release updates")
    require(config, ["'seo' => [", "'social_image' => '/assets/share-card.svg'", "'locale' => 'en_US'", "'language' => 'en-US'"], "portable SEO defaults")
    require(setup, ["$base['seo']['author']=$display"], "setup SEO identity propagation")
    require(ui, ["seo-quality-heading", "seo-site-findings", "seo-page-score", "seo-page-findings"], "SEO quality UI")
    require(js, ["renderSiteQuality", "renderPageQuality", "siteFindings", "SEO saved, projected, and re-audited."], "SEO quality browser client")
    if ".innerHTML" in js or "insertAdjacentHTML" in js or "document.write" in js:
        fail("SEO quality browser client introduced an HTML injection rendering path")
    require(cli, ["PHP_SAPI!=='cli'", "seoQualitySite($root)", "--strict", "summary['errors']"], "CLI SEO audit")

    combined = "\n".join([audit, projection, sync_seo, config]).lower()
    personal_display = "ju" + "de o"
    personal_slug = "jude" + "oneill"
    for forbidden in [personal_display, personal_slug, "site-config.json", "poetry/", "profilepage"]:
        if forbidden in combined:
            fail(f"production/site-specific SEO assumption leaked into reusable core: {forbidden}")

    print("PASS: site-wide SEO quality/projection parity contract")


if __name__ == "__main__":
    main()
