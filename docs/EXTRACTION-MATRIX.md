# Extraction and release matrix

This file distinguishes the frozen `0.1.0-rc.3` release baseline from post-release reusable-core extraction.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | released | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Canonical release schema | `database/schema.sql` | core | released — rc.3 / v8 | Frozen release bootstrap remains schema v8 until a later release line is cut |
| Explicit schema upgrades | `database/migrations/` | compatibility core | v7→8 released; v8→9 technically satisfied in M-015 | Versioned CLI migrations; bootstrap repair and browser requests are never migration |
| Repository page/document registry | `config/site.php` | adopter config | released | Repository pages remain bounded source-lineage entries even when later adopted into canonical composition |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | released | Three-way source/canonical reconciliation; browser never promotes repository source silently |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | released; v9 composition-compatible | Accepted state projects deterministically; anonymous reads stay static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | released | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls and site-wide quality | SEO APIs/UI + audit/projection | core | released | Canonical overrides; same-origin canonicals; deterministic projection |
| Canonical redirects + static runtime | redirect APIs/UI + `__redirect*` | core | released | Graph safety in SQL; anonymous routing remains database-free |
| Converted structural presets | `api/block-presets.php` | compatibility core | technically satisfied — M-015 | Existing repository block structures become canonical saved recipes with typed copy/media/link values |
| Governed primitive presets | `api/composer-primitives.php`, Block Composer | core | technically satisfied — M-015 | SQL may own constrained semantic definitions; server owns validation and structural HTML generation |
| Saved block authority | schema-v9 `block_presets` | core | technically satisfied — M-015 | One reusable-block authority replaces active `page_block_templates`; legacy table becomes recovery archive during migration |
| Preset instance composition | composition store | core | technically satisfied — M-015 | A preset is a recipe, not a live shared component; each placement stores `presetKey`, stable instance ID, and typed value snapshot |
| Standalone Block Composer | `cms/blocks.php` + API/client | core UX | technically satisfied — M-015 | Design/edit/delete governed presets; in-use presets cannot be deleted |
| Shared thumbnail media picker | `cms/media-picker.*` | core UX | technically satisfied — M-015 | Same first-party media catalog used by Block Composer and Page Composer |
| Unified live Page Composer | `cms/composer.php`, typed composition APIs | core UX | technically satisfied — M-016 | Public layout is an interaction surface; browser submits typed values/identities only and server remains structural authority |
| Source-derived page adoption | composition store + Page Composer | compatibility core | technically satisfied — M-016 | Existing canonical page copy is hydrated into typed preset values; first adoption requires a source-state hash and moves leaf identity into the stable composition namespace |
| Unified page-copy/composition commit | composition store + `page_blocks` | core authority | technically satisfied — M-016 | Intentional Composer saves update typed composition and canonical editable leaves together; ordinary rebuild preserves accepted leaves |
| Retired separate Pages mutation surface | `/cms/pages.php` compatibility redirect | compatibility UX | technically satisfied — M-016 | One browser page-authoring concept; old API/client are removed, route remains as authenticated redirect |
| Embedded Block Composer | Page Composer + `cms/block-composer-embed.js` | core UX | technically satisfied — M-016 | Block design can be summoned in context without changing preset snapshot semantics |
| Incremental repository-preset import | `blockPresetBootstrap` + Page Composer | compatibility core | technically satisfied — M-016 | Import creates only missing converted presets using canonical keys and never overwrites existing saved presets |
| Save selected block as new preset | Page Composer + `blockPresetSaveAsNew` | core UX/authority | candidate — M-017 | Selected typed instance values can create a new reusable recipe without mutating the source preset or page placement |
| Typed save-as-new snapshot mapping | `compositionTypedSnapshotValues` | core authority | candidate — M-017 | Browser submits bounded copy/media/link values only; unknown leaf identity or indexed-field drift fails closed |
| Media library | media APIs/UI | core | released | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy, navigation, branding | respective stores/APIs/UI | core | released | Trusted shells, parent validation, safe navigation, bounded branding tokens |
| Database bootstrap | `database/bootstrap*.php` | core | released | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | released | Read-only actionable evidence; never deploys/migrates/publishes/exposes secrets |
| Starter site, onboarding, repo/hosting ops, LLM governance | root/config/docs/CMS | product + governance | released; authority model updated through M-016 | Adopters can initialize, operate, deploy, and collaborate without bypassing authority boundaries |
| Deterministic release package | release metadata + builder | release engineering | released — rc.3 | Published tag remains tied to the old main SHA; feature branches do not redefine the published artifact |
| License and attribution | license files | distribution governance | released — rc.3 | Apache 2.0 + Commons Clause v1.0; source-available, not OSI open source |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## Frozen public release

