# Extraction and pre-release matrix

This is the bounded product frontier for AI Native CMS. Lattice derives executable work from the active milestone; this file records what belongs in reusable core, adapters, release engineering, and adopter-specific layers.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior |
| Canonical schema | `database/schema.sql` | core | extracted — v8 | Structure only; no adopter content seed |
| Explicit schema upgrades | `database/migrations/` | compatibility core | extracted — 7→8 | Versioned CLI migrations; bootstrap repair is never migration |
| Repository page/document registry | `config/site.php` | adopter config | extracted | Public structure only; no secrets; repository pages remain distinct from CMS-created pages |
| Page editing/revisions | content/page APIs + Pages UI | core | extracted | Sanitized bounded content, optimistic hashes, revision snapshots |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | extracted | Three-way source/canonical reconciliation; browsers never promote repository source into SQL |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | extracted | Accepted state projects deterministically; anonymous reads stay static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | extracted | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls | SEO store/API/UI | core | extracted | Canonical overrides; same-origin canonicals; discovery consumes final SEO state |
| Site-wide SEO quality audit | `api/seo-quality.php`, `database/seo-audit.php`, Search + Social UI | core QA | **parity extraction implemented; final PR gate pending** | Read-only duplicate/link/orphan/sitemap/canonical/H1/social/schema/alt findings; never creates a second SEO authority |
| Deterministic SEO/social/schema enhancement | `api/seo-projection.php`, rebuild finalizer | core projection | **parity extraction implemented; final PR gate pending** | Applies canonical overrides first, honors inherited/custom social modes, fills site-wide social/schema defaults through one projection boundary |
| Release-managed SEO compare-and-swap | `api/content-sync-seo.php`, update-set dispatch | core compatibility | **parity extraction implemented; final PR gate pending** | May seed a missing SEO override; an existing canonical override changes only when the update names its exact predecessor hash |
| Canonical redirects | redirect store/API/UI | core | extracted | Graph serialization, optimistic record revisions, collision/conflict/cycle rejection |
| Static redirect runtime | `__redirect.php`, `__redirect-map.php` | core projection | extracted | Anonymous redirect path is database-free |
| Templates/composer | composer APIs/UI | core | extracted | Structural HTML remains repository-owned; CMS stores trusted template identities + typed values |
| Media library | media APIs/UI | core | extracted | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy | composition store/Composer | core | extracted | Trusted shells, bounded routes, parent validation/cycle rejection |
| Navigation | navigation store/API/UI | core | extracted | Canonical ordered state; safe destinations; projection only into `#site-nav` |
| Branding | branding store/API/UI | core + adopter token definitions | extracted | Identity + explicitly declared bounded CSS variables only |
| Database bootstrap | `database/bootstrap*.php` | core | extracted | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | extracted | Read-only actionable evidence; never deploys, migrates, publishes, or exposes secrets |
| Starter public site | root pages, `assets/`, `templates/article.html` | product starter | extracted — rc.3 | Neutral Home/About/Writing site works before customization and seeds trusted templates |
| Public site initializer | `setup/site.php` | onboarding utility | extracted — rc.3 | CLI-only non-secret public config writer; refuses overwrite unless explicit; keeps owner identity aligned with SEO defaults |
| State-derived onboarding | onboarding API/UI | core UX | **merged — rc.3 / M-011** | Authenticated read-only progress model; no completion flag; hands mutations to owning guarded contracts |
| GitHub/repository operations guide | `docs/REPOSITORY-OPERATIONS.md` | operability docs | **merged — rc.3 / M-012** | Git vs SQL vs projection vs host state, PR workflow, deploy patterns, backup/migration/rollback/provider checklist |
| Agent repository contract | `AGENTS.md` | governance | **merged — rc.3 / M-013** | Discoverable authority, branch, migration, secret, test, and change-packet rules for coding agents |
| LLM collaboration guide | `docs/LLM-COLLABORATION.md` | governance docs | **merged — rc.3 / M-013** | Design/content/feature/bug/schema/release request packets preserve the four authority classes |
| Browser credential-writing setup | deployment/provider layer | excluded from core | excluded | Friendly onboarding begins after secure bootstrap/auth; core never writes provider/database credentials in browser |
| Redirect interception contract | `docs/DEPLOYMENT-ADAPTERS.md` | adapter contract | extracted | Serve real files/dirs first; unresolved requests use static redirect projection |
| Apache public/private references | `adapters/apache/` | reference adapters | extracted | Conservative public caching/compression; private/preview `no-store`; not required by core |
| Other server/CDN adapters | `adapters/<target>/` | optional adapters | later | Follow the same contract; no platform is required |
| Deterministic release candidate | `VERSION`, release metadata, builder | release engineering | extracted — rc.3 | Exact source provenance, reproducible ZIP/manifest/SHA256, residue guards, selected license, `public:false` |
| License and attribution | `LICENSE`, `LICENSE-APACHE-2.0.txt`, `NOTICE` | distribution governance | selected — rc.3 | Apache 2.0 subject to Commons Clause v1.0; source-available, attribution-preserving, not OSI open source |
| Installation/upgrade/rollback | installation + release docs | release engineering | updated — rc.3 | Public site init → private secrets → bootstrap → reconcile → onboarding → readiness; migrations and rollback explicit |
| Clean empty-site release rehearsal | M-014 | release assurance | **blocked by reopened production parity** | Rehearse shipped docs, clean bootstrap, onboarding, representative LLM/repo/canonical changes, deploy/rollback, artifact and parity evidence only after parity is current |
| Host repository updater / automatic deployment | provider adapter | optional | later | Host authority stays explicit/provider-specific |
| Newsletter/subscription | extension | optional | later | Not required for core release |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## rc.3 baseline through PR #15

