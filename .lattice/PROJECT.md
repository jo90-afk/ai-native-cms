# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `a9b9b7dc9f79d693acfaf2bd60140e767d85336b`
Working branch: `feat/content-authority`
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
- Site identity, content, page/document registries, theme, and deployment secrets are adopter state, not product-core constants.
- Path compatibility with the production implementation is valuable because it reduces the cost of upstreaming future general capabilities.
- Public-release artifacts must contain no personal content, credentials, private repository links, or adopter-specific operational state.
- Technical readiness and repository merge do not imply public visibility, licensing, tagged release, or production adoption.

## Satisfied milestone

**M-001 — Foundation is generic, executable, and safe to extend.**

Merged to `main` after GitHub Actions independently passed the public-release contract plus PHP/Python syntax checks. The foundation established the generic database/security runtime, schema-v7 model, adopter configuration seam, path-compatible upstreaming contract, and extraction matrix.

## Active objective

**OBJ-002 — Establish canonical content authority.**

Connect the generic runtime/schema to the product’s defining write model: canonical page/document state, revision-safe editing, three-way repository reconciliation, immutable explicit supersession, and deterministic static projection.

## Active milestone

**M-002 — Canonical page/document state survives every ordinary writer.**

Readiness conditions:

1. `authority.blocks`: configured HTML pages can be seeded into canonical `page_blocks` and full page-source documents.
2. `authority.editing`: authenticated page edits use optimistic hashes, sanitization, revision snapshots, and canonical SQL commits before projection.
3. `authority.reconcile`: repository candidates advance canonical SQL only when SQL still matches the prior effective source; divergent SQL is preserved and logged.
4. `authority.supersession`: immutable compare-and-swap update sets support deliberate corrections, with optional standing transforms for lagging repository source.
5. `authority.projection`: configured pages/documents deterministically project from canonical SQL while anonymous delivery remains database-free.
6. `authority.verification`: CI exercises pure content behavior and locks the reconciliation/authentication contract without depending on an adopter site or the later site-builder layer.

Implementation for all six conditions is present on `feat/content-authority`; independent PR verification remains the current acceptance boundary.

## Next frontier after M-002

Add the rebuild/projector extension contract and reusable CMS page-editing UI. Subsequent writing, SEO, templates/composer, media, navigation, branding, and hierarchy work must attach to the canonical authority rather than introducing parallel state.

## Release boundary

Technical readiness does not make the repository public. Visibility, license selection, tagged release, package distribution, production deployment, and adoption into an existing site remain separate Principal decisions.
