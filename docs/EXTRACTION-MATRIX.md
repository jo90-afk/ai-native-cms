# Extraction and release matrix

This file distinguishes the frozen `0.1.0-rc.3` public release from development already integrated to `main` and from later independently governed extraction milestones.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | released | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Published release schema | `database/schema.sql` at rc.3 | core | released — rc.3 / v8 | Frozen package remains schema v8 and is not redefined by development `main` |
| Explicit schema upgrades | `database/migrations/` | compatibility core | v7→8 released; v8→9 integrated in M-015 | Versioned CLI migrations; bootstrap repair and browser requests are never migration |
| Repository page/document registry | `config/site.php` | adopter config | released | Repository pages remain bounded source-lineage entries even when adopted into canonical composition |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | released | Three-way source/canonical reconciliation; browser never promotes repository source silently |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | released; expanded by M-018 candidate | Accepted state projects deterministically; anonymous reads stay static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | released | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls and site-wide quality | SEO APIs/UI + audit/projection | core | released | Canonical overrides; same-origin canonicals; deterministic projection |
| Canonical redirects + static runtime | redirect APIs/UI + `__redirect*` | core | released | Graph safety in SQL; anonymous routing remains database-free |
| Governed saved block presets | schema-v9 `block_presets` + renderer | core | integrated — M-015 / PR #20 | Saved recipes own bounded converted or semantic primitive structure; browser never submits arbitrary structural HTML/CSS |
| Preset instance composition | composition store | core | integrated — M-015 / PR #20 | Each placement stores `presetKey`, stable instance ID, and independent typed values |
| Standalone Block Composer | `cms/blocks.php` + API/client | core UX | integrated — M-015 / PR #20 | Governed preset design; in-use presets cannot be deleted |
| Shared thumbnail media picker | `cms/media-picker.*` | core UX | integrated — M-015 / PR #20 | Same first-party media catalog used across composition surfaces |
| Unified live Page Composer | `cms/composer.php`, typed composition APIs | core UX | integrated — M-016 / PR #21 | Public iframe is interaction state; server remains structural authority |
| Source-derived page adoption | composition store + Page Composer | compatibility core | integrated — M-016 / PR #21 | Existing canonical page copy is hydrated; stale first adoption fails closed |
| Unified page-copy/composition commit | composition store + `page_blocks` | core authority | integrated — M-016 / PR #21 | Intentional Composer saves update typed composition and canonical editable leaves together |
| Retired separate Pages mutation surface | `/cms/pages.php` compatibility redirect | compatibility UX | integrated — M-016 / PR #21 | One browser page-authoring concept |
| Embedded Block Composer | Page Composer + embed bridge | core UX | integrated — M-016 / PR #21 | Preset design can be summoned in context without changing snapshot semantics |
| Incremental repository-preset import | preset bootstrap + Page Composer | compatibility core | integrated — M-016 / PR #21 | Creates missing converted presets without overwriting saved presets |
| Save selected block as new preset | Page Composer + preset derivation | core UX/authority | integrated — M-017 / PR #22 | Typed instance values create a new reusable recipe without mutating source preset/page placement |
| Typed save-as-new snapshot mapping | `api/composition-values.php` | core authority | integrated — M-017 / PR #22 | Unknown/stale browser leaf identity or indexed-field drift fails closed |
| Clean managed-page public routes | `api/page-routes.php`, `api/page-projection.php` | projection core | technically verified — M-018 / PR #23 | Stable internal page keys project to clean `/slug/` static routes; only managed pages participate |
| Conditional legacy-route canonicalization | deployment adapters | adapter | technically verified — M-018 / PR #23 | Redirect `*.html` only when the matching clean projection exists; core does not depend on Apache |
| Media library | media APIs/UI | core | released | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy, navigation, branding | respective stores/APIs/UI | core | released; navigation clean-route aware in M-018 candidate | Trusted shells, parent validation, safe navigation, bounded branding tokens |
| Database bootstrap | `database/bootstrap*.php` | core | released | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | released | Read-only actionable evidence; never deploys/migrates/publishes/exposes secrets |
| Starter site, onboarding, repo/hosting ops, LLM governance | root/config/docs/CMS | product + governance | released; authority model updated through M-018 candidate | Adopters can initialize, operate, deploy, and collaborate without bypassing authority boundaries |
| Deterministic release package | release metadata + builder | release engineering | released — rc.3 | Published tag remains tied to its released SHA; development branches/main do not redefine it |
| License and attribution | license files | distribution governance | released — rc.3 | Apache 2.0 + Commons Clause v1.0; source-available, not OSI open source |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |
| Lattice-specific signup control pane | proving-ground PR #66 | site-only at present | excluded / no generic authority | Public core has no subscriber/list authority; do not invent a parallel store merely to chase parity |

