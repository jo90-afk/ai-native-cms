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
| Canonical redirects | redirect store/API/UI | core | extracted | Graph serialization, optimistic record revisions, collision/conflict/cycle rejection |
| Static redirect runtime | `__redirect.php`, `__redirect-map.php` | core projection | extracted | Anonymous redirect path is database-free |
| Templates/composer | composer APIs/UI | core | extracted | Structural HTML remains repository-owned; CMS stores trusted template identities + typed values |
| Media library | media APIs/UI | core | extracted | Canonical metadata + adopter-owned bytes; bounded validated uploads |
| Page hierarchy | composition store/Composer | core | extracted | Trusted shells, bounded routes, parent validation/cycle rejection |
| Navigation | navigation store/API/UI | core | extracted | Canonical ordered state; safe destinations; projection only into `#site-nav` |
| Branding | branding store/API/UI | core + adopter token definitions | extracted | Identity + explicitly declared bounded CSS variables only |
| Database bootstrap | `database/bootstrap*.php` | core | extracted | CLI-only schema + first owner; no content seed, credential overwrite, or implicit migration |
| Production readiness | readiness API/CLI/UI | core + adapter checks | extracted | Read-only actionable evidence; never deploys, migrates, publishes, or exposes secrets |
| Starter public site | root pages, `assets/`, `templates/article.html` | product starter | **extracted — rc.3** | Neutral Home/About/Writing site works before customization and seeds trusted templates |
| Public site initializer | `setup/site.php` | onboarding utility | **extracted — rc.3** | CLI-only non-secret public config writer; refuses overwrite unless explicit; never reads/writes credentials |
| State-derived onboarding | onboarding API/UI | core UX | **extracted — rc.3 / M-011 satisfied** | Authenticated read-only progress model; no completion flag; hands mutations to owning guarded contracts |
| GitHub/repository operations guide | `docs/REPOSITORY-OPERATIONS.md` | operability docs | **extracted — rc.3 / M-012 satisfied** | Git vs SQL vs projection vs host state, PR workflow, deploy patterns, backup/migration/rollback/provider checklist |
| Agent repository contract | `AGENTS.md` | governance | **extracted — rc.3 / M-013 satisfied** | Discoverable authority, branch, migration, secret, test, and change-packet rules for coding agents |
| LLM collaboration guide | `docs/LLM-COLLABORATION.md` | governance docs | **extracted — rc.3 / M-013 satisfied** | Design/content/feature/bug/schema/release request packets preserve the four authority classes |
| Browser credential-writing setup | deployment/provider layer | excluded from core | excluded | Friendly onboarding begins after secure bootstrap/auth; core never writes provider/database credentials in browser |
| Redirect interception contract | `docs/DEPLOYMENT-ADAPTERS.md` | adapter contract | extracted | Serve real files/dirs first; unresolved requests use static redirect projection |
| Apache public/private references | `adapters/apache/` | reference adapters | extracted | Conservative public caching/compression; private/preview `no-store`; not required by core |
| Other server/CDN adapters | `adapters/<target>/` | optional adapters | later | Follow the same contract; no platform is required |
| Deterministic release candidate | `VERSION`, release metadata, builder | release engineering | **extracted — rc.3** | Exact source provenance, reproducible ZIP/manifest/SHA256, residue guards, selected license, `public:false` |
| License and attribution | `LICENSE`, `LICENSE-APACHE-2.0.txt`, `NOTICE` | distribution governance | **selected — rc.3** | Apache 2.0 subject to Commons Clause v1.0; source-available, attribution-preserving, not OSI open source |
| Installation/upgrade/rollback | installation + release docs | release engineering | **updated — rc.3** | Public site init → private secrets → bootstrap → reconcile → onboarding → readiness; migrations and rollback explicit |
| Clean empty-site release rehearsal | M-014 | release assurance | **active / required before publication gate** | Rehearse shipped docs, clean bootstrap, onboarding, representative LLM/repo/canonical changes, deploy/rollback, artifact and parity evidence |
| Host repository updater / automatic deployment | provider adapter | optional | later | Host authority stays explicit/provider-specific |
| Newsletter/subscription | extension | optional | later | Not required for core release |
| Site-specific themes/content/integrations | adopter repository | site-only | excluded | Reusable mechanisms may upstream; identity/authored semantics do not |

## rc.3 verification evidence

The first complete M-011–M-013 implementation head `f8a8a19ca2ca6fd9df44bda1b350ba6df00f9856` passed cumulative validation run **#155** (`33001221021`): every M-001–M-010 contract, the new onboarding/governance contract, all executable PHP behavior tests including onboarding/site setup, PHP/JavaScript/Python syntax, deterministic rc.3 build, and private artifact upload.

The run #155 artifact was inspected directly:

- version `0.1.0-rc.3`, schema 8;
- manifest source revision exactly `f8a8a19ca2ca6fd9df44bda1b350ba6df00f9856`;
- candidate ZIP SHA256 `4ed0930c3483719f13b074f50bf3b6667905f84e33d4073476f0f4390345a2c9`, matching the emitted checksum;
- embedded/external manifests identical;
- `public:false`, `licenseSelected:true`, Apache-2.0 + Commons Clause v1.0 metadata present;
- `LICENSE`, Apache license text, `NOTICE`, starter Home/About/Writing site, design assets, article template, safe site initializer, onboarding API/UI, `AGENTS.md`, repository operations guide, LLM collaboration guide, v7→v8 migration, redirect runtime, and Apache reference adapters all present;
- no `.git`, `.github`, `.lattice`, tests/tools/dist/runtime/uploads, adopter-local `config/site.php`, known personal/reference-adopter residue, private-key markers, GitHub token markers, or AWS access-key markers.

M-011, M-012, and M-013 are therefore technically satisfied on the implementation tree. A final documentation head and PR merge still require cumulative verification.

## Current frontier — M-014

The next delegated milestone is the **clean empty-site release rehearsal**. It must start from an rc.3 candidate with no adopter state and prove the product, documentation, repository workflow, LLM governance, deployment boundary, rollback path, deterministic artifact, and production-proving-ground parity together.

Only after M-014 passes does the project return to the Principal publication gate.

Repository visibility, Git tag/GitHub Release creation, public package publication, production deployment, credentials, and production adoption remain separate Principal decisions. License selection is resolved and does not itself authorize publication.
