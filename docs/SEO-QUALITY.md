# Site-wide SEO quality

AI Native CMS treats search/social quality as a site-wide inspection and projection concern without creating a second SEO source of truth.

## Authority boundary

Accepted page-specific SEO remains canonical in MySQL `seo_overrides`:

- browser title;
- description;
- canonical URL;
- index/follow/archive and preview controls;
- explicit custom Open Graph/Twitter copy.

Repository configuration may declare site-wide defaults such as author identity, locale, language, and a default social image. Those defaults are deterministic projection inputs, not page-specific authored overrides.

Generated `<meta>` and JSON-LD output remains projection. Do not repair it directly and treat the generated HTML as durable source.

## Site-wide audit

The Search + Social workspace and `database/seo-audit.php` inspect public HTML for cross-page and page-local defects including:

- missing, unusually short/long, or duplicate titles;
- missing, unusually short/long, or duplicate descriptions;
- missing or contradictory H1 structure;
- missing/foreign canonicals;
- canonicals pointing to missing or `noindex` targets;
- noncanonical pages remaining in the sitemap;
- indexable pages absent from `sitemap.txt`;
- `noindex` pages present in the sitemap;
- foreign sitemap URLs;
- missing canonical sitemap declaration in `robots.txt` when a sitemap is present;
- broken internal links;
- orphan indexable sitemap pages;
- incomplete Open Graph or Twitter/X metadata;
- missing/invalid JSON-LD;
- images missing an `alt` attribute.

The audit is read-only. Findings are evidence; they never become hidden authored state.

Run it from the project root after projection/rebuild:

```bash
php database/seo-audit.php
```

The normal command exits non-zero for structural errors. To make warnings release-blocking as well:

```bash
php database/seo-audit.php --strict
```

## Deterministic social/schema projection

During the final public-projection boundary, `api/seo-projection.php` applies canonical page overrides first and then fills safe site-wide presentation defaults:

- author metadata;
- `og:type`, locale, site name, canonical social URL;
- inherited Open Graph/Twitter titles and descriptions;
- configured default social image/alt where absent;
- Twitter card type;
- a generic `WebPage`, `CollectionPage`, or `Article` JSON-LD fallback only when valid JSON-LD is otherwise absent.

When a canonical SEO override sets `socialMode=custom`, explicit social copy is preserved. When `socialMode=inherit`, social titles/descriptions follow the canonical browser title/description on every rebuild.

The starter ships a neutral SVG share asset so projection has a complete default. Adopters should replace it with their own appropriate social image before launch; many social networks prefer a raster PNG/JPEG asset.

## Release-managed SEO updates

Repository update sets may include a `kind: seo` change for migration/release compatibility. This does not give Git unconditional authority over canonical SEO.

Rules:

1. If no canonical override exists, the release may seed it.
2. If canonical state already equals the release state, the update is idempotent.
3. If canonical state differs, it is preserved by default.
4. The release may replace an existing override only when it supplies an `expectedHash` that exactly matches the current canonical predecessor.

This is the same compare-and-swap principle used elsewhere in the content authority layer: release code can evolve known prior state without erasing newer human or agent-authored canonical work.

## Release use

For a release/rehearsal:

1. rebuild/project accepted state;
2. run the ordinary readiness report;
3. run `php database/seo-audit.php`;
4. investigate structural errors before publication;
5. optionally use `--strict` when warnings are intended to block the release;
6. keep any site-specific remediation in the adopter repository or canonical CMS state, not in reusable core.