## Frozen public release

`0.1.0-rc.3` remains the published schema-v8 release. Its tag, release metadata, installation contract, package provenance, and release notes are not rewritten by later development.

Development `main` can contain merged post-release capabilities without implying that an installed rc.3 site has migrated or that a schema-v9 public release exists.

## Integrated post-release milestones

### M-015 — governed saved-block composition

PR #20 merged to `main` as `16d2571b18f1f404233140aea315cbd205575b51` on 2026-08-27 after cumulative validation run #246 and MySQL release rehearsal run #57. It introduced the explicit v8→v9 migration, canonical `block_presets`, governed primitive rendering, Block Composer, typed preset-instance composition, shared media picker, and the schema-v8 read-compatibility seam.

### M-016 — unified live Page Composer

PR #21 merged to `main` as `e7b91a1df138c8fdbf6fbecfdaba7342b94a7ee3` on 2026-08-27 after validation run #291 and release rehearsal run #61. It consolidated page copy and composition into the live Page Composer, added source-derived adoption/convergence rules, retired the competing Pages mutation surface, embedded Block Composer, and preserved typed/server-owned structure.

### M-017 — save edited page block as new preset

PR #22 merged to `main` as `b5e384f0d58057fa650caeb3dffc430f3764b3ca` on 2026-08-27 after validation run #311 and release rehearsal run #66. It allows a selected edited block instance to create a new independent preset through bounded typed values while preserving the source preset and selected page composition.

Those repository merges remain distinct from installed-site migration, production adoption, and release publication.

## M-018 — clean managed-page routes

Draft PR #23 reconstructs the reusable behavior proven by reference proving-ground PR #63 and carries the generic runtime-relative URL lesson exposed by source PR #65. The source implementation is evidence, not a file-copy authority.

The public implementation keeps canonical managed page keys such as `about.html` while optionally projecting `/about/index.html` for the reader-facing `/about/` route. Clean routes are enabled in the example configuration for new installs; the runtime default is off when the setting is absent so existing adopters retain their established route shape until explicit adoption.

The projector is scoped to the authoritative managed-page set, rewrites managed references and generated discovery metadata, preserves external/unmanaged destinations, rebases relocated HTML resource/form references, and rejects route collisions. JavaScript-created runtime URLs are explicitly outside HTML parsing and must use a root-relative or otherwise base-aware strategy when a page may move beneath `/slug/`.

Apache conditional legacy-route redirects remain an adapter concern and fire only when the clean `slug/index.html` projection exists.

Accepted technical evidence on candidate `0e432ab5aebe415bf0227660964b21dc75e5d2d8` before the documentation reconciliation commit:

- validation run #331 (`33104318665`) passed every cumulative contract and behavior suite, the new M-018 structural/PHP behavior gates, all syntax checks, and deterministic candidate build;
- release-rehearsal run #70 (`33104318671`) passed the frozen rc.3 clean-site path and schema-v9 upgrade/composition rehearsal and uploaded evidence;
- no schema migration or canonical SQL mutation is introduced.

Because durable documentation/project state was updated after that candidate, the exact final PR head must pass the same required workflows before pre-merge handoff.

## Next extraction frontier

Continue comparing the reusable core with the current proving ground after PR #65. Proving-ground PR #66 is currently classified as site-only because its audience pane depends on an existing site-specific `subscribers` authority and `list_key='lattice-updates'`; `ai-native-cms` has no corresponding generic list/subscriber authority. A future generic audience capability would need its own product mandate and authority design rather than copying that pane or creating a second store.

Public release publication, installed-site migration, and production deployment remain explicit operator/Principal boundaries.
