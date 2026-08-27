# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Release baseline: `main` at public `0.1.0-rc.3`
Working branch: `feat/page-block-save-as-new-port`
Stacked base: `feat/live-page-composer-port` / draft PR #21
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
- Structural HTML is generated server-side from repository-derived converted presets or canonical governed semantic primitive definitions.
- A saved block preset is a recipe, not a live shared component. Each page placement owns an independent typed value snapshot.
- Page Composer is the sole browser mutation boundary for public page copy and page composition after schema v9.
- A repository page may be adopted into canonical composition only from a source-state hash; stale first adoption fails closed.
- Rebuild may preserve canonical `page_blocks`; an intentional live Composer save synchronizes its typed rendered leaves into canonical `page_blocks` atomically with composition state.
- Once a repository block is adopted into composition, editable leaf identity is namespaced to the stable composition instance while the accepted value is preserved.
- Saving an edited page block as a new reusable preset derives a new recipe from bounded typed values; it never mutates the source preset or selected page instance.
- Repository source may propose canonical content but may not silently overwrite newer accepted database state.
- Database bootstrap owns schema structure and the first persisted owner only; migrations remain explicit CLI operations.
- Browser onboarding/readiness/maintenance may not migrate, deploy, publish, or expose secrets.
- Redirect authority remains canonical in SQL while anonymous routing consumes generated static state only.
- Before a future release line is cut, a fresh production proving-ground comparison and clean rehearsal are required.

## Satisfied release milestones

M-001 through M-014 and SEO parity closure are satisfied for public `0.1.0-rc.3`.

## M-015 — governed saved-block composition

**Technically satisfied on draft PR #20; not merged, released, or deployed.**

Accepted evidence:

- explicit CLI-only schema `8→9` migration creates `block_presets`, converts former template rows, rewrites `templateKey→presetKey`, archives the old table, and is idempotent;
- bounded semantic primitive renderer and standalone Block Composer never accept arbitrary browser structural HTML/CSS;
- Page Composer stores `presetKey`, `instanceId`, and typed value snapshots;
- shared first-party thumbnail media picker is reused across composition surfaces;
- schema-v8 installed sites remain read-compatible immediately before migration;
- cumulative validation run #246 passed contracts, behavior, PHP/JS/Python syntax, and deterministic candidate build;
- MySQL release rehearsal run #57 passed clean rc.3 bootstrap/reconcile plus the v8→v9 conversion, archive, idempotence, sanitation, and single-H1 composition invariants.

M-015 remains independently replaceable because its PR is not collapsed into M-016.

## M-016 — unified live Page Composer

**Technically satisfied on stacked draft PR #21; not merged, released, or deployed.**

Accepted implementation:

- public pages render in a same-origin sandboxed iframe with site scripts disabled;
- direct rich-text edits map to typed preset variables rather than browser-submitted structural HTML;
- media, link, and text fields remain typed and bounded; first-party media selection is shared with Block Composer;
- block add/duplicate/move/remove operations submit only preset identities, instance identities, and typed values; server rendering remains structural authority;
- repository pages can be adopted into canonical composition without losing prior accepted `page_blocks` values;
- first adoption is guarded by both composition hash (`none`) and a canonical source-state hash;
- live typed saves synchronize composition snapshots and canonical editable leaves; later rebuilds preserve those accepted values;
- adopted editable IDs move into the stable composition-instance namespace rather than retaining repository-source identity;
- Block Composer can open inside Page Composer and saved preset edits remain future-placement-only;
- `/cms/pages.php` is an authenticated compatibility redirect only; `api/cms-pages.php` and its browser mutation client are removed;
- onboarding and CMS navigation teach Blocks + Composer rather than competing Pages/Composer concepts;
- Page Composer can import missing repository-derived presets without overwriting existing saved presets;
- schema-v8 read/reconcile compatibility from M-015 remains intact.

Accepted verification evidence:

- GitHub Actions validation run **#291** passed every cumulative static/security contract, behavior suite, PHP/JavaScript/Python syntax check, and deterministic release-candidate build;
- MySQL release rehearsal run **#61** passed the frozen rc.3 clean-site rehearsal before exercising post-release schema work;
- the same rehearsal passed schema `8→9` migration, archive creation, composition-reference rewrite, and migration idempotence;
- the M-016 convergence proof preserved a pre-adoption canonical copy value, rejected a stale source hash, adopted into namespaced canonical leaf identity, committed a live typed copy edit into composition state + canonical `page_blocks` + public projection, and preserved that edit through a later full rebuild;
- evidence artifact: `ai-native-cms-release-rehearsal-evidence` from run #61.

M-016 remains independently replaceable as a stacked PR above M-015. Its technical acceptance does not authorize merging, migration of an installed site, deployment, or release publication.

## M-017 — save edited page block as new preset

**Active candidate on `feat/page-block-save-as-new-port`; stacked on M-016 and not merged, released, or deployed.**

Source proving-ground change: `jo90-afk/judeoneill.com` PR #61, merged to production source on 2026-08-26.

Objective: let a Page Composer user turn the currently edited selected block into a new reusable preset without mutating the original preset or page placement and without promoting browser structural DOM into authority.

Acceptance conditions:

- Page Composer exposes **Save as new block** for a selected persisted/source-derived instance;
- browser submits bounded rich-copy leaves, media path/alt pairs, and link href/text pairs only; `outerHTML`, structural HTML, classes, styles, and CSS never enter the payload;
- the server verifies the selected instance still belongs to the current canonical/source-derived page state and resolves its source preset itself;
- browser field identities are mapped back through the source preset variable schema; unknown/stale leaf identities and out-of-range media/link indices fail closed;
- primitive source presets apply typed instance values to a fresh primitive definition and save a new primitive preset;
- converted source presets apply typed instance values to the original governed converted template and save a new converted preset;
- the new preset always receives a new key; source preset metadata/definition/template remains unchanged;
- the selected page composition/value snapshot remains unchanged by save-as-new;
- schema remains v9; no migration is introduced;
- cumulative validation plus the MySQL rehearsal prove both primitive and converted paths, source/page immutability, sanitation, stale/tamper rejection, deterministic release packaging, and frozen rc.3 compatibility.

Current implementation evidence:

- typed snapshot normalization: `api/composition-values.php`;
- new-preset derivation: `api/block-presets.php`;
- guarded `saveAsNewPreset` Page Composer action: `api/cms-composer.php`;
- Page Composer affordance: `cms/page-block-save-as-new.js` + `cms/composer.php`;
- independent static contract: `tests/page_block_save_as_new_contract.py`;
- MySQL primitive + converted rehearsal coverage: `tests/schema_v9_rehearsal.sh`.

Independent GitHub Actions verification is required before M-017 can be technically accepted.

## Next frontier after M-017

Continue the proving-ground comparison from current `judeoneill.com` main after PR #61. Any additional reusable behavior should remain a separate milestone unless it is inseparable from M-017’s typed snapshot contract. The frozen rc.3 public artifact remains unchanged; merge, installed-site migration, deployment, and public release publication remain outside technical milestone acceptance.
