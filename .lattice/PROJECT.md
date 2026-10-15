# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.4` (schema v10, frozen artifact at `89ea969aa299de8a4ae06e89d925d50625c066ae`)
Current development baseline: `main` through accepted M-022 plus state reconciliation (`663533c4491bc79df654e8eb4e5c1f3281d36a80`)
Working branch: `release/0.1.0-rc.5`
Active milestone: M-023 — publish `0.1.0-rc.5`
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-10-15 (UTC)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS with canonical SQL authority, repository-owned application behavior, deterministic/static public projection where practical, explicit migrations, bounded provider adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, private-data import, bulk messaging, and destructive live-data actions remain explicit operator/Principal boundaries.

The Principal explicitly authorized publication of a new version on 2026-10-15 after M-022 acceptance. For M-023, merging the reviewed rc.5 release-preparation PR to `main` is therefore an authorized public-release consequence, provided the exact candidate head satisfies the release contract.

## Frozen predecessor truth

`0.1.0-rc.4` remains the current published schema-v10 predecessor until M-023 publication succeeds. `0.1.0-rc.3` remains the historical schema-v8 upgrade fixture. Current schema evolution remains 7→8→9→10 with explicit migrations; bootstrap repair is not migration.

## Durable product truths

- MySQL owns accepted authored and other mutable canonical application state.
- Git owns code, rendering behavior, schema/migrations, tests, docs, adapters, and non-secret adopter configuration.
- Generated public files are replaceable projections, not reverse source authority.
- Anonymous delivery remains static-first/database-free where practical.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients do not submit arbitrary structural HTML/CSS for governed composition.
- Secrets remain outside canonical SQL, public configuration, generated output, and browser-visible state.
- Provider-specific transport/deployment integration remains adapter state and may not become application authority.
- Exact-head cumulative validation and fresh proving-ground parity are required before release publication.

## Accepted development sequence

- M-015 / PR #20 — schema-v9 governed saved-block composition.
- M-016 / PR #21 — unified live Page Composer.
- M-017 / PR #22 — save edited page block as a new independent preset.
- M-018 / PR #23 — clean managed public routes.
- M-019 / PR #24 — schema-v10 Audience authority, double opt-in, authenticated Audience CMS, transactional-mail adapters, and cPanel onboarding.
- M-020 / PR #25 — generic discovery plus published rc.4/schema-v10 release line.
- M-021 / PR #26 — adjacent public Markdown alternates and compact LLM routing derived from current public HTML.
- M-022 / PR #27 — generic save-versus-publication semantics, shared private publication locking, pending recovery state, and public-writer coordination extracted from the production proving ground.

## M-023 — rc.5 publication

`0.1.0-rc.5` promotes accepted M-021 and M-022 behavior into a new public prerelease without changing schema 10.

Release content must include:

1. deterministic public Markdown alternates and compact LLM routing;
2. publication locking plus canonical-save/publication outcome separation;
3. bounded `cms-publication` pending recovery state and follow-on writer blocking;
4. redirect convergence on complete public finalization;
5. generic publication-outcome/adapter documentation;
6. unchanged explicit 7→8→9→10 migration lineage and frozen rc.3 fixture.

The release identity is `0.1.0-rc.5`, tag `v0.1.0-rc.5`, channel `public-release-candidate`, schema 10. No database migration is added between rc.4 and rc.5.

## M-023 acceptance

Before the authorized merge to `main`:

- cumulative `validate` must be green on the exact release head;
- the dedicated publication-outcome behavior/contract must be part of release assurance and green;
- `release-rehearsal` must prove the deterministic rc.5 package, clean schema-10 install, and published rc.3 → v9 → v10 upgrade path;
- public-release sanitization must find no adopter identity, private paths, credentials, or proving-ground residue;
- `release/release.json`, `VERSION`, release notes, package root, tag, docs, and candidate tests must agree on rc.5;
- no unresolved reusable proving-ground delta may remain.

After the merge, `publish-release.yml` must create/verify tag `v0.1.0-rc.5`, create the GitHub prerelease, and verify the rc.5 ZIP, manifest, and SHA256 assets. Publication success must be observed before downstream adoption is represented as complete.

## Consequence boundary

This milestone authorizes the rc.5 public release because the Principal explicitly requested publication. It does not itself authorize deployment or migration of any adopter site. Downstream adoption, including the requested update of the production proving-ground repository, remains a separate governed repository/deployment step after publication succeeds.

## Next action

Finish exact-head rc.5 assurance, merge the release-preparation PR when green, observe the publisher succeed, then update the production proving-ground repository to reflect/adopt the published rc.5 core while retaining its site-specific extensions and host adapter behavior.
