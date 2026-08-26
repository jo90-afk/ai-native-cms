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
- Branding core controls only adopter-declared bounded design tokens and identity text.
- Database bootstrap owns structure and the first persisted owner only. It never seeds adopter content, replaces existing owner credentials, or silently migrates older schemas.
- Canonical repository content initialization is an explicit reconciliation step after bootstrap.
- Readiness is observational and may not initialize, migrate, publish, mail, deploy, invoke shell commands, or expose secret/grant values.
- Host/provider behavior enters only through bounded adopter/deployment adapters.
- Internal candidate generation is not publication. Candidate metadata must keep public distribution and license selection false until explicit Principal decisions change that boundary.
- A review candidate must be reproducible for a fixed source revision and must identify the reviewed source head, not a synthetic CI merge commit.
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
Merged via PR #7 at `993b3f0a89e6e062a165a6d4ee55a3ce50bac261` after cumulative validation passed on the final head. The merged slice includes CLI-only schema/first-owner bootstrap, foreign/partial/current-version repair classification, explicit canonical repository import, read-only core/adapter readiness, and a first-party Readiness workspace across CMS navigation.

## Active objective

**OBJ-008 — Prepare a reproducible review candidate without making release decisions implicitly.**

Release engineering should make the source candidate inspectable, reproducible, attributable to an exact reviewed commit, and free of adopter/private/runtime residue. It must also make installation, backup, migration boundaries, and rollback explicit while keeping every public-release decision separate.

## Active milestone

**M-008 — A release candidate can be produced reproducibly without crossing the public-release boundary.**

Readiness conditions:

1. `release.identity`: `VERSION` and `release/release.json` define one internal candidate identity and schema/runtime contract; metadata explicitly keeps `public:false` and `licenseSelected:false`.
2. `release.reproducibility`: `tools/build_release.py` produces a deterministic source ZIP, embedded/external manifest, and whole-ZIP SHA256; two builds from the same source ref are byte-for-byte identical.
3. `release.contents`: the source candidate contains reusable runtime/config-example/schema/docs material while excluding `.git`, `.github`, `.lattice`, tests, release tooling, generated `dist`, runtime/uploads, adopter-local `config/site.php`, populated INI files, symlinks, and known secret/adopter markers.
4. `release.provenance`: CI candidate manifests record the reviewed PR head (or exact main push SHA), never GitHub’s synthetic PR merge SHA.
5. `release.license-boundary`: no `LICENSE*` file may enter the candidate while `licenseSelected:false`; candidate generation cannot choose licensing by accident.
6. `release.installation`: documentation covers fresh schema/owner bootstrap, separate canonical import, readiness, backup/restore testing, the absence of a prior-public-schema migration obligation for the first candidate, future explicit migration requirements, and paired code/database rollback.
7. `release.private-review`: CI may upload the candidate only as a short-lived private workflow artifact for inspection; it must not create a tag, GitHub Release, public package, public download, or deployment.
8. `release.adversarial-review`: inspect the actual CI-built archive and manifest, not only the builder code, for checksum agreement, source provenance, included/excluded files, license state, adopter/private residue, and suspicious external URLs.
9. `m008.verification`: cumulative M-001–M-008 contracts, behavior tests, PHP/JavaScript/Python syntax, candidate build, and private artifact upload pass on the final PR head.

## Current M-008 evidence

- PR #8 is open as a draft on `feat/release-candidate` against the exact M-007 merge baseline.
- Internal candidate identity is `0.1.0-rc.1`, schema 7.
- Cumulative CI run #49 passed the initial M-008 implementation and produced a private candidate artifact.
- Direct archive inspection found correct exclusions, matching ZIP checksum, identical embedded/external manifests, no license file, and no known adopter/host residue, but also found a real provenance defect: the manifest recorded GitHub’s synthetic PR merge SHA.
- That defect was corrected by making CI pass `${{ github.event.pull_request.head.sha || github.sha }}` to the builder and locking the expression in the release contract.
- Cumulative run #51 passed on hardened head `60413166dc3ce8933f8d14821d06d168a2882711`.
- The run #51 private artifact was downloaded and directly inspected: manifest `sourceRevision` exactly equals `60413166dc3ce8933f8d14821d06d168a2882711`; external and embedded manifests are identical; the emitted SHA256 matches the candidate ZIP; the archive has 62 product files plus the embedded manifest; excluded paths are absent; no `LICENSE*` entry exists; distribution remains `public:false`, `licenseSelected:false`.
- Documentation and product-map alignment after that artifact inspection are now being finalized. The exact final head must receive one more cumulative run before PR #8 can be accepted.

## Adversarial review conclusion so far

No unresolved **core candidate** defect has been identified after correcting provenance. The following are deliberately outside M-008 rather than release blockers:

- **Future schema migrations:** `0.1.0-rc.1` is the first candidate, so there is no prior public schema to migrate. A versioned migration layer becomes mandatory when a future released schema changes; bootstrap remains prohibited from filling that role.
- **Browser credential-writing setup:** excluded from core because it broadens credential-handling attack surface and tends to embed provider assumptions. CLI/env/private-INI setup is the portable baseline.
- **Host repository updater/deployment automation:** deployment adapter, not CMS authority.
- **Newsletter and adopter-specific content/layout integrations:** optional extensions.
- **License, repository visibility, release/tag creation, public package publication, production deployment/adoption:** explicit Principal decisions.

## Release boundary

A successful M-008 merge may establish that an internal candidate is reproducible and reviewable. It does **not** authorize making the repository public, selecting a license, creating a tag/release, publishing the package, deploying to production, or adopting it into an existing site.