`0.1.0-rc.3` remains the published schema-v8 release. Its tag, release metadata, and package provenance are not rewritten by post-release feature extraction.

## M-015 — governed saved-block composition

M-015 is technically satisfied on draft PR #20, but remains unmerged and unpublished. Its explicit v8→v9 migration, governed primitive renderer, canonical saved-preset authority, standalone Block Composer, preset-instance Page Composer, media picker, and schema-v8 read-compatibility seam passed cumulative validation run #246 and MySQL release rehearsal run #57.

Keeping M-015 as its own PR preserves an independently replaceable authority/migration tranche beneath later UI work.

## M-016 — unified live Page Composer

M-016 is technically satisfied on stacked draft PR #21, but remains unmerged, undeployed, and unpublished. The portable implementation consolidates public-page authoring into one live Page Composer without promoting iframe DOM into structural authority. Direct edits map back to governed preset variables; block operations carry only preset/instance identities and typed values; server rendering reconstructs and validates structural HTML.

Repository pages remain valid source-lineage objects. On first live adoption, current canonical `page_blocks` values are hydrated into the converted preset snapshot so earlier accepted copy is not lost, and a source-state hash rejects stale first adoption. Adoption intentionally rekeys editable leaves into the stable composition-instance namespace. A deliberate live save then synchronizes rendered typed leaves into canonical `page_blocks` in the same composition transaction, while deterministic rebuild preserves accepted canonical leaf state.

The separate Pages mutation API/client is removed. `/cms/pages.php` remains only as an authenticated compatibility redirect to Composer. Block Composer can be embedded from Page Composer, and saved presets keep future-placement-only semantics. Repository-preset import is incremental: missing converted presets can be added without overwriting existing saved recipes.

Accepted evidence:

- validation run **#291** passed all cumulative contracts, behavior suites, PHP/JavaScript/Python syntax checks, and deterministic release-candidate construction;
- MySQL release rehearsal run **#61** first passed the frozen rc.3 clean-site path, then passed schema `8→9` migration/archive/idempotence;
- the M-016 convergence proof preserved pre-adoption canonical copy, rejected stale adoption, verified namespaced post-adoption leaf identity, committed a live typed edit into composition + canonical `page_blocks` + public projection, and preserved the edit across a later full rebuild;
- run #61 uploaded the `ai-native-cms-release-rehearsal-evidence` artifact.

Technical acceptance does not authorize merging the stacked PRs, migrating an installed site, deploying to production, or publishing a new release.

## M-017 — save edited page block as new preset

The current production proving ground added this behavior in `judeoneill.com` PR #61. The portable candidate keeps the user capability but uses the public CMS's stronger typed-preset boundary: Page Composer submits rich-copy leaves, image path/alt pairs, and link href/text pairs rather than any structural block HTML.

The server verifies the selected instance against current canonical/source-derived page state, maps the browser snapshot through the source preset's variable schema, and then derives a new preset. Primitive origins hydrate a fresh governed primitive definition. Converted origins apply typed values to the original governed converted template. Both paths create a new key; neither path mutates the source preset or the selected page instance.

Unknown/stale browser leaf IDs and out-of-range media/link indices fail closed. There is no schema change: `block_presets` remains the sole reusable-block authority under schema v9.

Acceptance requires the new independent static contract plus cumulative validation and MySQL evidence for primitive/converted cloning, typed edit preservation, source preset immutability, page-instance immutability, tamper rejection, deterministic packaging, and frozen rc.3 compatibility.

## Next release frontier

After M-017 verification, continue comparing the reusable core against the current production proving ground after PR #61 and extract additional portable gaps as separate milestones. The M-015 → M-016 → M-017 stack remains unmerged while technical extraction continues. Public publication and production adoption remain explicit operator boundaries.
