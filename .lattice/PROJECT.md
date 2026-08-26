# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `b8ebd1e7b050a1b6a0831cea29e77db884be3240`
Working branch: `feat/m010-deployment-adapters`
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
- CMS mutation surfaces fail closed when the installed schema is older than the code contract.
- Derived site-wide projection has one explicit finalization boundary so one projector cannot erase another projector's accepted output until the next rebuild.
- Redirect authority is canonical in SQL, while anonymous redirect requests remain database-free through generated routing data plus a deployment adapter.
- Redirect graph-changing writes serialize and revalidate globally; optimistic record hashes remain the per-record stale-write boundary.
- Readiness is observational and may not initialize, migrate, publish, mail, deploy, invoke shell commands, or expose secret/grant values.
- Host/provider behavior enters only through bounded adopter/deployment adapters.
- Reference deployment adapters may ship with the source candidate but never become CMS authority or automatic deployment behavior.
- Internal candidate generation is not publication. Candidate metadata keeps public distribution and license selection false until explicit Principal decisions change that boundary.
- Public-release artifacts contain no personal content, credentials, private repository/account identifiers, adopter-local configuration, or runtime/governance state.
- Before a Principal release gate, compare against the current production proving-ground frontier. A material unresolved reusable core delta automatically reopens delegated parity work.

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
Merged via PR #8 at `b3588304acdfc61faa541a1ffa65c1fb284d4513`; added deterministic internal candidate packaging, exact reviewed-head provenance, package residue guards, installation/rollback documentation, and private CI artifact review. `0.1.0-rc.1` remains historical schema-7 evidence.

### M-009 — Canonical redirects and schema-v8 projection boundaries are portable, deterministic, and safe
Implementation entered `main` through PR #10 and verification-closure PR #11. The accepted implementation on PR #12's verification tree includes:

- schema v8 canonical `redirect_records` plus explicit CLI-only `database/migrations/7-to-8.php`;
- bounded same-site redirect normalization, reserved-path/encoded-separator/dot-segment/collision/conflict/cycle rejection, allowed 301/302/307/308 statuses, query-preservation state, and optimistic revision hashes;
- a bounded MySQL advisory lock around graph-changing redirect writes so concurrent new records cannot jointly create an invalid graph after independent preflight;
- published long-form slug history through redirect authority, including safe post-managed chain collapse while preserving manually governed ownership;
- deterministic `__redirect-map.php` plus database-free `__redirect.php` anonymous routing;
- configured read-only system aliases and a first-party guarded Redirects workspace;
- current-schema write guards on canonical mutation surfaces and no browser Git/source reconciliation path;
- one final public-projection boundary with `after_seo` available for sitemap/discovery adapters before navigation, branding, and redirect projection;
- rc.2 package inclusion of the explicit migration and static redirect runtime.

Cumulative validation run **#76** (`32994215474`) passed M-001 through M-010 contracts, all executable behavior tests, PHP/JavaScript/Python syntax, candidate build, and private artifact upload on verification commit `7f141f06390af75ad8fb47ba6d613cfa106d1372`. That commit has the same source tree as implementation head `ad3055083e746463d0fb0f13f93df5014a0b5fd6` and exists only to force exact-tree verification.

The run #76 `0.1.0-rc.2` artifact was directly inspected: whole-ZIP SHA256 `479ccd558de1f2741e9e92a4150579facbfb78adf404b909515deb30c9ca59be` matches the emitted checksum; embedded and external manifests are identical; manifest source revision is `7f141f06390af75ad8fb47ba6d613cfa106d1372`; schema version is 8; distribution remains `public:false` / `licenseSelected:false`; the 7→8 migration and redirect runtime are present; no excluded operational/adopter path, `LICENSE*`, known reference-adopter marker, private-key marker, GitHub token marker, or AWS access-key marker was found.

### M-010 — Reference deployment adapters encode redirect interception and bounded transport policy without becoming CMS authority
Technically accepted on the same run #76 verification tree. The source candidate now contains:

- a host-neutral deployment-adapter contract under `docs/DEPLOYMENT-ADAPTERS.md`;
- an Apache public reference that serves real files/directories first, routes unresolved requests to the static redirect runtime, denies direct map access, applies conservative cache lifetimes, and enables DEFLATE when available;
- an Apache private/preview reference that retains the same redirect interception/compression but forces `Cache-Control: no-store, private` and contains no public cache lifetimes;
- package/residue tests that treat `.example` deployment configuration as text and include adapters in the deterministic candidate;
- CI branch-push/manual-dispatch fallbacks so same-repository feature/fix/plan heads can still receive exact-head validation if PR event delivery is delayed.

No Apache assumption enters canonical CMS state. No CDN, asset fingerprinting, provider credential, automatic deployment, or access-control invention is part of M-010.

## Production proving-ground parity record

The parity refresh that created M-009/M-010 was based on:

- production PR #49, merged at `15a4f9acb1370ee6b7d979b1dd57767d6dfca31d`, containing reusable schema-v8 redirect/projection hardening plus site/host-specific updater work;
- production PR #50, merged at `113068842a808ed00268892dc6a2ffa51c27ffa6`, containing conservative Apache caching/compression policy.

The portable core and reference-adapter portions of those releases are now represented in the public product. Provider-specific updater/deployment machinery remains outside core.

## Current objective

**OBJ-011 — Re-establish the Principal public-release gate from a verified schema-v8 candidate.**

Before restoring the gate, perform one fresh comparison against current production `judeoneill.com` `main`. Classify intervening deltas as core / adapter / site-only. Any material unresolved reusable core delta reopens extraction. Adapter/site-only deltas do not automatically block release.

## Remaining non-core / post-release frontier

- Host repository updater / automated deployment: optional provider-specific adapter work, not required for core authority.
- Browser credential-writing setup: excluded from core; CLI bootstrap remains the portable security boundary.
- Newsletter/subscription: optional extension.
- Poetry-specific visual/content projection and portfolio/Lattice public views: adopter extensions, not public-core requirements.
- Future schema changes after a public release: must ship explicit source/target migrations; bootstrap repair never substitutes for migration.

## Release boundary

No public-release authorization has been granted. `0.1.0-rc.2` remains an **internal**, schema-v8 candidate. Repository visibility, license selection, tag/GitHub Release creation, package publication, production deployment, credentials, and production adoption remain separate Principal decisions.

After PR #12's final documentation head passes the same cumulative gate, its artifact is inspected, the PR is merged, and the fresh production-parity check finds no unresolved reusable core delta, the project returns to the Principal release gate.