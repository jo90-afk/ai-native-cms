# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `4c875ae847e7a9a7871ddfab710c49c193f37a7b`
Working branch: `feat/bootstrap-readiness`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-26 (America/New_York)**

## Confirmed mandate

Extract the reusable CMS architecture proven in a production personal-site implementation into a public-releasable, site-neutral product. Preserve the production system’s security, canonical-content, revision, composition, media, navigation, SEO, and static-projection strengths while removing personal content and adopter-specific assumptions.

The product must be structured so general features developed first in an adopter site can move upstream with small, reviewable diffs instead of being reimplemented.

Routine reversible implementation, refactoring, tests, documentation, repository-side merges, and repository-side release preparation are delegated. Repository visibility changes, production deployment, credentials, destructive data actions, and final public-release licensing remain Principal boundaries.

## Durable product truths

- Anonymous public delivery is static-first and must not require MySQL.
- Accepted authored state resolves through one canonical authority before projection.
- Repository source is a portable proposal/fixture; it must not silently overwrite newer accepted database state.
- Human and agent writers converge on the same guarded mutation contracts rather than maintaining separate authority systems.
- Structural HTML is repository/template-owned. Browser and agent composition requests name trusted templates and bounded typed values rather than submitting arbitrary structure.
- Repository-authored pages and canonical CMS-created pages are distinct source classes: generated pages may consume repository templates but must not become their own Git source lineage or template authority.
- Media bytes remain adopter-owned files; the CMS may own canonical metadata, validation, catalog identity, and references to those files.
- Site-wide navigation and branding are canonical authored objects, not incidental edits to generated HTML.
- Branding core owns only bounded adopter-declared design tokens and identity text; it must not invent or assume one adopter’s CSS vocabulary.
- Database bootstrap owns structure and the first persisted owner only. It must not seed adopter content, overwrite an existing owner credential, or silently act as a schema migration.
- Canonical repository content initialization remains an explicit reconciliation step after schema/owner bootstrap.
- Production readiness is observational: core readiness checks and adopter-owned host adapters report state without initializing, migrating, publishing, mailing, deploying, or exposing secret values.
- Site identity, content seeds, repository page/document registry, theme assets, article templates, deployment credentials, and host-specific deployment behavior remain adopter state rather than product-core constants.
- Public-release artifacts must contain no personal content, credentials, private repository links, or adopter-specific operational state.
- Technical readiness and repository merge do not imply public visibility, licensing, tagged release, or production adoption.

## Satisfied milestones

### M-001 — Foundation is generic, executable, and safe to extend

Merged after GitHub Actions independently passed the public-release contract plus PHP/Python syntax checks. The foundation established the generic database/security runtime, schema-v7 model, adopter configuration seam, path-compatible upstreaming contract, and extraction matrix.

### M-002 — Canonical page/document state survives every ordinary writer

Merged at `39e25616068567746e62bc9f46eb8da692975ce8` after GitHub Actions passed the public-release guard, reconciliation contract, executable sanitization/block tests, PHP syntax, and Python syntax. The merged authority includes optimistic canonical page editing, three-way source reconciliation, immutable compare-and-swap update sets, and deterministic configured page/document projection.

### M-003 — An adopter can operate and extend the page CMS without forking core authority

Merged at `8c0c5258e05b6e7f10f6175fc194cbe0c6c13cb3` after GitHub Actions passed every prior invariant plus rebuild-hook, native CMS UI, PHP, JavaScript, and Python verification. The merged product has bounded trusted projector hooks and first-party secure Pages UI.

### M-004 — Long-form content and SEO remain reversible, safe, and deterministic

Merged at `479389c02f9fb2b2a601a08b2678b5cc64d6ef85` after GitHub Actions passed publishing/SEO behavior, structural contracts, syntax checks, and every prior milestone gate. Posts use canonical MySQL state, optimistic revision hashes, restorable snapshots, bounded Markdown, static publish/unpublish projection, and canonical SEO overrides projected after article generation.

### M-005 — Typed composition survives ordinary edits, rebuilds, and repository reconciliation

