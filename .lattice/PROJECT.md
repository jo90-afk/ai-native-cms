# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `7043eeee47bf4b0112957e7e3a6e564c5da1d020`
Working branch: `docs/restore-release-gate`
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
- Redirect sources that resolve to existing public files **or directories** cannot become active redirect authorities when deployment adapters serve real filesystem routes first.
- Conflicting configured read-only system aliases fail closed rather than using order-dependent last-write wins.
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
Implementation entered `main` through PR #10, verification-closure PR #11, M-010 PR #12, and the final directory-aware parity hardening in PR #13. The accepted schema-v8 core now includes:

- canonical `redirect_records` plus explicit CLI-only `database/migrations/7-to-8.php`;
- bounded same-site redirect normalization, reserved-path/encoded-separator/dot-segment/file-or-directory collision/conflict/cycle rejection, allowed 301/302/307/308 statuses, query-preservation state, and optimistic revision hashes;
- a bounded MySQL advisory lock around graph-changing redirect writes;
- published long-form slug history through redirect authority while preserving manually governed ownership;
- deterministic `__redirect-map.php` plus database-free `__redirect.php` anonymous routing;
- configured read-only system aliases with conflicting duplicate definitions rejected;
- current-schema write guards, no browser Git/source reconciliation path, and one final public-projection boundary with `after_seo` discovery/sitemap support.

### M-010 — Reference deployment adapters encode redirect interception and bounded transport policy without becoming CMS authority
Merged via PR #12 at `97d72a74491f66726b3c9a28da313d3753c89646`. The source candidate contains:

- a host-neutral deployment-adapter contract under `docs/DEPLOYMENT-ADAPTERS.md`;
- an Apache public reference that serves real files/directories first, routes unresolved requests to the static redirect runtime, denies direct map access, applies conservative cache lifetimes, and enables DEFLATE when available;
- an Apache private/preview reference that retains redirect interception/compression but forces `Cache-Control: no-store, private` and contains no public cache lifetimes;
- package/residue tests that scan `.example` deployment configuration and include adapters in the deterministic candidate;
- CI branch-push/manual-dispatch fallbacks for exact-head validation when PR event delivery is delayed.

PR #13 then merged at `7043eeee47bf4b0112957e7e3a6e564c5da1d020`, aligning filesystem collision semantics with the adapter contract and rejecting conflicting duplicate system aliases.

## Verification and rc.2 artifact evidence

- Run #76 (`32994215474`) first completed the cumulative M-001–M-010 gate on the implementation tree.
- Run #82 (`32994522688`) completed the same gate on PR #12's final documented head `5833123c21b4a3394ba8d87f0a3e1b06f24396a7`.
- The run #82 artifact directly inspected cleanly: whole-ZIP SHA256 `7a22d7a67cddaea7bc6dba88794b873cea3146de8664e360d7d273ef105b5547`; exact manifest provenance `5833123c21b4a3394ba8d87f0a3e1b06f24396a7`; embedded/external manifests identical; schema 8; `public:false`; `licenseSelected:false`; explicit 7→8 migration, redirect runtime, and both Apache reference adapters present; no excluded operational/adopter path, `LICENSE*`, known reference-adopter marker, private-key marker, GitHub-token marker, or AWS access-key marker.
- Run #88 (`32995003080`) passed the full cumulative gate on directory-aware hardening head `a9a47a14e9189dade11474a970eadf7ca594a188`.
- The run #88 artifact also inspected cleanly with exact provenance and ZIP SHA256 `57a4cb593e93a2d35c99683a54ec0ef2d5908f5d761470a3152ec1f466f04cad`.

## Production proving-ground parity

The source refresh was based on:

- production PR #49, merged at `15a4f9acb1370ee6b7d979b1dd57767d6dfca31d`, containing schema-v8 redirect/projection hardening plus site/host-specific updater work;
- production PR #50, merged at `113068842a808ed00268892dc6a2ffa51c27ffa6`, containing conservative Apache caching/compression policy.

A fresh post-M-010 parity check on **2026-08-26** found production `judeoneill.com` `main` still at `113068842a808ed00268892dc6a2ffa51c27ffa6`; there are no newer production commits to classify. The directory-aware routing behavior inside PR #49 was explicitly rechecked and the final public-core mismatch was closed by PR #13. No unresolved reusable **core** delta remains at this parity point.

Provider-specific updater/deployment machinery remains an optional adapter concern and does not block the public-source release decision.

## Current state — Principal release gate restored

The delegated extraction/parity work required for `0.1.0-rc.2` is complete. The project has returned to the **Principal release gate**.

The candidate remains **internal**, schema v8, private, unlicensed, untagged, unpublished, and undeployed. The next actions are not routine implementation choices; they require explicit Principal authorization:

1. select a public license, if public distribution is desired;
2. change repository visibility, if desired;
3. authorize a Git tag / GitHub Release / public package or download;
4. authorize any production deployment or adoption.

Any new production proving-ground release containing a material reusable core capability automatically suspends this gate and reopens parity work before publication.

## Remaining non-core / post-release frontier

- Host repository updater / automated deployment: optional provider-specific adapter work.
- Browser credential-writing setup: excluded from core; CLI bootstrap remains the portable security boundary.
- Newsletter/subscription: optional extension.
- Poetry-specific visual/content projection and portfolio/Lattice public views: adopter extensions.
- Future schema changes after a public release: must ship explicit source/target migrations; bootstrap repair never substitutes for migration.

## Release boundary

No public-release authorization has yet been granted. Repository visibility, license selection, tag/GitHub Release creation, package publication, production deployment, credentials, and production adoption remain separate Principal decisions.