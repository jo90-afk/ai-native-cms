# Extraction and release matrix

This file records which capabilities belong in reusable core, adapters, release engineering, and adopter-specific layers for AI Native CMS `0.1.0-rc.3`.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | released | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Canonical schema | `database/schema.sql` | core | released — v8 | Structure only; no adopter content seed |
| Explicit schema upgrades | `database/migrations/` | compatibility core | released — 7→8 | Versioned CLI migrations; bootstrap repair is never migration |
| Repository page/document registry | `config/site.php` | adopter config | released | Public structure only; repository pages remain distinct from CMS-created pages |
| Page editing/revisions | content/page APIs + Pages UI | core | released | Sanitized bounded content, optimistic hashes, revisions |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | released | Three-way source/canonical reconciliation; browser never promotes repository source into SQL |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | released | Accepted state projects deterministically; anonymous reads stay static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | released | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls | SEO store/API/UI | core | released | Canonical overrides; same-origin canonicals |
| Site-wide SEO quality audit | `api/seo-quality.php`, `database/seo-audit.php`, Search + Social UI | core QA | released | Read-only duplicate/link/orphan/sitemap/canonical/H1/social/schema/alt findings |
| Deterministic SEO/social/schema enhancement | `api/seo-projection.php`, rebuild finalizer | core projection | released | Canonical overrides first; inherited/custom social modes preserved |
| Release-managed SEO compare-and-swap | `api/content-sync-seo.php` | compatibility core | released | Existing canonical override changes only with exact predecessor hash |
| Canonical redirects | redirect store/API/UI | core | released | Graph serialization, optimistic record revisions, collision/conflict/cycle rejection |
| Static redirect runtime | `__redirect.php`, `__redirect-map.php` | core projection | released | Anonymous redirect path is database-free |
| Templates/composer | composer APIs/UI | core | released | Structural HTML remains repository-owned; CMS stores trusted template identities + typed values |
| Media library | media APIs/UI | core | released | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy | composition store/Composer | core | released | Trusted shells, bounded routes, parent validation/cycle rejection |
| Navigation | navigation store/API/UI | core | released | Canonical ordered state; safe destinations; projects only into `#site-nav` |
| Branding | branding store/API/UI | core + adopter token definitions | released | Identity + explicitly declared bounded CSS variables only |
| Database bootstrap | `database/bootstrap*.php` | core | released | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | released | Read-only actionable evidence; never deploys/migrates/publishes/exposes secrets |
| Starter public site | root pages, `assets/`, `templates/article.html` | product starter | released — rc.3 | Neutral Home/About/Writing site works before customization |
| Public site initializer | `setup/site.php` | onboarding utility | released — rc.3 | CLI-only non-secret public config writer |
| State-derived onboarding | onboarding API/UI | core UX | released — M-011 | Authenticated read-only progress model; no completion flag |
| GitHub/repository operations guide | `docs/REPOSITORY-OPERATIONS.md` | operability docs | released — M-012 | Git vs SQL vs projection vs host state, deploy, backup, migration, rollback |
| Agent repository contract | `AGENTS.md` | governance | released — M-013 | Authority/branch/migration/secret/test rules for coding agents |
| LLM collaboration guide | `docs/LLM-COLLABORATION.md` | governance docs | released — M-013 | Governed design/content/feature/bug/schema/release workflows |
| Browser credential-writing setup | deployment/provider layer | excluded from core | excluded | Core never writes provider/database credentials in browser |
| Redirect interception contract | `docs/DEPLOYMENT-ADAPTERS.md` | adapter contract | released | Serve real files/dirs first; unresolved paths use static redirect projection |
| Apache public/private references | `adapters/apache/` | reference adapters | released | Conservative public caching/compression; private/preview `no-store` |
| Other server/CDN adapters | `adapters/<target>/` | optional adapters | later | Follow the same contract; no platform required |
| Deterministic release package | `VERSION`, release metadata, builder | release engineering | released — rc.3 | Exact source provenance, reproducible ZIP/manifest/SHA256, residue guards |
| License and attribution | `LICENSE`, `LICENSE-APACHE-2.0.txt`, `NOTICE` | distribution governance | released — rc.3 | Apache 2.0 + Commons Clause v1.0; source-available, not OSI open source |
| Installation/upgrade/rollback | installation + release docs | release engineering | released — rc.3 | Site init → secrets → bootstrap → reconcile → onboarding → readiness; migrations/rollback explicit |
| Clean empty-site release rehearsal | workflow + `tests/release_rehearsal.sh` | release assurance | satisfied — M-014 | Packaged candidate proves deterministic build, clean MySQL bootstrap, authenticated onboarding, governed changes, redirect transport, paired rollback |
| Public GitHub prerelease publisher | `.github/workflows/publish-release.yml` | release automation | authorized — rc.3 | Exact main SHA → tag `v0.1.0-rc.3` → prerelease assets; idempotent on same tag/SHA |
| Host repository updater / automatic deployment | provider adapter | optional | later | Host authority remains explicit/provider-specific |
| Newsletter/subscription | extension | optional | later | Not required for core release |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## Release closure

M-001 through M-014 are satisfied for `0.1.0-rc.3`. The final clean-candidate rehearsal built the packaged candidate twice, bootstrapped a fresh MySQL 8 database and owner, reconciled repository content, authenticated through the shipped CMS boundary, reached zero-blocker readiness, exercised governed repository/agent work and canonical content mutation, generated database-free redirect state, restored paired filesystem/database backups, and returned to green readiness.

The final proving-ground parity check found no unresolved reusable CMS-core delta. Governance-only or adopter-only changes do not reopen extraction.

## Public distribution state

The Principal authorized publication of `0.1.0-rc.3`.

`release/release.json` now records:

- channel `public-release-candidate`;
- `public: true`;
- required tag `v0.1.0-rc.3`;
- selected Apache 2.0 + Commons Clause v1.0 source-available license.

The release publisher builds from the exact merged `main` SHA and creates/verifies the Git tag, GitHub prerelease, ZIP, manifest, and SHA256 assets. Repository visibility is also requested; if GitHub's workflow token cannot perform that administrative change, an owner must change visibility to Public in repository settings.

Production deployment/adoption remains independent of source publication.
