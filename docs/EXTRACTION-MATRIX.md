# Extraction and release matrix

This file distinguishes the frozen `0.1.0-rc.3` release baseline from post-release reusable-core extraction.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | released | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Canonical release schema | `database/schema.sql` | core | released — rc.3 / v8 | Frozen release bootstrap remains schema v8 until a later release line is cut |
| Explicit schema upgrades | `database/migrations/` | compatibility core | v7→8 released; v8→9 candidate | Versioned CLI migrations; bootstrap repair and browser requests are never migration |
| Repository page/document registry | `config/site.php` | adopter config | released | Public structure only; repository pages remain distinct from CMS-created pages |
| Page editing/revisions | content/page APIs + Pages UI | core | released | Sanitized bounded content, optimistic hashes, revisions |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | released | Three-way source/canonical reconciliation; browser never promotes repository source into SQL |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | released; v9 floor candidate | Accepted state projects deterministically; anonymous reads stay static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | released | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls and site-wide quality | SEO APIs/UI + audit/projection | core | released | Canonical overrides; same-origin canonicals; deterministic projection |
| Canonical redirects + static runtime | redirect APIs/UI + `__redirect*` | core | released | Graph safety in SQL; anonymous routing remains database-free |
| Converted structural presets | `api/block-presets.php` | compatibility core | candidate — M-015 | Existing repository block structures become canonical saved recipes with typed copy/media/link values |
| Governed primitive presets | `api/composer-primitives.php`, Block Composer | core | candidate — M-015 | SQL may own constrained semantic definitions; server owns validation and structural HTML generation; browser never submits arbitrary HTML/CSS |
| Saved block authority | schema-v9 `block_presets` | core | candidate — M-015 | One reusable-block authority replaces active `page_block_templates`; legacy table becomes recovery archive during migration |
| Preset instance composition | composition store + Page Composer | core | candidate — M-015 | A preset is a recipe, not a live shared component; each placement stores `presetKey`, stable instance ID, and typed value snapshot |
| Standalone Block Composer | `cms/blocks.php` + API/client | core UX | candidate — M-015 | Design/edit/delete governed presets; in-use presets cannot be deleted |
| Shared thumbnail media picker | `cms/media-picker.*` | core UX | candidate — M-015 | Same first-party media catalog used by Block Composer and Page Composer |
| Unified live Page Composer / retirement of separate Pages editor | Page Composer | core UX | next frontier | Port only after M-015 authority/migration tranche is green; page copy and composition mutations must converge deliberately |
| Media library | media APIs/UI | core | released | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy, navigation, branding | respective stores/APIs/UI | core | released | Trusted shells, parent validation, safe navigation, bounded branding tokens |
| Database bootstrap | `database/bootstrap*.php` | core | released | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | released | Read-only actionable evidence; never deploys/migrates/publishes/exposes secrets |
| Starter site, onboarding, repo/hosting ops, LLM governance | root/config/docs/CMS | product + governance | released — rc.3 | Adopters can initialize, operate, deploy, and collaborate without bypassing authority boundaries |
| Deterministic release package | release metadata + builder | release engineering | released — rc.3 | Published tag remains tied to the old main SHA; feature branches do not redefine the published artifact |
| License and attribution | license files | distribution governance | released — rc.3 | Apache 2.0 + Commons Clause v1.0; source-available, not OSI open source |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## Frozen public release

`0.1.0-rc.3` remains the published schema-v8 release. Its tag, release metadata, and package provenance are not rewritten by post-release feature extraction.

## M-015 — governed saved-block composition

The production proving ground added a coherent reusable-core wave after rc.3: semantic primitive block design, one canonical saved-preset authority, a standalone Block Composer, Page Composer preset instances, and a shared visual media picker. The portable tranche deliberately excludes site-specific content, styling assumptions, credentials, deployment coupling, and the later live-page-editor consolidation.

The v8→v9 migration is CLI-only and guarded. It creates `block_presets`, copies the former template library as converted presets, rewrites canonical composition records from `templateKey` to `presetKey`, renames the old template table to `block_presets_legacy_archive`, and only then advances `app_meta` to schema 9. Browser mutation surfaces fail closed until that migration is complete.

Acceptance requires static/security contracts, PHP/JavaScript syntax, migration behavior, composition invariants including exactly one H1, first-party media bounds, and the cumulative rc.3 contracts to remain green. The next extraction wave—one live Page Composer as the sole page mutation boundary—stays out of scope until M-015 is accepted.
