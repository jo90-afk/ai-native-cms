# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `c63dfc211d89bf046b8c48acab8bfa20005d5ecf`
Working branch: `feat/m011-license-onboarding-foundation`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-26 (America/New_York)**

## Confirmed mandate

Maintain a public-releasable, site-neutral AI-native CMS extracted from a production proving ground. Preserve canonical SQL authority, repository/template ownership, static public delivery, revisions/provenance, explicit migrations, deterministic projection, and bounded host adapters. Make the product approachable enough that a new adopter can create, operate, deploy, and iteratively evolve a site with human or LLM assistance without learning or bypassing the internal authority model.

Routine reversible implementation, refactoring, tests, documentation, repository-side merges, proving-ground parity refreshes, internal release-candidate preparation, and pre-release usability/rehearsal work are delegated. Repository visibility, public/tagged release creation, package publication, production deployment/adoption, credentials, and destructive data actions remain Principal boundaries.

The Principal selected **Apache License 2.0 subject to Commons Clause License Condition v1.0**. The project must describe these terms as **source-available**, not OSI-approved open source. Use, modification, derivative works, attribution-preserving redistribution, and commercial use are permitted subject to the Commons Clause restriction on selling the CMS itself or a product/service whose value derives entirely or substantially from the CMS functionality.

## Durable product truths

- Anonymous public delivery is static-first and does not require MySQL.
- Accepted authored state resolves through one canonical SQL authority before projection.
- Git owns application code, structural templates/shells, CSS/JS behavior, schema/migrations, tests, documentation, adapters, and public non-secret adopter configuration.
- Repository source is a proposal/fixture for canonical content; it may not silently overwrite newer accepted database state.
- Generated HTML/JSON/XML/indexes/redirect maps are outputs, never reverse source authority.
- Host/provider credentials, rewrites, caching, document-root details, and deployment mechanisms remain explicit operator/adapter state.
- Human and agent writers converge on the same guarded mutation contracts.
- Structural HTML stays repository/template-owned; browsers and agents submit trusted template identities and bounded typed values, not arbitrary structure.
- Repository-authored pages and CMS-created pages remain distinct source classes; generated pages cannot become repository/template authority.
- Media bytes remain adopter-owned files while canonical metadata/references may live in SQL.
- Navigation, branding, publishing, SEO, redirects, and other accepted authored objects are canonical state, not incidental generated-file edits.
- Database bootstrap owns schema structure and the first persisted owner only. It never seeds adopter content, replaces persisted owner credentials, or migrates older schemas.
- Friendly onboarding begins after secure bootstrap/auth. Browser onboarding never writes credentials, runs migrations, executes shell commands, deploys, or creates a second onboarding state store.
- Canonical repository content initialization remains an explicit reconciliation step after bootstrap.
- CMS mutation surfaces fail closed when the installed schema is older than the code contract.
- Derived site-wide projection has one explicit finalization boundary.
- Redirect authority is canonical in SQL; anonymous redirect requests use generated database-free routing state through a deployment adapter.
- Redirect graph-changing writes serialize/revalidate globally; optimistic hashes protect per-record stale writes; sources colliding with real public files/directories or conflicting authorities fail closed.
- Readiness is observational. It may not initialize, migrate, publish, mail, deploy, invoke shell commands, or expose secret/grant values.
- Reference deployment adapters may ship in the candidate but never become CMS authority or automatic deployment behavior.
- GitHub branches/PRs are the durable review surface for repository-owned changes.
- LLM collaboration preserves human governance: inspect current state first, identify the owning authority, work through branches or canonical CMS contracts, add tests/docs, and summarize durable state changes. Conversation history is context, not authority.
- Internal candidate generation is not publication. A selected license does not make a candidate public.
- Public candidates contain no personal content, credentials, private repository/account identifiers, adopter-local secret configuration, or runtime/governance state.
- Before any publication decision, compare with the current production proving-ground frontier. A material unresolved reusable core delta automatically reopens delegated parity work.

