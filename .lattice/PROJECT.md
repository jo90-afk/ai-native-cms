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

Extract and maintain a public-releasable, site-neutral AI-native CMS from the production proving ground. Preserve canonical SQL authority, repository/template ownership, static public delivery, revisions/provenance, safe migrations, deterministic projection, and bounded provider adapters. Make the product approachable enough that a new adopter can create, operate, deploy, and evolve a site without first learning the internal architecture.

Routine reversible implementation, refactoring, tests, documentation, repository-side merges, upstream parity refreshes, internal candidate preparation, and pre-release usability work are delegated. Repository visibility, public/tagged release creation, package publication, production deployment, credentials, destructive data actions, and production adoption remain Principal boundaries.

The Principal has selected the release license terms: **Apache License 2.0 subject to Commons Clause License Condition v1.0**. This permits use, modification, derivative works, attribution-preserving redistribution, and commercial use while withholding the right to sell AI Native CMS itself, or a product/service whose value derives entirely or substantially from the CMS. The project must describe this accurately as **source-available**, not OSI open source.

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
- Friendly onboarding begins only after the secure bootstrap/auth boundary; it must not become a browser credential writer.
- Canonical repository content initialization is an explicit reconciliation step after bootstrap.
- Browser CMS operations may mutate canonical authored objects and rebuild accepted state, but must never promote a generated/live working tree back into repository-source authority.
- CMS mutation surfaces fail closed when the installed schema is older than the code contract.
- Derived site-wide projection has one explicit finalization boundary so one projector cannot erase another projector's accepted output until the next rebuild.
- Redirect authority is canonical in SQL, while anonymous redirect requests remain database-free through generated routing data plus a deployment adapter.
- Redirect graph-changing writes serialize and revalidate globally; optimistic record hashes remain the per-record stale-write boundary.
- Redirect sources that resolve to existing public files or directories cannot become active redirect authorities when deployment adapters serve real filesystem routes first.
- Conflicting configured read-only system aliases fail closed rather than using order-dependent last-write wins.
- Readiness is observational and may not initialize, migrate, publish, mail, deploy, invoke shell commands, or expose secret/grant values.
- Host/provider behavior enters only through bounded adopter/deployment adapters.
- GitHub is the durable collaboration/review surface for repository-owned structure, code, configuration examples, docs, and migrations; generated public output is not a substitute source authority.
- LLM collaboration must preserve the same governance as human collaboration: branch-first changes, explicit schema migrations, canonical/source boundaries, tests, reviewable diffs, no secret handling, and no silent production mutation.
- Internal candidate generation is not publication. A selected license does not make a candidate public.
- Public-release artifacts contain no personal content, credentials, private repository/account identifiers, adopter-local configuration, or runtime/governance state.
- Before any public release decision, compare against the current production proving-ground frontier. A material unresolved reusable core delta automatically reopens delegated parity work.

## Satisfied milestones

### M-001 — Foundation is generic, executable, and safe to extend
Established generic database/security runtime, adopter configuration, path-compatible upstreaming, and public-release guards.

### M-002 — Canonical page/document state survives every ordinary writer
Merged at `39e25616068567746e62bc9f46eb8da692975ce8`; optimistic canonical editing, three-way reconciliation, immutable update sets, deterministic projection.

### M-003 — An adopter can operate and extend the page CMS without forking core authority
Merged at `8c0c5258e05b6e7f10f6175fc194cbe0c6c13cb3`; trusted projector hooks and secure Pages UI.

### M-004 — Long-form content and SEO remain reversible, safe, and deterministic
Merged at `479389c02f9fb2b2a601a08b2678b5cc64d6ef85`; posts/revisions, bounded Markdown, static publishing, canonical SEO.

### M-005 — Typed composition survives ordinary edits, rebuilds, and repository reconciliation
Merged at `0d7747819bf7f611e3615771c7f30e907d2136af`; repository-owned templates, typed compositions, leaf preservation, canonical media metadata.

### M-006 — New-page hierarchy, navigation, and branding remain safe and deterministic across rebuilds
Merged at `4c875ae847e7a9a7871ddfab710c49c193f37a7b`; trusted-shell page creation, hierarchy validation, canonical navigation/branding.

### M-007 — Portable bootstrap and readiness preserve authority boundaries
Merged at `993b3f0a89e6e062a165a6d4ee55a3ce50bac261`; CLI-only schema/owner bootstrap, explicit canonical import, read-only readiness.

### M-008 — A release candidate can be produced reproducibly without crossing publication boundaries
Merged at `b3588304acdfc61faa541a1ffa65c1fb284d4513`; deterministic packaging, exact provenance, residue guards, install/rollback docs.

### M-009 — Canonical redirects and schema-v8 projection boundaries are portable, deterministic, and safe
Completed through PRs #10/#11/#12/#13; schema v8, explicit 7→8 migration, canonical redirect authority, static DB-free routing, slug history, current-schema write guards, final projection boundary, concurrency/collision/conflict hardening.

### M-010 — Reference deployment adapters remain bounded and do not become CMS authority
Merged via PR #12 at `97d72a74491f66726b3c9a28da313d3753c89646`, with directory-aware hardening through PR #13 at `7043eeee47bf4b0112957e7e3a6e564c5da1d020`. Public/private Apache examples encode unresolved-path interception, conservative caching/compression, and private no-store behavior.

### OBJ-011 — Schema-v8 extraction parity and release-gate restoration
PR #14 merged at `c63dfc211d89bf046b8c48acab8bfa20005d5ecf`; final main run #96 passed the cumulative gate. The source proving ground had no newer reusable core delta at the recorded parity check.

## Current pre-release sequence