M-011, M-012, and M-013 were verified on the private rc.3 line and merged through PR #15 at `ab7039efa34a8b00f7357c23f18fb5a9d1251135`. The release remains private/unpublished; no tag, GitHub Release, public package, deployment, credential action, or production adoption occurred.

## Reopened proving-ground parity — production PR #51

The mandatory M-014 preflight parity check found that the production proving ground had advanced from the recorded source baseline `113068842a808ed00268892dc6a2ffa51c27ffa6` to `3830735dda257c14616c32a08f24808953a79bf9` through production PR #51, **“Make SEO a site-wide CMS quality system.”**

That production change contains reusable core mechanics, so parity reopened before the clean release rehearsal could be accepted.

The public extraction deliberately includes only site-neutral mechanisms:

- site-wide read-only SEO quality detection;
- Search + Social site/page findings;
- deterministic author/social/schema projection defaults;
- effective inherited-vs-custom social projection;
- guarded expected-hash release SEO updates;
- generic CLI SEO audit and release regression coverage.

The extraction deliberately excludes the production site’s authored SEO metadata/update set, site-specific configuration, deployment script, content assumptions, and Lattice/evidence files.

Cumulative run #182 on parity head `4c83be4f3ac6263823a736df77e96bd880a723c4` passed all structural contracts, all executable behavior tests including the new SEO quality/projection primitives, PHP/JavaScript/Python syntax, deterministic candidate build, and private artifact upload. Documentation commits after that implementation head still require the exact-head branch/PR gate before parity is closed.

## Current frontier

Close the reopened SEO parity objective on a verified PR, recheck the current proving-ground head, then recreate M-014 from the new `main` baseline. The old rehearsal branch was cut before this reusable production delta and cannot serve as final release evidence.

Only after M-014 passes does the project return to the Principal publication gate.

Repository visibility, Git tag/GitHub Release creation, public package publication, production deployment, credentials, and production adoption remain separate Principal decisions. License selection is resolved and does not itself authorize publication.
