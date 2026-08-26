# AI Native CMS

AI Native CMS is a static-first publishing system extracted from a production CMS into a site-neutral reusable core.

MySQL owns accepted authored state. Authenticated operators and agents mutate that state through the same guarded operations, and public HTML/JSON/XML are deterministic projections. Anonymous readers do not require a database connection.

## Why “AI native”

An AI agent operates the same durable model as a human editor instead of automating browser clicks or editing generated HTML blindly. Changes target typed content objects, carry provenance, preserve revision history, respect compare-and-swap guards, and rebuild deterministic public output.

There is no privileged agent authority system. Authentication, expected revisions, canonical state, provenance, validation, and projection boundaries apply regardless of caller.

## Current core

The reusable core now covers:

- hardened PHP/MySQL owner authentication, HTTPS/origin/CSRF/session/rate-limit boundaries, and audit records;
- schema-v7 canonical content, revision, provenance, composition, media, navigation, branding, and SEO state;
- editable repository pages plus full repository page-source documents;
- repository-owned structural templates exposing bounded rich-text, safe-link, and media values;
- canonical typed page compositions with optimistic hashes and deterministic reprojection;
- CMS-created root-level HTML pages built only from trusted shells/templates with validated parent hierarchy;
- canonical media metadata while adopter-owned image bytes remain in configured public roots;
- validated JPEG/PNG/WebP/GIF uploads constrained to configured roots and size limits;
- transactional long-form Markdown publishing with immutable prior snapshots, stale-write rejection, revision restore, draft/published state, and static projection;
- canonical SEO, primary navigation, site identity, and adopter-declared bounded CSS custom-property values;
- three-way repository reconciliation that preserves newer accepted CMS state;
- deterministic rebuild ordering plus bounded adopter projector hooks;
- CLI-only idempotent schema/first-owner bootstrap that never seeds adopter content or silently migrates old schemas;
- authenticated GET-only and CLI readiness reports covering portable runtime, database, canonical-state, and filesystem checks;
- trusted repository-owned readiness adapters for host-specific diagnostics;
- first-party `/cms/` workspaces for Pages, Composer, Media, Navigation, Branding, Writing, SEO, and Readiness with no frontend framework or third-party active runtime dependency.

## Internal release candidate

M-008 adds reproducible **internal** release-candidate mechanics. `VERSION` and `release/release.json` currently identify `0.1.0-rc.1`, schema version 7, with `public:false` and `licenseSelected:false`.

Build a review candidate from a repository checkout with:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits a deterministic source ZIP, a per-file manifest, and a whole-archive SHA256 under `dist/`. It excludes development/governance/runtime/adopter state such as `.git`, `.github`, `.lattice`, tests, release tooling, local `config/site.php`, uploads, runtime state, populated INI files, and generated `dist/` artifacts. CI also produces the candidate only as a short-lived private workflow artifact for review.

This is **not a public release**. No license has been selected. Repository visibility, a Git tag or GitHub Release, package publication, public download distribution, production deployment, and production adoption remain explicit separate decisions.

See `docs/RELEASE.md` for the candidate contract.

## Product boundary

Reusable core includes the hardened runtime, canonical authored state and revisions, repository page sources, canonical CMS-created pages, posts, templates/compositions, media metadata, navigation, branding, SEO, deterministic projection, portable bootstrap, portable readiness checks, and deterministic release-candidate mechanics.

Adopter-owned state includes site copy/content seeds, repository page/document registry, theme assets and visual identity, the exact CSS custom properties exposed to Branding, article/site templates, media bytes, host-specific readiness adapters, custom deterministic projectors, deployment credentials, and provider-specific deployment behavior.

Optional extensions stay outside core when they are not general CMS concerns.

## Compatibility strategy

The repository intentionally keeps the reference implementation’s `api/`, `cms/`, and `database/` topology. General features can therefore move between an adopter site and this repository with small, reviewable diffs. Site-specific values enter through configuration or adapters rather than forks of core behavior.

Architecture and extraction details are in `docs/ARCHITECTURE.md`, `docs/UPSTREAMING.md`, and `docs/EXTRACTION-MATRIX.md`.

## Installation

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

The complete fresh-install, backup, upgrade-boundary, and rollback procedure is in `docs/INSTALLATION.md`.

## Authority model

Repository pages declared in `cms.editable_pages` are the portable Git/source lineage and the only pages from which structural templates are harvested. Managed pages are the broader runtime set: repository pages plus canonical CMS-created compositions.

A generated page can consume repository templates, participate in Pages/SEO/navigation/hierarchy, and be rebuilt deterministically, but it never feeds itself back into repository-source or template authority.

The browser never submits structural HTML. Template discovery stores trusted blocks plus only their exposed typed values. Surviving canonical leaf values persist across recomposition. New CMS routes are bounded lowercase root-level `.html` filenames using trusted shells; missing/self/cyclic parent relationships are rejected before persistence.

## Media, publishing, navigation, branding, and SEO

`media_library` stores canonical media identity/metadata while bytes remain adopter-owned assets. Raster uploads are byte-, MIME-, dimension-, and root-bounded; SVG may be cataloged when already present but is not accepted as an upload.

`posts` is canonical. Updates snapshot prior state; stale writes conflict rather than overwrite newer content. Drafts have no public article projection. Published posts materialize static HTML.

SEO is canonical in `seo_overrides`; custom canonicals stay on the configured public origin. Primary navigation is canonical in `site_navigation` and projects only into `<nav id="site-nav">`. Branding is canonical in `site_branding` but controls only identity text and CSS custom properties explicitly declared by the adopter.

## Reconciliation and rebuild

`php database/reconcile.php <source-ref>` compares repository candidates with canonical hashes and prior source lineage, accepts source changes only when canonical state has not diverged, preserves newer database edits, applies explicit update sets, then deterministically projects repository pages, compositions, published articles, SEO, navigation, branding, and bounded adopter hooks.

A Git pull therefore cannot silently undo accepted CMS state, and regeneration cannot silently erase composition or site-wide canonical state.

## Readiness

Readiness is observational. The browser endpoint is authenticated GET-only; the CLI report uses the same core model. It does not initialize/migrate schema, publish content, send email, deploy files, invoke shell commands, or return credential/grant values.

Portable checks cover PHP/configuration, production origin/security posture, MySQL availability/schema/owner state, remote TLS status, grant-metadata visibility, canonical content initialization, and bounded filesystem write targets. Host/provider checks belong in trusted repository-owned readiness adapters.

## Security and release status

See `SECURITY.md` for the administration, secret-handling, sanitization, and release-artifact security boundaries.

The reusable extraction is functionally complete through portable operability and internal candidate generation. The current candidate is suitable for review, not public distribution. A future public-release decision must deliberately address license selection, publication/version/tag mechanics, disclosure channels, and any desired distribution/deployment adapters rather than inheriting those choices from development automation.
