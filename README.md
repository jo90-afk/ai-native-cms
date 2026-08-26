# AI Native CMS

AI Native CMS is a static-first publishing system extracted from a production CMS into a site-neutral reusable core.

MySQL owns accepted authored state. Authenticated operators and agents mutate that state through the same guarded operations, and public HTML/JSON/XML plus redirect routing data are deterministic projections. Anonymous readers do not require a database connection.

## Why “AI native”

An AI agent operates the same durable model as a human editor instead of automating browser clicks or editing generated HTML blindly. Changes target typed content objects, carry provenance, preserve revision history, respect compare-and-swap guards, and rebuild deterministic public output.

There is no privileged agent authority system. Authentication, expected revisions, canonical state, provenance, validation, schema guards, and projection boundaries apply regardless of caller.

## Current core

The reusable core now covers:

- hardened PHP/MySQL owner authentication, HTTPS/origin/CSRF/session/rate-limit boundaries, and audit records;
- schema-v8 canonical content, revision, provenance, composition, media, navigation, branding, SEO, and redirect state;
- editable repository pages plus full repository page-source documents;
- repository-owned structural templates exposing bounded rich-text, safe-link, and media values;
- canonical typed page compositions with optimistic hashes and deterministic reprojection;
- CMS-created root-level HTML pages built only from trusted shells/templates with validated parent hierarchy;
- canonical media metadata while adopter-owned image bytes remain in configured public roots;
- validated JPEG/PNG/WebP/GIF uploads constrained to configured roots and size limits;
- transactional long-form Markdown publishing with immutable prior snapshots, stale-write rejection, revision restore, draft/published state, static projection, and canonical published-slug redirect history;
- canonical SEO, primary navigation, site identity, adopter-declared bounded CSS custom-property values, and redirect authority;
- bounded same-site redirect validation with collision, reserved-path, ambiguous-separator, dot-segment, conflict, and cycle rejection;
- deterministic static redirect-map projection so anonymous redirects never query MySQL;
- configured read-only system aliases alongside editable canonical manual redirect records;
- three-way repository reconciliation that preserves newer accepted CMS state and remains CLI/deployment-adapter-only;
- one deterministic site-wide finalization boundary plus bounded adopter projector hooks, including an `after_seo` phase for sitemap/discovery work that must consume final SEO state;
- CLI-only idempotent schema/first-owner bootstrap that never seeds adopter content or silently migrates old schemas;
- an explicit CLI-only schema 7→8 migration separate from bootstrap repair;
- authenticated GET-only and CLI readiness reports covering portable runtime, database, canonical-state, and filesystem checks;
- trusted repository-owned readiness adapters for host-specific diagnostics;
- first-party `/cms/` workspaces for Pages, Composer, Media, Navigation, Branding, Writing, SEO, Redirects, and Readiness with no frontend framework or third-party active runtime dependency.

## Internal release candidate

The current internal candidate line is **`0.1.0-rc.2`**, schema version **8**. `VERSION` and `release/release.json` keep `public:false` and `licenseSelected:false`.

Build a review candidate from a repository checkout with:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits a deterministic source ZIP, a per-file manifest, and a whole-archive SHA256 under `dist/`. It excludes development/governance/runtime/adopter state such as `.git`, `.github`, `.lattice`, tests, release tooling, local `config/site.php`, uploads, runtime state, populated INI files, and generated `dist/` artifacts. It now includes optional reference deployment adapters under `adapters/` alongside the portable product documentation. CI also produces the candidate only as a short-lived private workflow artifact for review.

`0.1.0-rc.1` remains historical evidence for the schema-7 extraction. It is not silently rewritten into rc.2.

This is **not a public release**. No license has been selected. Repository visibility, a Git tag or GitHub Release, package publication, public download distribution, production deployment, and production adoption remain explicit separate decisions.

See `docs/RELEASE.md` for the candidate contract.

## Product boundary

Reusable core includes the hardened runtime, canonical authored state and revisions, repository page sources, canonical CMS-created pages, posts, templates/compositions, media metadata, navigation, branding, SEO, redirects, deterministic projection, portable bootstrap, explicit migrations, portable readiness checks, and deterministic release-candidate mechanics.

Reference deployment adapters may ship beside core but do not become CMS authority. Adopter-owned state includes site copy/content seeds, repository page/document registry, theme assets and visual identity, the exact CSS custom properties exposed to Branding, article/site templates, media bytes, configured system redirect aliases, host-specific readiness adapters, custom deterministic projectors, deployment credentials, and provider-specific deployment/interception behavior.

Optional extensions stay outside core when they are not general CMS concerns.

## Compatibility strategy

The repository intentionally keeps the reference implementation’s `api/`, `cms/`, and `database/` topology. General features can therefore move between an adopter site and this repository with small, reviewable diffs. Site-specific values enter through configuration or adapters rather than forks of core behavior.

Before a candidate returns to the public-release gate, intervening production proving-ground work should be classified as **core**, **adapter**, or **site-only**. An unresolved reusable core delta reopens extraction; adapter and personal-site changes do not.

Architecture and extraction details are in `docs/ARCHITECTURE.md`, `docs/UPSTREAMING.md`, and `docs/EXTRACTION-MATRIX.md`.

## Installation and migration

Requirements:

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- Python 3.10+ for repository/release validation

Node is used only for repository-side JavaScript syntax validation; it is not a production runtime dependency.

The portable fresh-install sequence is:

1. Copy `config/site.example.php` to adopter-local `config/site.php` and define public structure only.
2. Put database/CMS secrets in environment variables or a private INI outside the public root; `database/private-config.example.ini` documents supported `AINCMS_*` values.
3. Create a dedicated MySQL database and runtime user.
4. Initialize schema and the first persisted owner:

