# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `b3588304acdfc61faa541a1ffa65c1fb284d4513`
Working branch: `plan/source-v8-refresh`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-26 (America/New_York)**

## Confirmed mandate

Extract the reusable CMS architecture proven in the production personal-site implementation into a public-releasable, site-neutral product. Preserve its security, canonical-content, revision, composition, media, navigation, SEO, redirect, static-projection, and operability strengths while removing personal content and adopter-specific assumptions.

Routine reversible implementation, refactoring, tests, documentation, repository-side merges, upstream parity refreshes, and internal release-candidate preparation are delegated. Repository visibility, license selection, public/tagged release creation, package publication, production deployment, credentials, destructive data actions, and production adoption remain Principal boundaries.

## Durable product truths

- Anonymous public delivery is static-first and must not require MySQL.
- Accepted authored state resolves through one canonical SQL authority before projection.
- Repository source is a portable proposal/fixture; it must not silently overwrite newer accepted database state.
- Human and agent writers converge on the same guarded mutation contracts.
- Structural HTML is repository/template-owned; browsers and agents submit trusted template identities and bounded typed values, not arbitrary structure.
- Repository-authored pages and canonical CMS-created pages are distinct source classes; generated pages may consume templates but cannot become Git/template authority.
- Media bytes remain adopter-owned files while canonical metadata/references may live in SQL.
- Navigation, branding, publishing, SEO, and redirects are canonical authored state, not incidental generated-file edits.
- Database bootstrap owns structure and the first persisted owner only. It never seeds adopter content, replaces existing owner credentials, or silently migrates older schemas.
- Canonical repository content initialization is an explicit reconciliation step after bootstrap.
- Browser CMS operations may mutate canonical authored objects and rebuild accepted state, but must never promote a generated/live working tree back into repository-source authority.
- CMS mutation surfaces must fail closed when the installed schema is older than the code contract.
- Derived site-wide projection must have one explicit finalization boundary so one projector cannot erase another projector's accepted output until the next rebuild.
- Redirect authority may be canonical in SQL, but anonymous redirect requests remain static-first through a generated map/router or equivalent deployment adapter; public redirect handling may not query MySQL.
- Readiness is observational and may not initialize, migrate, publish, mail, deploy, invoke shell commands, or expose secret/grant values.
- Host/provider behavior enters only through bounded adopter/deployment adapters.
- Internal candidate generation is not publication. Candidate metadata keeps public distribution and license selection false until explicit Principal decisions change that boundary.
- Public-release artifacts contain no personal content, credentials, private repository/account identifiers, adopter-local configuration, or runtime/governance state.
- A release decision is evaluated against the current reusable production-source frontier. A newer production feature release that contains general CMS capability reopens delegated parity work before the Principal release gate.

## Satisfied milestones

### M-001 — Foundation is generic, executable, and safe to extend
Established the generic database/security runtime, schema-v7 model, adopter configuration seam, path-compatible upstreaming contract, and public-release guard.

### M-002 — Canonical page/document state survives every ordinary writer
Merged at `39e25616068567746e62bc9f46eb8da692975ce8`; added optimistic canonical editing, three-way reconciliation, immutable compare-and-swap update sets, and deterministic configured page/document projection.

### M-003 — An adopter can operate and extend the page CMS without forking core authority
Merged at `8c0c5258e05b6e7f10f6175fc194cbe0c6c13cb3`; added bounded trusted projector hooks and first-party secure Pages UI.

### M-004 — Long-form content and SEO remain reversible, safe, and deterministic
Merged at `479389c02f9fb2b2a601a08b2678b5cc64d6ef85`; added canonical posts/revisions, bounded Markdown, static publish/unpublish projection, and canonical SEO overrides.

### M-005 — Typed composition survives ordinary edits, rebuilds, and repository reconciliation
Merged at `0d7747819bf7f611e3615771c7f30e907d2136af`; added repository-owned templates, canonical typed compositions, leaf preservation across recomposition, and canonical media metadata with bounded raster uploads.

