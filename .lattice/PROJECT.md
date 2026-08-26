# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Working branch: `feat/public-cms-foundation`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-25 (America/New_York)**

## Confirmed mandate

Extract the reusable CMS architecture proven in a production personal-site implementation into a public-releasable, site-neutral product. Preserve the production system’s security, canonical-content, revision, composition, media, navigation, SEO, and static-projection strengths while removing personal content and adopter-specific assumptions.

The product must be structured so general features developed first in an adopter site can move upstream with small, reviewable diffs instead of being reimplemented.

Routine reversible implementation, refactoring, tests, documentation, and repository-side release preparation are delegated. Repository visibility changes, production deployment, credentials, destructive data actions, and final public-release licensing remain Principal boundaries.

## Durable product truths

- Anonymous public delivery is static-first and must not require MySQL.
- Accepted authored state resolves through one canonical authority before projection.
- Repository source is a portable proposal/fixture; it must not silently overwrite newer accepted database state.
- Human and agent writers must converge on the same guarded mutation contracts rather than maintaining separate authority systems.
- Site identity, content, page registries, theme, and deployment secrets are adopter state, not product-core constants.
- Path compatibility with the production implementation is valuable because it reduces the cost of upstreaming future general capabilities.
- Public-release artifacts must contain no personal content, credentials, private repository links, or adopter-specific operational state.

## Active objective

**OBJ-001 — Establish the public-core extraction boundary.**

Create a runnable, testable foundation that preserves the proven database/runtime model, externalizes site-specific configuration, documents the upstream contract, and can accept subsequent CMS modules without architectural rework.

## Active milestone

**M-001 — Foundation is generic, executable, and safe to extend.**

Readiness conditions:

1. `foundation.runtime`: generic database and HTTP/auth runtime no longer depends on personal-site identifiers.
2. `foundation.schema`: canonical schema represents reusable CMS state without authored personal seed content.
3. `foundation.adapter`: editable-page/site identity differences are supplied by configuration or adapters.
4. `foundation.upstream`: path-compatible upstreaming rules are explicit and regression-enforceable.
5. `foundation.release-contract`: CI rejects known personal identifiers/secrets and syntax failures.
6. `foundation.next-frontier`: extraction matrix identifies the next reusable modules and deliberately excluded site-specific features.

## Release boundary

Technical readiness does not make the repository public. Visibility, license selection, tagged release, package distribution, and production adoption are separate Principal decisions.
