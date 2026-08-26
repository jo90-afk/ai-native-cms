# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `993b3f0a89e6e062a165a6d4ee55a3ce50bac261`
Working branch: `feat/release-candidate`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-26 (America/New_York)**

## Confirmed mandate

Extract the reusable CMS architecture proven in a production personal-site implementation into a public-releasable, site-neutral product. Preserve its security, canonical-content, revision, composition, media, navigation, SEO, static-projection, and operability strengths while removing personal content and adopter-specific assumptions.

Routine reversible implementation, refactoring, tests, documentation, repository-side merges, and repository-side **internal release-candidate preparation** are delegated. Repository visibility, license selection, public/tagged release creation, package publication, production deployment, credentials, destructive data actions, and production adoption remain Principal boundaries.

## Durable product truths

- Anonymous public delivery is static-first and must not require MySQL.
- Accepted authored state resolves through one canonical SQL authority before projection.
- Repository source is a portable proposal/fixture; it must not silently overwrite newer accepted database state.
- Human and agent writers converge on the same guarded mutation contracts.
- Structural HTML is repository/template-owned; browsers and agents submit trusted template identities and bounded typed values, not arbitrary structure.
- Repository-authored pages and canonical CMS-created pages are distinct source classes; generated pages may consume templates but cannot become Git/template authority.
- Media bytes remain adopter-owned files while canonical metadata/references may live in SQL.
- Navigation, branding, publishing, and SEO are canonical authored state, not incidental generated-file edits.
- Database bootstrap owns structure and the first persisted owner only. It never seeds adopter content, replaces existing owner credentials, or silently migrates older schemas.
- Canonical repository content initialization is an explicit reconciliation step after bootstrap.
- Readiness is observational and may not initialize, migrate, publish, mail, deploy, invoke shell commands, or expose secret/grant values.
- Host/provider behavior enters only through bounded adopter/deployment adapters.
- Internal candidate generation is not publication. Candidate metadata keeps public distribution and license selection false until explicit Principal decisions change that boundary.
- A review candidate must be reproducible for a fixed source revision and identify the reviewed source head, not a synthetic CI merge commit.
- Public-release artifacts contain no personal content, credentials, private repository/account identifiers, adopter-local configuration, or runtime/governance state.
- Technical readiness, repository merge, or a green release-candidate artifact do not imply public visibility, licensing, tagging, publication, deployment, or adoption.

## Satisfied milestones

### M-001 — Foundation is generic, executable, and safe to extend
Established the generic database/security runtime, schema-v7 model, adopter configuration seam, path-compatible upstreaming contract, and public-release guard.

### M-002 — Canonical page/document state survives every ordinary writer
Merged at `39e25616068567746e62bc9f46eb8da692975ce8`; added optimistic canonical editing, three-way reconciliation, immutable compare-and-swap update sets, and deterministic configured page/document projection.

### M-003 — An adopter can operate and extend the page CMS without forking core authority
Merged at `8c0c5258e05b6e7f10f6175fc194cbe0c6c13cb3`; added bounded trusted projector hooks and first-party secure Pages UI while retaining prior invariants.

### M-004 — Long-form content and SEO remain reversible, safe, and deterministic
Merged at `479389c02f9fb2b2a601a08b2678b5cc64d6ef85`; added canonical posts/revisions, bounded Markdown, static publish/unpublish projection, and canonical SEO overrides.

### M-005 — Typed composition survives ordinary edits, rebuilds, and repository reconciliation
Merged at `0d7747819bf7f611e3615771c7f30e907d2136af`; added repository-owned templates, canonical typed compositions, leaf preservation across recomposition, and canonical media metadata with bounded raster uploads.

### M-006 — New-page hierarchy, navigation, and branding remain safe and deterministic across rebuilds
Merged at `4c875ae847e7a9a7871ddfab710c49c193f37a7b`; added trusted-shell page creation, validated parent hierarchy, canonical navigation/branding, and deterministic site-wide projection.

