# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `ab7039efa34a8b00f7357c23f18fb5a9d1251135`
Working branch: `feat/seo-site-audit-parity`
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
- SEO quality inspection is observational. Deterministic social/schema enhancement may enrich public projection, but canonical page-specific SEO remains in `seo_overrides`.
- Release-managed SEO update sets may seed missing canonical state but may replace an existing override only through an explicit expected-predecessor hash; existing CMS authorship wins by default.
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
Merged through PR #15 at `ab7039efa34a8b00f7357c23f18fb5a9d1251135`. Delivered a neutral Home/About/Writing starter, safe CLI public-config initializer, state-derived authenticated onboarding, placeholder-identity completion guard, first-login handoff, persistent Onboarding navigation, and dedicated structural/behavior tests without introducing browser credential or migration authority.

### M-012 — Repository and hosting operations are understandable and reversible
Merged through PR #15. `docs/REPOSITORY-OPERATIONS.md` defines Git vs SQL vs projection vs host state, branch/PR flow, SSH pull and reviewed-artifact/copy deployment patterns, secret placement, backup/migration, hotfix recovery, readiness, rollback, and upgrade practice.

### M-013 — LLM collaboration accelerates iteration without replacing governance
Merged through PR #15. Root `AGENTS.md` and `docs/LLM-COLLABORATION.md` define source-of-truth order, branch-first repository work, guarded canonical content changes, explicit migration/release behavior, no secret handling, and conversation history as context rather than authority.

## rc.3 verification evidence through PR #15

Implementation run #155 passed the full cumulative gate and produced an inspected internal rc.3 artifact. Documentation head `fad891d019fb9d5efa9ef9bce365a2de6b4b44bc` passed branch run #157 and PR-triggered run #158. PR #15 then merged the exact verified head to `main` at `ab7039efa34a8b00f7357c23f18fb5a9d1251135`.

No publication, tag, public package, production deployment, credential action, or production-site adoption occurred.

## Active parity objective — site-wide SEO quality system

The M-014 release rehearsal was started from `ab7039e…`, but its mandatory proving-ground parity check found a new reusable production delta before rehearsal could close.

Production proving-ground `main` is now `3830735dda257c14616c32a08f24808953a79bf9`, merging production PR #51, **“Make SEO a site-wide CMS quality system,”** directly on top of the previously recorded source baseline `113068842a808ed00268892dc6a2ffa51c27ffa6`.

That change is materially reusable core and therefore reopens extraction before M-014 may proceed.

### Parity acceptance conditions

1. Extract site-neutral site-wide SEO audit mechanics: duplicate titles/descriptions, broken links, orphan pages, sitemap/index contradictions, canonical-target contradictions, H1 problems, social-card completeness, structured-data validity, and missing image alt attributes.
2. Expose site-wide and per-page findings in the existing guarded Search + Social workspace without creating a second SEO authority.
3. Add deterministic author/social-card/schema defaults through the existing final public-projection boundary; page-specific accepted values remain canonical in `seo_overrides`.
4. Make `socialMode=inherit` effective during rebuild while preserving explicit custom social copy.
5. Allow immutable release update sets to seed SEO overrides, but preserve an existing canonical override unless the release names its exact predecessor hash.
6. Provide a generic CLI SEO audit suitable for release/readiness evidence.
7. Keep all configuration in public adopter `config/site.php`; do not import production `site-config.json`, personal content/metadata, production deployment scripts, or proving-ground evidence.
8. Add targeted structural and executable regression coverage and pass the cumulative rc.3 gate.
9. Recheck current production proving-ground head immediately before merge/rehearsal; any newer material reusable delta reopens classification again.

Current implementation branch: `feat/seo-site-audit-parity`.

Run #182 on head `4c83be4f3ac6263823a736df77e96bd880a723c4` passed all cumulative contracts/behavior/syntax/candidate-build steps, including the new SEO quality contract and executable projection/audit behavior. Documentation/evidence commits after that head still require the final exact-head gate before parity can be accepted.

## Next milestone — M-014

### M-014 — Clean empty-site release rehearsal proves product, docs, deployment, and agent workflow together

M-014 is **blocked until the reopened SEO parity objective is merged**. The earlier empty rehearsal branch was cut from the now-stale `ab7039e…` baseline and must not be used as release evidence.

Acceptance conditions:

1. Start from the post-parity rc.3 source candidate in a clean environment with no adopter state.
2. Follow only shipped documentation to create public repository configuration, configure private runtime state, bootstrap schema/owner, initialize canonical repository source, enter onboarding, and reach green readiness.
3. Confirm the starter produces a coherent multi-page site and exposes navigation, branding, writing, media, Search + Social/SEO quality, redirects, and readiness without hand-authoring initial HTML.
4. Exercise one representative LLM-governed repository structural/design change through branch/PR semantics, one canonical content change through the CMS/store contract, and one small feature change with tests.
5. Exercise one documented deployment flow and the redirect/transport adapter boundary where applicable.
6. Exercise backup/recovery and paired rollback behavior; schema migration rules remain explicit even if the clean rehearsal begins at schema 8.
7. Build rc.3 twice and prove deterministic artifact identity/provenance; inspect license/NOTICE, onboarding/starter/docs/agent contract, schema/migration/redirect/adapters/SEO audit, exclusions, and residue.
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

No public-release authorization has been granted. Repository visibility, Git tag/GitHub Release creation, public package publication, production deployment/adoption, credentials, and destructive data actions remain separate Principal decisions. The active delegated sequence is **close reopened SEO parity → run M-014 → return to Principal publication gate**.
