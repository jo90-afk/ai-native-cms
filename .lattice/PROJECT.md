# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `479389c02f9fb2b2a601a08b2678b5cc64d6ef85`
Working branch: `feat/composer-media`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-25 (America/New_York)**

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
- Media bytes remain adopter-owned files; the CMS may own canonical metadata, validation, catalog identity, and references to those files.
- Site identity, content, page/document registries, theme, article templates, and deployment secrets are adopter state, not product-core constants.
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

Merged at `479389c02f9fb2b2a601a08b2678b5cc64d6ef85` after GitHub Actions passed publishing/SEO behavior, structural contracts, syntax checks, and every prior milestone gate. Posts now use canonical MySQL state, optimistic revision hashes, restorable snapshots, bounded Markdown, static publish/unpublish projection, and canonical SEO overrides projected after article generation.

## Active objective

**OBJ-005 — Make structural page composition reusable without making HTML a browser-authored authority.**

Extract the production template/composer pattern and first-party media catalog so operators and agents can build pages from trusted structural blocks, typed values, and adopter-owned assets while preserving canonical leaf editing and deterministic projection.

## Active milestone

**M-005 — Typed composition survives ordinary edits, rebuilds, and repository reconciliation.**

Readiness conditions:

1. `composer.templates`: configured pages can be harvested into stable repository-owned templates whose exposed variables are bounded rich text, links, and media references.
2. `composer.authority`: canonical compositions store template identities, instance identities, and normalized typed values in `page_compositions`; stale composition saves are rejected by expected hash.
3. `composer.leaves`: composition saves namespace editable leaf IDs and preserve surviving canonical page-block values across reorder/recomposition instead of resetting copy.
4. `composer.integration`: Pages edits reproject composed structure, rebuild reapplies compositions, and repository block reconciliation skips composition-owned leaves while page-source documents continue to reconcile.
5. `media.catalog`: configured public media roots can be indexed into canonical metadata without moving adopter-owned file bytes into a second store.
6. `media.upload`: uploads are size/root bounded, verify actual raster image bytes, and accept JPEG/PNG/WebP/GIF only; cataloged existing SVG may be selected but SVG upload is not enabled.
7. `m005.ui`: native Composer and Media workspaces use the same owner/session/origin/CSRF/rate-limit/audit boundary and do not submit or render browser-authored structural HTML.
8. `m005.verification`: CI exercises composer/media behavior, integration contracts, PHP/JavaScript/Python syntax, and every earlier milestone invariant.

Implementation for these conditions is present on `feat/composer-media`; independent PR verification remains the current acceptance boundary.

## Deliberate M-005 boundary

M-005 composes only pages already declared by the adopter in `config/site.php`. Creating new routes, assigning parent hierarchy, mutating navigation, and applying site-wide branding remain separate work so structural composition can be accepted independently.

## Next frontier after M-005

Add canonical new-page hierarchy plus navigation and bounded site branding. Those capabilities should consume the merged template/composition/media contracts rather than broadening them.

## Release boundary

Technical readiness does not make the repository public. Visibility, license selection, tagged release, package distribution, production deployment, and adoption into an existing site remain separate Principal decisions.