Merged at `0d7747819bf7f611e3615771c7f30e907d2136af` after the cumulative validation suite passed typed Composer/media contracts, executable primitives, PHP/JavaScript/Python syntax, and every earlier milestone gate. Repository-owned templates expose bounded rich-text/link/media variables; canonical compositions store template identities, instance identities, and normalized typed values with optimistic hashes; surviving leaf edits persist across recomposition; media metadata is canonical while adopter-owned bytes remain in configured public roots; uploads validate bounded raster image bytes.

### M-006 — New-page hierarchy, navigation, and branding remain safe and deterministic across rebuilds

Merged at `4c875ae847e7a9a7871ddfab710c49c193f37a7b` after the cumulative suite passed hierarchy/navigation/branding contracts, executable site-wide presentation behavior, and PHP/JavaScript/Python syntax. Repository pages remain the only Git/template authority; CMS-created pages use trusted shells/templates and validated parent relationships; navigation and branding are canonical optimistic state; rebuild re-applies SEO, navigation, and branding deterministically before adopter after-page projectors.

### M-007 — Portable bootstrap and readiness preserve authority boundaries

Accepted on PR #7 after cumulative validation run #44 passed every M-001–M-007 structural contract, every executable behavior test, and PHP/JavaScript/Python syntax on hardened implementation head `447bbc4e08e4628351c84f5b450eac6736a12d66`.

The accepted slice provides:

1. `bootstrap.schema`: `database/bootstrap.php` is CLI-only and derives required tables/current schema version from `database/schema.sql` instead of maintaining a second schema list.
2. `bootstrap.owner`: first-run initialization installs schema plus the first owner only; configured owner credentials are validated before DDL when an owner will be needed, and an existing owner credential is never overwritten.
3. `bootstrap.refusal`: unrelated non-empty databases are rejected. Partial repair requires explicit `--repair` and is limited to incomplete installations already stamped with the current schema version; older schemas require an explicit migration path.
4. `bootstrap.content-boundary`: bootstrap never seeds adopter content. Repository-authored canonical state still enters through `php database/reconcile.php initial-import`.
5. `readiness.core`: read-only diagnostics report runtime/configuration, database connection/schema/owner/TLS/grant visibility, canonical content initialization, and bounded filesystem projection capability without returning secrets or grant contents.
6. `readiness.adapters`: host/provider checks can be added only through explicitly configured trusted repository-owned scripts/callables resolved inside the repository root.
7. `readiness.non-mutating`: browser readiness is authenticated GET-only; CLI and browser reports do not initialize, migrate, publish, send mail, deploy, or invoke shell commands.
8. `m007.ui`: Readiness is a first-party CMS workspace and remains discoverable from every primary CMS workspace.
9. `m007.verification`: CI independently exercises SQL splitting, database-state classification, repair eligibility, path safety, readiness shape, all earlier milestone behavior, and language syntax.

## Active objective

**OBJ-008 — Prepare a releasable artifact without making release decisions implicitly.**

The reusable CMS core is now functionally extracted through portable operability. The next work should be release engineering and adversarial packaging review: ensure a fresh adopter can understand installation and operational boundaries, verify the private repository contains no adopter/private residue, define versioning/package mechanics, and identify any remaining general defects before the Principal chooses visibility, license, tag, distribution, or production adoption.

## Next milestone

**M-008 — A release candidate can be produced reproducibly without crossing the public-release boundary.**

Expected conditions:

1. installation and upgrade documentation describe bootstrap, reconciliation, readiness, backup/rollback, and the migration boundary without provider-specific assumptions;
2. repository/public-release guards cover private links, credentials, adopter identity/content, generated operational state, and packaging exclusions;
3. a deterministic release-candidate manifest identifies included files, required runtime versions, schema version, validation status, and excluded adopter/deployment state;
4. versioning and packaging mechanics can produce a reviewable candidate without changing repository visibility, selecting a license, publishing a package, creating a release/tag, or deploying to production;
5. final adversarial review distinguishes core defects from optional extensions and explicit Principal decisions.

## Release boundary

Technical readiness does not make the repository public. Repository visibility, license selection, release/tag creation, package publication, production deployment, and adoption into an existing site remain separate Principal decisions.
