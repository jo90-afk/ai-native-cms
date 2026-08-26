# AI Native CMS

AI Native CMS is a static-first publishing system extracted from a production CMS into a site-neutral reusable core.

MySQL owns accepted authored state. Authenticated operators and agents mutate that state through the same guarded operations, and public HTML/JSON/XML are deterministic projections. Anonymous readers do not require a database connection.

## Why “AI native”

An AI agent operates the same durable model as a human editor instead of automating browser clicks or editing generated HTML blindly. Changes target typed content objects, carry provenance, preserve revision history, respect compare-and-swap guards, and rebuild deterministic public output.

There is no privileged agent authority system. Authentication, expected revisions, canonical state, provenance, validation, and projection boundaries apply regardless of caller.

## Current usable slice

The repository now contains a site-neutral CMS for repository pages, CMS-created composed pages, structured documents, first-party media, long-form Markdown publishing, canonical SEO, primary navigation, bounded site branding, portable schema/owner bootstrap, and read-only production-readiness diagnostics.

Core capabilities include:

- hardened PHP/MySQL owner authentication, HTTPS/origin/CSRF/session/rate-limit boundaries, and audit records;
- schema-v7 canonical content, revision, provenance, composition, media, navigation, branding, and SEO state;
- canonical editable page leaves plus full repository page-source documents;
- repository-owned page-block templates exposing bounded rich-text, safe-link, and media variables;
- canonical typed page compositions with optimistic hashes and deterministic reprojection;
- CMS-created root-level HTML pages built only from trusted repository shells/templates with validated parent hierarchy;
- canonical media metadata while adopter-owned image bytes remain in configured public roots;
- validated JPEG/PNG/WebP/GIF uploads constrained to configured roots and size limits;
- transactional long-form posts with immutable prior snapshots, stale-write rejection, revision restore, draft/published state, and static projection;
- bounded Markdown with escaped raw HTML and safe links;
- canonical SEO overrides reapplied after content projection;
- canonical primary navigation with bounded destinations, hierarchy-aware active state, and safe external-link projection;
- canonical site identity plus adopter-declared bounded CSS custom-property values rather than a core-owned design vocabulary;
- three-way repository reconciliation that preserves newer CMS state;
- immutable compare-and-swap release update sets;
- deterministic rebuild ordering plus bounded adopter projector hooks;
- a CLI-only idempotent database bootstrap that initializes schema plus the first owner without seeding adopter content;
- authenticated GET-only and CLI readiness reports covering portable runtime, database, canonical-state, and filesystem checks;
- trusted repository-owned readiness adapters for host-specific diagnostics;
- first-party `/cms/` workspaces for Pages, Composer, Media, Navigation, Branding, Writing, SEO, and Readiness with no frontend framework or third-party active runtime dependency.

The remaining work before a release decision is release engineering: installation/upgrade documentation, packaging and manifest mechanics, adversarial residue review, and reproducible release-candidate generation. Repository visibility, licensing, tagging, publication, deployment, and production adoption remain separate decisions.

## Product boundary

Reusable core includes the hardened runtime, canonical authored state and revisions, repository page sources, canonical CMS-created pages, posts, templates/compositions, media metadata, navigation, branding, SEO, deterministic projection, portable bootstrap, and portable readiness checks.

Adopter-owned state includes site copy/content seeds, repository page/document registry, theme assets and visual identity, the exact CSS custom properties exposed to Branding, article/site templates, media bytes, host-specific readiness adapters, custom deterministic projectors, deployment credentials, and provider-specific deployment behavior.

Optional extensions remain outside core when they are not general CMS concerns.

## Compatibility strategy

The repository intentionally keeps the reference implementation’s `api/`, `cms/`, and `database/` topology. General features can therefore move between an adopter site and this repository with small, reviewable diffs. Site-specific values enter through configuration or adapters rather than forks of core behavior.

See `docs/ARCHITECTURE.md`, `docs/UPSTREAMING.md`, and `docs/EXTRACTION-MATRIX.md`.

## Installation

Requirements:

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- Python 3.10+ for repository validation

Node is used only for repository-side JavaScript syntax validation; it is not a production runtime dependency.

1. Copy `config/site.example.php` to `config/site.php`. Define site identity/public origin, repository pages and documents, writing/media paths, optional navigation seed, branding tokens, projection outputs/hooks, and optional readiness adapters. This file contains public structure, never credentials.
2. Create a dedicated MySQL database and runtime user.
3. Put private settings in environment variables or an INI file outside the public root. `database/private-config.example.ini` documents the supported `AINCMS_*` values. Configure at least database access, the public origin, bootstrap owner username/password hash, and a rate-limit secret.
4. Ensure configured repository pages exist and mark editable text leaves with stable `data-cms-id` values. Public navigation projection targets `<nav id="site-nav">` when present; identity projection targets only the configured brand classes.
5. Initialize schema and the first persisted owner:

```bash
php database/bootstrap.php
```

Bootstrap is CLI-only and idempotent. It derives the current schema/table set from `database/schema.sql`, never seeds adopter content, never overwrites an existing owner credential, and refuses unrelated non-empty databases. A recognized incomplete installation requires explicit `--repair`; automatic repair is limited to incomplete installations already stamped with the current schema version. Older schemas require an explicit migration rather than being silently upgraded by bootstrap.