### M-007 — Portable bootstrap and readiness preserve authority boundaries
Merged via PR #7 at `993b3f0a89e6e062a165a6d4ee55a3ce50bac261`; added CLI-only schema/first-owner bootstrap, foreign/partial/current-version repair classification, explicit canonical repository import, read-only core/adapter readiness, and a first-party Readiness workspace across CMS navigation.

### M-008 — A release candidate can be produced reproducibly without crossing the public-release boundary

Accepted technically after cumulative run #54 passed every M-001–M-008 contract and behavior test, PHP/JavaScript/Python syntax, deterministic candidate build, and private artifact upload on reviewed head `a5ec63491ae03943f894700f389a2009174cf3d8`.

The accepted release-engineering design includes:

1. `release.identity`: `VERSION` and `release/release.json` define internal candidate `0.1.0-rc.1`, schema 7, runtime requirements, and explicit `public:false` / `licenseSelected:false` state.
2. `release.reproducibility`: `tools/build_release.py` emits a deterministic source ZIP, embedded/external per-file manifest, and whole-ZIP SHA256; CI tests two builds from the same source ref for byte equality.
3. `release.contents`: the candidate includes reusable runtime/config-example/schema/docs material while excluding `.git`, `.github`, `.lattice`, tests, release tooling, generated `dist`, runtime/uploads, adopter-local `config/site.php`, populated INI files, symlinks, and known secret/adopter markers.
4. `release.provenance`: PR CI passes `${{ github.event.pull_request.head.sha || github.sha }}` to the builder so manifests record the reviewed branch head rather than the synthetic PR merge SHA; this was added after direct inspection exposed the original provenance defect.
5. `release.license-boundary`: CI opens the built archive and rejects any `LICENSE*` entry while `licenseSelected:false`.
6. `release.installation`: `docs/INSTALLATION.md` defines fresh bootstrap -> explicit canonical import -> readiness, backup/restore testing, future migration requirements, and paired code/database rollback.
7. `release.private-review`: CI uploads the candidate only as a seven-day private workflow artifact; it does not tag, create a GitHub Release, publish a package, expose a public download, or deploy.
8. `release.adversarial-review`: the run #54 artifact was downloaded and inspected directly. Its manifest `sourceRevision` exactly equals `a5ec63491ae03943f894700f389a2009174cf3d8`; the emitted ZIP SHA256 matches recomputation; embedded and external manifests are identical; it contains 62 product files plus the embedded manifest; excluded paths are absent; no `LICENSE*` entry exists; only example/placeholder external origins were found; distribution remains private and unlicensed.
9. `release.migration-boundary`: because this is the first candidate, no prior public schema exists. A versioned migration layer becomes mandatory when a future released schema changes; bootstrap remains prohibited from substituting for migration.

The final post-acceptance documentation head still requires one cumulative validation run before PR #8 may merge.

## Adversarial review disposition

No unresolved **core candidate** defect remains after the provenance correction. The following are deliberately outside M-008 rather than release blockers:

- browser credential-writing setup — deployment adapter / additional credential surface;
- host repository updater and deployment automation — deployment adapters;
- newsletter and adopter-specific content/layout integrations — optional extensions;
- future schema migrations — required when a future released schema actually changes, not for this first candidate;
- repository visibility, license selection, release/tag creation, public package publication, production deployment/adoption — explicit Principal decisions.

## Active gate — Principal release decision

After PR #8 merges, ordinary delegated extraction/release-preparation work is exhausted. Further action crosses at least one recorded Principal boundary.

The Principal must explicitly decide any next release action, including separately as appropriate:

- whether the repository may become public;
- which license, if any, governs distribution;
- whether `0.1.0-rc.1` should remain an internal candidate or be replaced by another release identity;
- whether to create a Git tag/GitHub Release or publish any package/download;
- whether and where to deploy/adopt the core in production.

Until those decisions are made, the correct steady state is a private repository with a reproducible internal candidate and no public distribution.
