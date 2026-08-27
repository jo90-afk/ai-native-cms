# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Release baseline: `main` at public `0.1.0-rc.3`
Working branch: `feat/schema-v9-block-composer-port`
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-08-26 (America/New_York)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS that preserves canonical SQL authority, repository-owned application behavior, static public delivery, revisions/provenance, explicit migrations, deterministic projection, bounded deployment adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, and destructive data actions remain explicit operator/Principal boundaries.

## Frozen release truth

`0.1.0-rc.3` is already published and remains a schema-v8 artifact tied to its released main SHA. Post-release development may not silently redefine that tag, package, release metadata, or installation contract.

## Durable product truths

- Anonymous public delivery is static-first and database-free.
- MySQL owns accepted authored state and bounded reusable composition state.
- Git owns code, rendering behavior, shells, CSS/JS, schema/migrations, tests, docs, adapters, and public non-secret adopter configuration.
- Generated HTML/JSON/XML/indexes/redirect maps are outputs, not reverse source authority.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients never submit arbitrary structural HTML or CSS.
- Structural HTML is generated server-side from either repository-derived converted presets or canonical governed semantic primitive definitions.
- A saved block preset is a recipe, not a live shared component. Page Composer stores a preset key, stable instance identity, and typed value snapshot; later preset edits affect future placements only.
- Repository source may propose canonical content but may not silently overwrite newer accepted database state.
- Database bootstrap owns schema structure and the first persisted owner only; migrations remain explicit CLI operations.
- Browser onboarding/readiness/maintenance may not migrate, deploy, publish, or expose secrets.
- Redirect authority remains canonical in SQL while anonymous routing consumes generated static state only.
- Before a future release line is cut, a fresh production proving-ground comparison and clean rehearsal are required.

## Satisfied release milestones

M-001 through M-014 and SEO parity closure are satisfied for public `0.1.0-rc.3`.

## M-015 — governed saved-block composition

**Active candidate on `feat/schema-v9-block-composer-port`; not released, merged, or deployed.**

Objective: port the reusable composition wave validated in the production proving ground without importing site identity, authored content, styling assumptions, credentials, or host-specific deployment behavior.

Acceptance conditions:

- schema-v8 release state remains frozen and installable;
- explicit CLI-only `8-to-9` migration creates canonical `block_presets`, converts former template rows, rewrites composition references, archives the retired table, and advances the schema only after guarded work;
- primitive definitions are bounded to application-declared layouts, surfaces, widths, spacing, semantic elements, counts, media paths, link schemes, and heading rules;
- Block Composer never persists arbitrary browser HTML/CSS;
- Page Composer consumes saved presets and persists only `presetKey`, `instanceId`, and typed values;
- in-use presets cannot be deleted;
- shared thumbnail media selection is first-party and used by both new composition surfaces;
- composed public pages retain exactly one H1 and deterministic editable-leaf identities;
- rebuild/projection requires schema v9 once preset-based composition is active;
- cumulative public-release, authority, security, syntax, migration, and behavior contracts remain green.

Current implementation evidence:

- new primitive renderer: `api/composer-primitives.php`;
- new canonical preset store: `api/block-presets.php`;
- guarded Block Composer API/UI: `api/cms-blocks.php`, `cms/blocks.php`, `cms/block-composer.js`;
- explicit migration: `database/migrations/8-to-9.php`;
- Page Composer composition records now use preset instance snapshots;
- shared visual media picker: `cms/media-picker.js` + `cms/media-picker.css`;
- extraction and test contracts updated for the v8-release/v9-development boundary.

Independent CI verification is required before M-015 can be accepted.

## Next frontier after M-015

Port the later production change that makes one live Page Composer the sole page mutation boundary and retires the separate Pages editor. That change is intentionally excluded from M-015 because it changes operator information architecture and mutation routing on top of the new schema-v9 authority model.
