# Extraction and pre-release matrix

This is the bounded product frontier for AI Native CMS. Lattice derives executable work from the active milestone; this file records what belongs in reusable core, adapters, release engineering, and adopter-specific layers.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets stay outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior |
| Canonical schema | `database/schema.sql` | core | extracted — v8 | Structure only; no adopter content seed |
| Explicit schema upgrades | `database/migrations/` | compatibility core | extracted — 7→8 | Versioned CLI migrations; bootstrap repair is never migration |
| Repository page/document registry | `config/site.php` | adopter config | extracted | Public structure only; repository pages remain distinct from CMS-created pages |
| Page editing/revisions | content/page APIs + Pages UI | core | extracted | Sanitized bounded content, optimistic hashes, revision snapshots |
| Canonical content reconciliation | content authority/sync + CLI reconcile | core | extracted | Three-way source/canonical reconciliation; browsers never promote repository source into SQL |
| Deterministic public projection | rebuild/projector pipeline | core + adapter hooks | extracted | Accepted state projects deterministically; anonymous reads stay static-first |
| Posts/drafts/revisions/publishing | writing store/API/UI | core | extracted | Canonical SQL, bounded Markdown, static projection, slug-history redirects |
| SEO controls | SEO store/API/UI | core | extracted | Canonical overrides; same-origin canonicals; discovery consumes final SEO state |
| Site-wide SEO quality audit | `api/seo-quality.php`, `database/seo-audit.php`, Search + Social UI | core QA | **extracted — parity closed** | Read-only duplicate/link/orphan/sitemap/canonical/H1/social/schema/alt findings; never creates a second SEO authority |
| Deterministic SEO/social/schema enhancement | `api/seo-projection.php`, rebuild finalizer | core projection | **extracted — parity closed** | Applies canonical overrides first, honors inherited/custom social modes, fills site-wide defaults through one projection boundary |
| Release-managed SEO compare-and-swap | `api/content-sync-seo.php`, update-set dispatch | core compatibility | **extracted — parity closed** | May seed missing SEO state; existing canonical override changes only with its exact predecessor hash |
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
| Public site initializer | `setup/site.php` | onboarding utility | extracted — rc.3 | CLI-only non-secret public config writer; refuses overwrite unless explicit |
| State-derived onboarding | onboarding API/UI | core UX | **satisfied — rc.3 / M-011** | Authenticated read-only progress model; no completion flag; mutations stay with owning guarded contracts |
| GitHub/repository operations guide | `docs/REPOSITORY-OPERATIONS.md` | operability docs | **satisfied — rc.3 / M-012** | Git vs SQL vs projection vs host state, PR workflow, deploy patterns, backup/migration/rollback/provider checklist |
| Agent repository contract | `AGENTS.md` | governance | **satisfied — rc.3 / M-013** | Discoverable authority, branch, migration, secret, test, and change-packet rules for coding agents |
| LLM collaboration guide | `docs/LLM-COLLABORATION.md` | governance docs | **satisfied — rc.3 / M-013** | Design/content/feature/bug/schema/release request packets preserve the four authority classes |
| Browser credential-writing setup | deployment/provider layer | excluded from core | excluded | Friendly onboarding begins after secure bootstrap/auth; core never writes provider/database credentials in browser |
| Redirect interception contract | `docs/DEPLOYMENT-ADAPTERS.md` | adapter contract | extracted | Serve real files/dirs first; unresolved requests use static redirect projection |
| Apache public/private references | `adapters/apache/` | reference adapters | extracted | Conservative public caching/compression; private/preview `no-store`; not required by core |
| Other server/CDN adapters | `adapters/<target>/` | optional adapters | later | Follow the same contract; no platform is required |
| Deterministic release candidate | `VERSION`, release metadata, builder | release engineering | extracted — rc.3 | Exact source provenance, reproducible ZIP/manifest/SHA256, residue guards, selected license, `public:false` |
| License and attribution | `LICENSE`, `LICENSE-APACHE-2.0.txt`, `NOTICE` | distribution governance | selected — rc.3 | Apache 2.0 subject to Commons Clause v1.0; source-available, attribution-preserving, not OSI open source |
| Installation/upgrade/rollback | installation + release docs | release engineering | satisfied — rc.3 | Public site init → private secrets → bootstrap → reconcile → onboarding → readiness; migrations and paired rollback explicit |
| Clean empty-site release rehearsal | workflow + `tests/release_rehearsal.sh` | release assurance | **satisfied — rc.3 / M-014 implementation** | Packaged candidate proves deterministic build, clean MySQL bootstrap, authenticated onboarding, governed repo/canonical changes, redirect transport, and paired rollback |
| Host repository updater / automatic deployment | provider adapter | optional | later | Host authority stays explicit/provider-specific |
| Newsletter/subscription | extension | optional | later | Not required for core release |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## rc.3 pre-publication closure

M-011 through M-013 were merged through PR #15. The reopened site-wide SEO core parity work was merged through PR #16 at `eea0c0f9a6faf0b147551dc797e4b39a43d3038a`.

M-014 implementation head `43a981e4d995294c48f4022fc724bb2c48392da4` then passed both the cumulative PR gate and the clean packaged-candidate rehearsal. The rehearsal used a clean MySQL 8 service, reached green readiness, authenticated through the shipped CMS boundary, completed state-derived onboarding, exercised repository-owned agent work and canonical content mutation through their separate authorities, generated database-free redirect state, restored paired filesystem/database backups, and returned to green readiness. Two builds from the same source revision were byte-identical.

The post-rehearsal proving-ground head check found a newer change only in governance/evidence files, with no reusable CMS-core delta. Parity therefore remains closed.

The final documentation head must still pass the cumulative and clean-rehearsal workflows and receive one last proving-ground head check before PR #17 merges.

## Current frontier — Principal publication gate

After that final green merge, delegated pre-publication engineering is complete. The remaining actions are separate Principal decisions:

1. repository visibility;
2. public tag/GitHub Release creation;
3. public package/download publication;
4. production deployment/adoption.

License selection is resolved but does not itself authorize publication. Candidate metadata remains `public:false` until an explicit publication decision changes the relevant boundary.
