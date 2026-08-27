# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.3` (schema v8, frozen artifact)
Current development baseline: `main` after merged M-015 → M-017
Working branch: `feat/clean-managed-routes-port`
Active milestone: M-018 / draft PR #23
Runtime contract: `jo90-afk/lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-08-27 (America/New_York)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS that preserves canonical SQL authority, repository-owned application behavior, static public delivery, revisions/provenance, explicit migrations, deterministic projection, bounded deployment adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, and destructive data actions remain explicit operator/Principal boundaries.

## Frozen release truth

`0.1.0-rc.3` remains the published schema-v8 artifact tied to its released main SHA. Later merged development does not redefine that tag, package, release metadata, installation contract, or provenance. A later public release requires its own release decision and rehearsal.

## Current development truth

M-015, M-016, and M-017 were merged to `main` on 2026-08-27 after their independent verification gates. Development `main` therefore contains schema-v9 saved block presets, the unified live Page Composer, and typed **Save as new block** behavior, while the published rc.3 artifact remains schema v8.

Merged development sequence:

- M-015 / PR #20 — schema-v9 governed saved-block composition; merge `16d2571b18f1f404233140aea315cbd205575b51`;
- M-016 / PR #21 — unified live Page Composer; merge `e7b91a1df138c8fdbf6fbecfdaba7342b94a7ee3`;
- M-017 / PR #22 — save edited page block as a new independent preset; merge `b5e384f0d58057fa650caeb3dffc430f3764b3ca`.

Those merges authorize repository integration only. They do not imply an installed-site migration, production deployment, or a new published release.

## Durable product truths

- Anonymous public delivery is static-first and database-free.
- MySQL owns accepted authored state and bounded reusable composition state.
- Git owns code, rendering behavior, shells, CSS/JS, schema/migrations, tests, docs, adapters, and public non-secret adopter configuration.
- Generated HTML/JSON/XML/indexes/redirect maps are outputs, not reverse source authority.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients never submit arbitrary structural HTML or CSS.
- Structural HTML is generated server-side from repository-derived converted presets or canonical governed semantic primitive definitions.
- A saved block preset is a recipe, not a live shared component. Each placement owns an independent typed value snapshot.
- Page Composer is the sole browser mutation boundary for public page copy and page composition after schema v9.
- Repository source may propose canonical content but may not silently overwrite newer accepted database state.
- Database bootstrap owns schema structure and the first persisted owner only; migrations remain explicit CLI operations.
- Browser onboarding/readiness/maintenance may not migrate, deploy, publish, or expose secrets.
- Redirect authority remains canonical in SQL while anonymous routing consumes generated static state only.
- Public route shape is a projection concern; canonical managed-page identity remains stable behind that boundary.
- Provider-specific request interception/canonicalization remains an adapter concern.
- Before a future release line is cut, a fresh production proving-ground comparison and clean rehearsal are required.

## M-018 — clean public routes for managed pages

**Technically verified on draft PR #23; not merged, deployed, adopted, or released.**

Source evidence:

- proving-ground PR #63 established the reusable goal: stable internal managed `*.html` keys with clean reader-facing `/slug/` static projections;
- proving-ground PR #65 exposed a relocation hazard: JavaScript-created document-relative runtime URLs are outside HTML projection and must be root-relative when a page can move beneath `/slug/`;
- proving-ground PR #66 is not part of this milestone. Its Lattice-specific subscriber pane depends on a site authority that does not exist in the public core and is not being invented merely to chase parity.

Accepted M-018 implementation:

- `api/page-routes.php` translates stable canonical page keys to clean public routes, resolves clean routes back to managed keys, preserves query/fragment components, preserves external URLs, and rejects public-route collisions;
- `api/page-projection.php` writes `/slug/index.html` only for the authoritative managed-page set, rebases relocated HTML references, rewrites references to managed pages, and normalizes discovery metadata where present;
- `api/content-rebuild.php` runs the clean-route pass at the final public projection boundary without changing SQL authority or schema;
- `api/navigation.php` emits clean managed-page destinations while still resolving active state against canonical page keys;
- `config/site.example.php` enables clean managed routes for new installs, while an absent setting remains backward-compatible/off for existing adopters;
- the Apache adapter conditionally canonicalizes legacy `*.html` only when a corresponding clean projection exists, preserving unmanaged HTML behavior;
- runtime URLs created dynamically by JavaScript are explicitly outside projector parsing and are documented as root-relative implementation responsibilities;
- the extraction contains no proving-ground identity, Lattice page assets, private endpoints, or authored site content.

Accepted evidence on candidate head `0e432ab5aebe415bf0227660964b21dc75e5d2d8`:

- validation run #331 / workflow `33104318665` passed the full cumulative contract and behavior suite, the new clean-route structural and PHP behavior gates, PHP/JavaScript/Python syntax, and deterministic release-candidate construction;
- release-rehearsal run #70 / workflow `33104318671` passed the frozen rc.3 empty-site rehearsal and the schema-v9 upgrade/composition rehearsal and uploaded rehearsal evidence;
- no schema migration or irreversible canonical-state mutation is introduced by M-018.

M-018 remains independently replaceable as PR #23. Technical verification does not authorize merge, installed-site adoption, production deployment, or release publication.

## Next frontier

After M-018 handoff, continue comparing the public core with newer proving-ground work. Port mechanisms only when the public product already has, or intentionally needs, the corresponding generic authority. Site-specific authored content, identity, mailing-list implementations, and Lattice-specific operational panes remain excluded unless a separate public-core mandate justifies a generic capability.

The next source PR after the clean-route work is proving-ground PR #66. It is currently classified as **site-only / no portable authority yet**, because `ai-native-cms` has no generic subscriber/list authority to extend. Do not create a parallel audience store solely to reproduce that private-site feature.