## Satisfied milestones

### M-001 — Generic executable foundation
Generic runtime/database/security seams, adopter configuration, schema foundation, Lattice capsule, CI, and public-release sanitization.

### M-002 — Canonical page/document authority
Optimistic canonical editing, revisions, three-way repository reconciliation, immutable compare-and-swap update sets, and deterministic page/document projection.

### M-003 — Operable page CMS and bounded projector hooks
First-party Pages workspace plus trusted repository-owned projection hooks without creating a second write model.

### M-004 — Long-form publishing and SEO
Canonical posts/revisions, bounded Markdown, draft/published static projection, restore, SEO overrides, same-origin canonicals, and deterministic ordering.

### M-005 — Typed composition and media
Repository-owned structural templates, typed canonical compositions, value preservation through template evolution, and bounded first-party media catalog/upload behavior.

### M-006 — CMS-created pages, hierarchy, navigation, and branding
Trusted-shell page creation, parent-cycle validation, canonical navigation/branding, bounded design tokens, and deterministic site-wide projection.

### M-007 — Portable bootstrap and readiness
CLI-only schema/first-owner bootstrap, explicit canonical import, current/partial/foreign classification, read-only readiness core and adapter seam, and first-party Readiness workspace.

### M-008 — Reproducible internal packaging
Deterministic ZIP/manifest/SHA256, exact reviewed-head provenance, residue guards, installation/backup/rollback documentation, and private CI artifact review.

### M-009 — Schema-v8 redirects and projection boundaries
Explicit 7→8 migration, canonical redirects, safe graph validation/concurrency, post-slug history, static database-free redirect routing, current-schema write guards, source-promotion boundary, and unified final public projection.

### M-010 — Reference deployment adapters
Provider-neutral deployment-adapter contract plus public/private Apache examples for unresolved-path redirect interception, conservative public caching/compression, and private `no-store` behavior.

### OBJ-011 — Schema-v8 parity closure
Production proving-ground parity was rechecked after M-009/M-010; no unresolved reusable core delta remained at the recorded frontier. The schema-v8 rc.2 line was green and reproducible before usability work reopened the pre-release sequence.

### M-011 — Friendly onboarding and coherent starter site
**Technically satisfied on rc.3 implementation head `f8a8a19ca2ca6fd9df44bda1b350ba6df00f9856`, cumulative run #155 (`33001221021`).**

Delivered:

- neutral, immediately browsable Home/About/Writing starter site with responsive design system and article template;
- `setup/site.php`, a CLI-only non-secret public site initializer that preserves the shipped config contract and refuses accidental overwrite;
- authenticated read-only onboarding state derived from actual config/bootstrap/canonical-content/branding/navigation/writing/readiness state rather than a one-time completion flag;
- placeholder identity cannot count as completed onboarding;
- first-login and authenticated-entry routing sends unfinished sites to Onboarding and configured/ready sites to normal Pages;
- Onboarding remains discoverable from every operator workspace;
- browser onboarding never writes credentials, migrates schema, executes shell commands, promotes Git source into SQL, or creates arbitrary structural state;
- dedicated structural and executable onboarding/site-setup tests.

### M-012 — Repository and hosting operations are understandable and reversible
**Technically satisfied on the same rc.3 implementation head/run.**

`docs/REPOSITORY-OPERATIONS.md` now defines:

- Git vs canonical SQL vs generated projection vs host/provider state;
- starting an adopter repository with the safe public config initializer;
- branch/PR workflow;
- SSH pull-to-host and reviewed-artifact/copy deployment patterns;
- secret placement, first deployment, canonical-content handling, host hotfix recovery, backups/migrations, readiness, paired rollback, and provider capability checklist;
- LLM-assisted repository changes still deploy only after normal review/merge verification.

### M-013 — LLM collaboration accelerates iteration without replacing governance
**Technically satisfied on the same rc.3 implementation head/run.**

