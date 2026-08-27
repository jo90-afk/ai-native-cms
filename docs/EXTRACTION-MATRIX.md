# Extraction and release matrix

This file distinguishes the frozen `0.1.0-rc.3` public release from development integrated to `main` and later governed milestones.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | released | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Published release schema | `database/schema.sql` at rc.3 | core | released — rc.3 / v8 | Frozen package remains schema v8 and is not redefined by development `main` |
| Explicit schema upgrades | `database/migrations/` | compatibility core | v7→8 released; v8→9 integrated; v9→10 candidate M-019 | Versioned CLI migrations; bootstrap repair/browser requests are never migration |
| Repository page/document registry | `config/site.php` | adopter config | released | Repository pages remain bounded source-lineage entries even when adopted into canonical composition |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | released | Three-way source/canonical reconciliation; browser never promotes repository source silently |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | released; clean routes integrated M-018 | Accepted state projects deterministically; anonymous page delivery stays static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | released | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls and site-wide quality | SEO APIs/UI + audit/projection | core | released | Canonical overrides; same-origin canonicals; deterministic projection |
| Canonical redirects + static runtime | redirect APIs/UI + `__redirect*` | core | released | Graph safety in SQL; anonymous routing remains database-free |
| Governed saved block presets | schema-v9 `block_presets` + renderer | core | integrated — M-015 / PR #20 | Browser never submits arbitrary structural HTML/CSS |
| Preset instance composition | composition store | core | integrated — M-015 / PR #20 | Stable preset/instance identity with independent typed values |
| Standalone Block Composer | `cms/blocks.php` + API/client | core UX | integrated — M-015 / PR #20 | Governed preset design; in-use presets cannot be deleted |
| Shared thumbnail media picker | `cms/media-picker.*` | core UX | integrated — M-015 / PR #20 | First-party media catalog shared across composition surfaces |
| Unified live Page Composer | `cms/composer.php`, typed composition APIs | core UX | integrated — M-016 / PR #21 | Public iframe is interaction state; server remains structural authority |
| Source-derived page adoption/convergence | composition store + Page Composer | compatibility/core | integrated — M-016 / PR #21 | Accepted copy survives adoption/rebuild; stale first adoption fails closed |
| Retired separate Pages mutation surface | `/cms/pages.php` compatibility redirect | compatibility UX | integrated — M-016 / PR #21 | One browser page-authoring concept |
| Embedded Block Composer + incremental preset import | Page Composer | core/compatibility UX | integrated — M-016 / PR #21 | In-context block design without overwriting existing presets |
| Save selected block as new preset | Page Composer + typed derivation | core UX/authority | integrated — M-017 / PR #22 | New recipe from typed instance values without mutating source/page placement |
| Clean managed-page public routes | `api/page-routes.php`, `api/page-projection.php` | projection core | integrated — M-018 / PR #23 | Stable internal page keys project to clean `/slug/` routes; only managed pages participate |
| Conditional legacy-route canonicalization | deployment adapters | adapter | integrated — M-018 / PR #23 | Redirect `*.html` only when the matching clean projection exists |
| Route-first Composer presentation | Page Composer | core UX | integrated — M-018 / PR #23 | Clean-route adopters see/enter slugs while API authority remains stable `*.html` keys; feature-off adopters retain filename UI |
| Media library | media APIs/UI | core | released | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy, navigation, branding | respective stores/APIs/UI | core | released; clean-route aware after M-018 | Trusted shells, parent validation, safe navigation, bounded branding tokens |
| Database bootstrap | `database/bootstrap*.php` | core | released | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | released | Read-only actionable evidence; never deploys/migrates/publishes/exposes secrets |
| Starter site, onboarding, repo/hosting ops, LLM governance | root/config/docs/CMS | product + governance | released; evolving | Adopters can initialize, operate, deploy, and collaborate without bypassing authority boundaries |
| Audience list authority | `audience_lists` + `audience_subscriptions` | core canonical state | implementation candidate — M-019 / PR #24 | Generic lists/memberships are SQL authority; latent legacy subscriber state migrates into disabled generic lists instead of a parallel store |
| Audience CMS + CSV | `/cms/audience.php`, Audience API/export | core UX | implementation candidate — M-019 / PR #24 | Authenticated list management, bounded inspection/export, resend/unsubscribe, no token hashes/internal IDs/bulk sends |
| Governed Signup block | generated Audience `block_presets` entries | core composition UX | implementation candidate — M-019 / PR #24 | Server owns fixed form/list structure; Page Composer places the generated preset |
| Public double-opt-in collection | `/api/audience-subscribe.php` | core public interaction | implementation candidate — M-019 / PR #24 | Non-enumerating, same-origin/rate-limited, scanner-safe GET review + explicit POST confirmation |
| Outgoing transactional mail | `api/mail-transport.php` | provider adapter | implementation candidate — M-019 / PR #24 | Replaceable SMTP/mail/log transport; private credentials never become Audience authority or browser-visible state |
| cPanel email onboarding | `docs/CPANEL-EMAIL.md`, private-config example, onboarding/Audience UI | provider documentation/UX | implementation candidate — M-019 / PR #24 | Use exact Connect Devices secure settings, explicit test send, Email Deliverability SPF/DKIM/DMARC review |
| Deterministic release package | release metadata + builder | release engineering | released — rc.3 | Published tag remains tied to its released SHA; development `main` does not redefine it |
| License and attribution | license files | distribution governance | released — rc.3 | Apache 2.0 + Commons Clause v1.0; source-available, not OSI open source |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## Frozen public release