6. Initialize canonical repository content separately:

```bash
php database/reconcile.php initial-import
```

Keeping this separate makes the state transition explicit: schema/owner initialization does not silently accept repository content as canonical authored state.

7. Run the read-only readiness report:

```bash
php database/readiness.php
```

A ready report covers portable runtime/configuration, database connection/schema/owner/TLS posture, canonical content initialization, and writable in-root projection/media/branding targets. Host-specific checks may be added through trusted repository-owned adapters configured under `readiness.adapters`.

8. Serve the repository with PHP and open `/cms/` directly. The CMS does not require or add a public-site navigation link to itself. The **Readiness** workspace exposes the same diagnostics after authentication.

Once a persisted owner has successfully logged in, removing the bootstrap password hash from private configuration reduces the amount of recovery credential material left at rest.

## Repository pages, managed pages, and composition

Repository pages are declared in `cms.editable_pages`; they are the portable Git/source lineage and the only pages from which structural templates are harvested. Managed pages are the broader runtime set: repository pages plus canonical CMS-created compositions.

A generated page can consume repository templates, participate in Pages/SEO/navigation/hierarchy, and be rebuilt deterministically, but it never feeds itself back into repository-source or template authority.

The browser never submits structural HTML. Template discovery stores trusted top-level blocks in `page_block_templates` together with only the values each block exposes. A composition stores `templateKey`, stable instance ID, normalized typed values, shell path, browser title, and optional parent in `page_compositions`.

Surviving canonical leaf values are retained when blocks are reordered or recomposed. New CMS routes are bounded lowercase root-level `.html` filenames using trusted shells; missing/self/cyclic parent relationships are rejected before persistence.

## Media model

`media_library` stores canonical path identity, hashes, dimensions, MIME type, title, alt text, caption, and provenance while file bytes remain ordinary adopter-owned public assets.

Existing JPEG/PNG/WebP/GIF/SVG files in configured media roots may be cataloged. Uploads are intentionally narrower: JPEG, PNG, WebP, and GIF only, with actual MIME/dimension validation, byte limits, generated filenames, and writes constrained to `media.upload_root`. SVG upload is not enabled.

## Navigation and branding

Primary navigation is canonical in `site_navigation`. Saves carry an expected hash and validate item count, stable IDs, labels, and destinations. External navigation is HTTPS-only and projects with `target="_blank" rel="noopener noreferrer"`. Projection replaces only the contents of `<nav id="site-nav">` when present.

Branding is canonical in `site_branding`, but core does not own a universal theme schema. It manages identity text plus only CSS custom properties explicitly declared by the adopter. Tokens are bounded `color`, `number`, or `length` values; browser requests never contain arbitrary CSS.

## Publishing and SEO

`posts` is canonical. Updates snapshot the previous state to `post_revisions`; stale writes conflict instead of overwriting newer content. Drafts have no public article projection, while published posts materialize static HTML and enter the configured public index.

SEO state is canonical in `seo_overrides`. Custom canonicals are restricted to the configured public origin. Rebuild regenerates content first and then reapplies SEO so publishing/recomposition cannot erase deliberate metadata.

## Repository reconciliation and rebuild

`php database/reconcile.php <source-ref>` performs the portable authority sequence:

1. initialize configured repository page/document state when absent;
2. compare incoming repository candidates with canonical hashes and prior effective source hashes;
3. accept source changes only when canonical SQL still matches the prior source;
4. preserve divergent/newer canonical values and record incoming source lineage;
5. skip repository leaf reconciliation for composition-owned page structure while continuing to reconcile repository page-source documents;
6. apply immutable explicit update sets;
7. project canonical documents and repository pages;
8. project canonical compositions, including CMS-created pages;
9. project published articles and their public index;
10. reapply SEO overrides;
11. project canonical navigation;
12. project canonical branding;
13. run adopter projectors at their bounded phases.

This prevents a Git pull from silently undoing accepted CMS state and prevents regeneration from silently undoing composition, SEO, navigation, or branding state.

## Readiness model

Readiness is deliberately observational. The browser endpoint is authenticated GET-only; the CLI report uses the same core checks. Readiness does not initialize or migrate schema, publish content, send email, deploy files, invoke shell commands, or return credential values/grant contents.

Portable core checks cover PHP/configuration, production origin/security posture, MySQL availability and schema/owner state, remote TLS status, visibility of current-user grant metadata, canonical content-authority initialization, and bounded filesystem write targets.

Host/provider behavior belongs in trusted repository-owned readiness adapters. Adapter scripts and named callables must resolve inside the repository root; their output is normalized into the same pass/warn/fail report without granting them a second authored-state system.

## Rebuild hooks

Adopters may register trusted repository-owned projectors under `projection.hooks`. Hooks run only at bounded phases (`before_documents`, `after_documents`, `before_pages`, `after_pages`, `finalize`), scripts must resolve inside the repository root, and callables are explicitly named.

Hooks are deterministic presentation/integration work. They must not become alternate stores of authored truth.

## Development status

Pre-release extraction is functionally complete through portable operability. The current frontier is reproducible release-candidate preparation and adversarial packaging review.

Making the repository public, choosing a license, creating a tag/release, publishing a package, deploying to production, or adopting this core into an existing site are explicit release boundaries rather than automatic consequences of a successful development merge.