Delivered:

- root `AGENTS.md` as an agent-discoverable repository contract;
- `docs/LLM-COLLABORATION.md` with the four authority classes and reusable request packets for design, content, feature, bug, schema/migration, and release work;
- explicit rules for branch-first repository changes, guarded canonical content changes, no reverse authority from generated HTML, explicit migrations, no secret handling, test/doc updates, release residue/parity checks, and conversation history as context rather than authority;
- release packaging includes these governance documents.

## rc.3 implementation verification evidence

Cumulative run **#155** (`33001221021`) passed on exact source head `f8a8a19ca2ca6fd9df44bda1b350ba6df00f9856`:

- all M-001–M-010 structural contracts;
- rc.3 release candidate contract;
- onboarding/governance contract;
- all executable PHP behavior tests, including onboarding/site setup;
- PHP, JavaScript, and Python syntax;
- deterministic internal rc.3 build and private artifact upload.

The run #155 artifact was directly inspected:

- version `0.1.0-rc.3`, schema 8;
- exact source provenance `f8a8a19ca2ca6fd9df44bda1b350ba6df00f9856`;
- candidate ZIP SHA256 `4ed0930c3483719f13b074f50bf3b6667905f84e33d4073476f0f4390345a2c9`, matching the emitted checksum;
- embedded and external manifests identical;
- `public:false`, `licenseSelected:true`, Apache-2.0 + Commons Clause v1.0 metadata correct;
- license/NOTICE, starter site, design assets, article template, safe site initializer, onboarding API/UI, `AGENTS.md`, repository/LLM guides, v7→8 migration, redirect runtime, and Apache adapters present;
- no excluded operational paths, adopter-local `config/site.php`, known personal/reference residue, private-key markers, GitHub token markers, or AWS access-key markers.

M-011–M-013 are accepted as implementation-complete subject to the final documentation-head cumulative gate and PR merge.

## Active milestone — M-014

### M-014 — Clean empty-site release rehearsal proves product, docs, deployment, and agent workflow together

Acceptance conditions:

1. Start from an rc.3 source candidate in a clean environment with no adopter state.
2. Follow only shipped documentation to create public repository configuration, configure private runtime state, bootstrap schema/owner, initialize canonical repository source, enter onboarding, and reach green readiness.
3. Confirm the starter produces a coherent multi-page site and exposes navigation, branding, writing, media, SEO, redirects, and readiness without hand-authoring initial HTML.
4. Exercise one representative LLM-governed repository structural/design change through branch/PR semantics, one canonical content change through the CMS/store contract, and one small feature change with tests.
5. Exercise one documented deployment flow and the redirect/transport adapter boundary where applicable.
6. Exercise backup/recovery and paired rollback behavior; schema migration rules must remain explicit even if the clean rehearsal begins at schema 8.
7. Build rc.3 twice and prove deterministic artifact identity/provenance; inspect license/NOTICE, onboarding/starter/docs/agent contract, schema/migration/redirect/adapters, exclusions, and residue.
8. Repeat the production proving-ground parity check. Any material reusable core delta reopens extraction.
9. Only after these conditions pass does the project return to the Principal publication gate.

## License boundary

License selection is resolved. Candidate metadata remains `public:false`; the repository/package remains private and unpublished until separate Principal authorization. Documentation must not call the project OSI open source.

## Optional/post-release frontier

- Additional provider/server/CDN adapters and automated deployment integrations.
- Browser credential-writing setup remains excluded from core.
- Newsletter/subscription extension.
- Additional starter themes/template packs.
- Future schema changes require explicit migrations; bootstrap repair never substitutes for migration.

## Release boundary

No public-release authorization has been granted. Repository visibility, Git tag/GitHub Release creation, public package publication, production deployment/adoption, credentials, and destructive data actions remain separate Principal decisions. The only active delegated pre-publication work after M-011–M-013 merge is **M-014**.
