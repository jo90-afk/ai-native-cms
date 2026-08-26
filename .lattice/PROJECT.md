# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `0d7747819bf7f611e3615771c7f30e907d2136af`
Working branch: `feat/hierarchy-navigation-branding`
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
- Human and agent writers must converge on the same guarded mutation contracts rather than maintaining separate authority systems.
- Structural HTML is repository/template-owned. Browser and agent composition requests name trusted templates and bounded typed values rather than submitting arbitrary structure.
- Repository-authored pages and canonical CMS-created pages are distinct source classes: generated pages may consume repository templates but must not become their own Git source lineage or template authority.
- Media bytes remain adopter-owned files; the CMS may own canonical metadata, validation, catalog identity, and references to those files.
- Site-wide navigation and branding are canonical authored objects, not incidental edits to generated HTML.
- Branding core owns only bounded adopter-declared design tokens and identity text; it must not invent or assume one adopter’s CSS vocabulary.
- Site identity, content seeds, repository page/document registry, theme assets, article templates, and deployment secrets remain adopter state rather than product-core constants.
- Path compatibility with the production implementation is valuable because it reduces the cost of upstreaming future general capabilities.
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

## Active objective

**OBJ-006 — Make site-wide structure canonical without collapsing repository source and CMS-generated state.**

Allow operators and agents to create new pages from trusted shells/templates, organize them into a validated hierarchy, control the primary navigation, and apply bounded site identity/design tokens while preserving static deterministic delivery and the source-authority split established by earlier milestones.

## Active milestone

**M-006 — New-page hierarchy, navigation, and branding remain safe and deterministic across rebuilds.**

Readiness conditions:

1. `pages.source-classes`: repository-configured pages remain the only page-source/template-harvest authority, while canonical composed pages join the broader managed-page graph for editing, SEO, hierarchy, navigation, and projection.
2. `pages.create`: Composer can create a bounded root-level HTML route from a trusted repository shell and typed templates without accepting browser-authored structural HTML; shell title/canonical/social URL metadata is retargeted to the new route.
3. `pages.hierarchy`: canonical compositions carry optional parent relationships; invalid parents, self-parenting, and cycles are rejected before persistence/projection.
4. `navigation.authority`: primary navigation is canonical SQL state with bounded labels/URLs/item count, safe external-link handling, optimistic hashes, revision snapshots, and hierarchy-aware active states.
5. `branding.authority`: site identity plus only adopter-declared CSS custom properties are canonical SQL state; colors/numbers/lengths are validated and bounded, stale saves are rejected, and arbitrary CSS/HTML is never browser-authored.
6. `site-wide.projection`: deterministic rebuild order is repository pages -> compositions -> published articles -> SEO -> navigation -> branding -> adopter after-page projectors; navigation parent state is loaded once per projection rather than once per page.
7. `m006.ui`: Composer new-page controls plus Navigation and Branding workspaces use the same owner/session/origin/CSRF/rate-limit/audit boundary, and every primary CMS workspace exposes the site-wide controls in its header.
8. `m006.verification`: CI exercises hierarchy/navigation/branding structural and executable behavior, PHP/JavaScript/Python syntax, and every earlier milestone invariant on the final PR head.

Implementation for these conditions is present on `feat/hierarchy-navigation-branding`. PR #6 may merge only after the cumulative validation suite passes on its final head.

## Next frontier after M-006

Build the portable setup/bootstrap and production-readiness layer. The next coherent slice should distinguish core readiness checks from host-specific adapters, provide a safe first-run path for database/schema/owner initialization, and avoid embedding any hosting-provider identity in core.

## Release boundary

Technical readiness does not make the repository public. Visibility, license selection, tagged release, package distribution, production deployment, and adoption into an existing site remain separate Principal decisions.
