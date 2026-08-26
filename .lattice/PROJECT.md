# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `8c0c5258e05b6e7f10f6175fc194cbe0c6c13cb3`
Working branch: `feat/publishing-seo`
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

## Active objective

**OBJ-004 — Add durable long-form publishing and canonical SEO.**

Make posts a first-class authored object using the same revision-safe, static-first model as pages, and make search/social metadata survive every deterministic rebuild rather than living only in generated HTML.

## Active milestone

**M-004 — Long-form content and SEO remain reversible, safe, and deterministic.**

Readiness conditions:

1. `publishing.store`: posts are canonical in MySQL; updates preserve prior snapshots and reject stale expected revision hashes.
2. `publishing.markdown`: Markdown is rendered through an escaped bounded subset with safe-link enforcement and no raw-HTML passthrough.
3. `publishing.projection`: draft/published state controls static article materialization and the public post index; adopter-owned article templates remain configuration rather than core identity.
4. `publishing.restore`: a prior post revision can become the new current canonical value without erasing the previously current version.
5. `seo.authority`: SEO overrides are canonical database state, canonicals are restricted to the configured public origin, and structured robots/social controls are projected into static HTML.
6. `seo.rebuild`: core rebuild ordering is page projection -> published posts -> SEO overrides -> adopter after-page projectors.
7. `m004.ui`: native Writing and SEO workspaces use the same authentication/CSRF boundary and avoid third-party active runtime or browser HTML injection paths.
8. `m004.verification`: CI exercises publishing/SEO behavior, contracts, PHP syntax, JavaScript syntax, and all earlier milestones.

Implementation for these conditions is present on `feat/publishing-seo`; independent PR verification remains the current acceptance boundary.

## Next frontier after M-004

Extract reusable page-block templates/composition together with the first-party media library. Typed template variables can then reference canonical media without allowing browser-authored structural HTML.

## Release boundary

Technical readiness does not make the repository public. Visibility, license selection, tagged release, package distribution, production deployment, and adoption into an existing site remain separate Principal decisions.