The prior release gate is **reopened by Principal requirement**. `0.1.0-rc.2` remains verified historical evidence. New usability/governance work advances the internal candidate line to `0.1.0-rc.3`.

### M-011 — Friendly onboarding can build a coherent starter site without bypassing authority

Acceptance conditions:

1. A first authenticated visit presents a clear onboarding path when the site is materially unconfigured; experienced users can dismiss/resume it.
2. Credential/database bootstrap stays CLI-only. Browser onboarding never writes DB credentials, runs migrations, executes shell commands, or edits secret files.
3. Onboarding explains the distinction between repository-owned structure/configuration and canonical SQL content in plain language rather than exposing implementation jargon.
4. A generic starter site ships with enough trusted shell/template/style structure to create a useful site without hand-authoring HTML first.
5. The onboarding path can establish site identity, starter pages, navigation, bounded branding, a writing area, and initial content using existing guarded canonical mutation contracts.
6. Completion is derived from actual state/readiness, not a fragile one-time `onboarding_complete` flag. The flow is resumable after interruption.
7. The final step runs/readably summarizes readiness and hands the adopter into the normal CMS workspaces.
8. Mobile and desktop onboarding are usable and accessible; no controls depend on hover or viewport-specific hidden actions.
9. Tests prove onboarding cannot create arbitrary structure, bypass expected-hash/canonical guards, promote generated output into repository authority, or weaken bootstrap/readiness constraints.

### M-012 — Repository and hosting operations are understandable and reversible

Acceptance conditions:

1. Ship `docs/REPOSITORY-OPERATIONS.md` as the canonical GitHub operating guide.
2. Document repository creation/template use, clone/pull, branch/PR flow, adopter-owned `config/site.php`, secrets outside public root, and what belongs in Git versus SQL.
3. Document at least two provider-neutral deployment patterns: SSH/pull-to-host and build/copy artifact deployment. Apache remains an example adapter, not a required host.
4. Document first deployment, routine code/content operations, database backup, schema migration, static rebuild, health/readiness check, and paired rollback.
5. Explain how to handle host-side hotfixes: capture them back into a branch/PR or discard them; never allow production drift to become implicit source authority.
6. Include a provider checklist so adopters can map document root, PHP/MySQL versions, rewrite support, secret placement, file permissions, scheduled backup, and deployment method without vendor-specific credentials.
7. Include a safe upgrade checklist for future tagged releases and explicit migrations.

### M-013 — LLM collaboration accelerates iteration without replacing governance

Acceptance conditions:

1. Ship a concise root `AGENTS.md` that coding agents can discover without reading the full documentation set.
2. Ship `docs/LLM-COLLABORATION.md` for human operators using ChatGPT, Codex, Claude, or other coding/content agents.
3. Define source-of-truth order and mutation classes: repository structure/templates/code/config examples; canonical SQL content/state; generated public projection; host/deployment adapter state.
4. Give reusable request packets for design, content, feature, bugfix, schema/migration, and release work.
5. Require branch-first/reviewable work for repository changes and existing CMS/API contracts for canonical content changes. Agents may not use generated HTML as a reverse source of truth.
6. Schema changes require explicit migration + rollback guidance + current-schema guards + tests. Agents may not use bootstrap repair as migration.
7. Agents may not request, persist, print, commit, or relocate credentials/secrets into the public tree.
8. Agents must update tests/contracts and relevant docs when changing governed behavior; failures are resolved, not waived as agent-generated noise.
9. Content collaboration must distinguish editorial/source proposals from canonical accepted content and preserve revision/provenance behavior.
10. Iterative design guidance must distinguish repository-owned structural/template/CSS changes from CMS-owned typed values and branding tokens.
11. Release work must repeat package residue checks and the proving-ground parity check before publication.
12. The documentation should teach how to ask an LLM to inspect current state first, propose bounded changes, implement on a branch, verify, and summarize the durable state change—without depending on conversation memory as authority.

### M-014 — Empty-site release rehearsal proves the product, docs, and agent workflow together

Acceptance conditions:

1. Start from an rc.3 source candidate in a clean environment with no adopter state.
2. Follow only shipped documentation to create repository configuration, bootstrap schema/owner, initialize canonical source, complete onboarding, and reach green readiness.
3. Build a coherent multi-page starter site with navigation, branding, writing, media, SEO, and redirects available.
4. Perform one representative LLM-assisted structural/design change through a branch/PR, one canonical content edit through the CMS contract, and one small feature change with tests.
5. Exercise deployment using one documented provider-neutral flow plus the Apache reference adapter where applicable.
6. Exercise backup, explicit migration/upgrade instructions as applicable, rollback/recovery, and regeneration of static projection.
7. Build rc.3 twice and prove deterministic artifact identity/provenance; inspect license, NOTICE, residue, schema, migration, redirect runtime, adapters, onboarding/docs, and agent contract.
8. Repeat the production proving-ground parity check. Any material reusable core delta reopens extraction.
9. Only after these conditions pass does the project return to the Principal publication gate.

## License boundary

License selection is now resolved: Apache 2.0 + Commons Clause v1.0, with attribution retained. Candidate metadata must remain `public:false` until a separate publication decision. Documentation must not call the project OSI open source.

## Remaining optional/post-release frontier

- Additional provider adapters and automated deployment integrations.
- Browser credential-writing setup remains excluded from core.
- Newsletter/subscription extension.
- Additional starter themes/template packs.
- Additional server/CDN adapters.
- Future schema changes require explicit migrations; bootstrap repair never substitutes for migration.

## Release boundary

No public-release authorization has been granted. Repository visibility, tag/GitHub Release creation, package publication, production deployment, credentials, and production adoption remain separate Principal decisions. The active delegated work is M-011 → M-012 → M-013 → M-014.