`0.1.0-rc.3` remains the published schema-v8 release. Its tag, release metadata, installation contract, package provenance, and release notes are not rewritten by later development.

Development `main` can contain merged post-release capabilities without implying that an installed rc.3 site has migrated or that a schema-v9/v10 public release exists.

## Integrated post-release milestones

### M-015 — governed saved-block composition

PR #20 merged on 2026-08-27 after cumulative validation #246 and MySQL release rehearsal #57. It introduced the explicit v8→v9 migration, canonical `block_presets`, governed primitive rendering, Block Composer, typed preset-instance composition, shared media picker, and schema-v8 read-compatibility seam.

### M-016 — unified live Page Composer

PR #21 merged after validation #291 and release rehearsal #61. It consolidated page copy/composition, added source-derived adoption/convergence rules, retired the competing Pages mutation surface, embedded Block Composer, and preserved typed/server-owned structure.

### M-017 — save edited page block as new preset

PR #22 merged after validation #311 and release rehearsal #66. It derives a new independent preset from bounded edited typed values while preserving the source preset and selected page composition.

### M-018 — clean managed-page routes

PR #23 merged to development `main` as `d0e4edadc4ea367c2865b3f91355ffe22ee57ffb` after exact-final-head validation #351 and release rehearsal #80 passed.

M-018 keeps canonical internal managed keys such as `about.html` while projecting `/about/`, scopes projection to the authoritative managed-page set, rewrites managed references/discovery metadata, rebases relocated HTML references, rejects route collisions, and leaves provider request canonicalization in adapters. Runtime URLs created by JavaScript remain a page-implementation responsibility and must be root-relative/base-aware when relocation is possible.

The Page Composer route presentation follows the same feature flag: new clean-route adopters use slugs while configurations that omit/disable the setting retain established filename behavior.

Repository integration still does not imply installed-site adoption, production deployment, or release publication.

## M-019 — Audience lists and mail-provider onboarding

PR #24 is the implementation candidate for the generic Audience capability previously absent from the public core.

### Canonical promotion, not parallel state

The schema-v9→v10 migration creates `audience_lists` and `audience_subscriptions`. If the earlier latent `subscribers` primitive contains data, the migration validates and copies each list/membership into Audience authority, creates imported list definitions as **disabled**, and archives the old table as `subscribers_legacy_archive`. This preserves earlier state without silently opening a public collection surface or maintaining two writable authorities.

### Consent collection

The generic endpoint accepts a list key and email through a same-site/rate-limited POST, returns non-enumerating success semantics, stores confirmation token hashes only, throttles resend, expires pending confirmations, and separates GET review from explicit POST confirmation so link scanners cannot create consent.

Unsubscribed rows remain suppression state. A later explicit new signup may begin a fresh pending double-opt-in cycle rather than silently reactivating membership.

### CMS composition and operations

Audience list saves generate/refresh a trusted Signup block preset in the **Audience** category. Page Composer places the preset while server code owns structural form HTML and fixed list identity.

The Audience CMS provides list settings/status, membership counts and bounded inspection, pending resend, operator unsubscribe, confirmed CSV export, safe mail-configuration status, explicit test send, and handoffs to Page Composer/provider documentation.

### Provider boundary

Transactional delivery supports authenticated SMTP with verified encryption, deliberate host-local PHP `mail`, and development/CI log transport. Provider credentials are read only from private `AINCMS_MAIL_*` runtime configuration and are never returned by mail status.

The cPanel path documents **Email Accounts → Connect Devices → Secure SSL/TLS Settings (Recommended)**, exact provider hostname/port/security values, CMS test sending, and **Email Deliverability** review for SPF/DKIM/DMARC.

### Assurance

The exact final PR head must pass the cumulative `validate` workflow and the release rehearsal containing all three paths: frozen rc.3 clean installation, schema-v9 composition upgrade, and schema-v10 Audience/legacy-preservation/double-opt-in/fake-SMTP rehearsal. Passing earlier candidate heads remains evidence but does not replace exact-final-head verification.

## Next release frontier

If PR #24 reaches green exact-final-head Quality/Assurance, it is ready for the Principal pre-merge handoff. Integration still does not authorize installed-site migration, production provider credentials, enabling public collection, deployment, campaign/bulk mail, or public release publication.
