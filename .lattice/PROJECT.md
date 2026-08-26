# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `39e25616068567746e62bc9f46eb8da692975ce8`
Working branch: `feat/rebuild-page-ui`
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

## Satisfied milestones

### M-001 — Foundation is generic, executable, and safe to extend

Merged after GitHub Actions independently passed the public-release contract plus PHP/Python syntax checks. The foundation established the generic database/security runtime, schema-v7 model, adopter configuration seam, path-compatible upstreaming contract, and extraction matrix.

### M-002 — Canonical page/document state survives every ordinary writer

Merged at `39e25616068567746e62bc9f46eb8da692975ce8` after GitHub Actions passed the public-release guard, reconciliation contract, executable sanitization/block tests, PHP syntax, and Python syntax. The merged authority now includes optimistic canonical page editing, three-way source reconciliation, immutable compare-and-swap update sets, and deterministic configured page/document projection.

## Active objective

**OBJ-003 — Make the canonical core operable and extensible.**

Provide a stable rebuild extension boundary for adopter-specific deterministic projectors and a native operator surface that uses the same guarded authentication and page-mutation contracts as automated callers.

## Active milestone

**M-003 — An adopter can operate and extend the page CMS without forking core authority.**

Readiness conditions:

1. `rebuild.registry`: core projection has named bounded phases for trusted repository-owned adopter hooks.
2. `rebuild.boundary`: hook scripts must resolve inside the repository root and execute named callables without shell/eval dispatch.
3. `ui.auth`: the native CMS exposes secure login/logout through the existing HTTPS, session, same-origin, CSRF, rate-limit, and audit boundary.
4. `ui.pages`: authenticated operators can select configured pages, edit canonical blocks, detect stale hashes, save, and receive conflicts through the guarded page API.
5. `ui.runtime`: the CMS interface has no third-party active runtime dependency and does not introduce an HTML-injection rendering path.
6. `m003.verification`: CI validates the rebuild/UI contract, JS syntax, PHP syntax, and all prior milestone contracts.

Implementation for all six conditions is present on `feat/rebuild-page-ui`; independent PR verification remains the current acceptance boundary.

## Next frontier after M-003

Extract long-form writing/publishing and SEO controls onto the same canonical/projection runtime. Later templates/composer, media, navigation, branding, hierarchy, readiness, and setup work must attach to these shared contracts rather than introduce parallel state.

## Release boundary

Technical readiness does not make the repository public. Visibility, license selection, tagged release, package distribution, production deployment, and adoption into an existing site remain separate Principal decisions.