```bash
php database/bootstrap.php
```

5. Explicitly initialize canonical repository content:

```bash
php database/reconcile.php initial-import
```

6. Run the read-only readiness report:

```bash
php database/readiness.php
```

7. Serve the repository with PHP and open `/cms/` directly over HTTPS.

Bootstrap derives the schema/table contract from `database/schema.sql`, never seeds adopter content, never overwrites an existing owner credential, refuses unrelated non-empty databases, and does not silently migrate older schemas. `--repair` is limited to incomplete installs already stamped with the current schema version.

An existing schema-7 installation must use the explicit migration after a verified backup/restore test:

```bash
php database/migrations/7-to-8.php --apply
```

The migration accepts schema 7 only, uses a database migration lock, creates canonical redirect authority, and advances the schema stamp only after the structural change succeeds. It is not reachable through bootstrap repair.

The complete fresh-install, backup, migration, and rollback procedure is in `docs/INSTALLATION.md`.

## Authority model

Repository pages declared in `cms.editable_pages` are the portable Git/source lineage and the only pages from which structural templates are harvested. Managed pages are the broader runtime set: repository pages plus canonical CMS-created compositions.

A generated page can consume repository templates, participate in Pages/SEO/navigation/hierarchy, and be rebuilt deterministically, but it never feeds itself back into repository-source or template authority.

Browser endpoints do not reconcile Git/source candidates into SQL. Repository reconciliation is explicit CLI/deployment-adapter work. Canonical mutation surfaces require the current schema before writing, so old installations fail closed rather than partially mutating newer state.

The browser never submits structural HTML. Template discovery stores trusted blocks plus only their exposed typed values. Surviving canonical leaf values persist across recomposition. New CMS routes are bounded lowercase root-level `.html` filenames using trusted shells; missing/self/cyclic parent relationships are rejected before persistence.

## Media, publishing, navigation, branding, SEO, and redirects

`media_library` stores canonical media identity/metadata while bytes remain adopter-owned assets. Raster uploads are byte-, MIME-, dimension-, and root-bounded; SVG may be cataloged when already present but is not accepted as an upload.

`posts` is canonical. Updates snapshot prior state; stale writes conflict rather than overwrite newer content. Drafts have no public article projection. Published posts materialize static HTML. When a published slug changes, the old route enters canonical redirect authority rather than becoming an ad hoc redirect file.

SEO is canonical in `seo_overrides`; custom canonicals stay on the configured public origin. Primary navigation is canonical in `site_navigation` and projects only into `<nav id="site-nav">`. Branding is canonical in `site_branding` but controls only identity text and CSS custom properties explicitly declared by the adopter.

`redirect_records` is canonical redirect authority. Manual records use optimistic revision hashes and are editable in Redirects. Adopter-configured system aliases are visible/read-only. Redirect sources and targets remain same-site and are graph-validated before persistence/projection. Core emits deterministic `__redirect-map.php` data and a database-free redirect runtime; interception of unresolved requests belongs to deployment adapters rather than canonical CMS authority.

## Deployment adapters

`docs/DEPLOYMENT-ADAPTERS.md` defines the host-neutral transport contract. A deployment adapter should serve existing files/directories normally, route only unresolved requests to the database-free redirect runtime (or an equivalent projection consumer), prevent direct public access to the generated redirect map, and preserve the original request/query semantics.

The first reference implementation is Apache:

- `adapters/apache/public.htaccess.example` adds redirect interception, one-day CSS/JS caching, 30-day image caching, one-hour discovery-file caching, HTML revalidation, and DEFLATE when available.
- `adapters/apache/private.htaccess.example` keeps redirect interception/compression while applying `Cache-Control: no-store, private` and omitting public cache lifetimes.

These are mergeable examples, not an instruction to overwrite an adopter’s `.htaccess`. They contain no access-control credentials, provider assumptions, automatic deployment behavior, CDN requirement, or asset-fingerprinting requirement. Equivalent nginx/CDN/edge adapters may implement the same contract later.

## Reconciliation and rebuild

`php database/reconcile.php <source-ref>` compares repository candidates with canonical hashes and prior source lineage, accepts source changes only when canonical state has not diverged, preserves newer database edits, applies explicit update sets, then runs deterministic public rebuilding.

The derived-state order is explicit: canonical documents/pages → compositions → published articles → `after_pages` adapters → SEO → `after_seo` discovery/sitemap adapters → navigation → branding → redirect map → final hooks.

A Git pull therefore cannot silently undo accepted CMS state, regeneration cannot silently erase composition or site-wide canonical state, and discovery projectors have a defined point at which final SEO decisions are already available.

## Readiness

Readiness is observational. The browser endpoint is authenticated GET-only; the CLI report uses the same core model. It does not initialize/migrate schema, publish content, send email, deploy files, invoke shell commands, or return credential/grant values.

Portable checks cover PHP/configuration, production origin/security posture, MySQL availability/schema/owner state, remote TLS status, grant-metadata visibility, canonical content initialization, and bounded filesystem write targets. Host/provider checks belong in trusted repository-owned readiness adapters.

## Security and release status

See `SECURITY.md` for the administration, secret-handling, sanitization, redirect-routing, and release-artifact security boundaries.

The reusable extraction is on the schema-v8 `0.1.0-rc.2` line with optional reference deployment adapters. The candidate remains an internal review artifact until cumulative M-001–M-010 verification and artifact inspection are recorded. Public distribution still requires deliberate Principal decisions on licensing, visibility, tagging/publication, and production adoption.