### M-006 — New-page hierarchy, navigation, and branding remain safe and deterministic across rebuilds
Merged at `4c875ae847e7a9a7871ddfab710c49c193f37a7b`; added trusted-shell page creation, validated parent hierarchy, canonical navigation/branding, and deterministic site-wide projection.

### M-007 — Portable bootstrap and readiness preserve authority boundaries
Merged via PR #7 at `993b3f0a89e6e062a165a6d4ee55a3ce50bac261`; added CLI-only schema/first-owner bootstrap, foreign/partial/current-version repair classification, explicit canonical repository import, read-only core/adapter readiness, and a first-party Readiness workspace.

### M-008 — A release candidate can be produced reproducibly without crossing the public-release boundary
Merged via PR #8 at `b3588304acdfc61faa541a1ffa65c1fb284d4513`; added deterministic internal candidate packaging, exact reviewed-head provenance, package residue guards, installation/rollback documentation, and private CI artifact review. The resulting `0.1.0-rc.1` candidate is schema 7 and remains private/unlicensed.

## Source delta intake — production release after M-008 roadmap

The production source repository advanced materially after the schema-7 candidate was prepared.

### Production PR #49 — reusable core delta

Merged in the source repository at `15a4f9acb1370ee6b7d979b1dd57767d6dfca31d` as **"Harden canonical authority and public projection boundaries."** The reusable portions are:

- schema v8 with canonical `redirect_records`;
- bounded same-site redirect normalization and allowed 301/302/307/308 status codes;
- redirect collision, reserved-path, ambiguity, conflict, and cycle rejection;
- canonical essay/post slug history routed through redirect authority instead of ad hoc static redirect pages;
- generated static redirect map/runtime so anonymous redirects remain database-free;
- read-only system/compatibility redirect sources merged with canonical manual redirects;
- one-time legacy redirect import kept at an explicit migration/preparation boundary rather than normal projection;
- browser maintenance restricted to rebuilding accepted state rather than Git-to-SQL reconciliation;
- current-schema preconditions on CMS mutations;
- one complete derived-state finalization boundary;
- SEO-before-sitemap ordering, with sitemap as the final search/discovery projection authority.

The source release also contains Bluehost-specific updater and reconstructible-deployment-manifest hardening. Those are deployment-adapter lessons, not portable CMS-core requirements.

### Production PR #50 — deployment-adapter delta

Merged in the source repository at `113068842a808ed00268892dc6a2ffa51c27ffa6`. It adds conservative Apache caching and DEFLATE compression while retaining private `no-store` behavior. The policy is useful, but its implementation is `.htaccess`/Apache-specific and therefore belongs in an optional transport/deployment adapter rather than canonical CMS core.

## Roadmap consequence

The prior **Principal release gate is suspended**. `0.1.0-rc.1` remains a valid reproducibility proof for the schema-7 extraction, but it is no longer the preferred parity candidate for public-release evaluation because the production proving ground now contains additional general CMS capability.

Do not change visibility, license, tag, package publication, or deployment state yet. Resume delegated extraction work first.

## Active objective

**OBJ-009 — Upstream the production schema-v8 redirect and projection hardening without importing site/host assumptions.**

The goal is not to copy PR #49 mechanically. Re-express its reusable behavior through the public core's existing typed, optimistic, site-neutral architecture and keep deployment-specific routing/preparation behind adapters.

## Active milestone

**M-009 — Canonical redirects and schema-v8 projection boundaries are portable, deterministic, and safe.**

Readiness conditions:

