# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.3` (schema v8, frozen artifact)
Current development baseline: `main` through merged M-019 (`53ebe3960e87a4f7732392a1bf2689569bc31412`)
Working branch: `release/0.1.0-rc.4`
Active milestone: M-020 — `0.1.0-rc.4` release-preparation candidate
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-08-27 (America/New_York)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS with canonical SQL authority, repository-owned application behavior, deterministic/static public projection where practical, explicit migrations, bounded provider adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, private-data import, bulk messaging, destructive data actions, and any merge that directly triggers public release publication remain explicit operator/Principal boundaries.

## Frozen predecessor truth

`0.1.0-rc.3` remains the published schema-v8 artifact. M-020 does not redefine its tag, package, schema, installation contract, notes, or provenance. Rc.4 release rehearsal reads the actual `v0.1.0-rc.3:database/schema.sql` file as the supported upgrade origin.

## Integrated development truth

M-015 through M-019 are integrated to development `main`:

- M-015 / PR #20 — schema-v9 governed saved-block composition;
- M-016 / PR #21 — unified live Page Composer;
- M-017 / PR #22 — save edited page block as a new independent preset;
- M-018 / PR #23 — clean managed public routes;
- M-019 / PR #24 — schema-v10 Audience authority, governed double opt-in, authenticated Audience CMS, transactional mail adapters, and cPanel onboarding; merged as `53ebe3960e87a4f7732392a1bf2689569bc31412` after exact-head validate #377 and release-rehearsal #91.

Repository integration never implies installed-site migration, production credentials, deployment, public collection activation, or release publication.

## Durable product truths

- MySQL owns accepted authored and other mutable canonical application state.
- Git owns code, rendering behavior, schema/migrations, tests, docs, adapters, and non-secret adopter configuration.
- Generated public files are replaceable projections, not reverse source authority.
- Anonymous delivery remains static-first/database-free where practical.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients do not submit arbitrary structural HTML/CSS for governed composition.
- Secrets remain outside canonical SQL, public configuration, generated output, and browser-visible state.
- Database migrations are explicit CLI operations; bootstrap repair is not migration.
- Provider-specific transport/deployment integration remains adapter state and may not become application authority.
- Before a release is accepted, exact-head Quality/Assurance and fresh proving-ground parity are required.

## M-020A — deterministic public/LLM discovery

The reusable mechanism demonstrated by proving-ground PR #68 has been generalized into public core without importing site identity or authored taxonomy:

- `api/discovery-projection.php` derives `site-index.json`, `sitemap.xml`, and `sitemap.txt` from indexable same-site projected HTML;
- duplicate legacy and clean-route files collapse on canonical public URLs;
- discovery runs after managed clean-route materialization;
- `api/llms-projection.php` emits a compact absolute-link `llms.txt` from the public index;
- an optional `llms-full.txt` can retain expanded public context beneath a synchronized compact index;
- public HTML receives one idempotent `rel="describedby"` link;
- private CMS state, Audience member data, credentials, drafts, private APIs, and host-only operational markers are excluded;
- executable regression proves shape, deduplication, privacy exclusions, absolute URLs, synchronization, and idempotence.

Proving-ground PR #67 remains host-specific at its private marker/path implementation layer. Its reusable principle is preserved in architecture/deployment guidance: unavailable operational provenance is unknown, not proof that canonical SQL is stale.

## M-020B — rc.4 / schema v10 release line

The implementation candidate now defines:

- `VERSION` `0.1.0-rc.4`;
- `release/release.json` schema 10 and tag `v0.1.0-rc.4`;
- fresh schema 10 containing current `block_presets` and generic Audience authority without retired active `page_block_templates` or `subscribers` tables;
- explicit packaged migrations 7→8, 8→9, and 9→10;
- updated README, architecture, installation, release notes, release process, public release contracts, onboarding governance, and bootstrap behavior;
- a version-driven publication workflow rather than rc.3-specific notes/assets;
- a clean rc.4 packaged-candidate rehearsal that proves schema-10 bootstrap/readiness/onboarding, generated discovery, canonical mutation, redirect projection, deterministic builds, and paired restore;
- an upgrade rehearsal that resets the database to the exact published rc.3 schema-v8 tag, proves 8→9 preset/composition conversion, then leaves schema 9 for the existing 9→10 Audience/consent/mail rehearsal.

## Fresh proving-ground parity

The proving-ground commit sweep performed during M-020 found no commit after merged PR #68 (`e1b1d5eb99edd3da4f909ada19def9ab6b7bc12d`). There is therefore **no unresolved later reusable-core delta** at this candidate stage.

## Quality / Assurance status

Required gates remain authoritative and are not weakened for release promotion. Early M-020 runs correctly exposed rc.3/schema-8 assumptions in onboarding-governance and bootstrap behavior tests; those tests were advanced to current rc.4/schema-10 truth while preserving their underlying security/authority assertions.

M-020 is technically satisfied only on the exact final PR head when:

1. cumulative `validate` is green through all structural/behavior/syntax/reproducible-build checks;
2. `release-rehearsal` is green through clean schema-10 install plus published rc.3 8→9→10 upgrade evidence;
3. no unresolved review thread exists; and
4. the recorded proving-ground parity result remains current.

A green candidate is a pre-publication handoff only.

## Explicit exclusions and consequence boundary

M-020 does not authorize production deployment, installed-site migration, real mail credentials, enabling live collection, private-list import, bulk/campaign messaging, destructive state mutation, or publication of `0.1.0-rc.4`.

Because release metadata reaching `main` triggers the public publisher, **merging PR #25 is itself the Principal publication boundary**. Director execution stops with a green, mergeable PR and exact-head evidence unless the Principal separately authorizes that merge/publication consequence.

## Next action

Run exact-final-head Quality/Assurance on PR #25, remediate any required failure without weakening the gate, confirm review threads remain clear, then mark the PR ready for Principal publication handoff. Do not merge from Director authority alone.
