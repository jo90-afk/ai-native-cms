# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.4` (schema v10, frozen artifact at `89ea969aa299de8a4ae06e89d925d50625c066ae`)
Current development baseline: `main` through merged M-021 / PR #26 (`8797f1d87d52664ea085b776debee524db6da375`)
Working branch: `parity/current-proving-ground-cms`
Active milestone: M-022 — upstream reusable proving-ground publication correctness
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-10-15 (UTC)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS with canonical SQL authority, repository-owned application behavior, deterministic/static public projection where practical, explicit migrations, bounded provider adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, private-data import, bulk messaging, destructive data actions, and any merge that directly triggers public release publication remain explicit operator/Principal boundaries.

## Frozen release truth

`0.1.0-rc.4` remains the published schema-v10 release. PR #26 / M-021 subsequently added generic public Markdown discovery to development `main` without changing version, tag, schema, published artifact, or release metadata. This parity milestone starts from merge commit `8797f1d87d52664ea085b776debee524db6da375` and does not authorize another public release.

`0.1.0-rc.3` remains the historical schema-v8 upgrade origin. Current schema evolution remains 7→8→9→10 with explicit migrations; bootstrap repair is not migration.

## Durable product truths

- MySQL owns accepted authored and other mutable canonical application state.
- Git owns code, rendering behavior, schema/migrations, tests, docs, adapters, and non-secret adopter configuration.
- Generated public files are replaceable projections, not reverse source authority.
- Anonymous delivery remains static-first/database-free where practical.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients do not submit arbitrary structural HTML/CSS for governed composition.
- Secrets remain outside canonical SQL, public configuration, generated output, and browser-visible state.
- Provider-specific transport/deployment integration remains adapter state and may not become application authority.
- Exact-head cumulative validation and fresh proving-ground parity are required before a release candidate is promoted.

## Accepted development sequence

- M-015 / PR #20 — schema-v9 governed saved-block composition.
- M-016 / PR #21 — unified live Page Composer.
- M-017 / PR #22 — save edited page block as a new independent preset.
- M-018 / PR #23 — clean managed public routes.
- M-019 / PR #24 — schema-v10 Audience authority, double opt-in, authenticated Audience CMS, transactional-mail adapters, and cPanel onboarding.
- M-020 / PR #25 — generic discovery plus published rc.4/schema-v10 release line.
- M-021 / PR #26 — adjacent public Markdown alternates and compact LLM routing derived from current public HTML.

Repository integration never implies installed-site migration, production credentials, deployment, public collection activation, or release publication.

## M-022 — proving-ground CMS publication parity

A current private production proving-ground sweep at inspected revision `40357dc470f3e5f44bd524bd4eed69209a35b52d` identified a reusable cluster that landed after the prior upstream sweep: authenticated CMS writers now distinguish canonical persistence from successful public publication and coordinate with host recovery through a shared private publication boundary.

The generic upstream increment must preserve schema-10/product authority while extracting only reusable behavior:

1. public-content writers acquire one shared private publication lock before canonical mutation;
2. after canonical persistence succeeds, request-local outcome state records that a save occurred;
3. only successful complete public finalization clears that saved state;
4. a later projection failure records bounded private pending state of kind `cms-publication` and reports `saved=true`, `published=false` rather than pretending the request was unsaved or published;
5. unresolved pending publication blocks follow-on public writers before they mutate canonical state;
6. redirects converge on the same complete finalization boundary rather than bypassing it;
7. Writing, Branding, Navigation, SEO, Page Composer, Redirects, Blocks, and Media participate in the coordination boundary appropriate to their mutation semantics;
8. private state uses only the product's public configuration namespace and contains no adopter identity, authored content, credentials, or proving-ground filesystem paths.

The core owns the save-versus-publication invariant and pending-state format. Immutable snapshots, private staging, serving-generation materialization, web-server selector switching, shared-host runtime/tool discovery, host capability evidence, and rollback remain deployment-adapter responsibilities. Their portable obligations are documented; adopter-specific executable paths and markers do not move upstream.

## Acceptance

M-022 is acceptable when:

- the dedicated publication-outcome contract and executable behavior test pass on the exact candidate head;
- cumulative `validate` remains green across schema-10 authoring, Audience/mail, discovery, migrations, packaging, and sanitizer contracts;
- `release-rehearsal` remains green across clean schema-10 install and the published rc.3 → v9 → v10 upgrade path;
- the candidate contains no proving-ground-specific environment names, personal/site identity, private host path, database dump, private evidence, or release-publication metadata change;
- proving-ground parity is recorded against the inspected source revision and any intentionally non-upstreamed behavior is classified as adopter/deployment-adapter state rather than silently omitted.

## Consequence boundary

This milestone authorizes reusable repository implementation and merge when exact-head evidence is green. It does not authorize installed-site adoption, production deployment, credential changes, public release publication, tag/version movement, private-data import, or destructive recovery on a live adopter.

## Next action

Complete M-022 exact-head Quality/Assurance, merge when ready, and then return the parity frontier to observation. Any later public release/version increment remains a separate Principal decision.