1. `schema.v8`: advance the public schema contract from 7 to 8 with canonical `redirect_records`; update bootstrap/readiness/release metadata/tests consistently. Bootstrap remains a fresh/current-version initializer, not a migration engine.
2. `migration.7-8`: add an explicit versioned schema-7 -> schema-8 migration path for existing internal/adopter installations. It must be idempotent, backup/rollback documented, and separate from `bootstrap --repair`.
3. `redirect.authority`: implement a site-neutral canonical redirect store with bounded same-site source/target normalization, allowed status codes, query-preservation behavior, active state, provenance/management class, notes, and public-core concurrency/revision protection appropriate to canonical global state.
4. `redirect.safety`: reject reserved application paths, ambiguous encoded separators, control characters, dot segments, source/target self-resolution, live managed-route collisions, duplicate conflicting authorities, and redirect cycles before persistence/projection.
5. `redirect.projection`: generate deterministic static redirect runtime data from canonical SQL plus explicitly registered read-only system aliases; anonymous redirect requests must never query MySQL. Core owns the map semantics; web-server interception of unresolved paths is a deployment-adapter contract.
6. `redirect.publishing`: when a published long-form slug changes, preflight and persist its old route through redirect authority, remove the obsolete public article projection, collapse safe post-managed chains, and preserve manually governed redirect ownership.
7. `redirect.ui`: add a first-party Redirects API/workspace to the existing CMS navigation. Manual canonical records are editable; system aliases are visible/read-only; all writes use the standard owner/origin/CSRF/rate-limit/audit boundary.
8. `authority.source-boundary`: keep repository reconciliation CLI/deployment-adapter-only. No browser endpoint may reconcile Git/source candidates into SQL or run schema migration opportunistically. Add a regression contract even though the public core already largely follows this shape.
9. `schema.write-guards`: every canonical CMS mutation surface must verify the current schema before writing, so older installations fail closed before partial mutation/projection.
10. `projection.finalizer`: establish one reusable core finalization boundary for site-wide derived state after content/composition/publishing. Preserve the public core's hook phases while guaranteeing deterministic ordering among SEO, sitemap/discovery adapters, navigation, branding, redirects, and other registered system projections.
11. `seo.sitemap-order`: keep SEO inventory broader than sitemap membership; sitemap/discovery projection must consume final SEO state and exclude noindexed targets while respecting safe same-site canonical replacement.
12. `m009.verification`: cumulative M-001–M-009 tests cover schema migration, redirect validation/graph behavior, slug-history integration, static redirect-map determinism, schema write guards, source-boundary restrictions, projection ordering, PHP/JS/Python syntax, and release-candidate packaging.

## Candidate consequence after M-009

After M-009 merges, produce a **new internal candidate identity** (expected `0.1.0-rc.2`, unless implementation findings justify another version) at schema 8. Re-run direct artifact inspection before restoring the Principal release gate. `0.1.0-rc.1` remains historical evidence and must not be silently mutated into the schema-v8 candidate.

## Follow-on adapter track — M-010

**M-010 — Reference deployment adapters encode redirect interception and bounded transport policy without becoming CMS authority.**

This is lower priority than M-009 and may remain optional for the first public release if documentation is sufficient.

Planned scope:

- host-neutral redirect-interception contract plus at least one clearly labeled reference adapter (Apache may be first because it is production-proven);
- conservative cache headers by asset mutability class;
- text compression when supported;
- explicit preservation of private/no-store behavior in private preview environments;
- no CDN requirement, no asset-filename hashing requirement, and no assumption that Apache is the only supported server;
- transport settings remain deployment configuration, never canonical authored CMS state.

## Standing upstream-parity rule

Before promoting any future internal candidate to a Principal public-release decision, compare the production proving-ground repository against the last recorded source-parity commit. Classify new deltas as:

- **core** — general CMS authority/authoring/projection behavior that should upstream before release;
- **adapter** — host/provider/transport/deployment behavior that may ship as optional reference integration;
- **site-only** — personal content, visual identity, portfolio-specific projection, or private operational evidence that must not upstream.

A material unresolved **core** delta reopens delegated extraction work. Adapter/site-only deltas do not automatically block release.

## Release boundary

No public-release authorization has been granted. Repository visibility, license selection, tag/GitHub Release creation, package publication, production deployment, and production adoption remain separate Principal decisions. The next valid Principal release gate occurs only after M-009 is accepted and a schema-v8 internal candidate has been reproduced and inspected.