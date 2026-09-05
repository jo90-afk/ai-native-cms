# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.4` (schema v10, frozen artifact at `89ea969aa299de8a4ae06e89d925d50625c066ae`)
Current development baseline: `main` through merged M-020 / PR #25 (`89ea969aa299de8a4ae06e89d925d50625c066ae`)
Working branch: `feat/public-markdown-discovery`
Active milestone: M-021 — generic public Markdown discovery successor
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-09-04 (UTC)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS with canonical SQL authority, repository-owned application behavior, deterministic/static public projection where practical, explicit migrations, bounded provider adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, private-data import, bulk messaging, destructive data actions, and any merge that directly triggers public release publication remain explicit operator/Principal boundaries.

## Frozen predecessor truth

`0.1.0-rc.4` was published on 2026-08-27 at 22:45:46 UTC after PR #25 merged at `89ea969aa299de8a4ae06e89d925d50625c066ae`. The GitHub release record `v0.1.0-rc.4` and successful publisher run `33123764799` supersede the former pending-publication status. M-021 starts from that commit without changing its version, tag, schema, package, notes, or provenance.

`0.1.0-rc.3` remains the historical schema-v8 published artifact and supported upgrade origin. The current rehearsal still reads the actual `v0.1.0-rc.3:database/schema.sql` file. Lineage is rc.3 → published rc.4 → this development successor.

## Integrated development truth

M-015 through M-020 are integrated to development `main`:

- M-015 / PR #20 — schema-v9 governed saved-block composition;
- M-016 / PR #21 — unified live Page Composer;
- M-017 / PR #22 — save edited page block as a new independent preset;
- M-018 / PR #23 — clean managed public routes;
- M-019 / PR #24 — schema-v10 Audience authority, governed double opt-in, authenticated Audience CMS, transactional mail adapters, and cPanel onboarding; merged as `53ebe3960e87a4f7732392a1bf2689569bc31412` after exact-head validate #377 and release-rehearsal #91.
- M-020 / PR #25 — generic discovery plus published rc.4/schema-v10 release line; merged as `89ea969aa299de8a4ae06e89d925d50625c066ae` with successful main validation run `33123764788` and the publication evidence above.

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

## Historical M-020A — deterministic public/LLM discovery

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

## Historical M-020B — rc.4 / schema v10 release line

The accepted release candidate established:

- `VERSION` `0.1.0-rc.4`;
- `release/release.json` schema 10 and tag `v0.1.0-rc.4`;
- fresh schema 10 containing current `block_presets` and generic Audience authority without retired active `page_block_templates` or `subscribers` tables;
- explicit packaged migrations 7→8, 8→9, and 9→10;
- updated README, architecture, installation, release notes, release process, public release contracts, onboarding governance, and bootstrap behavior;
- a version-driven publication workflow rather than rc.3-specific notes/assets;
- a clean rc.4 packaged-candidate rehearsal that proves schema-10 bootstrap/readiness/onboarding, generated discovery, canonical mutation, redirect projection, deterministic builds, and paired restore;
- an upgrade rehearsal that resets the database to the exact published rc.3 schema-v8 tag, proves 8→9 preset/composition conversion, then leaves schema 9 for the existing 9→10 Audience/consent/mail rehearsal.

## Proving-ground parity

The historical M-020 sweep ended at merged PR #68 (`e1b1d5eb99edd3da4f909ada19def9ab6b7bc12d`); its no-later-delta finding applied only at that release-preparation stage.

The fresh 2026-09-04 sweep through proving-ground `main` `681703f7daba972fd11147f75a670ac7cdf4f243` found PR #69 (site-only Lattice positioning/copy/SEO) and PR #70 (reusable Markdown projection and compact LLM routing). M-021 generalizes PR #70 at its merged source head. Its fixed personal routes, identity, taxonomy, and expanded-corpus requirement are not imported. Site-specific project-state publishing remains an adapter concern.

## Historical M-020 Quality / Assurance criteria

Required gates remain authoritative and are not weakened for release promotion. Early M-020 runs correctly exposed rc.3/schema-8 assumptions in onboarding-governance and bootstrap behavior tests; those tests were advanced to current rc.4/schema-10 truth while preserving their underlying security/authority assertions.

The M-020 pre-publication handoff required the exact final PR head to satisfy:

1. cumulative `validate` is green through all structural/behavior/syntax/reproducible-build checks;
2. `release-rehearsal` is green through clean schema-10 install plus published rc.3 8→9→10 upgrade evidence;
3. no unresolved review thread exists; and
4. the recorded proving-ground parity result remains current.

A green candidate was a pre-publication handoff only. The subsequent PR #25 merge and release publication resolved that boundary; they are no longer pending actions.

## Historical M-020 publication boundary

Before Principal acceptance, M-020 implementation authority did not authorize production deployment, installed-site migration, real mail credentials, enabling live collection, private-list import, bulk/campaign messaging, destructive state mutation, or publication of `0.1.0-rc.4`.

Because release metadata reaching `main` triggers the public publisher, merging PR #25 was the Principal publication boundary. This records the historical constraint, not a current instruction to reopen or republish the accepted release.

## M-021 — public Markdown discovery

The authorized repository/projection increment gives every eligible public canonical page a deterministic adjacent Markdown alternate, adds idempotent alternate/discovery metadata, and routes the compact LLM index to current alternates. MySQL remains authored-state authority; Git owns the serializer and contracts; HTML/Markdown/discovery outputs remain replaceable projections.

The owning runtime paths are `api/discovery-projection.php`, `api/markdown-projection.php`, and `api/llms-projection.php`. No schema migration, CMS mutation API, authentication change, release metadata change, tag movement, production deployment, or private-data import is involved.

Acceptance requires generic behavior coverage for serialization, same-origin/base-path eligibility, private/source-path and symlink exclusion, forged/stale index rejection, canonical deduplication, noindex/deletion cleanup, authored-file preservation, byte-stable repeated projection, and absent versus malformed-present optional full context. The cumulative `validate` and MySQL `release-rehearsal` workflows must pass on the exact final candidate. The rehearsal must prove packaged runtime presence, fresh-install Markdown output, and the existing rc.3→rc.4 upgrade/recovery guarantees.

Rollback is a repository revert with public discovery rebuilt from the prior implementation; remove this successor's marked generated `.md` outputs and its Markdown alternate tags as a paired projection rollback. Never recover canonical content from the generated files. Published rc.4 artifacts remain unchanged. Downstream sites may prepare a dependent upgrade from the reviewed candidate but acceptance precedes adoption.

## Next action

Complete and review M-021 on its branch, run exact-head cumulative Quality/Assurance, resolve failures without weakening existing guarantees, and present the PR with evidence. Local PHP is unavailable in the current workspace, so PHP behavior and packaged MySQL proofs belong to the GitHub workflows. No new release publication or production adoption is part of this handoff.
